<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;

if ($course_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing course_id']);
    exit;
}

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    // Delete discount record if exists
    $del = $db->prepare('DELETE FROM course_discounts WHERE course_id = :cid');
    $del->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $del->execute();

    echo json_encode(['success' => true, 'course_id' => $course_id, 'message' => 'Discount removed']);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

?>
