<?php
// Database connection for containerized environment
$host   = "db";
$dbname = "unix_project";
$user   = "root";
$pass   = trim(file_get_contents('/run/secrets/db_password'));

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initialize Redis cache
require_once __DIR__ . '/cache.php';
$redis_host = "redis";
$cache = new RedisCache($redis_host);
?>
