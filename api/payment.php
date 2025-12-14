<?php
// API endpoint for payment processing
// This file should be placed in the root or an api directory

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
require_once '../controller/PaymentController.php';

// Route the request
try {
    $controller = new PaymentController();
    
    // Determine the action based on URL or request parameters
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'process':
        case '':
            // Default action is process payment
            $controller->processPayment();
            break;
            
        case 'health':
            $controller->healthCheck();
            break;
            
        case 'validate':
            // Just validate card without processing payment
            $controller->validateCard();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Action not found',
                'available_actions' => ['process', 'health', 'validate']
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>