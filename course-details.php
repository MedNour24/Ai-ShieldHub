<?php
// course-details.php
include_once 'config/Database.php';
include_once 'config/StripeConfig.php';  // Add this line for Stripe
include_once 'model/Course.php';
include_once 'model/Purchase.php';
include_once 'model/CreditCardVerification.php';

// Initialisation base de données
$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$purchase = new Purchase($db);

// Récupérer l'ID du cours depuis l'URL
$course_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Course ID not found.');

// Lire les données du cours
$course->id = $course_id;
if (!$course->readOne()) {
    die('ERROR: Course not found.');
}

// Pour l'exemple, utilisateur fixe - dans un vrai système, viendrait de la session
$user_id = 1;

// Vérifier si l'utilisateur a déjà acheté ce cours (seulement pour les cours payants)
$has_purchased = false;
if ($course->license_type === 'paid') {
    $has_purchased = $purchase->userHasPurchased($user_id, $course_id);
}

// Check for one-time discount applied to this course
$discount = null;
$discounted_price = null;
try {
    $dq = $db->prepare('SELECT * FROM course_discounts WHERE course_id = :cid LIMIT 1');
    $dq->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $dq->execute();
    $discount = $dq->fetch(PDO::FETCH_ASSOC);
    if ($discount) {
        $percent = floatval($discount['percent']);
        $discounted_price = round(floatval($course->price) * (1 - ($percent/100)), 2);
    }
} catch (Exception $e) {
    // ignore, non-fatal
}

// Check completion state for this user/course
$is_completed = false;
try {
    $cq = $db->prepare('SELECT id FROM course_completions WHERE user_id = :uid AND course_id = :cid LIMIT 1');
    $cq->bindValue(':uid', $user_id, PDO::PARAM_INT);
    $cq->bindValue(':cid', $course_id, PDO::PARAM_INT);
    $cq->execute();
    $is_completed = $cq->rowCount() > 0;
} catch (Exception $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course->title); ?> - AI ShieldHub</title>
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
            color: var(--light);
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

        /* (theme toggle removed) */
        
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

        .btn-success-custom {
            background: linear-gradient(135deg, var(--success), #34d399);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
            color: white;
        }
        
        /* Course Details Styles */
        .course-details-section {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(10px);
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px 20px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--light);
        }

        .course-header {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .course-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .action-section {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .module-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .module-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .module-item:hover {
            background: rgba(99, 102, 241, 0.05);
            border-color: var(--primary);
            transform: translateX(5px);
        }

        .module-header h5 {
            color: white;
            margin-bottom: 5px;
        }

        .text-muted {
            color: var(--light) !important;
            opacity: 0.7;
        }

        .purchase-status {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Payment Form Styles with Stripe */
        .payment-form .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--light);
            padding: 12px 15px;
        }

        .payment-form .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            color: var(--light);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .payment-form .form-label {
            color: var(--light);
            font-weight: 500;
            margin-bottom: 8px;
        }

        /* Stripe Card Element Styling */
        .StripeElement {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 0.375rem;
            padding: 12px 15px;
            color: var(--light);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .StripeElement--focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .StripeElement--invalid {
            border-color: #ff6b6b;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }

        .invalid-feedback {
            display: none;
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }

        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #ff6b6b;
        }

        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: var(--success);
        }

        .payment-summary {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .payment-processing {
            text-align: center;
            padding: 20px;
        }

        .payment-processing .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* 3D Secure Modal */
        #threeDSecureModal .modal-content {
            background: var(--darker);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--light);
        }

        #threeDSecureModal .modal-header {
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
        }

        #threeDSecureModal .modal-footer {
            border-top: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Stripe Test Card Info */
        .stripe-test-info {
            background: rgba(99, 102, 241, 0.1);
            border-left: 4px solid var(--primary);
            border-radius: 8px;
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
            .course-meta-grid {
                grid-template-columns: 1fr;
            }
            
            .action-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-section .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .course-header {
                padding: 25px 20px;
            }
        }
    </style>
        <link rel="stylesheet" href="assets/css/chatbot.css">
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
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="community.html">Community</a></li>
                    <li class="nav-item"><a class="nav-link" href="tools.html">Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <!-- Course Details Section -->
    <section class="course-details-section" style="padding: 140px 0 80px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Courses</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course->title); ?></li>
                        </ol>
                    </nav>

                    <!-- Purchase Status (for paid courses) -->
                    <?php if ($course->license_type === 'paid' && $has_purchased): ?>
                    <div class="purchase-status">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="text-success mb-1">Course Purchased</h5>
                                <p class="text-light mb-0">You have full access to this course content</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Course Header -->
                    <div class="course-header mb-5">
                        <span class="badge <?php echo $course->license_type === 'free' ? 'bg-info' : 'bg-primary'; ?> px-3 py-2 mb-3">
                            <?php echo $course->license_type === 'free' ? 'FREE' : 'PREMIUM'; ?>
                        </span>
                        <h1 class="text-white fw-bold mb-3"><?php echo htmlspecialchars($course->title); ?></h1>
                        <p class="lead text-light mb-4"><?php echo htmlspecialchars($course->description); ?></p>
                        
                        <div class="course-meta-grid mb-4">
                            <div class="meta-item">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <span class="text-light"><?php echo $course->duration; ?> hours</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <span class="text-light">Created: <?php echo date('F j, Y', strtotime($course->created_at)); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-tag text-primary me-2"></i>
                                <span class="text-light"><?php echo ucfirst($course->license_type); ?> Course</span>
                            </div>
                            <?php if ($course->license_type === 'paid'): ?>
                            <div class="meta-item">
                                <i class="fas fa-dollar-sign text-primary me-2"></i>
                                <?php if ($discounted_price !== null): ?>
                                    <span class="text-light"><s>$<?php echo number_format($course->price, 2); ?></s> <strong style="color:#34d399;">$<?php echo number_format($discounted_price, 2); ?></strong></span>
                                <?php else: ?>
                                    <span class="text-light">$<?php echo number_format($course->price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="action-section">
                            <?php if ($course->license_type === 'free'): ?>
                                <!-- Free Course - Always accessible -->
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <button class="btn btn-primary-custom btn-lg me-3" onclick="startCourse(<?php echo $course->id; ?>)">
                                        <i class="fas fa-play me-2"></i>Start Course
                                    </button>
                                    <?php if ($is_completed): ?>
                                        <button id="completeBtn" class="btn btn-sm btn-success" title="Mark as not completed">Completed ✓</button>
                                    <?php else: ?>
                                        <button id="completeBtn" class="btn btn-sm btn-outline-success">Mark as complete</button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Paid Course - Check purchase status -->
                                <?php if ($has_purchased): ?>
                                    <!-- Already purchased -->
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <button class="btn btn-primary-custom btn-lg me-3" onclick="startCourse(<?php echo $course->id; ?>)">
                                            <i class="fas fa-play me-2"></i>Continue Learning
                                        </button>
                                        <?php if ($is_completed): ?>
                                            <button id="completeBtn" class="btn btn-sm btn-success" title="Mark as not completed">Completed ✓</button>
                                        <?php else: ?>
                                            <button id="completeBtn" class="btn btn-sm btn-outline-success">Mark as complete</button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Not purchased yet -->
                                    <div style="display:flex;gap:12px;align-items:center;">
                                        <button class="btn btn-success-custom btn-lg me-3" id="purchaseBtn" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                            <i class="fas fa-shopping-cart me-2"></i>Purchase Course - <?php echo ($discounted_price !== null) ? '$' . number_format($discounted_price, 2) : '$' . number_format($course->price, 2); ?>
                                        </button>
                                        <div style="display:flex;gap:8px;align-items:center;">
                                            <?php if ($discounted_price === null): ?>
                                                <input id="discountCodeInput" class="form-control form-control-sm" style="width:160px;background:rgba(255,255,255,0.04);color:var(--light);" placeholder="Discount code" />
                                                <button id="applyDiscountBtn" class="btn btn-outline-light btn-sm">Apply</button>
                                            <?php else: ?>
                                                <div style="color:#cfe0ff;font-size:14px;">Discount applied: <strong style="color:#34d399;margin-left:6px;"><?php echo htmlspecialchars($discount['code']); ?></strong></div>
                                                <?php if ($user_id === 1): ?>
                                                    <button id="undoDiscountBtn" class="btn btn-sm btn-warning" style="margin-left:8px;">Undo</button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-light btn-lg" onclick="window.history.back()">
                                <i class="fas fa-arrow-left me-2"></i>Back to Courses
                            </button>
                        </div>

                        <!-- Loading indicator (hidden by default) -->
                        <div id="purchaseLoading" class="mt-3 text-center" style="display: none;">
                            <div class="loading-spinner me-2"></div>
                            <span class="text-light">Processing purchase...</span>
                        </div>
                    </div>

                    <!-- Course Content -->
                    <div class="course-content">
                        <div class="content-card">
                            <h3 class="text-white mb-4"><i class="fas fa-list-alt me-2"></i>Course Content</h3>
                            
                            <div class="module-list">
                                <div class="module-item">
                                    <div class="module-header">
                                        <h5 class="text-white mb-2">Introduction to Cybersecurity</h5>
                                        <span class="text-muted">3 lessons • 45 minutes</span>
                                    </div>
                                    <?php if ($course->license_type === 'paid' && !$has_purchased): ?>
                                    <div class="module-locked mt-2">
                                        <small class="text-warning"><i class="fas fa-lock me-1"></i>Purchase required</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="module-item">
                                    <div class="module-header">
                                        <h5 class="text-white mb-2">Core Concepts</h5>
                                        <span class="text-muted">5 lessons • 1.5 hours</span>
                                    </div>
                                    <?php if ($course->license_type === 'paid' && !$has_purchased): ?>
                                    <div class="module-locked mt-2">
                                        <small class="text-warning"><i class="fas fa-lock me-1"></i>Purchase required</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="module-item">
                                    <div class="module-header">
                                        <h5 class="text-white mb-2">Practical Applications</h5>
                                        <span class="text-muted">4 lessons • 2 hours</span>
                                    </div>
                                    <?php if ($course->license_type === 'paid' && !$has_purchased): ?>
                                    <div class="module-locked mt-2">
                                        <small class="text-warning"><i class="fas fa-lock me-1"></i>Purchase required</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="module-item">
                                    <div class="module-header">
                                        <h5 class="text-white mb-2">Advanced Techniques</h5>
                                        <span class="text-muted">6 lessons • 3 hours</span>
                                    </div>
                                    <?php if ($course->license_type === 'paid' && !$has_purchased): ?>
                                    <div class="module-locked mt-2">
                                        <small class="text-warning"><i class="fas fa-lock me-1"></i>Purchase required</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="content-card mt-4">
                            <h3 class="text-white mb-3"><i class="fas fa-list-check me-2"></i>Requirements</h3>
                            <ul class="text-light">
                                <li>Basic computer knowledge</li>
                                <li>Internet connection</li>
                                <li>No prior cybersecurity experience required</li>
                                <li>Dedication to learn and practice</li>
                            </ul>
                        </div>

                        <!-- What You'll Learn -->
                        <div class="content-card mt-4">
                            <h3 class="text-white mb-3"><i class="fas fa-graduation-cap me-2"></i>What You'll Learn</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="text-light">
                                        <li>Fundamental cybersecurity concepts</li>
                                        <li>Network security principles</li>
                                        <li>Threat identification and prevention</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="text-light">
                                        <li>Security best practices</li>
                                        <li>Risk assessment techniques</li>
                                        <li>Real-world security scenarios</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Modal with Stripe Elements -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: var(--darker); border: 1px solid rgba(99, 102, 241, 0.3);">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="paymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Complete Your Purchase
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Payment Summary -->
                        <div class="payment-summary">
                        <h6 class="text-white mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-light">Course:</span>
                            <span class="text-white fw-bold"><?php echo htmlspecialchars($course->title); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-light">Total Amount:</span>
                                <span class="text-success fw-bold fs-5">
                                    <?php if ($discounted_price !== null): ?>
                                        $<?php echo number_format($discounted_price, 2); ?>
                                    <?php else: ?>
                                        $<?php echo number_format($course->price, 2); ?>
                                    <?php endif; ?>
                                </span>
                        </div>
                    </div>

                    <!-- Payment Processing Indicator -->
                    <div id="paymentProcessing" class="payment-processing" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Processing payment...</span>
                        </div>
                        <h5 class="text-white">Processing Payment</h5>
                        <p class="text-light">Please wait while we verify your payment information...</p>
                    </div>

                    <!-- Payment Form with Stripe Elements -->
                    <form id="paymentForm" class="payment-form">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-white mb-3">Payment Information</h6>
                            </div>
                            
                            <!-- Stripe Card Element -->
                            <div class="col-12 mb-3">
                                <label for="card-element" class="form-label">Card Details</label>
                                <div id="card-element" class="form-control" style="height: 40px; padding: 10px;"></div>
                                <div id="card-errors" role="alert" class="text-danger mt-2" style="font-size: 0.875rem;"></div>
                            </div>

                            <!-- Cardholder Name -->
                            <div class="col-12 mb-3">
                                <label for="cardholderName" class="form-label">Cardholder Name</label>
                                <input type="text" class="form-control" id="cardholderName" 
                                       placeholder="John Doe" 
                                       required>
                                <div class="invalid-feedback" id="cardholderNameError">
                                    Please enter the cardholder name
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-12 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" 
                                       placeholder="your@email.com" 
                                       required>
                                <div class="invalid-feedback" id="emailError">
                                    Please enter a valid email address
                                </div>
                            </div>

                            <!-- Billing Address -->
                            <div class="col-12 mb-3">
                                <label for="billingAddress" class="form-label">Billing Address</label>
                                <input type="text" class="form-control" id="billingAddress" 
                                       placeholder="123 Main St, City, Country" 
                                       required>
                                <div class="invalid-feedback" id="billingAddressError">
                                    Please enter your billing address
                                </div>
                            </div>

                            <!-- Test Info for Stripe -->
                            <div class="col-12 mb-4">
                                <div class="alert alert-info stripe-test-info">
                                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Test with Stripe</h6>
                                    <p class="small mb-1">Use these test card numbers:</p>
                                    <ul class="small mb-0">
                                        <li><strong>Successful payment:</strong> 4242 4242 4242 4242</li>
                                        <li><strong>Requires 3D Secure:</strong> 4000 0025 0000 3155</li>
                                        <li><strong>Declined card:</strong> 4000 0000 0000 0002</li>
                                        <li><strong>CVC:</strong> Any 3 digits</li>
                                        <li><strong>Date:</strong> Any future date</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success-custom btn-lg" id="submitPayment">
                                <i class="fas fa-lock me-2"></i>Pay $<?php echo number_format($course->price, 2); ?>
                            </button>
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 3D Secure Modal -->
    <div class="modal fade" id="threeDSecureModal" tabindex="-1" aria-labelledby="threeDSecureModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="threeDSecureModalLabel">
                        <i class="fas fa-shield-alt me-2"></i>3D Secure Authentication Required
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="text-white mb-3">Complete Authentication</h5>
                    <p class="text-light">Please complete the 3D Secure authentication in the popup window to proceed with your payment.</p>
                    <p class="text-muted small">If no popup appears, check your browser's popup blocker settings.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cancel3DSecure()">
                        <i class="fas fa-times me-2"></i>Cancel Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                    <a href="index.php">Courses</a>
                    <a href="community.html">Community</a>
                    <a href="tools.html">Tools</a>
                    <a href="pricing.html">Pricing</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <a href="blog.html">Blog</a>
                    <a href="docs.html">Documentation</a>
                    <a href="faq.html">FAQ</a>
                    <a href="support.html">Support</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Company</h5>
                    <a href="about.html">About Us</a>
                    <a href="contact.html">Contact</a>
                    <a href="careers.html">Careers</a>
                    <a href="press.html">Press Kit</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <a href="privacy.html">Privacy Policy</a>
                    <a href="terms.html">Terms of Service</a>
                    <a href="cookies.html">Cookie Policy</a>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">© 2023 AI ShieldHub. All rights reserved. Built with <i class="fas fa-heart" style="color: var(--accent);"></i> for student safety.</p>
                <a href="admin.php" style="color: var(--light);">Admin Dashboard</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Stripe.js Library -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <script>
        // Configuration
        const stripePublishableKey = '<?php echo StripeConfig::getPublishableKey(); ?>';
        const stripe = Stripe(stripePublishableKey);
        const userId = <?php echo $user_id; ?>;
        const courseId = <?php echo $course->id; ?>;
        const coursePrice = <?php echo $course->price; ?>;
        const isFreeCourse = <?php echo $course->license_type === 'free' ? 'true' : 'false'; ?>;
        const hasPurchased = <?php echo $has_purchased ? 'true' : 'false'; ?>;
        
        let elements;
        let cardElement;
        let paymentIntentClientSecret;
        let currentPaymentIntentId;
        
        // Initialize when document is ready
        $(document).ready(function() {
            console.log('Course Details Loaded:', {
                courseId: courseId,
                userId: userId,
                isFreeCourse: isFreeCourse,
                hasPurchased: hasPurchased,
                price: coursePrice,
                stripeKey: stripePublishableKey.substring(0, 20) + '...'
            });

            // Initialize Stripe Elements when modal opens
            $('#paymentModal').on('show.bs.modal', function() {
                initializeStripeElements();
            });

            // Reset form when modal is closed
            $('#paymentModal').on('hidden.bs.modal', function() {
                resetPaymentForm();
                if (cardElement) {
                    cardElement.unmount();
                    cardElement = null;
                }
            });

            // If paid course is already purchased, ensure UI is correct
            if (!isFreeCourse && hasPurchased) {
                updatePurchaseUI(courseId);
            }

            // Handle close alerts
            $(document).on('click', '.alert .btn-close', function() {
                $(this).closest('.alert').remove();
            });

            // Theme toggle removed
        });

        async function initializeStripeElements() {
            try {
                // Create or re-create elements
                if (elements) {
                    elements.unmount();
                }
                
                elements = stripe.elements();
                
                // Create card element
                cardElement = elements.create('card', {
                    style: {
                        base: {
                            color: '#ffffff',
                            fontFamily: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
                            fontSize: '16px',
                            '::placeholder': {
                                color: '#a0aec0'
                            }
                        },
                        invalid: {
                            color: '#ff6b6b',
                            iconColor: '#ff6b6b'
                        }
                    },
                    hidePostalCode: true
                });
                
                // Mount card element
                cardElement.mount('#card-element');
                
                // Add real-time validation
                cardElement.on('change', function(event) {
                    const displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
                
                // Create Payment Intent
                await createPaymentIntent();
                
            } catch (error) {
                console.error('Error initializing Stripe Elements:', error);
                showPaymentError('Failed to initialize payment system. Please refresh the page.');
            }
        }
        
        async function createPaymentIntent() {
            try {
                const response = await fetch('controller/PaymentController.php?action=create-payment-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        user_id: userId,
                        amount: coursePrice
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    paymentIntentClientSecret = data.data.client_secret;
                    currentPaymentIntentId = data.data.payment_intent_id;
                    console.log('Payment Intent created:', currentPaymentIntentId);
                } else {
                    console.error('Failed to create payment intent:', data.message);
                    showPaymentError('Failed to initialize payment: ' + data.message);
                }
            } catch (error) {
                console.error('Error creating payment intent:', error);
                showPaymentError('Network error. Please try again.');
            }
        }
        
        async function processPaymentForm(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            // Show processing indicator
            document.getElementById('paymentProcessing').style.display = 'block';
            document.getElementById('paymentForm').style.display = 'none';
            document.getElementById('submitPayment').disabled = true;
            
            try {
                // Create payment method from card element
                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: document.getElementById('cardholderName').value,
                        email: document.getElementById('email').value,
                        address: {
                            line1: document.getElementById('billingAddress').value
                        }
                    }
                });
                
                if (error) {
                    throw new Error(error.message);
                }
                
                // Prepare payment data
                const paymentData = {
                    course_id: courseId,
                    user_id: userId,
                    amount: coursePrice,
                    payment_method_id: paymentMethod.id,
                    cardholder_name: document.getElementById('cardholderName').value,
                    email: document.getElementById('email').value,
                    billing_address: document.getElementById('billingAddress').value,
                    card_brand: paymentMethod.card.brand,
                    last4: paymentMethod.card.last4
                };
                
                // Process payment
                await processPayment(paymentData);
                
            } catch (error) {
                showPaymentError(error.message);
                resetPaymentUI();
            }
        }
        
        async function processPayment(paymentData) {
            try {
                const response = await fetch('controller/PaymentController.php?action=process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(paymentData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.data.requires_action) {
                        // Handle 3D Secure authentication
                        await handle3DSecure(data.data.client_secret);
                    } else {
                        // Payment succeeded
                        showPaymentSuccess(data.data);
                        completePurchase(data.data.transaction_id);
                        closePaymentModal();
                    }
                } else {
                    throw new Error(data.message || 'Payment failed');
                }
            } catch (error) {
                throw error;
            }
        }
        
        async function handle3DSecure(clientSecret) {
            try {
                // Show 3D Secure modal
                const threeDSecureModal = new bootstrap.Modal(document.getElementById('threeDSecureModal'));
                threeDSecureModal.show();
                
                const { error, paymentIntent } = await stripe.handleCardAction(clientSecret);
                
                // Hide 3D Secure modal
                threeDSecureModal.hide();
                
                if (error) {
                    throw new Error(`3D Secure failed: ${error.message}`);
                }
                
                if (paymentIntent.status === 'succeeded') {
                    // Confirm the payment on our server
                    const confirmResponse = await fetch('controller/PaymentController.php?action=process', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id,
                            course_id: courseId,
                            user_id: userId,
                            amount: coursePrice
                        })
                    });
                    
                    const confirmData = await confirmResponse.json();
                    
                    if (confirmData.success) {
                        showPaymentSuccess(confirmData.data);
                        completePurchase(confirmData.data.transaction_id);
                        closePaymentModal();
                    } else {
                        throw new Error('Payment confirmation failed');
                    }
                } else {
                    throw new Error('3D Secure authentication failed');
                }
            } catch (error) {
                // Hide 3D Secure modal on error
                const threeDSecureModalEl = document.getElementById('threeDSecureModal');
                const modal = bootstrap.Modal.getInstance(threeDSecureModalEl);
                if (modal) {
                    modal.hide();
                }
                throw error;
            }
        }
        
        function cancel3DSecure() {
            const threeDSecureModal = new bootstrap.Modal(document.getElementById('threeDSecureModal'));
            threeDSecureModal.hide();
            resetPaymentUI();
            showPaymentError('Payment cancelled by user.');
        }
        
        function validateForm() {
            let isValid = true;
            
            // Clear previous errors
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            
            // Validate cardholder name
            const cardholderName = document.getElementById('cardholderName').value.trim();
            if (!cardholderName || cardholderName.length < 2) {
                showFieldError('cardholderName', 'Please enter cardholder name');
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email');
                isValid = false;
            }
            
            // Validate billing address
            const billingAddress = document.getElementById('billingAddress').value.trim();
            if (!billingAddress || billingAddress.length < 10) {
                showFieldError('billingAddress', 'Please enter complete billing address');
                isValid = false;
            }
            
            return isValid;
        }
        
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId + 'Error');
            
            if (field && errorElement) {
                field.classList.add('is-invalid');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
        }
        
        function showPaymentSuccess(paymentData) {
            const successAlert = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Payment Successful!</strong><br>
                    Transaction ID: ${paymentData.transaction_id}<br>
                    Amount: $${paymentData.amount}<br>
                    ${paymentData.card_brand ? `Card: ${paymentData.card_brand} ending in ${paymentData.card_last4}<br>` : ''}
                    Status: ${paymentData.status}<br>
                    <small class="text-muted">Processed by Stripe</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('.course-header').prepend(successAlert);
        }
        
        function showPaymentError(message) {
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Payment Failed!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('.course-header').prepend(errorAlert);
        }
        
        function resetPaymentUI() {
            document.getElementById('paymentProcessing').style.display = 'none';
            document.getElementById('paymentForm').style.display = 'block';
            document.getElementById('submitPayment').disabled = false;
        }
        
        function resetPaymentForm() {
            document.getElementById('paymentForm').reset();
            document.getElementById('paymentForm').classList.remove('was-validated');
            document.getElementById('paymentProcessing').style.display = 'none';
            document.getElementById('paymentForm').style.display = 'block';
            document.getElementById('submitPayment').disabled = false;
            
            // Clear card errors
            document.getElementById('card-errors').textContent = '';
            
            // Remove validation classes
            const formControls = document.querySelectorAll('#paymentForm .form-control');
            formControls.forEach(control => {
                control.classList.remove('is-valid', 'is-invalid');
            });
            
            // Hide error messages
            const errorMessages = document.querySelectorAll('#paymentForm .invalid-feedback');
            errorMessages.forEach(error => {
                error.style.display = 'none';
            });
        }
        
        function closePaymentModal() {
            const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            if (paymentModal) {
                paymentModal.hide();
            }
        }
        
        function startCourse(courseId) {
            if (isFreeCourse || hasPurchased) {
                // Access allowed — redirect to course player
                window.location.href = 'course-player.php?id=' + courseId;
            } else {
                // Access denied - should purchase first
                alert('Please purchase this course to access the content.');
            }
        }
        
        function completePurchase(transactionId) {
            // Show loading indicator
            $('#purchaseLoading').show();

            // Send purchase request
            $.ajax({
                url: 'controller/CourseController.php',
                type: 'POST',
                data: {
                    action: 'purchase',
                    course_id: courseId,
                    user_id: userId,
                    payment_method: 'stripe',
                    transaction_id: transactionId
                },
                dataType: 'json',
                success: function(response) {
                    $('#purchaseLoading').hide();
                    
                    if (response.success) {
                        // Purchase successful
                        updatePurchaseUI(courseId);
                    } else {
                        // Purchase error
                        showPurchaseError(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#purchaseLoading').hide();
                    showPurchaseError('Network error. Please try again.');
                }
            });
        }
        
        function updatePurchaseUI(courseId) {
            // Change purchase button to start button
            $('#purchaseBtn')
                .removeClass('btn-success-custom')
                .addClass('btn-primary-custom')
                .html('<i class="fas fa-play me-2"></i>Start Course Now')
                .attr('onclick', `startCourse(${courseId})`)
                .attr('data-bs-toggle', '')
                .attr('data-bs-target', '');

            // Remove module locks
            $('.module-locked').each(function() {
                $(this).html('<small class="text-success"><i class="fas fa-unlock me-1"></i>Access granted</small>');
            });

            // Update purchase status
            window.hasPurchased = true;
        }
        
        function showPurchaseError(message) {
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Purchase Failed!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('.course-header').prepend(errorAlert);
        }
        
        // Update form submission handler
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            processPaymentForm(e);
        });
        
        // Check purchase status periodically (every 30 seconds)
        if (!isFreeCourse && !hasPurchased) {
            setInterval(checkPurchaseStatus, 30000);
        }
        
        // Discount apply/undo handlers
        $(document).on('click', '#applyDiscountBtn', function(){
            const code = $('#discountCodeInput').val().trim();
            if (!code) {
                alert('Please enter a discount code');
                return;
            }
            $(this).prop('disabled', true).text('Applying...');
            fetch('api/apply-discount.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ course_id: courseId, code: code })
            }).then(r => r.json()).then(resp => {
                if (resp.success) {
                    // simple: reload to reflect discounted price
                    location.reload();
                } else {
                    alert(resp.message || 'Failed to apply discount');
                    $('#applyDiscountBtn').prop('disabled', false).text('Apply');
                }
            }).catch(err => {
                console.error(err);
                alert('Network error applying discount');
                $('#applyDiscountBtn').prop('disabled', false).text('Apply');
            });
        });

        $(document).on('click', '#undoDiscountBtn', function(){
            if (!confirm('Undo discount for this course?')) return;
            $(this).prop('disabled', true).text('Removing...');
            fetch('api/undo-discount.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ course_id: courseId })
            }).then(r => r.json()).then(resp => {
                if (resp.success) {
                    location.reload();
                } else {
                    alert(resp.message || 'Failed to remove discount');
                    $('#undoDiscountBtn').prop('disabled', false).text('Undo');
                }
            }).catch(err => {
                console.error(err);
                alert('Network error');
                $('#undoDiscountBtn').prop('disabled', false).text('Undo');
            });
        });

        // Completion toggle handler
        $(document).on('click', '#completeBtn', function(){
            const btn = $(this);
            const currentlyCompleted = btn.hasClass('btn-success');
            btn.prop('disabled', true);
            const action = currentlyCompleted ? 'undo' : 'complete';
            btn.text(currentlyCompleted ? 'Removing...' : 'Saving...');

            fetch('api/complete-course.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ user_id: userId, course_id: courseId, action: action })
            }).then(r => r.json()).then(resp => {
                if (resp.success) {
                    if (action === 'complete') {
                        btn.removeClass('btn-outline-success').addClass('btn-success').text('Completed ✓').attr('title','Mark as not completed');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-outline-success').text('Mark as complete').attr('title','Mark as complete');
                    }
                } else {
                    alert(resp.message || 'Failed to update completion');
                }
                btn.prop('disabled', false);
            }).catch(err => {
                console.error(err);
                alert('Network error');
                btn.prop('disabled', false);
                btn.text(currentlyCompleted ? 'Completed ✓' : 'Mark as complete');
            });
        });
        
        function checkPurchaseStatus() {
            if (isFreeCourse) return;
        
            $.ajax({
                url: 'controller/CourseController.php',
                type: 'GET',
                data: {
                    action: 'check_purchase',
                    course_id: courseId,
                    user_id: userId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.has_purchased && !window.hasPurchased) {
                        // Update UI if purchase was made elsewhere
                        updatePurchaseUI(courseId);
                        showPurchaseSuccess({ 
                            transaction_id: 'SYNC_' + Date.now(),
                            amount: coursePrice,
                            course_title: '<?php echo $course->title; ?>'
                        });
                    }
                }
            });
        }
    </script>
        <script src="assets/js/chatbot.js"></script>
</body>
</html>