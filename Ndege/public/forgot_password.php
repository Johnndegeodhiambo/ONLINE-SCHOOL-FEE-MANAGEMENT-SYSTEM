<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if (empty($identifier)) {
        $error = "Please enter your username or email.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // ✅ Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // ✅ Save token in password_resets table
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires_at]);

            // ✅ Link for resetting password
            $reset_link = "reset_password.php?token=" . urlencode($token);

            $message = "Password reset link generated successfully. <br>
                        <a href='$reset_link'>Click here to reset your password</a>";
        } else {
            $error = "No account found with that username or email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - School Fee Management System</title>
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
    .forgot-container {
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
    .forgot-container h2 {
      color: #4b4fe2;
      font-weight: 700;
      margin-bottom: 1rem;
      font-size: 1.8rem;
    }
    .forgot-container p {
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
      color: #444;
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
    .message, .error {
      padding: 0.8rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    .message { background: #4CAF50; color: #fff; }
    .error { background: #ff4e4e; color: #fff; }
    .back-link {
      margin-top: 1rem;
      display: block;
      font-size: 0.9rem;
      color: #4b4fe2;
      text-decoration: none;
      font-weight: 500;
    }
    .back-link:hover {
      text-decoration: underline;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="forgot-container">
    <h2><i class="fas fa-key"></i> Forgot Password</h2>
    <p>Enter your username or registered email to reset your password.</p>

    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="identifier" placeholder="Username or Email" required>
      </div>
      <button type="submit"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
    </form>

    <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
  </div>
</body>
</html>
