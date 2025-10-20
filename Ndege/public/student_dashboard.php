<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: ../public/login.php');
    exit;
}

$user = $_SESSION['user'];

// Fetch student data (e.g., balance, transactions)
$stmt = $pdo->prepare("SELECT amount_due FROM fees WHERE id = ?");
$stmt->execute([$user['id']]);
$studentData = $stmt->fetch(PDO::FETCH_ASSOC);
$balance = $studentData['amount_due'] ?? 0.00;

// ✅ Fixed: Alias amount_paid AS amount to match the HTML
$feeHistory = $pdo->prepare("
    SELECT term, amount_paid AS amount, status 
    FROM fees 
    WHERE student_id = ? 
    ORDER BY term DESC 
    LIMIT 5
");
$feeHistory->execute([$user['id']]);
$feeHistory = $feeHistory->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard - OSFMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
  --primary: #6a5acd;
  --secondary: #00bcd4;
  --background1: #667eea;
  --background2: #764ba2;
  --text-dark: #222;
  --text-light: #777;
}

* {
  margin: 0; padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  background: #f5f6fa;
  display: flex;
  min-height: 100vh;
}

/* ---------- SIDEBAR ---------- */
.sidebar {
  width: 250px;
  background: linear-gradient(180deg, var(--background1), var(--background2));
  color: #fff;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  transition: 0.3s;
  z-index: 10;
}

.sidebar h2 {
  text-align: center;
  padding: 1.5rem 0;
  border-bottom: 1px solid rgba(255,255,255,0.2);
  font-size: 1.4rem;
}

.sidebar a {
  padding: 1rem 1.5rem;
  color: #fff;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: background 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: rgba(255,255,255,0.15);
  border-left: 4px solid #fff;
}

.sidebar a i {
  font-size: 1.2rem;
}

.logout-btn {
  margin-top: auto;
  padding: 1rem 1.5rem;
  background: rgba(255,255,255,0.1);
  text-align: center;
  border-top: 1px solid rgba(255,255,255,0.2);
}

.logout-btn button {
  background: none;
  border: none;
  color: #fff;
  font-size: 1rem;
  cursor: pointer;
}

/* ---------- MAIN CONTENT ---------- */
.main {
  margin-left: 250px;
  flex: 1;
  padding: 2rem;
  transition: margin-left 0.3s ease;
}

header {
  background: #fff;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

header h1 {
  font-size: 1.4rem;
  color: var(--primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 2px solid var(--primary);
}

.cards {
  margin-top: 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.card {
  background: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  transition: 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

.card i {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.card h3 {
  margin-bottom: 0.5rem;
  color: var(--text-dark);
}

.card p {
  color: var(--text-light);
}

.table-container {
  margin-top: 2rem;
  background: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

table th, table td {
  text-align: left;
  padding: 0.8rem;
  border-bottom: 1px solid #eee;
}

table th {
  color: var(--primary);
  font-weight: 600;
}

table td {
  color: #555;
}

.badge {
  padding: 0.3rem 0.6rem;
  border-radius: 5px;
  font-size: 0.85rem;
  color: #fff;
}

.badge.paid { background: #4CAF50; }
.badge.pending { background: #FFC107; color: #000; }
.badge.notpaid { background: #E74C3C; }

footer {
  text-align: center;
  margin-top: 2rem;
  color: #888;
  font-size: 0.9rem;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2><i class="fas fa-graduation-cap"></i> OSFMS</h2>
  <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="view_fees.php"><i class="fas fa-money-check-alt"></i> My Fees</a>
  <a href="payment_history.php"><i class="fas fa-receipt"></i> Payment History</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
  <div class="logout-btn">
    <form action="logout.php" method="POST">
      <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <header>
    <h1><i class="fas fa-user-graduate"></i> Welcome, <?= htmlspecialchars($user['role']); ?></h1>
    <div class="user-info">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
      <span><?= htmlspecialchars($user['username']); ?></span>
    </div>
  </header>

  <section class="cards">
    <div class="card">
      <i class="fas fa-wallet"></i>
      <h3>Current Balance</h3>
      <p><strong>Ksh <?= number_format($balance, 2); ?></strong></p>
    </div>

    <div class="card">
      <i class="fas fa-calendar-alt"></i>
      <h3>Next Payment Due</h3>
      <p>Term 2, 2025 — <strong>15th Nov 2025</strong></p>
    </div>

    <div class="card">
      <i class="fas fa-envelope"></i>
      <h3>New Notifications</h3>
      <p>2 new fee reminders available</p>
    </div>
  </section>

  <section class="table-container">
    <h2><i class="fas fa-file-invoice"></i> Recent Fee Records</h2>
    <table>
      <thead>
        <tr>
          <th>Term</th>
          <th>Amount (Ksh)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($feeHistory as $fee): ?>
          <tr>
            <td><?= htmlspecialchars($fee['term']); ?></td>
            <td><?= number_format($fee['amount'], 2); ?></td>
            <td>
              <?php
              $status = strtolower($fee['status']);
              echo "<span class='badge $status'>" . htmlspecialchars($fee['status']) . "</span>";
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <footer>
    &copy; <?= date('Y'); ?> Online School Fee Management System. All rights reserved.
  </footer>
</div>

</body>
</html>
