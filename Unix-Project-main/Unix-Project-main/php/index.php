p<?php
require __DIR__ . '/connection.php';
/** @var mysqli $conn */

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name   = trim($_POST["name"]   ?? "");
  $email  = trim($_POST["email"]  ?? "");
  $phone  = trim($_POST["phone"]  ?? "");
  $guests = trim($_POST["guests"] ?? "");
  $date   = $_POST["date"] ?? "";
  $time   = $_POST["time"] ?? "";
  $notes  = trim($_POST["notes"]  ?? "");

  $dateObj = DateTime::createFromFormat('Y-m-d', $date);
  $timeObj = DateTime::createFromFormat('H:i', $time);

  if ($name === "" || $email === "" || $phone === "" || $guests === "" || $date === "" || $time === "") {
    $message = "Please fill all required fields.";
  } elseif (!ctype_digit($guests) || (int)$guests < 1) {
    $message = "Guests must be a positive whole number.";
  } elseif (!$dateObj) {
    $message = "Invalid date format.";
  } elseif (!$timeObj) {
    $message = "Invalid time format.";
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


