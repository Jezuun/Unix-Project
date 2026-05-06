<?php
// Database connection that works in Docker and during local PHP development.
$project_root = dirname(__DIR__, 2);
$docker_secret = '/run/secrets/db_password';
$local_secret = $project_root . '/secrets/db_password.txt';
$running_in_docker = file_exists($docker_secret);

$host = getenv('DB_HOST') ?: ($running_in_docker ? 'db' : '127.0.0.1');
$dbname = getenv('DB_NAME') ?: 'unix_project';
$user = getenv('DB_USER') ?: 'root';

if (getenv('DB_PASSWORD') !== false) {
  $pass = getenv('DB_PASSWORD');
} elseif (file_exists($docker_secret)) {
  $pass = trim(file_get_contents($docker_secret));
} elseif (file_exists($local_secret)) {
  $pass = trim(file_get_contents($local_secret));
} else {
  $pass = '';
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  throw new RuntimeException("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Initialize Redis cache when the PHP Redis extension is available.
require_once __DIR__ . '/cache.php';
$redis_host = getenv('REDIS_HOST') ?: ($running_in_docker ? 'redis' : '127.0.0.1');
$cache = new RedisCache($redis_host);
?>
