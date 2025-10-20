<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// Restrict access to parents only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    header('Location: ../public/login.php');
    exit;
}

$user = $_SESSION['user'];
$parent_id = $user['id'];

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['amount'])) {
    $student_id = (int) $_POST['student_id'];
    $amount = floatval($_POST['amount']);

    // Verify student belongs to this parent
    $verify = $pdo->prepare("SELECT id FROM students WHERE id = ? AND parent_id = ?");
    $verify->execute([$student_id, $parent_id]);

    if ($verify->rowCount() === 1 && $amount > 0) {
        $insert = $pdo->prepare("INSERT INTO payments (student_id, amount_paid, payment_date, status) VALUES (?, ?, NOW(), 'paid')");
        $insert->execute([$student_id, $amount]);
        echo "<script>alert('Payment successful!'); window.location.href='parent_dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Invalid student or amount.');</script>";
    }
}

// Fetch children and calculate fee balances
$query = $pdo->prepare("
    SELECT 
        s.id, 
        s.full_name, 
        s.class,
        COALESCE(SUM(f.total_amount), 0) AS total_fees,
        COALESCE(SUM(p.amount_paid), 0) AS total_paid,
        COALESCE(SUM(f.total_amount), 0) - COALESCE(SUM(p.amount_paid), 0) AS balance
    FROM students s
    LEFT JOIN fees f ON s.id = f.student_id
    LEFT JOIN payments p ON s.id = p.student_id AND p.status = 'paid'
    WHERE s.parent_id = ?
    GROUP BY s.id, s.full_name, s.class
");
$query->execute([$parent_id]);
$children = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Parent Dashboard - OSFMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.15);
            border-left: 4px solid #fff;
        }
        .logout-btn {
            margin-top: auto;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.1);
            border-top: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }
        .logout-btn button {
            background: none;
            border: none;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
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
        .badge {
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            font-size: 0.85rem;
            color: #fff;
        }
        .badge.paid { background: #4CAF50; }
        .badge.pending { background: #FFC107; color: #000; }

        form.payment-form {
            margin-top: 2rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        form.payment-form label {
            font-weight: 600;
        }
        form.payment-form select,
        form.payment-form input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form.payment-form button {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2><i class="fas fa-users"></i> OSFMS</h2>
    <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="children_fees.php"><i class="fas fa-money-bill-wave"></i> Children Fees</a>
    <a href="parents_payment_history.php"><i class="fas fa-receipt"></i> Payment History</a>
    <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <div class="logout-btn">
        <form action="logout.php" method="POST">
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <header>
        <h1><i class="fas fa-user-friends"></i> Welcome, <?= htmlspecialchars($user['username']); ?></h1>
        <div class="user-info">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile" width="45" />
            <span><?= htmlspecialchars($user['username']); ?></span>
        </div>
    </header>

    <section class="cards">
        <div class="card">
            <i class="fas fa-user-graduate"></i>
            <h3>Total Children</h3>
            <p><strong><?= count($children); ?></strong></p>
        </div>

        <div class="card">
            <i class="fas fa-wallet"></i>
            <h3>Total Outstanding Balance</h3>
            <?php $totalBalance = array_sum(array_column($children, 'balance')); ?>
            <p><strong>Ksh <?= number_format($totalBalance, 2); ?></strong></p>
        </div>

        <div class="card">
            <i class="fas fa-calendar-check"></i>
            <h3>Upcoming Payments</h3>
            <p>Next term begins soon. Stay updated!</p>
        </div>
    </section>

    <section class="table-container">
        <h2><i class="fas fa-file-invoice"></i> Children Fee Overview</h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Balance (Ksh)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($children) > 0): ?>
                    <?php foreach ($children as $child): ?>
                        <tr>
                            <td><?= htmlspecialchars($child['full_name']); ?></td>
                            <td><?= htmlspecialchars($child['class']); ?></td>
                            <td><?= number_format($child['balance'], 2); ?></td>
                            <td>
                                <?php
                                    $statusClass = $child['balance'] <= 0 ? 'paid' : 'pending';
                                    $statusText = $child['balance'] <= 0 ? 'Cleared' : 'Pending';
                                ?>
                                <span class="badge <?= $statusClass; ?>"><?= $statusText; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No linked students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Payment Form -->
    <form method="POST" class="payment-form">
        <h2><i class="fas fa-credit-card"></i> Make a Payment</h2>

        <label for="student_id">Select Child:</label>
        <select name="student_id" id="student_id" required>
            <?php foreach ($children as $child): ?>
                <option value="<?= $child['id']; ?>"><?= htmlspecialchars($child['full_name']) ?> (<?= htmlspecialchars($child['class']) ?>)</option>
            <?php endforeach; ?>
        </select>

        <label for="amount">Amount (Ksh):</label>
        <input type="number" name="amount" id="amount" step="0.01" min="1" required />

        <button type="submit"><i class="fas fa-money-bill-wave"></i> Pay Now</button>
    </form>

    <footer>
        <p>&copy; <?= date('Y'); ?> Online School Fee Management System. All rights reserved.</p>
    </footer>
</div>

</body>
</html>
