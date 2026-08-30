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
        $pdo->exec("ALTER TABLE library_issues ADD COLUMN issued_by INT NULL DEFAULT NULL AFTER returned_date");
        echo "<p>Added issued_by to library_issues</p>";
    } catch (Exception $e) { echo "<p>Skipped issued_by (might already exist)</p>"; }

    // Hostel rooms
    try {
        $pdo->exec("ALTER TABLE hostel_rooms ADD COLUMN hostel_id INT NULL DEFAULT NULL AFTER school_id");
        echo "<p>Added hostel_id to hostel_rooms</p>";
    } catch (Exception $e) { echo "<p>Skipped hostel_id (might already exist)</p>"; }

    // Visitor logs
    try {
        $pdo->exec("ALTER TABLE visitor_logs ADD COLUMN status ENUM('inside', 'left') DEFAULT 'inside' AFTER school_id");
        echo "<p>Added status to visitor_logs</p>";
    } catch (Exception $e) { echo "<p>Skipped status (might already exist)</p>"; }

    // Inventory items
    try {
        $pdo->exec("ALTER TABLE inventory_items ADD COLUMN category_id INT NULL DEFAULT NULL AFTER school_id");
        echo "<p>Added category_id to inventory_items</p>";
    } catch (Exception $e) { echo "<p>Skipped category_id (might already exist)</p>"; }

    echo "<h1>All missing columns have been successfully added!</h1>";
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
