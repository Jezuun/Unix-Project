<?php
require __DIR__ . '/connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = trim($_POST["name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $phone = trim($_POST["phone"] ?? "");
  $guests = trim($_POST["guests"] ?? "");
  $date = $_POST["date"] ?? "";
  $time = $_POST["time"] ?? "";
  $notes = trim($_POST["notes"] ?? "");

  if ($name === "" || $email === "" || $phone === "" || $guests === "" || $date === "" || $time === "") {
    $message = "Please fill all required fields.";
  } elseif (!is_numeric($guests)) {
    $message = "Guests must be a number.";
  } else {

    $stmt = $conn->prepare(
      "INSERT INTO reservations
      (name, email, phone, guests, reservation_date, reservation_time, notes)
      VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
      die("Prepare failed: " . $conn->error);
    }

    $guests = (int) $guests;

    $stmt->bind_param("sssisss", $name, $email, $phone, $guests, $date, $time, $notes);

    if ($stmt->execute()) {
      $message = "Reservation created successfully.";
    } else {
      $message = "Error: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reservation</title>
</head>
<body>
  <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>
