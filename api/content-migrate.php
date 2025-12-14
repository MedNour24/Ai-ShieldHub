<?php
// Migration helper: create course_contents table
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `course_contents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `content_html` LONGTEXT NOT NULL,
        `created_at` DATETIME NOT NULL,
        UNIQUE KEY `ux_course` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql);
    echo json_encode(['success' => true, 'message' => 'migration OK']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
