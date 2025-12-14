<?php
// Admin page to generate AI content for courses

include_once 'config/database.php';
include_once 'model/ContentGenerator.php';

$db = (new Database())->getConnection();
$message = '';
$message_type = '';

// Get all courses
$courses_stmt = $db->prepare('SELECT id, title FROM courses ORDER BY created_at DESC');
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'generate';
    
    if ($course_id <= 0) {
        $message = 'Please select a course';
        $message_type = 'error';
    } else {
        // Fetch course
        $stmt = $db->prepare('SELECT title, description FROM courses WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            $message = 'Course not found';
            $message_type = 'error';
        } else {
            $generator = new ContentGenerator();
            $result = $generator->generateCourseContent($course['title'], $course['description']);
            
            if ($result['success']) {
                // Save to database
                try {
                    $ins = $db->prepare('INSERT INTO course_contents (course_id, content_html, created_at) VALUES (:cid, :html, :at) ON DUPLICATE KEY UPDATE content_html = :html');
                    $ins->bindValue(':cid', $course_id, PDO::PARAM_INT);
                    $ins->bindValue(':html', $result['content'], PDO::PARAM_STR);
                    $ins->bindValue(':at', date('Y-m-d H:i:s'));
                    $ins->execute();
                    
                    $message = 'AI content generated and saved successfully!';
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = 'Generated but failed to save: ' . $e->getMessage();
                    $message_type = 'warning';
                }
            } else {
                $message = 'Error: ' . $result['error'];
                $message_type = 'error';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate AI Course Content</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0b1020; color: #e6eef8; font-family: Segoe UI, Arial; padding: 30px; }
        .gen-card { background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto; }
        .form-label { color: #e6eef8; font-weight: 500; }
        .form-control, .form-select { background: rgba(255,255,255,0.05); border: 1px solid rgba(99,102,241,0.3); color: #e6eef8; }
        .form-control:focus, .form-select:focus { background: rgba(255,255,255,0.08); border-color: #6366f1; color: #e6eef8; }
        .btn-primary { background: #6366f1; border: none; }
        .btn-primary:hover { background: #4f46e5; }
        .alert { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="gen-card">
        <h2 class="mb-4">Generate AI Course Content</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : ($message_type === 'warning' ? 'warning' : 'danger'); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="course_id" class="form-label">Select Course</label>
                <select class="form-select" id="course_id" name="course_id" required>
                    <option value="">-- Choose a course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <p style="font-size: 13px; color: #9fb3db;">
                    This will use OpenAI API to generate a short, educational content overview for the selected course.
                    Make sure you have configured your OpenAI API key in the admin setup page.
                </p>
            </div>

            <button type="submit" class="btn btn-primary w-100">Generate Content</button>
        </form>

        <div class="alert alert-info mt-4" style="font-size: 13px;">
            <strong>Prerequisites:</strong>
            <ul style="margin-bottom: 0;">
                <li>You must have an OpenAI API account</li>
                <li>Set your API key via environment variable <code>OPENAI_API_KEY</code> or update <code>config/AIConfig.php</code></li>
                <li>Visit <a href="admin-stripe-setup.php" style="color: #6366f1;">admin-stripe-setup.php</a> for additional setup guidance</li>
            </ul>
        </div>

        <a href="index.php" class="btn btn-sm btn-outline-light mt-3">← Back to Courses</a>
    </div>
</body>
</html>
