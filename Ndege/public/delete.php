<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// ✅ Only allow admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ✅ Check required parameters
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    die("Invalid request");
}

$type = $_GET['type']; // e.g. 'student', 'fee', 'payment'
$id = (int) $_GET['id'];

// ✅ Map type to database table
$allowedTypes = [
    'student' => 'students',
    'fee' => 'fees',
    'payment' => 'payments'
];

if (!array_key_exists($type, $allowedTypes)) {
    die("Invalid deletion type");
}

$table = $allowedTypes[$type];

// ✅ Prepare delete statement
$stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
try {
    $stmt->execute([$id]);
    $_SESSION['success'] = ucfirst($type) . " deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting {$type}: " . $e->getMessage();
}

// ✅ Redirect back
if ($type === 'student') {
    header("Location: manage_students.php");
} elseif ($type === 'fee') {
    header("Location: manage_fees.php");
} elseif ($type === 'payment') {
    header("Location: view_payments.php");
} else {
    header("Location: admin_dashboard.php");
}
exit;
