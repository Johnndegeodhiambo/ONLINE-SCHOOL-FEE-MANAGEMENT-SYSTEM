<?php
include 'header.php';
require_once __DIR__ . '/../public/db.php';

// Fetch announcements
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

// Handle new announcement
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['post_announcement'])){
    $title = $_POST['title'];
    $message = $_POST['message'];
    $stmt = $pdo->prepare("INSERT INTO announcements (title, message, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$title,$message]);
    header("Location: announcements.php");
    exit;
}
?>
<h1>Announcements</h1>
<form method="POST">
  <input type="text" name="title" placeholder="Title" required>
  <textarea name="message" placeholder="Message" required></textarea>
  <button type="submit" name="post_announcement">Post</button>
</form>

<?php foreach($announcements as $ann): ?>
<div class="announcement-item">
  <h4><?= htmlspecialchars($ann['title']) ?></h4>
  <p><?= htmlspecialchars($ann['message']) ?></p>
  <small><?= $ann['created_at'] ?></small>
</div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
