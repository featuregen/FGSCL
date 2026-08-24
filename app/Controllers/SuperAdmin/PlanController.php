<?php
/**
 * Plan Controller (Super Admin)
 * CRUD for subscription plans — pricing, features, limits
 */

class PlanController
{
    /**
     * List all plans
     */
    public function index(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans ORDER BY sort_order ASC");

        // Get subscriber counts per plan
        foreach ($plans as &$plan) {
            $plan['subscriber_count'] = Database::count(
                'subscriptions',
                "plan_id = ? AND status = 'active'",
                [$plan['id']]
            );
            $plan['features_list'] = json_decode($plan['features'] ?? '[]', true);
        }

        Response::view('super-admin.plans', [
            'pageTitle' => 'Subscription Plans',
            'plans'     => $plans,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $modules = Database::fetchAll("SELECT slug, name, category FROM modules WHERE is_active = 1 ORDER BY sort_order");

        Response::view('super-admin.plan-form', [
            'pageTitle' => 'Create Plan',
            'plan'      => null,
            'isEdit'    => false,
            'modules'   => $modules,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id): void
    {
        $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [(int)$id]);
        if (!$plan) Response::abort(404);

        $plan['features_list'] = json_decode($plan['features'] ?? '[]', true);
        $modules = Database::fetchAll("SELECT slug, name, category FROM modules WHERE is_active = 1 ORDER BY sort_order");

        Response::view('super-admin.plan-form', [
            'pageTitle' => 'Edit Plan — ' . $plan['name'],
            'plan'      => $plan,
            'isEdit'    => true,
            'modules'   => $modules,
        ]);
    }

    /**
     * Store new plan
     */
    public function store(): void
    {
        $data = $_POST;

        // Validate required fields
        $validator = Validator::make($data)
            ->required('name', 'Plan Name')
            ->required('slug', 'Slug');

        if ($validator->fails()) {
            Session::flash('error', $validator->allErrors()[0]);
            Response::redirect('plans/create');
        }

        // Check unique slug
        $existing = Database::fetch("SELECT id FROM plans WHERE slug = ?", [$data['slug']]);
        if ($existing) {
            Session::flash('error', 'A plan with this slug already exists.');
            Response::redirect('plans/create');
        }

        $features = $data['features'] ?? [];

        Database::insert('plans', [
            'name'                     => $data['name'],
            'slug'                     => $data['slug'],
            'description'              => $data['description'] ?? '',
            'pricing_type'             => $data['pricing_type'] ?? 'fixed',
            'price_monthly'            => (float)($data['price_monthly'] ?? 0),
            'price_yearly'             => (float)($data['price_yearly'] ?? 0),
            'price_quarterly'          => (float)($data['price_quarterly'] ?? 0),
            'price_half_yearly'        => (float)($data['price_half_yearly'] ?? 0),
            'price_per_student_monthly'=> (float)($data['price_per_student_monthly'] ?? 0),
            'price_per_student_yearly' => (float)($data['price_per_student_yearly'] ?? 0),
            'min_students'             => (int)($data['min_students'] ?? 0),
            'max_students_limit'       => (int)($data['max_students_limit'] ?? 0),
            'max_students'             => (int)($data['max_students'] ?? 0),
            'max_staff'                => (int)($data['max_staff'] ?? 0),
            'max_branches'             => (int)($data['max_branches'] ?? 1),
            'features'                 => json_encode($features),
            'is_active'                => isset($data['is_active']) ? 1 : 0,
            'sort_order'               => (int)($data['sort_order'] ?? 0),
        ]);

        Session::flash('success', 'Plan created successfully.');
        Response::redirect('plans');
    }

    /**
     * Update plan
     */
    public function update($id): void
    {
        $id = (int)$id;
        $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$id]);
        if (!$plan) Response::abort(404);

        $data = $_POST;

        // Check slug uniqueness (exclude current)
        $existing = Database::fetch("SELECT id FROM plans WHERE slug = ? AND id != ?", [$data['slug'], $id]);
        if ($existing) {
            Session::flash('error', 'A plan with this slug already exists.');
            Response::redirect("plans/edit/{$id}");
        }

        $features = $data['features'] ?? [];

        Database::update('plans', [
            'name'                     => $data['name'],
            'slug'                     => $data['slug'],
            'description'              => $data['description'] ?? '',
            'pricing_type'             => $data['pricing_type'] ?? 'fixed',
            'price_monthly'            => (float)($data['price_monthly'] ?? 0),
            'price_yearly'             => (float)($data['price_yearly'] ?? 0),
            'price_quarterly'          => (float)($data['price_quarterly'] ?? 0),
            'price_half_yearly'        => (float)($data['price_half_yearly'] ?? 0),
            'price_per_student_monthly'=> (float)($data['price_per_student_monthly'] ?? 0),
            'price_per_student_yearly' => (float)($data['price_per_student_yearly'] ?? 0),
            'min_students'             => (int)($data['min_students'] ?? 0),
            'max_students_limit'       => (int)($data['max_students_limit'] ?? 0),
            'max_students'             => (int)($data['max_students'] ?? 0),
            'max_staff'                => (int)($data['max_staff'] ?? 0),
            'max_branches'             => (int)($data['max_branches'] ?? 1),
            'features'                 => json_encode($features),
            'is_active'                => isset($data['is_active']) ? 1 : 0,
            'sort_order'               => (int)($data['sort_order'] ?? 0),
        ], 'id = ?', [$id]);

        Session::flash('success', 'Plan updated successfully.');
        Response::redirect('plans');
    }

    /**
     * Delete plan (soft)
     */
    public function delete($id): void
    {
        $id = (int)$id;

        // Check if any active subscriptions use this plan
        $activeCount = Database::count('subscriptions', "plan_id = ? AND status = 'active'", [$id]);
        if ($activeCount > 0) {
            Session::flash('error', "Cannot delete — {$activeCount} active subscription(s) use this plan. Deactivate it instead.");
            Response::redirect('plans');
        }

        Database::update('plans', ['is_active' => 0], 'id = ?', [$id]);
        Session::flash('success', 'Plan deactivated.');
        Response::redirect('plans');
    }
}
