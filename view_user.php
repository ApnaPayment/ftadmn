<?php 
require_once __DIR__ . "/includes/auth.php";
require_login();
if (!is_user_manager()) { die("Access denied"); }

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("User not found."); }

// ✅ Fetch user
$st = $pdo->prepare("SELECT id, name, email, phone, is_verified, created_at, updated_at 
                     FROM users WHERE id = :id LIMIT 1");
$st->execute([':id' => $id]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user) { die("User not found."); }

// ✅ Fetch addresses
$addr = $pdo->prepare("SELECT id, house_no, landmark, city, pincode, created_at 
                       FROM addresses WHERE user_id = :id ORDER BY created_at DESC");
$addr->execute([':id' => $id]);
$addresses = $addr->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <title>User Details</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<div class="container no-sidebar">
  <?php include "includes/header.php"; ?>
  <main class="main">
    <a href="user_management.php" 
   style="display:inline-block; margin:10px 0; padding:8px 16px; 
          background:#6c757d; color:#fff; border-radius:5px; 
          text-decoration:none;">⬅ Go Back</a>
    <div class="card">
      <h3>User Info</h3>
      <p><strong>ID:</strong> <?=h($user['id'])?></p>
      <p><strong>Name:</strong> <?=h($user['name'])?></p>
      <p><strong>Email:</strong> <?=h($user['email'])?></p>
      <p><strong>Phone:</strong> <?=h($user['phone'])?></p>
      <p><strong>Verified:</strong> <?=$user['is_verified'] ? 'Yes' : 'No'?></p>
      <p><strong>Created:</strong> <?=h($user['created_at'])?></p>
      <p><strong>Updated:</strong> <?=h($user['updated_at'])?></p>
    </div>

    <div class="card">
      <h3>Addresses</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th><th>House No</th><th>Landmark</th><th>City</th><th>Pincode</th><th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($addresses as $a): ?>
          <tr>
            <td><?=h($a['id'])?></td>
            <td><?=h($a['house_no'])?></td>
            <td><?=h($a['landmark'])?></td>
            <td><?=h($a['city'])?></td>
            <td><?=h($a['pincode'])?></td>
            <td><?=h($a['created_at'])?></td>
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
