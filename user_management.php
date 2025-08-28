<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

// Only user managers allowed
if (!is_user_manager()) {
    header("Location: index.php");
    exit;
}

// ✅ Fetch users
$stmt = $pdo->query("SELECT id, name, email, phone, is_verified, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch contact messages
$stmt2 = $pdo->query("SELECT id, name, email, subject, message, submitted_at, viewed FROM contact_queries ORDER BY submitted_at DESC");
$messages = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html>
<head>
  <title>User Management</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include "includes/header.php"; ?>
<div class="container no-sidebar">
  <main class="main">
      <div class="topbar" style="display:flex; justify-content:space-between; align-items:center;">
          <h2>User Management</h2>
          <a href="admin_dashboard.php" 
             style="padding:8px 16px; background: #121a33; color: white; border-radius: 8px;
  border: 1px solid #808080ff; text-decoration:none; border-radius:5px; font-weight:bold; font-size:14px;">
             📊 Go To Dashboard
          </a>
      </div>
      <div class="card">
        <h3>Users</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
              <th>Verified</th><th>Created</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= h($u['id']) ?></td>
              <td><?= h($u['name']) ?></td>
              <td><?= h($u['email']) ?></td>
              <td><?= h($u['phone']) ?></td>
              <td><?= $u['is_verified'] ? 'Yes' : 'No' ?></td>
              <td><?= h($u['created_at']) ?></td>
              <!-- ✅ Corrected link for user details -->
              <td><a class="btn secondary" href="view_user.php?id=<?= $u['id'] ?>">View All</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card">
        <h3>Contact Messages</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Submitted</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($messages as $m): ?>
            <tr <?= $m['viewed'] ? '' : 'style="background:#2a2f45;"' ?>> <!-- highlight new -->
              <td><?= h($m['id']) ?></td>
              <td><?= h($m['name']) ?></td>
              <td><?= h($m['email']) ?></td>
              <td><?= h($m['subject']) ?></td>
              <td><?= h($m['submitted_at']) ?></td>
              <td><?= $m['viewed'] ? 'Viewed' : 'New' ?></td>
              <!-- ✅ Goes through mark_message.php to update status -->
              <td><a class="btn" href="mark_message.php?id=<?= $m['id'] ?>">Reply / View All</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php include "includes/footer.php"; ?>
    </main>
  </div>
</body>
</html>
