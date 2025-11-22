<?php
session_start();

// Inclure la configuration
require_once '../../config.php';

// Connexion à la base de données
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch(PDOException $e) {
    $db_error = "Erreur de connexion à la base de données";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Power of AI Home | AI ShieldHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <link rel="apple-touch-icon" sizes="57x57" href="./assets/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="./assets/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="./assets/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="./assets/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./assets/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="./assets/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./assets/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="./assets/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="./assets/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="./assets/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Latest compiled and minified CSS -->
    <link href="assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/js/bootstrap.min.js">
    <!-- Font Awesome link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- StyleSheet link CSS -->
    <link href="assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="assets/css/custom-style.css" rel="stylesheet" type="text/css">
    <link href="assets/css/special-classes.css" rel="stylesheet" type="text/css">
    <link href="assets/css/responsive.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Styles pour la page de connexion/inscription -->
    <style>
    :root {
        --primary-violet: #6c63ff;
        --primary-blue: #4a7bff;
        --accent-pink: #ff6b9d;
        --accent-cyan: #00d4ff;
        --light-gray: #f5f7fa;
        --medium-gray: #e4e9f2;
        --dark-gray: #8c94a7;
        --text-dark: #2d3748;
        --white: #ffffff;
        --success-green: #10b981;
        --error-red: #ef4444;
    }
    
    .auth-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
        animation: fadeInBackdrop 0.4s ease-out;
    }
    
    @keyframes fadeInBackdrop {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .auth-container {
        display: flex;
        width: 90%;
        max-width: 950px;
        height: auto;
        min-height: 600px;
        max-height: 90vh;
        background: var(--white);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        animation: slideUpIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
    }
    
    @keyframes slideUpIn {
        from { 
            opacity: 0;
            transform: translateY(50px) scale(0.9);
        }
        to { 
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .auth-form-container {
        flex: 1;
        padding: 50px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow-y: auto;
        background: linear-gradient(135deg, rgba(108, 99, 255, 0.02) 0%, rgba(74, 123, 255, 0.02) 100%);
    }
    
    .auth-form-container::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-violet) 0%, var(--primary-blue) 100%);
        opacity: 0.03;
        z-index: 0;
        animation: pulse 8s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .auth-header {
        margin-bottom: 35px;
        position: relative;
        z-index: 1;
    }
    
    .auth-header h1 {
        color: var(--text-dark);
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        position: relative;
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-violet), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .auth-header h1::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-violet), var(--primary-blue));
        border-radius: 4px;
        animation: expandWidth 1s ease-out;
    }
    
    @keyframes expandWidth {
        from { width: 0; }
        to { width: 60px; }
    }
    
    .auth-header p {
        color: var(--dark-gray);
        font-size: 15px;
        margin-top: 15px;
    }
    
    .form-group {
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.3px;
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dark-gray);
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .input-with-icon input, 
    .input-with-icon select {
        width: 100%;
        padding: 14px 18px 14px 50px;
        border: 2px solid var(--medium-gray);
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: var(--white);
        color: var(--text-dark);
    }
    
    .input-with-icon input:focus,
    .input-with-icon select:focus {
        outline: none;
        border-color: var(--primary-violet);
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        transform: translateY(-2px);
    }
    
    .input-with-icon input:focus ~ i,
    .input-with-icon select:focus ~ i {
        color: var(--primary-violet);
        transform: translateY(-50%) scale(1.1);
    }
    
    .password-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dark-gray);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 18px;
        z-index: 2;
    }
    
    .password-toggle:hover {
        color: var(--primary-violet);
        transform: translateY(-50%) scale(1.2);
    }
    
    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        font-size: 14px;
    }
    
    .remember-me {
        display: flex;
        align-items: center;
    }
    
    .remember-me input[type="checkbox"] {
        margin-right: 8px;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .forgot-password {
        color: var(--primary-blue);
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .forgot-password:hover {
        color: var(--primary-violet);
        text-decoration: underline;
    }
    
    .auth-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary-violet) 0%, var(--primary-blue) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 20px rgba(108, 99, 255, 0.3);
        position: relative;
        overflow: hidden;
        margin-top: 10px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .auth-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: all 0.6s ease;
    }
    
    .auth-btn:hover::before {
        left: 100%;
    }
    
    .auth-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(108, 99, 255, 0.5);
    }
    
    .auth-btn:active {
        transform: translateY(-1px);
    }
    
    .auth-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .switch-mode-link {
        text-align: center;
        margin-top: 25px;
        font-size: 15px;
        color: var(--dark-gray);
        position: relative;
        z-index: 1;
    }
    
    .switch-mode-link a {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .switch-mode-link a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary-violet);
        transition: width 0.3s ease;
    }
    
    .switch-mode-link a:hover::after {
        width: 100%;
    }
    
    .switch-mode-link a:hover {
        color: var(--primary-violet);
    }
    
    .auth-graphics {
        flex: 1;
        background: linear-gradient(135deg, var(--primary-violet) 0%, var(--primary-blue) 50%, var(--accent-cyan) 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .graphics-content {
        text-align: center;
        color: white;
        position: relative;
        z-index: 2;
    }
    
    .graphics-content h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .graphics-content p {
        font-size: 16px;
        opacity: 0.95;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 1;
    }
    
    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 8s ease-in-out infinite;
    }
    
    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 10%;
        left: 15%;
        animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        left: 8%;
        animation-delay: 1.5s;
    }
    
    .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        top: 25%;
        right: 12%;
        animation-delay: 3s;
    }
    
    .shape:nth-child(4) {
        width: 100px;
        height: 100px;
        bottom: 8%;
        right: 18%;
        animation-delay: 4.5s;
    }
    
    .shape:nth-child(5) {
        width: 50px;
        height: 50px;
        top: 45%;
        right: 5%;
        animation-delay: 2s;
    }
    
    .ai-robot {
        width: 180px;
        height: 180px;
        position: relative;
        margin-bottom: 30px;
        filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.2));
    }
    
    .ai-robot .head {
        width: 90px;
        height: 90px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        animation: headFloat 3s ease-in-out infinite;
    }
    
    @keyframes headFloat {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50% { transform: translateX(-50%) translateY(-10px); }
    }
    
    .ai-robot .antenna {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 3px;
        height: 20px;
        background: white;
    }
    
    .ai-robot .antenna::after {
        content: '';
        position: absolute;
        top: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 8px;
        height: 8px;
        background: var(--accent-pink);
        border-radius: 50%;
        animation: antennaGlow 2s ease-in-out infinite;
    }
    
    @keyframes antennaGlow {
        0%, 100% { box-shadow: 0 0 0 rgba(255, 107, 157, 0.5); }
        50% { box-shadow: 0 0 20px rgba(255, 107, 157, 1); }
    }
    
    .ai-robot .eyes {
        display: flex;
        gap: 15px;
    }
    
    .ai-robot .eye {
        width: 18px;
        height: 18px;
        background: var(--primary-blue);
        border-radius: 50%;
        animation: blink 5s infinite;
        position: relative;
    }
    
    .ai-robot .eye::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
    }
    
    .ai-robot .body {
        width: 110px;
        height: 90px;
        background: white;
        border-radius: 18px;
        position: absolute;
        top: 75px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .ai-robot .screen {
        width: 80px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-cyan));
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 10px;
        font-weight: 700;
        overflow: hidden;
        position: relative;
    }
    
    .ai-robot .screen::after {
        content: 'AI ShieldHub';
        animation: textScroll 10s linear infinite;
        white-space: nowrap;
    }
    
    .ai-robot .arm {
        position: absolute;
        width: 12px;
        height: 60px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .ai-robot .arm-left {
        top: 85px;
        left: 20px;
        transform: rotate(-20deg);
        animation: armWave 3s ease-in-out infinite;
    }
    
    .ai-robot .arm-right {
        top: 85px;
        right: 20px;
        transform: rotate(20deg);
        animation: armWave 3s ease-in-out infinite 1.5s;
    }
    
    @keyframes armWave {
        0%, 100% { transform: rotate(-20deg); }
        50% { transform: rotate(-35deg); }
    }
    
    .close-auth {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 24px;
        color: var(--dark-gray);
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .close-auth:hover {
        background: rgba(255, 255, 255, 0.3);
        color: var(--primary-violet);
        transform: rotate(90deg) scale(1.1);
    }
    
    .alert {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInDown 0.4s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border-left: 4px solid var(--success-green);
    }
    
    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border-left: 4px solid var(--error-red);
    }
    
    .alert i {
        font-size: 18px;
    }
    
    /* Styles pour les messages d'erreur de validation */
    .error-message {
        color: var(--error-red);
        font-size: 12px;
        margin-top: 5px;
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }
    
    .input-error {
        border-color: var(--error-red) !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1) !important;
    }
    
    .input-success {
        border-color: var(--success-green) !important;
    }
    
    /* Champ rôle caché */
    .role-field {
        display: none !important;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    @keyframes blink {
        0%, 45%, 55%, 100% { transform: scaleY(1); }
        50% { transform: scaleY(0.1); }
    }
    
    @keyframes textScroll {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    
    @media (max-width: 768px) {
        .auth-container {
            flex-direction: column;
            height: auto;
            max-height: 95vh;
            overflow-y: auto;
        }
        
        .auth-graphics {
            order: -1;
            padding: 30px 25px;
            min-height: 250px;
        }
        
        .auth-form-container {
            padding: 35px 25px;
        }
        
        .ai-robot {
            width: 140px;
            height: 140px;
        }
        
        .ai-robot .head {
            width: 70px;
            height: 70px;
        }
        
        .ai-robot .body {
            width: 90px;
            height: 70px;
            top: 60px;
        }
        
        .ai-robot .eyes {
            gap: 10px;
        }
        
        .ai-robot .eye {
            width: 14px;
            height: 14px;
        }
        
        .auth-header h1 {
            font-size: 26px;
        }
        
        .graphics-content h2 {
            font-size: 22px;
        }
        
        .graphics-content p {
            font-size: 14px;
        }
    }
</style>
</head>
<body>
<!-- Modal de connexion/inscription -->
<div class="auth-modal" id="authModal">
    <div class="auth-container">
        <button class="close-auth" id="closeAuth">&times;</button>
        
        <!-- Formulaire d'inscription -->
        <div class="auth-form-container" id="registerForm" style="display: none;">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join AI ShieldHub and discover the power of AI</p>
            </div>
            
            <div id="registerAlerts"></div>
            
            <form id="registrationForm" novalidate>
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="registerName">Full Name</label>
                    <div class="input-with-icon">
                        <input type="text" id="registerName" name="name" placeholder="Enter your full name">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="error-message" id="nameError"></div>
                </div>
                
                <div class="form-group">
                    <label for="registerEmail">Email Address</label>
                    <div class="input-with-icon">
                        <input type="text" id="registerEmail" name="email" placeholder="Enter your email address">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="error-message" id="emailError"></div>
                </div>
                
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="registerPassword" name="password" placeholder="Create a password">
                        <i class="fas fa-lock"></i>
                        <span class="password-toggle" data-target="registerPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="passwordError"></div>
                </div>
                
                <div class="form-group">
                    <label for="registerConfirmPassword">Confirm Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="registerConfirmPassword" name="confirmPassword" placeholder="Confirm your password">
                        <i class="fas fa-lock"></i>
                        <span class="password-toggle" data-target="registerConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>

                <!-- Champ rôle caché avec valeur par défaut -->
                <div class="form-group role-field">
                    <label for="registerRole">Role</label>
                    <div class="input-with-icon">
                        <select id="registerRole" name="role">
                            <option value="student" selected>Student</option>
                            <option value="admin" style="display:none">Admin</option>
                        </select>
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="error-message" id="roleError"></div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="registerTerms" name="terms">
                        <label for="registerTerms">I accept the terms of use</label>
                    </div>
                </div>
                <div class="error-message" id="termsError"></div>
                
                <button type="submit" class="auth-btn">Create Account</button>
            </form>
            
            <div class="switch-mode-link">
                Already have an account? <a href="#" class="switch-to-login">Sign in</a>
            </div>
        </div>
        
        <!-- Formulaire de connexion -->
        <div class="auth-form-container" id="loginForm" style="display: none;">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to continue your AI journey</p>
            </div>
            
            <div id="loginAlerts"></div>
            
            <form id="signinForm" novalidate>
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <div class="input-with-icon">
                        <input type="text" id="loginEmail" name="email" placeholder="Enter your email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="error-message" id="loginEmailError"></div>
                </div>
                
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="loginPassword" name="password" placeholder="Enter your password">
                        <i class="fas fa-lock"></i>
                        <span class="password-toggle" data-target="loginPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="loginPasswordError"></div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="rememberMe" name="remember">
                        <label for="rememberMe">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="auth-btn">Sign In</button>
            </form>
            
            <div class="switch-mode-link">
                Don't have an account? <a href="#" class="switch-to-register">Create one</a>
            </div>
        </div>
        
        <div class="auth-graphics">
            <div class="ai-robot">
                <div class="antenna"></div>
                <div class="head">
                    <div class="eyes">
                        <div class="eye"></div>
                        <div class="eye"></div>
                    </div>
                </div>
                <div class="body">
                    <div class="screen"></div>
                </div>
                <div class="arm arm-left"></div>
                <div class="arm arm-right"></div>
            </div>
            
            <div class="graphics-content">
                <h2>The Future of AI Starts Here</h2>
                <p>Join our community and explore the infinite possibilities of artificial intelligence with cutting-edge cybersecurity protection</p>
            </div>
            
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
        </div>
    </div>
</div>
<!-- AJOUTEZ CES MODALES APRÈS LA MODAL authModal DANS VOTRE index.php -->

<!-- Modal Forgot Password -->
<div class="auth-modal" id="forgotPasswordModal" style="display: none;">
    <div class="auth-container" style="max-width: 500px;">
        <button class="close-auth" id="closeForgotPassword">&times;</button>
        
        <div class="auth-form-container" style="display: flex;">
            <div class="auth-header">
                <h1>Reset Password</h1>
                <p>Enter your email to receive a verification code</p>
            </div>
            
            <div id="forgotPasswordAlerts"></div>
            
            <form id="forgotPasswordForm" novalidate>
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="form-group">
                    <label for="forgotEmail">Email Address</label>
                    <div class="input-with-icon">
                        <input type="email" id="forgotEmail" name="email" placeholder="Enter your email address" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="error-message" id="forgotEmailError"></div>
                </div>
                
                <button type="submit" class="auth-btn">Send Verification Code</button>
            </form>
            
            <div class="switch-mode-link">
                Remember your password? <a href="#" class="back-to-login">Sign in</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verify Code -->
<div class="auth-modal" id="verifyCodeModal" style="display: none;">
    <div class="auth-container" style="max-width: 500px;">
        <button class="close-auth" id="closeVerifyCode">&times;</button>
        
        <div class="auth-form-container" style="display: flex;">
            <div class="auth-header">
                <h1>Verify Code</h1>
                <p>Enter the 6-digit code sent to your email</p>
            </div>
            
            <div id="verifyCodeAlerts"></div>
            
            <form id="verifyCodeForm" novalidate>
                <input type="hidden" name="action" value="verify_code">
                <input type="hidden" id="verifyEmail" name="email">
                
                <div class="form-group">
                    <label for="verificationCode">Verification Code</label>
                    <div class="input-with-icon">
                        <input type="text" id="verificationCode" name="code" placeholder="Enter 6-digit code" maxlength="6" required>
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="error-message" id="verificationCodeError"></div>
                </div>
                
                <button type="submit" class="auth-btn">Verify Code</button>
            </form>
            
            <div class="switch-mode-link">
                Didn't receive the code? <a href="#" class="resend-code">Resend code</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="auth-modal" id="resetPasswordModal" style="display: none;">
    <div class="auth-container" style="max-width: 500px;">
        <button class="close-auth" id="closeResetPassword">&times;</button>
        
        <div class="auth-form-container" style="display: flex;">
            <div class="auth-header">
                <h1>Create New Password</h1>
                <p>Enter your new password</p>
            </div>
            
            <div id="resetPasswordAlerts"></div>
            
            <form id="resetPasswordForm" novalidate>
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" id="resetEmail" name="email">
                <input type="hidden" id="resetCode" name="code">
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="newPassword" name="new_password" placeholder="Enter new password" required>
                        <i class="fas fa-lock"></i>
                        <span class="password-toggle" data-target="newPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="newPasswordError"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirmNewPassword">Confirm New Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="confirmNewPassword" name="confirm_password" placeholder="Confirm new password" required>
                        <i class="fas fa-lock"></i>
                        <span class="password-toggle" data-target="confirmNewPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="error-message" id="confirmNewPasswordError"></div>
                </div>
                
                <button type="submit" class="auth-btn">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles additionnels pour les modales de reset password */
.verification-code-input {
    text-align: center;
    font-size: 24px;
    letter-spacing: 10px;
    font-weight: bold;
}

.resend-code {
    color: var(--primary-blue);
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
}

.resend-code:hover {
    color: var(--primary-violet);
    text-decoration: underline;
}

.back-to-login {
    color: var(--primary-blue);
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
}

.back-to-login:hover {
    color: var(--primary-violet);
    text-decoration: underline;
}
</style>
<!-- Le reste de votre contenu HTML existant -->
<!-- Header -->
<!-- Back to top button -->
<a id="button"></a>
<div class="power_of_ai">
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="./index.html"><figure class="mb-0 banner-logo"><img src="./assets/images/home2-logo.png" alt="" class="img-fluid" style="max-width: 150px;"></figure></a>
                <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown active">
                            <a class="nav-link dropdown-toggle dropdown-color navbar-text-color" href="#" id="navbarDropdownone" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"> Home </a>
                            <div class="dropdown-menu drop-down-content">
                                <ul class="list-unstyled drop-down-pages" aria-labelledby="navbarDropdownone">
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./index.html">Home 1</a></li>
                                    <li class="nav-item active"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./index1.html">Home 2</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./index2.html">Home 3</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./about.html">About</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle dropdown-color navbar-text-color" href="#" id="navbarDropdowntwo" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"> Pages </a>
                            <div class="dropdown-menu drop-down-content">
                                <ul class="list-unstyled drop-down-pages" aria-labelledby="navbarDropdowntwo">
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./cart.html">Cart</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./checkout.html">Checkout</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./shop.html">Our Shop</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./product-detail.html">Product Detail</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle dropdown-color navbar-text-color" href="#" id="navbarDropdownthree" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"> Blog </a>
                            <div class="dropdown-menu drop-down-content">
                                <ul class="list-unstyled drop-down-pages" aria-labelledby="navbarDropdownthree">
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="./blog.html">Blog</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="single-blog.html">Single Blog</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="load-more.html">Load More</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="one-column.html">One Column</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="two-column.html">Two Column</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="three-column.html">Three Column</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="three-colum-sidbar.html">Three Column Sidebar</a></li>
                                    <li class="nav-item"><i class="fa-solid fa-chevron-right"></i><a class="dropdown-item nav-link" href="four-column.html">Four Column</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./service.html">Service</a>
                        </li>
                    </ul>
                    <div class="last_list">
                        <a class="login" href="#" id="openLogin">Login</a>
                        
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Le reste de votre contenu existant -->
    <section class="banner-section">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-7 col-md-12 col-sm-12 col-12">
                    <div class="banner_content">
                        <h1>Master<span class="type-text"></span><br>with The Power of<span>Innovation.</span></h1>
                        <p class="paragraph">Transform your future. Learn cutting-edge cybersecurity skills from industry experts and protect the digital world.…</p>
                       
                        <div class="tags">
                            <span class="heading">Popular Tags:</span>
                            <span class="list text-size-16">Creative ~ Hyperreality ~ Steampunk ~ Animation ~ Business</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-sm-12 col-12">
                    <div class="banner_wrapper">
                        <figure class="mb-0 banner-main-image">
                            <img class="img-fluid" src="./assets/images/banner2-arrow.png" alt="">
                        </figure>
                        <figure class="mb-0 banner-image1">
                            <img class="img-fluid" src="./assets/images/cyber-security-hero.svg" alt="" style="max-width: 400px; width: 100%;">
                        </figure>
                    </div>
                </div>
            </div>
        </div>  
    </section>
</div>

<!-- Le reste de votre code HTML existant -->
<!-- Logos -->
<div class="logo-section2">
    <div class="container">
        <ul class="mb-0 list-unstyled">
            <li>
                <figure class="mb-0 logo">
                    <img class="img-fluid" src="./assets/images/home2-logo1.png" alt="">
                </figure>
            </li>
            <li>
                <figure class="mb-0 logo">
                    <img class="img-fluid" src="./assets/images/home2-logo2.png" alt="">
                </figure>
            </li>
            <li>
                <figure class="mb-0 logo">
                    <img class="img-fluid" src="./assets/images/home2-logo3.png" alt="">
                </figure>
            </li>
            <li>
                <figure class="mb-0 logo">
                    <img class="img-fluid" src="./assets/images/home2-logo4.png" alt="">
                </figure>
            </li>
            <li>
                <figure class="mb-0 logo">
                    <img class="img-fluid" src="./assets/images/home2-logo5.png" alt="">
                </figure>
            </li>
        </ul>
    </div>
</div>

<!-- Footer -->
<div class="power_footer_portion">
    <div class="footer-section">
        <div class="container">        
            <div class="middle-portion">
                <div class="row">
                    <div class="col-lg-4 col-md-5 col-sm-6 col-12">
                        <a href="./index.html">
                            <figure class="footer-logo">
                                <img src="./assets/images/footer2-logo.png" class="img-fluid" alt="">
                            </figure>
                        </a>
                        <p class="text-size-18 footer-text">Qorem ipsum dolor sit amet, consectetur adipiscing elit aut elit tellus luctus nec ulla corper mattis aulvinar daibus leo.</p>
                        <ul class="list-unstyled mb-0 social-icons">
                            <li><a href="#" class="text-decoration-none"><i class="fa-brands fa-facebook-f social-networks"></i></a></li>
                            <li><a href="#" class="text-decoration-none"><i class="fa-brands fa-twitter social-networks"></i></a></li>
                            <li><a href="#" class="text-decoration-none"><i class="fa-brands fa-linkedin-in social-networks"></i></a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-12 col-12 d-md-block d-none">
                        <div class="links">
                            <ul class="list-unstyled mb-0">
                                <li><i class="fa-solid fa-circle"></i><a href="./index.html" class=" text-size-18 text text-decoration-none">Home</a></li>
                                <li><i class="fa-solid fa-circle"></i><a href="./about.html" class=" text-size-18 text text-decoration-none">About</a></li>
                                <li><i class="fa-solid fa-circle"></i><a href="./service.html" class=" text-size-18 text text-decoration-none">Services</a></li>
                                <li><i class="fa-solid fa-circle"></i><a href="./contact.html" class=" text-size-18 text text-decoration-none">Contact us</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12 d-sm-block">
                        <div class="icon">
                            <ul class="list-unstyled mb-0">
                                <li class="text">
                                    <i class="fa fa-phone fa-icon footer-location"></i>
                                    <a href="tel:+61383766284" class="mb-0 text text-decoration-none text-size-16">+61 3 8376 6284</a>
                                </li>
                                <li class="text">
                                    <i class="fa fa-envelope fa-icon footer-location"></i>
                                    <a href="mailto:info@aishieldhub.com" class="mb-0 text text-decoration-none text-size-16">Info@aishieldhub.com</a>
                                </li>
                                <li class="text">
                                    <i class="fa-solid fa-location-dot footer-location footer-location3"></i>
                                    <p class="text1 text-size-16">21 King Street Melbourne, 3000, Australia</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-2 col-sm-12 col-12 d-lg-block d-none">
                        <div class="email-form">
                            <form action="javascript:;">
                                <div class="form-group position-relative">
                                    <input type="text" class="form_style" placeholder="Enter Email:" name="email">
                                    <button><i class="send fa-sharp fa-solid fa-paper-plane"></i></button>
                                </div>
                                <div class="form-group check-box">
                                    <input type="checkbox" id="term">
                                    <label for="term">Quis autem vel eum iure reprehenderit rui in ea voluptate esse.</label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <div class="row">
                    <div class="col-12">
                        <p class="mb-0 text-size-16 text-white">Copyright © 2023 AI ShieldHub All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PRE LOADER -->
<div class="js">
    <div id="preloader"></div>
</div>

<!-- Latest compiled JavaScript -->
<script src="assets/js/jquery-3.6.0.min.js"> </script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"> </script>
<script src="assets/js/bootstrap.min.js"> </script>
<script src="assets/js/video_link.js"></script>
<script src="./assets/js/video.js"></script>
<script src="assets/js/counter.js"></script>
<script src="assets/js/owl.carousel.js"></script>
<script src="assets/js/custom-carousel.js"></script>
<script src="assets/js/custom.js"></script>
<script src="assets/js/animation_links.js"></script>
<script src="assets/js/animation.js"></script>
<script src="assets/js/gallery.js"></script>
<script src="assets/js/preloader.js"></script>
<script src="assets/js/back-to-top-button.js"></script>
<script src="assets/js/text_type.js"></script>

<script>
// REMPLACEZ TOUT LE JAVASCRIPT DANS index.php PAR CE CODE

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // ============================================
    // ÉLÉMENTS DE BASE
    // ============================================
    const authModal = document.getElementById('authModal');
    const openLogin = document.getElementById('openLogin');
    const closeAuth = document.getElementById('closeAuth');
    const registerFormContainer = document.getElementById('registerForm');
    const loginFormContainer = document.getElementById('loginForm');
    
    const switchToLoginBtns = document.querySelectorAll('.switch-to-login');
    const switchToRegisterBtns = document.querySelectorAll('.switch-to-register');
    
    // ============================================
    // ÉLÉMENTS RESET PASSWORD
    // ============================================
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const verifyCodeModal = document.getElementById('verifyCodeModal');
    const resetPasswordModal = document.getElementById('resetPasswordModal');
    
    const closeForgotPassword = document.getElementById('closeForgotPassword');
    const closeVerifyCode = document.getElementById('closeVerifyCode');
    const closeResetPassword = document.getElementById('closeResetPassword');
    
    // Variable globale pour stocker l'email
    let resetEmail = '';
    
    // ============================================
    // FONCTIONS DE VALIDATION
    // ============================================
    function validateName(name) {
        const nameRegex = /^[a-zA-ZÀ-ÿ\s]{2,50}$/;
        return nameRegex.test(name.trim());
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email.trim());
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function validateConfirmPassword(password, confirmPassword) {
        return password === confirmPassword;
    }

    function validateTerms(termsChecked) {
        return termsChecked;
    }

    // ============================================
    // FONCTIONS D'AFFICHAGE D'ERREURS
    // ============================================
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.getElementById(fieldId.replace('Error', ''));
        
        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            inputElement.classList.add('input-error');
            inputElement.classList.remove('input-success');
        }
    }

    function hideError(fieldId) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.getElementById(fieldId.replace('Error', ''));
        
        if (errorElement && inputElement) {
            errorElement.style.display = 'none';
            inputElement.classList.remove('input-error');
            inputElement.classList.add('input-success');
        }
    }
    
    function clearAlerts() {
        document.getElementById('registerAlerts').innerHTML = '';
        document.getElementById('loginAlerts').innerHTML = '';
    }
    
    function resetValidation() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(error => {
            error.style.display = 'none';
        });
        
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.classList.remove('input-error');
            input.classList.remove('input-success');
        });
    }
    
    function resetForms() {
        document.getElementById('registrationForm').reset();
        document.getElementById('signinForm').reset();
        resetValidation();
        clearAlerts();
    }
    
    function showLoginForm() {
        registerFormContainer.style.display = 'none';
        loginFormContainer.style.display = 'flex';
        clearAlerts();
        resetValidation();
    }
    
    function showRegisterForm() {
        loginFormContainer.style.display = 'none';
        registerFormContainer.style.display = 'flex';
        clearAlerts();
        resetValidation();
    }

    // ============================================
    // GESTION MODAL LOGIN/REGISTER
    // ============================================
    openLogin.addEventListener('click', function(e) {
        e.preventDefault();
        authModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        showRegisterForm();
    });
    
    closeAuth.addEventListener('click', function() {
        authModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForms();
    });
    
    authModal.addEventListener('click', function(e) {
        if (e.target === authModal) {
            authModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForms();
        }
    });
    
    switchToLoginBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });
    });
    
    switchToRegisterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showRegisterForm();
        });
    });
    
    // ============================================
    // TOGGLE PASSWORD
    // ============================================
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input && icon) {
                if (input.getAttribute('type') === 'password') {
                    input.setAttribute('type', 'text');
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.setAttribute('type', 'password');
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });

    // ============================================
    // VALIDATION EN TEMPS RÉEL - REGISTER
    // ============================================
    const registerName = document.getElementById('registerName');
    const registerEmail = document.getElementById('registerEmail');
    const registerPassword = document.getElementById('registerPassword');
    const registerConfirmPassword = document.getElementById('registerConfirmPassword');
    const registerTerms = document.getElementById('registerTerms');
    
    if (registerName) {
        registerName.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                showError('nameError', 'Full name is required');
            } else if (!validateName(this.value)) {
                showError('nameError', 'Please enter a valid name (2-50 characters, letters and spaces only)');
            } else {
                hideError('nameError');
            }
        });
    }

    if (registerEmail) {
        registerEmail.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                showError('emailError', 'Email address is required');
            } else if (!validateEmail(this.value)) {
                showError('emailError', 'Please enter a valid email address');
            } else {
                hideError('emailError');
            }
        });
    }

    if (registerPassword) {
        registerPassword.addEventListener('blur', function() {
            if (this.value === '') {
                showError('passwordError', 'Password is required');
            } else if (!validatePassword(this.value)) {
                showError('passwordError', 'Password must be at least 6 characters long');
            } else {
                hideError('passwordError');
            }
        });
    }

    if (registerConfirmPassword) {
        registerConfirmPassword.addEventListener('blur', function() {
            const password = document.getElementById('registerPassword').value;
            if (this.value === '') {
                showError('confirmPasswordError', 'Please confirm your password');
            } else if (!validateConfirmPassword(password, this.value)) {
                showError('confirmPasswordError', 'Passwords do not match');
            } else {
                hideError('confirmPasswordError');
            }
        });
    }

    if (registerTerms) {
        registerTerms.addEventListener('change', function() {
            if (!validateTerms(this.checked)) {
                showError('termsError', 'You must accept the terms of use');
            } else {
                hideError('termsError');
            }
        });
    }

    // ============================================
    // VALIDATION EN TEMPS RÉEL - LOGIN
    // ============================================
    const loginEmail = document.getElementById('loginEmail');
    const loginPassword = document.getElementById('loginPassword');
    
    if (loginEmail) {
        loginEmail.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                showError('loginEmailError', 'Email address is required');
            } else if (!validateEmail(this.value)) {
                showError('loginEmailError', 'Please enter a valid email address');
            } else {
                hideError('loginEmailError');
            }
        });
    }

    if (loginPassword) {
        loginPassword.addEventListener('blur', function() {
            if (this.value === '') {
                showError('loginPasswordError', 'Password is required');
            } else {
                hideError('loginPasswordError');
            }
        });
    }

    // ============================================
    // SOUMISSION REGISTER
    // ============================================
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        document.getElementById('registerRole').value = 'student';
        
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('registerConfirmPassword').value;
        const termsChecked = document.getElementById('registerTerms').checked;
        
        let isValid = true;
        
        if (!validateName(name)) {
            showError('nameError', 'Please enter a valid name');
            isValid = false;
        }
        
        if (!validateEmail(email)) {
            showError('emailError', 'Please enter a valid email address');
            isValid = false;
        }
        
        if (!validatePassword(password)) {
            showError('passwordError', 'Password must be at least 6 characters long');
            isValid = false;
        }
        
        if (!validateConfirmPassword(password, confirmPassword)) {
            showError('confirmPasswordError', 'Passwords do not match');
            isValid = false;
        }
        
        if (!validateTerms(termsChecked)) {
            showError('termsError', 'You must accept the terms of use');
            isValid = false;
        }
        
        if (isValid) {
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.auth-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            fetch('../../controller/UserController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertsContainer = document.getElementById('registerAlerts');
                
                if (data.success) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        resetForms();
                        showLoginForm();
                        document.getElementById('loginAlerts').innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span>Account created successfully! Please sign in.</span>
                            </div>
                        `;
                    }, 2000);
                } else {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('registerAlerts').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>An error occurred during registration</span>
                    </div>
                `;
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }
    });
    
    // ============================================
    // SOUMISSION LOGIN
    // ============================================
    document.getElementById('signinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        let isValid = true;
        
        if (!validateEmail(email)) {
            showError('loginEmailError', 'Please enter a valid email address');
            isValid = false;
        }
        
        if (password === '') {
            showError('loginPasswordError', 'Password is required');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('.auth-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Signing In...';
        submitBtn.disabled = true;
        
        fetch('../../controller/UserController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertsContainer = document.getElementById('loginAlerts');
            
            if (data.success) {
                alertsContainer.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>${data.message}</span>
                    </div>
                `;
                
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }, 1500);
            } else {
                alertsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>${data.message}</span>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loginAlerts').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>An error occurred during login</span>
                </div>
            `;
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // ============================================
    // GESTION FORGOT PASSWORD MODAL
    // ============================================
    
    // Ouvrir forgot password
    const forgotPasswordLink = document.querySelector('.forgot-password');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Forgot password clicked');
            authModal.style.display = 'none';
            forgotPasswordModal.style.display = 'flex';
        });
    }
    
    // Fermer les modales
    if (closeForgotPassword) {
        closeForgotPassword.addEventListener('click', function() {
            forgotPasswordModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('forgotPasswordForm').reset();
            document.getElementById('forgotPasswordAlerts').innerHTML = '';
            resetValidation();
        });
    }

    if (closeVerifyCode) {
        closeVerifyCode.addEventListener('click', function() {
            verifyCodeModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('verifyCodeForm').reset();
            document.getElementById('verifyCodeAlerts').innerHTML = '';
            resetValidation();
        });
    }

    if (closeResetPassword) {
        closeResetPassword.addEventListener('click', function() {
            resetPasswordModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('resetPasswordForm').reset();
            document.getElementById('resetPasswordAlerts').innerHTML = '';
            resetValidation();
        });
    }

    // Retour à la connexion
    document.querySelectorAll('.back-to-login').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            forgotPasswordModal.style.display = 'none';
            verifyCodeModal.style.display = 'none';
            resetPasswordModal.style.display = 'none';
            authModal.style.display = 'flex';
            showLoginForm();
        });
    });

    // Fermer en cliquant à l'extérieur
    [forgotPasswordModal, verifyCodeModal, resetPasswordModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });

    // ============================================
    // FORGOT PASSWORD FORM
    // ============================================
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Forgot password form submitted');
            
            const email = document.getElementById('forgotEmail').value.trim();
            console.log('Email:', email);
            
            if (!validateEmail(email)) {
                showError('forgotEmailError', 'Please enter a valid email address');
                return;
            }
            
            hideError('forgotEmailError');
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.auth-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Sending Code...';
            submitBtn.disabled = true;
            
            console.log('Sending request...');
            
            fetch('../../controller/UserController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Data:', data);
                const alertsContainer = document.getElementById('forgotPasswordAlerts');
                
                if (data.success) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                    
                    resetEmail = email;
                    document.getElementById('verifyEmail').value = email;
                    
                    setTimeout(() => {
                        forgotPasswordModal.style.display = 'none';
                        verifyCodeModal.style.display = 'flex';
                        document.getElementById('forgotPasswordForm').reset();
                        alertsContainer.innerHTML = '';
                    }, 2000);
                } else {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('forgotPasswordAlerts').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>An error occurred. Please try again.</span>
                    </div>
                `;
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // ============================================
    // VERIFY CODE FORM
    // ============================================
    const verifyCodeForm = document.getElementById('verifyCodeForm');
    if (verifyCodeForm) {
        verifyCodeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('verificationCode').value.trim();
            
            if (code.length !== 6) {
                showError('verificationCodeError', 'Please enter a 6-digit code');
                return;
            }
            
            hideError('verificationCodeError');
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.auth-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Verifying...';
            submitBtn.disabled = true;
            
            fetch('../../controller/UserController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertsContainer = document.getElementById('verifyCodeAlerts');
                
                if (data.success) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                    
                    document.getElementById('resetEmail').value = resetEmail;
                    document.getElementById('resetCode').value = code;
                    
                    setTimeout(() => {
                        verifyCodeModal.style.display = 'none';
                        resetPasswordModal.style.display = 'flex';
                        document.getElementById('verifyCodeForm').reset();
                        alertsContainer.innerHTML = '';
                    }, 1500);
                } else {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('verifyCodeAlerts').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>An error occurred. Please try again.</span>
                    </div>
                `;
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // ============================================
    // RESEND CODE
    // ============================================
    document.querySelectorAll('.resend-code').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!resetEmail) {
                alert('Please start from the beginning.');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'forgot_password');
            formData.append('email', resetEmail);
            
            fetch('../../controller/UserController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertsContainer = document.getElementById('verifyCodeAlerts');
                
                if (data.success) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>A new code has been sent to your email.</span>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        alertsContainer.innerHTML = '';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    // ============================================
    // RESET PASSWORD FORM
    // ============================================
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmNewPassword').value;
            
            let isValid = true;
            
            if (!validatePassword(newPassword)) {
                showError('newPasswordError', 'Password must be at least 6 characters long');
                isValid = false;
            } else {
                hideError('newPasswordError');
            }
            
            if (newPassword !== confirmPassword) {
                showError('confirmNewPasswordError', 'Passwords do not match');
                isValid = false;
            } else {
                hideError('confirmNewPasswordError');
            }
            
            if (!isValid) {
                return;
            }
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.auth-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Resetting Password...';
            submitBtn.disabled = true;
            
            fetch('../../controller/UserController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertsContainer = document.getElementById('resetPasswordAlerts');
                
                if (data.success) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        resetPasswordModal.style.display = 'none';
                        authModal.style.display = 'flex';
                        showLoginForm();
                        document.getElementById('resetPasswordForm').reset();
                        
                        document.getElementById('loginAlerts').innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span>Password reset successfully! Please sign in with your new password.</span>
                            </div>
                        `;
                        
                        resetEmail = '';
                    }, 2000);
                } else {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${data.message}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('resetPasswordAlerts').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>An error occurred. Please try again.</span>
                    </div>
                `;
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // ============================================
    // VALIDATION TEMPS RÉEL - RESET PASSWORD
    // ============================================
    const forgotEmail = document.getElementById('forgotEmail');
    if (forgotEmail) {
        forgotEmail.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                showError('forgotEmailError', 'Email address is required');
            } else if (!validateEmail(this.value)) {
                showError('forgotEmailError', 'Please enter a valid email address');
            } else {
                hideError('forgotEmailError');
            }
        });
    }

    const verificationCode = document.getElementById('verificationCode');
    if (verificationCode) {
        verificationCode.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length === 6) {
                hideError('verificationCodeError');
            }
        });

        verificationCode.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                showError('verificationCodeError', 'Verification code is required');
            } else if (this.value.length !== 6) {
                showError('verificationCodeError', 'Code must be 6 digits');
            } else {
                hideError('verificationCodeError');
            }
        });
    }

    const newPassword = document.getElementById('newPassword');
    if (newPassword) {
        newPassword.addEventListener('blur', function() {
            if (this.value === '') {
                showError('newPasswordError', 'Password is required');
            } else if (!validatePassword(this.value)) {
                showError('newPasswordError', 'Password must be at least 6 characters long');
            } else {
                hideError('newPasswordError');
            }
        });
    }

    const confirmNewPassword = document.getElementById('confirmNewPassword');
    if (confirmNewPassword) {
        confirmNewPassword.addEventListener('blur', function() {
            const newPwd = document.getElementById('newPassword').value;
            if (this.value === '') {
                showError('confirmNewPasswordError', 'Please confirm your password');
            } else if (!validateConfirmPassword(newPwd, this.value)) {
                showError('confirmNewPasswordError', 'Passwords do not match');
            } else {
                hideError('confirmNewPasswordError');
            }
        });
    }
    
    console.log('Initialization complete');
});
</script>
</body>
</html>