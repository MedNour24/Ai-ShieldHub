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

// Inclure PHPMailer si disponible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

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

// Fonction pour envoyer un email
function sendVerificationEmail($email, $code, $name) {
    // ÉTAPE 1: Toujours enregistrer dans les logs (pour développement)
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/verification_codes.txt';
    $logMessage = date('Y-m-d H:i:s') . " - Email: $email - Code: $code - Name: $name\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    error_log("Code de vérification pour $email: $code");
    
    // ÉTAPE 2: Essayer d'envoyer l'email
    try {
        // Vérifier si PHPMailer est disponible
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return sendEmailWithPHPMailer($email, $code, $name);
        } else {
            // Utiliser la fonction mail() de PHP
            return sendEmailWithMailFunction($email, $code, $name);
        }
    } catch (Exception $e) {
        error_log("Erreur envoi email: " . $e->getMessage());
        // Continuer même si l'envoi échoue (le code est dans les logs)
        return true;
    }
}

// Envoi avec PHPMailer
function sendEmailWithPHPMailer($email, $code, $name) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fakraouikodra@gmail.com';
        $mail->Password = 'ypdh bgyi iqpe ggmj';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Expéditeur et destinataire
        $mail->setFrom('fakraouikodra@gmail.com', 'AI ShieldHub');
        $mail->addAddress($email, $name);
        
        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Code de réinitialisation - AI ShieldHub';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .code { font-size: 32px; font-weight: bold; color: #6c63ff; text-align: center; padding: 20px; background: #f5f7fa; border-radius: 10px; margin: 20px 0; }
                    .footer { margin-top: 30px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Bonjour $name,</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur AI ShieldHub.</p>
                    <p>Voici votre code de vérification :</p>
                    <div class='code'>$code</div>
                    <p>Ce code est valide pendant 15 minutes.</p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                    <div class='footer'>
                        <p>Cordialement,<br>L'équipe AI ShieldHub</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur PHPMailer: " . $e->getMessage());
        return false;
    }
}

// Envoi avec mail() de PHP
function sendEmailWithMailFunction($email, $code, $name) {
    $subject = "Code de réinitialisation - AI ShieldHub";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #6c63ff; text-align: center; padding: 20px; background: #f5f7fa; border-radius: 10px; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Bonjour $name,</h2>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur AI ShieldHub.</p>
            <p>Voici votre code de vérification :</p>
            <div class='code'>$code</div>
            <p>Ce code est valide pendant 15 minutes.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <div class='footer'>
                <p>Cordialement,<br>L'équipe AI ShieldHub</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: AI ShieldHub <noreply@aishieldhub.com>\r\n";
    
    return mail($email, $subject, $message, $headers);
}

try {
    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendJsonResponse(false, 'Database connection failed');
    }
    
    // Initialiser le modèle utilisateur
    $userModel = new UserModel($db);
    
    // Déterminer l'action à effectuer
    $action = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
    }
    
    // Log de l'action pour le débogage
    error_log("Action received: " . $action);
    error_log("POST data: " . print_r($_POST, true));
    
    // Router vers la bonne fonction selon l'action
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
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage());
}

// ============================================
// FONCTIONS DE GESTION
// ============================================

/**
 * Gestion de l'inscription
 */
function handleRegister($userModel) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    // Validation
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
    
    // Vérifier si l'email existe déjà
    if ($userModel->emailExists($email)) {
        sendJsonResponse(false, 'This email is already registered');
    }
    
    // Créer l'utilisateur
    if ($userModel->createUser($name, $email, $password, $role)) {
        sendJsonResponse(true, 'Account created successfully! Please sign in.');
    } else {
        sendJsonResponse(false, 'Failed to create account. Please try again.');
    }
}

/**
 * Gestion de la connexion
 */
function handleLogin($userModel) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($password)) {
        sendJsonResponse(false, 'Password is required');
    }
    
    // Récupérer l'utilisateur
    $user = $userModel->getUserByEmail($email);
    
    if (!$user) {
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        sendJsonResponse(false, 'Invalid email or password');
    }
    
    // Vérifier le statut
    if ($user['status'] !== 'active') {
        sendJsonResponse(false, 'Your account is inactive. Please contact support.');
    }
    
    // Mettre à jour la dernière connexion
    $userModel->updateLastLogin($user['id']);
    
    // Créer la session
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Gestion du "Remember me"
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
    }
    
    // Redirection selon le rôle
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

/**
 * Gestion de la demande de réinitialisation de mot de passe
 */
/**
 * Gestion de la demande de réinitialisation de mot de passe
 * VERSION DEBUG AVEC LOGS DÉTAILLÉS
 */
function handleForgotPassword($userModel) {
    error_log("========================================");
    error_log("DÉBUT handleForgotPassword");
    error_log("========================================");
    
    // Log de toutes les données POST
    error_log("Données POST reçues:");
    error_log(print_r($_POST, true));
    
    $email = trim($_POST['email'] ?? '');
    
    error_log("Email extrait: '" . $email . "'");
    error_log("Email vide? " . (empty($email) ? "OUI" : "NON"));
    
    // Validation
    if (empty($email)) {
        error_log("ERREUR: Email vide");
        sendJsonResponse(false, 'Email is required');
    }
    
    if (!validateEmail($email)) {
        error_log("ERREUR: Email invalide: " . $email);
        sendJsonResponse(false, 'Valid email is required');
    }
    
    error_log("Email valide, recherche de l'utilisateur...");
    
    // Vérifier si l'utilisateur existe
    try {
        $user = $userModel->getUserByEmail($email);
        error_log("Résultat recherche utilisateur:");
        error_log($user ? print_r($user, true) : "AUCUN UTILISATEUR TROUVÉ");
    } catch (Exception $e) {
        error_log("EXCEPTION lors de la recherche utilisateur: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendJsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    
    if (!$user) {
        error_log("Utilisateur non trouvé pour: " . $email);
        // Pour la sécurité, on renvoie quand même un message de succès
        sendJsonResponse(true, 'If this email exists, a verification code has been sent.');
    }
    
    error_log("Utilisateur trouvé: ID=" . $user['id'] . ", Name=" . $user['name']);
    
    // Générer un code de vérification
    $code = generateVerificationCode();
    $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 minutes
    
    error_log("Code généré: " . $code);
    error_log("Expire à: " . $expiresAt);
    
    // Enregistrer le code dans la base de données
    try {
        error_log("Tentative d'enregistrement du code dans la DB...");
        $saveResult = $userModel->savePasswordResetCode($user['id'], $code, $expiresAt);
        error_log("Résultat savePasswordResetCode: " . ($saveResult ? "SUCCÈS" : "ÉCHEC"));
        
        if (!$saveResult) {
            error_log("ERREUR: Échec d'enregistrement dans la DB");
            sendJsonResponse(false, 'Failed to save verification code to database');
        }
    } catch (Exception $e) {
        error_log("EXCEPTION lors de l'enregistrement du code: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendJsonResponse(false, 'Database error while saving code: ' . $e->getMessage());
    }
    
    error_log("Code enregistré dans la DB avec succès");
    
    // Envoyer l'email
    try {
        error_log("Tentative d'envoi de l'email...");
        $emailResult = sendVerificationEmail($email, $code, $user['name']);
        error_log("Résultat envoi email: " . ($emailResult ? "SUCCÈS" : "ÉCHEC"));
        
        if ($emailResult) {
            error_log("Email envoyé avec succès");
            sendJsonResponse(true, 'A verification code has been sent to your email.');
        } else {
            error_log("AVERTISSEMENT: Échec d'envoi email, mais code dans les logs");
            // Le code est dans les logs, donc on peut continuer
            sendJsonResponse(true, 'A verification code has been generated. Check logs/verification_codes.txt for development.');
        }
    } catch (Exception $e) {
        error_log("EXCEPTION lors de l'envoi email: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Continuer quand même car le code est dans les logs
        sendJsonResponse(true, 'Verification code saved in logs (email failed): ' . $e->getMessage());
    }
    
    error_log("========================================");
    error_log("FIN handleForgotPassword");
    error_log("========================================");
}

/**
 * Vérification du code de réinitialisation
 */
function handleVerifyCode($userModel) {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    error_log("Vérification code - Email: $email, Code: $code");
    
    // Validation
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(false, 'Valid email is required');
    }
    
    if (empty($code)) {
        sendJsonResponse(false, 'Verification code is required');
    }
    
    // Vérifier le code
    $result = $userModel->verifyPasswordResetCode($email, $code);
    
    error_log("Résultat vérification: " . print_r($result, true));
    
    if ($result['valid']) {
        sendJsonResponse(true, 'Code verified successfully', ['user_id' => $result['user_id']]);
    } else {
        sendJsonResponse(false, $result['message']);
    }
}

/**
 * Réinitialisation du mot de passe
 */
function handleResetPassword($userModel) {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
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
    
    // Vérifier à nouveau le code
    $result = $userModel->verifyPasswordResetCode($email, $code);
    
    if (!$result['valid']) {
        sendJsonResponse(false, $result['message']);
    }
    
    // Mettre à jour le mot de passe
    if ($userModel->updatePassword($result['user_id'], $newPassword)) {
        // Invalider le code utilisé
        $userModel->invalidatePasswordResetCode($result['user_id']);
        
        sendJsonResponse(true, 'Password reset successfully! You can now sign in.');
    } else {
        sendJsonResponse(false, 'Failed to reset password. Please try again.');
    }
}

/**
 * Liste des utilisateurs (admin)
 */
function handleList($userModel) {
    $users = $userModel->getAllUsers();
    
    if ($users !== false) {
        sendJsonResponse(true, 'Users retrieved successfully', $users);
    } else {
        sendJsonResponse(false, 'Failed to retrieve users');
    }
}

/**
 * Ajouter un utilisateur (admin)
 */
function handleAdd($userModel) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    // Validation
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

/**
 * Mettre à jour un utilisateur (admin)
 */
function handleUpdate($userModel) {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
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

/**
 * Supprimer un utilisateur (admin)
 */
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

/**
 * Déconnexion
 */
function handleLogout() {
    session_unset();
    session_destroy();
    
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, "/");
    }
    
    sendJsonResponse(true, 'Logged out successfully', null, '../../view/front/future-ai/index.php');
}
?>