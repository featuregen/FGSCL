<?php
// Debug login flow - DELETE AFTER TESTING
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';
require_once APP_PATH . '/Helpers/Session.php';

// Start session exactly like the app does
Session::start();

echo "<h2>Login Flow Debug</h2>";
echo "<pre>";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.auto_start: " . ini_get('session.auto_start') . "\n";
echo "APP_URL: " . APP_URL . "\n";
echo "APP_ENV: " . APP_ENV . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "\n\n";

echo "SESSION DATA:\n";
print_r($_SESSION);

echo "\n--- Is Logged In: " . (Session::isLoggedIn() ? 'YES' : 'NO') . " ---\n";

// If ?login=1, simulate setting user data
if (isset($_GET['login'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'super_admin';
    $_SESSION['user_data'] = ['id' => 1, 'full_name' => 'Test'];
    $_SESSION['_created'] = time();
    $_SESSION['last_activity'] = time();
    echo "\n*** Set user_id=1 and user_role=super_admin ***\n";
    echo "*** Now visit this page WITHOUT ?login to check persistence ***\n";
}

echo "</pre>";
