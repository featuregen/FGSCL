<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    Database::query("ALTER TABLE `sections` ADD COLUMN `class_teacher_id` INT UNSIGNED DEFAULT NULL AFTER `capacity`");
    echo "<h1 style='color:green'>SUCCESS: Column 'class_teacher_id' was added successfully!</h1>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h1 style='color:blue'>SUCCESS: Column 'class_teacher_id' ALREADY EXISTS. No action needed.</h1>";
    } else {
        echo "<h1 style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</h1>";
    }
}
echo "<p><a href='" . APP_URL . "/school-setup/classes?year_id=1'>Click here to return to Classes page</a></p>";
