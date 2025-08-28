<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
if (!is_order_manager()) { die("Access denied"); }

if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    header("Location: order_management.php"); 
    exit; 
}
if (!csrf_check($_POST['csrf'] ?? '')) { 
    die("Invalid CSRF token."); 
}

$id = intval($_POST['order_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$locationPreset = trim($_POST['location_preset'] ?? '');
$locationCustom = trim($_POST['location'] ?? '');

// ✅ Allowed statuses
$allowed = ['Pending','Processing','Shipped','Delivered','Cancelled'];
if (!$id || !in_array($status, $allowed, true)) { 
    die("Bad request"); 
}

// ✅ Update order status
$st = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
$st->execute([':status' => $status, ':id' => $id]);

// ✅ Determine final location: prefer custom input if not empty
$finalLocation = $locationCustom !== '' ? $locationCustom : $locationPreset;

// ✅ Insert tracking if location provided
if ($finalLocation !== '') {
    $ins = $pdo->prepare("INSERT INTO order_tracking (order_id, location) VALUES (:oid, :loc)");
    $ins->execute([':oid' => $id, ':loc' => $finalLocation]);
}

// ✅ Redirect back to order view
header("Location: view_order.php?id=" . $id);
exit;
