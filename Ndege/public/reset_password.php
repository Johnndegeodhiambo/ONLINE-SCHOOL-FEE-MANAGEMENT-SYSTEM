<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$token = $_GET['token'] ?? '';
$error = '';
$message = '';

if (!$token) {
    die("Invalid password reset link.");
}

// ✅ Verify token and expiration
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("This password reset link is invalid or has expired.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // ✅ Update user's password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $reset['user_id']]);
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);

        $message = "Password has been successfully reset. <a href='login.php'>Click here to login</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - School Fee Management System</title>
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
    }
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      z-index: 0;
    }
    .reset-container {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.9);
      padding: 2.5rem 2rem;
      border-radius: 20px;
      box-shadow: 0px 10px 25px rgba(0,0,0,0.25);
      width: 380px;
      text-align: center;
    }
    .reset-container h2 {
      color: #4b4fe2;
      margin-bottom: 1rem;
    }
    .input-group {
      position: relative;
      margin-bottom: 1rem;
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
    .message, .error {
      padding: 0.8rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    .message { background: #4CAF50; color: #fff; }
    .error { background: #ff4e4e; color: #fff; }
  </style>
</head>
<body>
  <div class="reset-container">
    <h2><i class="fas fa-lock"></i> Reset Password</h2>

    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <?php if (!$message): ?>
    <form method="POST">
      <div class="input-group">
        <i class="fas fa-key"></i>
        <input type="password" name="password" placeholder="New Password" required>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
      </div>
      <button type="submit"><i class="fas fa-save"></i> Reset Password</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
