<?php
/**
 * Attendance Controller
 * Student attendance marking and reports
 */

class AttendanceController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    private function getSchoolId(): ?int { return Session::schoolId(); }

    private function getSchoolSetting(int $schoolId, string $key, $default = null)
    {
        $row = Database::fetch(
            "SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = ?",
            [$schoolId, $key]
        );
        return $row ? $row['setting_value'] : $default;
    }

    private function getCurrentAcademicYear($schoolId)
    {
        return Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
    }

    // ─── Main Page: Select Class & Mark ────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Load attendance settings
        $mode = $this->getSchoolSetting($schoolId, 'attendance_type', 'morning');
        $accessJson = $this->getSchoolSetting($schoolId, 'attendance_access', '["class_teacher","subject_teacher"]');
        $access = json_decode($accessJson, true) ?: ['class_teacher','subject_teacher'];

        // Load classes with sections
        $classes = Database::fetchAll(
            "SELECT c.id, c.name, c.numeric_name FROM classes c
             WHERE c.school_id = ? AND c.academic_year_id = ?
             ORDER BY c.numeric_name",
            [$schoolId, $yearId]
        );

        foreach ($classes as &$c) {
            $c['sections'] = Database::fetchAll(
                "SELECT s.id, s.name FROM sections s WHERE s.class_id = ? ORDER BY s.name",
                [$c['id']]
            );
        }

        // Selected Date & Session
        $today = $_GET['date'] ?? date('Y-m-d');
        $selectedSession = $_GET['session'] ?? 'morning';

        // Today's attendance summary
        $todaySummary = Database::fetchAll(
            "SELECT a.class_id, a.section_id, a.session_type,
                    COUNT(*) as total,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
             FROM attendance a
             WHERE a.school_id = ? AND a.attendance_date = ? AND a.session_type IN ('morning','evening')
             GROUP BY a.class_id, a.section_id, a.session_type",
            [$schoolId, $today]
        );

        $summaryMap = [];
        foreach ($todaySummary as $s) {
            $summaryMap[$s['class_id'] . '-' . $s['section_id'] . '-' . $s['session_type']] = $s;
        }

        // Enabled statuses
        $lateEnabled = $this->getSchoolSetting($schoolId, 'attendance_late_allowed', '1') === '1';
        $halfDayEnabled = $this->getSchoolSetting($schoolId, 'attendance_half_day', '0') === '1';
        $excusedEnabled = $this->getSchoolSetting($schoolId, 'attendance_excused', '1') === '1';

        Response::view('attendance/index', [
            'pageTitle'      => 'Student Attendance',
            'classes'        => $classes,
            'mode'           => $mode,
            'access'         => $access,
            'summaryMap'     => $summaryMap,
            'today'          => $today,
            'selectedSession'=> $selectedSession,
            'lateEnabled'    => $lateEnabled,
            'halfDayEnabled' => $halfDayEnabled,
            'excusedEnabled' => $excusedEnabled,
            'breadcrumbs'    => [['label' => 'Attendance']],
        ]);
    }

    // ─── Mark Attendance Page ───────────────────────
    public function mark()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $classId = $_GET['class_id'] ?? null;
        $sectionId = $_GET['section_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        $sessionType = $_GET['session'] ?? 'morning';

        if (!$classId || !$sectionId) {
            Session::flash('error', 'Please select a class and section.');
            Response::redirect('attendance');
            return;
        }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;
        $mode = $this->getSchoolSetting($schoolId, 'attendance_type', 'morning');

        $class = Database::fetch("SELECT * FROM classes WHERE id = ? AND school_id = ?", [$classId, $schoolId]);
        $section = Database::fetch("SELECT * FROM sections WHERE id = ? AND class_id = ?", [$sectionId, $classId]);
        if (!$class || !$section) { Response::abort(404); return; }

        // Get students in this class/section
        $students = Database::fetchAll(
            "SELECT u.id, u.full_name, sd.admission_no, sd.roll_number, u.gender
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             WHERE sd.class_id = ? AND sd.section_id = ? AND u.school_id = ? AND u.is_active = 1
             ORDER BY sd.roll_number ASC, u.full_name ASC",
            [$classId, $sectionId, $schoolId]
        );

        // Load existing attendance for this date/session
        $existing = Database::fetchAll(
            "SELECT student_id, status, remarks FROM attendance
             WHERE class_id = ? AND section_id = ? AND attendance_date = ? AND session_type = ? AND school_id = ?",
            [$classId, $sectionId, $date, $sessionType, $schoolId]
        );

        $attendanceMap = [];
        foreach ($existing as $e) {
            $attendanceMap[$e['student_id']] = $e;
        }

        // For subject-wise mode: load periods for today
        $periods = [];
        $periodId = $_GET['period_id'] ?? null;
        $subjectId = $_GET['subject_id'] ?? null;
        $subjectName = '';

        if ($mode === 'subject') {
            $sessionType = 'period';
            $dayOfWeek = date('N', strtotime($date)); // 1=Mon ... 7=Sun

            $periods = Database::fetchAll(
                "SELECT tp.id, tp.name, tp.short_name, tp.start_time, tp.end_time,
                        t.subject_id, s.name as subject_name, s.code as subject_code,
                        t.teacher_id, u.full_name as teacher_name
                 FROM timetable_periods tp
                 LEFT JOIN timetable t ON t.period_id = tp.id AND t.class_id = ? AND t.section_id = ? AND t.day_of_week = ?
                 LEFT JOIN subjects s ON t.subject_id = s.id
                 LEFT JOIN users u ON t.teacher_id = u.id
                 WHERE tp.school_id = ? AND tp.period_type = 'class'
                 ORDER BY tp.display_order, tp.start_time",
                [$classId, $sectionId, $dayOfWeek, $schoolId]
            );

            // If period selected, load its attendance
            if ($periodId) {
                $existing = Database::fetchAll(
                    "SELECT student_id, status, remarks FROM attendance
                     WHERE class_id = ? AND section_id = ? AND attendance_date = ? AND session_type = 'period' AND period_id = ? AND school_id = ?",
                    [$classId, $sectionId, $date, $periodId, $schoolId]
                );
                $attendanceMap = [];
                foreach ($existing as $e) {
                    $attendanceMap[$e['student_id']] = $e;
                }

                // Get subject name for the selected period
                $periodEntry = Database::fetch(
                    "SELECT s.name as subject_name FROM timetable t
                     JOIN subjects s ON t.subject_id = s.id
                     WHERE t.period_id = ? AND t.class_id = ? AND t.section_id = ? AND t.day_of_week = ?",
                    [$periodId, $classId, $sectionId, $dayOfWeek]
                );
                $subjectName = $periodEntry['subject_name'] ?? '';
                $subjectId = $periodEntry['subject_id'] ?? null;
            }
        }

        $lateEnabled = $this->getSchoolSetting($schoolId, 'attendance_late_allowed', '1') === '1';
        $halfDayEnabled = $this->getSchoolSetting($schoolId, 'attendance_half_day', '0') === '1';
        $excusedEnabled = $this->getSchoolSetting($schoolId, 'attendance_excused', '1') === '1';
        $allowPastAttendance = $this->getSchoolSetting($schoolId, 'allow_past_attendance', '1') === '1';

        $role = Session::userRole();
        $isSuperOrSchoolAdmin = in_array($role, ['super_admin', 'school_admin']);
        $canEdit = true;
        
        $today = date('Y-m-d');
        if (!$isSuperOrSchoolAdmin && !$allowPastAttendance && $date < $today) {
            $canEdit = false;
        }

        Response::view('attendance/mark', [
            'pageTitle'      => 'Mark Attendance',
            'class'          => $class,
            'section'        => $section,
            'students'       => $students,
            'date'           => $date,
            'sessionType'    => $sessionType,
            'mode'           => $mode,
            'attendanceMap'  => $attendanceMap,
            'periods'        => $periods,
            'periodId'       => $periodId,
            'subjectId'      => $subjectId,
            'subjectName'    => $subjectName,
            'lateEnabled'    => $lateEnabled,
            'halfDayEnabled' => $halfDayEnabled,
            'excusedEnabled' => $excusedEnabled,
            'canEdit'        => $canEdit,
            'breadcrumbs'    => [
                ['label' => 'Attendance', 'url' => APP_URL . '/attendance'],
                ['label' => $class['name'] . ' ' . $section['name']],
            ],
        ]);
    }

    // ─── Save Attendance (POST) ─────────────────────
    public function store()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $classId = (int)$_POST['class_id'];
        $sectionId = (int)$_POST['section_id'];
        $date = $_POST['attendance_date'];
        $sessionType = $_POST['session_type'] ?? 'morning';
        $periodId = !empty($_POST['period_id']) ? (int)$_POST['period_id'] : null;
        $subjectId = !empty($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
        $statuses = $_POST['status'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        $markedBy = Session::userId();

        $allowPastAttendance = $this->getSchoolSetting($schoolId, 'allow_past_attendance', '1') === '1';
        $role = Session::userRole();
        $isSuperOrSchoolAdmin = in_array($role, ['super_admin', 'school_admin']);
        $today = date('Y-m-d');
        if (!$isSuperOrSchoolAdmin && !$allowPastAttendance && $date < $today) {
            Session::flash('error', 'You are not allowed to edit past attendance.');
            Response::back();
            return;
        }

        $saved = 0;
        foreach ($statuses as $studentId => $status) {
            $studentId = (int)$studentId;
            if (!in_array($status, ['present','absent','late','excused','half_day'])) continue;

            // Check if already exists
            $conditions = "student_id = ? AND attendance_date = ? AND session_type = ? AND school_id = ?";
            $params = [$studentId, $date, $sessionType, $schoolId];

            if ($periodId) {
                $conditions .= " AND period_id = ?";
                $params[] = $periodId;
            } else {
                $conditions .= " AND period_id IS NULL";
            }

            $existing = Database::fetch("SELECT id FROM attendance WHERE {$conditions}", $params);

            $data = [
                'school_id'        => $schoolId,
                'academic_year_id' => $currentYear['id'],
                'class_id'         => $classId,
                'section_id'       => $sectionId,
                'student_id'       => $studentId,
                'attendance_date'  => $date,
                'period_id'        => $periodId,
                'subject_id'       => $subjectId,
                'session_type'     => $sessionType,
                'status'           => $status,
                'remarks'          => !empty($remarks[$studentId]) ? trim($remarks[$studentId]) : null,
                'marked_by'        => $markedBy,
            ];

            if ($existing) {
                Database::update('attendance', [
                    'status'    => $status,
                    'remarks'   => $data['remarks'],
                    'marked_by' => $markedBy,
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('attendance', $data);
            }
            $saved++;
        }

        Session::flash('success', "Attendance saved for {$saved} students.");

        // Redirect back
        $redirectUrl = "attendance/mark?class_id={$classId}&section_id={$sectionId}&date={$date}&session={$sessionType}";
        if ($periodId) $redirectUrl .= "&period_id={$periodId}";
        Response::redirect($redirectUrl);
    }

    // ─── Quick Mark All (AJAX) ──────────────────────
    public function markAll()
    {
        $schoolId = $this->getSchoolId();
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $classId = (int)$_POST['class_id'];
        $sectionId = (int)$_POST['section_id'];
        $date = $_POST['date'];
        $sessionType = $_POST['session_type'] ?? 'morning';
        $status = $_POST['status'] ?? 'present';
        $periodId = !empty($_POST['period_id']) ? (int)$_POST['period_id'] : null;
        $subjectId = !empty($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;

        // Get all students
        $students = Database::fetchAll(
            "SELECT u.id FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             WHERE sd.class_id = ? AND sd.section_id = ? AND u.school_id = ? AND u.is_active = 1",
            [$classId, $sectionId, $schoolId]
        );

        $count = 0;
        foreach ($students as $s) {
            $conditions = "student_id = ? AND attendance_date = ? AND session_type = ? AND school_id = ?";
            $params = [$s['id'], $date, $sessionType, $schoolId];
            if ($periodId) { $conditions .= " AND period_id = ?"; $params[] = $periodId; }
            else { $conditions .= " AND period_id IS NULL"; }

            $existing = Database::fetch("SELECT id FROM attendance WHERE {$conditions}", $params);

            if ($existing) {
                Database::update('attendance', ['status' => $status, 'marked_by' => Session::userId()], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('attendance', [
                    'school_id' => $schoolId, 'academic_year_id' => $currentYear['id'],
                    'class_id' => $classId, 'section_id' => $sectionId, 'student_id' => $s['id'],
                    'attendance_date' => $date, 'period_id' => $periodId, 'subject_id' => $subjectId,
                    'session_type' => $sessionType, 'status' => $status, 'marked_by' => Session::userId(),
                ]);
            }
            $count++;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    // ─── Reports ────────────────────────────────────
    public function report()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $classId = $_GET['class_id'] ?? null;
        $sectionId = $_GET['section_id'] ?? null;
        $month = $_GET['month'] ?? date('Y-m');

        $classes = Database::fetchAll(
            "SELECT c.id, c.name FROM classes c WHERE c.school_id = ? AND c.academic_year_id = ? ORDER BY c.numeric_name",
            [$schoolId, $yearId]
        );

        $sections = [];
        $students = [];
        $report = [];
        $daysInMonth = 0;
        $selectedClass = null;
        $selectedSection = null;

        if ($classId && $sectionId) {
            $selectedClass = Database::fetch("SELECT * FROM classes WHERE id = ?", [$classId]);
            $selectedSection = Database::fetch("SELECT * FROM sections WHERE id = ?", [$sectionId]);

            $sections = Database::fetchAll("SELECT id, name FROM sections WHERE class_id = ? ORDER BY name", [$classId]);

            // Month range
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $daysInMonth = (int)date('t', strtotime($startDate));

            // Get students
            $students = Database::fetchAll(
                "SELECT u.id, u.full_name, sd.roll_number
                 FROM users u JOIN student_details sd ON u.id = sd.user_id
                 WHERE sd.class_id = ? AND sd.section_id = ? AND u.school_id = ? AND u.is_active = 1
                 ORDER BY sd.roll_number, u.full_name",
                [$classId, $sectionId, $schoolId]
            );

            // Get attendance for month
            $attendance = Database::fetchAll(
                "SELECT student_id, attendance_date, status, session_type
                 FROM attendance
                 WHERE class_id = ? AND section_id = ? AND school_id = ?
                   AND attendance_date BETWEEN ? AND ? AND session_type = 'morning'
                 ORDER BY attendance_date",
                [$classId, $sectionId, $schoolId, $startDate, $endDate]
            );

            // Map: [student_id][day] = status
            foreach ($attendance as $a) {
                $day = (int)date('j', strtotime($a['attendance_date']));
                $report[$a['student_id']][$day] = $a['status'];
            }
        }

        Response::view('attendance/report', [
            'pageTitle'       => 'Attendance Report',
            'classes'         => $classes,
            'sections'        => $sections,
            'students'        => $students,
            'report'          => $report,
            'classId'         => $classId,
            'sectionId'       => $sectionId,
            'month'           => $month,
            'daysInMonth'     => $daysInMonth,
            'selectedClass'   => $selectedClass,
            'selectedSection' => $selectedSection,
            'breadcrumbs'     => [
                ['label' => 'Attendance', 'url' => APP_URL . '/attendance'],
                ['label' => 'Monthly Report'],
            ],
        ]);
    }

    // ─── AJAX: Get sections for a class ─────────────
    public function getSections()
    {
        $classId = $_GET['class_id'] ?? 0;
        $sections = Database::fetchAll("SELECT id, name FROM sections WHERE class_id = ? ORDER BY name", [$classId]);
        header('Content-Type: application/json');
        echo json_encode($sections);
        exit;
    }
}
