<?php
require_once __DIR__ . '/security-functions.php';
require_once __DIR__ . '/csrf-protection.php';
require __DIR__ . '/connection.php';
/** @var mysqli $conn */

$message = "";
$form_data = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        validate_csrf_token($_POST['csrf_token']);
        
        // Collect and validate input
        $form_data = [
            'name' => validate_input(trim($_POST["name"] ?? ""), 'string'),
            'email' => validate_input(trim($_POST["email"] ?? ""), 'email'),
            'phone' => validate_input(trim($_POST["phone"] ?? ""), 'phone'),
            'guests' => validate_input(trim($_POST["guests"] ?? ""), 'int'),
            'date' => validate_input($_POST["date"] ?? "", 'string'),
            'time' => validate_input($_POST["time"] ?? "", 'string'),
            'notes' => validate_input(trim($_POST["notes"] ?? ""), 'string')
        ];

        // Enhanced validation
        $validation_errors = validate_reservation_data($form_data);
        
        if (!empty($validation_errors)) {
            $message = implode(", ", $validation_errors);
        } else {
            // Additional date/time validation
            $dateObj = DateTime::createFromFormat('Y-m-d', $form_data['date']);
            $timeObj = DateTime::createFromFormat('H:i', $form_data['time']);

            if (!$dateObj || $dateObj < new DateTime('today')) {
                $message = "Valid future date required.";
            } elseif (!$timeObj) {
                $message = "Valid time required.";
            } else {
                // All validation passed, proceed with database insertion
                $stmt = $conn->prepare(
                    "INSERT INTO reservations
                    (name, email, phone, guests, reservation_date, reservation_time, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    error_log("Database prepare failed: " . $conn->error);
                    $message = "System error. Please try again later.";
                } else {
                    $guests = (int) $form_data['guests'];
                    $notes = $form_data['notes'] ?? '';

                    $stmt->bind_param("sssisss", 
                        $form_data['name'], 
                        $form_data['email'], 
                        $form_data['phone'], 
                        $guests, 
                        $form_data['date'], 
                        $form_data['time'], 
                        $notes
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: /website.html?success=1');
                        exit();
                    } else {
                        error_log("Database execute failed: " . $stmt->error);
                        $message = "System error. Please try again later.";
                        $stmt->close();
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Security error in reservation: " . $e->getMessage());
        $message = "Security validation failed. Please try again.";
    }
}
?>
