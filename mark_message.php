<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

if (!is_user_manager()) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // ✅ Mark message as viewed
    $stmt = $pdo->prepare("UPDATE contact_queries SET viewed = 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // ✅ Redirect to reply page
    header("Location: view_query.php?id=" . $id);
    exit;
}

// If invalid ID, go back
header("Location: user_management.php");
exit;
