<?php
// Database connection for containerized environment
$host  = "db"; // Docker container name
$dbname = "unix_project";
$user = "root";
$pass = "rootpass";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initialize Redis cache
require_once __DIR__ . '/cache.php';
$redis_host = "redis"; // Docker container name
$cache = new RedisCache($redis_host);
?>
