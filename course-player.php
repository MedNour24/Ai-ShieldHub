<?php
// Simple course player page: shows short content for a course
include_once 'config/Database.php';
include_once 'model/Course.php';
include_once 'model/Purchase.php';

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$purchase = new Purchase($db);

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id <= 0) die('Course ID missing');

$course->id = $course_id;
if (!$course->readOne()) die('Course not found');

// demo user
$user_id = 1;

$has_purchased = false;
if ($course->license_type === 'paid') {
    $has_purchased = $purchase->userHasPurchased($user_id, $course_id);
}

// Access control: free or purchased
if (!($course->license_type === 'free' || $has_purchased)) {
    die('You must purchase this course to access the content.');
}

$content = '';
try {
    $stmt = $db->prepare('SELECT content_html FROM course_contents WHERE course_id = :cid LIMIT 1');
    $stmt->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $content = $row['content_html'];
} catch (Exception $e) {
    $content = '';
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($course->title); ?> — Player</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#0b1020;color:#e6eef8;font-family:Segoe UI,Arial,sans-serif;padding:30px} .player{max-width:900px;margin:0 auto} .player h1{font-size:28px;margin-bottom:8px} .player .meta{color:#9fb3db;margin-bottom:18px}</style>
</head>
<body>
  <div class="player">
    <a href="course-details.php?id=<?php echo $course_id; ?>" class="btn btn-sm btn-outline-light mb-3">← Back</a>
    <h1><?php echo htmlspecialchars($course->title); ?></h1>
    <div class="meta"><?php echo htmlspecialchars($course->description); ?></div>
    <div class="content-card p-4" style="background:rgba(255,255,255,0.03);border-radius:12px;border:1px solid rgba(99,102,241,0.12);">
      <?php if ($content !== ''): ?>
        <?php echo $content; ?>
      <?php else: ?>
        <h3>Course Content</h3>
        <p>This course doesn't have detailed content yet. Here's a short outline:</p>
        <ul>
          <li>Introduction and objectives</li>
          <li>Core concepts and principles</li>
          <li>Hands-on exercise</li>
          <li>Summary and next steps</li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
