<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
check_login('parent');

$parent_national_id = $_SESSION['user']['id'];

// Fetch children of the logged-in parent
$students = $pdo->prepare("SELECT id, full_name FROM students WHERE parent_national_id = ?");
$students->execute([$parent_national_id]);
$children = $students->fetchAll(PDO::FETCH_ASSOC);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $amount = $_POST['amount'] ?? null;

    if ($student_id && $amount > 0) {
        $ref = 'TXN-' . strtoupper(uniqid());
        $stmt = $pdo->prepare("
            INSERT INTO payments (student_id, transaction_ref, amount_paid, status, payment_date)
            VALUES (?, ?, ?, 'Paid', NOW())
        ");
        $stmt->execute([$student_id, $ref, $amount]);

        $_SESSION['success'] = "Payment successful! Transaction Ref: $ref";
        header("Location: parents_payment_history.php");
        exit;
    } else {
        $_SESSION['error'] = "Please select a student and enter a valid amount.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pay Fees - OSFMS Parent</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body {
        background: #f4f6fa;
        font-family: 'Poppins', sans-serif;
        padding: 40px;
    }
    .card {
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
        color: #6a5acd;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    select, input {
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
    }
    button {
        padding: 12px;
        background: linear-gradient(45deg, #6a5acd, #00bcd4);
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.3s;
    }
    button:hover { opacity: 0.85; }
    .msg {
        text-align: center;
        margin-bottom: 10px;
        color: green;
    }
    .error { color: red; }
</style>
</head>
<body>

<div class="card">
    <h2><i class="fas fa-wallet"></i> Pay School Fees</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <p class="msg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <p class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Choose Child:</label>
        <select name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($children as $child): ?>
                <option value="<?= $child['id'] ?>"><?= htmlspecialchars($child['full_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Amount (KES):</label>
        <input type="number" name="amount" min="1" required>

        <button type="submit"><i class="fas fa-check-circle"></i> Pay Now</button>
    </form>
</div>

</body>
</html>
