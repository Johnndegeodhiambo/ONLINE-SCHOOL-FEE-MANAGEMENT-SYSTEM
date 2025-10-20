<?php
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Redirect if already logged in
if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
    if ($_SESSION['user']['role'] === 'student') {
        header('Location: student_dashboard.php');
        exit;
    } elseif ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_SESSION['user']['role'] === 'parent') {
        header('Location: parent_dashboard.php');
        exit;   
}}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } elseif ($user['role'] === 'parent') {
            header('Location: parent_dashboard.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        }
        exit;
    } else {
        $err = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Fee Management System - Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: url('../assets/image/jjj.JFIF') no-repeat center center/cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      z-index: 0;
    }

    .login-container {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.9);
      padding: 2.5rem 2rem;
      border-radius: 20px;
      box-shadow: 0px 10px 25px rgba(0,0,0,0.25);
      width: 380px;
      text-align: center;
      animation: fadeIn 1s ease;
      backdrop-filter: blur(8px);
    }

    @keyframes fadeIn {
      from { transform: translateY(-30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .login-container h2 {
      color: #4b4fe2;
      font-weight: 700;
      margin-bottom: 1rem;
      font-size: 1.8rem;
    }

    .login-container p {
      font-size: 0.9rem;
      margin-bottom: 2rem;
      color: #444;
    }

    .login-container p a {
      color: #4b4fe2;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s;
    }

    .login-container p a:hover {
      color: #333;
    }

    .error {
      background: #ff4e4e;
      color: #fff;
      padding: 0.8rem;
      margin-bottom: 1rem;
      border-radius: 10px;
      font-size: 0.9rem;
      text-align: left;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .input-group {
      position: relative;
      margin-bottom: 1.2rem;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #667eea;
    }

    .input-group input {
      width: 85%;
      padding: 0.9rem 0.9rem 0.9rem 2.5rem;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 1rem;
      transition: 0.3s;
      background-color: #fafafa;
    }

    .input-group input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
      outline: none;
      background-color: #fff;
    }

    button {
      width: 100%;
      background: linear-gradient(90deg, #667eea, #764ba2);
      border: none;
      padding: 0.9rem;
      border-radius: 10px;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    }

    .forgot-password {
      margin-top: 1rem;
      text-align: center;
    }

    .forgot-password a {
      font-size: 0.9rem;
      color: #4b4fe2;
      text-decoration: none;
      font-weight: 500;
    }

    .forgot-password a:hover {
      text-decoration: underline;
      color: #333;
    }

    .footer-text {
      margin-top: 1.5rem;
      font-size: 0.8rem;
      color: #666;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <h2><i class="fas fa-school"></i> LOGIN</h2>
    <p>Welcome to the School Fee Management System</p>

    <?php if($err): ?>
      <div class="error">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($err) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" name="username" placeholder="Enter your username" required>
      </div>
      <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>

      <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>

    <p>Don’t have an account? <a href="signup.php"><i class="fas fa-user-plus"></i> Sign up</a></p>

    <!-- 🔹 Forgot password link moved below Sign up -->
    <div class="forgot-password">
      <a href="forgot_password.php"><i class="fas fa-key"></i> Forgot Password?</a>
    </div>

    <div class="footer-text">
      &copy; <?= date('Y'); ?> School Fee Management System. All rights reserved.
    </div>
  </div>

</body>
</html>
