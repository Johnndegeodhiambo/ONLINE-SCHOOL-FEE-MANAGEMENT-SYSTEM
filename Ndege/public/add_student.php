<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure user is logged in and is admin
check_login();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_no = trim($_POST['admission_no'] ?? '');
    $full_name    = trim($_POST['full_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $class        = trim($_POST['class'] ?? '');
    $balance      = trim($_POST['balance'] ?? '0');

    // Basic validation
    if ($admission_no === '' || $full_name === '' || $email === '') {
        $message = "<div class='alert error'>⚠️ Please fill in admission number, full name and email.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (admission_no, full_name, email, class, balance, created_at)
                                   VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$admission_no, $full_name, $email, $class, $balance]);
            $message = "<div class='alert success'>✅ Student added successfully. <a href='manage_students.php'>Back to list</a></div>";
            $_POST = [];
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                $message = "<div class='alert error'>❌ Admission number already exists. Use a unique admission number.</div>";
            } else {
                $message = "<div class='alert error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Student - Admin | OSFMS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{
            --primary:#6a5acd; --secondary:#00bcd4; --success:#2ecc71; --danger:#e74c3c;
            --light-bg:#f8f9ff; --text-dark:#333; --text-light:#666;
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family:"Poppins", "Segoe UI", sans-serif;}
        body{display:flex;min-height:100vh;background:var(--light-bg);}
        .sidebar{
            width:250px;background:linear-gradient(180deg,#667eea,#764ba2);color:#fff;display:flex;
            flex-direction:column;position:fixed;height:100vh;padding-top:1rem;
        }
        .sidebar h2{ text-align:center;padding:1rem 0;border-bottom:1px solid rgba(255,255,255,0.12); }
        .sidebar a{ color:#fff;text-decoration:none;padding:12px 18px;display:flex;align-items:center;gap:10px;}
        .sidebar a.active, .sidebar a:hover{ background:rgba(255,255,255,0.08); border-left:4px solid #fff;}
        .logout-btn{ margin-top:auto;padding:12px 18px;border-top:1px solid rgba(255,255,255,0.12); }
        .logout-btn button{ background:none;border:none;color:#fff;cursor:pointer;padding:8px 10px; }
        .main{ flex:1;margin-left:250px;padding:2rem; }
        h1{ color:var(--primary); margin-bottom:1rem; }
        form{ max-width:720px;background:#fff;padding:1.6rem;border-radius:12px;box-shadow:0 8px 25px rgba(0,0,0,0.08); }
        .form-group{ margin-bottom:1rem; }
        label{ display:block;font-weight:600;margin-bottom:0.4rem;color:var(--text-dark); }
        input[type="text"], input[type="email"], input[type="number"], select{
            width:100%;padding:0.75rem;border:1px solid #e6e6e6;border-radius:8px;font-size:1rem;
            background: #fff;
            color: var(--text-dark);
        }
        input:focus, select:focus{ 
            outline:none;
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(106,90,205,0.08);
        }
        .row{ display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .actions{ margin-top:1rem; display:flex; gap:1rem; align-items:center; }
        button.btn{ background:linear-gradient(90deg,var(--primary),var(--secondary)); color:#fff;border:none;
            padding:0.85rem 1.2rem;border-radius:8px;cursor:pointer;font-weight:600; }
        .alert{ padding:0.8rem 1rem;border-radius:8px;margin-bottom:1rem; }
        .alert.success{ background:var(--success); color:#fff; }
        .alert.error{ background:var(--danger); color:#fff; }
        .footer{ margin-top:1.5rem;color:var(--text-light); font-size:0.9rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_students.php" class="active"><i class="fa-solid fa-users"></i> Manage Students</a>
    <a href="manage_fees.php"><i class="fa-solid fa-money-bill"></i> Manage Fees</a>
    <a href="view_payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a>
    <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a>
    <a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a>
    <div class="logout-btn">
        <form action="../includes/logout.php" method="post">
            <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</div>

<div class="main">
    <h1><i class="fa-solid fa-user-plus"></i> Add New Student</h1>

    <?= $message ?>

    <form method="post" novalidate>
        <div class="form-group">
            <label for="admission_no">Admission No *</label>
            <input id="admission_no" name="admission_no" type="text" required value="<?= htmlspecialchars($_POST['admission_no'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input id="full_name" name="full_name" type="text" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="row">
            <div class="form-group">
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="class">Class</label>
                <select id="class" name="class" required>
                    <option value="">-- Select Class --</option>
                    <?php
                    $classes = ['Form 1', 'Form 2', 'Form 3', 'Form 4', ]; // customize this list
                    foreach ($classes as $c) {
                        $selected = (($_POST['class'] ?? '') === $c) ? 'selected' : '';
                        echo "<option value=\"$c\" $selected>$c</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Add Student</button>
            <a href="manage_students.php" style="align-self:center;text-decoration:none;color:var(--text-light)">Cancel</a>
        </div>
    </form>

    <div class="footer">© <?= date('Y') ?> Online School Fee Management System</div>
</div>

</body>
</html>
