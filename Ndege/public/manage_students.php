<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Students | OSFMS Admin</title>
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
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      display: flex;
      background: var(--light-bg);
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

    .main-content {
      margin-left: 250px;
      padding: 2rem;
      width: 100%;
    }

    h2 {
      color: var(--text-dark);
      margin-bottom: 1rem;
      font-size: 1.8rem;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .add-btn {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: none;
      transition: 0.3s;
    }

    .add-btn:hover {
      background: #5548c8;
    }

    .search-box input {
      padding: 0.6rem 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
      width: 250px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      margin-top: 1rem;
      box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #eee;
      font-size: 0.95rem;
    }

    th {
      background: var(--primary);
      color: #fff;
      font-weight: 600;
    }

    tr:hover {
      background: #f2f2ff;
    }

    .no-data {
      text-align: center;
      padding: 2rem;
      color: var(--text-light);
      font-size: 1.1rem;
    }

    .action-icons a {
      margin: 0 0.5rem;
      text-decoration: none;
    }

    .action-icons i {
      font-size: 1.1rem;
    }

    .action-icons .edit {
      color: var(--secondary);
    }

    .action-icons .delete {
      color: var(--danger);
    }

    .footer {
      text-align: center;
      margin-top: 2rem;
      color: var(--text-light);
      font-size: 0.9rem;
    }
  </style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2><i class="fas fa-graduation-cap"></i> OSFMS</h2>
  <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="manage_students.php"><i class="fas fa-users"></i> Manage Students</a>
  <a href="manage_fees.php"><i class="fas fa-money-check-alt"></i> Manage Fees</a>
  <a href="view_payments.php"><i class="fas fa-receipt"></i> Payments</a>
  <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <div class="logout-btn">
    <form action="../public/logout.php" method="POST">
      <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <h2><i class="fa-solid fa-users"></i> Manage Students</h2>

  <div class="top-bar">
    <a href="add_student.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add New Student</a>
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search students...">
    </div>
  </div>

  <?php
  try {
      $stmt = $pdo->query("
        SELECT
            s.id,
            s.admission_no,
            s.full_name,
            s.class,
            COALESCE(SUM(f.amount_due), 0) - COALESCE(SUM(p.amount_paid), 0) AS balance,
            s.created_at
        FROM students s
        LEFT JOIN fees f ON f.student_id = s.id
        LEFT JOIN payments p ON p.student_id = s.id
        GROUP BY s.id
        ORDER BY s.created_at DESC
      ");
      $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
      echo "<p style='color:red;'>Error fetching students: " . htmlspecialchars($e->getMessage()) . "</p>";
  }

  if (!empty($students)):
  ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Admission No</th>
        <th>Name</th>
        <th>Class</th>
        <th>Balance</th>
        <th>Date Added</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($students as $index => $student): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($student['admission_no']) ?></td>
        <td><?= htmlspecialchars($student['full_name']) ?></td>
        <td><?= htmlspecialchars($student['class']) ?></td>
        <td>KES <?= number_format($student['balance'], 2) ?></td>
        <td><?= date("d M Y", strtotime($student['created_at'])) ?></td>
        <td class="action-icons">
          <a href="edit_student.php?id=<?= $student['id'] ?>" class="edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
          <a href="delete.php?type=student&id=<?= $student['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this student?');"><i class="fa-solid fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="no-data">No students found.</div>
  <?php endif; ?>

  <div class="footer">
    © <?= date('Y') ?> Online School Fee Management System | Admin Panel
  </div>
</div>

<!-- Search Filter Script -->
<script>
  document.getElementById("searchInput").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
      const rowText = row.textContent.toLowerCase();
      row.style.display = rowText.includes(filter) ? "" : "none";
    });
  });
</script>

</body>
</html>
