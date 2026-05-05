<?php
function generate_csrf_token() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(32));
    } else {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        throw new Exception("CSRF token validation failed");
    }
    return true;
}

function get_csrf_input() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_csrf_token();
    }
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}
?>
