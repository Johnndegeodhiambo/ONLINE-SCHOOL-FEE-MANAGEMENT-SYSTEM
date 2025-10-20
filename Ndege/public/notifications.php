<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    header('Location: ../public/login.php');
    exit;
}

$user = $_SESSION['user'];
$parent_id = $user['id'];

// Mark all as read (optional)
$upd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE parent_id = ? OR parent_id IS NULL");
$upd->execute([$parent_id]);

// Fetch notifications for this parent + broadcast
$stmt = $pdo->prepare("
    SELECT id, title, message, created_at, is_read
    FROM notifications
    WHERE parent_id = :pid OR parent_id IS NULL
    ORDER BY created_at DESC
");
$stmt->execute([':pid' => $parent_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Notifications</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 2rem; background: #f5f5f5; }
      .notif { background: #fff; padding: 1rem; margin-bottom: 1rem; border-left: 4px solid #6a5acd; }
      .notif.unread { border-left-color: #e74c3c; }
      .notif h3 { margin: 0 0 0.5rem; }
      .notif small { color: #666; }
    </style>
</head>
<body>
    <h1>Notifications</h1>

    <?php if (count($notifications) === 0): ?>
        <p>No notifications at this time.</p>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <div class="notif <?= $n['is_read'] ? '' : 'unread'; ?>">
                <h3><?= htmlspecialchars($n['title']); ?></h3>
                <small><?= htmlspecialchars($n['created_at']); ?></small>
                <p><?= nl2br(htmlspecialchars($n['message'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
