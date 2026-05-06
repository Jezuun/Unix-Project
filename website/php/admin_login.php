<?php
require_once __DIR__ . '/security-config.php';
require_once __DIR__ . '/csrf-protection.php';
require_once __DIR__ . '/rate-limiter.php';
require_once __DIR__ . '/security-functions.php';

secure_session_start();

if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true) {
    if (check_session_timeout()) {
        header("Location: /php/admin.php");
        exit();
    } else {
        $error = "Session expired. Please login again.";
    }
}

require __DIR__ . '/connection.php';
/** @var mysqli $conn */

$error = "";

// Initialize rate limiter
$cache = new RedisCache("redis");
$rateLimiter = new RateLimiter($cache);
$key = 'login:' . $_SERVER['REMOTE_ADDR'];

if ($rateLimiter->tooManyAttempts($key)) {
    $minutes = ceil($rateLimiter->availableIn($key) / 60);
    $error = "Too many login attempts. Try again in {$minutes} minutes.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        validate_csrf_token($_POST['csrf_token']);

        $username = validate_input(trim($_POST["username"] ?? ""), 'string');
        $password = validate_input(trim($_POST["password"] ?? ""), 'string');

        if ($username === false || $password === false) {
            $error = "Invalid input provided.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($hashed);
                $stmt->fetch();

                if (password_verify($password, $hashed)) {
                    $rateLimiter->clear($key);
                    $_SESSION["admin"] = true;
                    $_SESSION["login_time"] = time();
                    $_SESSION["csrf_token"] = generate_csrf_token();
                    header("Location: /php/admin.php");
                    exit();
                } else {
                    $attempts = $rateLimiter->hit($key);
                    $remaining = $rateLimiter->getRemainingAttempts($key);
                    $error = "Invalid credentials. {$remaining} attempts remaining.";
                }
            } else {
                $attempts = $rateLimiter->hit($key);
                $remaining = $rateLimiter->getRemainingAttempts($key);
                $error = "Invalid credentials. {$remaining} attempts remaining.";
            }

            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Security error: " . $e->getMessage());
        $error = "Security validation failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
      background: #000000;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #1d1d1f;
      line-height: 1.47059;
      font-weight: 400;
      letter-spacing: -.022em;
      -webkit-font-smoothing: antialiased;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
      pointer-events: none;
    }

    .card {
      background: rgba(255, 255, 255, 0.04);
      padding: 48px 40px;
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      width: 100%;
      max-width: 420px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      position: relative;
      z-index: 1;
    }

    h2 {
      text-align: center;
      margin-bottom: 8px;
      font-size: 28px;
      color: #f5f5f7;
      font-weight: 700;
      letter-spacing: -0.022em;
    }

    .sub {
      text-align: center;
      color: #a1a1a6;
      font-size: 14px;
      margin-bottom: 32px;
      font-weight: 400;
    }

    .error {
      background: rgba(255, 59, 48, 0.1);
      color: #ff3b30;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 14px;
      margin-bottom: 24px;
      text-align: center;
      border: 1px solid rgba(255, 59, 48, 0.2);
      font-weight: 400;
    }

    .form-group { margin-bottom: 20px; }

    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #f5f5f7;
      margin-bottom: 8px;
      letter-spacing: -0.022em;
    }

    input {
      width: 100%;
      padding: 16px 14px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      font-size: 17px;
      background: rgba(255, 255, 255, 0.05);
      transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
      color: #f5f5f7;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
    }

    input:focus {
      outline: none;
      border-color: #0071e3;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1);
    }

    input::placeholder {
      color: #86868b;
    }

    button {
      width: 100%;
      padding: 16px;
      background: #0071e3;
      color: #ffffff;
      border: none;
      border-radius: 980px;
      font-size: 17px;
      font-weight: 400;
      cursor: pointer;
      margin-top: 12px;
      transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
      box-shadow: 0 4px 14px 0 rgba(0, 26, 255, 0.2);
    }

    button:hover {
      background: #0077ed;
      transform: scale(1.02);
      box-shadow: 0 6px 20px 0 rgba(0, 26, 255, 0.3);
    }

    button:active {
      transform: scale(0.98);
    }

    .back-link {
      text-align: center;
      margin-top: 24px;
    }

    .back-link a {
      color: #86868b;
      font-size: 14px;
      text-decoration: none;
      transition: color 0.3s;
    }

    .back-link a:hover {
      color: #f5f5f7;
    }
  </style>
</head>
<body>
<div class="card">
  <h2>Admin Login</h2>
  <p class="sub">Restaurant Reservation System</p>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?php echo get_csrf_input(); ?>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter username" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>
    </div>
    <button type="submit">Login</button>
  </form>
  <div class="back-link">
    <a href="/website.html">← Back to site</a>
  </div>
</div>
</body>
</html>
