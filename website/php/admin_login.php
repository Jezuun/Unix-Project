<?php
session_start();

if (isset($_SESSION["admin"])) {
  header("Location: /php/admin.php");
  exit();
}

require __DIR__ . '/connection.php';
/** @var mysqli $conn */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = trim($_POST["password"] ?? "");

  $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $stmt->bind_result($hashed);
    $stmt->fetch();

    if (password_verify($password, $hashed)) {
      $_SESSION["admin"] = true;
      header("Location: /php/admin.php");
      exit();
    } else {
      $error = "Invalid username or password.";
    }
  } else {
    $error = "Invalid username or password.";
  }

  $stmt->close();
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
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #681111 0%, #000000 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #fff;
      padding: 44px 40px;
      border-radius: 14px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 8px;
      font-size: 24px;
      color: #222;
    }
    .sub {
      text-align: center;
      color: #999;
      font-size: 14px;
      margin-bottom: 28px;
    }
    .error {
      background: #fde8e8;
      color: #9b1c1c;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      text-align: center;
    }
    .form-group { margin-bottom: 18px; }
    label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #555;
      margin-bottom: 6px;
    }
    input {
      width: 100%;
      padding: 12px 14px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 15px;
      background: #fafafa;
      transition: border-color 0.2s;
    }
    input:focus {
      outline: none;
      border-color: #764ba2;
      background: #fff;
    }
    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 6px;
      transition: transform 0.2s;
    }
    button:hover { transform: translateY(-2px); }
  </style>
</head>
<body>
<div class="card">
  <h2>🔒 Admin Login</h2>
  <p class="sub">Restaurant Reservation System</p>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
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
</div>
</body>
</html>
