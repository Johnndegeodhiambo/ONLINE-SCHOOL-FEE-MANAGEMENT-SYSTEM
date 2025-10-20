<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
check_login('parent');

$parent_national_id = $_SESSION['user']['id'];

// Fetch children (students linked to this parent)
$query = $pdo->prepare("
    SELECT s.id AS student_id, s.full_name AS student_name, f.total_amount, f.amount_paid, f.amount_due, f.status 
    FROM students s
    LEFT JOIN fees f ON s.id = f.student_id
    WHERE s.full_name = ?
");
$query->execute([$parent_national_id]);
$children = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Children Fees - OSFMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a5acd;
            --secondary: #00bcd4;
            --danger: #e74c3c;
            --success: #2ecc71;
            --text-dark: #222;
            --text-light: #777;
            --bg-light: #f5f7fa;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
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
            font-size: 1.3rem;
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

        /* Main Content */
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
            max-width: 900px;
            margin: 0 auto;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
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
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        tr:hover {
            background: #f3f4ff;
        }

        .status {
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }

        .paid { background: var(--success); color: #fff; }
        .pending { background: #f1c40f; color: #fff; }
        .overdue { background: var(--danger); color: #fff; }

        .btn {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(90deg, #5a4ad4, #03acc1);
        }

        .no-data {
            text-align: center;
            color: var(--text-light);
            margin-top: 2rem;
            font-size: 1rem;
        }
        /* ---------- SIDEBAR ---------- */
.sidebar {
  width: 250px;
  background: linear-gradient(180deg, var(--background1), var(--background2));
  color: #350de6ff;
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
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fas fa-user-friends"></i> Parent</h3>
    <a href="parent_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="children_fees.php" class="active"><i class="fas fa-money-bill"></i> Children Fees</a>
    <a href="parent_payment_history.php"><i class="fas fa-receipt"></i> Payment History</a>
    <a href="parent_notification.php"><i class="fas fa-bell"></i> Notifications</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="card">
        <h2><i class="fas fa-school"></i> Children Fee Details</h2>

        <?php if (count($children) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Total Fee</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($children as $child): ?>
                        <tr>
                            <td><?= htmlspecialchars($child['student_name']) ?></td>
                            <td>KES <?= number_format($child['total_amount'] ?? 0, 2) ?></td>
                            <td>KES <?= number_format($child['amount_paid'] ?? 0, 2) ?></td>
                            <td>KES <?= number_format($child['balance'] ?? 0, 2) ?></td>
                            <td>
                                <?php 
                                    $status = strtolower($child['status'] ?? 'pending');
                                    $class = $status === 'paid' ? 'paid' : ($status === 'overdue' ? 'overdue' : 'pending');
                                ?>
                                <span class="status <?= $class ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>
                                <a href="view_child_fees.php?id=<?= $child['student_id'] ?>" class="btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle"></i> No children or fee records found.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
