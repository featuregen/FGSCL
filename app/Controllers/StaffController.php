<?php
/**
 * Staff Controller
 * CRUD for teachers, staff members
 */

class StaffController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    private function getSchoolId(): ?int { return Session::schoolId(); }

    private function getSchoolSetting(int $schoolId, string $key, string $default = ''): string
    {
        $row = Database::fetch(
            "SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = ?",
            [$schoolId, $key]
        );
        return $row['setting_value'] ?? $default;
    }

    private function generateEmployeeId(int $schoolId): string
    {
        $prefix = $this->getSchoolSetting($schoolId, 'employee_id_prefix', 'EMP');
        $startNumber = max(1, intval($this->getSchoolSetting($schoolId, 'employee_id_start', '1')));

        // Find last employee_id
        $last = Database::fetch(
            "SELECT employee_id FROM staff_details WHERE school_id = ? AND employee_id IS NOT NULL ORDER BY id DESC LIMIT 1",
            [$schoolId]
        );

        $nextSeq = $startNumber;
        if ($last && $last['employee_id']) {
            preg_match('/(\d+)$/', $last['employee_id'], $matches);
            if (!empty($matches[1])) {
                $nextSeq = max($startNumber, (int)$matches[1] + 1);
            }
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    // ─── Staff List ─────────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $search = $_GET['search'] ?? '';
        $type = $_GET['user_type'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 25;

        $where = "u.school_id = ? AND u.user_type IN ('teacher','staff','accountant','librarian','transport_manager')";
        $params = [$schoolId];

        if (!empty($search)) {
            $where .= " AND (u.full_name LIKE ? OR u.phone LIKE ? OR u.email LIKE ? OR sd.employee_id LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if (!empty($type)) {
            $where .= " AND u.user_type = ?";
            $params[] = $type;
        }
        if ($status !== '') {
            $statWhere = $status === 'active' ? 'u.is_active = 1' : 'u.is_active = 0';
            $where .= " AND {$statWhere}";
        }

        // Count
        $total = (int)(Database::fetch(
            "SELECT COUNT(*) as cnt FROM users u LEFT JOIN staff_details sd ON u.id = sd.user_id WHERE {$where}",
            $params
        )['cnt'] ?? 0);

        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        // Fetch
        $staff = Database::fetchAll(
            "SELECT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.avatar, u.is_active, u.user_type, u.username,
                    sd.employee_id, COALESCE(dep.name, sd.department) as department, COALESCE(desig.name, sd.designation) as designation,
                    sd.qualification, sd.date_of_joining, sd.status as staff_status,
                    sd.salary, sd.experience_years
             FROM users u
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments dep ON sd.department_id = dep.id
             LEFT JOIN designations desig ON sd.designation_id = desig.id
             WHERE {$where}
             ORDER BY u.full_name ASC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get subject assignments count for teachers
        foreach ($staff as &$s) {
            if ($s['user_type'] === 'teacher') {
                $s['subject_count'] = (int)(Database::fetch(
                    "SELECT COUNT(DISTINCT subject_id) as cnt FROM timetable WHERE teacher_id = ?",
                    [$s['id']]
                )['cnt'] ?? 0);
            }
        }

        Response::view('staff/index', [
            'pageTitle'   => 'Staff Management',
            'staff'       => $staff,
            'search'      => $search,
            'userType'    => $type,
            'status'      => $status,
            'total'       => $total,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'breadcrumbs' => [['label' => 'Staff']],
        ]);
    }

    // ─── Create Staff Form ──────────────────────
    public function create()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $departments = Database::fetchAll("SELECT id, name FROM departments WHERE school_id = ? AND is_active = 1 ORDER BY name", [$schoolId]);
        $designations = Database::fetchAll("SELECT id, name, staff_category FROM designations WHERE school_id = ? AND is_active = 1 ORDER BY staff_category, level, name", [$schoolId]);
        $customFields = $this->loadStaffCustomFields($schoolId);

        Response::view('staff/form', [
            'pageTitle'    => 'Add Staff',
            'staff'        => null,
            'departments'  => $departments,
            'designations' => $designations,
            'customFields' => $customFields,
            'customValues' => [],
            'breadcrumbs'  => [
                ['label' => 'Staff', 'url' => APP_URL . '/staff'],
                ['label' => 'Add Staff'],
            ],
        ]);
    }

    private function loadStaffCustomFields(int $schoolId): array
    {
        return Database::fetchAll(
            "SELECT * FROM custom_fields WHERE school_id = ? AND form_type = 'staff' AND is_active = 1 ORDER BY display_order ASC",
            [$schoolId]
        );
    }

    private function loadCustomValues(int $entityId): array
    {
        $rows = Database::fetchAll(
            "SELECT custom_field_id, field_value FROM custom_field_values WHERE entity_type = 'staff' AND entity_id = ?",
            [$entityId]
        );
        $map = [];
        foreach ($rows as $r) { $map[$r['custom_field_id']] = $r['field_value']; }
        return $map;
    }

    private function saveCustomFieldValues(int $schoolId, int $userId, array $data): void
    {
        $customFields = $this->loadStaffCustomFields($schoolId);
        foreach ($customFields as $cf) {
            $val = $data['cf_' . $cf['id']] ?? null;
            if ($val !== null && $val !== '') {
                $existing = Database::fetch(
                    "SELECT id FROM custom_field_values WHERE custom_field_id = ? AND entity_type = 'staff' AND entity_id = ?",
                    [$cf['id'], $userId]
                );
                if ($existing) {
                    Database::update('custom_field_values', ['field_value' => $val], 'id = ?', [$existing['id']]);
                } else {
                    Database::insert('custom_field_values', [
                        'custom_field_id' => $cf['id'],
                        'entity_type'     => 'staff',
                        'entity_id'       => $userId,
                        'field_value'     => $val,
                    ]);
                }
            }
        }
    }

    // ─── Store Staff ────────────────────────────
    public function store()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        $name = trim($data['full_name'] ?? '');
        if (empty($name)) {
            Session::flash('error', 'Full name is required.');
            Response::back();
            return;
        }

        $userType = $data['user_type'] ?? 'teacher';
        $email = !empty($data['email']) ? $data['email'] : null;
        $password = !empty($data['password']) ? trim($data['password']) : 'Emp@123';

        // Generate employee ID (used as username)
        $employeeId = !empty($data['employee_id']) ? strtoupper(trim($data['employee_id'])) : $this->generateEmployeeId($schoolId);
        $username = $employeeId; // Employee ID = Login Username

        try {
            Database::beginTransaction();

            $userId = Database::insert('users', [
                'school_id'     => $schoolId,
                'username'      => $username,
                'email'         => $email,
                'password'      => password_hash($password, PASSWORD_DEFAULT),
                'full_name'     => $name,
                'phone'         => !empty($data['phone']) ? $data['phone'] : null,
                'gender'        => !empty($data['gender']) ? $data['gender'] : null,
                'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'user_type'     => $userType,
                'is_active'     => 1,
                'created_by'    => Session::userId(),
            ]);

            Database::insert('staff_details', [
                'user_id'          => $userId,
                'school_id'        => $schoolId,
                'employee_id'      => $employeeId,
                'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'designation_id'   => !empty($data['designation_id']) ? (int)$data['designation_id'] : null,
                'staff_category'   => $data['staff_category'] ?? 'teaching',
                'department'       => !empty($data['department']) ? $data['department'] : null,
                'designation'      => !empty($data['designation']) ? $data['designation'] : null,
                'qualification'    => !empty($data['qualification']) ? $data['qualification'] : null,
                'experience_years' => !empty($data['experience_years']) ? (int)$data['experience_years'] : 0,
                'date_of_joining'  => !empty($data['date_of_joining']) ? $data['date_of_joining'] : null,
                'salary'           => !empty($data['salary']) ? $data['salary'] : null,
                'address'          => !empty($data['address']) ? $data['address'] : null,
                'city'             => !empty($data['city']) ? $data['city'] : null,
                'state'            => !empty($data['state']) ? $data['state'] : null,
                'pincode'          => !empty($data['pincode']) ? $data['pincode'] : null,
                'emergency_contact'=> !empty($data['emergency_contact']) ? $data['emergency_contact'] : null,
                'blood_group'      => !empty($data['blood_group']) ? $data['blood_group'] : null,
            ]);

            // Assign role
            $roleSlug = $userType;
            $role = Database::fetch("SELECT id FROM roles WHERE slug = ?", [$roleSlug]);
            if ($role) {
                Database::insert('user_roles', [
                    'user_id' => $userId,
                    'role_id' => $role['id'],
                ]);
            }

            // Save custom field values
            $this->saveCustomFieldValues($schoolId, $userId, $data);

            Database::commit();
            Session::flash('success', "Staff '{$name}' added. Login → Username: {$username} | Password: {$password}");
            Response::redirect('staff');

        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
            Response::back();
        }
    }

    // ─── Edit Staff Form ────────────────────────
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $staff = Database::fetch(
            "SELECT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.avatar, u.username, u.is_active, u.user_type,
                    sd.employee_id, sd.department_id, sd.designation_id, sd.staff_category,
                    COALESCE(dep.name, sd.department) as department, COALESCE(desig.name, sd.designation) as designation,
                    sd.qualification, sd.experience_years,
                    sd.date_of_joining, sd.salary, sd.address, sd.city, sd.state, sd.pincode,
                    sd.emergency_contact, sd.blood_group, sd.status as staff_status
             FROM users u
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments dep ON sd.department_id = dep.id
             LEFT JOIN designations desig ON sd.designation_id = desig.id
             WHERE u.id = ? AND u.school_id = ?",
            [$id, $schoolId]
        );

        if (!$staff) { Response::abort(404); return; }

        // Subject assignments for teachers (from timetable)
        $assignments = [];
        if ($staff['user_type'] === 'teacher') {
            $assignments = Database::fetchAll(
                "SELECT MIN(t.id) as id, c.name as class_name, s.name as subject_name,
                        COUNT(*) as periods_per_week
                 FROM timetable t
                 JOIN classes c ON t.class_id = c.id
                 JOIN subjects s ON t.subject_id = s.id
                 WHERE t.teacher_id = ?
                 GROUP BY t.class_id, t.subject_id, c.name, s.name, c.numeric_name
                 ORDER BY c.numeric_name, s.name",
                [$id]
            );
        }

        $departments = Database::fetchAll("SELECT id, name FROM departments WHERE school_id = ? AND is_active = 1 ORDER BY name", [$schoolId]);
        $designations = Database::fetchAll("SELECT id, name, staff_category FROM designations WHERE school_id = ? AND is_active = 1 ORDER BY staff_category, level, name", [$schoolId]);
        $customFields = $this->loadStaffCustomFields($schoolId);
        $customValues = $this->loadCustomValues($id);

        Response::view('staff/form', [
            'pageTitle'    => 'Edit Staff',
            'staff'        => $staff,
            'assignments'  => $assignments,
            'departments'  => $departments,
            'designations' => $designations,
            'customFields' => $customFields,
            'customValues' => $customValues,
            'breadcrumbs'  => [
                ['label' => 'Staff', 'url' => APP_URL . '/staff'],
                ['label' => 'Edit: ' . $staff['full_name']],
            ],
        ]);
    }

    // ─── Update Staff ───────────────────────────
    public function update($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        try {
            Database::beginTransaction();

            // Resolve employee ID (= username)
            $employeeId = !empty($data['employee_id']) ? strtoupper(trim($data['employee_id'])) : $this->generateEmployeeId($schoolId);

            $userData = [
                'full_name'     => $data['full_name'],
                'username'      => $employeeId, // Sync employee_id → username
                'phone'         => !empty($data['phone']) ? $data['phone'] : null,
                'gender'        => !empty($data['gender']) ? $data['gender'] : null,
                'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'email'         => !empty($data['email']) ? $data['email'] : null,
                'user_type'     => $data['user_type'] ?? 'teacher',
            ];

            // Update password only if provided
            if (!empty($data['password'])) {
                $userData['password'] = password_hash(trim($data['password']), PASSWORD_DEFAULT);
            }

            Database::update('users', $userData, 'id = ? AND school_id = ?', [$id, $schoolId]);

            // Check if staff_details exists
            $existing = Database::fetch("SELECT id FROM staff_details WHERE user_id = ? AND school_id = ?", [$id, $schoolId]);

            $staffData = [
                'employee_id'      => $employeeId,
                'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'designation_id'   => !empty($data['designation_id']) ? (int)$data['designation_id'] : null,
                'staff_category'   => $data['staff_category'] ?? 'teaching',
                'department'       => !empty($data['department']) ? $data['department'] : null,
                'designation'      => !empty($data['designation']) ? $data['designation'] : null,
                'qualification'    => !empty($data['qualification']) ? $data['qualification'] : null,
                'experience_years' => !empty($data['experience_years']) ? (int)$data['experience_years'] : 0,
                'date_of_joining'  => !empty($data['date_of_joining']) ? $data['date_of_joining'] : null,
                'salary'           => !empty($data['salary']) ? $data['salary'] : null,
                'address'          => !empty($data['address']) ? $data['address'] : null,
                'city'             => !empty($data['city']) ? $data['city'] : null,
                'state'            => !empty($data['state']) ? $data['state'] : null,
                'pincode'          => !empty($data['pincode']) ? $data['pincode'] : null,
                'emergency_contact'=> !empty($data['emergency_contact']) ? $data['emergency_contact'] : null,
                'blood_group'      => !empty($data['blood_group']) ? $data['blood_group'] : null,
            ];

            if ($existing) {
                Database::update('staff_details', $staffData, 'user_id = ? AND school_id = ?', [$id, $schoolId]);
            } else {
                $staffData['user_id'] = $id;
                $staffData['school_id'] = $schoolId;
                Database::insert('staff_details', $staffData);
            }

            // Save custom field values
            $this->saveCustomFieldValues($schoolId, (int)$id, $data);

            Database::commit();
            Session::flash('success', 'Staff updated successfully.');
            Response::redirect('staff');

        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
            Response::back();
        }
    }

    // ─── View Staff Profile ─────────────────────
    public function show($id)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $staff = Database::fetch(
            "SELECT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.avatar, u.username, u.is_active, u.user_type, u.created_at,
                    sd.employee_id, sd.department_id, sd.designation_id, sd.staff_category,
                    COALESCE(dep.name, sd.department) as department, COALESCE(desig.name, sd.designation) as designation,
                    sd.qualification, sd.experience_years,
                    sd.date_of_joining, sd.salary, sd.address, sd.city, sd.state, sd.pincode,
                    sd.emergency_contact, sd.blood_group, sd.status as staff_status
             FROM users u
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments dep ON sd.department_id = dep.id
             LEFT JOIN designations desig ON sd.designation_id = desig.id
             WHERE u.id = ? AND u.school_id = ?",
            [$id, $schoolId]
        );

        if (!$staff) { Response::abort(404); return; }

        $assignments = Database::fetchAll(
            "SELECT MIN(t.id) as id, c.name as class_name, sec.name as section_name, s.name as subject_name,
                    COUNT(*) as periods_per_week
             FROM timetable t
             JOIN classes c ON t.class_id = c.id
             JOIN subjects s ON t.subject_id = s.id
             LEFT JOIN sections sec ON t.section_id = sec.id
             WHERE t.teacher_id = ?
             GROUP BY t.class_id, t.section_id, t.subject_id, c.name, sec.name, s.name, c.numeric_name
             ORDER BY c.numeric_name, s.name",
            [$id]
        );

        $customFields = $this->loadStaffCustomFields($schoolId);
        $customValues = $this->loadCustomValues($id);

        Response::view('staff/view', [
            'pageTitle'   => $staff['full_name'],
            'staff'       => $staff,
            'assignments' => $assignments,
            'customFields'=> $customFields,
            'customValues'=> $customValues,
            'breadcrumbs' => [
                ['label' => 'Staff', 'url' => APP_URL . '/staff'],
                ['label' => $staff['full_name']],
            ],
        ]);
    }

    // ─── Delete Staff ───────────────────────────
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        Database::update('users', ['is_active' => 0], "id = ? AND school_id = ? AND user_type != 'school_admin'", [$id, $schoolId]);
        Session::flash('success', 'Staff member deactivated.');
        Response::redirect('staff');
    }

    // ─── Import Page ────────────────────────────
    public function import()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('staff/import', [
            'pageTitle'    => 'Import Staff',
            'importResult' => null,
            'breadcrumbs'  => [
                ['label' => 'Staff', 'url' => APP_URL . '/staff'],
                ['label' => 'Import'],
            ],
        ]);
    }

    // ─── Download CSV Template ──────────────────
    public function downloadTemplate()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $headers = [
            'full_name', 'staff_category', 'user_type', 'department', 'designation',
            'employee_id', 'phone', 'email', 'gender', 'date_of_birth',
            'qualification', 'experience_years', 'date_of_joining', 'salary',
            'address', 'city', 'state', 'pincode', 'blood_group', 'emergency_contact'
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="staff_import_template.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel
        fputcsv($output, $headers);
        // Example row
        fputcsv($output, [
            'Rajesh Kumar', 'teaching', 'teacher', 'Mathematics', 'PGT',
            'EMP001', '9876543210', 'rajesh@school.com', 'male', '1985-03-15',
            'M.Sc, B.Ed', '10', '2020-06-01', '35000',
            '123 Main Road', 'Delhi', 'Delhi', '110001', 'B+', '9876543211'
        ]);
        fputcsv($output, [
            'Priya Sharma', 'non_teaching', 'staff', 'Administration', 'Office Clerk',
            'EMP002', '9876543220', '', 'female', '1990-07-20',
            '12th Pass', '5', '2022-01-10', '18000',
            '456 Park Street', 'Delhi', 'Delhi', '110002', 'A+', '9876543221'
        ]);
        fclose($output);
        exit;
    }

    // ─── Process Import ─────────────────────────
    public function processImport()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please upload a valid CSV file.');
            Response::redirect('staff/import');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Could not read file.');
            Response::redirect('staff/import');
            return;
        }

        // Skip BOM
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) { rewind($handle); }

        $headers = fgetcsv($handle);
        if (!$headers) {
            Session::flash('error', 'Empty file.');
            Response::redirect('staff/import');
            return;
        }
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        // Load department and designation maps
        $deptRows = Database::fetchAll("SELECT id, name FROM departments WHERE school_id = ?", [$schoolId]);
        $deptMap = [];
        foreach ($deptRows as $d) { $deptMap[strtolower(trim($d['name']))] = $d['id']; }

        $desigRows = Database::fetchAll("SELECT id, name FROM designations WHERE school_id = ?", [$schoolId]);
        $desigMap = [];
        foreach ($desigRows as $d) { $desigMap[strtolower(trim($d['name']))] = $d['id']; }

        $result = ['success' => 0, 'errors' => [], 'total' => 0];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $result['total']++;

            $data = [];
            foreach ($headers as $i => $h) {
                $data[$h] = isset($row[$i]) ? trim($row[$i]) : '';
            }

            $name = $data['full_name'] ?? '';
            if (empty($name)) {
                $result['errors'][] = ['row' => $rowNum, 'name' => '', 'error' => 'Full name is required'];
                continue;
            }

            try {
                Database::beginTransaction();

                $userType = $data['user_type'] ?? 'teacher';
                if (!in_array($userType, ['teacher','staff','accountant','librarian','transport_manager'])) {
                    $userType = 'teacher';
                }

                $staffCategory = $data['staff_category'] ?? 'teaching';
                if (!in_array($staffCategory, ['teaching','non_teaching'])) {
                    $staffCategory = 'teaching';
                }

                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(100, 999);
                $email = !empty($data['email']) ? $data['email'] : null;
                $password = 'staff@123';

                $userId = Database::insert('users', [
                    'school_id'     => $schoolId,
                    'username'      => $username,
                    'email'         => $email,
                    'password'      => password_hash($password, PASSWORD_DEFAULT),
                    'full_name'     => $name,
                    'phone'         => !empty($data['phone']) ? $data['phone'] : null,
                    'gender'        => !empty($data['gender']) ? strtolower($data['gender']) : null,
                    'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                    'user_type'     => $userType,
                    'is_active'     => 1,
                    'created_by'    => Session::userId(),
                ]);

                // Resolve department_id and designation_id by name
                $deptId = null;
                $deptName = strtolower($data['department'] ?? '');
                if (!empty($deptName) && isset($deptMap[$deptName])) {
                    $deptId = $deptMap[$deptName];
                }

                $desigId = null;
                $desigName = strtolower($data['designation'] ?? '');
                if (!empty($desigName) && isset($desigMap[$desigName])) {
                    $desigId = $desigMap[$desigName];
                }

                Database::insert('staff_details', [
                    'user_id'          => $userId,
                    'school_id'        => $schoolId,
                    'employee_id'      => !empty($data['employee_id']) ? $data['employee_id'] : $this->generateEmployeeId($schoolId),
                    'department_id'    => $deptId,
                    'designation_id'   => $desigId,
                    'staff_category'   => $staffCategory,
                    'department'       => !empty($data['department']) ? $data['department'] : null,
                    'designation'      => !empty($data['designation']) ? $data['designation'] : null,
                    'qualification'    => !empty($data['qualification']) ? $data['qualification'] : null,
                    'experience_years' => !empty($data['experience_years']) ? (int)$data['experience_years'] : 0,
                    'date_of_joining'  => !empty($data['date_of_joining']) ? $data['date_of_joining'] : null,
                    'salary'           => !empty($data['salary']) ? $data['salary'] : null,
                    'address'          => !empty($data['address']) ? $data['address'] : null,
                    'city'             => !empty($data['city']) ? $data['city'] : null,
                    'state'            => !empty($data['state']) ? $data['state'] : null,
                    'pincode'          => !empty($data['pincode']) ? $data['pincode'] : null,
                    'emergency_contact'=> !empty($data['emergency_contact']) ? $data['emergency_contact'] : null,
                    'blood_group'      => !empty($data['blood_group']) ? $data['blood_group'] : null,
                ]);

                // Assign role
                $role = Database::fetch("SELECT id FROM roles WHERE slug = ?", [$userType]);
                if ($role) {
                    Database::insert('user_roles', ['user_id' => $userId, 'role_id' => $role['id']]);
                }

                Database::commit();
                $result['success']++;

            } catch (\Exception $e) {
                Database::rollback();
                $result['errors'][] = ['row' => $rowNum, 'name' => $name, 'error' => $e->getMessage()];
            }
        }

        fclose($handle);

        Response::view('staff/import', [
            'pageTitle'    => 'Import Staff',
            'importResult' => $result,
            'breadcrumbs'  => [
                ['label' => 'Staff', 'url' => APP_URL . '/staff'],
                ['label' => 'Import Results'],
            ],
        ]);
    }
}
