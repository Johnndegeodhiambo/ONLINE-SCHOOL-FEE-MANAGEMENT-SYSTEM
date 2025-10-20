<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// --- Dashboard Data ---
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalPayments = $pdo->query("SELECT IFNULL(SUM(amount_paid), 0) FROM payments")->fetchColumn();
$pendingFees = $pdo->query("SELECT IFNULL(SUM(amount_due - amount_paid), 0) FROM fees WHERE amount_due > amount_paid")->fetchColumn();
// --- Monthly Payments Summary (for Chart.js) ---
$monthlyData = $pdo->query("
    SELECT 
        DATE_FORMAT(payment_date, '%b %Y') AS month,
        SUM(amount_paid) AS total
    FROM payments
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY MIN(payment_date)
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);

$months = array_column($monthlyData, 'month');
$totals = array_column($monthlyData, 'total');

// --- Recent Payments ---
$recentPayments = $pdo->query("
    SELECT 
        p.id, 
        s.full_name AS student_name, 
        p.amount_paid, 
        p.payment_date, 
        p.method 
    FROM payments p
    JOIN students s ON p.student_id = s.id
    ORDER BY p.payment_date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// --- Monthly Payments Data for Chart ---
$chartQuery = $pdo->query("
    SELECT 
        DATE_FORMAT(payment_date, '%b %Y') AS month,
        SUM(amount_paid) AS total
    FROM payments
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY MIN(payment_date)
");
$chartData = $chartQuery->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$totals = [];
foreach ($chartData as $row) {
    $months[] = $row['month'];
    $totals[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - OSFMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
  --primary: #6a5acd;
  --secondary: #2f3232ff;
  --bg1: #0c35eaff;
  --bg2: #4b0cf8ff;
  --danger: #e74c3c;
  --success: #2ecc71;
  --text-dark: #222;
  --text-light: #777;
  --bg-light: #f5f7fa;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  background: var(--bg-light);
  display: flex;
  min-height: 100vh;
}

/* ---------- SIDEBAR ---------- */
.sidebar {
  width: 250px;
  background: linear-gradient(180deg, var(--bg1), var(--bg2));
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  font-size: 1.4rem;
}

.sidebar a {
  padding: 1rem 1.5rem;
  color: #ecebf1;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: background 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: rgba(255, 255, 255, 0.15);
  border-left: 4px solid #fff;
  border-radius: 6px;
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
  padding: 1.8rem;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  text-align: center;
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
  margin-bottom: 0.3rem;
  color: var(--text-dark);
}

.card p {
  color: var(--text-light);
  font-size: 1.2rem;
  font-weight: 600;
}

.table-container {
  margin-top: 2rem;
  background: #fff;
  padding: 1.5rem;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.table-container h2 {
  color: var(--primary);
  margin-bottom: 1rem;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  text-align: left;
  padding: 0.9rem;
  border-bottom: 1px solid #eee;
}

th {
  background: var(--primary);
  color: #fff;
  text-transform: uppercase;
  font-size: 0.85rem;
}

tr:hover {
  background: #f3f4ff;
}

.chart-container {
  background: #fff;
  padding: 1.5rem;
  margin-top: 2rem;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.chart-container h2 {
  color: var(--primary);
  margin-bottom: 1rem;
  text-align: center;
}

footer {
  text-align: center;
  margin-top: 2rem;
  color: #888;
  font-size: 0.9rem;
}

.alert {
  margin: 1rem 0;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
}

.alert.success {
  background: #e6ffed;
  color: #2f7a32;
  border-left: 5px solid #2f7a32;
}

.alert.error {
  background: #ffe6e6;
  color: #d93025;
  border-left: 5px solid #d93025;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2><i class="fas fa-graduation-cap"></i> OSFMS</h2>
  <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="manage_students.php"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php"><i class="fas fa-money-check-alt"></i> Manage Fees</a>
  <a href="view_payments.php"><i class="fas fa-receipt"></i> Payments</a>
  <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <div class="logout-btn">
    <form action="../public/logout.php" method="POST">
      <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <header>
    <h1><i class="fas fa-user-shield"></i> Welcome, <?= htmlspecialchars($user['username']); ?></h1>
    <div class="user-info">
      <img src="https://cdn-icons-png.flaticon.com/512/219/219983.png" alt="Admin">
      <span><?= htmlspecialchars($user['role']); ?></span>
    </div>
  </header>

  <?php if (isset($_SESSION['success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['success']); ?></div>
  <?php unset($_SESSION['success']); ?>
  <?php elseif (isset($_SESSION['error'])): ?>
  <div class="alert error"><?= htmlspecialchars($_SESSION['error']); ?></div>
  <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <section class="cards">
    <div class="card">
      <i class="fas fa-users"></i>
      <h3>Total Students</h3>
      <p><?= $totalStudents; ?></p>
    </div>

    <div class="card">
      <i class="fas fa-wallet"></i>
      <h3>Total Payments</h3>
      <p><?= format_currency($totalPayments); ?></p>
    </div>

    <div class="card">
      <i class="fas fa-exclamation-circle"></i>
      <h3>Pending Fees</h3>
      <p><?= format_currency($pendingFees); ?></p>
    </div>
  </section>

  <section class="table-container">
    <h2><i class="fas fa-history"></i> Recent Payments</h2>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Amount (Ksh)</th>
          <th>Date</th>
          <th>Method</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($recentPayments) > 0): ?>
          <?php foreach ($recentPayments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']); ?></td>
              <td><?= htmlspecialchars($p['student_name']); ?></td>
              <td><?= number_format($p['amount_paid'], 2); ?></td>
              <td><?= date('d M Y', strtotime($p['payment_date'])); ?></td>
              <td><?= htmlspecialchars($p['method']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" style="text-align:center;">No recent payments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

    </section>

  <!-- Monthly Fee Chart and PDF Button -->
  <div class="chart-container">
    <h2><i class="fas fa-chart-line"></i> Monthly Fee Collection</h2>
    <canvas id="paymentsChart"></canvas>
    <form action="generate_report.php" method="POST" style="text-align:center; margin-top:1.5rem;">
      <button type="submit" class="btn-download" name="download">
        <i class="fas fa-file-pdf"></i> Download Report (PDF)
      </button>
    </form>
  </div>

  <footer>
    &copy; <?= date('Y'); ?> Online School Fee Management System. All rights reserved.
  </footer>
</div>


<script>
const ctx = document.getElementById('paymentsChart');
const months = <?= json_encode($months); ?>;
const totals = <?= json_encode($totals); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Total Payments (Ksh)',
            data: totals,
            borderColor: '#6a5acd',
            backgroundColor: 'rgba(106, 90, 205, 0.2)',
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('paymentsChart');
const months = <?= json_encode($months); ?>;
const totals = <?= json_encode($totals); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Total Payments (Ksh)',
            data: totals,
            borderColor: '#6a5acd',
            backgroundColor: 'rgba(106, 90, 205, 0.2)',
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

<style>
.chart-container {
  margin-top: 2rem;
  background: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-download {
  background: #6a5acd;
  color: #fff;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 10px;
  font-size: 1rem;
  cursor: pointer;
  transition: 0.3s;
}

.btn-download:hover {
  background: #5943d3;
}
</style>
</body>
</html>
