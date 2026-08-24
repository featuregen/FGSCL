<?php
/**
 * EduGen — Front Controller
 * All requests are routed through this file
 */

// ─── Bootstrap ──────────────────────────────────────────
require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';
require_once APP_PATH . '/Helpers/Session.php';
require_once APP_PATH . '/Helpers/Validator.php';
require_once APP_PATH . '/Helpers/Response.php';

// Start session
Session::start();

// ─── Route Parsing ──────────────────────────────────────
$route = $_GET['route'] ?? '';
$route = trim($route, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Parse route into segments
$segments = $route ? explode('/', $route) : [];
$GLOBALS['_segments'] = $segments;
$module = $segments[0] ?? 'dashboard';
$action = $segments[1] ?? 'index';
$id     = $segments[2] ?? null;
$extra  = $segments[3] ?? null;

// ─── Public Routes (no auth required) ───────────────────
$publicRoutes = [
    'auth/login',
    'auth/do-login',
    'auth/forgot-password',
    'auth/send-otp',
    'auth/verify-otp',
    'auth/reset-password',
    'auth/do-reset-password',
    'admission/online',
    'admission/submit',
];

$currentRoute = $module . '/' . $action;

// ─── Auth Check ─────────────────────────────────────────
if (!in_array($currentRoute, $publicRoutes) && $module !== 'assets') {
    // Check if user is logged in
    if (!Session::isLoggedIn()) {
        if ($method === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::error('Session expired. Please login again.', 401);
        }
        Response::redirect('auth/login');
    }
    
    // Check session expiry
    if (Session::isExpired()) {
        Session::destroy();
        Session::start();
        Session::flash('warning', 'Your session has expired. Please login again.');
        Response::redirect('auth/login');
    }
    
    // Update last activity
    Session::touch();
}

// ─── CSRF Check for POST requests ───────────────────────
if ($method === 'POST' && !in_array($module, ['api'])) {
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    // Skip CSRF for AJAX requests with proper header
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if (!$isAjax && !empty($token) && !Session::verifyCsrf($token)) {
        Session::flash('error', 'Invalid security token. Please try again.');
        Response::back();
    }
}

// ─── Activity Logging (for authenticated users) ─────────
if (Session::isLoggedIn() && $method === 'POST') {
    require_once APP_PATH . '/Middleware/ActivityLogger.php';
    ActivityLogger::log($module, $action);
}

// ─── Route Mapping ──────────────────────────────────────
// Map URL routes to controller actions
$routes = [
    // Auth
    'auth' => 'AuthController',
    
    // Dashboard
    'dashboard' => 'DashboardController',
    
    // User Management
    'users' => 'UserController',
    
    // Super Admin
    'super-admin' => 'SuperAdmin/SuperAdminController',
    'schools' => 'SuperAdmin/SchoolController',
    'subscriptions' => 'SuperAdmin/SubscriptionController',
    'plans' => 'SuperAdmin/PlanController',
    
    // School Setup (Phase 2)
    'school-setup' => 'SchoolSetupController',
    'academic' => 'AcademicController',
    'masters' => 'MasterController',
    
    // Students (Phase 3)
    'students' => 'StudentController',
    'admission' => 'AdmissionController',
    
    // Staff (Phase 4)
    'staff' => 'StaffController',
    'payroll' => 'PayrollController',
    'leave' => 'LeaveController',
    
    // Attendance (Phase 5)
    'attendance' => 'AttendanceController',
    'staff-attendance' => 'StaffAttendanceController',
    
    // Timetable (Phase 6)
    'timetable' => 'TimetableController',
    
    // Exams (Phase 7)
    'exams' => 'ExamController',
    'marks' => 'MarksController',
    
    // Fees (Phase 8)
    'fees' => 'FeeController',
    'payments' => 'PaymentController',
    
    // Homework (Phase 9)
    'homework' => 'HomeworkController',
    
    // Communication (Phase 10)
    'communication' => 'CommunicationController',
    
    // Library (Phase 11)
    'library' => 'LibraryController',
    
    // Transport (Phase 12)
    'transport' => 'TransportController',
    
    // Hostel (Phase 13)
    'hostel' => 'HostelController',
    
    // Inventory (Phase 14)
    'inventory' => 'InventoryController',
    
    // Visitors (Phase 15)
    'visitors' => 'VisitorController',
    
    // Certificates (Phase 16)
    'certificates' => 'CertificateController',
    
    // Reports (Phase 17)
    'reports' => 'ReportController',

    // Profile / Settings
    'profile' => 'ProfileController',
    'settings' => 'SettingsController',
];

// ─── Controller Dispatch ────────────────────────────────
if (isset($routes[$module])) {
    $controllerFile = APP_PATH . '/Controllers/' . $routes[$module] . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        // Get class name from file path
        $className = basename($routes[$module]);
        
        if (class_exists($className)) {
            $controller = new $className();
            
            // Convert action to camelCase method name
            $methodName = lcfirst(str_replace('-', '', ucwords($action, '-')));
            
            // Handle special method mappings
            $methodMap = [
                'index'  => 'index',
                'create' => 'create',
                'store'  => 'store',
                'edit'   => 'edit',
                'update' => 'update',
                'delete' => 'delete',
                'view'   => 'show',
                'show'   => 'show',
            ];
            
            $methodName = $methodMap[$action] ?? $methodName;
            
            if (method_exists($controller, $methodName)) {
                $controller->$methodName($id, $extra);
            } else {
                // Try HTTP method specific: getIndex, postStore, etc.
                $httpMethod = strtolower($method) . ucfirst($methodName);
                if (method_exists($controller, $httpMethod)) {
                    $controller->$httpMethod($id, $extra);
                } else {
                    Response::abort(404, "Action '{$action}' not found in {$className}.");
                }
            }
        } else {
            Response::abort(500, "Controller class '{$className}' not found.");
        }
    } else {
        Response::abort(404, "Module '{$module}' not found.");
    }
} else {
    // Default: redirect to dashboard
    if (empty($route)) {
        Response::redirect('dashboard');
    } else {
        Response::abort(404);
    }
}
