<?php
/**
 * Student Controller
 * Student CRUD, enrollment, and listing
 */
require_once __DIR__ . '/SchoolSetupController.php';

class StudentController
{
    private function getSchoolId()
    {
        $user = Session::user();
        return $user['school_id'] ?? null;
    }

    private function getCurrentAcademicYear(int $schoolId)
    {
        return Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1",
            [$schoolId]
        );
    }

    private function getSchoolSetting(int $schoolId, string $key, $default = null)
    {
        $row = Database::fetch(
            "SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = ?",
            [$schoolId, $key]
        );
        return $row ? $row['setting_value'] : $default;
    }

    private function generateAdmissionNo(int $schoolId): string
    {
        $prefix = $this->getSchoolSetting($schoolId, 'admission_prefix', 'ADM');
        $format = $this->getSchoolSetting($schoolId, 'admission_format', '{PREFIX}-{YEAR}-{SEQ}');
        $includeYear = $this->getSchoolSetting($schoolId, 'admission_include_year', '1');
        $startNumber = max(1, intval($this->getSchoolSetting($schoolId, 'admission_start_number', '1')));
        $year = date('Y');

        // Find last seq number
        $last = Database::fetch(
            "SELECT admission_no FROM student_details WHERE school_id = ? ORDER BY id DESC LIMIT 1",
            [$schoolId]
        );

        $nextSeq = $startNumber;
        if ($last) {
            // Extract last digits from admission number
            preg_match('/(\d{3,})$/', $last['admission_no'], $matches);
            if (!empty($matches[1])) {
                $nextSeq = (int)$matches[1] + 1;
            }
        }

        $admNo = $format;
        $admNo = str_replace('{PREFIX}', $prefix, $admNo);

        if ($includeYear === '1') {
            $admNo = str_replace('{YEAR}', $year, $admNo);
        } else {
            // Remove year and its separator
            $admNo = str_replace('-{YEAR}', '', $admNo);
            $admNo = str_replace('/{YEAR}', '', $admNo);
            $admNo = str_replace('{YEAR}', '', $admNo);
        }

        $admNo = str_replace('{SEQ}', sprintf('%04d', $nextSeq), $admNo);

        return $admNo;
    }

    private function loadFieldConfig(int $schoolId): array
    {
        $configs = Database::fetchAll(
            "SELECT * FROM form_field_config WHERE school_id = ? AND form_type = 'student_admission'",
            [$schoolId]
        );
        $map = [];
        foreach ($configs as $c) {
            $map[$c['field_name']] = $c;
        }
        return $map;
    }

    private function loadCustomFields(int $schoolId): array
    {
        return Database::fetchAll(
            "SELECT * FROM custom_fields WHERE school_id = ? AND form_type = 'student_admission' AND is_active = 1 ORDER BY display_order ASC",
            [$schoolId]
        );
    }

    // ─── List Students ───────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Filters
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $query = "SELECT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.avatar, u.is_active, u.username,
                         sd.admission_no, sd.roll_number, sd.status as student_status, sd.admission_date,
                         sd.father_name, sd.father_phone,
                         c.name as class_name, s.name as section_name
                  FROM users u
                  JOIN student_details sd ON u.id = sd.user_id
                  LEFT JOIN classes c ON sd.class_id = c.id
                  LEFT JOIN sections s ON sd.section_id = s.id
                  WHERE u.school_id = ? AND u.user_type = 'student' AND sd.academic_year_id = ?";
        $params = [$schoolId, $yearId];

        if (!empty($classId)) {
            $query .= " AND sd.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($sectionId)) {
            $query .= " AND sd.section_id = ?";
            $params[] = $sectionId;
        }

        if (!empty($search)) {
            $query .= " AND (u.full_name LIKE ? OR sd.admission_no LIKE ? OR u.phone LIKE ? OR sd.father_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status !== '') {
            $query .= " AND sd.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY c.numeric_name ASC, s.name ASC, sd.roll_number ASC, u.full_name ASC";

        // Pagination
        $perPage = 25;
        $page = max(1, intval($_GET['page'] ?? 1));

        // Count total
        $countQuery = "SELECT COUNT(*) as total FROM users u 
                       JOIN student_details sd ON u.id = sd.user_id
                       LEFT JOIN classes c ON sd.class_id = c.id
                       LEFT JOIN sections s ON sd.section_id = s.id
                       WHERE u.school_id = ? AND u.user_type = 'student' AND sd.academic_year_id = ?";
        $countParams = [$schoolId, $yearId];

        if (!empty($classId)) { $countQuery .= " AND sd.class_id = ?"; $countParams[] = $classId; }
        if (!empty($sectionId)) { $countQuery .= " AND sd.section_id = ?"; $countParams[] = $sectionId; }
        if (!empty($search)) {
            $countQuery .= " AND (u.full_name LIKE ? OR sd.admission_no LIKE ? OR u.phone LIKE ? OR sd.father_name LIKE ?)";
            $countParams[] = "%{$search}%"; $countParams[] = "%{$search}%";
            $countParams[] = "%{$search}%"; $countParams[] = "%{$search}%";
        }
        if ($status !== '') { $countQuery .= " AND sd.status = ?"; $countParams[] = $status; }

        $totalStudents = (int)(Database::fetch($countQuery, $countParams)['total'] ?? 0);
        $totalPages = max(1, ceil($totalStudents / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        $students = Database::fetchAll($query, $params);

        // Get classes & sections for filters
        $classes = Database::fetchAll(
            "SELECT id, name FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name",
            [$schoolId, $yearId]
        );

        $sections = [];
        if (!empty($classId)) {
            $sections = Database::fetchAll(
                "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name",
                [$classId]
            );
        }

        Response::view('students/index', [
            'pageTitle'     => 'Students',
            'students'      => $students,
            'classes'       => $classes,
            'sections'      => $sections,
            'classId'       => $classId,
            'sectionId'     => $sectionId,
            'search'        => $search,
            'status'        => $status,
            'currentYear'   => $currentYear,
            'totalStudents' => $totalStudents,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'perPage'       => $perPage,
            'breadcrumbs'   => [['label' => 'Students']],
        ]);
    }

    // ─── Create Student Form ─────────────────────
    public function create()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        if (!$currentYear) {
            Session::flash('error', 'Please set up an academic year first.');
            Response::redirect('school-setup');
            return;
        }

        $classes = Database::fetchAll(
            "SELECT c.id, c.name, 
                    (SELECT GROUP_CONCAT(CONCAT(s.id, ':', s.name) SEPARATOR '|') FROM sections s WHERE s.class_id = c.id) as sections_data
             FROM classes c 
             WHERE c.school_id = ? AND c.academic_year_id = ? 
             ORDER BY c.numeric_name",
            [$schoolId, $currentYear['id']]
        );

        $admissionNo = $this->generateAdmissionNo($schoolId);
        $fieldConfigMap = $this->loadFieldConfig($schoolId);
        $customFields = $this->loadCustomFields($schoolId);

        Response::view('students/form', [
            'pageTitle'      => 'Add Student',
            'student'        => null,
            'classes'        => $classes,
            'admissionNo'    => $admissionNo,
            'currentYear'    => $currentYear,
            'fieldConfigMap' => $fieldConfigMap,
            'customFields'   => $customFields,
            'customValues'   => [],
            'breadcrumbs'    => [
                ['label' => 'Students', 'url' => APP_URL . '/students'],
                ['label' => 'Add Student'],
            ],
        ]);
    }

    // ─── Store Student ───────────────────────────
    public function store()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);

        $data = $_POST;

        // Validate required
        if (empty($data['full_name']) || empty($data['admission_no'])) {
            Session::flash('error', 'Student name and admission number are required.');
            Response::back();
            return;
        }

        // Generate username
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['full_name'])) . rand(100, 999);
        $email = $data['email'] ?? ($username . '@student.classoragen.local');
        $password = $data['password'] ?? 'student@123';

        try {
            Database::beginTransaction();

            // Create user record
            $userId = Database::insert('users', [
                'school_id'   => $schoolId,
                'username'    => $username,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'full_name'   => $data['full_name'],
                'phone'       => $data['phone'] ?? null,
                'gender'      => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?: null,
                'user_type'   => 'student',
                'is_active'   => 1,
                'created_by'  => Session::userId(),
            ]);

            // Create student details
            Database::insert('student_details', [
                'user_id'           => $userId,
                'school_id'         => $schoolId,
                'admission_no'      => $data['admission_no'],
                'admission_date'    => $data['admission_date'] ?: date('Y-m-d'),
                'class_id'          => $data['class_id'] ?: null,
                'section_id'        => $data['section_id'] ?: null,
                'academic_year_id'  => $currentYear['id'],
                'roll_number'       => $data['roll_number'] ?? null,
                'blood_group'       => $data['blood_group'] ?: null,
                'religion'          => $data['religion'] ?? null,
                'category'          => $data['category'] ?? 'general',
                'nationality'       => $data['nationality'] ?? 'Indian',
                'address'           => $data['address'] ?? null,
                'city'              => $data['city'] ?? null,
                'state'             => $data['state'] ?? null,
                'pincode'           => $data['pincode'] ?? null,
                'father_name'       => $data['father_name'] ?? null,
                'father_phone'      => $data['father_phone'] ?? null,
                'father_occupation' => $data['father_occupation'] ?? null,
                'mother_name'       => $data['mother_name'] ?? null,
                'mother_phone'      => $data['mother_phone'] ?? null,
                'mother_occupation' => $data['mother_occupation'] ?? null,
                'guardian_name'     => $data['guardian_name'] ?? null,
                'guardian_phone'    => $data['guardian_phone'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'medical_conditions'=> $data['medical_conditions'] ?? null,
                'previous_school'   => $data['previous_school'] ?? null,
                'status'            => 'active',
            ]);

            // Assign student role
            $studentRole = Database::fetch("SELECT id FROM roles WHERE slug = 'student'");
            if ($studentRole) {
                Database::insert('user_roles', [
                    'user_id' => $userId,
                    'role_id' => $studentRole['id'],
                ]);
            }

            // Save custom field values
            $customFields = $this->loadCustomFields($schoolId);
            foreach ($customFields as $cf) {
                $val = $data['cf_' . $cf['id']] ?? null;
                if ($val !== null && $val !== '') {
                    Database::insert('custom_field_values', [
                        'custom_field_id' => $cf['id'],
                        'entity_type'     => 'student',
                        'entity_id'       => $userId,
                        'field_value'     => $val,
                    ]);
                }
            }

            Database::commit();
            Session::flash('success', "Student '{$data['full_name']}' admitted successfully. Username: {$username}, Password: {$password}");
            Response::redirect('students');

        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
            Response::back();
        }
    }

    // ─── Edit Student Form ───────────────────────
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $student = Database::fetch(
            "SELECT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.avatar, u.username, u.is_active,
                    sd.admission_no, sd.admission_date, sd.class_id, sd.section_id, sd.roll_number,
                    sd.blood_group, sd.religion, sd.category, sd.nationality, sd.address, sd.city, sd.state, sd.pincode,
                    sd.father_name, sd.father_phone, sd.father_occupation,
                    sd.mother_name, sd.mother_phone, sd.mother_occupation,
                    sd.guardian_name, sd.guardian_phone, sd.guardian_relation,
                    sd.emergency_contact, sd.medical_conditions, sd.previous_school,
                    sd.academic_year_id, sd.status as student_status
             FROM users u 
             JOIN student_details sd ON u.id = sd.user_id 
             WHERE u.id = ? AND u.school_id = ?",
            [$id, $schoolId]
        );

        if (!$student) { Response::abort(404); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $student['academic_year_id'] ?? $currentYear['id'] ?? 0;

        $classes = Database::fetchAll(
            "SELECT c.id, c.name, 
                    (SELECT GROUP_CONCAT(CONCAT(s.id, ':', s.name) SEPARATOR '|') FROM sections s WHERE s.class_id = c.id) as sections_data
             FROM classes c 
             WHERE c.school_id = ? AND c.academic_year_id = ? 
             ORDER BY c.numeric_name",
            [$schoolId, $yearId]
        );

        $fieldConfigMap = $this->loadFieldConfig($schoolId);
        $customFields = $this->loadCustomFields($schoolId);

        // Load existing custom field values
        $customValRows = Database::fetchAll(
            "SELECT custom_field_id, field_value FROM custom_field_values WHERE entity_type = 'student' AND entity_id = ?",
            [$id]
        );
        $customValues = [];
        foreach ($customValRows as $cv) {
            $customValues[$cv['custom_field_id']] = $cv['field_value'];
        }

        Response::view('students/form', [
            'pageTitle'      => 'Edit Student',
            'student'        => $student,
            'classes'        => $classes,
            'admissionNo'    => $student['admission_no'],
            'currentYear'    => $currentYear,
            'fieldConfigMap' => $fieldConfigMap,
            'customFields'   => $customFields,
            'customValues'   => $customValues,
            'breadcrumbs'    => [
                ['label' => 'Students', 'url' => APP_URL . '/students'],
                ['label' => 'Edit: ' . $student['full_name']],
            ],
        ]);
    }

    // ─── Update Student ──────────────────────────
    public function update($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        try {
            Database::beginTransaction();

            // Update user record
            Database::update('users', [
                'full_name'     => $data['full_name'],
                'phone'         => $data['phone'] ?? null,
                'gender'        => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?: null,
                'email'         => !empty($data['email']) ? $data['email'] : null,
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);

            // Update student details
            Database::update('student_details', [
                'class_id'          => $data['class_id'] ?: null,
                'section_id'        => $data['section_id'] ?: null,
                'roll_number'       => $data['roll_number'] ?? null,
                'blood_group'       => $data['blood_group'] ?: null,
                'religion'          => $data['religion'] ?? null,
                'category'          => $data['category'] ?? 'general',
                'address'           => $data['address'] ?? null,
                'city'              => $data['city'] ?? null,
                'state'             => $data['state'] ?? null,
                'pincode'           => $data['pincode'] ?? null,
                'father_name'       => $data['father_name'] ?? null,
                'father_phone'      => $data['father_phone'] ?? null,
                'father_occupation' => $data['father_occupation'] ?? null,
                'mother_name'       => $data['mother_name'] ?? null,
                'mother_phone'      => $data['mother_phone'] ?? null,
                'mother_occupation' => $data['mother_occupation'] ?? null,
                'guardian_name'     => $data['guardian_name'] ?? null,
                'guardian_phone'    => $data['guardian_phone'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'medical_conditions'=> $data['medical_conditions'] ?? null,
                'previous_school'   => $data['previous_school'] ?? null,
            ], 'user_id = ? AND school_id = ?', [$id, $schoolId]);

            // Save custom field values
            $customFields = $this->loadCustomFields($schoolId);
            foreach ($customFields as $cf) {
                $val = $data['cf_' . $cf['id']] ?? null;
                $existing = Database::fetch(
                    "SELECT id FROM custom_field_values WHERE custom_field_id = ? AND entity_type = 'student' AND entity_id = ?",
                    [$cf['id'], $id]
                );
                if ($existing) {
                    Database::update('custom_field_values', ['field_value' => $val], 'id = ?', [$existing['id']]);
                } elseif ($val !== null && $val !== '') {
                    Database::insert('custom_field_values', [
                        'custom_field_id' => $cf['id'],
                        'entity_type'     => 'student',
                        'entity_id'       => $id,
                        'field_value'     => $val,
                    ]);
                }
            }

            Database::commit();
            Session::flash('success', 'Student updated successfully.');
            Response::redirect('students');

        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
            Response::back();
        }
    }

    // ─── Delete Student ──────────────────────────
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM users WHERE id = ? AND school_id = ? AND user_type = 'student'")->execute([$id, $schoolId]);
        Session::flash('success', 'Student deleted.');
        Response::redirect('students');
    }

    // ─── Get Sections API (AJAX) ─────────────────
    public function getSections($classId)
    {
        $sections = Database::fetchAll(
            "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name",
            [$classId]
        );
        Response::json(['sections' => $sections]);
    }

    // ─── Import Form ────────────────────────────
    public function import()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        Response::view('students/import', [
            'pageTitle'    => 'Import Students',
            'importResult' => null,
            'breadcrumbs'  => [
                ['label' => 'Students', 'url' => APP_URL . '/students'],
                ['label' => 'Import'],
            ],
        ]);
    }

    // ─── Download CSV Template ──────────────────
    public function downloadTemplate()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        // Build headers from visible base fields
        $fieldConfigMap = $this->loadFieldConfig($schoolId);
        $baseFields = SchoolSetupController::getBaseFields();
        $customFields = $this->loadCustomFields($schoolId);

        $headers = [];
        foreach ($baseFields as $f) {
            // Skip locked-hidden or actually hidden fields
            if (!$f['locked']) {
                $config = $fieldConfigMap[$f['name']] ?? null;
                if ($config && $config['visibility'] === 'hide') continue;
            }
            // Map to CSV-friendly names
            if ($f['name'] === 'class_id') { $headers[] = 'class_name'; continue; }
            if ($f['name'] === 'section_id') { $headers[] = 'section_name'; continue; }
            $headers[] = $f['name'];
        }

        // Add custom fields
        foreach ($customFields as $cf) {
            $headers[] = $cf['field_label'];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_import_template.csv"');
        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        // Add one example row
        $example = [];
        foreach ($headers as $h) {
            switch ($h) {
                case 'admission_no': $example[] = ''; break;
                case 'full_name': $example[] = 'Rahul Sharma'; break;
                case 'gender': $example[] = 'male'; break;
                case 'date_of_birth': $example[] = '2015-06-15'; break;
                case 'class_name': $example[] = 'Class 5'; break;
                case 'section_name': $example[] = 'A'; break;
                case 'phone': $example[] = '9876543210'; break;
                case 'father_name': $example[] = 'Suresh Sharma'; break;
                case 'father_phone': $example[] = '9876543211'; break;
                case 'nationality': $example[] = 'Indian'; break;
                case 'admission_date': $example[] = date('Y-m-d'); break;
                default: $example[] = ''; break;
            }
        }
        fputcsv($output, $example);
        fclose($output);
        exit;
    }

    // ─── Process Import ─────────────────────────
    public function processImport()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        if (!$currentYear) {
            Session::flash('error', 'Please set up an academic year first.');
            Response::redirect('students/import');
            return;
        }

        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please upload a valid CSV file.');
            Response::redirect('students/import');
            return;
        }

        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Could not read file.');
            Response::redirect('students/import');
            return;
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            Session::flash('error', 'Empty file or invalid CSV format.');
            Response::redirect('students/import');
            return;
        }

        // Clean headers
        $headers = array_map(function($h) { return strtolower(trim($h)); }, $headers);

        // Load classes and sections for name→ID mapping
        $classRows = Database::fetchAll(
            "SELECT id, name FROM classes WHERE school_id = ? AND academic_year_id = ?",
            [$schoolId, $currentYear['id']]
        );
        $classMap = [];
        foreach ($classRows as $c) {
            $classMap[strtolower(trim($c['name']))] = $c['id'];
        }

        $sectionRows = Database::fetchAll(
            "SELECT s.id, s.name, s.class_id FROM sections s JOIN classes c ON s.class_id = c.id WHERE c.school_id = ?",
            [$schoolId]
        );
        $sectionMap = []; // class_id => [name => id]
        foreach ($sectionRows as $s) {
            $sectionMap[$s['class_id']][strtolower(trim($s['name']))] = $s['id'];
        }

        // Load custom fields
        $customFields = $this->loadCustomFields($schoolId);
        $cfLabelMap = []; // lowercase label => cf row
        foreach ($customFields as $cf) {
            $cfLabelMap[strtolower(trim($cf['field_label']))] = $cf;
        }

        $result = ['success' => 0, 'errors' => [], 'total' => 0];
        $rowNum = 1; // header was row 1

        // Get student role
        $studentRole = Database::fetch("SELECT id FROM roles WHERE slug = 'student'");

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $result['total']++;

            // Build data from headers
            $data = [];
            foreach ($headers as $i => $h) {
                $data[$h] = isset($row[$i]) ? trim($row[$i]) : '';
            }

            // Validate required
            $name = $data['full_name'] ?? '';
            if (empty($name)) {
                $result['errors'][] = ['row' => $rowNum, 'name' => '', 'error' => 'Full name is required'];
                continue;
            }

            try {
                Database::beginTransaction();

                // Resolve class_id
                $classId = null;
                $className = strtolower($data['class_name'] ?? '');
                if (!empty($className) && isset($classMap[$className])) {
                    $classId = $classMap[$className];
                }

                // Resolve section_id
                $sectionId = null;
                $sectionName = strtolower($data['section_name'] ?? '');
                if ($classId && !empty($sectionName) && isset($sectionMap[$classId][$sectionName])) {
                    $sectionId = $sectionMap[$classId][$sectionName];
                }

                // Generate admission no if empty
                $admNo = $data['admission_no'] ?? '';
                if (empty($admNo)) {
                    $admNo = $this->generateAdmissionNo($schoolId);
                }

                // Generate username
                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(100, 999);
                $email = $data['email'] ?? ($username . '@student.classoragen.local');
                $password = 'student@123';

                // Create user
                $userId = Database::insert('users', [
                    'school_id'     => $schoolId,
                    'username'      => $username,
                    'email'         => $email,
                    'password'      => password_hash($password, PASSWORD_DEFAULT),
                    'full_name'     => $name,
                    'phone'         => !empty($data['phone']) ? $data['phone'] : null,
                    'gender'        => !empty($data['gender']) ? strtolower($data['gender']) : null,
                    'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                    'user_type'     => 'student',
                    'is_active'     => 1,
                    'created_by'    => Session::userId(),
                ]);

                // Helper: empty string → null
                $n = function($key, $default = null) use ($data) {
                    $v = $data[$key] ?? '';
                    return ($v !== '' && $v !== null) ? $v : $default;
                };

                // Create student details
                Database::insert('student_details', [
                    'user_id'           => $userId,
                    'school_id'         => $schoolId,
                    'admission_no'      => $admNo,
                    'admission_date'    => $n('admission_date', date('Y-m-d')),
                    'class_id'          => $classId,
                    'section_id'        => $sectionId,
                    'academic_year_id'  => $currentYear['id'],
                    'roll_number'       => $n('roll_number'),
                    'blood_group'       => $n('blood_group'),
                    'religion'          => $n('religion'),
                    'category'          => $n('category', 'general'),
                    'nationality'       => $n('nationality', 'Indian'),
                    'address'           => $n('address'),
                    'city'              => $n('city'),
                    'state'             => $n('state'),
                    'pincode'           => $n('pincode'),
                    'father_name'       => $n('father_name'),
                    'father_phone'      => $n('father_phone'),
                    'father_occupation' => $n('father_occupation'),
                    'mother_name'       => $n('mother_name'),
                    'mother_phone'      => $n('mother_phone'),
                    'mother_occupation' => $n('mother_occupation'),
                    'guardian_name'     => $n('guardian_name'),
                    'guardian_phone'    => $n('guardian_phone'),
                    'guardian_relation' => $n('guardian_relation'),
                    'emergency_contact' => $n('emergency_contact'),
                    'medical_conditions'=> $n('medical_conditions'),
                    'previous_school'   => $n('previous_school'),
                    'status'            => 'active',
                ]);

                // Assign student role
                if ($studentRole) {
                    Database::insert('user_roles', [
                        'user_id' => $userId,
                        'role_id' => $studentRole['id'],
                    ]);
                }

                // Save custom field values
                foreach ($customFields as $cf) {
                    $cfHeader = strtolower(trim($cf['field_label']));
                    $cfVal = $data[$cfHeader] ?? '';
                    if (!empty($cfVal)) {
                        Database::insert('custom_field_values', [
                            'custom_field_id' => $cf['id'],
                            'entity_type'     => 'student',
                            'entity_id'       => $userId,
                            'field_value'     => $cfVal,
                        ]);
                    }
                }

                Database::commit();
                $result['success']++;

            } catch (\Exception $e) {
                Database::rollback();
                $result['errors'][] = ['row' => $rowNum, 'name' => $name, 'error' => $e->getMessage()];
            }
        }

        fclose($handle);

        if ($result['success'] > 0) {
            Session::flash('success', "{$result['success']} student(s) imported successfully!");
        }

        Response::view('students/import', [
            'pageTitle'    => 'Import Results',
            'importResult' => $result,
            'breadcrumbs'  => [
                ['label' => 'Students', 'url' => APP_URL . '/students'],
                ['label' => 'Import Results'],
            ],
        ]);
    }
}
