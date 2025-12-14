<?php
include_once '../config/database.php';
include_once '../model/Course.php';
include_once '../model/Purchase.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$purchase = new Purchase($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Router principal
switch ($action) {
    case 'list':
        getCourses();
        break;
    case 'add':
        addCourse();
        break;
    case 'update':
        updateCourse();
        break;
    case 'delete':
        deleteCourse();
        break;
    case 'purchase':
        purchaseCourse();
        break;
    case 'check_purchase':
        checkUserPurchase();
        break;
    case 'purchase_stats':
        getPurchaseStats();
        break;
    case 'user_purchases':
        getUserPurchases();
        break;
    case 'course_purchase_stats':
        getCoursePurchaseStats();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        break;
}

// === FONCTIONS COURSES ===

function getCourses() {
    global $course;
    
    $stmt = $course->read();
    $num = $stmt->rowCount();
    
    $courses_arr = array();
    
    if ($num > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $course_item = array(
                'id' => $id,
                'title' => $title,
                'description' => $description,
                'license_type' => $license_type,
                'price' => $price,
                'duration' => $duration,
                'status' => $status,
                'created_at' => $created_at
            );
            array_push($courses_arr, $course_item);
        }
        echo json_encode(['success' => true, 'data' => $courses_arr]);
    } else {
        echo json_encode(['success' => true, 'data' => []]);
    }
}

function addCourse() {
    global $course;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    // Validation
    if (empty($data['title'])) {
        echo json_encode(['success' => false, 'message' => 'Course title is required']);
        return;
    }
    if (empty($data['description'])) {
        echo json_encode(['success' => false, 'message' => 'Description is required']);
        return;
    }
    if (empty($data['license_type'])) {
        echo json_encode(['success' => false, 'message' => 'License type is required']);
        return;
    }
    if ($data['license_type'] === 'paid' && (empty($data['price']) || floatval($data['price']) <= 0)) {
        echo json_encode(['success' => false, 'message' => 'Valid price is required for paid courses']);
        return;
    }
    if (empty($data['duration']) || intval($data['duration']) < 1) {
        echo json_encode(['success' => false, 'message' => 'Valid duration is required']);
        return;
    }
    
    // Assignation des données
    $course->title = $data['title'];
    $course->description = $data['description'];
    $course->license_type = $data['license_type'];
    $course->price = $data['license_type'] === 'paid' ? $data['price'] : 0;
    $course->duration = $data['duration'];
    
    // Création du cours
    if ($course->create()) {
        echo json_encode(['success' => true, 'message' => 'Course created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to create course']);
    }
}

function updateCourse() {
    global $course;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    // Validation
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        return;
    }
    if (empty($data['title'])) {
        echo json_encode(['success' => false, 'message' => 'Course title is required']);
        return;
    }
    if (empty($data['description'])) {
        echo json_encode(['success' => false, 'message' => 'Description is required']);
        return;
    }
    if (empty($data['license_type'])) {
        echo json_encode(['success' => false, 'message' => 'License type is required']);
        return;
    }
    if ($data['license_type'] === 'paid' && (empty($data['price']) || floatval($data['price']) <= 0)) {
        echo json_encode(['success' => false, 'message' => 'Valid price is required for paid courses']);
        return;
    }
    if (empty($data['duration']) || intval($data['duration']) < 1) {
        echo json_encode(['success' => false, 'message' => 'Valid duration is required']);
        return;
    }
    if (empty($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Status is required']);
        return;
    }
    
    // Assignation des données
    $course->id = $data['id'];
    $course->title = $data['title'];
    $course->description = $data['description'];
    $course->license_type = $data['license_type'];
    $course->price = $data['license_type'] === 'paid' ? $data['price'] : 0;
    $course->duration = $data['duration'];
    $course->status = $data['status'];
    
    // Mise à jour du cours
    if ($course->update()) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to update course']);
    }
}

function deleteCourse() {
    global $course;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        return;
    }
    
    $course->id = $data['id'];
    
    if ($course->delete()) {
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to delete course']);
    }
}

// === FONCTIONS PURCHASES ===

function purchaseCourse() {
    global $course, $purchase;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    // Validation des données requises
    if (empty($data['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        return;
    }
    if (empty($data['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    // Vérifier si le cours existe
    $course->id = $data['course_id'];
    if (!$course->readOne()) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        return;
    }
    
    // Vérifier si c'est un cours gratuit
    if ($course->license_type === 'free') {
        echo json_encode(['success' => false, 'message' => 'This is a free course - no purchase required']);
        return;
    }
    
    // Vérifier si l'utilisateur a déjà acheté ce cours
    if ($purchase->userHasPurchased($data['user_id'], $data['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'You have already purchased this course']);
        return;
    }
    
    // Créer l'enregistrement d'achat
    $purchase->course_id = $data['course_id'];
    $purchase->user_id = $data['user_id'];
    $purchase->purchase_date = date('Y-m-d H:i:s');
    $purchase->amount = $course->price;
    $purchase->status = 'completed';
    $purchase->payment_method = $data['payment_method'] ?? 'credit_card';
    $purchase->transaction_id = 'TXN_' . uniqid() . '_' . time();
    
    if ($purchase->create()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Course purchased successfully',
            'data' => [
                'transaction_id' => $purchase->transaction_id,
                'purchase_date' => $purchase->purchase_date,
                'amount' => $purchase->amount,
                'course_title' => $course->title
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to process purchase']);
    }
}

function checkUserPurchase() {
    global $purchase;
    
    $user_id = $_GET['user_id'] ?? '';
    $course_id = $_GET['course_id'] ?? '';
    
    if (empty($user_id) || empty($course_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID and Course ID are required']);
        return;
    }
    
    $hasPurchased = $purchase->userHasPurchased($user_id, $course_id);
    
    echo json_encode([
        'success' => true,
        'has_purchased' => $hasPurchased,
        'user_id' => $user_id,
        'course_id' => $course_id
    ]);
}

function getPurchaseStats() {
    global $purchase;
    
    $stats = $purchase->getPurchaseStats();
    
    if ($stats) {
        echo json_encode([
            'success' => true,
            'data' => [
                'total_purchases' => (int)$stats['total_purchases'],
                'total_revenue' => (float)$stats['total_revenue'],
                'average_purchase' => (float)$stats['average_purchase'],
                'unique_customers' => (int)$stats['unique_customers']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'total_purchases' => 0,
                'total_revenue' => 0,
                'average_purchase' => 0,
                'unique_customers' => 0
            ]
        ]);
    }
}

function getUserPurchases() {
    global $purchase;
    
    $user_id = $_GET['user_id'] ?? '';
    
    // If user_id is 'all', get all purchases for admin dashboard
    if ($user_id === 'all') {
        $stmt = $purchase->read();
    } else if (!empty($user_id)) {
        $stmt = $purchase->readByUser($user_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    $purchases_arr = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $purchase_item = array(
            'id' => $row['id'],
            'course_id' => $row['course_id'],
            'course_title' => $row['course_title'] ?? 'Unknown Course',
            'user_id' => $row['user_id'],
            'purchase_date' => $row['purchase_date'],
            'amount' => $row['amount'],
            'status' => $row['status'],
            'payment_method' => $row['payment_method'],
            'transaction_id' => $row['transaction_id'],
            'created_at' => $row['created_at']
        );
        array_push($purchases_arr, $purchase_item);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $purchases_arr,
        'count' => count($purchases_arr)
    ]);
}

function getCoursePurchaseStats() {
    global $purchase;
    
    $stmt = $purchase->getCoursePurchaseStats();
    $courseStats = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $courseStats[] = [
            'course_id' => $row['course_id'],
            'course_title' => $row['course_title'],
            'license_type' => $row['license_type'],
            'price' => (float)$row['price'],
            'course_status' => $row['course_status'],
            'purchase_count' => (int)$row['purchase_count'],
            'total_revenue' => (float)$row['total_revenue']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $courseStats
    ]);
}

// Handle case when no action is specified
if (empty($action)) {
    echo json_encode([
        'success' => false, 
        'message' => 'No action specified',
        'available_actions' => [
            'list', 'add', 'update', 'delete', 
            'purchase', 'check_purchase', 'purchase_stats', 'user_purchases', 'course_purchase_stats'
        ]
    ]);
}
?>