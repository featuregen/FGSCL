<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

echo "<h1>Forcing All Migrations & Seeds</h1>";
echo "<pre style='background:#111; color:#0f0; padding:20px; height: 500px; overflow: auto;'>";

$pdo = Database::pdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Migrations
$migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    echo "Running: " . basename($file) . "\n";
    $sql = file_get_contents($file);
    
    // Split by statement (rough approximation for basic scripts)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $pdo->exec($stmt);
        } catch (Exception $e) {
            echo "  [IGNORED] " . $e->getMessage() . "\n";
        }
    }
}

// 2. Seeds
$seedFiles = glob(__DIR__ . '/../database/seeds/*.sql');
sort($seedFiles);

foreach ($seedFiles as $file) {
    echo "Running Seed: " . basename($file) . "\n";
    $sql = file_get_contents($file);
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $pdo->exec($stmt);
        } catch (Exception $e) {
            echo "  [IGNORED] " . $e->getMessage() . "\n";
        }
    }
}

echo "\n\nDONE! All tables should now exist.</pre>";
echo "<p><a href='" . APP_URL . "/dashboard' style='font-size:20px; font-weight:bold;'>Return to Dashboard</a></p>";
