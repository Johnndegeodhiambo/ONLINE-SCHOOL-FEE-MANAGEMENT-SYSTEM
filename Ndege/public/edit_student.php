<?php
require_once '../includes/auth.php';
check_login('admin');
require_once '../includes/db.php';

// Get student ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID.");
}

$student_id = (int) $_GET['id'];

// Fetch current student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_no = trim($_POST['admission_no']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $balance = floatval($_POST['balance']);

    if ($full_name && $admission_no) {
        $update = $pdo->prepare("
            UPDATE students 
            SET admission_no = ?, full_name = ?, email = ?, class = ?, balance = ? 
            WHERE id = ?
        ");
        $update->execute([$admission_no, $full_name, $email, $class, $balance, $student_id]);

        $success = "Student details updated successfully!";
        // Refresh data
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student - Admin Panel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --primary: #4b6ef5;
    --danger: #e74c3c;
    --success: #2ecc71;
    --light-bg: #f8f9ff;
}
body {
    font-family: "Poppins", sans-serif;
    background: var(--light-bg);
    display: flex;
    min-height: 100vh;
}
.sidebar {
  width: 250px;
  background: var(--primary);
  color: #fff;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
}
.sidebar h2 {
    color: #fff;
  text-align: center;
  padding: 1.5rem 0;
  border-bottom: 1px solid rgba(255,255,255,0.2);
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
      background: none;
      border: none;
      color: #fff;
      font-size: 1rem;
      cursor: pointer;
    }
.container {
    flex: 1;
    margin-left: 250px;
    padding: 2rem;
}

form {
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: auto;
}

h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    text-align: center;
}

label {
    font-weight: 600;
    display: block;
    margin-top: 1rem;
}

input {
    width: 100%;
    padding: 0.8rem;
    margin-top: 0.3rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
}

button {
    margin-top: 1.5rem;
    padding: 0.9rem 1.5rem;
    border: none;
    background: var(--primary);
    color: #fff;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
}

button:hover { background: #324bd1; }

.message {
    margin-top: 1rem;
    text-align: center;
    font-weight: 600;
}
.message.success { color: var(--success); }
.message.error { color: var(--danger); }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_students.php" class="active"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php"><i class="fas fa-money-bill"></i> Manage Fees</a>
  <a href="view_payments.php"><i class="fas fa-credit-card"></i> View Payments</a>
  <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
  <div class="logout-btn">
      <a href="../public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <form method="POST">
        <h2><i class="fas fa-user-edit"></i> Edit Student</h2>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label>Admission No</label>
        <input type="text" name="admission_no" value="<?= htmlspecialchars($student['admission_no']) ?>" required>

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>">

        <label>Class</label>
        <input type="text" name="class" value="<?= htmlspecialchars($student['class']) ?>">

        <label>Balance (KES)</label>
        <input type="number" step="0.01" name="balance" value="<?= htmlspecialchars($student['balance']) ?>">

        <button type="submit"><i class="fas fa-save"></i> Update Student</button>
    </form>
</div>

</body>
</html>
