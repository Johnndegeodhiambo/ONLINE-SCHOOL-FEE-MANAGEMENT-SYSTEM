<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OSFMS</title>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #f5f7fa;
  color: #333;
  display: flex;
  flex-direction: column; 
  min-height: 100vh;

}

.site-header {
  background: #5865f2;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.site-header .logo {
  display: flex;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;

}

.site-header .logo i {
  margin-right: 0.5rem;
  font-size: 1.5rem;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;

}

.nav-links a {
  color: #fff;
  text-decoration: none;
  font-weight: 500;
  display: flex;
  align-items: center;
  transition: color 0.3s ease;
  
}

.nav-links a i {
  margin-right: 0.4rem;
}

.nav-links a:hover {
  color: #ffdd57;
}

.logout {
  background: #e74c3c;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  transition: background 0.3s ease;
}

.logout:hover {
  background: #c0392b;
}

.site-content {
  padding: 2rem;
}

  </style>
</head>
<body>
<header class="site-header">
  <div class="logo">
    <i class="fas fa-school"></i>
    <span>ONLINE SCHOOL FEE MANAGEMENT SYSTEM</span>
  </div>
  
  <?php if (isset($_SESSION['user'])): ?>
    <nav class="nav-links">
      <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="admin/students.php"><i class="fas fa-user-graduate"></i> Students</a>
      <a href="admin/fees.php"><i class="fas fa-file-invoice-dollar"></i> Fees</a>
      <a href="admin/payments.php"><i class="fas fa-credit-card"></i> Payments</a>
      <a href="admin/reports.php"><i class="fas fa-chart-line"></i> Reports</a>
      <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  <?php endif; ?>
</header>

<main class="site-content">
