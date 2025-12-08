<?php
// Session configuration
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.cookie_httponly', 1); // Security: prevent JS access to session cookies
ini_set('session.use_strict_mode', 1); // Security: reject uninitialized session IDs

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'solida_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
