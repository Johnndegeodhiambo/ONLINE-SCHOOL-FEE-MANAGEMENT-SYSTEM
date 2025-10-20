<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
check_login('parent');

$parent_id = $_SESSION['user']['id'];

// Fetch payment history for the parent's children
$query = $pdo->prepare("
    SELECT 
        p.transaction_ref,
        p.amount_paid,
        p.payment_date,
        p.status,
        s.full_name AS student_name
    FROM payments p
    INNER JOIN students s ON p.student_id = s.id
    WHERE s.parent_id = ?
    ORDER BY p.payment_date DESC
");
$query->execute([$parent_id]);
$payments = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History - OSFMS Parent</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a5acd;
            --secondary: #00bcd4;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f1c40f;
            --text-dark: #222;
            --text-light: #777;
            --bg-light: #f5f7fa;
        }

        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            width: 240px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding-top: 30px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h3 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        .sidebar a {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .sidebar a i { margin-right: 10px; }

        .sidebar a:hover, .sidebar a.active {
            background: var(--primary);
            color: #fff;
            border-radius: 8px;
            margin: 5px 10px;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--bg-light);
        }

        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 950px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: #fff;
        }

        tr:hover { background: #f3f4ff; }

        .status { padding: 5px 10px; border-radius: 8px; font-weight: 600; text-align: center; }
        .paid { background: var(--success); color: #fff; }
        .pending { background: var(--warning); color: #fff; }
        .failed { background: var(--danger); color: #fff; }

        .back-btn {
            display: inline-block;
            margin-top: 1.5rem;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: #fff;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fas fa-user-friends"></i> Parent</h3>
    <a href="parent_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="parent_pay_fees.php"><i class="fas fa-money-check"></i> Pay Fees</a>
    <a href="parents_payment_history.php" class="active"><i class="fas fa-receipt"></i> Payment History</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="card">
        <h2><i class="fas fa-file-invoice-dollar"></i> Payment History</h2>

        <?php if (count($payments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Transaction Ref</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><?= htmlspecialchars($pay['transaction_ref']) ?></td>
                            <td><?= htmlspecialchars($pay['student_name']) ?></td>
                            <td>KES <?= number_format($pay['amount_paid'], 2) ?></td>
                            <td><span class="status paid"><?= ucfirst($pay['status']) ?></span></td>
                            <td><?= date('d M Y, h:i A', strtotime($pay['payment_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payments yet.</p>
        <?php endif; ?>

        <a href="parent_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

</body>
</html>
