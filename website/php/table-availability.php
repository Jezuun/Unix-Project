<?php
header('Content-Type: application/json');

$total_tables = 10;
$reservation_hours = 2;
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
  http_response_code(400);
  echo json_encode([
    'available' => null,
    'message' => 'Choose a date and time to check availability.'
  ]);
  exit();
}

try {
  require __DIR__ . '/connection.php';
  /** @var mysqli $conn */

  $stmt = $conn->prepare(
    "SELECT COUNT(*) AS booked_tables
     FROM reservations
     WHERE reservation_date = ?
       AND reservation_time < ADDTIME(?, ?)
       AND ADDTIME(reservation_time, ?) > ?"
  );

  if (!$stmt) {
    throw new RuntimeException("Availability query failed: " . $conn->error);
  }

  $requested_time = $time . ':00';
  $duration = sprintf('%02d:00:00', $reservation_hours);

  $stmt->bind_param(
    'sssss',
    $date,
    $requested_time,
    $duration,
    $duration,
    $requested_time
  );
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result ? $result->fetch_assoc() : ['booked_tables' => 0];
  $stmt->close();

  $booked_tables = min((int) $row['booked_tables'], $total_tables);
  $available_tables = max(0, $total_tables - $booked_tables);

  echo json_encode([
    'available' => $available_tables,
    'total' => $total_tables,
    'booked' => $booked_tables,
    'fullyBooked' => $available_tables === 0
  ]);
} catch (Exception $e) {
  error_log("Table availability failed: " . $e->getMessage());
  http_response_code(500);
  echo json_encode([
    'available' => null,
    'message' => 'Availability is unavailable right now.'
  ]);
}
?>
