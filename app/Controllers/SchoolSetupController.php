<?php
/**
 * School Setup Controller
 * Academic Years, Classes, Sections, Subjects
 */

class SchoolSetupController
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

    // ─── Setup Dashboard ─────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $stats = [
            'academic_years' => Database::count('academic_years', 'school_id = ?', [$schoolId]),
            'classes' => Database::count('classes', 'school_id = ? AND academic_year_id = ?', [$schoolId, $yearId]),
            'sections' => Database::count('sections', 'school_id = ? AND class_id IN (SELECT id FROM classes WHERE academic_year_id = ?)', [$schoolId, $yearId]),
            'subjects' => Database::count('subjects', 'school_id = ?', [$schoolId]),
        ];

        Response::view('school-setup/index', [
            'pageTitle' => 'School Setup',
            'stats' => $stats,
            'currentYear' => $currentYear,
            'breadcrumbs' => [['label' => 'School Setup']],
        ]);
    }

    // ═══════════════════════════════════════════════
    // GENERAL SETTINGS
    // ═══════════════════════════════════════════════

    public function general()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currency = $this->getSchoolSetting($schoolId, 'currency', '$');
        
        Response::view('school-setup/general', [
            'pageTitle' => 'General Settings',
            'currency' => $currency,
            'breadcrumbs' => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'General Settings']
            ]
        ]);
    }

    public function saveGeneral()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currency = trim($_POST['currency'] ?? '$');
            $this->setSchoolSetting($schoolId, 'currency', $currency);
            
            Session::flash('success', 'General settings saved successfully.');
        }
        
        Response::redirect('school-setup/general');
    }

    // ═══════════════════════════════════════════════
    // ACADEMIC YEARS
    // ═══════════════════════════════════════════════

    public function academicYears()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $years = Database::fetchAll(
            "SELECT ay.*, 
                    (SELECT COUNT(*) FROM classes WHERE academic_year_id = ay.id) as class_count
             FROM academic_years ay 
             WHERE ay.school_id = ? 
             ORDER BY ay.start_date DESC",
            [$schoolId]
        );

        Response::view('school-setup/academic-years', [
            'pageTitle' => 'Academic Years',
            'years' => $years,
            'breadcrumbs' => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Academic Years'],
            ],
        ]);
    }

    public function storeAcademicYear()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $name = trim($_POST['name'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;

        if (empty($name) || empty($startDate) || empty($endDate)) {
            Session::flash('error', 'All fields are required.');
            Response::back();
            return;
        }

        try {
            Database::beginTransaction();

            // If setting as current, unset others
            if ($isCurrent) {
                Database::pdo()->prepare("UPDATE academic_years SET is_current = 0 WHERE school_id = ?")->execute([$schoolId]);
            }

            Database::insert('academic_years', [
                'school_id'  => $schoolId,
                'name'       => $name,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'is_current' => $isCurrent,
                'status'     => 'active',
            ]);

            Database::commit();
            Session::flash('success', "Academic year '{$name}' created successfully.");
        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
        }

        Response::redirect('school-setup/academic-years');
    }

    public function deleteAcademicYear($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM academic_years WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Academic year deleted.');
        Response::redirect('school-setup/academic-years');
    }

    public function setCurrentYear($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("UPDATE academic_years SET is_current = 0 WHERE school_id = ?")->execute([$schoolId]);
        Database::pdo()->prepare("UPDATE academic_years SET is_current = 1 WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Current academic year updated.');
        Response::redirect('school-setup/academic-years');
    }

    // ═══════════════════════════════════════════════
    // CLASSES & SECTIONS
    // ═══════════════════════════════════════════════

    public function classes()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        // Get all academic years for dropdown
        $allYears = Database::fetchAll(
            "SELECT * FROM academic_years WHERE school_id = ? ORDER BY start_date DESC",
            [$schoolId]
        );

        if (empty($allYears)) {
            Session::flash('error', 'Please create an academic year first.');
            Response::redirect('school-setup/academic-years');
            return;
        }

        // Use selected year from query param, or fallback to current year
        $selectedYearId = $_GET['year_id'] ?? null;
        $selectedYear = null;

        if ($selectedYearId) {
            foreach ($allYears as $y) {
                if ($y['id'] == $selectedYearId) { $selectedYear = $y; break; }
            }
        }

        // Fallback to current year
        if (!$selectedYear) {
            foreach ($allYears as $y) {
                if ($y['is_current']) { $selectedYear = $y; break; }
            }
        }

        // Final fallback: use the first year
        if (!$selectedYear) {
            $selectedYear = $allYears[0];
        }

        $classes = Database::fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM sections WHERE class_id = c.id) as section_count,
                    (SELECT COUNT(*) FROM class_subjects WHERE class_id = c.id) as subject_count
             FROM classes c
             WHERE c.school_id = ? AND c.academic_year_id = ?
             ORDER BY c.numeric_name ASC, c.display_order ASC",
            [$schoolId, $selectedYear['id']]
        );

        // Get sections for each class
        foreach ($classes as &$class) {
            $class['sections'] = Database::fetchAll(
                "SELECT s.*, u.full_name as teacher_name 
                 FROM sections s 
                 LEFT JOIN users u ON s.class_teacher_id = u.id 
                 WHERE s.class_id = ? ORDER BY s.name ASC",
                [$class['id']]
            );
        }

        // Get all teachers for assignment
        $teachers = Database::fetchAll(
            "SELECT id, full_name FROM users WHERE school_id = ? AND user_type = 'teacher' AND is_active = 1 ORDER BY full_name ASC",
            [$schoolId]
        );

        Response::view('school-setup/classes', [
            'pageTitle' => 'Classes & Sections',
            'classes' => $classes,
            'currentYear' => $selectedYear,
            'allYears' => $allYears,
            'teachers' => $teachers,
            'breadcrumbs' => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Classes & Sections'],
            ],
        ]);
    }

    public function storeClass()
    {
        $schoolId = $this->getSchoolId();
        $yearId = $_POST['year_id'] ?? null;
        if (!$yearId) {
            $currentYear = $this->getCurrentAcademicYear($schoolId);
            $yearId = $currentYear['id'] ?? null;
        }
        if (!$yearId) { Response::back(); return; }

        $name = trim($_POST['name'] ?? '');
        $numericName = (int)($_POST['numeric_name'] ?? 0);

        if (empty($name)) {
            Session::flash('error', 'Class name is required.');
            Response::back();
            return;
        }

        Database::insert('classes', [
            'school_id'        => $schoolId,
            'academic_year_id' => $yearId,
            'name'             => $name,
            'numeric_name'     => $numericName,
            'display_order'    => $numericName,
        ]);

        Session::flash('success', "Class '{$name}' created.");
        Response::redirect('school-setup/classes?year_id=' . $yearId);
    }

    public function bulkCreateClasses()
    {
        $schoolId = $this->getSchoolId();
        $yearId = $_POST['year_id'] ?? null;
        if (!$yearId) {
            $currentYear = $this->getCurrentAcademicYear($schoolId);
            $yearId = $currentYear['id'] ?? null;
        }
        if (!$yearId) { Response::back(); return; }

        $from = (int)($_POST['from_class'] ?? 1);
        $to = (int)($_POST['to_class'] ?? 12);
        $sections = array_filter(array_map('trim', explode(',', $_POST['sections'] ?? 'A')));
        $capacity = max(1, (int)($_POST['capacity'] ?? 40));

        if ($from > $to || $from < 1 || $to > 12) {
            Session::flash('error', 'Invalid class range.');
            Response::back();
            return;
        }

        $created = 0;
        try {
            Database::beginTransaction();

            for ($i = $from; $i <= $to; $i++) {
                $className = "Class {$i}";

                $existing = Database::fetch(
                    "SELECT id FROM classes WHERE school_id = ? AND academic_year_id = ? AND name = ?",
                    [$schoolId, $yearId, $className]
                );

                if (!$existing) {
                    $classId = Database::insert('classes', [
                        'school_id'        => $schoolId,
                        'academic_year_id' => $yearId,
                        'name'             => $className,
                        'numeric_name'     => $i,
                        'display_order'    => $i,
                    ]);

                    foreach ($sections as $sec) {
                        Database::insert('sections', [
                            'school_id' => $schoolId,
                            'class_id'  => $classId,
                            'name'      => strtoupper($sec),
                            'capacity'  => $capacity,
                        ]);
                    }

                    $created++;
                }
            }

            Database::commit();
            Session::flash('success', "{$created} classes created with sections: " . implode(', ', $sections));
        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
        }

        Response::redirect('school-setup/classes?year_id=' . $yearId);
    }

    public function deleteClass($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM classes WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Class deleted.');
        Response::redirect('school-setup/classes');
    }

    public function storeSection()
    {
        $schoolId = $this->getSchoolId();
        $classId = $_POST['class_id'] ?? null;
        $name = trim(strtoupper($_POST['name'] ?? ''));
        $capacity = (int)($_POST['capacity'] ?? 40);

        if (!$classId || empty($name)) {
            Session::flash('error', 'Class and section name are required.');
            Response::back();
            return;
        }

        try {
            Database::insert('sections', [
                'school_id' => $schoolId,
                'class_id'  => $classId,
                'name'      => $name,
                'capacity'  => $capacity,
            ]);
            Session::flash('success', "Section '{$name}' added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Section already exists or error occurred.');
        }

        Response::redirect('school-setup/classes');
    }

    public function deleteSection($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM sections WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Section deleted.');
        Response::redirect('school-setup/classes');
    }

    public function assignTeacher($id)
    {
        $schoolId = $this->getSchoolId();
        $teacherId = !empty($_POST['class_teacher_id']) ? (int)$_POST['class_teacher_id'] : null;
        
        $section = Database::fetch("SELECT id FROM sections WHERE id = ? AND school_id = ?", [$id, $schoolId]);
        if (!$section) { Response::abort(404); return; }

        Database::update('sections', ['class_teacher_id' => $teacherId], 'id = ?', [$id]);
        Session::flash('success', 'Class teacher updated.');
        Response::back();
    }

    // ═══════════════════════════════════════════════
    // SUBJECTS
    // ═══════════════════════════════════════════════

    public function subjects()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $subjects = Database::fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM class_subjects WHERE subject_id = s.id) as class_count
             FROM subjects s
             WHERE s.school_id = ?
             ORDER BY s.name ASC",
            [$schoolId]
        );

        // Get classes for assignment
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $classes = [];
        if ($currentYear) {
            $classes = Database::fetchAll(
                "SELECT id, name FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name",
                [$schoolId, $currentYear['id']]
            );
        }

        Response::view('school-setup/subjects', [
            'pageTitle' => 'Subjects',
            'subjects' => $subjects,
            'classes' => $classes,
            'breadcrumbs' => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Subjects'],
            ],
        ]);
    }

    public function storeSubject()
    {
        $schoolId = $this->getSchoolId();
        $name = trim($_POST['name'] ?? '');
        $code = trim(strtoupper($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'theory';
        $isElective = isset($_POST['is_elective']) ? 1 : 0;
        $classIds = $_POST['class_ids'] ?? [];

        if (empty($name)) {
            Session::flash('error', 'Subject name is required.');
            Response::back();
            return;
        }

        try {
            Database::beginTransaction();

            $subjectId = Database::insert('subjects', [
                'school_id'   => $schoolId,
                'name'        => $name,
                'code'        => $code ?: null,
                'type'        => $type,
                'is_elective' => $isElective,
            ]);

            // Assign to classes
            foreach ($classIds as $classId) {
                Database::insert('class_subjects', [
                    'class_id'   => $classId,
                    'subject_id' => $subjectId,
                ]);
            }

            Database::commit();
            Session::flash('success', "Subject '{$name}' created and assigned to " . count($classIds) . " class(es).");
        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
        }

        Response::redirect('school-setup/subjects');
    }

    public function deleteSubject($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM subjects WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Subject deleted.');
        Response::redirect('school-setup/subjects');
    }

    // ═══════════════════════════════════════════════
    // FORM SETTINGS
    // ═══════════════════════════════════════════════

    private function getSchoolSetting(int $schoolId, string $key, $default = null)
    {
        $row = Database::fetch(
            "SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = ?",
            [$schoolId, $key]
        );
        return $row ? $row['setting_value'] : $default;
    }

    private function setSchoolSetting(int $schoolId, string $key, $value)
    {
        $existing = Database::fetch(
            "SELECT id FROM school_settings WHERE school_id = ? AND setting_key = ?",
            [$schoolId, $key]
        );

        if ($existing) {
            Database::update('school_settings', ['setting_value' => $value], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('school_settings', [
                'school_id'     => $schoolId,
                'setting_key'   => $key,
                'setting_value' => $value,
            ]);
        }
    }

    public static function getBaseFields(): array
    {
        return [
            // Basic Info
            ['name' => 'admission_no',    'label' => 'Admission No',      'group' => 'basic',   'locked' => true],
            ['name' => 'admission_date',  'label' => 'Admission Date',    'group' => 'basic',   'locked' => false],
            ['name' => 'roll_number',     'label' => 'Roll Number',       'group' => 'basic',   'locked' => false],
            ['name' => 'full_name',       'label' => 'Full Name',         'group' => 'basic',   'locked' => true],
            ['name' => 'gender',          'label' => 'Gender',            'group' => 'basic',   'locked' => false],
            ['name' => 'date_of_birth',   'label' => 'Date of Birth',     'group' => 'basic',   'locked' => false],
            ['name' => 'class_id',        'label' => 'Class',             'group' => 'basic',   'locked' => false],
            ['name' => 'section_id',      'label' => 'Section',           'group' => 'basic',   'locked' => false],
            ['name' => 'blood_group',     'label' => 'Blood Group',       'group' => 'basic',   'locked' => false],
            ['name' => 'phone',           'label' => 'Phone',             'group' => 'basic',   'locked' => false],
            ['name' => 'email',           'label' => 'Email',             'group' => 'basic',   'locked' => false],
            ['name' => 'category',        'label' => 'Category',          'group' => 'basic',   'locked' => false],
            ['name' => 'religion',        'label' => 'Religion',          'group' => 'basic',   'locked' => false],
            ['name' => 'nationality',     'label' => 'Nationality',       'group' => 'basic',   'locked' => false],
            ['name' => 'previous_school', 'label' => 'Previous School',   'group' => 'basic',   'locked' => false],
            // Parent
            ['name' => 'father_name',       'label' => "Father's Name",       'group' => 'parent', 'locked' => false],
            ['name' => 'father_phone',      'label' => "Father's Phone",      'group' => 'parent', 'locked' => false],
            ['name' => 'father_occupation', 'label' => "Father's Occupation", 'group' => 'parent', 'locked' => false],
            ['name' => 'mother_name',       'label' => "Mother's Name",       'group' => 'parent', 'locked' => false],
            ['name' => 'mother_phone',      'label' => "Mother's Phone",      'group' => 'parent', 'locked' => false],
            ['name' => 'mother_occupation', 'label' => "Mother's Occupation", 'group' => 'parent', 'locked' => false],
            ['name' => 'guardian_name',     'label' => 'Guardian Name',       'group' => 'parent', 'locked' => false],
            ['name' => 'guardian_phone',    'label' => 'Guardian Phone',      'group' => 'parent', 'locked' => false],
            ['name' => 'guardian_relation', 'label' => 'Guardian Relation',   'group' => 'parent', 'locked' => false],
            // Address
            ['name' => 'address',           'label' => 'Address',             'group' => 'address', 'locked' => false],
            ['name' => 'city',              'label' => 'City',                'group' => 'address', 'locked' => false],
            ['name' => 'state',             'label' => 'State',               'group' => 'address', 'locked' => false],
            ['name' => 'pincode',           'label' => 'Pincode',             'group' => 'address', 'locked' => false],
            ['name' => 'emergency_contact', 'label' => 'Emergency Contact',   'group' => 'address', 'locked' => false],
            ['name' => 'medical_conditions','label' => 'Medical Conditions',  'group' => 'address', 'locked' => false],
        ];
    }

    public function formSettings()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        // Load admission settings
        $admissionPrefix = $this->getSchoolSetting($schoolId, 'admission_prefix', 'ADM');
        $admissionFormat = $this->getSchoolSetting($schoolId, 'admission_format', '{PREFIX}-{YEAR}-{SEQ}');
        $includeYear = $this->getSchoolSetting($schoolId, 'admission_include_year', '1');
        $startNumber = $this->getSchoolSetting($schoolId, 'admission_start_number', '1');

        // Load field configs
        $fieldConfigs = Database::fetchAll(
            "SELECT * FROM form_field_config WHERE school_id = ? AND form_type = 'student_admission'",
            [$schoolId]
        );
        $fieldConfigMap = [];
        foreach ($fieldConfigs as $fc) {
            $fieldConfigMap[$fc['field_name']] = $fc;
        }

        // Load custom fields
        $customFields = Database::fetchAll(
            "SELECT * FROM custom_fields WHERE school_id = ? AND form_type = 'student_admission' ORDER BY display_order ASC, id ASC",
            [$schoolId]
        );
        $baseFields = self::getBaseFields();

        // Load attendance settings
        $attendanceType = $this->getSchoolSetting($schoolId, 'attendance_type', 'class');
        $attendanceAccess = json_decode($this->getSchoolSetting($schoolId, 'attendance_access', '["school_admin","teacher"]'), true) ?: ['school_admin','teacher'];
        $attendanceLateAllowed = $this->getSchoolSetting($schoolId, 'attendance_late_allowed', '1');
        $attendanceHalfDay = $this->getSchoolSetting($schoolId, 'attendance_half_day', '0');
        $attendanceExcused = $this->getSchoolSetting($schoolId, 'attendance_excused', '1');
        $allowPastAttendance = $this->getSchoolSetting($schoolId, 'allow_past_attendance', '1');

        // Load employee ID settings
        $employeeIdPrefix = $this->getSchoolSetting($schoolId, 'employee_id_prefix', 'EMP');
        $employeeIdStart = $this->getSchoolSetting($schoolId, 'employee_id_start', '1');
        // Load staff custom fields
        $staffCustomFields = Database::fetchAll(
            "SELECT * FROM custom_fields WHERE school_id = ? AND form_type = 'staff' ORDER BY display_order ASC, id ASC",
            [$schoolId]
        );

        Response::view('school-setup/form-settings', [
            'pageTitle'       => 'Form Settings',
            'admissionPrefix' => $admissionPrefix,
            'admissionFormat' => $admissionFormat,
            'includeYear'     => $includeYear,
            'startNumber'     => $startNumber,
            'employeeIdPrefix'=> $employeeIdPrefix,
            'employeeIdStart' => $employeeIdStart,
            'baseFields'      => $baseFields,
            'fieldConfigMap'  => $fieldConfigMap,
            'customFields'    => $customFields,
            'staffCustomFields'=> $staffCustomFields,
            'attendanceType'  => $attendanceType,
            'attendanceAccess'=> $attendanceAccess,
            'attendanceLateAllowed' => $attendanceLateAllowed,
            'attendanceHalfDay'     => $attendanceHalfDay,
            'attendanceExcused'     => $attendanceExcused,
            'allowPastAttendance'   => $allowPastAttendance,
            'breadcrumbs'     => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Form Settings'],
            ],
        ]);
    }

    public function saveAdmissionSettings()
    {
        $schoolId = $this->getSchoolId();
        $prefix = strtoupper(trim($_POST['admission_prefix'] ?? 'ADM'));
        $format = trim($_POST['admission_format'] ?? '{PREFIX}-{YEAR}-{SEQ}');
        $includeYear = isset($_POST['include_year']) ? '1' : '0';
        $startNumber = max(1, intval($_POST['start_number'] ?? 1));

        $this->setSchoolSetting($schoolId, 'admission_prefix', $prefix);
        $this->setSchoolSetting($schoolId, 'admission_format', $format);
        $this->setSchoolSetting($schoolId, 'admission_include_year', $includeYear);
        $this->setSchoolSetting($schoolId, 'admission_start_number', (string)$startNumber);

        Session::flash('success', 'Admission settings saved.');
        Response::redirect('school-setup/form-settings?tab=admission');
    }

    public function saveEmployeeIdSettings()
    {
        $schoolId = $this->getSchoolId();
        $prefix = strtoupper(trim($_POST['employee_id_prefix'] ?? 'EMP'));
        $startNumber = max(1, intval($_POST['employee_id_start'] ?? 1));

        $this->setSchoolSetting($schoolId, 'employee_id_prefix', $prefix);
        $this->setSchoolSetting($schoolId, 'employee_id_start', (string)$startNumber);

        Session::flash('success', 'Employee ID settings saved.');
        Response::redirect('school-setup/form-settings?tab=admission');
    }

    public function saveAttendanceSettings()
    {
        $schoolId = $this->getSchoolId();

        // Mode: morning, morning_evening, subject
        $mode = $_POST['attendance_mode'] ?? 'morning';
        // Who marks (class-based modes)
        $classMarker = $_POST['class_marker'] ?? 'class_teacher';
        // Who marks (subject mode)
        $subjectMarker = $_POST['subject_marker'] ?? 'subject_teacher';
        // Admin override
        $adminAccess = $_POST['admin_access'] ?? [];
        // Always include school_admin
        if (!in_array('school_admin', $adminAccess)) array_unshift($adminAccess, 'school_admin');

        // Build unified access array: [class_marker_or_subject_marker, subject_marker, ...admin_access]
        $access = [$classMarker, $subjectMarker];
        foreach ($adminAccess as $a) {
            if (!in_array($a, $access)) $access[] = $a;
        }

        $lateAllowed = isset($_POST['attendance_late']) ? '1' : '0';
        $halfDay = isset($_POST['attendance_half_day']) ? '1' : '0';
        $excused = isset($_POST['attendance_excused']) ? '1' : '0';
        $allowPast = isset($_POST['allow_past_attendance']) ? '1' : '0';

        $this->setSchoolSetting($schoolId, 'attendance_type', $mode);
        $this->setSchoolSetting($schoolId, 'attendance_access', json_encode($access));
        $this->setSchoolSetting($schoolId, 'attendance_late_allowed', $lateAllowed);
        $this->setSchoolSetting($schoolId, 'attendance_half_day', $halfDay);
        $this->setSchoolSetting($schoolId, 'attendance_excused', $excused);
        $this->setSchoolSetting($schoolId, 'allow_past_attendance', $allowPast);

        Session::flash('success', 'Attendance settings saved.');
        Response::redirect('school-setup/form-settings?tab=attendance');
    }

    public function saveFieldConfig()
    {
        $schoolId = $this->getSchoolId();
        $fields = $_POST['fields'] ?? [];

        foreach ($fields as $fieldName => $config) {
            $visibility = ($config['visibility'] ?? 'show') === 'show' ? 'show' : 'hide';
            $isRequired = isset($config['is_required']) ? 1 : 0;

            $existing = Database::fetch(
                "SELECT id FROM form_field_config WHERE school_id = ? AND form_type = 'student_admission' AND field_name = ?",
                [$schoolId, $fieldName]
            );

            if ($existing) {
                Database::update('form_field_config', [
                    'visibility'  => $visibility,
                    'is_required' => $isRequired,
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('form_field_config', [
                    'school_id'   => $schoolId,
                    'form_type'   => 'student_admission',
                    'field_name'  => $fieldName,
                    'visibility'  => $visibility,
                    'is_required' => $isRequired,
                ]);
            }
        }

        Session::flash('success', 'Field configuration saved.');
        Response::redirect('school-setup/form-settings?tab=fields');
    }

    public function storeCustomField()
    {
        $schoolId = $this->getSchoolId();
        $label = trim($_POST['field_label'] ?? '');
        $type = $_POST['field_type'] ?? 'text';
        $options = trim($_POST['options'] ?? '');
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $placeholder = trim($_POST['placeholder'] ?? '');
        $formType = $_POST['form_type'] ?? 'student_admission';

        if (empty($label)) {
            Session::flash('error', 'Field label is required.');
            Response::back();
            return;
        }

        // Generate safe field_name from label
        $fieldName = 'cf_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label));
        $fieldName = rtrim($fieldName, '_');

        // Parse options for select type
        $optionsJson = null;
        if (in_array($type, ['select', 'checkbox', 'radio']) && !empty($options)) {
            $optionsArray = array_filter(array_map('trim', explode(',', $options)));
            $optionsJson = json_encode($optionsArray);
        }

        // Get next display order
        $maxOrder = Database::fetch(
            "SELECT MAX(display_order) as max_order FROM custom_fields WHERE school_id = ? AND form_type = ?",
            [$schoolId, $formType]
        );

        try {
            Database::insert('custom_fields', [
                'school_id'     => $schoolId,
                'form_type'     => $formType,
                'field_label'   => $label,
                'field_name'    => $fieldName,
                'field_type'    => $type,
                'options'       => $optionsJson,
                'placeholder'   => $placeholder ?: null,
                'is_required'   => $isRequired,
                'display_order' => ($maxOrder['max_order'] ?? 0) + 1,
            ]);
            Session::flash('success', "Custom field \"{$label}\" added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Field name already exists or error: ' . $e->getMessage());
        }

        $tab = ($formType === 'staff') ? 'staff_fields' : 'custom';
        Response::redirect('school-setup/form-settings?tab=' . $tab);
    }

    public function deleteCustomField($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM custom_fields WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Custom field deleted.');
        Response::redirect('school-setup/form-settings?tab=custom');
    }

    public function updateCustomField($id)
    {
        $schoolId = $this->getSchoolId();
        $label = trim($_POST['field_label'] ?? '');
        $type = $_POST['field_type'] ?? 'text';
        $options = trim($_POST['options'] ?? '');
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $placeholder = trim($_POST['placeholder'] ?? '');

        if (empty($label)) {
            Session::flash('error', 'Field label is required.');
            Response::back();
            return;
        }

        $optionsJson = null;
        if (in_array($type, ['select', 'checkbox', 'radio']) && !empty($options)) {
            $optionsArray = array_filter(array_map('trim', explode(',', $options)));
            $optionsJson = json_encode($optionsArray);
        }

        try {
            Database::update('custom_fields', [
                'field_label' => $label,
                'field_type'  => $type,
                'options'     => $optionsJson,
                'placeholder' => $placeholder ?: null,
                'is_required' => $isRequired,
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);

            Session::flash('success', "Custom field \"{$label}\" updated.");
        } catch (\Exception $e) {
            Session::flash('error', 'Failed: ' . $e->getMessage());
        }

        Response::redirect('school-setup/form-settings?tab=custom');
    }

    // ═══════════════════════════════════════════════
    // DEPARTMENTS
    // ═══════════════════════════════════════════════

    public function departments()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $departments = Database::fetchAll(
            "SELECT d.*, u.full_name as head_name,
                    (SELECT COUNT(*) FROM staff_details sd WHERE sd.department_id = d.id) as staff_count
             FROM departments d
             LEFT JOIN users u ON d.head_id = u.id
             WHERE d.school_id = ?
             ORDER BY d.name",
            [$schoolId]
        );

        $teachers = Database::fetchAll(
            "SELECT id, full_name FROM users WHERE school_id = ? AND user_type IN ('teacher','staff') AND is_active = 1 ORDER BY full_name",
            [$schoolId]
        );

        Response::view('school-setup/departments', [
            'pageTitle'    => 'Departments',
            'departments'  => $departments,
            'teachers'     => $teachers,
            'breadcrumbs'  => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Departments'],
            ],
        ]);
    }

    public function storeDepartment()
    {
        $schoolId = $this->getSchoolId();
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) { Session::flash('error', 'Name is required.'); Response::back(); return; }

        try {
            Database::insert('departments', [
                'school_id' => $schoolId,
                'name'      => $name,
                'code'      => !empty($_POST['code']) ? strtoupper(trim($_POST['code'])) : null,
                'head_id'   => !empty($_POST['head_id']) ? (int)$_POST['head_id'] : null,
            ]);
            Session::flash('success', "Department '{$name}' added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Department already exists or error: ' . $e->getMessage());
        }
        Response::redirect('school-setup/departments');
    }

    public function updateDepartment($id)
    {
        $schoolId = $this->getSchoolId();
        Database::update('departments', [
            'name'    => trim($_POST['name']),
            'code'    => !empty($_POST['code']) ? strtoupper(trim($_POST['code'])) : null,
            'head_id' => !empty($_POST['head_id']) ? (int)$_POST['head_id'] : null,
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Department updated.');
        Response::redirect('school-setup/departments');
    }

    public function deleteDepartment($id)
    {
        $schoolId = $this->getSchoolId();
        $staffCount = Database::count('staff_details', 'department_id = ?', [$id]);
        if ($staffCount > 0) {
            Session::flash('error', "Cannot delete: {$staffCount} staff member(s) assigned.");
        } else {
            Database::delete('departments', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Department deleted.');
        }
        Response::redirect('school-setup/departments');
    }

    // ═══════════════════════════════════════════════
    // DESIGNATIONS
    // ═══════════════════════════════════════════════

    public function designations()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $designations = Database::fetchAll(
            "SELECT d.*,
                    (SELECT COUNT(*) FROM staff_details sd WHERE sd.designation_id = d.id) as staff_count
             FROM designations d
             WHERE d.school_id = ?
             ORDER BY d.staff_category, d.level, d.name",
            [$schoolId]
        );

        Response::view('school-setup/designations', [
            'pageTitle'     => 'Designations',
            'designations'  => $designations,
            'breadcrumbs'   => [
                ['label' => 'School Setup', 'url' => APP_URL . '/school-setup'],
                ['label' => 'Designations'],
            ],
        ]);
    }

    public function storeDesignation()
    {
        $schoolId = $this->getSchoolId();
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) { Session::flash('error', 'Name is required.'); Response::back(); return; }

        try {
            Database::insert('designations', [
                'school_id'      => $schoolId,
                'name'           => $name,
                'staff_category' => $_POST['staff_category'] ?? 'teaching',
                'level'          => (int)($_POST['level'] ?? 0),
            ]);
            Session::flash('success', "Designation '{$name}' added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Designation already exists or error: ' . $e->getMessage());
        }
        Response::redirect('school-setup/designations');
    }

    public function updateDesignation($id)
    {
        $schoolId = $this->getSchoolId();
        Database::update('designations', [
            'name'           => trim($_POST['name']),
            'staff_category' => $_POST['staff_category'] ?? 'teaching',
            'level'          => (int)($_POST['level'] ?? 0),
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Designation updated.');
        Response::redirect('school-setup/designations');
    }

    public function deleteDesignation($id)
    {
        $schoolId = $this->getSchoolId();
        $staffCount = Database::count('staff_details', 'designation_id = ?', [$id]);
        if ($staffCount > 0) {
            Session::flash('error', "Cannot delete: {$staffCount} staff member(s) assigned.");
        } else {
            Database::delete('designations', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Designation deleted.');
        }
        Response::redirect('school-setup/designations');
    }

    // ─── AJAX: Quick Add Department ─────────────
    public function ajaxStoreDepartment()
    {
        $schoolId = $this->getSchoolId();
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Name is required']);
            exit;
        }

        try {
            $id = Database::insert('departments', [
                'school_id' => $schoolId,
                'name'      => $name,
                'code'      => !empty($_POST['code']) ? strtoupper(trim($_POST['code'])) : null,
            ]);
            echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Already exists or error']);
        }
        exit;
    }

    // ─── AJAX: Quick Add Designation ─────────────
    public function ajaxStoreDesignation()
    {
        $schoolId = $this->getSchoolId();
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Name is required']);
            exit;
        }

        $category = $_POST['staff_category'] ?? 'teaching';

        try {
            $id = Database::insert('designations', [
                'school_id'      => $schoolId,
                'name'           => $name,
                'staff_category' => $category,
            ]);
            echo json_encode(['success' => true, 'id' => $id, 'name' => $name, 'staff_category' => $category]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Already exists or error']);
        }
        exit;
    }
}
