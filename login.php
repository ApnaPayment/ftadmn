<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Case-insensitive email match
    $stmt = $pdo->prepare("SELECT admin_id, email, password, role 
                           FROM admins 
                           WHERE LOWER(email) = LOWER(:email) 
                           LIMIT 1");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // ✅ Set all important session variables
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['email'] = $admin['email'];

        // Redirect based on role
        if ($admin['role'] === 'user') {
            header("Location: user_management.php"); exit;
        } elseif ($admin['role'] === 'orders') {
            header("Location: order_management.php"); exit;
        } else {
            header("Location: dashboard.php"); exit;
        }
    } else {
        echo "Invalid email or password.";
    }
} else {
    header("Location: index.php"); exit;
}
?>
