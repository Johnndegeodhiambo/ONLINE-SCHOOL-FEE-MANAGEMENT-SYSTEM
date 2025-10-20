<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
check_login('admin');

// Fetch students for dropdown
$students = $pdo->query("SELECT id, full_name FROM students ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $amount_due = $_POST['amount_due'] ?? null;
    $description = $_POST['description'] ?? '';

    if (!$student_id || !$amount_due || !is_numeric($amount_due) || $amount_due <= 0) {
        $message = '<p style="color:red;">Please select a student and enter a valid amount.</p>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO fees (student_id, amount_due, description, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$student_id, $amount_due, $description])) {
            $message = '<p style="color:green;">Fee added successfully!</p>';
        } else {
            $message = '<p style="color:red;">Error adding fee. Please try again.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Fees | OSFMS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9ff;
            padding: 2rem;
        }
        form {
            max-width: 450px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }
        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            color: #333;
        }
        select, input[type="number"], textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        button {
            background: #6a5acd;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5548c8;
        }
        .message {
            margin-bottom: 1rem;
            font-weight: 600;
        }
        a.back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #6a5acd;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2><i class="fa-solid fa-money-bill-wave"></i> Add Fee</h2>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="student_id">Select Student</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= htmlspecialchars($student['id']) ?>"><?= htmlspecialchars($student['full_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="amount_due">Fee Amount (KES)</label>
        <input type="number" name="amount_due" id="amount_due" step="0.01" min="0" required />

        <label for="description">Description (optional)</label>
        <textarea name="description" id="description" rows="3"></textarea>

        <button type="submit">Add Fee</button>
    </form>
    <a href="manage_fees.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Manage Fees</a>
</body>
</html>
