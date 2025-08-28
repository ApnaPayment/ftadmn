<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

// Only order managers allowed
if (!is_order_manager()) {
    header("Location: index.php");
    exit;
}

// ✅ Fetch orders with user info
$stmt = $pdo->query("
    SELECT o.id, o.user_id, u.name AS user_name, u.email AS user_email,
           o.payment_method, o.transaction_id, o.amount, o.status, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html>
<head>
  <title>Order Management</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include "includes/header.php"; ?>

  <div class="container no-sidebar">
  <main class="main">
      <div class="topbar" style="display:flex; justify-content:space-between; align-items:center;">
          <h2>Order Management</h2>
          <a href="admin_dashboard.php" 
             style="padding:8px 16px; background: #121a33; color:white;  border-radius: 8px; border: 1px solid #808080ff;
                    text-decoration:none; border-radius:5px; font-weight:bold; font-size:14px;">
             📊 Go To Dashboard
          </a>
      </div>
      <div class="card">
        <h3>Orders</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>User</th><th>Payment</th><th>Txn</th>
              <th>Amount</th><th>Status</th><th>Created</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><?= h($o['id']) ?></td>
              <td><?= h($o['user_name']) ?> (<?= h($o['user_email']) ?>)</td>
              <td><?= h($o['payment_method']) ?></td>
              <td><?= h($o['transaction_id']) ?></td>
              <td><?= h($o['amount']) ?></td>
              <td><?= h($o['status']) ?></td>
              <td><?= h($o['created_at']) ?></td>
              <td><a class="btn" href="view_order.php?id=<?= $o['id'] ?>">View All</a></td>
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
