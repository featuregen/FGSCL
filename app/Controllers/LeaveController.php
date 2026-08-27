<?php
/**
 * Leave Management Controller
 * Handles leave types, staff leave applications, approvals, and balances
 */
class LeaveController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Leave management main dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter   = $_GET['type_id'] ?? 'all';
        $userRole     = Session::userRole();
        $currentUserId = Session::userId();

        // Base query for requests
        $params = [$schoolId];
        $whereClause = "lr.school_id = ?";

        // If staff/teacher (non-admin), only show their own leaves unless they have full view permission
        $canManage = Session::hasPermission('leave.manage') || in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_SCHOOL_ADMIN, ROLE_PRINCIPAL]);
        if (!$canManage) {
            $whereClause .= " AND lr.user_id = ?";
            $params[] = $currentUserId;
        }

        if ($statusFilter !== 'all') {
            $whereClause .= " AND lr.status = ?";
            $params[] = $statusFilter;
        }

        if ($typeFilter !== 'all') {
            $whereClause .= " AND lr.leave_type_id = ?";
            $params[] = (int)$typeFilter;
        }

        $requests = Database::fetchAll(
            "SELECT lr.*, lt.name as type_name, lt.is_paid,
                    u.full_name as applicant_name, u.email as applicant_email,
                    r.name as role_name, d.name as department_name,
                    approver.full_name as approver_name
             FROM leave_requests lr
             JOIN leave_types lt ON lr.leave_type_id = lt.id
             JOIN users u ON lr.user_id = u.id
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             LEFT JOIN users approver ON lr.approved_by = approver.id
             WHERE {$whereClause}
             ORDER BY lr.created_at DESC",
            $params
        );

        // Fetch leave types
        $leaveTypes = Database::fetchAll(
            "SELECT lt.*, 
                    (SELECT COUNT(*) FROM leave_requests WHERE leave_type_id = lt.id AND status = 'approved') as approved_count
             FROM leave_types lt
             WHERE lt.school_id = ?
             ORDER BY lt.name ASC",
            [$schoolId]
        );

        // If no leave types exist yet, create default types automatically
        if (empty($leaveTypes)) {
            $defaultTypes = [
                ['name' => 'Casual Leave (CL)', 'code' => 'CL', 'days' => 12, 'is_paid' => 1, 'desc' => 'General short casual leaves'],
                ['name' => 'Sick Leave (SL)', 'code' => 'SL', 'days' => 10, 'is_paid' => 1, 'desc' => 'Medical and health related leaves'],
                ['name' => 'Earned Leave (EL)', 'code' => 'EL', 'days' => 15, 'is_paid' => 1, 'desc' => 'Privilege / earned annual leave'],
                ['name' => 'Maternity / Paternity', 'code' => 'ML', 'days' => 90, 'is_paid' => 1, 'desc' => 'Parental leave as per regulations'],
                ['name' => 'Unpaid Leave (LWP)', 'code' => 'LWP', 'days' => 30, 'is_paid' => 0, 'desc' => 'Leave without pay']
            ];
            foreach ($defaultTypes as $dt) {
                Database::insert('leave_types', [
                    'school_id'     => $schoolId,
                    'name'          => $dt['name'],
                    'code'          => $dt['code'],
                    'days_per_year' => $dt['days'],
                    'is_paid'       => $dt['is_paid'],
                    'description'   => $dt['desc'],
                    'status'        => 'active'
                ]);
            }
            $leaveTypes = Database::fetchAll("SELECT * FROM leave_types WHERE school_id = ? ORDER BY name ASC", [$schoolId]);
        }

        // Stats calculation
        $stats = [
            'pending'   => Database::fetch("SELECT COUNT(*) as cnt FROM leave_requests WHERE school_id = ? AND status = 'pending'", [$schoolId])['cnt'] ?? 0,
            'approved'  => Database::fetch("SELECT COUNT(*) as cnt FROM leave_requests WHERE school_id = ? AND status = 'approved' AND MONTH(from_date) = MONTH(CURRENT_DATE())", [$schoolId])['cnt'] ?? 0,
            'rejected'  => Database::fetch("SELECT COUNT(*) as cnt FROM leave_requests WHERE school_id = ? AND status = 'rejected'", [$schoolId])['cnt'] ?? 0,
            'on_leave'  => Database::fetch("SELECT COUNT(DISTINCT user_id) as cnt FROM leave_requests WHERE school_id = ? AND status = 'approved' AND CURDATE() BETWEEN from_date AND to_date", [$schoolId])['cnt'] ?? 0,
        ];

        // Fetch staff list for admin dropdown
        $staffList = [];
        if ($canManage) {
            $staffList = Database::fetchAll(
                "SELECT u.id, u.full_name, r.name as role_name 
                 FROM users u
                 JOIN user_roles ur ON u.id = ur.user_id
                 JOIN roles r ON ur.role_id = r.id
                 WHERE u.school_id = ? AND u.is_active = 1 AND r.slug NOT IN ('student', 'parent_user')
                 ORDER BY u.full_name ASC",
                [$schoolId]
            );
        }

        Response::view('leave/index', [
            'pageTitle'     => 'Leave Management',
            'breadcrumbs'   => [['label' => 'Leave Management']],
            'requests'      => $requests,
            'leaveTypes'    => $leaveTypes,
            'stats'         => $stats,
            'staffList'     => $staffList,
            'statusFilter'  => $statusFilter,
            'typeFilter'    => $typeFilter,
            'canManage'     => $canManage,
            'currentUserId' => $currentUserId
        ]);
    }

    /**
     * Submit leave application
     */
    public function apply()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $userId      = !empty($_POST['user_id']) && Session::hasPermission('leave.manage') ? (int)$_POST['user_id'] : Session::userId();
        $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
        $fromDate    = trim($_POST['from_date'] ?? '');
        $toDate      = trim($_POST['to_date'] ?? '');
        $reason      = trim($_POST['reason'] ?? '');

        if (!$leaveTypeId || !$fromDate || !$toDate || empty($reason)) {
            Session::flash('error', 'Please fill in all required fields (Leave Type, Dates, Reason).');
            Response::redirect('leave');
            return;
        }

        $start = strtotime($fromDate);
        $end   = strtotime($toDate);

        if ($start > $end) {
            Session::flash('error', 'End date cannot be earlier than start date.');
            Response::redirect('leave');
            return;
        }

        $days = (float)ceil(($end - $start) / 86400) + 1;

        // Check for existing overlapping request
        $overlap = Database::fetch(
            "SELECT id FROM leave_requests 
             WHERE school_id = ? AND user_id = ? AND status IN ('pending', 'approved') 
               AND ((from_date BETWEEN ? AND ?) OR (to_date BETWEEN ? AND ?) OR (? BETWEEN from_date AND to_date))
             LIMIT 1",
            [$schoolId, $userId, $fromDate, $toDate, $fromDate, $toDate, $fromDate]
        );

        if ($overlap) {
            Session::flash('error', 'You already have a pending or approved leave application for these dates.');
            Response::redirect('leave');
            return;
        }

        Database::insert('leave_requests', [
            'school_id'     => $schoolId,
            'user_id'       => $userId,
            'leave_type_id' => $leaveTypeId,
            'from_date'     => $fromDate,
            'to_date'       => $toDate,
            'total_days'    => $days,
            'reason'        => $reason,
            'status'        => 'pending'
        ]);

        Session::flash('success', 'Leave application submitted successfully.');
        Response::redirect('leave');
    }

    /**
     * Approve leave request
     */
    public function approve($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id || !Session::hasPermission('leave.manage')) {
            Session::flash('error', 'Unauthorized action.');
            Response::redirect('leave');
            return;
        }

        $reason = trim($_POST['action_reason'] ?? 'Approved by administration');

        Database::update('leave_requests', [
            'status'        => 'approved',
            'approved_by'   => Session::userId(),
            'action_reason' => $reason
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Leave application approved.');
        Response::redirect('leave');
    }

    /**
     * Reject leave request
     */
    public function reject($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id || !Session::hasPermission('leave.manage')) {
            Session::flash('error', 'Unauthorized action.');
            Response::redirect('leave');
            return;
        }

        $reason = trim($_POST['action_reason'] ?? 'Rejected by administration');

        Database::update('leave_requests', [
            'status'        => 'rejected',
            'approved_by'   => Session::userId(),
            'action_reason' => $reason
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('warning', 'Leave application rejected.');
        Response::redirect('leave');
    }

    /**
     * Save / Update Leave Type
     */
    public function saveType()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || !Session::hasPermission('leave.manage') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $daysPerYear = (int)($_POST['days_per_year'] ?? 12);
        $isPaid      = isset($_POST['is_paid']) ? 1 : 0;
        $desc        = trim($_POST['description'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'Leave Type name is required.');
            Response::redirect('leave');
            return;
        }

        $data = [
            'school_id'     => $schoolId,
            'name'          => $name,
            'code'          => $code,
            'days_per_year' => $daysPerYear,
            'is_paid'       => $isPaid,
            'description'   => $desc,
            'status'        => 'active'
        ];

        if ($id) {
            Database::update('leave_types', $data, 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Leave type updated successfully.');
        } else {
            Database::insert('leave_types', $data);
            Session::flash('success', 'New leave type created successfully.');
        }

        Response::redirect('leave');
    }

    /**
     * Delete Leave Type
     */
    public function deleteType($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id || !Session::hasPermission('leave.manage')) {
            Session::flash('error', 'Unauthorized action.');
            Response::redirect('leave');
            return;
        }

        // Check if any leave requests use this type
        $count = Database::fetch("SELECT COUNT(*) as cnt FROM leave_requests WHERE leave_type_id = ?", [$id])['cnt'] ?? 0;
        if ($count > 0) {
            Session::flash('error', 'Cannot delete this leave type because active leave requests are associated with it.');
            Response::redirect('leave');
            return;
        }

        Database::delete('leave_types', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Leave type deleted.');
        Response::redirect('leave');
    }
}