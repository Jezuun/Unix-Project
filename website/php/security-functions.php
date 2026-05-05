<?php
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function safe_echo($string) {
    echo escape($string);
}

function validate_input($data, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'phone':
            return preg_match('/^\(\d{3}\) \d{3}-\d{4}$/', $data) ? $data : false;
        case 'string':
        default:
            return is_string($data) ? trim($data) : false;
    }
}

function sanitize_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
}

// Enhanced validation for reservation data
function validate_reservation_data($data) {
    $errors = [];
    
    // Name validation
    if (empty($data['name']) || strlen($data['name']) < 2 || strlen($data['name']) > 100) {
        $errors['name'] = 'Name must be 2-100 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $data['name'])) {
        $errors['name'] = 'Name contains invalid characters';
    }
    
    // Email validation
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email required';
    }
    
    // Phone validation
    if (empty($data['phone']) || !preg_match('/^\(\d{3}\) \d{3}-\d{4}$/', $data['phone'])) {
        $errors['phone'] = 'Phone format: (123) 456-7890';
    }
    
    // Guests validation
    if (empty($data['guests']) || !filter_var($data['guests'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]])) {
        $errors['guests'] = 'Guests must be 1-20';
    }
    
    // Date validation
    $date = DateTime::createFromFormat('Y-m-d', $data['date']);
    if (!$date || $date < new DateTime('today')) {
        $errors['date'] = 'Valid future date required';
    }
    
    // Time validation
    $time = DateTime::createFromFormat('H:i', $data['time']);
    if (!$time) {
        $errors['time'] = 'Valid time required';
    }
    
    return $errors;
}
?>
