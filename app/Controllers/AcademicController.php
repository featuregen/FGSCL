<?php
/**
 * Academic Controller
 * Academic hub, subjects management, class-subject mapping
 */

class AcademicController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    private function getSchoolId(): ?int
    {
        return Session::schoolId();
    }

    private function getCurrentYear(int $schoolId): ?array
    {
        return Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
    }

    // ─── Hub / Index ────────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Classes with student and subject counts
        $classes = Database::fetchAll(
            "SELECT c.id, c.name, c.numeric_name,
                    (SELECT COUNT(*) FROM sections s WHERE s.class_id = c.id) as section_count,
                    (SELECT COUNT(*) FROM student_details sd WHERE sd.class_id = c.id AND sd.status = 'active') as student_count,
                    (SELECT COUNT(*) FROM class_subjects cs WHERE cs.class_id = c.id) as subject_count
             FROM classes c
             WHERE c.school_id = ? AND c.academic_year_id = ?
             ORDER BY c.numeric_name ASC",
            [$schoolId, $yearId]
        );

        // Total subjects
        $totalSubjects = Database::count('subjects', 'school_id = ? AND is_active = 1', [$schoolId]);

        // All academic years
        $years = Database::fetchAll(
            "SELECT * FROM academic_years WHERE school_id = ? ORDER BY start_date DESC",
            [$schoolId]
        );

        Response::view('academic/index', [
            'pageTitle'     => 'Academic',
            'currentYear'   => $currentYear,
            'classes'       => $classes,
            'totalSubjects' => $totalSubjects,
            'years'         => $years,
            'breadcrumbs'   => [['label' => 'Academic']],
        ]);
    }

    // ─── Subjects List ──────────────────────────
    public function subjects()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $subjects = Database::fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM class_subjects cs WHERE cs.subject_id = s.id) as class_count
             FROM subjects s
             WHERE s.school_id = ?
             ORDER BY s.name ASC",
            [$schoolId]
        );

        Response::view('academic/subjects', [
            'pageTitle'   => 'Subjects',
            'subjects'    => $subjects,
            'breadcrumbs' => [
                ['label' => 'Academic', 'url' => APP_URL . '/academic'],
                ['label' => 'Subjects'],
            ],
        ]);
    }

    // ─── Store Subject ──────────────────────────
    public function storeSubject()
    {
        $schoolId = $this->getSchoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'theory';

        if (empty($name)) {
            Session::flash('error', 'Subject name is required.');
            Response::redirect('academic/subjects');
            return;
        }

        try {
            Database::insert('subjects', [
                'school_id' => $schoolId,
                'name'      => $name,
                'code'      => $code ?: null,
                'type'      => $type,
            ]);
            Session::flash('success', "Subject \"{$name}\" added.");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                Session::flash('error', "Subject \"{$name}\" already exists.");
            } else {
                Session::flash('error', 'Error: ' . $e->getMessage());
            }
        }
        Response::redirect('academic/subjects');
    }

    // ─── Update Subject ─────────────────────────
    public function updateSubject($id)
    {
        $schoolId = $this->getSchoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'theory';

        if (empty($name)) {
            Session::flash('error', 'Subject name is required.');
            Response::redirect('academic/subjects');
            return;
        }

        try {
            Database::update('subjects', [
                'name' => $name,
                'code' => $code ?: null,
                'type' => $type,
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', "Subject \"{$name}\" updated.");
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }
        Response::redirect('academic/subjects');
    }

    // ─── Delete Subject ─────────────────────────
    public function deleteSubject($id)
    {
        $schoolId = $this->getSchoolId();
        Database::pdo()->prepare("DELETE FROM subjects WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        Session::flash('success', 'Subject deleted.');
        Response::redirect('academic/subjects');
    }

    // ─── Toggle Subject Active ──────────────────
    public function toggleSubject($id)
    {
        $schoolId = $this->getSchoolId();
        $subject = Database::fetch("SELECT * FROM subjects WHERE id = ? AND school_id = ?", [$id, $schoolId]);
        if ($subject) {
            $newStatus = $subject['is_active'] ? 0 : 1;
            Database::update('subjects', ['is_active' => $newStatus], 'id = ?', [$id]);
            Session::flash('success', $newStatus ? 'Subject activated.' : 'Subject deactivated.');
        }
        Response::redirect('academic/subjects');
    }

    // ─── Class Subjects (assign subjects to a class) ─────
    public function classSubjects($classId)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $class = Database::fetch(
            "SELECT c.*, ay.name as year_name FROM classes c JOIN academic_years ay ON c.academic_year_id = ay.id WHERE c.id = ? AND c.school_id = ?",
            [$classId, $schoolId]
        );
        if (!$class) { Response::abort(404); return; }

        // All active subjects
        $allSubjects = Database::fetchAll(
            "SELECT * FROM subjects WHERE school_id = ? AND is_active = 1 ORDER BY name",
            [$schoolId]
        );

        // Already assigned
        $assigned = Database::fetchAll(
            "SELECT cs.*, s.name as subject_name, s.code as subject_code, s.type as subject_type,
                    u.full_name as teacher_name
             FROM class_subjects cs
             JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN users u ON cs.teacher_id = u.id
             WHERE cs.class_id = ?
             ORDER BY s.name",
            [$classId]
        );
        $assignedIds = array_column($assigned, 'subject_id');

        // Teachers
        $teachers = Database::fetchAll(
            "SELECT id, full_name FROM users WHERE school_id = ? AND user_type = 'teacher' AND is_active = 1 ORDER BY full_name",
            [$schoolId]
        );

        Response::view('academic/class-subjects', [
            'pageTitle'    => $class['name'] . ' — Subjects',
            'class'        => $class,
            'allSubjects'  => $allSubjects,
            'assigned'     => $assigned,
            'assignedIds'  => $assignedIds,
            'teachers'     => $teachers,
            'breadcrumbs'  => [
                ['label' => 'Academic', 'url' => APP_URL . '/academic'],
                ['label' => $class['name'] . ' Subjects'],
            ],
        ]);
    }

    // ─── Assign Subject to Class ────────────────
    public function assignSubject()
    {
        $classId = intval($_POST['class_id'] ?? 0);
        $subjectId = intval($_POST['subject_id'] ?? 0);
        $teacherId = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
        $periods = max(1, intval($_POST['periods_per_week'] ?? 5));

        try {
            Database::insert('class_subjects', [
                'class_id'        => $classId,
                'subject_id'      => $subjectId,
                'teacher_id'      => $teacherId,
                'periods_per_week'=> $periods,
            ]);
            Session::flash('success', 'Subject assigned to class.');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                Session::flash('error', 'Subject already assigned to this class.');
            } else {
                Session::flash('error', 'Error: ' . $e->getMessage());
            }
        }
        Response::redirect('academic/class-subjects/' . $classId);
    }

    // ─── Remove Subject from Class ──────────────
    public function removeSubject($id)
    {
        $cs = Database::fetch("SELECT class_id FROM class_subjects WHERE id = ?", [$id]);
        Database::pdo()->prepare("DELETE FROM class_subjects WHERE id = ?")->execute([$id]);
        Session::flash('success', 'Subject removed from class.');
        Response::redirect('academic/class-subjects/' . ($cs['class_id'] ?? ''));
    }

    // ─── Update Subject-Teacher Assignment ──────
    public function updateAssignment($id)
    {
        $teacherId = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
        $periods = max(1, intval($_POST['periods_per_week'] ?? 5));

        $cs = Database::fetch("SELECT class_id FROM class_subjects WHERE id = ?", [$id]);
        Database::update('class_subjects', [
            'teacher_id'      => $teacherId,
            'periods_per_week'=> $periods,
        ], 'id = ?', [$id]);

        Session::flash('success', 'Assignment updated.');
        Response::redirect('academic/class-subjects/' . ($cs['class_id'] ?? ''));
    }
}
