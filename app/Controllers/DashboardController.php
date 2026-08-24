<?php
/**
 * Dashboard Controller
 * Role-specific dashboard rendering
 */

require_once APP_PATH . '/Models/User.php';

class DashboardController
{
    public function __construct()
    {
        // Ensure authenticated
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main dashboard — routes to role-specific view
     */
    public function index(): void
    {
        $user = Session::user();
        $role = $user['role_slug'] ?? $user['user_type'];

        $data = [
            'pageTitle' => 'Dashboard',
            'user'      => $user,
        ];

        switch ($role) {
            case ROLE_SUPER_ADMIN:
                $data = array_merge($data, $this->getSuperAdminData());
                Response::view('dashboard.super-admin', $data);
                break;
            
            case ROLE_SCHOOL_ADMIN:
            case ROLE_PRINCIPAL:
                $data = array_merge($data, $this->getSchoolAdminData());
                Response::view('dashboard.school-admin', $data);
                break;
            
            default:
                Response::view('dashboard.index', $data);
                break;
        }
    }

    /**
     * Super Admin dashboard data
     */
    private function getSuperAdminData(): array
    {
        return [
            'totalSchools'       => Database::count('schools'),
            'activeSchools'      => Database::count('schools', 'is_active = 1'),
            'totalUsers'         => Database::count('users'),
            'activeSubscriptions'=> Database::count('subscriptions', "status = 'active'"),
            'recentSchools'      => Database::fetchAll(
                "SELECT s.*, p.name as plan_name, sub.status as sub_status, sub.end_date 
                 FROM schools s 
                 LEFT JOIN subscriptions sub ON s.id = sub.school_id AND sub.status = 'active'
                 LEFT JOIN plans p ON sub.plan_id = p.id
                 ORDER BY s.created_at DESC LIMIT 5"
            ),
            'recentActivity'     => Database::fetchAll(
                "SELECT al.*, u.full_name, u.avatar 
                 FROM activity_logs al 
                 JOIN users u ON al.user_id = u.id 
                 ORDER BY al.created_at DESC LIMIT 10"
            ),
            'planDistribution'   => Database::fetchAll(
                "SELECT p.name, COUNT(sub.id) as count 
                 FROM plans p 
                 LEFT JOIN subscriptions sub ON p.id = sub.plan_id AND sub.status = 'active' 
                 GROUP BY p.id, p.name ORDER BY p.sort_order"
            ),
        ];
    }

    /**
     * School Admin / Principal dashboard data
     */
    private function getSchoolAdminData(): array
    {
        $schoolId = Session::schoolId();
        
        if (!$schoolId) {
            return ['stats' => []];
        }

        // Get current academic year
        $currentYear = Database::fetch(
            "SELECT * FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1",
            [$schoolId]
        );
        $yearId = $currentYear['id'] ?? 0;

        // Active students for current academic year
        $activeStudents = 0;
        if ($yearId) {
            $row = Database::fetch(
                "SELECT COUNT(*) as cnt FROM student_details sd 
                 JOIN users u ON sd.user_id = u.id 
                 WHERE sd.school_id = ? AND sd.academic_year_id = ? AND sd.status = 'active' AND u.is_active = 1",
                [$schoolId, $yearId]
            );
            $activeStudents = (int)($row['cnt'] ?? 0);
        }

        return [
            'totalStudents'  => $activeStudents,
            'currentYear'    => $currentYear,
            'totalTeachers'  => Database::count('users', "school_id = ? AND user_type = 'teacher' AND is_active = 1", [$schoolId]),
            'totalStaff'     => Database::count('users', "school_id = ? AND user_type IN ('staff','teacher','accountant','librarian','transport_manager') AND is_active = 1", [$schoolId]),
            'totalParents'   => Database::count('users', "school_id = ? AND user_type = 'parent' AND is_active = 1", [$schoolId]),
            'recentActivity' => Database::fetchAll(
                "SELECT al.*, u.full_name, u.avatar 
                 FROM activity_logs al 
                 JOIN users u ON al.user_id = u.id 
                 WHERE u.school_id = ? 
                 ORDER BY al.created_at DESC LIMIT 10",
                [$schoolId]
            ),
        ];
    }
}
