<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login();  

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'];

// ---- Accountant Dashboard Stats ----
$totalInvoices = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$totalPayments = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='Paid'")->fetchColumn();
$pendingPayments = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accountant Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body { margin:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f9; color:#333; }
    .sidebar { width:240px; height:100vh; position:fixed; background:#34495e; padding:20px 0; top:0; left:0; color:#ecf0f1; }
    .sidebar h2 { text-align:center; margin-bottom:30px; font-size:22px; }
    .sidebar a { display:block; padding:12px 20px; color:#ecf0f1; text-decoration:none; transition:0.3s; }
    .sidebar a:hover { background:#2c3e50; }
    .main-content { margin-left:240px; padding:20px; }
    header { background:#fff; padding:15px 25px; box-shadow:0 2px 5px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
    header h1 { font-size:20px; margin:0; }
    .card-container { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-top:20px; }
    .card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.05); text-align:center; }
    .card h3 { font-size:18px; margin:10px 0; }
    .card p { font-size:22px; font-weight:bold; color:#2c3e50; }
    .btn { display:inline-block; padding:8px 12px; background:#27ae60; color:#fff; border-radius:5px; text-decoration:none; transition:0.3s; }
    .btn:hover { background:#219150; }
    .section { margin-top:30px; }
    table { width:100%; border-collapse:collapse; margin-top:15px; }
    table th, table td { padding:10px; border:1px solid #ddd; text-align:left; }
    table th { background:#34495e; color:#fff; }
  </style>
</head>
<body>

<div class="sidebar">
  <h2><i class="fas fa-calculator"></i> Accountant</h2>
  <a href="accountant_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="manage_invoices.php"><i class="fas fa-file-invoice"></i> Manage Invoices</a>
  <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
  <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
  <header>
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?> (Accountant) 👋</h1>
    <a href="logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </header>

  <div class="card-container">
    <div class="card">
      <i class="fas fa-file-invoice fa-2x"></i>
      <h3>Total Invoices</h3>
      <p><?= $totalInvoices ?></p>
    </div>
    <div class="card">
      <i class="fas fa-hand-holding-usd fa-2x"></i>
      <h3>Total Payments</h3>
      <p>Ksh <?= number_format($totalPayments ?? 0) ?></p>
    </div>
    <div class="card">
      <i class="fas fa-clock fa-2x"></i>
      <h3>Pending Payments</h3>
      <p>Ksh <?= number_format($pendingPayments ?? 0) ?></p>
    </div>
  </div>

  <div class="section">
    <h2><i class="fas fa-receipt"></i> Recent Payments</h2>
    <table>
      <tr>
        <th>Date</th>
        <th>Invoice #</th>
        <th>Student</th>
        <th>Amount</th>
        <th>Status</th>
      </tr>
      <?php
      $stmt = $pdo->query("SELECT p.date, p.amount, p.status, i.invoice_no, s.name AS student
                           FROM payments p
                           JOIN invoices i ON p.invoice_id = i.id
                           JOIN students s ON i.student_id = s.id
                           ORDER BY p.date DESC LIMIT 5");
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
      <tr>
        <td><?= htmlspecialchars($row['date']) ?></td>
        <td><?= htmlspecialchars($row['invoice_no']) ?></td>
        <td><?= htmlspecialchars($row['student']) ?></td>
        <td>Ksh <?= number_format($row['amount']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

</div>

</body>
</html>
