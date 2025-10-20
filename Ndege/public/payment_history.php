<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// Restrict access to logged-in users only
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Determine student ID based on role
if ($user['role'] === 'student') {
    $student_id = $user['id'];
} elseif ($user['role'] === 'parent') {
    $student_id = $_GET['student_id'] ?? null;
    if (!$student_id) {
        die("Please select a student to view payment history.");
    }
} else {
    die("Unauthorized access.");
}

// Fetch payment history for the student
$stmt = $pdo->prepare("
    SELECT p.id, amount_paid, p.payment_date, p.payment_method, p.status,
           s.full_name AS student_name
    FROM payments p
    JOIN students s ON p.student_id = s.id
    WHERE p.student_id = ?
    ORDER BY p.payment_date DESC
");
$stmt->execute([$student_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment History - OSFMS</title>
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
  margin: 0;
  padding: 0;
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

/* ---------- MAIN ---------- */
.main {
  margin-left: 250px;
  flex: 1;
  padding: 2rem;
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

table tr:hover {
  background: #f9f9f9;
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

.badge.completed { background: #4CAF50; }
.badge.pending { background: #FFC107; color: #000; }
.badge.failed { background: #E74C3C; }

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
  <h2><i class="fas fa-university"></i> OSFMS</h2>
  <a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="view_fees.php"><i class="fas fa-money-bill-wave"></i> View Fees</a>
  <a href="payment_history.php" class="active"><i class="fas fa-receipt"></i> Payment History</a>
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
    <h1><i class="fas fa-receipt"></i> Payment History for <?= htmlspecialchars($payments[0]['student_name'] ?? 'Student'); ?></h1>
  </header>

  <section class="table-container">
    <table>
      <thead>
        <tr>
          <th>Amount (Ksh)</th>
          <th>Payment Date</th>
          <th>Method of Payment</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($payments) > 0): ?>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?= number_format($payment['amount'], 2); ?></td>
              <td><?= htmlspecialchars($payment['payment_date']); ?></td>
              <td><?= htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
              <td>
                <?php
                $status = strtolower($payment['status']);
                $badgeClass = match($status) {
                  'completed' => 'completed',
                  'pending' => 'pending',
                  'failed' => 'failed',
                  default => 'pending',
                };
                ?>
                <span class="badge <?= $badgeClass; ?>"><?= ucfirst($payment['status']); ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" style="text-align:center;">No payments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

  <footer>
    &copy; <?= date('Y'); ?> Online School Fee Management System. All rights reserved.
  </footer>
</div>

</body>
</html>
