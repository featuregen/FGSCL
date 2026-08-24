<?php
/**
 * Database Configuration
 * Returns PDO connection parameters
 */

return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => env('DB_PORT', '3306'),
    'database' => env('DB_NAME', 'fgsl_erp'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', 'root'),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];
