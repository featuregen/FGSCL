<?php
/**
 * Payroll Controller
 * Staff Salary & Payslips
 */

class PayrollController
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

        $month = $_GET['month'] ?? date('n');
        $year = $_GET['year'] ?? date('Y');

        $payrolls = Database::fetchAll(
            "SELECT p.*, u.full_name as staff_name, u.email as staff_email,
                    d.name as department_name, des.name as designation_name
             FROM payrolls p
             JOIN users u ON p.user_id = u.id
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             LEFT JOIN designations des ON sd.designation_id = des.id
             WHERE p.school_id = ? AND p.month = ? AND p.year = ?
             ORDER BY u.full_name",
            [$schoolId, $month, $year]
        );

        $currencyRes = Database::fetch("SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = 'currency'", [$schoolId]);
        $currency = $currencyRes['setting_value'] ?? '$';

        Response::view('payroll/index', [
            'pageTitle' => "Payroll - " . date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'payrolls' => $payrolls,
            'month' => $month,
            'year' => $year,
            'currency' => $currency,
            'breadcrumbs' => [['label' => 'HR & Payroll']]
        ]);
    }

    public function generate()
    {
        $schoolId = $this->getSchoolId();
        
        $month = $_POST['month'] ?? date('n');
        $year = $_POST['year'] ?? date('Y');

        // Find staff who have a payroll structure but no payroll generated for this month
        $staffWithStructure = Database::fetchAll(
            "SELECT ps.*, u.full_name 
             FROM payroll_structures ps
             JOIN users u ON ps.user_id = u.id
             WHERE ps.school_id = ? AND u.is_active = 1",
            [$schoolId]
        );

        $generatedCount = 0;
        $updatedCount = 0;
        foreach ($staffWithStructure as $staff) {
            $exists = Database::fetch(
                "SELECT id, status FROM payrolls WHERE school_id = ? AND user_id = ? AND month = ? AND year = ?",
                [$schoolId, $staff['user_id'], $month, $year]
            );

            if (!$exists) {
                Database::insert('payrolls', [
                    'school_id' => $schoolId,
                    'user_id' => $staff['user_id'],
                    'month' => $month,
                    'year' => $year,
                    'basic_salary' => $staff['basic_salary'],
                    'allowances_json' => $staff['allowances_json'],
                    'deductions_json' => $staff['deductions_json'],
                    'net_salary' => $staff['net_salary'],
                    'status' => 'generated',
                    'generated_by' => Session::userId()
                ]);
                $generatedCount++;
            } elseif ($exists['status'] === 'generated') {
                // Regenerate if not paid yet
                Database::update('payrolls', [
                    'basic_salary' => $staff['basic_salary'],
                    'allowances_json' => $staff['allowances_json'],
                    'deductions_json' => $staff['deductions_json'],
                    'net_salary' => $staff['net_salary'],
                    'generated_by' => Session::userId()
                ], 'id = ?', [$exists['id']]);
                $updatedCount++;
            }
        }

        Session::flash('success', "Generated $generatedCount new payslips. Regenerated $updatedCount existing payslips.");
        Response::redirect("payroll?month=$month&year=$year");
    }

    public function regenerate($id)
    {
        $schoolId = $this->getSchoolId();
        
        $payslip = Database::fetch("SELECT * FROM payrolls WHERE id = ? AND school_id = ?", [$id, $schoolId]);
        if (!$payslip || $payslip['status'] === 'paid') {
            Session::flash('error', 'Cannot regenerate. Payslip not found or already paid.');
            Response::redirect("payroll");
            return;
        }

        $structure = Database::fetch("SELECT * FROM payroll_structures WHERE user_id = ? AND school_id = ?", [$payslip['user_id'], $schoolId]);
        if (!$structure) {
            Session::flash('error', 'No salary structure found for this staff member.');
            Response::redirect("payroll?month={$payslip['month']}&year={$payslip['year']}");
            return;
        }

        Database::update('payrolls', [
            'basic_salary' => $structure['basic_salary'],
            'allowances_json' => $structure['allowances_json'],
            'deductions_json' => $structure['deductions_json'],
            'net_salary' => $structure['net_salary'],
            'generated_by' => Session::userId()
        ], 'id = ?', [$id]);

        Session::flash('success', 'Payslip regenerated successfully from the latest salary structure.');
        Response::redirect("payroll?month={$payslip['month']}&year={$payslip['year']}");
    }

    public function payslip($id)
    {
        $schoolId = $this->getSchoolId();
        
        $payslip = Database::fetch(
            "SELECT p.*, u.full_name as staff_name, u.email as staff_email,
                    d.name as department_name, des.name as designation_name
             FROM payrolls p
             JOIN users u ON p.user_id = u.id
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             LEFT JOIN designations des ON sd.designation_id = des.id
             WHERE p.id = ? AND p.school_id = ?",
            [$id, $schoolId]
        );

        if (!$payslip) {
            Response::abort(404);
            return;
        }

        $currencyRes = Database::fetch("SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = 'currency'", [$schoolId]);
        $currency = $currencyRes['setting_value'] ?? '$';

        $school = Database::fetch("SELECT name, logo, address, phone, email FROM schools WHERE id = ?", [$schoolId]);

        // We use a standalone view layout for printing
        require VIEW_PATH . '/payroll/payslip.php';
    }

    public function markPaid($id)
    {
        $schoolId = $this->getSchoolId();
        
        Database::update('payrolls', [
            'status' => 'paid',
            'payment_date' => date('Y-m-d'),
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'transaction_ref' => $_POST['transaction_ref'] ?? null
        ], 'id = ? AND school_id = ?', [$id, $schoolId]);

        Session::flash('success', 'Payslip marked as paid.');
        Response::back();
    }

    public function structures()
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $staffList = Database::fetchAll(
            "SELECT u.id, u.full_name, u.email, d.name as department_name, des.name as designation_name,
                    ps.basic_salary, ps.allowances_json, ps.deductions_json, ps.net_salary
             FROM users u
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             LEFT JOIN designations des ON sd.designation_id = des.id
             LEFT JOIN payroll_structures ps ON u.id = ps.user_id AND ps.school_id = ?
             WHERE u.school_id = ? AND u.user_type NOT IN ('student', 'parent_user', 'super_admin') AND u.is_active = 1
             ORDER BY u.full_name",
            [$schoolId, $schoolId]
        );

        $currencyRes = Database::fetch("SELECT setting_value FROM school_settings WHERE school_id = ? AND setting_key = 'currency'", [$schoolId]);
        $currency = $currencyRes['setting_value'] ?? '$';

        Response::view('payroll/structures', [
            'pageTitle' => 'Salary Structures',
            'staffList' => $staffList,
            'currency' => $currency,
            'breadcrumbs' => [['label' => 'HR & Payroll']]
        ]);
    }

    public function saveStructure($userId)
    {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) { Response::abort(403); return; }

        $basicSalary = floatval($_POST['basic_salary'] ?? 0);
        
        $allowances = [];
        $totalAllowances = 0;
        if (!empty($_POST['allowance_name']) && is_array($_POST['allowance_name'])) {
            foreach ($_POST['allowance_name'] as $i => $name) {
                if (trim($name) !== '') {
                    $amt = floatval($_POST['allowance_amount'][$i] ?? 0);
                    $allowances[] = ['name' => trim($name), 'amount' => $amt];
                    $totalAllowances += $amt;
                }
            }
        }

        $deductions = [];
        $totalDeductions = 0;
        if (!empty($_POST['deduction_name']) && is_array($_POST['deduction_name'])) {
            foreach ($_POST['deduction_name'] as $i => $name) {
                if (trim($name) !== '') {
                    $amt = floatval($_POST['deduction_amount'][$i] ?? 0);
                    $deductions[] = ['name' => trim($name), 'amount' => $amt];
                    $totalDeductions += $amt;
                }
            }
        }

        $netSalary = $basicSalary + $totalAllowances - $totalDeductions;

        // Check if exists
        $exists = Database::fetch("SELECT id FROM payroll_structures WHERE school_id = ? AND user_id = ?", [$schoolId, $userId]);

        if ($exists) {
            Database::update('payroll_structures', [
                'basic_salary' => $basicSalary,
                'allowances_json' => json_encode($allowances),
                'deductions_json' => json_encode($deductions),
                'net_salary' => $netSalary
            ], 'id = ?', [$exists['id']]);
        } else {
            Database::insert('payroll_structures', [
                'school_id' => $schoolId,
                'user_id' => $userId,
                'basic_salary' => $basicSalary,
                'allowances_json' => json_encode($allowances),
                'deductions_json' => json_encode($deductions),
                'net_salary' => $netSalary
            ]);
        }

        Session::flash('success', 'Salary structure updated successfully.');
        Response::redirect('payroll/structures');
    }
}
