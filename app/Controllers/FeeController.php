<?php
/**
 * Fee Controller
 * Fee management: heads, structures, discounts, collection, receipts, reports
 */

class FeeController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    private function getSchoolId(): ?int { return Session::schoolId(); }

    private function getCurrentAcademicYear($schoolId)
    {
        return Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
    }

    // ─── Fee Dashboard ─────────────────────────────
    public function index()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Summary stats
        $totalCollected = Database::fetch(
            "SELECT COALESCE(SUM(net_amount), 0) as total FROM fee_payments WHERE school_id = ? AND academic_year_id = ?",
            [$schoolId, $yearId]
        )['total'] ?? 0;

        $todayCollected = Database::fetch(
            "SELECT COALESCE(SUM(net_amount), 0) as total FROM fee_payments WHERE school_id = ? AND payment_date = CURDATE()",
            [$schoolId]
        )['total'] ?? 0;

        $totalReceipts = Database::fetch(
            "SELECT COUNT(*) as cnt FROM fee_payments WHERE school_id = ? AND academic_year_id = ?",
            [$schoolId, $yearId]
        )['cnt'] ?? 0;

        // Recent payments
        $recentPayments = Database::fetchAll(
            "SELECT fp.*, u.full_name as student_name, c.name as class_name, sec.name as section_name,
                    col.full_name as collected_by_name
             FROM fee_payments fp
             JOIN users u ON fp.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             LEFT JOIN users col ON fp.collected_by = col.id
             WHERE fp.school_id = ?
             ORDER BY fp.created_at DESC
             LIMIT 10",
            [$schoolId]
        );

        // Fee heads count
        $headsCount = Database::fetch(
            "SELECT COUNT(*) as cnt FROM fee_heads WHERE school_id = ? AND is_active = 1",
            [$schoolId]
        )['cnt'] ?? 0;

        // Payment mode breakdown
        $modeBreakdown = Database::fetchAll(
            "SELECT payment_mode, COUNT(*) as cnt, SUM(net_amount) as total
             FROM fee_payments WHERE school_id = ? AND academic_year_id = ?
             GROUP BY payment_mode ORDER BY total DESC",
            [$schoolId, $yearId]
        );

        // Pending cancellation requests
        $pendingApprovals = Database::fetch(
            "SELECT COUNT(*) as cnt FROM fee_cancellation_requests WHERE school_id = ? AND status = 'pending'",
            [$schoolId]
        )['cnt'] ?? 0;

        Response::view('fees/index', [
            'pageTitle'        => 'Fee Management',
            'totalCollected'   => $totalCollected,
            'todayCollected'   => $todayCollected,
            'totalReceipts'    => $totalReceipts,
            'headsCount'       => $headsCount,
            'recentPayments'   => $recentPayments,
            'modeBreakdown'    => $modeBreakdown,
            'currentYear'      => $currentYear,
            'pendingApprovals' => $pendingApprovals,
            'breadcrumbs'      => [['label' => 'Fees']],
        ]);
    }

    // ─── Fee Heads CRUD ────────────────────────────
    public function heads()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $heads = Database::fetchAll(
            "SELECT fh.*, 
                    (SELECT COUNT(*) FROM fee_structures fs WHERE fs.fee_head_id = fh.id AND fs.is_active = 1) as structure_count
             FROM fee_heads fh WHERE fh.school_id = ? ORDER BY fh.name",
            [$schoolId]
        );

        Response::view('fees/heads', [
            'pageTitle' => 'Fee Heads',
            'heads'     => $heads,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Fee Heads'],
            ],
        ]);
    }

    public function storeHead()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        if (empty(trim($data['name'] ?? ''))) {
            Session::flash('error', 'Fee head name is required.');
            Response::back(); return;
        }

        try {
            Database::insert('fee_heads', [
                'school_id'    => $schoolId,
                'name'         => trim($data['name']),
                'code'         => !empty($data['code']) ? strtoupper(trim($data['code'])) : null,
                'type'         => $data['type'] ?? 'mandatory',
                'is_recurring' => isset($data['is_recurring']) ? 1 : 0,
                'description'  => !empty($data['description']) ? trim($data['description']) : null,
                'created_by'   => Session::userId(),
                'updated_by'   => Session::userId(),
            ]);
            Session::flash('success', "Fee head '{$data['name']}' added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/heads');
    }

    public function updateHead($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        try {
            Database::update('fee_heads', [
                'name'         => trim($data['name']),
                'code'         => !empty($data['code']) ? strtoupper(trim($data['code'])) : null,
                'type'         => $data['type'] ?? 'mandatory',
                'is_recurring' => isset($data['is_recurring']) ? 1 : 0,
                'description'  => !empty($data['description']) ? trim($data['description']) : null,
                'is_active'    => isset($data['is_active']) ? 1 : 0,
                'updated_by'   => Session::userId(),
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Fee head updated.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/heads');
    }

    public function deleteHead($id)
    {
        $schoolId = $this->getSchoolId();

        // Check if used in payments
        $used = Database::fetch(
            "SELECT COUNT(*) as cnt FROM fee_payment_items fpi
             JOIN fee_payments fp ON fpi.fee_payment_id = fp.id
             WHERE fpi.fee_head_id = ? AND fp.school_id = ?",
            [$id, $schoolId]
        )['cnt'] ?? 0;

        if ($used > 0) {
            Session::flash('error', 'Cannot delete: Fee head has payment records. Deactivate instead.');
        } else {
            Database::delete('fee_structures', 'fee_head_id = ? AND school_id = ?', [$id, $schoolId]);
            Database::delete('fee_heads', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Fee head deleted.');
        }

        Response::redirect('fees/heads');
    }

    // ─── Fee Structures ────────────────────────────
    public function structures()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $classes = Database::fetchAll(
            "SELECT id, name, numeric_name FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name",
            [$schoolId, $yearId]
        );

        $heads = Database::fetchAll(
            "SELECT id, name, code, type, is_recurring FROM fee_heads WHERE school_id = ? AND is_active = 1 ORDER BY name",
            [$schoolId]
        );

        // Load existing structures
        $structures = Database::fetchAll(
            "SELECT fs.*, fh.name as head_name, fh.code as head_code, c.name as class_name
             FROM fee_structures fs
             JOIN fee_heads fh ON fs.fee_head_id = fh.id
             JOIN classes c ON fs.class_id = c.id
             WHERE fs.school_id = ? AND fs.academic_year_id = ?
             ORDER BY c.numeric_name, fh.name",
            [$schoolId, $yearId]
        );

        // Group by class
        $structuresByClass = [];
        foreach ($structures as $s) {
            $structuresByClass[$s['class_id']][] = $s;
        }

        Response::view('fees/structures', [
            'pageTitle'         => 'Fee Structure',
            'classes'           => $classes,
            'heads'             => $heads,
            'structuresByClass' => $structuresByClass,
            'currentYear'       => $currentYear,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Fee Structure'],
            ],
        ]);
    }

    public function saveStructure()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $classId = (int)($data['class_id'] ?? 0);
        $headId = (int)($data['fee_head_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        $frequency = $data['frequency'] ?? 'monthly';
        $dueDay = (int)($data['due_day'] ?? 10);

        if (!$classId || !$headId || $amount <= 0) {
            Session::flash('error', 'Class, fee head, and amount are required.');
            Response::back(); return;
        }

        try {
            // Upsert
            $existing = Database::fetch(
                "SELECT id FROM fee_structures WHERE school_id = ? AND academic_year_id = ? AND class_id = ? AND fee_head_id = ?",
                [$schoolId, $yearId, $classId, $headId]
            );

            $structureData = [
                'amount'    => $amount,
                'frequency' => $frequency,
                'due_day'   => $dueDay,
                'is_active' => 1,
                'updated_by' => Session::userId(),
            ];

            if ($existing) {
                Database::update('fee_structures', $structureData, 'id = ?', [$existing['id']]);
            } else {
                $structureData['school_id'] = $schoolId;
                $structureData['academic_year_id'] = $yearId;
                $structureData['class_id'] = $classId;
                $structureData['fee_head_id'] = $headId;
                $structureData['created_by'] = Session::userId();
                Database::insert('fee_structures', $structureData);
            }

            Session::flash('success', 'Fee structure saved.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/structures');
    }

    public function updateStructure($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;

        $amount = (float)($data['amount'] ?? 0);
        $frequency = $data['frequency'] ?? 'monthly';
        $dueDay = (int)($data['due_day'] ?? 10);

        if ($amount <= 0) {
            Session::flash('error', 'Amount must be greater than 0.');
            Response::back(); return;
        }

        try {
            Database::update('fee_structures', [
                'amount'     => $amount,
                'frequency'  => $frequency,
                'due_day'    => $dueDay,
                'updated_by' => Session::userId(),
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Fee structure updated.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/structures');
    }

    public function deleteStructure($id)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('fee_structures', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Fee structure removed.');
        Response::redirect('fees/structures');
    }

    // ─── Fee Discounts ─────────────────────────────
    public function discounts()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $discounts = Database::fetchAll(
            "SELECT * FROM fee_discounts WHERE school_id = ? ORDER BY name",
            [$schoolId]
        );

        // Get applied concessions grouped by discount
        $concessions = Database::fetchAll(
            "SELECT sfc.*, u.full_name as student_name, sd.admission_no,
                    c.name as class_name, sec.name as section_name
             FROM student_fee_concessions sfc
             JOIN users u ON sfc.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE sfc.school_id = ? AND sfc.academic_year_id = ? AND sfc.is_active = 1
             ORDER BY u.full_name",
            [$schoolId, $yearId]
        );
        $concessionsByDiscount = [];
        foreach ($concessions as $c) {
            $concessionsByDiscount[$c['fee_discount_id']][] = $c;
        }

        // Students for assign dropdown
        $students = Database::fetchAll(
            "SELECT u.id, u.full_name, sd.admission_no, c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.school_id = ? AND u.user_type = 'student' AND u.is_active = 1 AND sd.academic_year_id = ?
             ORDER BY u.full_name",
            [$schoolId, $yearId]
        );

        // Fee heads for applicable heads selection
        $feeHeads = Database::fetchAll(
            "SELECT * FROM fee_heads WHERE school_id = ? AND is_active = 1 ORDER BY name",
            [$schoolId]
        );

        Response::view('fees/discounts', [
            'pageTitle'             => 'Fee Discounts',
            'discounts'             => $discounts,
            'concessionsByDiscount' => $concessionsByDiscount,
            'students'              => $students,
            'feeHeads'              => $feeHeads,
            'currentYear'           => $currentYear,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Discounts'],
            ],
        ]);
    }

    public function storeDiscount()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $applicableHeads = !empty($data['applicable_heads']) ? json_encode(array_map('intval', $data['applicable_heads'])) : null;

        try {
            Database::insert('fee_discounts', [
                'school_id'       => $schoolId,
                'name'            => trim($data['name']),
                'type'            => $data['type'] ?? 'percentage',
                'value'           => (float)($data['value'] ?? 0),
                'applicable_heads'=> $applicableHeads,
                'description'     => !empty($data['description']) ? trim($data['description']) : null,
                'created_by'      => Session::userId(),
            ]);
            Session::flash('success', "Discount '{$data['name']}' added.");
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/discounts');
    }

    public function deleteDiscount($id)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('fee_discounts', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Discount deleted.');
        Response::redirect('fees/discounts');
    }

    public function updateDiscount($id)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $applicableHeads = !empty($data['applicable_heads']) ? json_encode(array_map('intval', $data['applicable_heads'])) : null;

        try {
            Database::update('fee_discounts', [
                'name'            => trim($data['name']),
                'type'            => $data['type'] ?? 'percentage',
                'value'           => (float)($data['value'] ?? 0),
                'applicable_heads'=> $applicableHeads,
                'description'     => !empty($data['description']) ? trim($data['description']) : null,
                'is_active'       => isset($data['is_active']) ? 1 : 0,
                'updated_by'      => Session::userId(),
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Discount updated.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
        }

        Response::redirect('fees/discounts');
    }

    public function assignDiscount()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $discountId = (int)($data['discount_id'] ?? 0);
        $studentId = (int)($data['student_id'] ?? 0);
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (!$discountId || !$studentId) {
            Session::flash('error', 'Please select a student.');
            Response::back(); return;
        }

        // Check if already applied
        $existing = Database::fetch(
            "SELECT id FROM student_fee_concessions WHERE student_id = ? AND fee_discount_id = ? AND school_id = ? AND academic_year_id = ?",
            [$studentId, $discountId, $schoolId, $yearId]
        );

        if ($existing) {
            Session::flash('error', 'This discount is already applied to the student.');
            Response::back(); return;
        }

        Database::insert('student_fee_concessions', [
            'student_id'      => $studentId,
            'fee_discount_id' => $discountId,
            'school_id'       => $schoolId,
            'academic_year_id'=> $yearId,
            'is_active'       => 1,
        ]);

        Session::flash('success', 'Discount applied to student.');
        Response::back();
    }

    public function removeDiscount($concessionId)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('student_fee_concessions', 'id = ? AND school_id = ?', [$concessionId, $schoolId]);
        Session::flash('success', 'Discount removed from student.');
        Response::back();
    }

    // ─── Optional Fee Assignment ──────────────────────
    public function assignOptionalFee()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $studentId = (int)($data['student_id'] ?? 0);
        $feeHeadId = (int)($data['fee_head_id'] ?? 0);
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        if (!$studentId || !$feeHeadId) {
            Session::flash('error', 'Please select a fee head.');
            Response::back(); return;
        }

        $existing = Database::fetch(
            "SELECT id FROM student_optional_fees WHERE student_id = ? AND fee_head_id = ? AND academic_year_id = ?",
            [$studentId, $feeHeadId, $yearId]
        );

        if ($existing) {
            Session::flash('error', 'This optional fee is already assigned.');
            Response::back(); return;
        }

        Database::insert('student_optional_fees', [
            'student_id'      => $studentId,
            'fee_head_id'     => $feeHeadId,
            'school_id'       => $schoolId,
            'academic_year_id'=> $yearId,
            'is_active'       => 1,
            'created_by'      => Session::userId(),
        ]);

        Session::flash('success', 'Optional fee assigned.');
        Response::back();
    }

    public function removeOptionalFee($id)
    {
        $schoolId = $this->getSchoolId();
        Database::delete('student_optional_fees', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Optional fee removed.');
        Response::back();
    }

    // ─── Collect Fee ───────────────────────────────
    public function collect($studentId = null)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        // Get student list for search
        $students = [];
        $selectedStudent = null;
        $feeStructure = [];
        $paidItems = [];

        if ($studentId) {
            $selectedStudent = Database::fetch(
                "SELECT u.id, u.full_name, u.phone, sd.admission_no, sd.class_id, sd.section_id,
                        c.name as class_name, sec.name as section_name
                 FROM users u
                 JOIN student_details sd ON u.id = sd.user_id
                 LEFT JOIN classes c ON sd.class_id = c.id
                 LEFT JOIN sections sec ON sd.section_id = sec.id
                 WHERE u.id = ? AND u.school_id = ?",
                [$studentId, $schoolId]
            );

            if ($selectedStudent) {
                // Get fee structure for this student's class
                $feeStructure = Database::fetchAll(
                    "SELECT fs.*, fh.name as head_name, fh.code as head_code, fh.is_recurring, fh.type as head_type
                     FROM fee_structures fs
                     JOIN fee_heads fh ON fs.fee_head_id = fh.id
                     WHERE fs.school_id = ? AND fs.academic_year_id = ? AND fs.class_id = ? AND fs.is_active = 1
                     ORDER BY fh.type ASC, fh.name",
                    [$schoolId, $yearId, $selectedStudent['class_id']]
                );

                // Get student's enrolled optional fees
                $enrolledOptFees = Database::fetchAll(
                    "SELECT fee_head_id FROM student_optional_fees WHERE student_id = ? AND school_id = ? AND academic_year_id = ? AND is_active = 1",
                    [$studentId, $schoolId, $yearId]
                );
                $enrolledOptHeadIds = array_column($enrolledOptFees, 'fee_head_id');

                // Filter: show mandatory always, optional only if enrolled
                $feeStructure = array_values(array_filter($feeStructure, function($fs) use ($enrolledOptHeadIds) {
                    if ($fs['head_type'] === 'optional') {
                        return in_array($fs['fee_head_id'], $enrolledOptHeadIds);
                    }
                    return true;
                }));

                // Get already paid items for this year
                $paidItems = Database::fetchAll(
                    "SELECT fpi.fee_head_id, fpi.period_label, SUM(fpi.amount) as paid_amount
                     FROM fee_payment_items fpi
                     JOIN fee_payments fp ON fpi.fee_payment_id = fp.id
                     WHERE fp.student_id = ? AND fp.school_id = ? AND fp.academic_year_id = ?
                     GROUP BY fpi.fee_head_id, fpi.period_label",
                    [$studentId, $schoolId, $yearId]
                );
            }
        }

        // Build paid map
        $paidMap = [];
        foreach ($paidItems as $pi) {
            $key = $pi['fee_head_id'] . '-' . ($pi['period_label'] ?? 'total');
            $paidMap[$key] = (float)$pi['paid_amount'];
        }

        // Student search list
        $students = Database::fetchAll(
            "SELECT u.id, u.full_name, sd.admission_no, c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.school_id = ? AND u.user_type = 'student' AND u.is_active = 1
             ORDER BY c.numeric_name, u.full_name
             LIMIT 500",
            [$schoolId]
        );

        // Discounts
        $discounts = Database::fetchAll(
            "SELECT * FROM fee_discounts WHERE school_id = ? AND is_active = 1 ORDER BY name",
            [$schoolId]
        );

        // Student's applied concessions
        $appliedDiscounts = [];
        $autoDiscountAmount = 0;
        $perHeadDiscounts = []; // fee_head_id => discount amount
        if ($selectedStudent) {
            $appliedDiscounts = Database::fetchAll(
                "SELECT sfc.*, fd.name as discount_name, fd.type as discount_type, fd.value as discount_value, fd.applicable_heads
                 FROM student_fee_concessions sfc
                 JOIN fee_discounts fd ON sfc.fee_discount_id = fd.id
                 WHERE sfc.student_id = ? AND sfc.school_id = ? AND sfc.academic_year_id = ? AND sfc.is_active = 1",
                [$studentId, $schoolId, $yearId]
            );

            // Build balance map per head
            $balanceByHead = [];
            foreach ($feeStructure as $fs) {
                $key = $fs['fee_head_id'] . '-total';
                $paid = $paidMap[$key] ?? 0;
                $balanceByHead[$fs['fee_head_id']] = max(0, $fs['amount'] - $paid);
            }

            // Calculate per-head discounts
            foreach ($appliedDiscounts as $ad) {
                $appHeads = $ad['applicable_heads'] ? json_decode($ad['applicable_heads'], true) : [];
                
                // Determine which heads this discount applies to
                $targetHeads = [];
                foreach ($feeStructure as $fs) {
                    if (empty($appHeads) || in_array($fs['fee_head_id'], $appHeads)) {
                        $targetHeads[] = $fs['fee_head_id'];
                    }
                }

                if (empty($targetHeads)) continue;

                if ($ad['discount_type'] === 'fixed') {
                    // Distribute fixed discount proportionally among applicable heads
                    $totalApplicable = 0;
                    foreach ($targetHeads as $hid) {
                        $totalApplicable += ($balanceByHead[$hid] ?? 0);
                    }
                    if ($totalApplicable > 0) {
                        foreach ($targetHeads as $hid) {
                            $share = round((($balanceByHead[$hid] ?? 0) / $totalApplicable) * (float)$ad['discount_value'], 2);
                            $perHeadDiscounts[$hid] = ($perHeadDiscounts[$hid] ?? 0) + $share;
                        }
                    }
                    $autoDiscountAmount += (float)$ad['discount_value'];
                } elseif ($ad['discount_type'] === 'percentage') {
                    foreach ($targetHeads as $hid) {
                        $disc = round(($balanceByHead[$hid] ?? 0) * (float)$ad['discount_value'] / 100, 2);
                        $perHeadDiscounts[$hid] = ($perHeadDiscounts[$hid] ?? 0) + $disc;
                        $autoDiscountAmount += $disc;
                    }
                }
            }
            $autoDiscountAmount = round($autoDiscountAmount, 2);
        }

        Response::view('fees/collect', [
            'pageTitle'         => 'Collect Fee',
            'students'          => $students,
            'selectedStudent'   => $selectedStudent,
            'feeStructure'      => $feeStructure,
            'paidMap'           => $paidMap,
            'discounts'         => $discounts,
            'appliedDiscounts'  => $appliedDiscounts,
            'autoDiscountAmount'=> $autoDiscountAmount,
            'perHeadDiscounts'  => $perHeadDiscounts,
            'currentYear'       => $currentYear,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Collect Fee'],
            ],
        ]);
    }

    public function processPayment()
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $studentId = (int)($data['student_id'] ?? 0);
        $paymentMode = $data['payment_mode'] ?? 'cash';
        $remarks = trim($data['remarks'] ?? '');
        $items = $data['items'] ?? [];

        if (!$studentId || empty($items)) {
            Session::flash('error', 'Select a student and at least one fee item.');
            Response::back(); return;
        }

        try {
            Database::beginTransaction();

            // Calculate totals
            $totalAmount = 0;
            $validItems = [];
            foreach ($items as $item) {
                $amt = (float)($item['amount'] ?? 0);
                if ($amt > 0) {
                    $totalAmount += $amt;
                    $validItems[] = $item;
                }
            }

            if ($totalAmount <= 0) {
                Session::flash('error', 'Payment amount must be greater than 0.');
                Response::back(); return;
            }

            // Discount
            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $netAmount = $totalAmount - $discountAmount;

            // Generate receipt number
            $receiptNo = $this->generateReceiptNumber($schoolId);

            // Insert payment
            $paymentId = Database::insert('fee_payments', [
                'school_id'        => $schoolId,
                'student_id'       => $studentId,
                'receipt_number'   => $receiptNo,
                'total_amount'     => $totalAmount,
                'discount_amount'  => $discountAmount,
                'net_amount'       => $netAmount,
                'payment_date'     => $data['payment_date'] ?? date('Y-m-d'),
                'payment_mode'     => $paymentMode,
                'transaction_ref'  => !empty($data['transaction_ref']) ? trim($data['transaction_ref']) : null,
                'collected_by'     => Session::userId(),
                'remarks'          => $remarks ?: null,
                'academic_year_id' => $yearId,
            ]);

            // Insert line items
            foreach ($validItems as $item) {
                Database::insert('fee_payment_items', [
                    'fee_payment_id' => $paymentId,
                    'fee_head_id'    => (int)$item['fee_head_id'],
                    'amount'         => (float)$item['amount'],
                    'period_label'   => !empty($item['period_label']) ? $item['period_label'] : null,
                ]);
            }

            Database::commit();
            Session::flash('success', "Payment of ₹" . number_format($netAmount, 2) . " collected. Receipt: {$receiptNo}");
            Response::redirect('fees/receipt/' . $paymentId);

        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Payment failed: ' . $e->getMessage());
            Response::back();
        }
    }

    // ─── Receipt ───────────────────────────────────
    public function receipt($id)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $payment = Database::fetch(
            "SELECT fp.*, u.full_name as student_name, u.phone as student_phone,
                    sd.admission_no, c.name as class_name, sec.name as section_name,
                    col.full_name as collected_by_name,
                    s.name as school_name, s.address as school_address, s.phone as school_phone,
                    s.email as school_email, s.logo as school_logo
             FROM fee_payments fp
             JOIN users u ON fp.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             LEFT JOIN users col ON fp.collected_by = col.id
             JOIN schools s ON fp.school_id = s.id
             WHERE fp.id = ? AND fp.school_id = ?",
            [$id, $schoolId]
        );

        if (!$payment) { Response::abort(404); return; }

        $items = Database::fetchAll(
            "SELECT fpi.*, fh.name as head_name, fh.code as head_code
             FROM fee_payment_items fpi
             JOIN fee_heads fh ON fpi.fee_head_id = fh.id
             WHERE fpi.fee_payment_id = ?
             ORDER BY fh.name",
            [$id]
        );

        Response::view('fees/receipt', [
            'pageTitle' => 'Receipt #' . $payment['receipt_number'],
            'payment'   => $payment,
            'items'     => $items,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Receipt #' . $payment['receipt_number']],
            ],
        ]);
    }

    // ─── Reports ───────────────────────────────────
    public function report()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $currentYear = $this->getCurrentAcademicYear($schoolId);
        $yearId = $currentYear['id'] ?? 0;

        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $classId = $_GET['class_id'] ?? '';
        $mode = $_GET['mode'] ?? '';

        $where = "fp.school_id = ? AND fp.payment_date BETWEEN ? AND ?";
        $params = [$schoolId, $dateFrom, $dateTo];

        if ($classId) {
            $where .= " AND sd.class_id = ?";
            $params[] = (int)$classId;
        }
        if ($mode) {
            $where .= " AND fp.payment_mode = ?";
            $params[] = $mode;
        }

        $payments = Database::fetchAll(
            "SELECT fp.*, u.full_name as student_name, sd.admission_no,
                    c.name as class_name, sec.name as section_name
             FROM fee_payments fp
             JOIN users u ON fp.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE {$where}
             ORDER BY fp.payment_date DESC, fp.id DESC",
            $params
        );

        $totalAmount = array_sum(array_column($payments, 'net_amount'));

        $classes = Database::fetchAll(
            "SELECT id, name FROM classes WHERE school_id = ? AND academic_year_id = ? ORDER BY numeric_name",
            [$schoolId, $yearId]
        );

        Response::view('fees/report', [
            'pageTitle'   => 'Fee Reports',
            'payments'    => $payments,
            'totalAmount' => $totalAmount,
            'classes'     => $classes,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'classId'     => $classId,
            'mode'        => $mode,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Reports'],
            ],
        ]);
    }

    // ─── Student Ledger ────────────────────────────
    public function studentLedger($studentId)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $student = Database::fetch(
            "SELECT u.id, u.full_name, sd.admission_no, c.name as class_name, sec.name as section_name
             FROM users u
             JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.id = ? AND u.school_id = ?",
            [$studentId, $schoolId]
        );

        if (!$student) { Response::abort(404); return; }

        $payments = Database::fetchAll(
            "SELECT fp.*, GROUP_CONCAT(fh.name SEPARATOR ', ') as head_names
             FROM fee_payments fp
             JOIN fee_payment_items fpi ON fp.id = fpi.fee_payment_id
             JOIN fee_heads fh ON fpi.fee_head_id = fh.id
             WHERE fp.student_id = ? AND fp.school_id = ?
             GROUP BY fp.id
             ORDER BY fp.payment_date DESC",
            [$studentId, $schoolId]
        );

        $totalPaid = array_sum(array_column($payments, 'net_amount'));

        Response::view('fees/student-ledger', [
            'pageTitle'  => 'Fee Ledger: ' . $student['full_name'],
            'student'    => $student,
            'payments'   => $payments,
            'totalPaid'  => $totalPaid,
            'breadcrumbs' => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => $student['full_name']],
            ],
        ]);
    }

    // ─── Helpers ────────────────────────────────────
    private function generateReceiptNumber(int $schoolId): string
    {
        $prefix = 'RCP';
        $last = Database::fetch(
            "SELECT receipt_number FROM fee_payments WHERE school_id = ? ORDER BY id DESC LIMIT 1",
            [$schoolId]
        );

        $nextNum = 1;
        if ($last && preg_match('/(\d+)$/', $last['receipt_number'], $m)) {
            $nextNum = (int)$m[1] + 1;
        }

        return $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    // ─── Request Receipt Cancellation ──────────────
    public function requestCancel($paymentId)
    {
        $schoolId = $this->getSchoolId();
        $data = $_POST;
        $reason = trim($data['reason'] ?? '');

        if (empty($reason)) {
            Session::flash('error', 'Cancellation reason is required.');
            Response::back(); return;
        }

        // Check payment exists and is active
        $payment = Database::fetch(
            "SELECT id, status, receipt_number FROM fee_payments WHERE id = ? AND school_id = ?",
            [$paymentId, $schoolId]
        );

        if (!$payment || $payment['status'] !== 'active') {
            Session::flash('error', 'Receipt not found or already cancelled.');
            Response::back(); return;
        }

        // Check if a pending request already exists
        $existing = Database::fetch(
            "SELECT id FROM fee_cancellation_requests WHERE fee_payment_id = ? AND status = 'pending'",
            [$paymentId]
        );

        if ($existing) {
            Session::flash('error', 'A cancellation request is already pending for this receipt.');
            Response::back(); return;
        }

        Database::insert('fee_cancellation_requests', [
            'school_id'      => $schoolId,
            'fee_payment_id' => $paymentId,
            'requested_by'   => Session::userId(),
            'reason'         => $reason,
        ]);

        Session::flash('success', "Cancellation request submitted for Receipt #{$payment['receipt_number']}. Awaiting approval.");
        Response::redirect('fees/receipt/' . $paymentId);
    }

    // ─── Approvals Page ────────────────────────────
    public function approvals()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId || !Session::hasPermission('fees.approve')) {
            Session::flash('error', 'You do not have permission to approve cancellations.');
            Response::redirect('fees'); return;
        }

        $requests = Database::fetchAll(
            "SELECT cr.*, fp.receipt_number, fp.net_amount, fp.payment_date, fp.status as payment_status,
                    u.full_name as student_name, sd.admission_no,
                    c.name as class_name, sec.name as section_name,
                    req.full_name as requested_by_name,
                    rev.full_name as reviewed_by_name
             FROM fee_cancellation_requests cr
             JOIN fee_payments fp ON cr.fee_payment_id = fp.id
             JOIN users u ON fp.student_id = u.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             JOIN users req ON cr.requested_by = req.id
             LEFT JOIN users rev ON cr.reviewed_by = rev.id
             WHERE cr.school_id = ?
             ORDER BY FIELD(cr.status, 'pending', 'approved', 'rejected'), cr.created_at DESC",
            [$schoolId]
        );

        $pendingCount = 0;
        foreach ($requests as $r) { if ($r['status'] === 'pending') $pendingCount++; }

        Response::view('fees/approvals', [
            'pageTitle'    => 'Cancellation Approvals',
            'requests'     => $requests,
            'pendingCount' => $pendingCount,
            'breadcrumbs'  => [
                ['label' => 'Fees', 'url' => APP_URL . '/fees'],
                ['label' => 'Cancellation Approvals'],
            ],
        ]);
    }

    // ─── Approve Cancellation ──────────────────────
    public function approveCancel($requestId)
    {
        $schoolId = $this->getSchoolId();
        if (!Session::hasPermission('fees.approve')) {
            Session::flash('error', 'You do not have permission to approve cancellations.');
            Response::redirect('fees'); return;
        }
        $data = $_POST;

        $request = Database::fetch(
            "SELECT cr.*, fp.id as payment_id, fp.receipt_number
             FROM fee_cancellation_requests cr
             JOIN fee_payments fp ON cr.fee_payment_id = fp.id
             WHERE cr.id = ? AND cr.school_id = ? AND cr.status = 'pending'",
            [$requestId, $schoolId]
        );

        if (!$request) {
            Session::flash('error', 'Request not found or already processed.');
            Response::redirect('fees/approvals'); return;
        }

        try {
            Database::beginTransaction();

            // Update request status
            Database::update('fee_cancellation_requests', [
                'status'         => 'approved',
                'reviewed_by'    => Session::userId(),
                'review_remarks' => trim($data['review_remarks'] ?? '') ?: null,
                'reviewed_at'    => date('Y-m-d H:i:s'),
            ], 'id = ?', [$requestId]);

            // Cancel the payment
            Database::update('fee_payments', [
                'status'        => 'cancelled',
                'cancelled_by'  => Session::userId(),
                'cancelled_at'  => date('Y-m-d H:i:s'),
                'cancel_reason' => $request['reason'],
            ], 'id = ?', [$request['payment_id']]);

            Database::commit();
            Session::flash('success', "Receipt #{$request['receipt_number']} cancelled successfully.");
        } catch (\Exception $e) {
            Database::rollback();
            Session::flash('error', 'Failed: ' . $e->getMessage());
        }

        Response::redirect('fees/approvals');
    }

    // ─── Reject Cancellation ───────────────────────
    public function rejectCancel($requestId)
    {
        $schoolId = $this->getSchoolId();
        if (!Session::hasPermission('fees.approve')) {
            Session::flash('error', 'You do not have permission to reject cancellations.');
            Response::redirect('fees'); return;
        }
        $data = $_POST;

        Database::update('fee_cancellation_requests', [
            'status'         => 'rejected',
            'reviewed_by'    => Session::userId(),
            'review_remarks' => trim($data['review_remarks'] ?? '') ?: 'Rejected',
            'reviewed_at'    => date('Y-m-d H:i:s'),
        ], 'id = ? AND school_id = ? AND status = ?', [$requestId, $schoolId, 'pending']);

        Session::flash('success', 'Cancellation request rejected.');
        Response::redirect('fees/approvals');
    }
}
