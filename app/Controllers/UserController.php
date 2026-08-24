<?php
/**
 * User Controller
 * Manages user CRUD operations
 */

require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Services/EmailService.php';

class UserController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * List users
     */
    public function index(): void
    {
        if (!Session::hasPermission('users.view')) {
            Response::abort(403);
        }

        $filters = [
            'search'    => Validator::input('search'),
            'user_type' => Validator::input('user_type'),
            'is_active' => Validator::input('is_active'),
        ];

        // Non-super-admin can only see their school's users
        if (Session::userRole() !== ROLE_SUPER_ADMIN) {
            $filters['school_id'] = Session::schoolId();
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = User::getAll($filters, $page);

        Response::view('users.list', [
            'pageTitle' => 'User Management',
            'users'     => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'filters'   => $filters,
        ]);
    }

    /**
     * Show create user form
     */
    public function create(): void
    {
        if (!Session::hasPermission('users.create')) {
            Response::abort(403);
        }

        $schools = [];
        if (Session::userRole() === ROLE_SUPER_ADMIN) {
            $schools = Database::fetchAll("SELECT id, name FROM schools WHERE is_active = 1 ORDER BY name");
        }

        $roles = Database::fetchAll("SELECT id, name, slug FROM roles WHERE is_active = 1 ORDER BY name");

        Response::view('users.form', [
            'pageTitle' => 'Add New User',
            'user'      => null,
            'schools'   => $schools,
            'roles'     => $roles,
        ]);
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        if (!Session::hasPermission('users.create')) {
            Response::abort(403);
        }

        $data = Validator::postData();
        
        $validator = Validator::make($_POST)
            ->required('full_name', 'Full Name')
            ->required('email', 'Email')
            ->email('email')
            ->unique('email', 'users')
            ->required('username', 'Username')
            ->required('user_type', 'User Type')
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password');

        if ($validator->fails()) {
            Session::flash('error', implode('<br>', $validator->allErrors()));
            Response::back();
        }

        // Set school_id
        $schoolId = null;
        if ($data['user_type'] !== 'super_admin') {
            $schoolId = Session::userRole() === ROLE_SUPER_ADMIN 
                ? ($data['school_id'] ?? null) 
                : Session::schoolId();
        }

        try {
            Database::beginTransaction();

            $userId = User::create([
                'school_id'  => $schoolId,
                'username'   => $data['username'],
                'email'      => $data['email'],
                'password'   => $data['password'],
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'] ?? null,
                'gender'     => $data['gender'] ?? null,
                'user_type'  => $data['user_type'],
                'is_active'  => 1,
                'force_password_change' => 1,
                'created_by' => Session::userId(),
            ]);

            // Assign role
            User::assignRole($userId, $data['user_type']);

            Database::commit();

            // Send welcome email
            try {
                $emailService = new EmailService();
                $emailService->sendWelcome($data['email'], $data['full_name'], $data['password']);
            } catch (Exception $e) {
                // Email failure shouldn't block user creation
            }

            Session::flash('success', 'User created successfully.');
            Response::redirect('users');

        } catch (Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed to create user: ' . $e->getMessage());
            Response::back();
        }
    }

    /**
     * Show edit form
     */
    public function edit($id): void
    {
        if (!Session::hasPermission('users.edit')) {
            Response::abort(403);
        }

        $user = User::find((int)$id);
        if (!$user) {
            Response::abort(404, 'User not found');
        }

        // School-level users can only edit their own school's users
        if (Session::userRole() !== ROLE_SUPER_ADMIN && $user['school_id'] !== Session::schoolId()) {
            Response::abort(403);
        }

        $schools = [];
        if (Session::userRole() === ROLE_SUPER_ADMIN) {
            $schools = Database::fetchAll("SELECT id, name FROM schools WHERE is_active = 1 ORDER BY name");
        }

        $roles = Database::fetchAll("SELECT id, name, slug FROM roles WHERE is_active = 1 ORDER BY name");

        Response::view('users.form', [
            'pageTitle' => 'Edit User',
            'user'      => $user,
            'schools'   => $schools,
            'roles'     => $roles,
        ]);
    }

    /**
     * Update user
     */
    public function update($id): void
    {
        if (!Session::hasPermission('users.edit')) {
            Response::abort(403);
        }

        $data = Validator::postData();
        $id = (int)$id;
        
        $validator = Validator::make($_POST)
            ->required('full_name', 'Full Name')
            ->required('email', 'Email')
            ->email('email')
            ->unique('email', 'users', 'email', $id)
            ->required('username', 'Username');

        if ($validator->fails()) {
            Session::flash('error', implode('<br>', $validator->allErrors()));
            Response::back();
        }

        $updateData = [
            'full_name'  => $data['full_name'],
            'email'      => $data['email'],
            'username'   => $data['username'],
            'phone'      => $data['phone'] ?? null,
            'gender'     => $data['gender'] ?? null,
            'is_active'  => isset($data['is_active']) ? 1 : 0,
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        User::update($id, $updateData);

        // Update role if changed
        if (!empty($data['user_type'])) {
            User::assignRole($id, $data['user_type']);
            Database::update('users', ['user_type' => $data['user_type']], 'id = ?', [$id]);
        }

        Session::flash('success', 'User updated successfully.');
        Response::redirect('users');
    }

    /**
     * Delete user
     */
    public function delete($id): void
    {
        if (!Session::hasPermission('users.delete')) {
            Response::abort(403);
        }

        $id = (int)$id;
        
        // Can't delete yourself
        if ($id === Session::userId()) {
            Session::flash('error', 'You cannot delete your own account.');
            Response::back();
        }

        User::delete($id);
        
        Session::flash('success', 'User deactivated successfully.');
        Response::redirect('users');
    }

    /**
     * View user profile
     */
    public function show($id): void
    {
        $user = User::findWithSchool((int)$id);
        if (!$user) {
            Response::abort(404, 'User not found');
        }

        $activityLogs = Database::fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [$id]
        );

        // Fee data for students
        $feeStructure = [];
        $feePayments = [];
        $feeTotalPaid = 0;
        $feeTotalDue = 0;

        if (($user['user_type'] ?? '') === 'student') {
            $schoolId = $user['school_id'] ?? null;
            $classId = $user['class_id'] ?? null;

            if ($schoolId && $classId) {
                // Get current academic year
                $currentYear = Database::fetch(
                    "SELECT id FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
                    [$schoolId]
                );
                $yearId = $currentYear['id'] ?? 0;

                // Fee structure for student's class
                $feeStructure = Database::fetchAll(
                    "SELECT fs.*, fh.name as head_name, fh.code as head_code, fh.type as head_type
                     FROM fee_structures fs
                     JOIN fee_heads fh ON fs.fee_head_id = fh.id
                     WHERE fs.school_id = ? AND fs.academic_year_id = ? AND fs.class_id = ? AND fs.is_active = 1
                     ORDER BY fh.name",
                    [$schoolId, $yearId, $classId]
                );

                // Paid amounts per head
                $paidByHead = Database::fetchAll(
                    "SELECT fpi.fee_head_id, SUM(fpi.amount) as paid
                     FROM fee_payment_items fpi
                     JOIN fee_payments fp ON fpi.fee_payment_id = fp.id
                     WHERE fp.student_id = ? AND fp.school_id = ? AND fp.academic_year_id = ? AND fp.status = 'active'
                     GROUP BY fpi.fee_head_id",
                    [$id, $schoolId, $yearId]
                );
                $paidMap = [];
                foreach ($paidByHead as $p) { $paidMap[$p['fee_head_id']] = (float)$p['paid']; }

                // Calculate totals
                foreach ($feeStructure as &$fs) {
                    $fs['paid'] = $paidMap[$fs['fee_head_id']] ?? 0;
                    $fs['balance'] = max(0, $fs['amount'] - $fs['paid']);
                    $feeTotalPaid += $fs['paid'];
                    $feeTotalDue += $fs['balance'];
                }
                unset($fs);

                // Recent payments
                $feePayments = Database::fetchAll(
                    "SELECT fp.*, GROUP_CONCAT(fh.name SEPARATOR ', ') as head_names
                     FROM fee_payments fp
                     JOIN fee_payment_items fpi ON fp.id = fpi.fee_payment_id
                     JOIN fee_heads fh ON fpi.fee_head_id = fh.id
                     WHERE fp.student_id = ? AND fp.school_id = ? AND fp.status = 'active'
                     GROUP BY fp.id
                     ORDER BY fp.payment_date DESC
                     LIMIT 10",
                    [$id, $schoolId]
                );

                // Available discounts and applied concessions
                $availableDiscounts = Database::fetchAll(
                    "SELECT * FROM fee_discounts WHERE school_id = ? AND is_active = 1 ORDER BY name",
                    [$schoolId]
                );

                $appliedDiscounts = Database::fetchAll(
                    "SELECT sfc.*, fd.name as discount_name, fd.type as discount_type, fd.value as discount_value
                     FROM student_fee_concessions sfc
                     JOIN fee_discounts fd ON sfc.fee_discount_id = fd.id
                     WHERE sfc.student_id = ? AND sfc.school_id = ? AND sfc.academic_year_id = ? AND sfc.is_active = 1",
                    [$id, $schoolId, $yearId]
                );

                // Optional fee heads and student enrollments
                $optionalFeeHeads = Database::fetchAll(
                    "SELECT fh.* FROM fee_heads fh
                     WHERE fh.school_id = ? AND fh.type = 'optional' AND fh.is_active = 1 ORDER BY fh.name",
                    [$schoolId]
                );

                $enrolledOptionalFees = Database::fetchAll(
                    "SELECT sof.*, fh.name as head_name, fh.code as head_code
                     FROM student_optional_fees sof
                     JOIN fee_heads fh ON sof.fee_head_id = fh.id
                     WHERE sof.student_id = ? AND sof.school_id = ? AND sof.academic_year_id = ? AND sof.is_active = 1",
                    [$id, $schoolId, $yearId]
                );
            }
        }

        Response::view('users.profile', [
            'pageTitle'            => $user['full_name'],
            'profileUser'          => $user,
            'activityLogs'         => $activityLogs,
            'feeStructure'         => $feeStructure,
            'feePayments'          => $feePayments,
            'feeTotalPaid'         => $feeTotalPaid,
            'feeTotalDue'          => $feeTotalDue,
            'availableDiscounts'   => $availableDiscounts ?? [],
            'appliedDiscounts'     => $appliedDiscounts ?? [],
            'optionalFeeHeads'     => $optionalFeeHeads ?? [],
            'enrolledOptionalFees' => $enrolledOptionalFees ?? [],
        ]);
    }
}
