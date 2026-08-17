<?php
// ─────────────────────────────────────────
//  db.php  –  Database connection (PDO)
//  Include this file wherever you need DB access:
//    require_once __DIR__ . '/db.php';
// ─────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_NAME', 'u353399544_wiuphp');   // ← change this
define('DB_USER', 'u353399544_wiuphp');     // ← change this
define('DB_PASS', '!69^C=cIa');     // ← change this
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // arrays by default
            PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Don't expose DB details to the browser in production
            error_log('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection error. Please try again later.', 0, $e);
        }
    }

    return $pdo;
}