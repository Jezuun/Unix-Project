
<?php
$env = file(__DIR__."/.env");
foreach($env as $line) {
  $line = trim($line);
  if ($line === '' || str_starts_with($line, '#')) {
    continue;
  }
  list ($key, $value) = explode('=', $line, 2);
  putenv(sprintf('%s=%s', $key, $value));
}
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$conn = new mysqli($host, $user, $pass, $dbname);

//check if connected
if ($conn->connect_error){
  die ("Connection failed: " . $conn->connect_error);
}
?>;
