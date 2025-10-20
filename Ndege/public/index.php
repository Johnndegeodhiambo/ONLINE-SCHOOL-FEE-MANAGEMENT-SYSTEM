<?php
session_start();

// Redirect logged-in users directly to their dashboards
if (isset($_SESSION['user'])) {
  $role = $_SESSION['user']['role'] ?? '';
  if ($role === 'admin') header("Location: admin_dashboard.php");
  elseif ($role === 'student') header("Location: student_dashboard.php");
  elseif ($role === 'parent') header("Location: parent_dashboard.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Online School Fee Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: url('../assets/image/jjJJ.JPG') no-repeat center center/cover;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      z-index: 0;
    }

    .container {
      position: relative;
      z-index: 1;
      text-align: center;
      color: #fff;
      max-width: 800px;
      padding: 2rem;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      backdrop-filter: blur(8px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: #ffffff;
    }

    p {
      font-size: 1rem;
      margin-bottom: 2rem;
      color: #f0f0f0;
      line-height: 1.6;
    }

    .buttons {
      display: flex;
      justify-content: center;
      gap: 1.2rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }

    a.btn {
      padding: 0.9rem 2rem;
      text-decoration: none;
      color: #fff;
      font-weight: 600;
      border-radius: 10px;
      transition: 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
    }

    a.btn-login {
      background: linear-gradient(90deg, #667eea, #764ba2);
    }

    a.btn-register {
      background: linear-gradient(90deg, #00b09b, #96c93d);
    }

    a.btn-login:hover, a.btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    /* Learn More Section */
    .about-section {
      margin-top: 2rem;
      text-align: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 15px;
      padding: 1.5rem;
      color: #fff;
      line-height: 1.6;
    }

    .about-section h2 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      color: #ffeb3b;
    }

    .about-section p {
      font-size: 0.95rem;
      color: #f5f5f5;
    }

    footer {
      position: absolute;
      bottom: 10px;
      text-align: center;
      width: 100%;
      color: #ccc;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      h1 { font-size: 1.8rem; }
      .container { width: 90%; padding: 1.5rem; }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1><i class="fas fa-school"></i> Online School Fee Management System</h1>
    <p>Efficiently manage student fees, monitor payments, and ensure financial transparency between parents, students, and administrators.</p>

    <div class="buttons">
      <a href="login.php" class="btn btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
      <a href="signup.php" class="btn btn-register"><i class="fas fa-user-plus"></i> Register</a>
    </div>

    <div class="about-section">
      <h2><i class="fas fa-info-circle"></i> Learn More</h2>
      <p>
        The Online School Fee Management System (OSFMS) simplifies school fee management by enabling secure payments, 
        real-time monitoring, and automated report generation.  
        It ensures that parents, students, and administrators can track transactions anytime, anywhere with ease.
      </p>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y'); ?> Online School Fee Management System. All rights reserved.
  </footer>

</body>
</html>
