<?php
/**
 * Visitor Management Controller
 * Handles visitor check-in, check-out, printable gate passes, and security visit history
 */
class VisitorController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Visitor Dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'inside';
        $search = trim($_GET['search'] ?? '');
        $dateFilter = trim($_GET['date'] ?? '');

        // 1. Fetch Visitors currently inside
        $insideVisitors = Database::fetchAll(
            "SELECT vl.*, u.full_name as meet_staff_name
             FROM visitor_logs vl
             LEFT JOIN users u ON vl.to_meet_user_id = u.id
             WHERE vl.school_id = ? AND vl.status = 'inside'
             ORDER BY vl.in_time DESC",
            [$schoolId]
        );

        // 2. Fetch History with filters
        $histParams = [$schoolId];
        $histWhere = "vl.school_id = ?";
        if (!empty($search)) {
            $histWhere .= " AND (vl.visitor_name LIKE ? OR vl.phone LIKE ? OR vl.to_meet_name LIKE ? OR vl.purpose LIKE ?)";
            $s = "%{$search}%";
            $histParams = array_merge($histParams, [$s, $s, $s, $s]);
        }
        if (!empty($dateFilter)) {
            $histWhere .= " AND DATE(vl.in_time) = ?";
            $histParams[] = $dateFilter;
        }

        $history = Database::fetchAll(
            "SELECT vl.*, u.full_name as meet_staff_name, creator.full_name as gate_officer_name
             FROM visitor_logs vl
             LEFT JOIN users u ON vl.to_meet_user_id = u.id
             LEFT JOIN users creator ON vl.created_by = creator.id
             WHERE {$histWhere}
             ORDER BY vl.in_time DESC",
            $histParams
        );

        // 3. Staff list for "To Meet" dropdown
        $staffList = Database::fetchAll(
            "SELECT u.id, u.full_name, r.name as role_name, d.name as department_name
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             WHERE u.school_id = ? AND u.is_active = 1 AND r.slug NOT IN ('student', 'parent_user')
             ORDER BY u.full_name ASC",
            [$schoolId]
        );

        // Stats
        $todayVisits = Database::fetch("SELECT COUNT(*) as cnt FROM visitor_logs WHERE school_id = ? AND DATE(in_time) = CURDATE()", [$schoolId])['cnt'] ?? 0;
        $monthVisits = Database::fetch("SELECT COUNT(*) as cnt FROM visitor_logs WHERE school_id = ? AND MONTH(in_time) = MONTH(CURDATE()) AND YEAR(in_time) = YEAR(CURDATE())", [$schoolId])['cnt'] ?? 0;

        $stats = [
            'currently_inside' => count($insideVisitors),
            'today_visits'     => $todayVisits,
            'month_visits'     => $monthVisits
        ];

        Response::view('visitors/index', [
            'pageTitle'      => 'Visitor Management',
            'breadcrumbs'    => [['label' => 'Visitors']],
            'tab'            => $tab,
            'insideVisitors' => $insideVisitors,
            'history'        => $history,
            'staffList'      => $staffList,
            'stats'          => $stats,
            'search'         => $search,
            'dateFilter'     => $dateFilter
        ]);
    }

    /**
     * Check-In a new Visitor
     */
    public function checkin()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $name       = trim($_POST['visitor_name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $idType     = trim($_POST['id_proof_type'] ?? 'National ID');
        $idNumber   = trim($_POST['id_proof_number'] ?? '');
        $purpose    = trim($_POST['purpose'] ?? 'General Visit');
        $meetUserId = !empty($_POST['to_meet_user_id']) ? (int)$_POST['to_meet_user_id'] : null;
        $meetName   = trim($_POST['to_meet_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $numPersons = max(1, (int)($_POST['number_of_persons'] ?? 1));
        $cardNo     = trim($_POST['visitor_card_no'] ?? ('PASS-' . rand(100, 999)));
        $remarks    = trim($_POST['remarks'] ?? '');

        if (empty($name) || empty($phone)) {
            Session::flash('error', 'Visitor name and phone number are required.');
            Response::redirect('visitors');
            return;
        }

        if ($meetUserId && empty($meetName)) {
            $staff = Database::fetch("SELECT full_name FROM users WHERE id = ?", [$meetUserId]);
            $meetName = $staff['full_name'] ?? 'Staff';
        }

        $id = Database::insert('visitor_logs', [
            'school_id'          => $schoolId,
            'visitor_name'       => $name,
            'phone'              => $phone,
            'email'              => $email,
            'id_proof_type'      => $idType,
            'id_proof_number'    => $idNumber,
            'purpose'            => $purpose,
            'to_meet_user_id'    => $meetUserId,
            'to_meet_name'       => $meetName,
            'department'         => $department,
            'number_of_persons'  => $numPersons,
            'visitor_card_no'    => $cardNo,
            'in_time'            => date('Y-m-d H:i:s'),
            'status'             => 'inside',
            'remarks'            => $remarks,
            'created_by'         => Session::userId()
        ]);

        Session::flash('success', "Visitor '{$name}' checked in successfully (Pass #{$cardNo}).");
        Response::redirect('visitors?tab=inside');
    }

    /**
     * Check-Out Visitor
     */
    public function checkout($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        Database::update('visitor_logs', [
            'status'   => 'exited',
            'out_time' => date('Y-m-d H:i:s')
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Visitor checked out.');
        Response::redirect('visitors?tab=inside');
    }

    /**
     * Printable Gate Pass / Badge View
     */
    public function pass($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_GET['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(404);
            return;
        }

        $visitor = Database::fetch(
            "SELECT vl.*, s.name as school_name, s.logo as school_logo, s.address as school_address, s.phone as school_phone
             FROM visitor_logs vl
             JOIN schools s ON vl.school_id = s.id
             WHERE vl.id = ? AND vl.school_id = ?",
            [$id, $schoolId]
        );

        if (!$visitor) {
            Response::abort(404);
            return;
        }

        Response::view('visitors/pass', [
            'visitor' => $visitor
        ]);
    }
}