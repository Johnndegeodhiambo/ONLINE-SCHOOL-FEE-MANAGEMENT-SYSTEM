<?php
require_once '../includes/auth.php';
check_login('admin');
require_once '../includes/db.php';

// --- Handle search and filter ---
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// --- Handle status update ---
if (isset($_GET['update_status'])) {
    $fee_id = $_GET['update_status'];
    $status = $_GET['status'] ?? 'pending';
    $stmt = $pdo->prepare("UPDATE fees SET status = ? WHERE id = ?");
    $stmt->execute([$status, $fee_id]);
    header("Location: manage_fees.php");
    exit;
}

// --- Handle delete ---
if (isset($_GET['delete'])) {
    $fee_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM fees WHERE id = ?");
    $stmt->execute([$fee_id]);
    header("Location: manage_fees.php");
    exit;
}

// --- Fetch filtered fees ---
try {
    $query = "
        SELECT fees.*, students.full_name AS student_name 
        FROM fees 
        LEFT JOIN students ON fees.student_id = students.id
        WHERE 1
    ";

    $params = [];

    if (!empty($search)) {
        $query .= " AND students.full_name LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($status_filter)) {
        $query .= " AND fees.status = ?";
        $params[] = $status_filter;
    }

    $query .= " ORDER BY fees.due_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching fees: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a5acd;
            --secondary: #00bcd4;
            --success: #2ecc71;
            --danger: #e74c3c;
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

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--background1), var(--background2));
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
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
        .sidebar a i { font-size: 1.2rem; }
        .logout-btn {
            margin-top: auto;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.1);
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .logout-btn button {
            background: none; border: none; color: #fff; font-size: 1rem; cursor: pointer;
        }

        /* MAIN CONTENT */
        .container {
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
        }
        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .btn-add {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: 0.3s;
        }
        .btn-add:hover { background: var(--background2); }

        /* Search & Filter */
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
        }
        .filter-bar input, .filter-bar select {
            padding: 0.6rem 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }
        .filter-bar button {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
        }
        .filter-bar button:hover {
            background: var(--primary);
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
            text-align: left;
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
        .badge-warning { background: #f1c40f; }

        .actions a {
            text-decoration: none;
            margin-right: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            color: #fff;
            font-size: 0.85rem;
        }
        .edit { background: var(--secondary); }
        .delete { background: var(--danger); }

        .no-data {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_students.php"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php" class="active"><i class="fas fa-money-bill"></i> Manage Fees</a>
  <a href="view_payments.php"><i class="fas fa-receipt"></i> Payments</a>
  <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
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
    <h2>Manage Fees</h2>

    <div class="filter-bar">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Search by student name..." value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="overdue" <?= $status_filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Filter</button>
        </form>
        <a href="add_fee.php" class="btn-add"><i class="fas fa-plus"></i> Add Fee</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($fees && count($fees) > 0): ?>
            <?php foreach ($fees as $fee): ?>
                <tr>
                    <td><?= htmlspecialchars($fee['id']) ?></td>
                    <td><?= htmlspecialchars($fee['student_name'] ?? 'N/A') ?></td>
                    <td>KES <?= number_format($fee['amount_paid'], 2) ?></td>
                    <td><?= htmlspecialchars($fee['due_date']) ?></td>
                    <td>
                        <?php if ($fee['status'] === 'paid'): ?>
                            <span class="badge badge-success">Paid</span>
                        <?php elseif ($fee['status'] === 'overdue'): ?>
                            <span class="badge badge-danger">Overdue</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="?update_status=<?= $fee['id'] ?>&status=paid" class="edit">Mark Paid</a>
                        <a href="?update_status=<?= $fee['id'] ?>&status=pending" class="edit">Mark Pending</a>
                        <a href="?delete=<?= $fee['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="no-data">No fee records found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
