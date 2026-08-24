<?php
/**
 * Timetable Controller
 * Manage period slots and class timetable
 */

class TimetableController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    private function getSchoolId(): ?int { return Session::schoolId(); }

    private function getCurrentAcademicYear($schoolId)
    {
        return Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
    }

    // ─── Main Timetable Page ────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Load periods
        $periods = Database::fetchAll(
            "SELECT * FROM timetable_periods WHERE school_id = ? ORDER BY display_order, start_time",
            [$schoolId]
        );

        // Load classes with sections
        $classes = Database::fetchAll(
            "SELECT c.id, c.name, c.numeric_name,
                    (SELECT GROUP_CONCAT(CONCAT(s.id, ':', s.name) SEPARATOR '|') FROM sections s WHERE s.class_id = c.id) as sections_data
             FROM classes c WHERE c.school_id = ? AND c.academic_year_id = ? ORDER BY c.numeric_name",
            [$schoolId, $yearId]
        );

        Response::view('timetable/index', [
            'pageTitle'   => 'Timetable',
            'periods'     => $periods,
            'classes'     => $classes,
            'currentYear' => $currentYear,
            'breadcrumbs' => [['label' => 'Timetable']],
        ]);
    }

    // ─── Period Management (AJAX-like) ──────────
    public function periods()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $periods = Database::fetchAll(
            "SELECT * FROM timetable_periods WHERE school_id = ? ORDER BY display_order, start_time",
            [$schoolId]
        );

        Response::view('timetable/periods', [
            'pageTitle'   => 'Period Configuration',
            'periods'     => $periods,
            'breadcrumbs' => [
                ['label' => 'Timetable', 'url' => APP_URL . '/timetable'],
                ['label' => 'Period Setup'],
            ],
        ]);
    }

    public function storePeriod()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        Database::insert('timetable_periods', [
            'school_id'     => $schoolId,
            'name'          => $data['name'],
            'short_name'    => !empty($data['short_name']) ? $data['short_name'] : null,
            'start_time'    => $data['start_time'],
            'end_time'      => $data['end_time'],
            'period_type'   => $data['period_type'] ?? 'class',
            'display_order' => (int)($data['display_order'] ?? 0),
        ]);

        Session::flash('success', "Period '{$data['name']}' added.");
        Response::redirect('timetable/periods');
    }

    public function updatePeriod($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        Database::update('timetable_periods', [
            'name'          => $data['name'],
            'short_name'    => !empty($data['short_name']) ? $data['short_name'] : null,
            'start_time'    => $data['start_time'],
            'end_time'      => $data['end_time'],
            'period_type'   => $data['period_type'] ?? 'class',
            'display_order' => (int)($data['display_order'] ?? 0),
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Period updated.');
        Response::redirect('timetable/periods');
    }

    public function deletePeriod($id)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('timetable_periods', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Period deleted.');
        Response::redirect('timetable/periods');
    }

    public function bulkPeriods()
    {
        $schoolId = $this->getSchoolId();
        $template = $_POST['template'] ?? 'standard';

        // Delete existing periods
        Database::delete('timetable_periods', 'school_id = ?', [$schoolId]);

        $templates = [
            'standard' => [
                ['Assembly',   null, '08:00', '08:30', 'assembly', 1],
                ['Period 1',   'P1', '08:30', '09:15', 'class', 2],
                ['Period 2',   'P2', '09:15', '10:00', 'class', 3],
                ['Break',      null, '10:00', '10:20', 'break', 4],
                ['Period 3',   'P3', '10:20', '11:05', 'class', 5],
                ['Period 4',   'P4', '11:05', '11:50', 'class', 6],
                ['Lunch',      null, '11:50', '12:30', 'lunch', 7],
                ['Period 5',   'P5', '12:30', '13:15', 'class', 8],
                ['Period 6',   'P6', '13:15', '14:00', 'class', 9],
                ['Period 7',   'P7', '14:00', '14:45', 'class', 10],
                ['Period 8',   'P8', '14:45', '15:30', 'class', 11],
            ],
            'compact' => [
                ['Period 1',   'P1', '08:00', '08:45', 'class', 1],
                ['Period 2',   'P2', '08:45', '09:30', 'class', 2],
                ['Period 3',   'P3', '09:30', '10:15', 'class', 3],
                ['Break',      null, '10:15', '10:35', 'break', 4],
                ['Period 4',   'P4', '10:35', '11:20', 'class', 5],
                ['Period 5',   'P5', '11:20', '12:05', 'class', 6],
                ['Lunch',      null, '12:05', '12:45', 'lunch', 7],
                ['Period 6',   'P6', '12:45', '13:30', 'class', 8],
                ['Period 7',   'P7', '13:30', '14:15', 'class', 9],
            ],
        ];

        $slots = $templates[$template] ?? $templates['standard'];

        foreach ($slots as $s) {
            Database::insert('timetable_periods', [
                'school_id'     => $schoolId,
                'name'          => $s[0],
                'short_name'    => $s[1],
                'start_time'    => $s[2],
                'end_time'      => $s[3],
                'period_type'   => $s[4],
                'display_order' => $s[5],
            ]);
        }

        Session::flash('success', ucfirst($template) . ' period template applied.');
        Response::redirect('timetable/periods');
    }

    // ─── Class Timetable View/Edit ──────────────
    public function classView()
    {
        $schoolId = $this->getSchoolId();
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $classId = $_GET['class_id'] ?? null;
        $sectionId = $_GET['section_id'] ?? null;

        if (!$classId || !$sectionId) {
            Response::redirect('timetable');
            return;
        }

        $class = Database::fetch("SELECT * FROM classes WHERE id = ? AND school_id = ?", [$classId, $schoolId]);
        $section = Database::fetch("SELECT * FROM sections WHERE id = ? AND class_id = ?", [$sectionId, $classId]);
        if (!$class || !$section) { Response::abort(404); return; }

        // Load periods (class type only for schedule, all for display)
        $periods = Database::fetchAll(
            "SELECT * FROM timetable_periods WHERE school_id = ? ORDER BY display_order, start_time",
            [$schoolId]
        );

        // Load timetable entries
        $entries = Database::fetchAll(
            "SELECT t.*, s.name as subject_name, s.code as subject_code,
                    u.full_name as teacher_name
             FROM timetable t
             JOIN subjects s ON t.subject_id = s.id
             LEFT JOIN users u ON t.teacher_id = u.id
             WHERE t.class_id = ? AND t.section_id = ? AND t.school_id = ?
             ORDER BY t.day_of_week, t.period_id",
            [$classId, $sectionId, $schoolId]
        );

        // Index entries: [day][period_id] = entry
        $schedule = [];
        foreach ($entries as $e) {
            $schedule[$e['day_of_week']][$e['period_id']] = $e;
        }

        // Load subjects assigned to this class
        $subjects = Database::fetchAll(
            "SELECT cs.id, s.id as subject_id, s.name, s.code, cs.teacher_id, u.full_name as teacher_name
             FROM class_subjects cs
             JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN users u ON cs.teacher_id = u.id
             WHERE cs.class_id = ?
             ORDER BY s.name",
            [$classId]
        );

        // All teachers
        $teachers = Database::fetchAll(
            "SELECT id, full_name FROM users WHERE school_id = ? AND user_type = 'teacher' AND is_active = 1 ORDER BY full_name",
            [$schoolId]
        );

        Response::view('timetable/class-view', [
            'pageTitle'   => $class['name'] . ' - ' . $section['name'] . ' Timetable',
            'class'       => $class,
            'section'     => $section,
            'periods'     => $periods,
            'schedule'    => $schedule,
            'subjects'    => $subjects,
            'teachers'    => $teachers,
            'days'        => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'breadcrumbs' => [
                ['label' => 'Timetable', 'url' => APP_URL . '/timetable'],
                ['label' => $class['name'] . ' ' . $section['name']],
            ],
        ]);
    }

    // ─── Save Single Slot ───────────────────────
    public function saveSlot()
    {
        $schoolId = $this->getSchoolId();
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $data = $_POST;

        $classId = (int)$data['class_id'];
        $sectionId = (int)$data['section_id'];
        $dayOfWeek = (int)$data['day_of_week'];
        $periodId = (int)$data['period_id'];
        $subjectId = (int)$data['subject_id'];
        $teacherId = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;

        // Upsert
        $existing = Database::fetch(
            "SELECT id FROM timetable WHERE class_id = ? AND section_id = ? AND day_of_week = ? AND period_id = ? AND school_id = ?",
            [$classId, $sectionId, $dayOfWeek, $periodId, $schoolId]
        );

        if ($subjectId === 0) {
            // Clear slot
            if ($existing) {
                Database::delete('timetable', 'id = ?', [$existing['id']]);
            }
        } elseif ($existing) {
            Database::update('timetable', [
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('timetable', [
                'school_id'        => $schoolId,
                'academic_year_id' => $currentYear['id'],
                'class_id'         => $classId,
                'section_id'       => $sectionId,
                'day_of_week'      => $dayOfWeek,
                'period_id'        => $periodId,
                'subject_id'       => $subjectId,
                'teacher_id'       => $teacherId,
            ]);
        }

        // Return JSON for AJAX or redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        Session::flash('success', 'Timetable updated.');
        Response::redirect("timetable/class-view?class_id={$classId}&section_id={$sectionId}");
    }

    // ─── Teacher's Timetable ────────────────────
    public function teacherView()
    {
        $schoolId = $this->getSchoolId();
        $teacherId = $_GET['teacher_id'] ?? Session::userId();

        $teacher = Database::fetch(
            "SELECT id, full_name FROM users WHERE id = ? AND school_id = ?",
            [$teacherId, $schoolId]
        );
        if (!$teacher) { Response::abort(404); return; }

        $periods = Database::fetchAll(
            "SELECT * FROM timetable_periods WHERE school_id = ? ORDER BY display_order, start_time",
            [$schoolId]
        );

        $entries = Database::fetchAll(
            "SELECT t.*, s.name as subject_name, s.code as subject_code,
                    c.name as class_name, sec.name as section_name
             FROM timetable t
             JOIN subjects s ON t.subject_id = s.id
             JOIN classes c ON t.class_id = c.id
             JOIN sections sec ON t.section_id = sec.id
             WHERE t.teacher_id = ? AND t.school_id = ?
             ORDER BY t.day_of_week, t.period_id",
            [$teacherId, $schoolId]
        );

        $schedule = [];
        foreach ($entries as $e) {
            $schedule[$e['day_of_week']][$e['period_id']] = $e;
        }

        Response::view('timetable/teacher-view', [
            'pageTitle'   => $teacher['full_name'] . ' - Timetable',
            'teacher'     => $teacher,
            'periods'     => $periods,
            'schedule'    => $schedule,
            'days'        => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'breadcrumbs' => [
                ['label' => 'Timetable', 'url' => APP_URL . '/timetable'],
                ['label' => $teacher['full_name']],
            ],
        ]);
    }
}
