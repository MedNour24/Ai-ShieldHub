<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AICyberLearn | Smarter Minds, Safer Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #0f172a;
            --darker: #020617;
            --light: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: var(--darker);
        }
        
        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #1e1b4b 100%);
        }
        
        .animated-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, -50px); }
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.3;
            animation: float 20s infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-100px) translateX(50px); }
            50% { transform: translateY(-200px) translateX(-50px); }
            75% { transform: translateY(-100px) translateX(100px); }
        }
        
        /* Header */
        .navbar {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
            padding: 20px 0;
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--light) !important;
            font-weight: 500;
            padding: 10px 20px !important;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 60%;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 0 50px;
            position: relative;
        }
        
        .hero h6 {
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease;
        }
        
        .hero h1 {
            font-size: 58px;
            font-weight: 900;
            color: white;
            line-height: 1.2;
            margin-bottom: 25px;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero h1 .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        .hero p {
            font-size: 20px;
            color: var(--light);
            margin-bottom: 35px;
            line-height: 1.7;
            animation: fadeInUp 1s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-image {
            position: relative;
            animation: floatImage 6s ease-in-out infinite;
        }
        
        .hero-image img {
            filter: drop-shadow(0 20px 40px rgba(99, 102, 241, 0.3));
        }
        
        @keyframes floatImage {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
        }
        
        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--light);
            font-size: 16px;
            font-weight: 500;
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 70px;
        }
        
        .section-title h6 {
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
        }
        
        .section-title h2 {
            font-size: 48px;
            font-weight: 900;
            color: white;
            margin-bottom: 20px;
        }
        
        .section-title p {
            color: var(--light);
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 40px;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }
        
        .feature-card:hover::before {
            opacity: 0.1;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }
        
        .feature-icon i {
            font-size: 30px;
            color: white;
        }
        
        .feature-card h4 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .feature-card p {
            color: var(--light);
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            position: relative;
        }
        
        .cta-box {
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            border-radius: 30px;
            padding: 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .cta-box h2 {
            color: white;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .cta-box p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            margin-bottom: 35px;
            position: relative;
            z-index: 1;
        }
        
        .btn-white-custom {
            background: white;
            color: var(--primary);
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .btn-white-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }
        
        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 60px 0 30px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .footer-brand {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        footer h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        footer a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
            margin-bottom: 10px;
        }
        
        footer a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transform: translateY(-5px);
            padding-left: 0;
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--light);
        }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 36px; }
            .section-title h2 { font-size: 32px; }
            .cta-box { padding: 40px 20px; }
            .cta-box h2 { font-size: 28px; }
        }
        
        /* Profile Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
            margin: 3% auto;
            padding: 0;
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideDown 0.4s ease;
            backdrop-filter: blur(10px);
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            padding: 30px;
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        
        .close {
            color: var(--light);
            font-size: 32px;
            font-weight: 300;
            cursor: pointer;
            background: none;
            border: none;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close:hover {
            color: var(--accent);
            background: rgba(236, 72, 153, 0.1);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .profile-photo-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .profile-photo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            border: 4px solid rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }
        
        .profile-photo:hover {
            transform: scale(1.05);
            border-color: var(--primary);
        }
        
        .photo-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: 3px solid var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .photo-upload-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.5);
        }
        
        .photo-upload-btn i {
            font-size: 14px;
            color: white;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: var(--light);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-group input::placeholder {
            color: rgba(226, 232, 240, 0.5);
        }
        
        .modal-footer {
            padding: 30px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        .btn-cancel {
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
            padding: 12px 35px;
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            color: var(--accent);
        }
        
        #photoInput {
            display: none;
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="particle" style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.3); top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; background: rgba(139, 92, 246, 0.3); top: 60%; left: 80%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 80px; height: 80px; background: rgba(236, 72, 153, 0.3); top: 80%; left: 20%; animation-delay: 6s;"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="./index.html">
                <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="./index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./courses.html">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="./community.html">Community</a></li>
                    <li class="nav-item"><a class="nav-link" href="./quiz.html">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="./tour.html">Tournoi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fas fa-user" style="font-size: 16px;"></i>
                            </div>
                            <span>John Doe</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(99, 102, 241, 0.3);">
                            <li><a class="dropdown-item" href="#" onclick="openProfileModal(); return false;" style="color: var(--light);"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider" style="border-color: rgba(99, 102, 241, 0.3);"></li>
                            <li><a class="dropdown-item" href="./logout.html" style="color: var(--accent);"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h6><i class="fas fa-rocket me-2"></i>WELCOME TO AI SHIELDHUB</h6>
                    <h1>Smarter Minds,<br><span class="gradient-text">Safer Students</span></h1>
                    <p>Master cybersecurity through AI-powered learning, collaborate with peers, and protect your digital future with intelligent tools.</p>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <div style="width: 100%; height: 400px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(236, 72, 153, 0.2)); border-radius: 20px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                            <i class="fas fa-shield-halved" style="font-size: 200px; color: rgba(99, 102, 241, 0.3);"></i>
                            <div style="position: absolute; width: 100%; height: 100%; top: 0; left: 0;">
                                <i class="fas fa-brain" style="position: absolute; top: 20%; left: 15%; font-size: 40px; color: var(--accent); animation: float 4s ease-in-out infinite;"></i>
                                <i class="fas fa-lock" style="position: absolute; top: 60%; right: 15%; font-size: 40px; color: var(--primary); animation: float 5s ease-in-out infinite;"></i>
                                <i class="fas fa-users" style="position: absolute; bottom: 20%; left: 20%; font-size: 40px; color: var(--secondary); animation: float 6s ease-in-out infinite;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">AI-Powered Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">1M+</div>
                        <div class="stat-label">Passwords Tested</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-title">
                <h6><i class="fas fa-star me-2"></i>POWERFUL FEATURES</h6>
                <h2>Why Choose AI ShieldHub?</h2>
                <p>Experience the future of cybersecurity education with AI-powered personalization and collaborative learning</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h4>AI-Personalized Learning</h4>
                        <p>Adaptive quizzes and courses tailored to your skill level. Our AI analyzes your progress and creates custom learning paths.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Student Community</h4>
                        <p>Share files, insights, and discoveries with peers. Learn from real-world experiences in a collaborative environment.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <h4>Password Strength Tester</h4>
                        <p>AI-powered analysis of password security. Get instant feedback and learn what makes passwords unbreakable.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Progress Tracking</h4>
                        <p>Monitor your learning journey with detailed analytics. See your growth and identify areas for improvement.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>Interactive Courses</h4>
                        <p>Hands-on labs, real-world scenarios, and practical exercises. Learn by doing, not just reading.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h4>Gamified Experience</h4>
                        <p>Earn badges, compete on leaderboards, and unlock achievements as you master cybersecurity concepts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to Become a Cybersecurity Expert?</h2>
                <p>Join thousands of students learning to protect their digital world with AI-powered education</p>
                <a href="./register.html" class="btn-white-custom">
                    Start Your Journey Today <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
                    </div>
                    <p style="color: var(--light); margin-top: 15px;">Empowering students with AI-powered cybersecurity education for a safer digital future.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Platform</h5>
                    <a href="./courses.html">Courses</a>
                    <a href="./community.html">Community</a>
                    <a href="./tools.html">Tools</a>
                    <a href="./pricing.html">Pricing</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <a href="./blog.html">Blog</a>
                    <a href="./docs.html">Documentation</a>
                    <a href="./faq.html">FAQ</a>
                    <a href="./support.html">Support</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Company</h5>
                    <a href="./about.html">About Us</a>
                    <a href="./contact.html">Contact</a>
                    <a href="./careers.html">Careers</a>
                    <a href="./press.html">Press Kit</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <a href="./privacy.html">Privacy Policy</a>
                    <a href="./terms.html">Terms of Service</a>
                    <a href="./cookies.html">Cookie Policy</a>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">© 2023 AI ShieldHub. All rights reserved. Built with <i class="fas fa-heart" style="color: var(--accent);"></i> for student safety.</p>
            </div>
        </div>
    </footer>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
                <button class="close" onclick="closeProfileModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="profile-photo-section">
                    <div class="profile-photo-wrapper">
                        <div class="profile-photo" id="modalProfilePhoto">
                            <i class="fas fa-user"></i>
                        </div>
                        <label for="photoInput" class="photo-upload-btn">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="photoInput" accept="image/*" onchange="handlePhotoUpload(event)">
                    </div>
                    <p style="color: var(--light); font-size: 14px;">Click the camera icon to change your profile photo</p>
                </div>
                
                <form id="profileForm" onsubmit="saveProfile(event)">
                    <div class="form-group">
                        <label for="fullName"><i class="fas fa-user me-2"></i>Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" value="John Doe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="john.doe@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your new password">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeProfileModal()">Cancel</button>
                <button class="btn-save" onclick="document.getElementById('profileForm').requestSubmit()">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile Modal Functions
        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('profileModal');
            if (event.target === modal) {
                closeProfileModal();
            }
        }
        
        // Handle photo upload
        function handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoDiv = document.getElementById('modalProfilePhoto');
                    photoDiv.style.backgroundImage = `url(${e.target.result})`;
                    photoDiv.style.backgroundSize = 'cover';
                    photoDiv.style.backgroundPosition = 'center';
                    photoDiv.innerHTML = '';
                    
                    // Update navbar profile photo too
                    const navProfileDiv = document.querySelector('.nav-link.dropdown-toggle > div');
                    if (navProfileDiv) {
                        navProfileDiv.style.backgroundImage = `url(${e.target.result})`;
                        navProfileDiv.style.backgroundSize = 'cover';
                        navProfileDiv.style.backgroundPosition = 'center';
                        navProfileDiv.innerHTML = '';
                    }
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Save profile
        function saveProfile(event) {
            event.preventDefault();
            
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validation
            if (password && password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password && password.length < 8) {
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            // Update navbar name
            const navName = document.querySelector('.nav-link.dropdown-toggle span');
            if (navName) {
                navName.textContent = fullName;
            }
            
            // Show success message
            alert('Profile updated successfully!');
            closeProfileModal();
            
            // Here you would normally send the data to your server
            console.log('Profile data:', { fullName, email, password: password ? '***' : 'unchanged' });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProfileModal();
            }
        });
    </script>
</body>
</html>