<?php
// API endpoint to generate content for course parts using AI

header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../model/ContentGenerator.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$action = isset($data['action']) ? trim($data['action']) : '';
$course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;
$part_title = isset($data['part_title']) ? trim($data['part_title']) : '';
$part_description = isset($data['part_description']) ? trim($data['part_description']) : '';

if (!$action || !$course_id || !$part_title) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$db = (new Database())->getConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

try {
    // Get course details
    $stmt = $db->prepare('SELECT title, description FROM courses WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }
    
    $generator = new ContentGenerator();
    
    if ($action === 'generate-part-content') {
        // Generate content for a specific part
        $result = $generator->generatePartContent($course['title'], $part_title, $part_description);
        
        if ($result['success']) {
            // Store in course_contents if generating full course, or save temporarily
            echo json_encode([
                'success' => true,
                'content' => $result['content'],
                'message' => 'Content generated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['error']
            ]);
        }
    } elseif ($action === 'generate-full-course') {
        // Generate full course content
        $result = $generator->generateCourseContent($course['title'], $course['description']);
        
        if ($result['success']) {
            // Save to course_contents table
            $ins = $db->prepare('INSERT INTO course_contents (course_id, content_html, created_at) VALUES (:cid, :html, :at) ON DUPLICATE KEY UPDATE content_html = :html');
            $ins->bindValue(':cid', $course_id, PDO::PARAM_INT);
            $ins->bindValue(':html', $result['content'], PDO::PARAM_STR);
            $ins->bindValue(':at', date('Y-m-d H:i:s'));
            $ins->execute();
            
            echo json_encode([
                'success' => true,
                'content' => $result['content'],
                'message' => 'Full course content generated and saved'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['error']
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
