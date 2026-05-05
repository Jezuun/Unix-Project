<?php
$env = __DIR__ . "/.env";

if (!file_exists($env)) {
  die("No .env file found");
}

foreach (file($env) as $line) {
  $line = trim($line);

  if ($line === '' || str_starts_with($line, '#')) continue;

  [$key, $value] = explode('=', $line, 2);
  $key   = trim($key);
  $value = trim(explode('#', $value, 2)[0]);
  putenv("$key=$value");
}

$host   = getenv('DB_HOST')     ?: "localhost";
$dbname = getenv('DB_NAME')     ?: "restaurant_reservations";
$user   = getenv('DB_USER')     ?: "root";
$pass   = getenv('DB_PASSWORD') ?: "rootpass";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
