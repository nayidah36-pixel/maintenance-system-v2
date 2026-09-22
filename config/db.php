<?php
// db.php - MySQLi connection with SSL for Aiven

$host   = getenv('DB_HOST') ?: '127.0.0.1';
$port   = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'maintenance_db';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    // Enable SSL if the host is not localhost (Aiven requires SSL)
    if ($host !== '127.0.0.1' && $host !== 'localhost') {
        $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    }
    $conn->real_connect($host, $user, $pass, $dbname, (int)$port, NULL,
        ($host !== '127.0.0.1' && $host !== 'localhost') ? MYSQLI_CLIENT_SSL : 0);
    $conn->set_charset("utf8mb4");
    $pdo = $conn;
} catch (Exception $e) {
    error_log("DB Connection Failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>
