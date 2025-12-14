<?php
// PaymentController.php - Located in /controller/ directory

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include Stripe PHP library (manual installation)
require_once __DIR__ . '/../vendor/stripe-php/init.php';
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../config/StripeConfig.php';
include_once __DIR__ . '/../model/CreditCardVerification.php';
include_once __DIR__ . '/../model/Purchase.php';
include_once __DIR__ . '/../model/Course.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;

class PaymentController {
    private $db;
    private $purchase;
    private $course;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->purchase = new Purchase($this->db);
        $this->course = new Course($this->db);
        
        // Initialize Stripe with API key
        Stripe::setApiKey(StripeConfig::getSecretKey());
    }
    
    /**
     * Process payment with Stripe
     */
    public function processPayment() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            
            if ($method !== 'POST') {
                $this->sendResponse(405, false, 'Method not allowed');
                return;
            }
            
            $input = $this->getInputData();
            
            // Validate required fields
            $validation = $this->validateStripeInput($input);
            if (!$validation['valid']) {
                $this->sendResponse(400, false, 'Validation failed', ['errors' => $validation['errors']]);
                return;
            }
            
            // Check if course exists and is paid
            $this->course->id = $input['course_id'];
            if (!$this->course->readOne()) {
                $this->sendResponse(404, false, 'Course not found');
                return;
            }
            
            if ($this->course->license_type === 'free') {
                $this->sendResponse(400, false, 'This is a free course - no purchase required');
                return;
            }
            
            // Check if user already purchased
            if ($this->purchase->userHasPurchased($input['user_id'], $input['course_id'])) {
                $this->sendResponse(400, false, 'You have already purchased this course');
                return;
            }
            
            // Verify amount matches course price
            $amount = floatval($input['amount']);
            $coursePrice = floatval($this->course->price);
            
            if (abs($amount - $coursePrice) > 0.01) {
                $this->sendResponse(400, false, 'Amount does not match course price');
                return;
            }
            
            // Process payment with Stripe
            $paymentResult = $this->processStripePayment($input);
            
            if (!$paymentResult['success']) {
                $this->logPaymentAttempt($input, $paymentResult, false);
                $this->sendResponse(400, false, $paymentResult['message'], $paymentResult);
                return;
            }
            
            // Create purchase record
            $purchaseData = [
                'transaction_id' => $paymentResult['transaction_id'],
                'status' => 'completed',
                'payment_method' => 'stripe_card',
                'gateway_response' => json_encode($paymentResult['stripe_response'] ?? [])
            ];
            
            $purchaseId = $this->createPurchaseRecord($input, $purchaseData);
            
            if ($purchaseId) {
                $this->logPaymentAttempt($input, $paymentResult, true);
                
                $responseData = [
                    'purchase_id' => $purchaseId,
                    'course_id' => $input['course_id'],
                    'user_id' => $input['user_id'],
                    'transaction_id' => $paymentResult['transaction_id'],
                    'stripe_payment_intent_id' => $paymentResult['payment_intent_id'],
                    'amount' => $input['amount'],
                    'status' => 'completed',
                    'payment_method' => 'stripe',
                    'card_last4' => $paymentResult['card_last4'] ?? '',
                    'card_brand' => $paymentResult['card_brand'] ?? '',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'course_title' => $this->course->title,
                    'stripe_response' => $paymentResult['stripe_response'] ?? []
                ];
                
                $this->sendResponse(200, true, 'Payment processed successfully with Stripe', $responseData);
            } else {
                $this->sendResponse(500, false, 'Failed to create purchase record');
            }
            
        } catch (Exception $e) {
            $this->sendResponse(500, false, 'Server error: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a Stripe Payment Intent (for frontend)
     */
    public function createPaymentIntent() {
        try {
            $input = $this->getInputData();
            
            // Validation
            if (empty($input['course_id']) || empty($input['amount'])) {
                $this->sendResponse(400, false, 'Course ID and amount are required');
                return;
            }
            
            // Get course details
            $this->course->id = $input['course_id'];
            if (!$this->course->readOne()) {
                $this->sendResponse(404, false, 'Course not found');
                return;
            }
            
            $amount = floatval($input['amount']) * 100; // Convert to cents
            
            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => StripeConfig::getCurrency(),
                'payment_method_types' => StripeConfig::getPaymentMethods(),
                'metadata' => [
                    'course_id' => $input['course_id'],
                    'user_id' => $input['user_id'] ?? 'guest',
                    'course_title' => $this->course->title
                ]
            ]);
            
            $this->sendResponse(200, true, 'Payment intent created', [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'publishable_key' => StripeConfig::getPublishableKey(),
                'amount' => $input['amount'],
                'currency' => StripeConfig::getCurrency()
            ]);
            
        } catch (ApiErrorException $e) {
            $this->sendResponse(400, false, 'Stripe error: ' . $e->getMessage());
        } catch (Exception $e) {
            $this->sendResponse(500, false, 'Server error: ' . $e->getMessage());
        }
    }
    
    /**
     * Process Stripe payment
     */
    private function processStripePayment($input) {
        try {
            $amount = floatval($input['amount']) * 100; // Convert to cents
            
            // Check minimum amount
            if ($amount < StripeConfig::getMinimumCharge()) {
                return [
                    'success' => false,
                    'message' => 'Amount is below minimum charge'
                ];
            }
            
            // Process payment based on available data
            if (!empty($input['payment_method_id'])) {
                // Handle Payment Method ID from Stripe Elements
                return $this->processWithPaymentMethod($input, $amount);
            } elseif (!empty($input['payment_intent_id'])) {
                // Handle completed Payment Intent
                return $this->confirmPaymentIntent($input['payment_intent_id']);
            } elseif (!empty($input['stripe_token'])) {
                // Fallback to token-based payment (for backward compatibility)
                return $this->processWithToken($input, $amount);
            } else {
                return [
                    'success' => false,
                    'message' => 'No payment method provided'
                ];
            }
            
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'message' => 'Stripe payment failed: ' . $e->getMessage(),
                'error_type' => $e->getError()->type,
                'error_code' => $e->getError()->code,
                'stripe_error' => $e->getJsonBody()
            ];
        }
    }
    
    /**
     * Process payment with Payment Method
     */
    private function processWithPaymentMethod($input, $amount) {
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => StripeConfig::getCurrency(),
            'payment_method' => $input['payment_method_id'],
            'confirm' => true,
            'confirmation_method' => 'manual',
            'return_url' => $this->getReturnUrl(),
            'metadata' => [
                'course_id' => $input['course_id'],
                'user_id' => $input['user_id'],
                'course_title' => $this->course->title,
                'email' => $input['email'] ?? '',
                'name' => $input['cardholder_name'] ?? ''
            ],
            'description' => 'Purchase: ' . $this->course->title,
            'receipt_email' => $input['email'] ?? null
        ]);
        
        // Check if payment requires action (3D Secure)
        if ($paymentIntent->status === 'requires_action' || 
            $paymentIntent->status === 'requires_confirmation') {
            return [
                'success' => true,
                'requires_action' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ];
        }
        
        // Check if payment succeeded
        if ($paymentIntent->status === 'succeeded') {
            $charge = $paymentIntent->charges->data[0] ?? null;
            
            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'card_last4' => $charge->payment_method_details->card->last4 ?? '',
                'card_brand' => $charge->payment_method_details->card->brand ?? '',
                'stripe_response' => $paymentIntent->toArray()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Payment failed with status: ' . $paymentIntent->status,
            'stripe_response' => $paymentIntent->toArray()
        ];
    }
    
    /**
     * Confirm Payment Intent
     */
    private function confirmPaymentIntent($paymentIntentId) {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        
        if ($paymentIntent->status === 'succeeded') {
            $charge = $paymentIntent->charges->data[0] ?? null;
            
            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'card_last4' => $charge->payment_method_details->card->last4 ?? '',
                'card_brand' => $charge->payment_method_details->card->brand ?? '',
                'stripe_response' => $paymentIntent->toArray()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Payment not completed',
            'status' => $paymentIntent->status
        ];
    }
    
    /**
     * Process with token (legacy method)
     */
    private function processWithToken($input, $amount) {
        // Create charge with token
        $charge = Charge::create([
            'amount' => $amount,
            'currency' => StripeConfig::getCurrency(),
            'source' => $input['stripe_token'], // token from Stripe.js
            'description' => 'Purchase: ' . $this->course->title,
            'metadata' => [
                'course_id' => $input['course_id'],
                'user_id' => $input['user_id'],
                'course_title' => $this->course->title
            ],
            'receipt_email' => $input['email'] ?? null
        ]);
        
        if ($charge->status === 'succeeded') {
            return [
                'success' => true,
                'transaction_id' => $charge->id,
                'payment_intent_id' => $charge->payment_intent ?? $charge->id,
                'status' => $charge->status,
                'card_last4' => $charge->payment_method_details->card->last4 ?? '',
                'card_brand' => $charge->payment_method_details->card->brand ?? '',
                'stripe_response' => $charge->toArray()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Charge failed with status: ' . $charge->status
        ];
    }
    
    /**
     * Validate Stripe input
     */
    private function validateStripeInput($input) {
        $errors = [];
        
        $requiredFields = [
            'course_id' => 'Course ID',
            'user_id' => 'User ID',
            'amount' => 'Amount'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($input[$field])) {
                $errors[$field] = "$label is required";
            }
        }
        
        // Validate numeric fields
        if (!empty($input['amount']) && !is_numeric($input['amount'])) {
            $errors['amount'] = 'Amount must be a number';
        }
        
        if (!empty($input['course_id']) && !is_numeric($input['course_id'])) {
            $errors['course_id'] = 'Course ID must be a number';
        }
        
        if (!empty($input['user_id']) && !is_numeric($input['user_id'])) {
            $errors['user_id'] = 'User ID must be a number';
        }
        
        // Check for Stripe payment method
        if (empty($input['payment_method_id']) && empty($input['payment_intent_id']) && empty($input['stripe_token'])) {
            $errors['payment_method'] = 'Payment method is required (payment_method_id, payment_intent_id, or stripe_token)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Handle Stripe webhooks
     */
    public function handleWebhook() {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, StripeConfig::getWebhookSecret()
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }
        
        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSucceeded($paymentIntent);
                break;
                
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handlePaymentFailed($paymentIntent);
                break;
                
            case 'charge.refunded':
                $charge = $event->data->object;
                $this->handleRefund($charge);
                break;
                
            case 'charge.succeeded':
                $charge = $event->data->object;
                $this->handleChargeSucceeded($charge);
                break;
                
            default:
                // Log unexpected event type
                $this->logWebhookEvent($event->type, $event->id, $event->data->object);
        }
        
        http_response_code(200);
    }
    
    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded($paymentIntent) {
        $metadata = $paymentIntent->metadata;
        
        // Update purchase record if exists
        if (!empty($metadata->course_id) && !empty($metadata->user_id)) {
            $this->updatePurchaseStatus(
                $paymentIntent->id,
                'completed',
                json_encode($paymentIntent)
            );
        }
        
        // Log the event
        $this->logWebhookEvent('payment_intent.succeeded', $paymentIntent->id, $paymentIntent);
    }
    
    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($paymentIntent) {
        $metadata = $paymentIntent->metadata;
        
        if (!empty($metadata->course_id) && !empty($metadata->user_id)) {
            $this->updatePurchaseStatus(
                $paymentIntent->id,
                'failed',
                json_encode($paymentIntent)
            );
        }
        
        $this->logWebhookEvent('payment_intent.payment_failed', $paymentIntent->id, $paymentIntent);
    }
    
    /**
     * Handle charge succeeded
     */
    private function handleChargeSucceeded($charge) {
        $metadata = $charge->metadata;
        
        if (!empty($metadata->course_id) && !empty($metadata->user_id)) {
            $this->updatePurchaseStatus(
                $charge->payment_intent ?? $charge->id,
                'completed',
                json_encode($charge)
            );
        }
        
        $this->logWebhookEvent('charge.succeeded', $charge->id, $charge);
    }
    
    /**
     * Handle refund
     */
    private function handleRefund($charge) {
        // Update purchase status to refunded
        $this->updatePurchaseStatus(
            $charge->payment_intent ?? $charge->id,
            'refunded',
            json_encode($charge)
        );
        
        $this->logWebhookEvent('charge.refunded', $charge->id, $charge);
    }
    
    /**
     * Update purchase status
     */
    private function updatePurchaseStatus($transactionId, $status, $gatewayResponse = null) {
        try {
            $query = "UPDATE purchases SET status = :status, updated_at = NOW()";
            
            if ($gatewayResponse) {
                $query .= ", gateway_response = :gateway_response";
            }
            
            $query .= " WHERE transaction_id = :transaction_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':transaction_id', $transactionId);
            
            if ($gatewayResponse) {
                $stmt->bindParam(':gateway_response', $gatewayResponse);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Failed to update purchase status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log webhook event
     */
    private function logWebhookEvent($eventType, $eventId, $data) {
        $logDir = __DIR__ . '/../logs/stripe_webhooks/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Y-m-d') . '.log';
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $eventType,
            'event_id' => $eventId,
            'data' => is_object($data) ? $data->toArray() : $data,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get return URL for 3D Secure
     */
    private function getReturnUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $basePath = dirname($_SERVER['REQUEST_URI']);
        
        return $protocol . '://' . $host . $basePath . '/payment-return.php';
    }
    
    /**
     * Get payment methods
     */
    public function getPaymentMethods() {
        $this->sendResponse(200, true, 'Available payment methods', [
            'methods' => StripeConfig::getPaymentMethods(),
            'currency' => StripeConfig::getCurrency(),
            'publishable_key' => StripeConfig::getPublishableKey()
        ]);
    }
    
    /**
     * Refund a payment
     */
    public function refundPayment() {
        try {
            $input = $this->getInputData();
            
            if (empty($input['transaction_id']) || empty($input['amount'])) {
                $this->sendResponse(400, false, 'Transaction ID and amount are required');
                return;
            }
            
            $amount = floatval($input['amount']) * 100;
            
            $refund = Refund::create([
                'payment_intent' => $input['transaction_id'],
                'amount' => $amount,
                'reason' => $input['reason'] ?? 'requested_by_customer'
            ]);
            
            // Update purchase status
            $this->updatePurchaseStatus($input['transaction_id'], 'refunded', json_encode($refund));
            
            $this->sendResponse(200, true, 'Refund processed successfully', [
                'refund_id' => $refund->id,
                'amount' => $amount / 100,
                'status' => $refund->status
            ]);
            
        } catch (ApiErrorException $e) {
            $this->sendResponse(400, false, 'Refund failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get input data from request
     */
    private function getInputData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        
        // Sanitize input
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = htmlspecialchars(strip_tags(trim($value)));
            }
        }
        
        return $input;
    }
    
    /**
     * Create purchase record in database
     */
    private function createPurchaseRecord($input, $paymentData) {
        try {
            $this->purchase->course_id = $input['course_id'];
            $this->purchase->user_id = $input['user_id'];
            $this->purchase->purchase_date = date('Y-m-d H:i:s');
            $this->purchase->amount = $input['amount'];
            $this->purchase->status = $paymentData['status'];
            $this->purchase->payment_method = $paymentData['payment_method'];
            $this->purchase->transaction_id = $paymentData['transaction_id'];
            
            // Additional data for logging
            $this->purchase->created_at = date('Y-m-d H:i:s');
            
            if ($this->purchase->create()) {
                return $this->purchase->id;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Purchase creation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log payment attempt
     */
    private function logPaymentAttempt($input, $paymentResult, $success) {
        $logDir = __DIR__ . '/../logs/payments/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Y-m-d') . '.log';
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'success' => $success,
            'course_id' => $input['course_id'],
            'user_id' => $input['user_id'],
            'amount' => $input['amount'],
            'stripe_payment_intent_id' => $paymentResult['payment_intent_id'] ?? null,
            'transaction_id' => $paymentResult['transaction_id'] ?? null,
            'status' => $paymentResult['status'] ?? 'failed',
            'stripe_response' => $paymentResult['stripe_response'] ?? [],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Mask sensitive data
        $maskedInput = $input;
        if (isset($maskedInput['payment_method_id'])) {
            $maskedInput['payment_method_id'] = substr($maskedInput['payment_method_id'], 0, 8) . '...';
        }
        if (isset($maskedInput['stripe_token'])) {
            $maskedInput['stripe_token'] = 'tok_...';
        }
        $logData['input'] = $maskedInput;
        
        $logEntry = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Send JSON response
     */
    private function sendResponse($statusCode, $success, $message, $data = []) {
        http_response_code($statusCode);
        
        $response = [
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($data)) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
    
    /**
     * Health check endpoint
     */
    public function healthCheck() {
        // Test Stripe connection
        $stripeStatus = 'unknown';
        try {
            // Try to retrieve account information to test connection
            \Stripe\Account::retrieve();
            $stripeStatus = 'connected';
        } catch (Exception $e) {
            $stripeStatus = 'disconnected: ' . $e->getMessage();
        }
        
        // Test database connection
        $dbStatus = 'unknown';
        try {
            if ($this->db) {
                $stmt = $this->db->query("SELECT 1");
                $dbStatus = 'connected';
            } else {
                $dbStatus = 'disconnected: No database connection';
            }
        } catch (Exception $e) {
            $dbStatus = 'disconnected: ' . $e->getMessage();
        }
        
        $this->sendResponse(200, true, 'Payment API is running', [
            'version' => '2.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'stripe_status' => $stripeStatus,
            'database_status' => $dbStatus,
            'stripe_api_version' => \Stripe\Stripe::getApiVersion(),
            'endpoints' => [
                'POST /process' => 'Process payment with Stripe',
                'POST /create-payment-intent' => 'Create Stripe Payment Intent',
                'POST /webhook' => 'Stripe webhook handler',
                'GET /payment-methods' => 'Get available payment methods',
                'POST /refund' => 'Process refund',
                'GET /health' => 'Health check',
                'POST /validate' => 'Validate card'
            ]
        ]);
    }
    
    /**
     * Validate card without processing payment
     */
    public function validateCard() {
        $input = $this->getInputData();
        
        // Validate required fields
        $errors = [];
        if (empty($input['payment_method_id']) && empty($input['stripe_token'])) {
            $errors['payment_method'] = 'Payment method is required';
        }
        
        if (!empty($errors)) {
            $this->sendResponse(400, false, 'Card validation failed', [
                'errors' => $errors
            ]);
            return;
        }
        
        // Try to create a payment method to validate
        try {
            if (!empty($input['payment_method_id'])) {
                // Retrieve payment method to validate
                $paymentMethod = \Stripe\PaymentMethod::retrieve($input['payment_method_id']);
                
                $this->sendResponse(200, true, 'Card validation passed', [
                    'valid' => true,
                    'card_brand' => $paymentMethod->card->brand ?? 'unknown',
                    'last4' => $paymentMethod->card->last4 ?? '',
                    'exp_month' => $paymentMethod->card->exp_month ?? '',
                    'exp_year' => $paymentMethod->card->exp_year ?? '',
                    'country' => $paymentMethod->card->country ?? ''
                ]);
            } else {
                // For token validation
                $this->sendResponse(200, true, 'Token validation passed', [
                    'valid' => true,
                    'message' => 'Token is valid'
                ]);
            }
        } catch (ApiErrorException $e) {
            $this->sendResponse(400, false, 'Card validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Stripe customer by ID or create new one
     */
    public function getOrCreateCustomer($email, $name = null) {
        try {
            // Try to find existing customer by email
            $customers = Customer::all(['email' => $email, 'limit' => 1]);
            
            if (count($customers->data) > 0) {
                return $customers->data[0];
            }
            
            // Create new customer
            $customerData = ['email' => $email];
            if ($name) {
                $customerData['name'] = $name;
            }
            
            return Customer::create($customerData);
        } catch (ApiErrorException $e) {
            error_log('Failed to get/create customer: ' . $e->getMessage());
            return null;
        }
    }
}

// Main request handler
$action = $_GET['action'] ?? 'process';

// Check if it's a webhook request (has Stripe signature header)
if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    $controller = new PaymentController();
    $controller->handleWebhook();
    exit();
}

// Handle regular API requests
$controller = new PaymentController();

switch ($action) {
    case 'create-payment-intent':
        $controller->createPaymentIntent();
        break;
    case 'webhook':
        // Already handled above, but kept for clarity
        $controller->handleWebhook();
        break;
    case 'payment-methods':
        $controller->getPaymentMethods();
        break;
    case 'refund':
        $controller->refundPayment();
        break;
    case 'health':
        $controller->healthCheck();
        break;
    case 'validate':
        $controller->validateCard();
        break;
    case 'process':
    default:
        $controller->processPayment();
        break;
}
?>