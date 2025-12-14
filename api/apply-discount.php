<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$allowed_codes = [ 'cyber', 'coursss' ];
$discount_percent = 20.0; // percent

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;
$code = isset($data['code']) ? trim(mb_strtolower($data['code'])) : '';

if ($course_id <= 0 || $code === '') {
    echo json_encode(['success' => false, 'message' => 'Missing course_id or code']);
    exit;
}

if (!in_array($code, $allowed_codes, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid discount code']);
    exit;
}

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    // Ensure table exists (best-effort)
    $db->exec("CREATE TABLE IF NOT EXISTS `course_discounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `code` VARCHAR(64) NOT NULL,
        `percent` DECIMAL(5,2) NOT NULL,
        `applied_at` DATETIME NOT NULL,
        UNIQUE KEY `unique_course` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Check if an active discount already applied for this course
    $check = $db->prepare('SELECT id FROM course_discounts WHERE course_id = :cid LIMIT 1');
    $check->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $check->execute();
    $existing = $check->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'A discount has already been applied to this course']);
        exit;
    }

    // Insert discount record
    $ins = $db->prepare('INSERT INTO course_discounts (course_id, code, percent, applied_at) VALUES (:cid, :code, :percent, :applied_at)');
    $ins->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $ins->bindValue(':code', $code, PDO::PARAM_STR);
    $ins->bindValue(':percent', $discount_percent);
    $ins->bindValue(':applied_at', date('Y-m-d H:i:s'));
    $ins->execute();

    // Fetch course price to return discounted price
    $stmt = $db->prepare('SELECT price FROM courses WHERE id = :cid LIMIT 1');
    $stmt->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    $price = isset($course['price']) ? floatval($course['price']) : 0.0;
    $discounted = round($price * (1 - ($discount_percent/100)), 2);

    echo json_encode(['success' => true, 'course_id' => $course_id, 'code' => $code, 'percent' => $discount_percent, 'discounted_price' => $discounted]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

?>
