<?php
require_once '../includes/auth.php';
check_login('admin');
require_once '../includes/db.php';

try {
    // Total students
    $students_total = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // Fees summary
    $fees_total = $pdo->query("SELECT SUM(amount_paid) FROM fees")->fetchColumn() ?: 0;
    $payments_total = $pdo->query("SELECT SUM(amount_paid) FROM payments WHERE status='paid'")->fetchColumn() ?: 0;
    $pending_fees = $fees_total - $payments_total;

    // Fee status breakdown
    $statusData = $pdo->query("
        SELECT status, COUNT(*) AS count 
        FROM fees 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $paidCount = $statusData['paid'] ?? 0;
    $pendingCount = $statusData['pending'] ?? 0;
    $overdueCount = $statusData['overdue'] ?? 0;

    // Monthly payments (for chart)
    $monthly = $pdo->query("
        SELECT DATE_FORMAT(payment_date, '%b %Y') AS month, SUM(amount_paid) AS total
        FROM payments
        WHERE status='paid'
        GROUP BY YEAR(payment_date), MONTH(payment_date)
        ORDER BY payment_date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ✅ FIXED: Recent payments
    $stmt = $pdo->query("
        SELECT 
            p.id, 
            s.full_name AS student_name, 
            p.amount_paid, 
            p.payment_date, 
            p.method, 
            p.status
        FROM payments p
        LEFT JOIN students s ON p.student_id = s.id
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<a href="download_report.php" class="btn btn-primary">
  <i class="fa fa-download"></i> Download Report (PDF)
</a>

<style>
:root {
    --primary: #6a5acd;
    --secondary: #00bcd4;
    --success: #2ecc71;
    --danger: #e74c3c;
    --warning: #f1c40f;
    --light-bg: #f8f9ff;
}
* {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: "Poppins", "Segoe UI", sans-serif;
}
body {
    background: var(--light-bg);
    display: flex;
    min-height: 100vh;
}
/* ---------- SIDEBAR ---------- */
.sidebar {
  width: 250px;
  background: #2a46e2;
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
.container {
    flex: 1;
    padding: 2rem;
    margin-left: 250px;
}
h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
}
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.card {
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    text-align: center;
}
.card h3 { margin-bottom: 0.5rem; }
.card p { font-size: 1.6rem; font-weight: bold; }
.card.total-students { border-top: 5px solid var(--secondary); }
.card.total-fees { border-top: 5px solid var(--primary); }
.card.total-payments { border-top: 5px solid var(--success); }
.card.pending-fees { border-top: 5px solid var(--danger); }
.chart-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}
.chart-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 1rem;
}

.export-buttons {
    margin-bottom: 2rem;
    text-align: right; /* aligns the button to the right */
}

.export-buttons .btn {
    background-color: #6a5acd; /* Primary color */
    color: #fff;
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.3s ease;
}

.export-buttons .btn:hover {
    background-color: #5941c3; /* Darker on hover */
    text-decoration: none;
    color: #fff;
}

.export-buttons .btn i {
    font-size: 1rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
th, td { padding: 1rem; border-bottom: 1px solid #eee; text-align: left; }
th { background: var(--primary); color: #fff; }
.badge {
    padding: 0.4rem 0.8rem; border-radius: 20px; color: #fff; font-size: 0.85rem;
}
.badge-success { background: var(--success); }
.badge-warning { background: var(--warning); }
.badge-danger { background: var(--danger); }

.view-all {
    text-align: right;
    margin-top: 1rem;
}
.view-all a {
    background: var(--secondary);
    color: #fff;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
}
.view-all a:hover {
    background: #029bb0;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_students.php"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php"><i class="fas fa-money-bill"></i> Manage Fees</a>
  <a href="view_payments.php"><i class="fas fa-credit-card"></i> View Payments</a>
  <a href="reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
  <div class="logout-btn">
    <form method="POST" action="../includes/logout.php">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <h2>Reports & Analytics</h2>

    <div class="summary-cards">
        <div class="card total-students"><h3>Total Students</h3><p><?= $students_total ?></p></div>
        <div class="card total-fees"><h3>Total Fees</h3><p><?= number_format($fees_total, 2) ?></p></div>
        <div class="card total-payments"><h3>Total Payments</h3><p><?= number_format($payments_total, 2) ?></p></div>
        <div class="card pending-fees"><h3>Pending Fees</h3><p><?= number_format($pending_fees, 2) ?></p></div>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <h3>Fee Status Overview</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Monthly Payment Trends</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <div class="export-buttons">
        <a href="download_report.php" class="btn btn-primary">
  <i class="fa fa-download"></i> Download Report (PDF)
</a>
    </div>

    <h2>Recent Payments</h2>
    <table id="reportTable">
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Amount Paid (KES)</th>
            <th>Date</th>
            <th>Method</th>
            <th>Status</th>
        </tr>
        <?php if (!empty($recent_payments)): ?>
            <?php foreach ($recent_payments as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['student_name']) ?></td>
                    <td><?= number_format($p['amount_paid'], 2) ?></td>
                    <td><?= htmlspecialchars($p['payment_date']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($p['method'])) ?></td>
                    <td>
                        <span class="badge <?= $p['status']=='paid'?'badge-success':($p['status']=='pending'?'badge-warning':'badge-danger') ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No payments found.</td></tr>
        <?php endif; ?>
    </table>

    <div class="view-all">
        <a href="view_payments.php"><i class="fas fa-arrow-right"></i> View All Payments</a>
    </div>
</div>

<script>
// PIE CHART
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Paid', 'Pending', 'Overdue'],
        datasets: [{
            data: [<?= $paidCount ?>, <?= $pendingCount ?>, <?= $overdueCount ?>],
            backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
        }]
    }
});

// BAR CHART
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($monthly, 'month')) ?>,
        datasets: [{
            label: 'Payments (KES)',
            data: <?= json_encode(array_column($monthly, 'total')) ?>,
            backgroundColor: '#6a5acd'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});

// EXPORT CSV
function exportTableToCSV() {
    let csv = [];
    const rows = document.querySelectorAll("#reportTable tr");
    for (let row of rows) {
        let cols = row.querySelectorAll("td, th");
        let data = Array.from(cols).map(col => `"${col.innerText}"`).join(",");
        csv.push(data);
    }
    const csvBlob = new Blob([csv.join("\n")], { type: "text/csv" });
    const url = URL.createObjectURL(csvBlob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "reports.csv";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
</body>
</html>
