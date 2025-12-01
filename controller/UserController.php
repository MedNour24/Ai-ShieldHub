<?php
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

// Fonction pour générer un CAPTCHA simple
function generateCaptcha() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $captcha;
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
    
    // Récupérer l'action depuis POST ou GET
    $action = '';
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } elseif (isset($_POST['action'])) {
        $action = $_POST['action'];
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
        case 'generate_captcha':
            handleGenerateCaptcha();
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
        case 'get_stats':
            handleGetStats($userModel);
            break;
        case 'get_current_profile':
            handleGetCurrentProfile($userModel);
            break;
        case 'update_profile':
            handleUpdateProfile($userModel);
            break;
        case 'ban_user':
            handleBanUser($userModel);
            break;
        case 'unban_user':
            handleUnbanUser($userModel);
            break;
        case 'get_notifications':
            handleGetNotifications($userModel);
            break;
        case 'mark_notification_read':
            handleMarkNotificationRead($userModel);
            break;
        case 'mark_all_notifications_read':
            handleMarkAllNotificationsRead($userModel);
            break;
        case 'delete_notification':
            handleDeleteNotification($userModel);
            break;
        case 'check_suspicious_activity':
            handleCheckSuspiciousActivity($userModel);
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
    $captcha_input = trim($_POST['captcha_input'] ?? '');
    $captcha_code = $_POST['captcha_code'] ?? '';
    
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
    
    // Vérification CAPTCHA
    if (empty($captcha_input)) {
        sendJsonResponse(false, 'Please complete the CAPTCHA verification');
    }
    
    if ($captcha_input !== $captcha_code) {
        sendJsonResponse(false, 'CAPTCHA verification failed. Please try again.');
    }
    
    if ($userModel->emailExists($email)) {
        sendJsonResponse(false, 'This email is already registered');
    }
    
    if ($userModel->createUser($name, $email, $password, $role)) {
        // Créer une notification pour l'inscription
        try {
            $userModel->createNotification(
                'registration',
                'Nouvel utilisateur inscrit',
                "Un nouvel utilisateur '{$name}' ({$email}) s'est inscrit avec le rôle '{$role}'.",
                null,
                'low'
            );
        } catch (Exception $e) {
            error_log("Erreur création notification inscription: " . $e->getMessage());
        }
        
        sendJsonResponse(true, 'Account created successfully! Please sign in.');
    } else {
        sendJsonResponse(false, 'Failed to create account. Please try again.');
    }
}

function handleLogin($userModel) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Obtenir l'IP et User Agent
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    if (empty($email) || !validateEmail($email)) {
        // Enregistrer tentative échouée
        $userModel->logLoginAttempt($email, $ipAddress, $userAgent, false);
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($password)) {
        $userModel->logLoginAttempt($email, $ipAddress, $userAgent, false);
        sendJsonResponse(false, 'Password is required');
    }
    
    $user = $userModel->getUserByEmail($email);
    
    if (!$user) {
        $userModel->logLoginAttempt($email, $ipAddress, $userAgent, false);
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    if (!password_verify($password, $user['password'])) {
        $userModel->logLoginAttempt($email, $ipAddress, $userAgent, false);
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    if ($user['status'] !== 'active') {
        $userModel->logLoginAttempt($email, $ipAddress, $userAgent, false);
        sendJsonResponse(false, 'Your account has been suspended. Please contact support for assistance.');
    }
    
    // Enregistrer tentative réussie
    $userModel->logLoginAttempt($email, $ipAddress, $userAgent, true);
    
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

function handleGenerateCaptcha() {
    $captcha = generateCaptcha();
    $_SESSION['captcha_code'] = $captcha;
    
    // Créer une image CAPTCHA simple
    $width = 200;
    $height = 60;
    $image = imagecreate($width, $height);
    
    // Couleurs
    $bg_color = imagecolorallocate($image, 245, 247, 250); // Fond clair
    $text_color = imagecolorallocate($image, 108, 99, 255); // Texte violet
    $noise_color = imagecolorallocate($image, 200, 200, 200); // Bruit
    
    // Remplir le fond
    imagefill($image, 0, 0, $bg_color);
    
    // Ajouter du bruit
    for ($i = 0; $i < 100; $i++) {
        imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
    }
    
    // Ajouter des lignes de bruit
    for ($i = 0; $i < 5; $i++) {
        imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
    }
    
    // Position du texte
    $font_size = 20;
    $text_bbox = imagettfbbox($font_size, 0, __DIR__ . '/../assets/fonts/arial.ttf', $captcha);
    $text_width = $text_bbox[2] - $text_bbox[0];
    $text_height = $text_bbox[1] - $text_bbox[7];
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2 + $text_height;
    
    // Essayer différentes polices
    $fonts = [
        __DIR__ . '/../assets/fonts/arial.ttf',
        __DIR__ . '/../assets/fonts/verdana.ttf'
    ];
    
    $selected_font = $fonts[0];
    if (file_exists($fonts[1])) {
        $selected_font = $fonts[array_rand($fonts)];
    }
    
    // Dessiner le texte
    imagettftext($image, $font_size, 0, $x, $y, $text_color, $selected_font, $captcha);
    
    // Envoyer l'image
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
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
    
    // Redirection vers la page d'accueil
    $redirectUrl = '/user1/view/FutureAi/index.php';
    sendJsonResponse(true, 'Logged out successfully', null, $redirectUrl);
}

function handleGetStats($userModel) {
    try {
        // Statistiques avancées
        $advancedStats = $userModel->getAdvancedStats();
        
        // Statistiques mensuelles pour l'histogramme
        $monthlyStats = $userModel->getMonthlyUserStats();
        
        // Statistiques d'activité des utilisateurs
        $activityStats = $userModel->getUserActivityStats();
        
        sendJsonResponse(true, 'Statistics retrieved successfully', [
            'advanced_stats' => $advancedStats,
            'monthly_stats' => $monthlyStats,
            'activity_stats' => $activityStats
        ]);
    } catch (Exception $e) {
        error_log("Erreur récupération stats: " . $e->getMessage());
        sendJsonResponse(false, 'Failed to retrieve statistics');
    }
}

function handleGetCurrentProfile($userModel) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        $user = $userModel->getUserById($userId);
        
        if ($user) {
            // Construire l'URL complète de l'image de profil
            $profileImage = 'assets/img/profile.jpg'; // Image par défaut
            if (!empty($user['profile_image'])) {
                // Vérifier si c'est un avatar ou une image uploadée
                if (strpos($user['profile_image'], 'avatars/') === 0) {
                    $profileImage = 'assets/img/' . $user['profile_image'];
                } else {
                    $profileImage = 'assets/uploads/' . $user['profile_image'];
                }
            }
            
            sendJsonResponse(true, 'Profile data retrieved successfully', [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profile_image' => $profileImage,
                'role' => $user['role'],
                'created_at' => $user['created_at']
            ]);
        } else {
            sendJsonResponse(false, 'User not found');
        }
    } catch (Exception $e) {
        error_log("Database error in getCurrentProfile: " . $e->getMessage());
        sendJsonResponse(false, 'Database error: ' . $e->getMessage());
    }
}

function handleUpdateProfile($userModel) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    $userId = $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $selectedAvatar = $_POST['selected_avatar'] ?? '';
    
    // Validation basique
    if (empty($name)) {
        sendJsonResponse(false, 'Name is required');
    }
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    try {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        if ($userModel->emailExists($email, $userId)) {
            sendJsonResponse(false, 'Email already exists for another user');
        }
        
        // Gérer le mot de passe si fourni
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                sendJsonResponse(false, 'Password must be at least 6 characters');
            }
            
            if ($newPassword !== $confirmPassword) {
                sendJsonResponse(false, 'Passwords do not match');
            }
        }
        
        // Gérer l'image de profil
        $uploadedFileName = null;
        
        // Priorité 1: Avatar sélectionné
        if (!empty($selectedAvatar)) {
            // Vérifier que l'avatar existe dans le dossier avatars
            $avatarPath = __DIR__ . '/../../../assets/img/avatars/' . $selectedAvatar;
            if (file_exists($avatarPath)) {
                // Supprimer l'ancienne image uploadée (pas les avatars)
                deleteOldUploadedImage($userId);
                $uploadedFileName = 'avatars/' . $selectedAvatar;
            } else {
                sendJsonResponse(false, 'Selected avatar does not exist');
            }
        }
        // Priorité 2: Image uploadée
        elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleProfileImageUpload($userId);
            
            if ($uploadResult['success']) {
                $uploadedFileName = $uploadResult['file_name'];
            } else {
                sendJsonResponse(false, $uploadResult['message']);
            }
        }
        
        // Mettre à jour le profil dans la base de données
        if ($userModel->updateUserProfile($userId, $name, $email, $newPassword, $uploadedFileName)) {
            // Récupérer les données mises à jour pour la réponse
            $updatedUser = $userModel->getUserById($userId);
            
            // Construire l'URL de l'image
            $profileImageUrl = 'assets/img/profile.jpg';
            if (!empty($updatedUser['profile_image'])) {
                // Vérifier si c'est un avatar ou une image uploadée
                if (strpos($updatedUser['profile_image'], 'avatars/') === 0) {
                    $profileImageUrl = 'assets/img/' . $updatedUser['profile_image'];
                } else {
                    $profileImageUrl = 'assets/uploads/' . $updatedUser['profile_image'];
                }
            }
            
            // Mettre à jour les données de session
            $_SESSION['user_name'] = $updatedUser['name'];
            $_SESSION['user_email'] = $updatedUser['email'];
            
            sendJsonResponse(true, 'Profile updated successfully', [
                'name' => $updatedUser['name'],
                'email' => $updatedUser['email'],
                'profile_image' => $profileImageUrl
            ]);
        } else {
            sendJsonResponse(false, 'Failed to update profile in database');
        }
        
    } catch (Exception $e) {
        error_log("Database error in updateProfile: " . $e->getMessage());
        sendJsonResponse(false, 'Database error: ' . $e->getMessage());
    }
}

function handleProfileImageUpload($userId) {
    $uploadDir = __DIR__ . '/../../../assets/uploads/';
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Could not create upload directory'
            ];
        }
    }
    
    $file = $_FILES['profile_image'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Types de fichiers autorisés
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.'
        ];
    }
    
    // Vérifier la taille du fichier (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return [
            'success' => false,
            'message' => 'File size must be less than 2MB'
        ];
    }
    
    // Vérifier que c'est bien une image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return [
            'success' => false,
            'message' => 'Uploaded file is not a valid image'
        ];
    }
    
    // Générer un nom de fichier unique
    $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $fileName;
    
    // Supprimer l'ancienne image uploadée si elle existe
    deleteOldUploadedImage($userId);
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        return [
            'success' => true,
            'file_name' => $fileName
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to upload file'
        ];
    }
}

function deleteOldUploadedImage($userId) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $userModel = new UserModel($db);
        
        $user = $userModel->getUserById($userId);
        
        if ($user && !empty($user['profile_image'])) {
            // Ne supprimer que si ce n'est pas un avatar
            if (strpos($user['profile_image'], 'avatars/') !== 0) {
                $oldImagePath = __DIR__ . '/../../../assets/uploads/' . $user['profile_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error deleting old uploaded image: " . $e->getMessage());
    }
}

function handleBanUser($userModel) {
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        sendJsonResponse(false, 'Unauthorized access');
    }
    
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($userId)) {
        sendJsonResponse(false, 'User ID is required');
    }
    
    // Empêcher l'admin de se bannir lui-même
    if ($userId == $_SESSION['user_id']) {
        sendJsonResponse(false, 'You cannot ban yourself');
    }
    
    // Vérifier si l'utilisateur existe
    $user = $userModel->getUserById($userId);
    if (!$user) {
        sendJsonResponse(false, 'User not found');
    }
    
    // Vérifier si l'utilisateur est déjà banni
    if ($user['status'] === 'inactive') {
        sendJsonResponse(false, 'User is already banned');
    }
    
    if ($userModel->banUser($userId)) {
        sendJsonResponse(true, 'User has been banned successfully', [
            'user_id' => $userId,
            'new_status' => 'inactive'
        ]);
    } else {
        sendJsonResponse(false, 'Failed to ban user');
    }
}

function handleUnbanUser($userModel) {
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        sendJsonResponse(false, 'Unauthorized access');
    }
    
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($userId)) {
        sendJsonResponse(false, 'User ID is required');
    }
    
    // Vérifier si l'utilisateur existe
    $user = $userModel->getUserById($userId);
    if (!$user) {
        sendJsonResponse(false, 'User not found');
    }
    
    // Vérifier si l'utilisateur est déjà actif
    if ($user['status'] === 'active') {
        sendJsonResponse(false, 'User is already active');
    }
    
    if ($userModel->unbanUser($userId)) {
        sendJsonResponse(true, 'User has been unbanned successfully', [
            'user_id' => $userId,
            'new_status' => 'active'
        ]);
    } else {
        sendJsonResponse(false, 'Failed to unban user');
    }
}

function handleGetNotifications($userModel) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    try {
        $userId = $_SESSION['user_id'];
        $notifications = $userModel->getAllNotifications($userId, 50);
        $unreadCount = $userModel->countUnreadNotifications($userId);
        
        sendJsonResponse(true, 'Notifications retrieved successfully', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    } catch (Exception $e) {
        error_log("Erreur récupération notifications: " . $e->getMessage());
        sendJsonResponse(false, 'Failed to retrieve notifications');
    }
}

function handleMarkNotificationRead($userModel) {
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    $notificationId = $_POST['notification_id'] ?? '';
    
    if (empty($notificationId)) {
        sendJsonResponse(false, 'Notification ID is required');
    }
    
    try {
        $userId = $_SESSION['user_id'];
        if ($userModel->markNotificationAsRead($userId, $notificationId)) {
            $unreadCount = $userModel->countUnreadNotifications($userId);
            sendJsonResponse(true, 'Notification marked as read', [
                'unread_count' => $unreadCount
            ]);
        } else {
            sendJsonResponse(false, 'Failed to mark notification as read');
        }
    } catch (Exception $e) {
        error_log("Erreur marquage notification: " . $e->getMessage());
        sendJsonResponse(false, 'Error marking notification as read');
    }
}

function handleMarkAllNotificationsRead($userModel) {
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    try {
        $userId = $_SESSION['user_id'];
        if ($userModel->markAllNotificationsAsRead($userId)) {
            sendJsonResponse(true, 'All notifications marked as read', [
                'unread_count' => 0
            ]);
        } else {
            sendJsonResponse(false, 'Failed to mark all notifications as read');
        }
    } catch (Exception $e) {
        error_log("Erreur marquage toutes notifications: " . $e->getMessage());
        sendJsonResponse(false, 'Error marking notifications as read');
    }
}

function handleDeleteNotification($userModel) {
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'User not logged in');
    }
    
    $notificationId = $_POST['notification_id'] ?? '';
    
    if (empty($notificationId)) {
        sendJsonResponse(false, 'Notification ID is required');
    }
    
    try {
        $userId = $_SESSION['user_id'];
        if ($userModel->deleteNotification($userId, $notificationId)) {
            $unreadCount = $userModel->countUnreadNotifications($userId);
            sendJsonResponse(true, 'Notification deleted successfully', [
                'unread_count' => $unreadCount
            ]);
        } else {
            sendJsonResponse(false, 'Failed to delete notification');
        }
    } catch (Exception $e) {
        error_log("Erreur suppression notification: " . $e->getMessage());
        sendJsonResponse(false, 'Error deleting notification');
    }
}

function handleCheckSuspiciousActivity($userModel) {
    try {
        // Détecter les activités suspectes
        $suspiciousActivities = $userModel->detectSuspiciousActivity();
        
        // Créer des notifications pour chaque activité suspecte
        foreach ($suspiciousActivities as $activity) {
            $userModel->createNotification(
                'suspicious_activity',
                $activity['title'],
                $activity['message'],
                null,
                $activity['severity']
            );
        }
        
        // Obtenir les statistiques de connexion
        $loginStats = $userModel->getLoginStats();
        
        sendJsonResponse(true, 'Suspicious activity check completed', [
            'suspicious_count' => count($suspiciousActivities),
            'login_stats' => $loginStats
        ]);
    } catch (Exception $e) {
        error_log("Erreur vérification activité suspecte: " . $e->getMessage());
        sendJsonResponse(false, 'Error checking suspicious activity');
    }
}
?>