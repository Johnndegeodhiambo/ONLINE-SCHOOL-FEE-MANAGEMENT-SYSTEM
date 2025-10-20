<?php
require_once '../includes/auth.php';
check_login('admin');
require_once '../includes/db.php';

// Fetch all payments with student info
try {
    $stmt = $pdo->query("
        SELECT 
            payments.*, 
            CONCAT(students.full_name, ' ', students.full_name) AS student_name,
            students.admission_no
        FROM payments
        LEFT JOIN students ON payments.student_id = students.id
        ORDER BY payments.payment_date DESC
    ");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching payments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Payments - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary: #6a5acd;
            --secondary: #00bcd4;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --background1: #667eea;
            --background2: #764ba2;
            --light-bg: #f8f9ff;
            --text-dark: #333;
            --text-light: #666;
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
  background: linear-gradient(180deg, #5c38c0da, #0c35eaff);
  color: #ffffffff;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  transition: 0.3s;
  z-index: 10;
}

.sidebar h2 {
  color: #fff;
  text-align: center;
  padding: 1.5rem 0;
  border-bottom: 1px solid rgba(69, 9, 232, 0.2);
  font-size: 1.4rem;
}

.sidebar a {
  padding: 1rem 1.5rem;
  color: #ecebf1ff;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: background 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: rgba(81, 6, 211, 0.85);
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

        .container {
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
        }
        h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        th, td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        th {
            background: var(--primary);
            color: #fff;
        }
        tr:hover { background: #f2f2ff; }
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            color: #fff;
            font-size: 0.85rem;
        }
        .badge-success { background: var(--success); }
        .badge-danger { background: var(--danger); }
        .badge-warning { background: var(--warning); }
        .btn-add {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 1rem;
        }
        .btn-add:hover { background: var(--background2); }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_students.php"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php"><i class="fas fa-money-bill"></i> Manage Fees</a>
  <a href="view_payments.php" class="active"><i class="fas fa-credit-card"></i> View Payments</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
  <div class="logout-btn">
    <form method="POST" action="../public/logout.php">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <h2>View Payments</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Admission No</th>
            <th>Amount (KES)</th>
            <th>Payment Date</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Status</th>
        </tr>

        <?php if (count($payments) > 0): ?>
            <?php foreach ($payments as $pay): ?>
                <tr>
                    <td><?= htmlspecialchars($pay['id']) ?></td>
                    <td><?= htmlspecialchars($pay['student_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($pay['admission_no'] ?? 'N/A') ?></td>
                    <td><?= number_format($pay['amount_paid'], 2) ?></td>
                    <td><?= htmlspecialchars($pay['payment_date']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($pay['method'])) ?></td>
                    <td><?= htmlspecialchars($pay['reference'] ?? '—') ?></td>
                    <td>
                        <?php if ($pay['status'] === 'paid'): ?>
                            <span class="badge badge-success">Paid</span>
                        <?php elseif ($pay['status'] === 'pending'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><?= ucfirst($pay['status']) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" class="no-data">No payment records found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
