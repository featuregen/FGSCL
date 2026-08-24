<?php
// Quick session debug - DELETE THIS FILE AFTER TESTING
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Session Debug</h2>";

// Show session config
echo "<h3>Session Config:</h3>";
echo "save_path: " . session_save_path() . "<br>";
echo "cookie_path: " . ini_get('session.cookie_path') . "<br>";
echo "cookie_domain: " . ini_get('session.cookie_domain') . "<br>";
echo "cookie_secure: " . ini_get('session.cookie_secure') . "<br>";
echo "cookie_samesite: " . ini_get('session.cookie_samesite') . "<br>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'not set') . "<br>";

// Test session
session_start();

if (isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter']++;
    echo "<h3 style='color:green;'>✅ Session is WORKING! Counter: " . $_SESSION['test_counter'] . "</h3>";
} else {
    $_SESSION['test_counter'] = 1;
    echo "<h3 style='color:orange;'>⏳ Session started. Refresh page to verify persistence.</h3>";
}

echo "<h3>Session ID:</h3>" . session_id();
echo "<h3>Session Data:</h3><pre>" . print_r($_SESSION, true) . "</pre>";

// Show APP config
require_once dirname(__DIR__) . '/config/app.php';
echo "<h3>App Config:</h3>";
echo "APP_URL: " . APP_URL . "<br>";
echo "APP_ENV: " . APP_ENV . "<br>";
