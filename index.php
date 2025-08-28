<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config/database.php"; // DB connection

// Redirect if already logged in
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user_management.php");
        exit;
    } elseif ($_SESSION['role'] === 'orders') {
        header("Location: order_management.php");
        exit;
    }
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['role'] = $admin['role'];

        if ($admin['role'] === 'user') {
            header("Location: user_management.php");
            exit;
        } elseif ($admin['role'] === 'orders') {
            header("Location: order_management.php");
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <!-- Load your website dark theme -->
  <link rel="stylesheet" href="../fastag_website/styles.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      background: #111827; /* Dark background */
      color: #fff;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      position: relative;
    }
    .nav-logo {
      position: absolute;
      top: 20px;
      left: 40px;
    }
    .nav-logo img {
      height: 90px;
      width: 160px
    }
    .login-box {
      background: #1f2937; /* slightly lighter dark box */
      padding: 30px;
      border-radius: 8px;
      width: 350px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }
    .login-box h2 {
      margin-bottom: 20px;
      color: #fff;
    }
    .login-box input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: none;
      border-radius: 6px;
      background: #374151;
      color: #fff;
    }
    .login-box button {
      width: 100%;
      padding: 10px;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }
    .login-box button:hover {
      background: #1d4ed8;
    }
    .error-msg {
      color: #f87171;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <!-- Logo in top-left -->
  <div class="nav-logo">
      <img src="https://www.apnapayment.com/website/img/logo/ApnaPayment200White.png" alt="Logo">
  </div>

  <!-- Login box -->
  <div class="login-box">
    <h2>Admin Login</h2>
    <?php if (!empty($error)): ?>
      <p class="error-msg"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
