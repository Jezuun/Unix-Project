<?php
require __DIR__ . '/connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = trim($_POST["name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $guests = trim($_POST["guests"] ?? "");
  $date = $_POST["date"] ?? "";
  $time = $_POST["time"] ?? "";

  if ($name === "" || $email === "" || $guests === "" || $date === "" || $time === "") {
    $message = "Please fill all required fields.";
  } elseif (!is_numeric($guests)) {
    $message = "Guests must be a number.";
  } else {

    $stmt = $conn->prepare(
      "INSERT INTO restaurant_reservations
      (customer_name, customer_email, party_size, reservation_date, reservation_time)
      VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
      die("Prepare failed: " . $conn->error);
    }

    $guests = (int) $guests;

    $stmt->bind_param("sssss", $name, $email, $guests, $date, $time);

    // Debug: Log the values
    error_log("Name: $name, Email: $email, Guests: $guests, Date: $date, Time: $time");

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
