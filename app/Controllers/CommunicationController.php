<?php
/**
 * Communication Controller
 * Send SMS, Email, and Notices
 */

class CommunicationController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) { Response::redirect('auth/login'); }
    }

    private function getSchoolId(): ?int { return Session::schoolId(); }

    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $communications = Database::fetchAll(
            "SELECT c.*, u.full_name as sent_by_name
             FROM communications c
             JOIN users u ON c.sent_by = u.id
             WHERE c.school_id = ?
             ORDER BY c.created_at DESC",
            [$schoolId]
        );

        Response::view('communication/index', [
            'pageTitle' => 'Noticeboard & Alerts',
            'communications' => $communications,
            'breadcrumbs' => [['label' => 'Communication']]
        ]);
    }

    public function create()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $year = Database::fetch("SELECT id FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $classes = Database::fetchAll("SELECT * FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name", [$schoolId, $year['id'] ?? 0]);

        Response::view('communication/compose', [
            'pageTitle' => 'Compose Message',
            'classes' => $classes,
            'breadcrumbs' => [['label' => 'Communication', 'url' => APP_URL . '/communication'], ['label' => 'Compose']]
        ]);
    }

    public function store()
    {
        $schoolId = $this->getSchoolId();
        
        $type = $_POST['type'] ?? 'notice';
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $targetRoles = isset($_POST['target_roles']) ? $_POST['target_roles'] : ['student'];
        $targetClasses = !empty($_POST['target_classes']) ? $_POST['target_classes'] : null;

        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = 'public/uploads/communication/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                $attachment = $fileName;
            }
        }

        $commId = Database::insert('communications', [
            'school_id' => $schoolId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'attachment' => $attachment,
            'target_roles' => json_encode($targetRoles),
            'target_classes' => $targetClasses ? json_encode($targetClasses) : null,
            'sent_by' => Session::userId()
        ]);

        // Logic to dispatch SMS/Email or create Notice recipients would go here
        // For notices, we just broadcast it to the Noticeboard.

        Session::flash('success', 'Message broadcast successfully.');
        Response::redirect('communication');
    }

    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        
        $comm = Database::fetch("SELECT attachment FROM communications WHERE id = ? AND school_id = ?", [$id, $schoolId]);
        if ($comm && $comm['attachment']) {
            @unlink('public/uploads/communication/' . $comm['attachment']);
        }
        
        Database::delete('communications', 'id = ? AND school_id = ?', [$id, $schoolId]);
        
        Session::flash('success', 'Message deleted.');
        Response::redirect('communication');
    }
}
