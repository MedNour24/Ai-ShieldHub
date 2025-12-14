<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;
$action = isset($data['action']) ? trim($data['action']) : 'complete'; // 'complete' or 'undo'

if ($user_id <= 0 || $course_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id or course_id']);
    exit;
}

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    // Ensure table exists (best-effort)
    $db->exec("CREATE TABLE IF NOT EXISTS `course_completions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `course_id` INT NOT NULL,
        `completed_at` DATETIME NOT NULL,
        UNIQUE KEY `ux_user_course` (`user_id`, `course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($action === 'complete') {
        // Insert or ignore if exists
        $ins = $db->prepare('INSERT INTO course_completions (user_id, course_id, completed_at) VALUES (:uid, :cid, :at)');
        $ins->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $ins->bindValue(':cid', $course_id, PDO::PARAM_INT);
        $ins->bindValue(':at', date('Y-m-d H:i:s'));
        try {
            $ins->execute();
        } catch (Exception $e) {
            // duplicate key -> already completed; ignore
        }
        echo json_encode(['success' => true, 'status' => 'completed', 'course_id' => $course_id]);
        exit;
    } else {
        // undo
        $del = $db->prepare('DELETE FROM course_completions WHERE user_id = :uid AND course_id = :cid');
        $del->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $del->bindValue(':cid', $course_id, PDO::PARAM_INT);
        $del->execute();
        echo json_encode(['success' => true, 'status' => 'not_completed', 'course_id' => $course_id]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

?>
