<?php
/**
 * Homework Controller
 * Assignments and student submissions
 */

class HomeworkController
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

    // ─── List Homework ──────────────────────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $yearId = $this->getCurrentAcademicYear($schoolId)['id'] ?? 0;
        $role = Session::userRole();
        $userId = Session::userId();

        $where = "h.school_id = ? AND h.academic_year_id = ?";
        $params = [$schoolId, $yearId];

        // If teacher, show only their homework
        if ($role === 'teacher') {
            $where .= " AND h.created_by = ?";
            $params[] = $userId;
        }

        $homework = Database::fetchAll(
            "SELECT h.*, c.name as class_name, s.name as section_name, sub.name as subject_name, u.full_name as created_by_name
             FROM homework h
             JOIN classes c ON h.class_id = c.id
             LEFT JOIN sections s ON h.section_id = s.id
             JOIN subjects sub ON h.subject_id = sub.id
             JOIN users u ON h.created_by = u.id
             WHERE $where
             ORDER BY h.due_date DESC",
            $params
        );

        $classes = Database::fetchAll("SELECT * FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name", [$schoolId, $yearId]);
        $sections = Database::fetchAll("SELECT * FROM sections WHERE class_id IN (SELECT id FROM classes WHERE school_id = ? AND academic_year_id = ?)", [$schoolId, $yearId]);
        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE school_id = ? ORDER BY name", [$schoolId]);

        Response::view('homework/index', [
            'pageTitle' => 'Homework',
            'homework' => $homework,
            'classes' => $classes,
            'sections' => $sections,
            'subjects' => $subjects,
            'breadcrumbs' => [['label' => 'Academics'], ['label' => 'Homework']]
        ]);
    }

    public function store()
    {
        $schoolId = $this->getSchoolId();
        $yearId = $this->getCurrentAcademicYear($schoolId)['id'] ?? 0;

        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = 'public/uploads/homework/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                $attachment = $fileName;
            }
        }

        $sectionIds = $_POST['section_ids'] ?? [];
        
        // If no sections selected, create one homework for all sections (section_id = null)
        if (empty($sectionIds)) {
            $sectionIds = [null];
        }

        foreach ($sectionIds as $sectionId) {
            Database::insert('homework', [
                'school_id' => $schoolId,
                'academic_year_id' => $yearId,
                'class_id' => $_POST['class_id'],
                'section_id' => $sectionId ?: null,
                'subject_id' => $_POST['subject_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'attachment' => $attachment,
                'assign_date' => $_POST['assign_date'],
                'due_date' => $_POST['due_date'],
                'created_by' => Session::userId()
            ]);
        }

        $count = count($sectionIds);
        Session::flash('success', "Homework created for {$count} section(s) successfully.");
        Response::redirect('homework');
    }

    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        
        $hw = Database::fetch("SELECT attachment FROM homework WHERE id = ? AND school_id = ?", [$id, $schoolId]);
        if ($hw && $hw['attachment']) {
            @unlink('public/uploads/homework/' . $hw['attachment']);
        }
        
        Database::delete('homework', 'id = ? AND school_id = ?', [$id, $schoolId]);
        
        Session::flash('success', 'Homework deleted.');
        Response::redirect('homework');
    }

    // ─── Submissions ──────────────────────────────────────────
    public function submissions($id)
    {
        $schoolId = $this->getSchoolId();
        $hw = Database::fetch(
            "SELECT h.*, c.name as class_name, s.name as section_name, sub.name as subject_name 
             FROM homework h
             JOIN classes c ON h.class_id = c.id
             LEFT JOIN sections s ON h.section_id = s.id
             JOIN subjects sub ON h.subject_id = sub.id
             WHERE h.id = ? AND h.school_id = ?",
            [$id, $schoolId]
        );
        if (!$hw) { Response::abort(404); return; }

        $submissions = Database::fetchAll(
            "SELECT hs.*, u.full_name, sd.roll_number
             FROM homework_submissions hs
             JOIN users u ON hs.student_id = u.id
             JOIN student_details sd ON u.id = sd.user_id
             WHERE hs.homework_id = ?
             ORDER BY sd.roll_number",
            [$id]
        );

        Response::view('homework/submissions', [
            'pageTitle' => 'Homework Submissions',
            'homework' => $hw,
            'submissions' => $submissions,
            'breadcrumbs' => [['label' => 'Homework', 'url' => APP_URL . '/homework'], ['label' => 'Submissions']]
        ]);
    }
}
