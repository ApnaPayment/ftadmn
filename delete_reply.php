<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
if (!is_user_manager()) { die("Access denied"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $query_id = intval($_POST['query_id'] ?? 0);

    if ($id && $query_id) {
        $stmt = $pdo->prepare("DELETE FROM contact_replies WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
    }

    // Redirect back to message details page
    header("Location: reply_message.php?id=" . $query_id);
    exit;
}
