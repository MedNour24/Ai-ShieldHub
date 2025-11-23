<?php
/**
 * Contrôleur des utilisateurs
 * Gère l'inscription, la connexion, la réinitialisation de mot de passe
 */

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/UserModel.php';

// Inclure PHPMailer SANS Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

// Définir le header JSON
header('Content-Type: application/json');

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($success, $message, $data = null, $redirect = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }
    
    echo json_encode($response);
    exit;
}

// Fonction pour valider l'email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour valider le mot de passe
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Fonction pour générer un code de vérification à 6 chiffres
function generateVerificationCode() {
    return sprintf("%06d", mt_rand(0, 999999));
}

// Fonction pour envoyer un email avec PHPMailer
function sendVerificationEmail($email, $code, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fakraouikodra@gmail.com';
        $mail->Password   = 'ypdh bgyi iqpe ggmj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Options supplémentaires pour éviter les erreurs SSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Destinataires
        $mail->setFrom('fakraouikodra@gmail.com', 'AI ShieldHub');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('fakraouikodra@gmail.com', 'AI ShieldHub Support');
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Code de réinitialisation - AI ShieldHub';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f5f7fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 30px auto; 
                    padding: 0;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background: linear-gradient(135deg, #6c63ff 0%, #4a7bff 100%);
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    padding: 40px 30px;
                }
                .content h2 {
                    color: #2d3748;
                    font-size: 22px;
                    margin-bottom: 20px;
                }
                .content p {
                    color: #4a5568;
                    font-size: 16px;
                    line-height: 1.6;
                    margin-bottom: 20px;
                }
                .code { 
                    font-size: 36px; 
                    font-weight: bold; 
                    color: #6c63ff; 
                    text-align: center; 
                    padding: 25px; 
                    background: #f5f7fa; 
                    border-radius: 10px;
                    margin: 30px 0;
                    letter-spacing: 8px;
                    border: 2px dashed #6c63ff;
                }
                .warning {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .warning p {
                    margin: 0;
                    color: #856404;
                    font-size: 14px;
                }
                .footer { 
                    padding: 30px;
                    text-align: center;
                    background-color: #f5f7fa;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 5px 0;
                    font-size: 14px;
                    color: #718096;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔒 AI ShieldHub</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour $name,</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur AI ShieldHub.</p>
                    <p>Voici votre code de vérification à 6 chiffres :</p>
                    <div class='code'>$code</div>
                    <p style='text-align: center; font-weight: 600;'>Ce code est valide pendant <strong>15 minutes</strong>.</p>
                    <div class='warning'>
                        <p><strong>⚠️ Attention :</strong> Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>Cordialement,<br><strong>L'équipe AI ShieldHub</strong></p>
                    <p style='margin-top: 10px; font-size: 12px;'>© 2024 AI ShieldHub. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Bonjour $name,\n\nVotre code de vérification : $code\n\nCe code est valide pendant 15 minutes.\n\nCordialement,\nL'équipe AI ShieldHub";
        
        $mail->send();
        
        // Log pour le développement
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/verification_codes.txt';
        $logMessage = date('Y-m-d H:i:s') . " - Email: $email - Code: $code - Name: $name - Status: SENT\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/verification_codes.txt';
        $logMessage = date('Y-m-d H:i:s') . " - Email: $email - Code: $code - Name: $name - Status: FAILED - Error: {$mail->ErrorInfo}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return false;
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendJsonResponse(false, 'Database connection failed');
    }
    
    $userModel = new UserModel($db);
    
    $action = '';
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
    }
    
    error_log("Action received: " . $action);
    
    switch ($action) {
        case 'register':
            handleRegister($userModel);
            break;
        case 'login':
            handleLogin($userModel);
            break;
        case 'forgot_password':
            handleForgotPassword($userModel);
            break;
        case 'verify_code':
            handleVerifyCode($userModel);
            break;
        case 'reset_password':
            handleResetPassword($userModel);
            break;
        case 'list':
            handleList($userModel);
            break;
        case 'add':
            handleAdd($userModel);
            break;
        case 'update':
            handleUpdate($userModel);
            break;
        case 'delete':
            handleDelete($userModel);
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            sendJsonResponse(false, 'Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Exception in UserController: " . $e->getMessage());
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage());
}

// FONCTIONS DE GESTION

function handleRegister($userModel) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($name)) {
        sendJsonResponse(false, 'Name is required');
    }
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($password) || !validatePassword($password)) {
        sendJsonResponse(false, 'Password must be at least 6 characters');
    }
    
    if ($password !== $confirmPassword) {
        sendJsonResponse(false, 'Passwords do not match');
    }
    
    if ($userModel->emailExists($email)) {
        sendJsonResponse(false, 'This email is already registered');
    }
    
    if ($userModel->createUser($name, $email, $password, $role)) {
        sendJsonResponse(true, 'Account created successfully! Please sign in.');
    } else {
        sendJsonResponse(false, 'Failed to create account. Please try again.');
    }
}

function handleLogin($userModel) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($password)) {
        sendJsonResponse(false, 'Password is required');
    }
    
    $user = $userModel->getUserByEmail($email);
    
    if (!$user) {
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    if (!password_verify($password, $user['password'])) {
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    if ($user['status'] !== 'active') {
        sendJsonResponse(false, 'Your account is inactive. Please contact support.');
    }
    
    $userModel->updateLastLogin($user['id']);
    
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
    }
    
    $redirect = '';
    if ($user['role'] === 'admin') {
        $redirect = '../back/kaiadmin-lite-1.2.0/index.php';
    } elseif ($user['role'] === 'student') {
        $redirect = './index2.php';
    } else {
        $redirect = './dashboard.php';
    }

    sendJsonResponse(true, 'Login successful!', null, $redirect);
}

function handleForgotPassword($userModel) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    $user = $userModel->getUserByEmail($email);
    
    if (!$user) {
        sendJsonResponse(true, 'If this email exists, a verification code has been sent.');
    }
    
    $code = generateVerificationCode();
    $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60));
    
    if ($userModel->savePasswordResetCode($user['id'], $code, $expiresAt)) {
        if (sendVerificationEmail($email, $code, $user['name'])) {
            sendJsonResponse(true, 'A verification code has been sent to your email.');
        } else {
            sendJsonResponse(false, 'Failed to send verification code. Please try again later.');
        }
    } else {
        sendJsonResponse(false, 'An error occurred. Please try again.');
    }
}

function handleVerifyCode($userModel) {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($code)) {
        sendJsonResponse(false, 'Verification code is required');
    }
    
    $result = $userModel->verifyPasswordResetCode($email, $code);
    
    if ($result['valid']) {
        sendJsonResponse(true, 'Code verified successfully', ['user_id' => $result['user_id']]);
    } else {
        sendJsonResponse(false, $result['message']);
    }
}

function handleResetPassword($userModel) {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($code)) {
        sendJsonResponse(false, 'Verification code is required');
    }
    
    if (empty($newPassword) || !validatePassword($newPassword)) {
        sendJsonResponse(false, 'Password must be at least 6 characters');
    }
    
    if ($newPassword !== $confirmPassword) {
        sendJsonResponse(false, 'Passwords do not match');
    }
    
    $result = $userModel->verifyPasswordResetCode($email, $code);
    
    if (!$result['valid']) {
        sendJsonResponse(false, $result['message']);
    }
    
    if ($userModel->updatePassword($result['user_id'], $newPassword)) {
        $userModel->invalidatePasswordResetCode($result['user_id']);
        sendJsonResponse(true, 'Password reset successfully! You can now sign in.');
    } else {
        sendJsonResponse(false, 'Failed to reset password. Please try again.');
    }
}

function handleList($userModel) {
    $users = $userModel->getAllUsers();
    if ($users !== false) {
        sendJsonResponse(true, 'Users retrieved successfully', $users);
    } else {
        sendJsonResponse(false, 'Failed to retrieve users');
    }
}

function handleAdd($userModel) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($name)) {
        sendJsonResponse(false, 'Name is required');
    }
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($password) || !validatePassword($password)) {
        sendJsonResponse(false, 'Password must be at least 6 characters');
    }
    
    if ($password !== $confirmPassword) {
        sendJsonResponse(false, 'Passwords do not match');
    }
    
    if ($userModel->emailExists($email)) {
        sendJsonResponse(false, 'This email is already registered');
    }
    
    if ($userModel->createUser($name, $email, $password, $role)) {
        sendJsonResponse(true, 'User added successfully');
    } else {
        sendJsonResponse(false, 'Failed to add user');
    }
}

function handleUpdate($userModel) {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($id)) {
        sendJsonResponse(false, 'User ID is required');
    }
    
    if (empty($name)) {
        sendJsonResponse(false, 'Name is required');
    }
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if ($userModel->emailExists($email, $id)) {
        sendJsonResponse(false, 'This email is already used by another user');
    }
    
    if ($userModel->updateUser($id, $name, $email, $role, $status)) {
        sendJsonResponse(true, 'User updated successfully');
    } else {
        sendJsonResponse(false, 'Failed to update user');
    }
}

function handleDelete($userModel) {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        sendJsonResponse(false, 'User ID is required');
    }
    
    if ($userModel->deleteUser($id)) {
        sendJsonResponse(true, 'User deleted successfully');
    } else {
        sendJsonResponse(false, 'Failed to delete user');
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, "/");
    }
    
    sendJsonResponse(true, 'Logged out successfully', null, '../../view/front/future-ai/index.php');
}
?>