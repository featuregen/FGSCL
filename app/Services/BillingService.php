<?php
/**
 * Billing Service
 * Core billing engine: invoice generation, amount calculation,
 * payment processing, overdue handling, and auto-renewal.
 */

class BillingService
{
    /**
     * Get a billing setting value
     */
    public static function getSetting(string $key, $default = null)
    {
        $row = Database::fetch(
            "SELECT setting_value, setting_type FROM billing_settings WHERE setting_key = ?",
            [$key]
        );
        
        if (!$row) return $default;
        
        return match ($row['setting_type']) {
            'number'  => (float) $row['setting_value'],
            'boolean' => (bool) $row['setting_value'],
            'json'    => json_decode($row['setting_value'], true),
            default   => $row['setting_value'],
        };
    }

    /**
     * Update a billing setting
     */
    public static function updateSetting(string $key, $value, ?int $userId = null): void
    {
        Database::update(
            'billing_settings',
            ['setting_value' => (string) $value, 'updated_by' => $userId],
            'setting_key = ?',
            [$key]
        );
    }

    /**
     * Get all billing settings as key => value
     */
    public static function getAllSettings(): array
    {
        $rows = Database::fetchAll("SELECT setting_key, setting_value, setting_type, description FROM billing_settings ORDER BY id");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = [
                'value'       => $row['setting_value'],
                'type'        => $row['setting_type'],
                'description' => $row['description'],
            ];
        }
        return $settings;
    }

    // ─── Invoice Number Generation ────────────────

    /**
     * Generate next invoice number: INV-2026-0001
     */
    public static function getNextInvoiceNumber(): string
    {
        $prefix = self::getSetting('invoice_prefix', 'INV');
        $year = date('Y');
        
        $last = Database::fetch(
            "SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1",
            ["{$prefix}-{$year}-%"]
        );
        
        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last['invoice_number']);
            $nextSeq = (int) end($parts) + 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $year, $nextSeq);
    }

    /**
     * Generate next payment number: PAY-2026-0001
     */
    public static function getNextPaymentNumber(): string
    {
        $prefix = self::getSetting('payment_prefix', 'PAY');
        $year = date('Y');
        
        $last = Database::fetch(
            "SELECT payment_number FROM payments WHERE payment_number LIKE ? ORDER BY id DESC LIMIT 1",
            ["{$prefix}-{$year}-%"]
        );
        
        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last['payment_number']);
            $nextSeq = (int) end($parts) + 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $year, $nextSeq);
    }

    /**
     * Generate next credit note number: CN-2026-0001
     */
    public static function getNextCreditNumber(): string
    {
        $year = date('Y');
        $last = Database::fetch(
            "SELECT credit_number FROM credit_notes WHERE credit_number LIKE ? ORDER BY id DESC LIMIT 1",
            ["CN-{$year}-%"]
        );
        
        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last['credit_number']);
            $nextSeq = (int) end($parts) + 1;
        }
        
        return sprintf('CN-%s-%04d', $year, $nextSeq);
    }

    // ─── Amount Calculation ───────────────────────

    /**
     * Calculate billing amount for a subscription
     *
     * @param array  $plan         Plan row from DB
     * @param string $billingCycle monthly|quarterly|half_yearly|yearly
     * @param int    $activeStudents Number of active students
     * @return array ['unit_price', 'subtotal', 'tax_rate', 'tax_amount', 'total']
     */
    public static function calculateAmount(array $plan, string $billingCycle, int $activeStudents = 0): array
    {
        $unitPrice = 0;
        $subtotal = 0;

        if ($plan['pricing_type'] === 'per_student') {
            // Per-student: monthly rate × cycle multiplier × student count
            $monthlyRate = (float) $plan['price_per_student_monthly'];
            $multiplier = self::getCycleMultiplier($billingCycle);
            
            // For yearly, use dedicated yearly rate if set
            if ($billingCycle === 'yearly' && $plan['price_per_student_yearly'] > 0) {
                $unitPrice = (float) $plan['price_per_student_yearly'];
            } else {
                $unitPrice = $monthlyRate * $multiplier;
            }
            
            // Enforce minimum students
            $billableStudents = max($activeStudents, (int) $plan['min_students']);
            $subtotal = $unitPrice * $billableStudents;
        } else {
            // Fixed pricing
            $unitPrice = match ($billingCycle) {
                'quarterly'   => (float) ($plan['price_quarterly'] ?: $plan['price_monthly'] * 3 * 0.95),
                'half_yearly' => (float) ($plan['price_half_yearly'] ?: $plan['price_monthly'] * 6 * 0.90),
                'yearly'      => (float) $plan['price_yearly'],
                default       => (float) $plan['price_monthly'],
            };
            $subtotal = $unitPrice;
        }

        // Tax
        $taxEnabled = self::getSetting('tax_enabled', false);
        $taxRate = $taxEnabled ? self::getSetting('tax_rate', 0) : 0;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total = $subtotal + $taxAmount;

        return [
            'unit_price'  => round($unitPrice, 2),
            'subtotal'    => round($subtotal, 2),
            'tax_rate'    => $taxRate,
            'tax_amount'  => $taxAmount,
            'total'       => round($total, 2),
            'students'    => $plan['pricing_type'] === 'per_student' ? max($activeStudents, (int) $plan['min_students']) : 0,
        ];
    }

    /**
     * Get cycle multiplier (months in cycle)
     */
    public static function getCycleMultiplier(string $cycle): int
    {
        return match ($cycle) {
            'quarterly'   => 3,
            'half_yearly' => 6,
            'yearly'      => 12,
            default       => 1,
        };
    }

    /**
     * Get billing period end date from start date
     */
    public static function getPeriodEndDate(string $startDate, string $billingCycle): string
    {
        $months = self::getCycleMultiplier($billingCycle);
        return date('Y-m-d', strtotime("+{$months} months", strtotime($startDate)));
    }

    // ─── Invoice Generation ───────────────────────

    /**
     * Generate an invoice for a school's subscription
     *
     * @param int    $schoolId
     * @param int    $subscriptionId
     * @param string $generatedBy 'auto' or 'manual'
     * @param int|null $createdBy User ID (null for auto)
     * @return int|null Invoice ID
     */
    public static function generateInvoice(
        int $schoolId,
        int $subscriptionId,
        string $generatedBy = 'manual',
        ?int $createdBy = null
    ): ?int {
        // Get subscription with plan
        $sub = Database::fetch(
            "SELECT s.*, p.name as plan_name, p.pricing_type, p.price_monthly, p.price_yearly,
                    p.price_quarterly, p.price_half_yearly,
                    p.price_per_student_monthly, p.price_per_student_yearly, p.min_students
             FROM subscriptions s
             JOIN plans p ON s.plan_id = p.id
             WHERE s.id = ?",
            [$subscriptionId]
        );

        if (!$sub) return null;

        // Count active students for this school
        $activeStudents = Database::count(
            'users',
            "school_id = ? AND user_type = 'student' AND is_active = 1",
            [$schoolId]
        );

        // Calculate billing period
        $periodStart = $sub['end_date']; // next period starts when current ends
        $periodEnd = self::getPeriodEndDate($periodStart, $sub['billing_cycle']);

        // Check if invoice already exists for this period
        $existing = Database::fetch(
            "SELECT id FROM invoices WHERE school_id = ? AND subscription_id = ? 
             AND billing_period_start = ? AND status NOT IN ('cancelled','void')",
            [$schoolId, $subscriptionId, $periodStart]
        );

        if ($existing) return $existing['id']; // Don't duplicate

        // Calculate amount
        $plan = $sub; // plan fields are merged
        $amounts = self::calculateAmount($plan, $sub['billing_cycle'], $activeStudents);

        // Due date
        $dueDays = (int) self::getSetting('invoice_due_days', 15);
        $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));

        $invoiceId = Database::insert('invoices', [
            'invoice_number'       => self::getNextInvoiceNumber(),
            'school_id'            => $schoolId,
            'subscription_id'      => $subscriptionId,
            'billing_period_start' => $periodStart,
            'billing_period_end'   => $periodEnd,
            'billing_cycle'        => $sub['billing_cycle'],
            'pricing_type'         => $sub['pricing_type'],
            'plan_name'            => $sub['plan_name'],
            'active_students'      => $amounts['students'],
            'unit_price'           => $amounts['unit_price'],
            'subtotal'             => $amounts['subtotal'],
            'discount'             => 0,
            'taxable_amount'       => $amounts['subtotal'],
            'tax_rate'             => $amounts['tax_rate'],
            'tax_amount'           => $amounts['tax_amount'],
            'total_amount'         => $amounts['total'],
            'amount_paid'          => 0,
            'balance_due'          => $amounts['total'],
            'status'               => 'pending',
            'due_date'             => $dueDate,
            'generated_by'         => $generatedBy,
            'created_by'           => $createdBy,
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        return $invoiceId;
    }

    /**
     * Generate first invoice when a school is created (for immediate billing period)
     */
    public static function generateFirstInvoice(int $schoolId, int $subscriptionId, ?int $createdBy = null): ?int
    {
        $sub = Database::fetch(
            "SELECT s.*, p.name as plan_name, p.pricing_type, p.price_monthly, p.price_yearly,
                    p.price_quarterly, p.price_half_yearly,
                    p.price_per_student_monthly, p.price_per_student_yearly, p.min_students
             FROM subscriptions s
             JOIN plans p ON s.plan_id = p.id
             WHERE s.id = ?",
            [$subscriptionId]
        );

        if (!$sub || $sub['pricing_type'] === 'fixed' && $sub['amount'] == 0) return null; // Free plan

        $activeStudents = (int) $sub['student_count']; // use expected count for first invoice

        $plan = $sub;
        $amounts = self::calculateAmount($plan, $sub['billing_cycle'], $activeStudents);

        $dueDays = (int) self::getSetting('invoice_due_days', 15);

        $invoiceId = Database::insert('invoices', [
            'invoice_number'       => self::getNextInvoiceNumber(),
            'school_id'            => $schoolId,
            'subscription_id'      => $subscriptionId,
            'billing_period_start' => $sub['start_date'],
            'billing_period_end'   => $sub['end_date'],
            'billing_cycle'        => $sub['billing_cycle'],
            'pricing_type'         => $sub['pricing_type'],
            'plan_name'            => $sub['plan_name'],
            'active_students'      => $amounts['students'],
            'unit_price'           => $amounts['unit_price'],
            'subtotal'             => $amounts['subtotal'],
            'discount'             => 0,
            'taxable_amount'       => $amounts['subtotal'],
            'tax_rate'             => $amounts['tax_rate'],
            'tax_amount'           => $amounts['tax_amount'],
            'total_amount'         => $amounts['total'],
            'amount_paid'          => 0,
            'balance_due'          => $amounts['total'],
            'status'               => 'pending',
            'due_date'             => date('Y-m-d', strtotime("+{$dueDays} days")),
            'generated_by'         => 'manual',
            'created_by'           => $createdBy,
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        return $invoiceId;
    }

    // ─── Payment Recording ────────────────────────

    /**
     * Record a payment against an invoice
     *
     * @return array ['success' => bool, 'message' => string, 'payment_id' => int|null]
     */
    public static function recordPayment(array $data): array
    {
        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$data['invoice_id']]);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        if (in_array($invoice['status'], ['paid', 'cancelled', 'void'])) {
            return ['success' => false, 'message' => "Cannot record payment on {$invoice['status']} invoice"];
        }

        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Payment amount must be positive'];
        }

        if ($amount > $invoice['balance_due']) {
            return ['success' => false, 'message' => "Amount exceeds balance due (₹" . number_format($invoice['balance_due'], 2) . ")"];
        }

        try {
            Database::beginTransaction();

            // Insert payment
            $paymentId = Database::insert('payments', [
                'payment_number'  => self::getNextPaymentNumber(),
                'invoice_id'      => $invoice['id'],
                'school_id'       => $invoice['school_id'],
                'amount'          => $amount,
                'payment_method'  => $data['payment_method'] ?? 'other',
                'payment_date'    => $data['payment_date'] ?? date('Y-m-d'),
                'transaction_ref' => $data['transaction_ref'] ?? null,
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? null,
                'razorpay_order_id'   => $data['razorpay_order_id'] ?? null,
                'razorpay_signature'  => $data['razorpay_signature'] ?? null,
                'status'          => 'success',
                'notes'           => $data['notes'] ?? null,
                'received_by'     => $data['received_by'] ?? null,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            // Update invoice
            $newAmountPaid = $invoice['amount_paid'] + $amount;
            $newBalance = $invoice['total_amount'] - $newAmountPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

            $updateData = [
                'amount_paid' => round($newAmountPaid, 2),
                'balance_due' => round(max(0, $newBalance), 2),
                'status'      => $newStatus,
            ];

            if ($newStatus === 'paid') {
                $updateData['paid_at'] = date('Y-m-d H:i:s');
            }

            Database::update('invoices', $updateData, 'id = ?', [$invoice['id']]);

            // If fully paid, extend subscription
            if ($newStatus === 'paid') {
                self::extendSubscription($invoice);
            }

            Database::commit();

            return [
                'success'    => true,
                'message'    => $newStatus === 'paid' ? 'Invoice fully paid' : 'Partial payment recorded',
                'payment_id' => $paymentId,
                'new_status' => $newStatus,
                'balance'    => round(max(0, $newBalance), 2),
            ];

        } catch (Exception $e) {
            Database::rollback();
            return ['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()];
        }
    }

    /**
     * Extend subscription after full payment
     */
    private static function extendSubscription(array $invoice): void
    {
        if (!$invoice['subscription_id']) return;

        Database::update('subscriptions', [
            'end_date'       => $invoice['billing_period_end'],
            'payment_status' => 'paid',
            'status'         => 'active',
        ], 'id = ?', [$invoice['subscription_id']]);

        // Reactivate school if it was suspended
        $sub = Database::fetch("SELECT school_id FROM subscriptions WHERE id = ?", [$invoice['subscription_id']]);
        if ($sub) {
            Database::update('schools', ['is_active' => 1], 'id = ?', [$sub['school_id']]);
        }
    }

    // ─── Credit Notes ─────────────────────────────

    /**
     * Issue a credit note for a school
     */
    public static function issueCreditNote(int $schoolId, float $amount, string $reason, ?int $invoiceId = null, ?string $expiresAt = null, ?int $createdBy = null): int
    {
        return Database::insert('credit_notes', [
            'credit_number' => self::getNextCreditNumber(),
            'school_id'     => $schoolId,
            'invoice_id'    => $invoiceId,
            'amount'        => $amount,
            'reason'        => $reason,
            'status'        => 'active',
            'expires_at'    => $expiresAt,
            'created_by'    => $createdBy,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Apply a credit note to an invoice
     */
    public static function applyCreditToInvoice(int $creditNoteId, int $invoiceId): array
    {
        $credit = Database::fetch("SELECT * FROM credit_notes WHERE id = ? AND status = 'active'", [$creditNoteId]);
        if (!$credit) return ['success' => false, 'message' => 'Credit note not found or already used'];

        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) return ['success' => false, 'message' => 'Invoice not found'];

        $applyAmount = min($credit['amount'], $invoice['balance_due']);

        // Record as payment
        $result = self::recordPayment([
            'invoice_id'     => $invoiceId,
            'amount'         => $applyAmount,
            'payment_method' => 'other',
            'payment_date'   => date('Y-m-d'),
            'transaction_ref'=> 'Credit: ' . $credit['credit_number'],
            'notes'          => "Credit note {$credit['credit_number']} applied",
            'received_by'    => Session::userId(),
        ]);

        if ($result['success']) {
            Database::update('credit_notes', [
                'status' => 'used',
                'used_against_invoice_id' => $invoiceId,
            ], 'id = ?', [$creditNoteId]);
        }

        return $result;
    }

    // ─── Invoice Actions ──────────────────────────

    /**
     * Apply discount to an invoice
     */
    public static function applyDiscount(int $invoiceId, float $discount, string $reason): bool
    {
        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice || in_array($invoice['status'], ['paid', 'cancelled', 'void'])) return false;

        $taxableAmount = $invoice['subtotal'] - $discount;
        $taxAmount = round($taxableAmount * $invoice['tax_rate'] / 100, 2);
        $totalAmount = $taxableAmount + $taxAmount;
        $balanceDue = $totalAmount - $invoice['amount_paid'];

        Database::update('invoices', [
            'discount'        => $discount,
            'discount_reason' => $reason,
            'taxable_amount'  => round($taxableAmount, 2),
            'tax_amount'      => $taxAmount,
            'total_amount'    => round($totalAmount, 2),
            'balance_due'     => round(max(0, $balanceDue), 2),
            'status'          => $balanceDue <= 0 ? 'paid' : $invoice['status'],
        ], 'id = ?', [$invoiceId]);

        return true;
    }

    /**
     * Cancel an invoice
     */
    public static function cancelInvoice(int $invoiceId, ?string $reason = null): bool
    {
        $invoice = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice || in_array($invoice['status'], ['paid', 'void'])) return false;

        Database::update('invoices', [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'notes'        => $invoice['notes'] . "\nCancelled: " . ($reason ?? 'No reason'),
        ], 'id = ?', [$invoiceId]);

        return true;
    }

    // ─── Overdue & Suspension ─────────────────────

    /**
     * Mark overdue invoices (called by cron)
     */
    public static function markOverdueInvoices(): int
    {
        $result = Database::pdo()->exec(
            "UPDATE invoices SET status = 'overdue' 
             WHERE status IN ('pending','partially_paid') 
             AND due_date < CURDATE()"
        );
        return $result ?: 0;
    }

    /**
     * Suspend schools with overdue invoices past grace period (called by cron)
     */
    public static function suspendOverdueSchools(): int
    {
        $enabled = self::getSetting('auto_suspend_enabled', true);
        if (!$enabled) return 0;

        $graceDays = (int) self::getSetting('grace_period_days', 15);
        
        $overdueSchools = Database::fetchAll(
            "SELECT DISTINCT i.school_id 
             FROM invoices i
             WHERE i.status = 'overdue' 
             AND DATEDIFF(CURDATE(), i.due_date) > ?
             AND i.school_id IN (SELECT id FROM schools WHERE is_active = 1)",
            [$graceDays]
        );

        $count = 0;
        foreach ($overdueSchools as $row) {
            Database::update('schools', ['is_active' => 0], 'id = ?', [$row['school_id']]);
            $count++;
        }

        return $count;
    }

    // ─── Auto-Renewal / Billing Cron ──────────────

    /**
     * Process auto-renewals: generate invoices for expiring subscriptions (called by cron)
     */
    public static function processAutoRenewals(): array
    {
        $daysBefore = (int) self::getSetting('auto_generate_days_before', 5);
        $autoGenerate = self::getSetting('auto_generate_invoices', true);

        if (!$autoGenerate) return ['generated' => 0];

        // Find subscriptions expiring within N days with auto_renew
        $expiring = Database::fetchAll(
            "SELECT s.id, s.school_id 
             FROM subscriptions s
             JOIN schools sch ON s.school_id = sch.id AND sch.is_active = 1
             WHERE s.status = 'active' 
             AND s.auto_renew = 1
             AND DATEDIFF(s.end_date, CURDATE()) <= ?
             AND DATEDIFF(s.end_date, CURDATE()) >= 0",
            [$daysBefore]
        );

        $generated = 0;
        foreach ($expiring as $sub) {
            $invoiceId = self::generateInvoice($sub['school_id'], $sub['id'], 'auto');
            if ($invoiceId) $generated++;
        }

        return ['generated' => $generated];
    }

    // ─── Revenue Stats ────────────────────────────

    /**
     * Get revenue statistics for dashboard
     */
    public static function getRevenueStats(): array
    {
        $currentMonth = date('Y-m');
        
        return [
            'total_revenue' => Database::fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'"
            )['total'] ?? 0,
            
            'this_month_revenue' => Database::fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success' AND DATE_FORMAT(payment_date, '%Y-%m') = ?",
                [$currentMonth]
            )['total'] ?? 0,
            
            'pending_amount' => Database::fetch(
                "SELECT COALESCE(SUM(balance_due), 0) as total FROM invoices WHERE status IN ('pending','partially_paid')"
            )['total'] ?? 0,
            
            'overdue_amount' => Database::fetch(
                "SELECT COALESCE(SUM(balance_due), 0) as total FROM invoices WHERE status = 'overdue'"
            )['total'] ?? 0,
            
            'overdue_count' => Database::count('invoices', "status = 'overdue'"),
            
            'pending_count' => Database::count('invoices', "status IN ('pending','partially_paid')"),
            
            'total_invoices' => Database::count('invoices'),
            
            'paid_invoices' => Database::count('invoices', "status = 'paid'"),
            
            'monthly_revenue' => Database::fetchAll(
                "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, 
                        COALESCE(SUM(amount), 0) as revenue
                 FROM payments 
                 WHERE status = 'success' 
                 AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY month ORDER BY month"
            ),
        ];
    }
}
