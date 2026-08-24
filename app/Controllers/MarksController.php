<?php
/**
 * Marks Controller
 * Entry of marks by teachers
 */

class MarksController
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

    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $yearId = $this->getCurrentAcademicYear($schoolId)['id'] ?? 0;

        $exams = Database::fetchAll("SELECT * FROM exams WHERE school_id = ? AND academic_year_id = ? ORDER BY start_date DESC", [$schoolId, $yearId]);
        
        $examId = $_GET['exam_id'] ?? ($exams[0]['id'] ?? 0);
        $classId = $_GET['class_id'] ?? 0;
        $sectionId = $_GET['section_id'] ?? 0;
        $subjectId = $_GET['subject_id'] ?? 0;

        $classes = Database::fetchAll("SELECT * FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name", [$schoolId, $yearId]);
        $sections = $classId ? Database::fetchAll("SELECT * FROM sections WHERE class_id = ? ORDER BY name", [$classId]) : [];
        $subjects = $classId ? Database::fetchAll(
            "SELECT s.id, s.name FROM class_subjects cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.class_id = ? ORDER BY s.name", 
            [$classId]
        ) : [];

        $students = [];
        $schedule = null;
        $marksMap = [];

        if ($examId && $classId && $sectionId && $subjectId) {
            // Get students
            $students = Database::fetchAll(
                "SELECT u.id, u.full_name, sd.roll_number 
                 FROM users u 
                 JOIN student_details sd ON u.id = sd.user_id 
                 WHERE sd.class_id = ? AND sd.section_id = ? AND sd.school_id = ? AND u.is_active = 1
                 ORDER BY sd.roll_number, u.full_name",
                [$classId, $sectionId, $schoolId]
            );

            // Get schedule
            $schedule = Database::fetch(
                "SELECT * FROM exam_schedules WHERE exam_id = ? AND class_id = ? AND subject_id = ?",
                [$examId, $classId, $subjectId]
            );

            if ($schedule) {
                // Get existing marks
                $marks = Database::fetchAll(
                    "SELECT * FROM exam_marks WHERE exam_schedule_id = ?",
                    [$schedule['id']]
                );
                foreach ($marks as $m) {
                    $marksMap[$m['student_id']] = $m;
                }
            }
        }

        Response::view('marks/index', [
            'pageTitle' => 'Marks Entry',
            'exams' => $exams,
            'classes' => $classes,
            'sections' => $sections,
            'subjects' => $subjects,
            'examId' => $examId,
            'classId' => $classId,
            'sectionId' => $sectionId,
            'subjectId' => $subjectId,
            'students' => $students,
            'schedule' => $schedule,
            'marksMap' => $marksMap,
            'breadcrumbs' => [['label' => 'Academics'], ['label' => 'Marks Entry']]
        ]);
    }

    public function store()
    {
        $schoolId = $this->getSchoolId();
        
        $examId = (int)$_POST['exam_id'];
        $classId = (int)$_POST['class_id'];
        $sectionId = (int)$_POST['section_id'];
        $subjectId = (int)$_POST['subject_id'];
        
        $schedule = Database::fetch(
            "SELECT * FROM exam_schedules WHERE exam_id = ? AND class_id = ? AND subject_id = ?",
            [$examId, $classId, $subjectId]
        );

        if (!$schedule) {
            Session::flash('error', 'Exam schedule not found for this subject.');
            Response::back();
            return;
        }

        $marks = $_POST['marks'] ?? [];
        $absent = $_POST['absent'] ?? [];
        $remarks = $_POST['remarks'] ?? [];

        foreach ($marks as $studentId => $markValue) {
            $isAbsent = isset($absent[$studentId]) ? 1 : 0;
            $mark = ($markValue === '' || $isAbsent) ? null : $markValue;
            
            // Check if exists
            $existing = Database::fetch(
                "SELECT id FROM exam_marks WHERE exam_schedule_id = ? AND student_id = ?",
                [$schedule['id'], $studentId]
            );

            if ($existing) {
                Database::update('exam_marks', [
                    'marks_obtained' => $mark,
                    'is_absent' => $isAbsent,
                    'remarks' => $remarks[$studentId] ?? null
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('exam_marks', [
                    'exam_schedule_id' => $schedule['id'],
                    'student_id' => $studentId,
                    'marks_obtained' => $mark,
                    'is_absent' => $isAbsent,
                    'remarks' => $remarks[$studentId] ?? null
                ]);
            }
        }

        Session::flash('success', 'Marks saved successfully.');
        Response::redirect("marks?exam_id={$examId}&class_id={$classId}&section_id={$sectionId}&subject_id={$subjectId}");
    }
}
