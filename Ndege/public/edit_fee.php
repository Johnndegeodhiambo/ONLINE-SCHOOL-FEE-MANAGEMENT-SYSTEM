<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure admin is logged in
check_login();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

// Get fee ID from query
$fee_id = $_GET['id'] ?? null;
if (!$fee_id) {
    header('Location: manage_fees.php');
    exit;
}

// Fetch all students for dropdown
try {
    $students = $pdo->query("SELECT id, full_name, admission_no FROM students ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching students: " . htmlspecialchars($e->getMessage()));
}

// Fetch existing fee record
try {
    $stmt = $pdo->prepare("SELECT * FROM fees WHERE id = ?");
    $stmt->execute([$fee_id]);
    $fee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fee) {
        die("<div style='margin:2rem;color:red;font-family:sans-serif;'>❌ Fee record not found. <a href='manage_fees.php'>Back</a></div>");
    }
} catch (PDOException $e) {
    die("Error fetching fee record: " . htmlspecialchars($e->getMessage()));
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $term = $_POST['term'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if ($student_id && $term && $amount && $due_date) {
        try {
            $stmt = $pdo->prepare("UPDATE fees SET student_id = ?, term = ?, amount = ?, due_date = ? WHERE id = ?");
            $stmt->execute([$student_id, $term, $amount, $due_date, $fee_id]);
            $success = "✅ Fee record updated successfully!";
            // refresh record
            $stmt = $pdo->prepare("SELECT * FROM fees WHERE id = ?");
            $stmt->execute([$fee_id]);
            $fee = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "❌ Error updating fee: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "⚠️ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Fee - Admin | OSFMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{
            --primary:#6a5acd;--secondary:#00bcd4;--danger:#e74c3c;
            --light-bg:#f8f9ff;--text-dark:#333;--text-light:#666;
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family:"Poppins","Segoe UI",sans-serif;}
        body{display:flex;min-height:100vh;background:var(--light-bg);}
        .sidebar{
            width:250px;background:linear-gradient(180deg,#667eea,#764ba2);
            color:#fff;display:flex;flex-direction:column;position:fixed;height:100vh;padding-top:1rem;
        }
        .sidebar h2{text-align:center;padding:1rem 0;border-bottom:1px solid rgba(255,255,255,0.12);}
        .sidebar a{color:#fff;text-decoration:none;padding:12px 18px;display:flex;align-items:center;gap:10px;}
        .sidebar a.active,.sidebar a:hover{background:rgba(255,255,255,0.08);border-left:4px solid #fff;}
        .logout-btn{margin-top:auto;padding:12px 18px;border-top:1px solid rgba(255,255,255,0.12);}
        .logout-btn button{background:none;border:none;color:#fff;cursor:pointer;padding:8px 10px;}

        .main{flex:1;margin-left:250px;padding:2rem;}
        h1{color:var(--primary);margin-bottom:1rem;}
        form{
            background:#fff;padding:2rem;border-radius:12px;max-width:600px;
            box-shadow:0 4px 15px rgba(0,0,0,0.05);
        }
        label{display:block;margin-top:1rem;font-weight:600;color:var(--text-dark);}
        select,input{width:100%;padding:0.7rem;margin-top:0.4rem;border:1px solid #ccc;border-radius:6px;font-size:1rem;}
        button{
            margin-top:1.5rem;background:linear-gradient(90deg,var(--primary),var(--secondary));
            color:#fff;border:none;padding:0.8rem 1.2rem;border-radius:6px;cursor:pointer;font-weight:600;
        }
        button:hover{opacity:0.9;}
        .alert{margin-bottom:1rem;padding:0.8rem 1rem;border-radius:8px;}
        .success{background:#2ecc71;color:#fff;}
        .error{background:var(--danger);color:#fff;}
        .footer{margin-top:2rem;color:var(--text-light);font-size:0.9rem;}
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_students.php"><i class="fa-solid fa-users"></i> Manage Students</a>
    <a href="manage_fees.php" class="active"><i class="fa-solid fa-money-bill"></i> Manage Fees</a>
    <a href="view_payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a>
    <a href="reports.php"><i class="fa-solid fa-chart-pie"></i> Reports</a>
    <a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a>
    <div class="logout-btn">
        <form action="../includes/logout.php" method="post">
            <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</div>

<div class="main">
    <h1><i class="fa-solid fa-edit"></i> Edit Fee Record</h1>

    <?php if (!empty($success)): ?>
        <div class="alert success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Choose Student --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= htmlspecialchars($student['id']) ?>" 
                    <?= $fee['student_id'] == $student['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['admission_no']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="term">Term:</label>
        <select name="term" id="term" required>
            <option value="">-- Select Term --</option>
            <option value="Term 1" <?= $fee['term'] === 'Term 1' ? 'selected' : '' ?>>Term 1</option>
            <option value="Term 2" <?= $fee['term'] === 'Term 2' ? 'selected' : '' ?>>Term 2</option>
            <option value="Term 3" <?= $fee['term'] === 'Term 3' ? 'selected' : '' ?>>Term 3</option>
        </select>

        <label for="amount">Amount (KES):</label>
        <input type="number" name="amount" id="amount" value="<?= htmlspecialchars($fee['amount']) ?>" required min="0">

        <label for="due_date">Due Date:</label>
        <input type="date" name="due_date" id="due_date" value="<?= htmlspecialchars($fee['due_date']) ?>" required>

        <button type="submit"><i class="fa-solid fa-save"></i> Update Fee</button>
    </form>

    <div class="footer">© <?= date('Y') ?> Online School Fee Management System</div>
</div>

</body>
</html>
