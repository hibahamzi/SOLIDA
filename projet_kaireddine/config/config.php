<?php
// Database configuration
define('RECAPTCHA_SITE_KEY', '6LdygissAAAAAP6fuP-xwxhdwr9Bz6o2Rezp9-Xw');
define('RECAPTCHA_SECRET_KEY', '6LdygissAAAAAHkOStubN57b0bInDNlCAGysmIIA');
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_web');
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
