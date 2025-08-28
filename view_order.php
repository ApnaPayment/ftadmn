<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
if (!is_order_manager() && !is_user_manager()) { die("Access denied"); }

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Order not found."); }

// ✅ Fetch order
$st = $pdo->prepare("
  SELECT o.*, u.name AS user_name, u.email AS email, a.house_no, a.landmark, a.city, a.pincode
  FROM orders o
  JOIN users u ON u.id = o.user_id
  LEFT JOIN addresses a ON a.id = o.address_id
  WHERE o.id = :id
  LIMIT 1
");
$st->execute([':id' => $id]);
$order = $st->fetch(PDO::FETCH_ASSOC);
if (!$order) { die("Order not found."); }

// ✅ Fetch items
$it = $pdo->prepare("SELECT product_name, bank, quantity, price FROM order_items WHERE order_id = :id");
$it->execute([':id' => $id]);
$items = $it->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch tracking history
$track = $pdo->prepare("SELECT id, location, updated_at FROM order_tracking WHERE order_id = :oid ORDER BY id ASC");
$track->execute([':oid' => $order['id']]);
$locations = $track->fetchAll();
?>
<!doctype html>
<html>
<head>
  <title>Order Details (Admin)</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <style>
   /* Common style for dropdowns */
.status_dropdown,
.location_dropdown {
    width: 5cm;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #444;
    font-size: 0.95em;
    background-color: transparent; /* dropdown box transparent */
    color: #ffffff; /* selected text white */
    appearance: none; /* cleaner look */
}

/* Make dropdown options white on dark */
.status_dropdown option,
.location_dropdown option {
    background-color: #444444ff; /* dark dropdown list background */
    color: #ffffff; /* white text for options */
}

/* Input box styled like dropdown */
.location_input {
    width: 5cm;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #444;
    font-size: 0.95em;
    background-color: transparent;
    color: #ffffff;
}

/* button style */
.update_btn {
  width: 3cm;
  padding: 3px 6px;
  border-radius: 8px;
  border: 1px solid #808080ff;
  font-size: 0.95em;
  background-color: transparent;
  color: #fff;
  cursor: pointer;
}
.update_btn:hover {
  background-color: #3a3a3aff;
  color: #fff;
}

.remove-btn {
    background: transparent;
    border-radius: 8px;
  border: 1px solid #808080ff;
    color: #fff;
    padding: 3px 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.85em;
}

.remove-btn:hover {
    background: #6d6d6dff;
}
</style>
<div class="container no-sidebar">
  <?php include "includes/header.php"; ?>
  <main class="main">
    <a href="order_management.php"
   style="display:inline-block; margin:10px 0; padding:8px 16px; 
   border:1px solid #808080ff;
   border-radius: 8px;
          background: transparent; color: #fff; border-radius:5px; 
          text-decoration:none;">⬅ Go Back</a>
    <div class="card">
      <h3>Order #<?=h($order['id'])?></h3>
      <p><strong>User:</strong> <?=h($order['user_name'])?> (<?=h($order['email'])?>)</p>
      <p><strong>Payment:</strong> <?=h($order['payment_method'])?></p>
      <p><strong>Transaction ID:</strong> <?=h($order['transaction_id'])?></p>
      <p><strong>Amount:</strong> <?=h($order['amount'])?></p>
      <p><strong>Status:</strong> <?=h($order['status'])?></p>
      <p><strong>Placed at:</strong> <?=h($order['created_at'])?></p>
    </div>

    <div class="card">
      <h3>Shipping Address</h3>
      <p><?=h($order['house_no'])?>, <?=h($order['landmark'])?>, <?=h($order['city'])?> - <?=h($order['pincode'])?></p>
    </div>

    <div class="card">
      <h3>Items</h3>
      <table>
        <thead><tr><th>Bank</th><th>Product</th><th>Qty</th><th>Price</th></tr></thead>
        <tbody>
        <?php foreach ($items as $itx): ?>
          <tr>
            <td><?=h($itx['bank'])?></td>
            <td><?=h($itx['product_name'])?></td>
            <td><?=h($itx['quantity'])?></td>
            <td><?=h($itx['price'])?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Order Status & Tracking</h3>
      <form method="POST" action="update_order.php">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
<label>Status:</label>
<select name="status" class="status_dropdown">
    <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= $s ?></option>
    <?php endforeach; ?>
</select>

<br><br>

<label>Add Location:</label>
<select name="location_preset" class="location_dropdown" onchange="if(this.value){document.getElementById('customLocation').value=this.value;}">
    <option value="">-- Choose preset --</option>
    <option value="Out for Delivery">Out for Delivery</option>
    <option value="Nearby Hub">Nearby Hub</option>
    <option value="Dropping Store">Dropping Store</option>
    <option value="Nearby Storage">Nearby Storage</option>
</select>

<input type="text" id="customLocation" class="location_input" name="location" placeholder="Or enter custom location">
<br><br>
<button type="submit" class="update_btn">~ Update ~</button>

      <h4>Tracking History</h4>
<?php if ($locations): ?>
    <ul style="list-style:none; padding:0;">
      <?php foreach ($locations as $loc): ?>
        <li style="margin-bottom:8px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
              <!-- Left side: location + time -->
              <span>
                <?= htmlspecialchars($loc['location']) ?> 
                <small>(<?= date("d M H:i", strtotime($loc['updated_at'])) ?>)</small>
              </span>

              <!-- Right side: remove button -->
              <form method="POST" action="delete_tracking.php" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= $loc['id'] ?>">
                  <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                  <button type="submit" class="remove-btn">Remove</button>
              </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No tracking updates yet.</p>
<?php endif; ?>

    </div>
    <?php include "includes/footer.php"; ?>
  </main>
</div>
</body>
</html>
