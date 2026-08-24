<?php
/**
 * Exam Controller
 * Manage exam terms, scheduling, and grading
 */

class ExamController
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

    // ─── Exam Terms ──────────────────────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $exams = Database::fetchAll(
            "SELECT * FROM exams WHERE school_id = ? AND academic_year_id = ? ORDER BY start_date",
            [$schoolId, $yearId]
        );

        Response::view('exams/index', [
            'pageTitle' => 'Exams',
            'exams' => $exams,
            'breadcrumbs' => [['label' => 'Academics'], ['label' => 'Exams']]
        ]);
    }

    public function store()
    {
        $schoolId = $this->getSchoolId();
        $yearId = $this->getCurrentAcademicYear($schoolId)['id'] ?? 0;

        Database::insert('exams', [
            'school_id' => $schoolId,
            'academic_year_id' => $yearId,
            'name' => $_POST['name'],
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'remarks' => $_POST['remarks'] ?? null
        ]);

        Session::flash('success', 'Exam term created.');
        Response::redirect('exams');
    }

    public function update($id)
    {
        $schoolId = $this->getSchoolId();
        
        Database::update('exams', [
            'name' => $_POST['name'],
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'remarks' => $_POST['remarks'] ?? null
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Exam term updated.');
        Response::redirect('exams');
    }

    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('exams', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Exam deleted.');
        Response::redirect('exams');
    }

    // ─── Exam Schedule ───────────────────────────────────────
    public function schedule($examId)
    {
        $schoolId = $this->getSchoolId();
        $exam = Database::fetch("SELECT * FROM exams WHERE id = ? AND school_id = ?", [$examId, $schoolId]);
        if (!$exam) { Response::abort(404); return; }

        $classes = Database::fetchAll("SELECT * FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name", [$schoolId, $exam['academic_year_id']]);
        $classId = $_GET['class_id'] ?? ($classes[0]['id'] ?? 0);

        $sections = Database::fetchAll("SELECT * FROM sections WHERE class_id = ? ORDER BY name", [$classId]);
        
        // Subjects for this class
        $subjects = Database::fetchAll(
            "SELECT s.id, s.name, s.code 
             FROM class_subjects cs 
             JOIN subjects s ON cs.subject_id = s.id 
             WHERE cs.class_id = ? ORDER BY s.name",
            [$classId]
        );

        // Current schedule
        $schedules = Database::fetchAll(
            "SELECT * FROM exam_schedules WHERE exam_id = ? AND class_id = ?",
            [$examId, $classId]
        );
        $scheduleMap = [];
        foreach ($schedules as $s) {
            $scheduleMap[$s['subject_id']] = $s;
        }

        Response::view('exams/schedule', [
            'pageTitle' => $exam['name'] . ' Schedule',
            'exam' => $exam,
            'classes' => $classes,
            'sections' => $sections,
            'classId' => $classId,
            'subjects' => $subjects,
            'scheduleMap' => $scheduleMap,
            'breadcrumbs' => [
                ['label' => 'Exams', 'url' => APP_URL . '/exams'],
                ['label' => 'Schedule']
            ]
        ]);
    }

    public function saveSchedule($examId)
    {
        $schoolId = $this->getSchoolId();
        $exam = Database::fetch("SELECT * FROM exams WHERE id = ? AND school_id = ?", [$examId, $schoolId]);
        if (!$exam) { Response::abort(404); return; }

        $classId = (int)$_POST['class_id'];
        
        // Clear existing
        Database::delete('exam_schedules', 'exam_id = ? AND class_id = ?', [$examId, $classId]);

        $dates = $_POST['exam_date'] ?? [];
        $startTimes = $_POST['start_time'] ?? [];
        $endTimes = $_POST['end_time'] ?? [];
        $maxMarks = $_POST['max_marks'] ?? [];
        $passingMarks = $_POST['passing_marks'] ?? [];
        $rooms = $_POST['room_no'] ?? [];

        foreach ($dates as $subjectId => $date) {
            if (empty($date)) continue;
            
            Database::insert('exam_schedules', [
                'exam_id' => $examId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'exam_date' => $date,
                'start_time' => !empty($startTimes[$subjectId]) ? $startTimes[$subjectId] : null,
                'end_time' => !empty($endTimes[$subjectId]) ? $endTimes[$subjectId] : null,
                'max_marks' => !empty($maxMarks[$subjectId]) ? $maxMarks[$subjectId] : 100,
                'passing_marks' => !empty($passingMarks[$subjectId]) ? $passingMarks[$subjectId] : 33,
                'room_no' => !empty($rooms[$subjectId]) ? $rooms[$subjectId] : null,
            ]);
        }

        Session::flash('success', 'Exam schedule saved.');
        Response::redirect("exams/schedule/{$examId}?class_id={$classId}");
    }

    // ─── Exam Marks ──────────────────────────────────────────
    public function marks($examId)
    {
        $schoolId = $this->getSchoolId();
        $exam = Database::fetch("SELECT * FROM exams WHERE id = ? AND school_id = ?", [$examId, $schoolId]);
        if (!$exam) { Response::abort(404); return; }

        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
        $subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

        // Fetch classes that have a schedule for this exam
        $classes = Database::fetchAll(
            "SELECT DISTINCT c.* 
             FROM exam_schedules es
             JOIN classes c ON es.class_id = c.id
             WHERE es.exam_id = ? ORDER BY c.name",
            [$examId]
        );

        if ($classId == 0 && !empty($classes)) {
            $classId = $classes[0]['id'];
        }

        // Fetch subjects that have a schedule for this exam and class
        $subjects = [];
        $schedule = null;
        if ($classId > 0) {
            $subjects = Database::fetchAll(
                "SELECT s.*, es.id as schedule_id, es.max_marks 
                 FROM exam_schedules es
                 JOIN subjects s ON es.subject_id = s.id
                 WHERE es.exam_id = ? AND es.class_id = ? ORDER BY s.name",
                [$examId, $classId]
            );
            
            if ($subjectId == 0 && !empty($subjects)) {
                $subjectId = $subjects[0]['id'];
            }

            foreach ($subjects as $s) {
                if ($s['id'] == $subjectId) {
                    $schedule = $s;
                    break;
                }
            }
        }

        $students = [];
        $marksMap = [];
        if ($classId > 0 && $schedule) {
            // Fetch all active students in this class
            $students = Database::fetchAll(
                "SELECT u.id, u.full_name, sd.admission_no, sd.roll_number 
                 FROM users u
                 JOIN student_details sd ON u.id = sd.user_id
                 WHERE sd.class_id = ? AND u.is_active = 1 AND u.school_id = ?
                 ORDER BY sd.roll_number, u.full_name",
                [$classId, $schoolId]
            );

            // Fetch existing marks
            $marks = Database::fetchAll(
                "SELECT * FROM exam_marks WHERE exam_schedule_id = ?",
                [$schedule['schedule_id']]
            );
            foreach ($marks as $m) {
                $marksMap[$m['student_id']] = $m;
            }
        }

        Response::view('exams/marks', [
            'pageTitle' => 'Enter Marks: ' . $exam['name'],
            'exam' => $exam,
            'classes' => $classes,
            'classId' => $classId,
            'subjects' => $subjects,
            'subjectId' => $subjectId,
            'schedule' => $schedule,
            'students' => $students,
            'marksMap' => $marksMap,
            'breadcrumbs' => [
                ['label' => 'Exams', 'url' => APP_URL . '/exams'],
                ['label' => 'Enter Marks']
            ]
        ]);
    }

    public function saveMarks($examId)
    {
        $schoolId = $this->getSchoolId();
        $classId = (int)$_POST['class_id'];
        $subjectId = (int)$_POST['subject_id'];
        $scheduleId = (int)$_POST['schedule_id'];

        $marks = $_POST['marks'] ?? [];
        $absents = $_POST['is_absent'] ?? [];
        $remarks = $_POST['remarks'] ?? [];

        foreach ($marks as $studentId => $mark) {
            $isAbsent = isset($absents[$studentId]) ? 1 : 0;
            $markValue = ($isAbsent || $mark === '') ? null : (float)$mark;
            $remark = $remarks[$studentId] ?? null;

            $existing = Database::fetch(
                "SELECT id FROM exam_marks WHERE exam_schedule_id = ? AND student_id = ?",
                [$scheduleId, $studentId]
            );

            if ($existing) {
                Database::update('exam_marks', [
                    'marks_obtained' => $markValue,
                    'is_absent' => $isAbsent,
                    'remarks' => $remark
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('exam_marks', [
                    'exam_schedule_id' => $scheduleId,
                    'student_id' => $studentId,
                    'marks_obtained' => $markValue,
                    'is_absent' => $isAbsent,
                    'remarks' => $remark
                ]);
            }
        }

        Session::flash('success', 'Marks saved successfully.');
        Response::redirect("exams/marks/{$examId}?class_id={$classId}&subject_id={$subjectId}");
    }
}
