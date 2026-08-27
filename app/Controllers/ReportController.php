<?php
/**
 * Reports & Analytics Controller
 * Consolidated reporting center: Exam Progress Report Cards / Marksheets, Financial summaries, Attendance registers, and School strength
 */
class ReportController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Reports Hub
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'academic';

        // 1. Fetch Classes & Sections
        $classes = Database::fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM student_details WHERE class_id = c.id) as student_count
             FROM classes c
             WHERE c.school_id = ?
             ORDER BY c.name ASC",
            [$schoolId]
        );

        // 2. Fetch Exams for Report Cards
        $exams = Database::fetchAll(
            "SELECT * FROM exams WHERE school_id = ? ORDER BY start_date DESC",
            [$schoolId]
        );

        // 3. Stats calculation
        $totalStudents = Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE school_id = ? AND user_type = 'student' AND is_active = 1", [$schoolId])['cnt'] ?? 0;
        $totalStaff = Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE school_id = ? AND user_type IN ('staff', 'teacher') AND is_active = 1", [$schoolId])['cnt'] ?? 0;
        $totalCollected = Database::fetch("SELECT COALESCE(SUM(amount_paid), 0) as total FROM fee_payments WHERE school_id = ?", [$schoolId])['total'] ?? 0;

        $stats = [
            'total_students'  => $totalStudents,
            'total_staff'     => $totalStaff,
            'total_collected' => (float)$totalCollected,
            'total_exams'     => count($exams)
        ];

        // 4. Financial breakdown by payment mode
        $paymentModes = Database::fetchAll(
            "SELECT payment_mode, COUNT(*) as txn_count, SUM(amount_paid) as total_amount 
             FROM fee_payments 
             WHERE school_id = ? 
             GROUP BY payment_mode",
            [$schoolId]
        );

        // 5. Class-wise Fee summary
        $classFeeSummary = Database::fetchAll(
            "SELECT c.name as class_name, 
                    COUNT(DISTINCT sd.user_id) as student_count,
                    COALESCE(SUM(fp.amount_paid), 0) as total_collected
             FROM classes c
             LEFT JOIN student_details sd ON c.id = sd.class_id
             LEFT JOIN fee_payments fp ON sd.user_id = fp.student_id
             WHERE c.school_id = ?
             GROUP BY c.id
             ORDER BY c.name ASC",
            [$schoolId]
        );

        Response::view('reports/index', [
            'pageTitle'       => 'Reports & Analytics',
            'breadcrumbs'     => [['label' => 'Reports']],
            'tab'             => $tab,
            'classes'         => $classes,
            'exams'           => $exams,
            'stats'           => $stats,
            'paymentModes'    => $paymentModes,
            'classFeeSummary' => $classFeeSummary
        ]);
    }

    /**
     * Generate Printable Exam Progress Report Card / Marksheet
     */
    public function reportCard()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $examId    = (int)($_GET['exam_id'] ?? 0);
        $studentId = (int)($_GET['student_id'] ?? 0);
        $classId   = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;

        if (!$examId || !$studentId) {
            Session::flash('error', 'Please select both an Exam and a Student to generate a Report Card.');
            Response::redirect('reports?tab=academic');
            return;
        }

        // Fetch Exam
        $exam = Database::fetch("SELECT * FROM exams WHERE id = ? AND school_id = ?", [$examId, $schoolId]);
        if (!$exam) {
            Response::abort(404, 'Exam not found');
            return;
        }

        // Fetch Student
        $student = Database::fetch(
            "SELECT u.*, sd.admission_no, sd.roll_number, sd.father_name, sd.mother_name, sd.date_of_birth,
                    c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.id = ? AND u.school_id = ?",
            [$studentId, $schoolId]
        );

        if (!$student) {
            Response::abort(404, 'Student not found');
            return;
        }

        // Fetch Marks for this student in this exam
        $marks = Database::fetchAll(
            "SELECT em.*, s.name as subject_name, s.code as subject_code, es.max_marks, es.pass_marks
             FROM exam_marks em
             JOIN subjects s ON em.subject_id = s.id
             LEFT JOIN exam_schedules es ON (em.exam_id = es.exam_id AND em.subject_id = es.subject_id)
             WHERE em.exam_id = ? AND em.student_id = ?
             ORDER BY s.name ASC",
            [$examId, $studentId]
        );

        // Fetch Student Attendance percentage
        $attendance = Database::fetch(
            "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
             FROM attendance
             WHERE student_id = ? AND school_id = ?",
            [$studentId, $schoolId]
        );

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);

        Response::view('reports/report-card', [
            'school'     => $school,
            'exam'       => $exam,
            'student'    => $student,
            'marks'      => $marks,
            'attendance' => $attendance
        ]);
    }
}