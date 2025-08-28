<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
if (!is_order_manager()) { die("Access denied"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $id = intval($_POST['id'] ?? 0);
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($id > 0) {
        // ✅ Delete the tracking entry by its ID
        $st = $pdo->prepare("DELETE FROM order_tracking WHERE id = :id");
        $st->execute([':id' => $id]);

        // If nothing was deleted, show error
        if ($st->rowCount() === 0) {
            die("Error: Tracking history not found or already deleted.");
        }
    } else {
        die("Invalid tracking history ID.");
    }

    // ✅ Redirect back to order details page
    header("Location: view_order.php?id=" . $order_id);
    exit;
} else {
    die("Invalid request.");
}
