<?php
session_start();

if (!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true) {
  header("Location: /php/admin_login.php");
  exit();
}

require __DIR__ . '/connection.php';
/** @var mysqli $conn */

$result = $conn->query("SELECT * FROM reservations ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Reservations</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: sans-serif;
      background: #f4f4f4;
      padding: 40px 20px;
      color: #333;
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    h1 {
      font-size: 24px;
      font-weight: 600;
    }

    .badge {
      background: #d4edda;
      color: #155724;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 13px;
    }

    .actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      text-decoration: none;
      color: #555;
      font-size: 14px;
      border: 1px solid #ccc;
      padding: 6px 14px;
      border-radius: 6px;
      background: #fff;
    }

    .btn:hover { background: #eee; }

    .btn-logout {
      color: #9b1c1c;
      border-color: #f5c6cb;
      background: #fde8e8;
    }

    .btn-logout:hover { background: #fcc; }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }

    thead {
      background: #2c3e50;
      color: #fff;
    }

    th {
      padding: 14px 16px;
      text-align: left;
      font-size: 13px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    td {
      padding: 12px 16px;
      font-size: 14px;
      border-bottom: 1px solid #f0f0f0;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f9f9f9; }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 15px;
    }

    .guests-badge {
      background: #e8f0fe;
      color: #1a56db;
      padding: 2px 10px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 500;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div style="display:flex; align-items:center; gap:12px;">
      <h1>Reservations</h1>
      <?php
      $count = $result ? $result->num_rows : 0;
      echo "<span class='badge'>$count total</span>";
      ?>
    </div>
    <div class="actions">
      <a href="/website.html" class="btn">← Back to site</a>
      <a href="/php/admin_logout.php" class="btn btn-logout">Logout</a>
    </div>
  </div>

  <table>
    <thead>
    <tr>
      <th>#</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Guests</th>
      <th>Date</th>
      <th>Time</th>
      <th>Notes</th>
      <th>Submitted</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><span class="guests-badge"><?= $row['guests'] ?></span></td>
          <td><?= $row['reservation_date'] ?></td>
          <td><?= $row['reservation_time'] ?></td>
          <td><?= htmlspecialchars($row['notes'] ?? '—') ?></td>
          <td><?= $row['created_at'] ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="9" class="no-data">No reservations yet.</td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
