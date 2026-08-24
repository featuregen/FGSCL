<?php
/**
 * Subscription Management Controller (Super Admin)
 */

class SubscriptionController
{
    /**
     * List all subscriptions
     */
    public static function index()
    {
        if (!Session::hasPermission('schools.manage_subscription')) {
            Response::abort(403, 'Access denied.');
            return;
        }

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $paymentStatus = $_GET['payment_status'] ?? '';

        $query = "SELECT s.*, 
                         sc.name AS school_name, sc.code AS school_code, sc.logo AS school_logo,
                         sc.primary_color, sc.is_active AS school_active,
                         p.name AS plan_name, p.pricing_type AS plan_pricing_type
                  FROM subscriptions s
                  JOIN schools sc ON s.school_id = sc.id
                  LEFT JOIN plans p ON s.plan_id = p.id
                  WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (sc.name LIKE ? OR sc.code LIKE ? OR p.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status !== '') {
            $query .= " AND s.status = ?";
            $params[] = $status;
        }

        if ($paymentStatus !== '') {
            $query .= " AND s.payment_status = ?";
            $params[] = $paymentStatus;
        }

        $query .= " ORDER BY s.created_at DESC";

        $subscriptions = Database::fetchAll($query, $params);

        // Summary stats
        $stats = [
            'total' => count($subscriptions),
            'active' => 0,
            'total_revenue' => 0,
            'pending_amount' => 0,
        ];

        foreach ($subscriptions as $sub) {
            if ($sub['status'] === 'active') $stats['active']++;
            if ($sub['payment_status'] === 'paid') $stats['total_revenue'] += (float)$sub['amount'];
            if ($sub['payment_status'] === 'pending') $stats['pending_amount'] += (float)$sub['amount'];
        }

        Response::view('super-admin/subscriptions', [
            'pageTitle' => 'Subscriptions',
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'breadcrumbs' => [
                ['label' => 'Subscriptions'],
            ],
        ]);
    }

    /**
     * Update subscription status
     */
    public static function updateStatus()
    {
        if (!Session::hasPermission('schools.manage_subscription')) { Response::abort(403); return; }

        $id = $_POST['id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$id || !in_array($newStatus, ['active', 'expired', 'cancelled', 'suspended'])) {
            Session::flash('error', 'Invalid request.');
            Response::redirect('subscriptions');
            return;
        }

        Database::update('subscriptions', ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        Session::flash('success', 'Subscription status updated.');
        Response::redirect('subscriptions');
    }

    /**
     * Record payment
     */
    public static function recordPayment()
    {
        if (!Session::hasPermission('schools.manage_subscription')) { Response::abort(403); return; }

        $id = $_POST['id'] ?? null;
        $paymentStatus = $_POST['payment_status'] ?? null;

        if (!$id || !in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'])) {
            Session::flash('error', 'Invalid request.');
            Response::redirect('subscriptions');
            return;
        }

        Database::update('subscriptions', ['payment_status' => $paymentStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        Session::flash('success', 'Payment status updated.');
        Response::redirect('subscriptions');
    }
}
