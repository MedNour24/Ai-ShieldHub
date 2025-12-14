<?php
// purchase-history.php
include_once 'config/Database.php';
include_once 'model/Purchase.php';

// Initialisation
$database = new Database();
$db = $database->getConnection();
$purchase = new Purchase($db);

// Pour l'exemple, utilisateur fixe - dans un vrai système, viendrait de la session
$user_id = 1;

// Récupérer l'historique des achats
$stmt = $purchase->readByUser($user_id);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - AI ShieldHub</title>
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

        /* Courses Section */
        .courses-section {
            padding: 140px 0 80px;
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
        
        .course-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .course-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }
        
        .course-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 12px;
            z-index: 1;
        }
        
        .badge-paid {
            background: linear-gradient(135deg, var(--accent), #f472b6);
            color: white;
        }
        
        .price-paid {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .course-card h4 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .course-card p {
            color: var(--light);
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .course-meta span {
            color: var(--light);
            font-size: 14px;
        }
        
        .course-meta i {
            margin-right: 5px;
            color: var(--primary);
        }
        
        .purchase-details {
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 15px;
            margin-top: 15px;
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
            .section-title h2 { font-size: 32px; }
            .course-meta { flex-direction: column; align-items: flex-start; gap: 10px; }
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
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link active" href="purchase-history.php">My Purchases</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Purchase History Section -->
    <section class="courses-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h6><i class="fas fa-shopping-bag me-2"></i>MY PURCHASES</h6>
                        <h2>Purchase History</h2>
                        <p>View all your course purchases and access your learning materials</p>
                    </div>

                    <?php if (empty($purchases)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h4 class="text-white">No purchases yet</h4>
                            <p class="text-light">Start your learning journey by exploring our courses</p>
                            <a href="index.php" class="btn btn-primary-custom">Browse Courses</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($purchases as $purchase): ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="course-card">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="course-badge badge-paid">PURCHASED</span>
                                            <span class="price-paid">$<?php echo number_format($purchase['amount'], 2); ?></span>
                                        </div>
                                        <h4><?php echo htmlspecialchars($purchase['course_title']); ?></h4>
                                        <!-- FIXED: Changed from 'description' to 'course_description' -->
                                        <p><?php echo htmlspecialchars($purchase['course_description']); ?></p>
                                        
                                        <div class="course-meta mb-3">
                                            <!-- FIXED: Changed from 'duration' to 'course_duration' -->
                                            <span><i class="fas fa-clock"></i><?php echo $purchase['course_duration']; ?>h</span>
                                            <span><i class="fas fa-calendar"></i><?php echo date('M j, Y', strtotime($purchase['purchase_date'])); ?></span>
                                        </div>

                                        <div class="purchase-details">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-receipt me-1"></i>
                                                Transaction: <?php echo $purchase['transaction_id']; ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Payment: <?php echo ucfirst($purchase['payment_method']); ?>
                                            </small>
                                        </div>

                                        <div class="mt-3">
                                            <a href="course-details.php?id=<?php echo $purchase['course_id']; ?>" 
                                               class="btn btn-primary-custom btn-sm">
                                                <i class="fas fa-play me-1"></i>Continue Learning
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
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
</body>
</html>