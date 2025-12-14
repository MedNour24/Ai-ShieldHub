<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI ShieldHub | Cybersecurity Courses</title>
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
        
        /* Dropdown Styling */
        .dropdown-menu {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            padding: 10px 0;
        }
        
        .dropdown-item {
            color: var(--light);
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding-left: 25px;
        }
        
        .dropdown-divider {
            border-color: rgba(99, 102, 241, 0.2);
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
        
        /* Courses Section - Now first section after navbar */
        .courses-section {
            padding: 140px 0 80px; /* Increased top padding to account for navbar */
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
            cursor: pointer;
        }
        
        .course-card::before {
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
        
        .course-card:hover::before {
            opacity: 0.1;
        }
        
        .course-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }
        
        .course-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 12px;
            z-index: 1;
        }
        
        .badge-free {
            background: linear-gradient(135deg, var(--success), #34d399);
            color: white;
        }
        
        .badge-paid {
            background: linear-gradient(135deg, var(--accent), #f472b6);
            color: white;
        }
        
        .course-price {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .price-free {
            background: linear-gradient(135deg, var(--success), #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .price-paid {
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
            position: relative;
            z-index: 1;
        }
        
        .course-card p {
            color: var(--light);
            line-height: 1.7;
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .course-meta span {
            color: var(--light);
            font-size: 14px;
        }
        
        .course-meta i {
            margin-right: 5px;
            color: var(--primary);
        }
        
        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--light);
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
        }
        
        /* Stats Section - Now comes after courses */
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
            .filter-buttons { flex-direction: column; align-items: center; }
            
            /* Mobile dropdown adjustments */
            .dropdown-menu {
                background: rgba(15, 23, 42, 0.98);
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="particle" style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.3); top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; background: rgba(139, 92, 246, 0.3); top: 60%; left: 80%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 80px; height: 80px; background: rgba(236, 72, 153, 0.3); top: 80%; left: 20%; animation-delay: 6s;"></div>
    </div>

    <!-- Navbar - UPDATED: Courses dropdown with My Purchases sub-menu -->
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
                    <!-- Courses Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="coursesDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-book me-1"></i>Courses
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="coursesDropdown">
                            <li>
                                <a class="dropdown-item" href="index.php">
                                    <i class="fas fa-list me-2"></i>All Courses
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="purchase-history.php">
                                    <i class="fas fa-shopping-bag me-2"></i>My Purchases
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item"><a class="nav-link" href="community.html">Community</a></li>
                    <li class="nav-item"><a class="nav-link" href="tools.html">Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Courses Section - Now first section after navbar -->
    <section id="courses" class="courses-section">
        <div class="container">
            <div class="section-title">
                <h6><i class="fas fa-book me-2"></i>OUR COURSES</h6>
                <h2>Cybersecurity Learning Paths</h2>
                <p>Choose from free and premium courses to master cybersecurity</p>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Courses</button>
                <button class="filter-btn" data-filter="free">Free Courses</button>
                <button class="filter-btn" data-filter="paid">Premium Courses</button>
            </div>

            <!-- Courses Grid -->
            <div class="row g-4" id="courses-grid">
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading courses...</span>
                    </div>
                    <p class="mt-2">Loading courses...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section - Now comes after courses -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="total-courses">0</div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="free-courses">0</div>
                        <div class="stat-label">Free Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="paid-courses">0</div>
                        <div class="stat-label">Paid Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="total-hours">0</div>
                        <div class="stat-label">Total Hours</div>
                    </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const controllerUrl = 'controller/CourseController.php';
            
            // Load courses on page load
            loadCourses();
            
            // Filter functionality
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).data('filter');
                filterCourses(filter);
            });

            // Course click handler - Only navigate to course details when clicking the card
            $(document).on('click', '.course-card', function(e) {
                // Don't trigger if clicking on a button or link inside
                if (!$(e.target).closest('.btn').length && 
                    !$(e.target).is('a') && 
                    !$(e.target).closest('a').length) {
                    const courseId = $(this).closest('.course-item').data('course-id');
                    if (courseId) {
                        window.location.href = `course-details.php?id=${courseId}`;
                    }
                }
            });

            function loadCourses() {
                $.ajax({
                    url: controllerUrl,
                    type: 'GET',
                    data: { action: 'list' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayCourses(response.data);
                            updateStatistics(response.data);
                        } else {
                            $('#courses-grid').html('<div class="col-12 text-center text-danger"><p>Error loading courses</p></div>');
                        }
                    },
                    error: function() {
                        $('#courses-grid').html('<div class="col-12 text-center text-danger"><p>Error loading courses</p></div>');
                    }
                });
            }

            function displayCourses(courses) {
                const grid = $('#courses-grid');
                grid.empty();

                if (courses.length === 0) {
                    grid.html('<div class="col-12 text-center"><p>No courses available</p></div>');
                    return;
                }

                // Only show active courses
                const activeCourses = courses.filter(course => course.status === 'active');

                activeCourses.forEach(course => {
                    const isFree = course.license_type === 'free';
                    const priceText = isFree ? 'Free' : `$${parseFloat(course.price).toFixed(2)}`;
                    const priceClass = isFree ? 'price-free' : 'price-paid';
                    const badgeClass = isFree ? 'badge-free' : 'badge-paid';
                    const badgeText = isFree ? 'FREE' : 'PREMIUM';

                    const courseCard = `
                        <div class="col-lg-4 col-md-6 course-item" data-license="${course.license_type}" data-course-id="${course.id}">
                            <div class="course-card">
                                <span class="course-badge ${badgeClass}">${badgeText}</span>
                                <div class="course-price ${priceClass}">${priceText}</div>
                                <h4>${escapeHtml(course.title)}</h4>
                                <p>${escapeHtml(course.description)}</p>
                                <div class="course-meta">
                                    <span><i class="fas fa-clock"></i>${course.duration}h</span>
                                    <span><i class="fas fa-calendar"></i>${new Date(course.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.append(courseCard);
                });
            }

            function filterCourses(filter) {
                if (filter === 'all') {
                    $('.course-item').show();
                } else {
                    $('.course-item').hide();
                    $(`.course-item[data-license="${filter}"]`).show();
                }
            }

            function updateStatistics(courses) {
                const activeCourses = courses.filter(course => course.status === 'active');
                const totalCourses = activeCourses.length;
                const freeCourses = activeCourses.filter(course => course.license_type === 'free').length;
                const paidCourses = activeCourses.filter(course => course.license_type === 'paid').length;
                const totalHours = activeCourses.reduce((sum, course) => sum + parseInt(course.duration), 0);

                $('#total-courses').text(totalCourses);
                $('#free-courses').text(freeCourses);
                $('#paid-courses').text(paidCourses);
                $('#total-hours').text(totalHours);
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;', 
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }
            
            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });
            
            // Set active state for dropdown items
            const currentPage = window.location.pathname.split('/').pop();
            if (currentPage === 'purchase-history.php') {
                $('.dropdown-item[href="purchase-history.php"]').addClass('active');
                $('.dropdown-item[href="index.php"]').removeClass('active');
            } else {
                $('.dropdown-item[href="index.php"]').addClass('active');
                $('.dropdown-item[href="purchase-history.php"]').removeClass('active');
            }
        });
    </script>
</body>
</html>