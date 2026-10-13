<?php
// Local settings live in config.php (not committed to Git).
// Copy config.example.php to config.php and edit it for your setup.
$configFile = __DIR__ . "/config.php";
if (!file_exists($configFile)) {
    die("Missing config/config.php. Copy config/config.example.php to config/config.php first.");
}
require_once $configFile;

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Database connection failed. Check your settings in config/config.php.");
}
mysqli_set_charset($conn, "utf8mb4");
?>
