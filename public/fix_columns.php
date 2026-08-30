<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = Database::pdo();
    
    $commands = [
        "ALTER TABLE transport_routes ADD COLUMN vehicle_id INT NULL DEFAULT NULL AFTER school_id" => "Added vehicle_id to transport_routes",
        "ALTER TABLE leave_types ADD COLUMN is_paid TINYINT(1) NOT NULL DEFAULT 1 AFTER name" => "Added is_paid to leave_types",
        "ALTER TABLE library_issues ADD COLUMN issued_by INT NULL DEFAULT NULL AFTER return_date" => "Added issued_by to library_issues",
        "ALTER TABLE hostel_rooms ADD COLUMN hostel_id INT NULL DEFAULT NULL AFTER school_id" => "Added hostel_id to hostel_rooms",
        "ALTER TABLE hostel_rooms CHANGE COLUMN bed_count number_of_beds INT NULL DEFAULT NULL" => "Changed bed_count to number_of_beds in hostel_rooms",
        "ALTER TABLE visitor_logs ADD COLUMN to_meet_user_id INT NULL DEFAULT NULL AFTER school_id" => "Added to_meet_user_id to visitor_logs",
        "ALTER TABLE visitor_logs ADD COLUMN status ENUM('inside', 'left') DEFAULT 'inside' AFTER to_meet_user_id" => "Added status to visitor_logs",
        "ALTER TABLE visitor_logs ADD COLUMN created_by INT NULL DEFAULT NULL AFTER status" => "Added created_by to visitor_logs",
        "ALTER TABLE inventory_items ADD COLUMN category_id INT NULL DEFAULT NULL AFTER school_id" => "Added category_id to inventory_items",
        "ALTER TABLE inventory_items ADD COLUMN supplier_id INT NULL DEFAULT NULL AFTER category_id" => "Added supplier_id to inventory_items",
        "ALTER TABLE leave_requests ADD COLUMN approved_by INT NULL DEFAULT NULL AFTER status" => "Added approved_by to leave_requests",
        "ALTER TABLE leave_types ADD COLUMN code VARCHAR(20) NULL DEFAULT NULL AFTER name" => "Added code to leave_types",
        "ALTER TABLE leave_types CHANGE COLUMN days days_per_year INT NULL DEFAULT NULL" => "Changed days to days_per_year in leave_types",
        "ALTER TABLE leave_types ADD COLUMN description TEXT NULL DEFAULT NULL AFTER is_paid" => "Added description to leave_types",
        "ALTER TABLE leave_types ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description" => "Added status to leave_types"
    ];

    foreach ($commands as $sql => $successMsg) {
        try {
            $pdo->exec($sql);
            echo "<p>{$successMsg}</p>";
        } catch (Exception $e) {
            echo "<p style='color:gray'>Skipped: " . htmlspecialchars($successMsg) . " (already exists)</p>";
        }
    }

    echo "<h1>All missing columns have been successfully evaluated!</h1>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
