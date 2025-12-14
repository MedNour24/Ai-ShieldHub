<?php
// Simple migration helper: creates course_discounts table if it doesn't exist
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `course_discounts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `code` VARCHAR(64) NOT NULL,
        `percent` DECIMAL(5,2) NOT NULL,
        `applied_at` DATETIME NOT NULL,
        UNIQUE KEY `unique_course` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $db->exec($sql);
    echo json_encode(['success' => true, 'message' => 'migration OK']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
