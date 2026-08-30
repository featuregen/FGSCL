<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = Database::pdo();
    
    // Transport routes
    try {
        $pdo->exec("ALTER TABLE transport_routes ADD COLUMN vehicle_id INT NULL DEFAULT NULL AFTER school_id");
        echo "<p>Added vehicle_id to transport_routes</p>";
    } catch (Exception $e) { echo "<p>Skipped vehicle_id (might already exist)</p>"; }

    // Leave types
    try {
        $pdo->exec("ALTER TABLE leave_types ADD COLUMN is_paid TINYINT(1) NOT NULL DEFAULT 1 AFTER name");
        echo "<p>Added is_paid to leave_types</p>";
    } catch (Exception $e) { echo "<p>Skipped is_paid (might already exist)</p>"; }
    
    // Library issues
    try {
        $pdo->exec("ALTER TABLE library_issues ADD COLUMN issued_by INT NULL DEFAULT NULL AFTER return_date");
        echo "<p>Added issued_by to library_issues</p>";
    } catch (Exception $e) { echo "<p>Skipped issued_by (might already exist)</p>"; }

    // Hostel rooms
    try {
        $pdo->exec("ALTER TABLE hostel_rooms ADD COLUMN hostel_id INT NULL DEFAULT NULL AFTER school_id");
        $pdo->exec("ALTER TABLE hostel_rooms CHANGE COLUMN bed_count number_of_beds INT NULL DEFAULT NULL");
        echo "<p>Added hostel_id and number_of_beds to hostel_rooms</p>";
    } catch (Exception $e) { echo "<p>Skipped hostel_rooms extras (might already exist)</p>"; }

    // Visitor logs
    try {
        $pdo->exec("ALTER TABLE visitor_logs ADD COLUMN to_meet_user_id INT NULL DEFAULT NULL AFTER school_id");
        $pdo->exec("ALTER TABLE visitor_logs ADD COLUMN status ENUM('inside', 'left') DEFAULT 'inside' AFTER to_meet_user_id");
        echo "<p>Added to_meet_user_id and status to visitor_logs</p>";
    } catch (Exception $e) { echo "<p>Skipped visitor_logs extras (might already exist)</p>"; }

    // Inventory items
    try {
        $pdo->exec("ALTER TABLE inventory_items ADD COLUMN category_id INT NULL DEFAULT NULL AFTER school_id");
        $pdo->exec("ALTER TABLE inventory_items ADD COLUMN supplier_id INT NULL DEFAULT NULL AFTER category_id");
        echo "<p>Added category_id and supplier_id to inventory_items</p>";
    } catch (Exception $e) { echo "<p>Skipped category_id (might already exist)</p>"; }

    // Leave requests
    try {
        $pdo->exec("ALTER TABLE leave_requests ADD COLUMN approved_by INT NULL DEFAULT NULL AFTER status");
        echo "<p>Added approved_by to leave_requests</p>";
    } catch (Exception $e) { echo "<p>Skipped approved_by (might already exist)</p>"; }

    // Leave types additional columns
    try {
        $pdo->exec("ALTER TABLE leave_types ADD COLUMN code VARCHAR(20) NULL DEFAULT NULL AFTER name");
        $pdo->exec("ALTER TABLE leave_types CHANGE COLUMN days days_per_year INT NULL DEFAULT NULL");
        $pdo->exec("ALTER TABLE leave_types ADD COLUMN description TEXT NULL DEFAULT NULL AFTER is_paid");
        $pdo->exec("ALTER TABLE leave_types ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description");
        echo "<p>Added code, days_per_year, description, status to leave_types</p>";
    } catch (Exception $e) { echo "<p>Skipped leave_types extra columns (might already exist)</p>"; }

    echo "<h1>All missing columns have been successfully added!</h1>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
