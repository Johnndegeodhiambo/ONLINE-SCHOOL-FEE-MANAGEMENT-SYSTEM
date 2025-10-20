<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

check_login();

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$message = "";

// Fetch user details
$stmt = $connect->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc(); // Use fetch_assoc() for associative array

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $connect->prepare("UPDATE users SET full_name=?, email=?, username=?, password=? WHERE id=?");
        $update->execute([$full_name, $email, $username, $hashed_password, $user_id]);
    } else {
        $update = $connect->prepare("UPDATE users SET full_name=?, email=?, username=? WHERE id=?");
        $update->execute([$full_name, $email, $username, $user_id]);
    }

    $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Profile updated successfully.</div>";

    // Refresh data
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= ucfirst($role) ?> Profile - OSFMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    :root {
        --primary: #6a5acd;
        --secondary: #00bcd4;
        --light-bg: #f5f7fa;
        --card-bg: #fff;
        --text-dark: #333;
        --text-light: #666;
        --success: #2ecc71;
        --danger: #e74c3c;
        --border-radius: 12px;
    }

    * {
        margin: 0; padding: 0; box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    body {
        background: var(--light-bg);
        display: flex;
        min-height: 100vh;
        color: var(--text-dark);
    }

    /* Sidebar */
    .sidebar {
        width: 230px;
        background: linear-gradient(180deg, var(--primary), var(--secondary));
        padding: 2rem 1rem;
        color: #fff;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .sidebar a {
        color: #fff;
        text-decoration: none;
        padding: 0.7rem 1rem;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Main Content */
    .main {
        margin-left: 250px;
        padding: 2rem;
        width: calc(100% - 250px);
    }

    h1 {
        color: var(--primary);
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }

    .profile-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 1.3rem;
    }

    label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.4rem;
    }

    input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.3s;
    }

    input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(106,90,205,0.2);
    }

    button {
        width: 100%;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        color: #fff;
        border: none;
        padding: 0.9rem;
        font-size: 1rem;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(106,90,205,0.3);
    }

    .alert {
        padding: 0.8rem 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
        font-size: 0.9rem;
        text-align: center;
    }

    .alert.success { background: var(--success); color: #fff; }
    .alert.error { background: var(--danger); color: #fff; }

</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><i class="fas fa-user-circle"></i> <?= ucfirst($role) ?></h2>

    <?php if ($role === 'student'): ?>
        <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="view_fees.php"><i class="fas fa-money-bill"></i> My Fees</a>
        <a href="payment_history.php"><i class="fas fa-history"></i> Payment History</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
    <?php elseif ($role === 'parent'): ?>
        <a href="parent_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="children_fees.php"><i class="fas fa-money-bill-wave"></i> Children Fees</a>
        <a href="parents_payment_history.php"><i class="fas fa-history"></i> Payment History</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
    <?php endif; ?>

    <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main -->
<div class="main">
    <h1><i class="fas fa-user-cog"></i> My Profile</h1>

    <div class="profile-card">
        <?= $message ?>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-user-shield"></i> Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> New Password (optional)</label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>

            <button type="submit"><i class="fas fa-save"></i> Update Profile</button>
        </form>
    </div>
</div>

</body>
</html>
