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
    
    /* Styles pour le formulaire de réinitialisation combiné */
    .reset-combined-form {
        display: none;
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }

    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--medium-gray);
        z-index: 0;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--medium-gray);
        color: var(--dark-gray);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .step.active .step-circle {
        background: linear-gradient(135deg, var(--primary-violet), var(--primary-blue));
        color: white;
        box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
        transform: scale(1.1);
    }

    .step.completed .step-circle {
        background: var(--success-green);
        color: white;
    }

    .step-label {
        font-size: 12px;
        color: var(--dark-gray);
        font-weight: 500;
    }

    .step.active .step-label {
        color: var(--primary-violet);
        font-weight: 600;
    }

    .form-step {
        display: none;
    }

    .form-step.active {
        display: block;
        animation: slideInRight 0.4s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .code-input-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }

    .code-digit {
        width: 50px;
        height: 60px;
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        border: 2px solid var(--medium-gray);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .code-digit:focus {
        border-color: var(--primary-violet);
        box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        outline: none;
    }

    .timer-container {
        text-align: center;
        margin: 20px 0;
        font-size: 14px;
        color: var(--dark-gray);
    }

    .timer {
        font-weight: 700;
        color: var(--primary-violet);
        font-size: 16px;
    }

    .password-strength {
        margin-top: 10px;
    }

    .strength-bar {
        height: 6px;
        background: var(--medium-gray);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .strength-weak .strength-fill {
        width: 33%;
        background: var(--error-red);
    }

    .strength-medium .strength-fill {
        width: 66%;
        background: #fbbf24;
    }

    .strength-strong .strength-fill {
        width: 100%;
        background: var(--success-green);
    }

    .strength-text {
        font-size: 12px;
        font-weight: 600;
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
        
        .code-input-group {
            gap: 5px;
        }
        
        .code-digit {
            width: 40px;
            height: 50px;
            font-size: 22px;
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

<!-- Modal combinée pour le reset password -->
<div class="auth-modal" id="resetPasswordCombinedModal" style="display: none;">
    <div class="auth-container" style="max-width: 550px;">
        <button class="close-auth" id="closeResetCombined">&times;</button>
        
        <div class="auth-form-container reset-combined-form" style="display: flex;">
            <div class="auth-header">
                <h1>Reset Password</h1>
                <p>Follow the steps to reset your password</p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="step-indicator">
                <div class="step active" id="stepIndicator1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step" id="stepIndicator2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Verify</div>
                </div>
                <div class="step" id="stepIndicator3">
                    <div class="step-circle">3</div>
                    <div class="step-label">New Password</div>
                </div>
            </div>
            
            <div id="resetCombinedAlerts"></div>
            
            <!-- Étape 1: Email -->
            <div class="form-step active" id="emailStep">
                <form id="emailStepForm" novalidate>
                    <div class="form-group">
                        <label for="resetEmailInput">Email Address</label>
                        <div class="input-with-icon">
                            <input type="email" id="resetEmailInput" name="email" placeholder="Enter your email" required autocomplete="email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="error-message" id="resetEmailInputError"></div>
                    </div>
                    
                    <button type="submit" class="auth-btn">Send Verification Code</button>
                </form>
            </div>
            
            <!-- Étape 2: Code de vérification -->
            <div class="form-step" id="codeStep">
                <form id="codeStepForm" novalidate>
                    <div class="form-group">
                        <label>Verification Code</label>
                        <p style="font-size: 14px; color: var(--dark-gray); margin-bottom: 15px;">
                            Enter the 6-digit code sent to your email
                        </p>
                        <div class="code-input-group">
                            <input type="text" class="code-digit" maxlength="1" id="digit1" autocomplete="off">
                            <input type="text" class="code-digit" maxlength="1" id="digit2" autocomplete="off">
                            <input type="text" class="code-digit" maxlength="1" id="digit3" autocomplete="off">
                            <input type="text" class="code-digit" maxlength="1" id="digit4" autocomplete="off">
                            <input type="text" class="code-digit" maxlength="1" id="digit5" autocomplete="off">
                            <input type="text" class="code-digit" maxlength="1" id="digit6" autocomplete="off">
                        </div>
                        <input type="hidden" id="fullCodeInput" name="code">
                        <div class="error-message" id="codeStepError"></div>
                        
                        <div class="timer-container">
                            <p>Code expires in: <span class="timer" id="codeTimer">15:00</span></p>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn">Verify Code</button>
                    
                    <div class="switch-mode-link">
                        Didn't receive the code? <a href="#" class="resend-code-combined">Resend code</a>
                    </div>
                </form>
            </div>
            
            <!-- Étape 3: Nouveau mot de passe -->
            <div class="form-step" id="passwordStep">
                <form id="passwordStepForm" novalidate>
                    <div class="form-group">
                        <label for="finalNewPassword">New Password</label>
                        <div class="input-with-icon">
                            <input type="password" id="finalNewPassword" name="new_password" placeholder="Enter new password" required autocomplete="new-password">
                            <i class="fas fa-lock"></i>
                            <span class="password-toggle" data-target="finalNewPassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill"></div>
                            </div>
                            <div class="strength-text"></div>
                        </div>
                        <div class="error-message" id="finalNewPasswordError"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="finalConfirmPassword">Confirm New Password</label>
                        <div class="input-with-icon">
                            <input type="password" id="finalConfirmPassword" name="confirm_password" placeholder="Confirm new password" required autocomplete="new-password">
                            <i class="fas fa-lock"></i>
                            <span class="password-toggle" data-target="finalConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="error-message" id="finalConfirmPasswordError"></div>
                    </div>
                    
                    <button type="submit" class="auth-btn">Reset Password</button>
                </form>
            </div>
            
            <div class="switch-mode-link">
                Remember your password? <a href="#" class="back-to-login-combined">Sign in</a>
            </div>
        </div>
    </div>
</div>

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
document.addEventListener('DOMContentLoaded', function() {
    const authModal = document.getElementById('authModal');
    const openLogin = document.getElementById('openLogin');
    const closeAuth = document.getElementById('closeAuth');
    const registerFormContainer = document.getElementById('registerForm');
    const loginFormContainer = document.getElementById('loginForm');
    
    const switchToLoginBtns = document.querySelectorAll('.switch-to-login');
    const switchToRegisterBtns = document.querySelectorAll('.switch-to-register');
    
    // Fonctions de validation
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

    function validateRole(role) {
        // Toujours valide car le rôle est défini par défaut
        return true;
    }

    function validateTerms(termsChecked) {
        return termsChecked;
    }

    // Fonction pour afficher/masquer les erreurs
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.getElementById(fieldId.replace('Error', ''));
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        inputElement.classList.add('input-error');
        inputElement.classList.remove('input-success');
    }

    function hideError(fieldId) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.getElementById(fieldId.replace('Error', ''));
        
        errorElement.style.display = 'none';
        inputElement.classList.remove('input-error');
        inputElement.classList.add('input-success');
    }

    // Ouvrir la modal avec le formulaire d'inscription par défaut
    openLogin.addEventListener('click', function(e) {
        e.preventDefault();
        authModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        showRegisterForm();
    });
    
    // Fermer la modal
    closeAuth.addEventListener('click', function() {
        authModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForms();
    });
    
    // Fermer en cliquant à l'extérieur
    authModal.addEventListener('click', function(e) {
        if (e.target === authModal) {
            authModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForms();
        }
    });
    
    // Basculer vers le formulaire de connexion
    switchToLoginBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });
    });
    
    // Basculer vers le formulaire d'inscription
    switchToRegisterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showRegisterForm();
        });
    });
    
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
    
    function clearAlerts() {
        document.getElementById('registerAlerts').innerHTML = '';
        document.getElementById('loginAlerts').innerHTML = '';
    }
    
    function resetValidation() {
        // Réinitialiser tous les messages d'erreur et styles
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
    
    // Toggle pour afficher/masquer les mots de passe
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.setAttribute('type', 'password');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // VALIDATION EN TEMPS RÉEL POUR L'INSCRIPTION
    
    // Validation du nom
    document.getElementById('registerName').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showError('nameError', 'Full name is required');
        } else if (!validateName(this.value)) {
            showError('nameError', 'Please enter a valid name (2-50 characters, letters and spaces only)');
        } else {
            hideError('nameError');
        }
    });

    // Validation de l'email
    document.getElementById('registerEmail').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showError('emailError', 'Email address is required');
        } else if (!validateEmail(this.value)) {
            showError('emailError', 'Please enter a valid email address');
        } else {
            hideError('emailError');
        }
    });

    // Validation du mot de passe
    document.getElementById('registerPassword').addEventListener('blur', function() {
        if (this.value === '') {
            showError('passwordError', 'Password is required');
        } else if (!validatePassword(this.value)) {
            showError('passwordError', 'Password must be at least 6 characters long');
        } else {
            hideError('passwordError');
        }
    });

    // Validation de la confirmation du mot de passe
    document.getElementById('registerConfirmPassword').addEventListener('blur', function() {
        const password = document.getElementById('registerPassword').value;
        if (this.value === '') {
            showError('confirmPasswordError', 'Please confirm your password');
        } else if (!validateConfirmPassword(password, this.value)) {
            showError('confirmPasswordError', 'Passwords do not match');
        } else {
            hideError('confirmPasswordError');
        }
    });

    // Pas de validation en temps réel pour le rôle car il est caché et défini par défaut

    // Validation des conditions d'utilisation
    document.getElementById('registerTerms').addEventListener('change', function() {
        if (!validateTerms(this.checked)) {
            showError('termsError', 'You must accept the terms of use');
        } else {
            hideError('termsError');
        }
    });

    // VALIDATION EN TEMPS RÉEL POUR LA CONNEXION
    
    // Validation de l'email de connexion
    document.getElementById('loginEmail').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showError('loginEmailError', 'Email address is required');
        } else if (!validateEmail(this.value)) {
            showError('loginEmailError', 'Please enter a valid email address');
        } else {
            hideError('loginEmailError');
        }
    });

    // Validation du mot de passe de connexion
    document.getElementById('loginPassword').addEventListener('blur', function() {
        if (this.value === '') {
            showError('loginPasswordError', 'Password is required');
        } else {
            hideError('loginPasswordError');
        }
    });

    // Soumission du formulaire d'inscription
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Forcer la valeur du rôle à "student"
        document.getElementById('registerRole').value = 'student';
        
        // Valider tous les champs
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('registerConfirmPassword').value;
        const role = document.getElementById('registerRole').value; // Toujours "student"
        const termsChecked = document.getElementById('registerTerms').checked;
        
        let isValid = true;
        
        if (!validateName(name)) {
            showError('nameError', 'Please enter a valid name (2-50 characters, letters and spaces only)');
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
        
        // Pas de validation du rôle nécessaire car il est toujours "student"
        
        if (!validateTerms(termsChecked)) {
            showError('termsError', 'You must accept the terms of use');
            isValid = false;
        }
        
        if (isValid) {
            // Soumettre le formulaire si tout est valide
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
                        const loginAlerts = document.getElementById('loginAlerts');
                        loginAlerts.innerHTML = `
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
    
    // Soumission du formulaire de connexion
    document.getElementById('signinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validation côté client
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
    // GESTION DU FORGOT PASSWORD COMBINÉ
    // ============================================

    // Variables globales pour le reset password
    let currentStep = 1;
    let resetEmailValue = '';
    let resetCodeValue = '';
    let timerInterval = null;
    let timeRemaining = 900; // 15 minutes en secondes

    const resetCombinedModal = document.getElementById('resetPasswordCombinedModal');
    const closeResetCombined = document.getElementById('closeResetCombined');

    // Ouvrir la modal depuis "Forgot password"
    document.querySelector('.forgot-password').addEventListener('click', function(e) {
        e.preventDefault();
        authModal.style.display = 'none';
        resetCombinedModal.style.display = 'flex';
        goToStep(1);
        startTimer();
    });

    // Fermer la modal
    closeResetCombined.addEventListener('click', function() {
        closeResetModal();
    });

    resetCombinedModal.addEventListener('click', function(e) {
        if (e.target === resetCombinedModal) {
            closeResetModal();
        }
    });

    function closeResetModal() {
        resetCombinedModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetResetForm();
        stopTimer();
    }

    function resetResetForm() {
        document.getElementById('emailStepForm').reset();
        document.getElementById('codeStepForm').reset();
        document.getElementById('passwordStepForm').reset();
        document.getElementById('resetCombinedAlerts').innerHTML = '';
        resetEmailValue = '';
        resetCodeValue = '';
        currentStep = 1;
        goToStep(1);
        clearAllCodeDigits();
        resetValidation();
    }

    // Retour à la connexion
    document.querySelectorAll('.back-to-login-combined').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeResetModal();
            authModal.style.display = 'flex';
            showLoginForm();
        });
    });

    // Gestion des étapes
    function goToStep(step) {
        currentStep = step;
        
        // Masquer toutes les étapes
        document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.step').forEach(s => {
            s.classList.remove('active');
            s.classList.remove('completed');
        });
        
        // Activer l'étape actuelle
        if (step === 1) {
            document.getElementById('emailStep').classList.add('active');
            document.getElementById('stepIndicator1').classList.add('active');
        } else if (step === 2) {
            document.getElementById('codeStep').classList.add('active');
            document.getElementById('stepIndicator1').classList.add('completed');
            document.getElementById('stepIndicator2').classList.add('active');
        } else if (step === 3) {
            document.getElementById('passwordStep').classList.add('active');
            document.getElementById('stepIndicator1').classList.add('completed');
            document.getElementById('stepIndicator2').classList.add('completed');
            document.getElementById('stepIndicator3').classList.add('active');
        }
    }

    // Timer pour le code
    function startTimer() {
        timeRemaining = 900; // 15 minutes
        updateTimerDisplay();
        
        timerInterval = setInterval(() => {
            timeRemaining--;
            updateTimerDisplay();
            
            if (timeRemaining <= 0) {
                stopTimer();
                showAlert('resetCombinedAlerts', 'danger', 'Verification code has expired. Please request a new one.');
                goToStep(1);
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        const timerElement = document.getElementById('codeTimer');
        if (timerElement) {
            timerElement.textContent = display;
            
            // Changer la couleur si moins de 2 minutes
            if (timeRemaining < 120) {
                timerElement.style.color = 'var(--error-red)';
            } else {
                timerElement.style.color = 'var(--primary-violet)';
            }
        }
    }

    // ÉTAPE 1: Envoi de l'email
    document.getElementById('emailStepForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('resetEmailInput').value.trim();
        
        if (!validateEmail(email)) {
            showError('resetEmailInputError', 'Please enter a valid email address');
            return;
        }
        
        hideError('resetEmailInputError');
        
        const formData = new FormData();
        formData.append('action', 'forgot_password');
        formData.append('email', email);
        
        const submitBtn = this.querySelector('.auth-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Sending Code...';
        submitBtn.disabled = true;
        
        fetch('../../controller/UserController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resetEmailValue = email;
                showAlert('resetCombinedAlerts', 'success', data.message);
                
                setTimeout(() => {
                    document.getElementById('resetCombinedAlerts').innerHTML = '';
                    goToStep(2);
                    document.getElementById('digit1').focus();
                }, 2000);
            } else {
                showAlert('resetCombinedAlerts', 'danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('resetCombinedAlerts', 'danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // ÉTAPE 2: Gestion des champs de code
    const codeDigits = document.querySelectorAll('.code-digit');
    
    codeDigits.forEach((digit, index) => {
        // Auto-focus sur le champ suivant
        digit.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length === 1 && index < codeDigits.length - 1) {
                codeDigits[index + 1].focus();
            }
            
            updateFullCode();
        });
        
        // Navigation avec les touches
        digit.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                codeDigits[index - 1].focus();
            }
            
            if (e.key === 'ArrowLeft' && index > 0) {
                codeDigits[index - 1].focus();
            }
            
            if (e.key === 'ArrowRight' && index < codeDigits.length - 1) {
                codeDigits[index + 1].focus();
            }
        });
        
        // Paste handling
        digit.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
            
            if (pasteData.length === 6) {
                for (let i = 0; i < 6; i++) {
                    codeDigits[i].value = pasteData[i];
                }
                codeDigits[5].focus();
                updateFullCode();
            }
        });
    });

    function updateFullCode() {
        let code = '';
        codeDigits.forEach(digit => {
            code += digit.value;
        });
        document.getElementById('fullCodeInput').value = code;
        
        // Auto-submit si 6 chiffres
        if (code.length === 6) {
            document.getElementById('codeStepForm').dispatchEvent(new Event('submit'));
        }
    }

    function clearAllCodeDigits() {
        codeDigits.forEach(digit => {
            digit.value = '';
        });
        document.getElementById('fullCodeInput').value = '';
    }

    // Vérification du code
    document.getElementById('codeStepForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const code = document.getElementById('fullCodeInput').value;
        
        if (code.length !== 6) {
            showError('codeStepError', 'Please enter a complete 6-digit code');
            return;
        }
        
        hideError('codeStepError');
        
        const formData = new FormData();
        formData.append('action', 'verify_code');
        formData.append('email', resetEmailValue);
        formData.append('code', code);
        
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
            if (data.success) {
                resetCodeValue = code;
                stopTimer();
                showAlert('resetCombinedAlerts', 'success', data.message);
                
                setTimeout(() => {
                    document.getElementById('resetCombinedAlerts').innerHTML = '';
                    goToStep(3);
                    document.getElementById('finalNewPassword').focus();
                }, 1500);
            } else {
                showAlert('resetCombinedAlerts', 'danger', data.message);
                clearAllCodeDigits();
                document.getElementById('digit1').focus();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('resetCombinedAlerts', 'danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Resend code
    document.querySelector('.resend-code-combined').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!resetEmailValue) {
            showAlert('resetCombinedAlerts', 'danger', 'Please start from the beginning.');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'forgot_password');
        formData.append('email', resetEmailValue);
        
        fetch('../../controller/UserController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('resetCombinedAlerts', 'success', 'A new code has been sent to your email.');
                clearAllCodeDigits();
                startTimer();
                document.getElementById('digit1').focus();
            } else {
                showAlert('resetCombinedAlerts', 'danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // ÉTAPE 3: Password strength indicator
    document.getElementById('finalNewPassword').addEventListener('input', function() {
        const password = this.value;
        const strengthContainer = document.getElementById('passwordStrength');
        const strengthText = strengthContainer.querySelector('.strength-text');
        
        strengthContainer.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
        
        if (password.length === 0) {
            strengthText.textContent = '';
            return;
        }
        
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        if (strength <= 2) {
            strengthContainer.classList.add('strength-weak');
            strengthText.textContent = 'Weak password';
            strengthText.style.color = 'var(--error-red)';
        } else if (strength <= 4) {
            strengthContainer.classList.add('strength-medium');
            strengthText.textContent = 'Medium password';
            strengthText.style.color = '#fbbf24';
        } else {
            strengthContainer.classList.add('strength-strong');
            strengthText.textContent = 'Strong password';
            strengthText.style.color = 'var(--success-green)';
        }
    });

    // Réinitialisation finale du mot de passe
    document.getElementById('passwordStepForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('finalNewPassword').value;
        const confirmPassword = document.getElementById('finalConfirmPassword').value;
        
        let isValid = true;
        
        if (!validatePassword(newPassword)) {
            showError('finalNewPasswordError', 'Password must be at least 6 characters long');
            isValid = false;
        } else {
            hideError('finalNewPasswordError');
        }
        
        if (newPassword !== confirmPassword) {
            showError('finalConfirmPasswordError', 'Passwords do not match');
            isValid = false;
        } else {
            hideError('finalConfirmPasswordError');
        }
        
        if (!isValid) return;
        
        const formData = new FormData();
        formData.append('action', 'reset_password');
        formData.append('email', resetEmailValue);
        formData.append('code', resetCodeValue);
        formData.append('new_password', newPassword);
        formData.append('confirm_password', confirmPassword);
        
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
            if (data.success) {
                showAlert('resetCombinedAlerts', 'success', data.message);
                
                setTimeout(() => {
                    closeResetModal();
                    authModal.style.display = 'flex';
                    showLoginForm();
                    
                    const loginAlerts = document.getElementById('loginAlerts');
                    loginAlerts.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>Password reset successfully! Please sign in with your new password.</span>
                        </div>
                    `;
                }, 2000);
            } else {
                showAlert('resetCombinedAlerts', 'danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('resetCombinedAlerts', 'danger', 'An error occurred. Please try again.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Fonction helper pour afficher les alertes
    function showAlert(containerId, type, message) {
        const container = document.getElementById(containerId);
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        container.innerHTML = `
            <div class="alert alert-${type}">
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            </div>
        `;
    }
});
</script>

</body>
</html>