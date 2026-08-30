<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    Database::query("ALTER TABLE `sections` ADD COLUMN `class_teacher_id` INT UNSIGNED DEFAULT NULL AFTER `capacity`");
    echo "<h2 style='color:green'>SUCCESS: 'class_teacher_id' added to sections!</h2>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h2 style='color:blue'>INFO: 'class_teacher_id' ALREADY EXISTS in sections.</h2>";
    } else {
        echo "<h2 style='color:red'>ERROR (sections): " . htmlspecialchars($e->getMessage()) . "</h2>";
    }
}

try {
    Database::query("ALTER TABLE `class_subjects` ADD COLUMN `teacher_id` INT UNSIGNED DEFAULT NULL AFTER `subject_id`");
    echo "<h2 style='color:green'>SUCCESS: 'teacher_id' added to class_subjects!</h2>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h2 style='color:blue'>INFO: 'teacher_id' ALREADY EXISTS in class_subjects.</h2>";
    } else {
        echo "<h2 style='color:red'>ERROR (class_subjects): " . htmlspecialchars($e->getMessage()) . "</h2>";
    }
}

echo "<p><a href='" . APP_URL . "/school-setup/classes?year_id=1'>Click here to return to Classes page</a></p>";

