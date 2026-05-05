<?php
require_once __DIR__ . '/security-config.php';
require_once __DIR__ . '/csrf-protection.php';
require_once __DIR__ . '/security-functions.php';

secure_session_start();

if (!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true) {
  header("Location: /php/admin_login.php");
  exit();
}

// Check session timeout
if (!check_session_timeout()) {
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
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
      background: #000000;
      padding: 40px 20px;
      color: #1d1d1f;
      line-height: 1.47059;
      font-weight: 400;
      letter-spacing: -.022em;
      -webkit-font-smoothing: antialiased;
      position: relative;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
      pointer-events: none;
      z-index: -1;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
      padding: 24px 0;
    }

    h1 {
      font-size: 32px;
      font-weight: 700;
      color: #f5f5f7;
      letter-spacing: -0.022em;
    }

    .badge {
      background: rgba(52, 199, 89, 0.1);
      color: #34c759;
      padding: 6px 16px;
      border-radius: 980px;
      font-size: 14px;
      font-weight: 500;
      border: 1px solid rgba(52, 199, 89, 0.2);
    }

    .actions {
      display: flex;
      gap: 12px;
    }

    .btn {
      text-decoration: none;
      color: #f5f5f7;
      font-size: 14px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 8px 20px;
      border-radius: 980px;
      background: rgba(255, 255, 255, 0.05);
      transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
      font-weight: 400;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }

    .btn:hover { 
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(255, 255, 255, 0.3);
      transform: scale(1.02);
    }

    .btn-logout {
      color: #ff3b30;
      border-color: rgba(255, 59, 48, 0.2);
      background: rgba(255, 59, 48, 0.05);
    }

    .btn-logout:hover { 
      background: rgba(255, 59, 48, 0.1);
      border-color: rgba(255, 59, 48, 0.3);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255, 255, 255, 0.04);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }

    thead {
      background: rgba(255, 255, 255, 0.08);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
      padding: 16px 20px;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #a1a1a6;
    }

    td {
      padding: 16px 20px;
      font-size: 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: #f5f5f7;
      font-weight: 400;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { 
      background: rgba(255, 255, 255, 0.02);
    }

    .no-data {
      text-align: center;
      padding: 60px;
      color: #86868b;
      font-size: 16px;
      font-weight: 400;
    }

    .guests-badge {
      background: rgba(0, 122, 255, 0.1);
      color: #007aff;
      padding: 4px 12px;
      border-radius: 980px;
      font-size: 12px;
      font-weight: 600;
      border: 1px solid rgba(0, 122, 255, 0.2);
      display: inline-block;
    }
    
    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
      }
      
      .actions {
        width: 100%;
        justify-content: flex-end;
      }
      
      table {
        font-size: 12px;
      }
      
      th, td {
        padding: 12px 8px;
      }
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
          <td><?= escape($row['id']) ?></td>
          <td><?= escape($row['name']) ?></td>
          <td><?= escape($row['email']) ?></td>
          <td><?= escape($row['phone']) ?></td>
          <td><span class="guests-badge"><?= escape($row['guests']) ?></span></td>
          <td><?= escape($row['reservation_date']) ?></td>
          <td><?= escape($row['reservation_time']) ?></td>
          <td><?= escape($row['notes'] ?? '—') ?></td>
          <td><?= escape($row['created_at']) ?></td>
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
