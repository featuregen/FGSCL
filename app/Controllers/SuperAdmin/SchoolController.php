<?php
/**
 * School Controller (Super Admin)
 * Manages school CRUD and subscriptions
 */

require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Services/ModuleService.php';

class SchoolController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
        // Only super admin can access
        if (Session::userRole() !== ROLE_SUPER_ADMIN) {
            Response::abort(403);
        }
    }

    /**
     * List all schools
     */
    public function index(): void
    {
        $search = Validator::input('search');
        $status = Validator::input('status');
        
        $where = ['1=1'];
        $params = [];
        
        if ($search) {
            $where[] = '(s.name LIKE ? OR s.code LIKE ? OR s.email LIKE ? OR s.city LIKE ?)';
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }
        if ($status !== '') {
            $where[] = 's.is_active = ?';
            $params[] = $status;
        }
        
        $schools = Database::fetchAll(
            "SELECT s.*, p.name as plan_name, p.pricing_type as plan_pricing_type,
                    sub.status as sub_status, sub.end_date, sub.amount as sub_amount, 
                    sub.student_count as sub_student_count, sub.pricing_type as sub_pricing_type,
                    (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.user_type = 'student' AND u.is_active = 1) as student_count,
                    (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.user_type = 'teacher' AND u.is_active = 1) as teacher_count
             FROM schools s 
             LEFT JOIN subscriptions sub ON s.id = sub.school_id AND sub.status = 'active'
             LEFT JOIN plans p ON sub.plan_id = p.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY s.created_at DESC",
            $params
        );
        
        Response::view('super-admin.schools', [
            'pageTitle' => 'School Management',
            'schools'   => $schools,
            'search'    => $search,
            'status'    => $status,
            'breadcrumb' => [
                ['label' => 'Schools'],
            ],
        ]);
    }

    /**
     * Show create school form
     */
    public function create(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order");
        
        $planDefaults = [];
        foreach ($plans as $plan) {
            $planDefaults[$plan['id']] = json_decode($plan['features'] ?? '[]', true) ?? [];
        }

        Response::view('super-admin.school-form', [
            'pageTitle'          => 'Add New School',
            'school'             => null,
            'plans'              => $plans,
            'modulesByCategory'  => ModuleService::getModulesByCategory(),
            'enabledModuleSlugs' => [],
            'planDefaultFeatures'=> $planDefaults,
            'breadcrumb'         => [
                ['label' => 'Schools', 'url' => 'schools'],
                ['label' => 'Add New'],
            ],
        ]);
    }

    /**
     * Store new school
     */
    public function store(): void
    {
        $data = Validator::postData();
        
        $validator = Validator::make($_POST)
            ->required('name', 'School Name')
            ->required('code', 'School Code')
            ->unique('code', 'schools')
            ->required('email', 'Email')
            ->email('email')
            ->required('phone', 'Phone')
            ->required('admin_name', 'Admin Name')
            ->required('admin_email', 'Admin Email')
            ->email('admin_email')
            ->required('admin_password', 'Admin Password')
            ->minLength('admin_password', 8, 'Admin Password');

        if ($validator->fails()) {
            Session::flash('error', implode('<br>', $validator->allErrors()));
            Response::back();
        }

        try {
            Database::beginTransaction();

            // Handle logo upload
            $logoPath = null;
            if (!empty($_FILES['logo']['name'])) {
                $uploadDir = UPLOAD_PATH . '/logos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $fileName = 'school_' . strtolower($data['code']) . '_' . time() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) {
                    $logoPath = $fileName;
                }
            }

            // Create school
            $schoolId = Database::insert('schools', [
                'name'            => $data['name'],
                'code'            => strtoupper($data['code']),
                'email'           => $data['email'],
                'phone'           => $data['phone'],
                'website'         => $data['website'] ?? null,
                'address'         => $data['address'] ?? null,
                'city'            => $data['city'] ?? null,
                'state'           => $data['state'] ?? null,
                'pincode'         => $data['pincode'] ?? null,
                'logo'            => $logoPath,
                'primary_color'   => $data['primary_color'] ?? '#4F46E5',
                'secondary_color' => $data['secondary_color'] ?? '#7C3AED',
                'tagline'         => $data['tagline'] ?? null,
                'board'           => $data['board'] ?? null,
                'school_type'     => $data['school_type'] ?? 'k12',
                'principal_name'  => $data['principal_name'] ?? null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            // Create school admin user
            $adminId = User::create([
                'school_id'  => $schoolId,
                'username'   => $data['admin_username'] ?? strtolower($data['code']) . '_admin',
                'email'      => $data['admin_email'],
                'password'   => $data['admin_password'],
                'full_name'  => $data['admin_name'],
                'phone'      => $data['admin_phone'] ?? null,
                'user_type'  => 'school_admin',
                'is_active'  => 1,
                'created_by' => Session::userId(),
            ]);

            User::assignRole($adminId, 'school_admin');

            // Create subscription if plan selected
            if (!empty($data['plan_id'])) {
                $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$data['plan_id']]);
                $billingCycle = $data['billing_cycle'] ?? 'monthly';
                $pricingType = $data['pricing_type'] ?? 'fixed';
                
                // End date based on billing cycle
                require_once APP_PATH . '/Services/BillingService.php';
                $cycleMonths = BillingService::getCycleMultiplier($billingCycle);
                $endDate = date('Y-m-d', strtotime("+{$cycleMonths} months"));

                // unit_price = the rate set by Super Admin
                $unitPrice = (float)($data['subscription_amount'] ?? 0);

                // Count active students (0 at creation time)
                $activeStudents = Database::count(
                    'users',
                    "school_id = ? AND user_type = 'student' AND is_active = 1",
                    [$schoolId]
                );

                // Total amount = unit_price × active students (for per_student)
                // For fixed pricing, amount = unit_price directly
                if ($pricingType === 'per_student') {
                    $amount = $unitPrice * $activeStudents; // ₹0 when no students
                } else {
                    $amount = $unitPrice;
                }

                // Free plan / trial override
                if ($plan['slug'] === 'free' || $unitPrice == 0) {
                    $endDate = date('Y-m-d', strtotime('+14 days'));
                    $amount = 0;
                    $unitPrice = 0;
                }

                Database::insert('subscriptions', [
                    'school_id'      => $schoolId,
                    'plan_id'        => $data['plan_id'],
                    'billing_cycle'  => $billingCycle,
                    'pricing_type'   => $pricingType,
                    'student_count'  => $activeStudents,
                    'unit_price'     => $unitPrice,
                    'start_date'     => date('Y-m-d'),
                    'end_date'       => $endDate,
                    'amount'         => $amount,
                    'payment_status' => $amount == 0 ? 'paid' : 'pending',
                    'status'         => 'active',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }

            // Save enabled modules for this school
            $selectedModules = $_POST['modules'] ?? [];
            if (!empty($selectedModules)) {
                require_once APP_PATH . '/Services/ModuleService.php';
                ModuleService::setSchoolModules($schoolId, $selectedModules, Session::userId());
            }

            Database::commit();

            // Send welcome email to school admin
            try {
                require_once APP_PATH . '/Services/EmailService.php';
                $emailService = new \EmailService();
                $emailService->sendWelcome($data['admin_email'], $data['admin_name'], $data['admin_password']);
            } catch (\Throwable $e) {
                // Email failure should not block school creation
            }

            Session::flash('success', "School '{$data['name']}' created successfully with admin account.");
            Response::redirect('schools');

        } catch (Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed to create school: ' . ($APP_DEBUG ? $e->getMessage() : 'Please try again.'));
            Response::back();
        }
    }

    /**
     * Edit school
     */
    public function edit($id): void
    {
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [(int)$id]);
        if (!$school) Response::abort(404);

        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order");
        $subscription = Database::fetch(
            "SELECT * FROM subscriptions WHERE school_id = ? ORDER BY created_at DESC LIMIT 1",
            [(int)$id]
        );
        
        $planDefaults = [];
        foreach ($plans as $plan) {
            $planDefaults[$plan['id']] = json_decode($plan['features'] ?? '[]', true) ?? [];
        }

        Response::view('super-admin.school-form', [
            'pageTitle'          => 'Edit School',
            'school'             => $school,
            'plans'              => $plans,
            'subscription'       => $subscription ?? null,
            'modulesByCategory'  => ModuleService::getModulesByCategory(),
            'enabledModuleSlugs' => ModuleService::getEnabledModules((int)$id),
            'planDefaultFeatures'=> $planDefaults,
            'breadcrumb'         => [
                ['label' => 'Schools', 'url' => 'schools'],
                ['label' => 'Edit'],
            ],
        ]);
    }

    /**
     * Update school
     */
    public function update($id): void
    {
        $id = (int)$id;
        $data = Validator::postData();
        
        $validator = Validator::make($_POST)
            ->required('name', 'School Name')
            ->required('email', 'Email')
            ->email('email')
            ->required('phone', 'Phone');

        if ($validator->fails()) {
            Session::flash('error', implode('<br>', $validator->allErrors()));
            Response::back();
        }

        // Handle logo upload
        if (!empty($_FILES['logo']['name'])) {
            $uploadDir = UPLOAD_PATH . '/logos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $fileName = 'school_' . $id . '_' . time() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) {
                $data['logo'] = $fileName;
            }
        }

        $updateData = [
            'name'            => $data['name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'website'         => $data['website'] ?? null,
            'address'         => $data['address'] ?? null,
            'city'            => $data['city'] ?? null,
            'state'           => $data['state'] ?? null,
            'pincode'         => $data['pincode'] ?? null,
            'primary_color'   => $data['primary_color'] ?? '#4F46E5',
            'secondary_color' => $data['secondary_color'] ?? '#7C3AED',
            'tagline'         => $data['tagline'] ?? null,
            'board'           => $data['board'] ?? null,
            'school_type'     => $data['school_type'] ?? 'k12',
            'principal_name'  => $data['principal_name'] ?? null,
            'is_active'       => isset($data['is_active']) ? 1 : 0,
        ];

        if (!empty($data['logo'])) {
            $updateData['logo'] = $data['logo'];
        }

        Database::update('schools', $updateData, 'id = ?', [$id]);

        // Save enabled modules for this school
        $selectedModules = $_POST['modules'] ?? [];
        require_once APP_PATH . '/Services/ModuleService.php';
        ModuleService::setSchoolModules($id, $selectedModules, Session::userId());

        // Save Subscription Plan
        if (!empty($data['plan_id'])) {
            $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$data['plan_id']]);
            if ($plan) {
                $billingCycle = $data['billing_cycle'] ?? 'monthly';
                $pricingType = $data['pricing_type'] ?? 'fixed';
                
                // End date based on billing cycle
                require_once APP_PATH . '/Services/BillingService.php';
                $cycleMonths = BillingService::getCycleMultiplier($billingCycle);
                
                $unitPrice = (float)($data['subscription_amount'] ?? 0);
                
                $activeStudents = Database::count(
                    'users',
                    "school_id = ? AND user_type = 'student' AND is_active = 1",
                    [$id]
                );

                if ($pricingType === 'per_student') {
                    $amount = $unitPrice * $activeStudents; 
                } else {
                    $amount = $unitPrice;
                }

                if ($plan['slug'] === 'free' || $unitPrice == 0) {
                    $amount = 0;
                    $unitPrice = 0;
                }
                
                $currentSub = Database::fetch("SELECT id, start_date FROM subscriptions WHERE school_id = ? ORDER BY created_at DESC LIMIT 1", [$id]);
                
                $subData = [
                    'school_id'      => $id,
                    'plan_id'        => $data['plan_id'],
                    'billing_cycle'  => $billingCycle,
                    'pricing_type'   => $pricingType,
                    'student_count'  => $activeStudents,
                    'unit_price'     => $unitPrice,
                    'amount'         => $amount,
                    'payment_status' => $amount == 0 ? 'paid' : 'pending',
                ];

                if ($currentSub) {
                    $subData['end_date'] = date('Y-m-d', strtotime($currentSub['start_date'] . " +{$cycleMonths} months"));
                    Database::update('subscriptions', $subData, 'id = ?', [$currentSub['id']]);
                } else {
                    $subData['start_date'] = date('Y-m-d');
                    $subData['end_date'] = date('Y-m-d', strtotime("+{$cycleMonths} months"));
                    $subData['status'] = 'active';
                    Database::insert('subscriptions', $subData);
                }
            }
        }

        Session::flash('success', 'School updated successfully.');
        Response::redirect('schools');
    }

    /**
     * Delete school
     */
    public function delete($id): void
    {
        $id = (int)$id;
        
        // Soft delete — deactivate
        Database::update('schools', ['is_active' => 0], 'id = ?', [$id]);
        Database::update('users', ['is_active' => 0], 'school_id = ?', [$id]);
        
        Session::flash('success', 'School deactivated successfully.');
        Response::redirect('schools');
    }

    /**
     * View school details
     */
    public function show($id): void
    {
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [(int)$id]);
        if (!$school) Response::abort(404);

        $stats = [
            'students' => Database::count('users', "school_id = ? AND user_type = 'student' AND is_active = 1", [$id]),
            'teachers' => Database::count('users', "school_id = ? AND user_type = 'teacher' AND is_active = 1", [$id]),
            'staff'    => Database::count('users', "school_id = ? AND user_type NOT IN ('student','parent') AND is_active = 1", [$id]),
            'parents'  => Database::count('users', "school_id = ? AND user_type = 'parent' AND is_active = 1", [$id]),
        ];

        $subscription = Database::fetch(
            "SELECT sub.*, p.name as plan_name FROM subscriptions sub 
             JOIN plans p ON sub.plan_id = p.id 
             WHERE sub.school_id = ? ORDER BY sub.created_at DESC LIMIT 1",
            [$id]
        );

        $admins = Database::fetchAll(
            "SELECT * FROM users WHERE school_id = ? AND user_type = 'school_admin' AND is_active = 1",
            [$id]
        );

        Response::view('super-admin.school-view', [
            'pageTitle'    => $school['name'],
            'school'       => $school,
            'stats'        => $stats,
            'subscription' => $subscription,
            'admins'       => $admins,
            'breadcrumb'   => [
                ['label' => 'Schools', 'url' => 'schools'],
                ['label' => $school['name']],
            ],
        ]);
    }
}
