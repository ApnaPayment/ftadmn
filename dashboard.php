<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

if (is_user_manager()) {
    header("Location: user_management.php");
    exit;
} elseif (is_order_manager()) {
    header("Location: order_management.php");
    exit;
} else {
    echo "Invalid role configured.";
}
?>
