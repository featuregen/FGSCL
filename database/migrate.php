<?php
/**
 * Database Migration Runner
 * Executes SQL migration files and seeds in order
 * 
 * Usage: Access via browser at /FGSL/database/migrate.php
 *        or run: php database/migrate.php
 */

// Bootstrap
require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';

// Detect if running from CLI or browser
$isCli = php_sapi_name() === 'cli';
$output = [];

function out(string $message, bool $isCli = false): void
{
    if ($isCli) {
        echo $message . PHP_EOL;
    } else {
        echo "<pre>{$message}</pre>";
    }
}

// Header
out("╔══════════════════════════════════════════════╗", $isCli);
out("║   EduGen — Database Migration               ║", $isCli);
out("╚══════════════════════════════════════════════╝", $isCli);
out("", $isCli);

$action = $isCli ? ($argv[1] ?? 'migrate') : ($_GET['action'] ?? 'migrate');

try {
    $pdo = Database::pdo();
    out("✓ Database connection successful", $isCli);
    out("  Database: " . env('DB_NAME'), $isCli);
    out("", $isCli);

    if ($action === 'fresh') {
        // Drop all tables and re-run
        out("⚠ FRESH migration — dropping all tables...", $isCli);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            out("  Dropped: {$table}", $isCli);
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        out("", $isCli);
    }

    // ─── Run Migrations ─────────────────────────────────
    out("── Running Migrations ─────────────────────────", $isCli);
    
    $migrationDir = __DIR__ . '/migrations';
    $files = glob($migrationDir . '/*.sql');
    sort($files);
    
    // Check which migrations have already run
    $executed = [];
    try {
        $stmt = $pdo->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // migrations table doesn't exist yet, that's fine
    }
    
    $migrationsRun = 0;
    foreach ($files as $file) {
        $filename = basename($file);
        
        if (in_array($filename, $executed)) {
            out("  ○ Skip: {$filename} (already executed)", $isCli);
            continue;
        }
        
        $sql = file_get_contents($file);
        
        // Remove SQL comments (lines starting with --)
        $lines = explode("\n", $sql);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--')) continue;
            $cleanLines[] = $line;
        }
        $cleanSql = implode("\n", $cleanLines);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $cleanSql)),
            fn($s) => !empty($s)
        );
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Skip duplicate index/table/key errors during re-runs
                if (!in_array($e->getCode(), ['42S01', '42000', '23000', '42S02'])) {
                    throw $e;
                }
                // Log but continue
                if (APP_DEBUG) {
                    out("  ⚠ Skipped (non-critical): " . substr($e->getMessage(), 0, 80), $isCli);
                }
            }
        }
        
        // Record migration
        try {
            $pdo->exec("INSERT INTO migrations (migration) VALUES ('{$filename}')");
        } catch (PDOException $e) {
            // Ignore if migrations table wasn't created yet
        }
        
        out("  ✓ Executed: {$filename}", $isCli);
        $migrationsRun++;
    }
    
    out("  Total: {$migrationsRun} migration(s) executed", $isCli);
    out("", $isCli);

    // ─── Run Seeds ──────────────────────────────────────
    if ($action === 'fresh' || $action === 'seed' || $action === 'migrate') {
        out("── Running Seeds ──────────────────────────────", $isCli);
        
        $seedDir = __DIR__ . '/seeds';
        $seedFiles = glob($seedDir . '/*.sql');
        sort($seedFiles);
        
        foreach ($seedFiles as $file) {
            $filename = basename($file);
            $sql = file_get_contents($file);
            
            // Remove SQL comments
            $lines = explode("\n", $sql);
            $cleanLines = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed) || str_starts_with($trimmed, '--')) continue;
                $cleanLines[] = $line;
            }
            $cleanSql = implode("\n", $cleanLines);
            
            $statements = array_filter(
                array_map('trim', explode(';', $cleanSql)),
                fn($s) => !empty($s)
            );
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement) || str_starts_with($statement, '--')) continue;
                
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') { // Ignore duplicate entry
                        out("  ⚠ Warning in {$filename}: " . $e->getMessage(), $isCli);
                    }
                }
            }
            
            out("  ✓ Seeded: {$filename}", $isCli);
        }
        out("", $isCli);
    }

    // ─── Create Super Admin ─────────────────────────────
    // Check if super admin exists
    $superAdmin = $pdo->query("SELECT id FROM users WHERE user_type = 'super_admin' LIMIT 1")->fetch();
    
    if (!$superAdmin) {
        out("── Creating Super Admin ────────────────────────", $isCli);
        
        $password = password_hash('admin@123', PASSWORD_DEFAULT);
        
        $pdo->exec("INSERT INTO users (username, email, password, full_name, phone, user_type, is_active, email_verified_at, created_at) 
                     VALUES ('superadmin', 'admin@fgsl.com', '{$password}', 'Super Administrator', '9999999999', 'super_admin', 1, NOW(), NOW())");
        
        $userId = $pdo->lastInsertId();
        $roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'super_admin'")->fetchColumn();
        
        if ($roleId) {
            $pdo->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$userId}, {$roleId})");
        }
        
        out("  ✓ Super Admin created:", $isCli);
        out("    Email: admin@fgsl.com", $isCli);
        out("    Password: admin@123", $isCli);
        out("    ⚠ CHANGE THIS PASSWORD IMMEDIATELY!", $isCli);
    } else {
        out("  ○ Super Admin already exists", $isCli);
    }
    
    out("", $isCli);
    out("╔══════════════════════════════════════════════╗", $isCli);
    out("║   ✓ Migration completed successfully!        ║", $isCli);
    out("╚══════════════════════════════════════════════╝", $isCli);

} catch (PDOException $e) {
    out("", $isCli);
    out("✗ ERROR: " . $e->getMessage(), $isCli);
    out("  Code: " . $e->getCode(), $isCli);
    out("  File: " . $e->getFile() . ':' . $e->getLine(), $isCli);
    
    if (str_contains($e->getMessage(), 'Unknown database')) {
        out("", $isCli);
        out("  Please create the database first:", $isCli);
        out("  CREATE DATABASE fgsl_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;", $isCli);
    }
}
