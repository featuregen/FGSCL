<?php
/**
 * Certificate & ID Card Management Controller
 * Handles Transfer Certificates (TC), Bonafide, Character Certificates, and printable Student ID Cards
 */
class CertificateController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Certificate Hub
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'generators';

        // 1. Fetch Classes & Sections for Student Selection
        $classes = Database::fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM student_details WHERE class_id = c.id) as student_count
             FROM classes c
             WHERE c.school_id = ?
             ORDER BY c.name ASC",
            [$schoolId]
        );

        // 2. Fetch Issued Certificates History
        $issued = Database::fetchAll(
            "SELECT ic.*, u.full_name as student_name, c.name as class_name, sec.name as section_name,
                    issuer.full_name as issued_by_name
             FROM issued_certificates ic
             JOIN users u ON ic.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             LEFT JOIN users issuer ON ic.created_by = issuer.id
             WHERE ic.school_id = ?
             ORDER BY ic.issue_date DESC, ic.id DESC",
            [$schoolId]
        );

        // 3. Students list for autocomplete / selection
        $students = Database::fetchAll(
            "SELECT u.id, u.full_name, u.phone, u.gender, u.date_of_birth,
                    sd.admission_no, sd.admission_date, sd.roll_number,
                    c.id as class_id, c.name as class_name, 
                    sec.id as section_id, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.school_id = ? AND u.is_active = 1
             ORDER BY c.name ASC, sec.name ASC, u.full_name ASC",
            [$schoolId]
        );

        // Stats
        $tcCount = Database::fetch("SELECT COUNT(*) as cnt FROM issued_certificates WHERE school_id = ? AND certificate_type = 'tc'", [$schoolId])['cnt'] ?? 0;
        $bonafideCount = Database::fetch("SELECT COUNT(*) as cnt FROM issued_certificates WHERE school_id = ? AND certificate_type = 'bonafide'", [$schoolId])['cnt'] ?? 0;

        $stats = [
            'total_issued' => count($issued),
            'tc_count'     => $tcCount,
            'bonafide'     => $bonafideCount
        ];

        Response::view('certificates/index', [
            'pageTitle'    => 'Certificates & ID Cards',
            'breadcrumbs'  => [['label' => 'Certificates']],
            'tab'          => $tab,
            'classes'      => $classes,
            'issued'       => $issued,
            'students'     => $students,
            'stats'        => $stats
        ]);
    }

    /**
     * Generate & Print Transfer Certificate (TC)
     */
    public function generateTc()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $studentId   = (int)($_POST['student_id'] ?? 0);
        $tcNo        = trim($_POST['tc_no'] ?? ('TC-' . date('Y') . '-' . rand(100, 999)));
        $issueDate   = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $leavingDate = trim($_POST['leaving_date'] ?? date('Y-m-d'));
        $conduct     = trim($_POST['conduct'] ?? 'Good');
        $reason      = trim($_POST['reason_leaving'] ?? 'Course Completed / Parent Transfer');
        $qualified   = trim($_POST['qualified_promotion'] ?? 'Yes');
        $remarks     = trim($_POST['remarks'] ?? '');

        $student = Database::fetch(
            "SELECT u.*, sd.admission_no, sd.admission_date, sd.roll_number, sd.father_name, sd.mother_name,
                    c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.id = ? AND u.school_id = ?",
            [$studentId, $schoolId]
        );

        if (!$student) {
            Session::flash('error', 'Student record not found.');
            Response::redirect('certificates');
            return;
        }

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);

        $dataJson = json_encode([
            'tc_no'               => $tcNo,
            'issue_date'          => $issueDate,
            'leaving_date'        => $leavingDate,
            'conduct'             => $conduct,
            'reason_leaving'      => $reason,
            'qualified_promotion' => $qualified,
            'remarks'             => $remarks,
            'student_name'        => $student['full_name'],
            'admission_no'        => $student['admission_no'],
            'admission_date'      => $student['admission_date'],
            'dob'                 => $student['date_of_birth'],
            'gender'              => $student['gender'],
            'father_name'         => $student['father_name'] ?? 'N/A',
            'mother_name'         => $student['mother_name'] ?? 'N/A',
            'class_name'          => $student['class_name'] ?? '',
            'section_name'        => $student['section_name'] ?? ''
        ]);

        // Record in issued certificates table
        Database::insert('issued_certificates', [
            'school_id'        => $schoolId,
            'certificate_type' => 'tc',
            'certificate_no'   => $tcNo,
            'student_id'       => $studentId,
            'issue_date'       => $issueDate,
            'data_json'        => $dataJson,
            'created_by'       => Session::userId()
        ]);

        Response::view('certificates/tc-template', [
            'school'  => $school,
            'student' => $student,
            'cert'    => json_decode($dataJson, true)
        ]);
    }

    /**
     * Generate & Print Bonafide / Character Certificate
     */
    public function generateBonafide()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $studentId = (int)($_POST['student_id'] ?? 0);
        $type      = $_POST['cert_type'] ?? 'bonafide'; // 'bonafide' or 'character' or 'study'
        $certNo    = trim($_POST['cert_no'] ?? (strtoupper(substr($type, 0, 3)) . '-' . date('Y') . '-' . rand(100, 999)));
        $issueDate = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $purpose   = trim($_POST['purpose'] ?? 'General Purpose / Official Use');
        $academicYr= trim($_POST['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1)));

        $student = Database::fetch(
            "SELECT u.*, sd.admission_no, sd.admission_date, sd.father_name,
                    c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.id = ? AND u.school_id = ?",
            [$studentId, $schoolId]
        );

        if (!$student) {
            Session::flash('error', 'Student record not found.');
            Response::redirect('certificates');
            return;
        }

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);

        $dataJson = json_encode([
            'cert_no'       => $certNo,
            'cert_type'     => $type,
            'issue_date'    => $issueDate,
            'purpose'       => $purpose,
            'academic_year' => $academicYr,
            'student_name'  => $student['full_name'],
            'admission_no'  => $student['admission_no'],
            'father_name'   => $student['father_name'] ?? 'N/A',
            'class_name'    => $student['class_name'] ?? '',
            'section_name'  => $student['section_name'] ?? ''
        ]);

        Database::insert('issued_certificates', [
            'school_id'        => $schoolId,
            'certificate_type' => $type,
            'certificate_no'   => $certNo,
            'student_id'       => $studentId,
            'issue_date'       => $issueDate,
            'data_json'        => $dataJson,
            'created_by'       => Session::userId()
        ]);

        Response::view('certificates/bonafide-template', [
            'school'  => $school,
            'student' => $student,
            'cert'    => json_decode($dataJson, true)
        ]);
    }

    /**
     * Batch Student ID Card Generator
     */
    public function generateIdCard()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $classId   = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $sectionId = !empty($_GET['section_id']) ? (int)$_GET['section_id'] : null;

        $params = [$schoolId];
        $where = "u.school_id = ? AND u.is_active = 1";

        if ($classId) {
            $where .= " AND sd.class_id = ?";
            $params[] = $classId;
        }
        if ($sectionId) {
            $where .= " AND sd.section_id = ?";
            $params[] = $sectionId;
        }

        $students = Database::fetchAll(
            "SELECT u.*, sd.admission_no, sd.roll_number, sd.blood_group, sd.father_name, sd.emergency_contact,
                    c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE {$where}
             ORDER BY c.name ASC, sec.name ASC, sd.roll_number ASC, u.full_name ASC",
            $params
        );

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);

        Response::view('certificates/id-card-template', [
            'school'   => $school,
            'students' => $students
        ]);
    }
}