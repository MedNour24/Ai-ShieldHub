<?php 
require_once __DIR__ . '/../../Controller/PublicationControllerFront.php';
require_once __DIR__ . '/../../Model/Publication.php';
require_once __DIR__ . '/../../Controller/CommentaireControllerfront.php';
require_once __DIR__ . '/../../Model/Commentaire.php';
require_once __DIR__ . '/../../Controller/ReactionControllerFront.php';
require_once __DIR__ . '/../../Model/Reaction.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
if ($idUser <= 0) die("ID utilisateur non spécifié !");

$pubController = new PublicationControllerFront();
$commentController = new CommentaireControllerfront();
$reactionController = new ReactionControllerFront();

// ---------- GESTION DE L'AJOUT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $filePath = null;
    $fileType = null;

    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . "/../../uploads/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = basename($_FILES['file']['name']);
        $targetPath = $uploadDir . $fileName;

        if(move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $filePath = "uploads/" . $fileName;
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        }
    }

    $texte = $_POST['texte'] ?? '';

    $publication = new Publication(null, $idUser, $texte, $filePath, $fileType, new DateTime());
    $pubController->addPublication($publication);

    // CORRECTION : Rediriger vers la même section
    $currentSection = isset($_GET['section']) ? $_GET['section'] : 'feed';
    header("Location: addPublication.php?id_utilisateur=".$idUser."&section=".$currentSection);
    exit();
}
// ---------- PAGINATION MES PUBLICATIONS ----------
$limit = 5;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$total = $pubController->countUserPublications($idUser);
$totalPages = ceil($total/$limit);

$myPublications = $pubController->listUserPublications($idUser, $limit, $offset);

// ---------- PAGINATION FEED ----------
$feedLimit = 5;
$feedPage = isset($_GET['feed_page']) ? max(1,intval($_GET['feed_page'])) : 1;
$feedOffset = ($feedPage-1)*$feedLimit;

$totalFeed = $pubController->countAllPublications();
$totalFeedPages = ceil($totalFeed/$feedLimit);

$feedPublications = $pubController->listAllPublications($feedLimit, $feedOffset);

// Section active - Par défaut on affiche le feed
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'feed';
?>

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
        
        /* Main Content */
        .main-content {
            padding: 120px 0 50px;
            min-height: 100vh;
        }
        
        .community-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
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
        
        /* Return to Feed Button */
        .return-feed-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: var(--light);
            padding: 12px 25px;
            font-weight: 600;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .return-feed-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateX(-5px);
        }
        
        /* Publication Form */
        .publication-form {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .publication-form h4 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
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
        
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            resize: none;
            min-height: 120px;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-group textarea::placeholder {
            color: rgba(226, 232, 240, 0.5);
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: block;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: rgba(226, 232, 240, 0.7);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
        }
        
        .btn-publish {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            width: 100%;
        }
        
        .btn-publish:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        /* Publication Cards */
        .publication-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.4s ease;
        }
        
        .publication-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
        }
        
        .publication-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .publication-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
        }
        
        .publication-author {
            color: white;
            font-weight: 700;
            font-size: 18px;
        }
        
        .publication-date {
            color: rgba(226, 232, 240, 0.6);
            font-size: 14px;
        }
        
        .publication-content {
            color: var(--light);
            line-height: 1.7;
            margin-bottom: 20px;
            white-space: pre-line;
        }
        
        .publication-file {
            margin-bottom: 20px;
        }
        
        .file-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .file-link:hover {
            color: var(--accent);
        }
        
        .file-link i {
            margin-right: 8px;
        }
        
        /* Système de Réactions */
        .reaction-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 20px;
        }
        
        .reaction-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            color: rgba(226, 232, 240, 0.7);
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .reaction-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .reaction-btn.active.liked {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .reaction-btn.active.disliked {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .reaction-count {
            font-size: 14px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        .reaction-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            color: rgba(226, 232, 240, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }
        
        .action-btn:hover {
            color: var(--primary);
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        /* Comments Section */
        .comments-preview {
            margin-top: 20px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 20px;
        }
        
        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .comments-title {
            color: white;
            font-size: 16px;
            font-weight: 600;
        }
        
        .comment-preview {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--primary);
        }
        
        .comment-preview-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .comment-preview-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
            font-size: 12px;
        }
        
        .comment-preview-author {
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .comment-preview-date {
            color: rgba(226, 232, 240, 0.5);
            font-size: 11px;
            margin-left: auto;
        }
        
        .comment-preview-content {
            color: var(--light);
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-line;
        }
        
        /* Boutons Edit/Delete pour les commentaires */
        .comment-preview-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        
        .btn-edit-comment {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-edit-comment:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-delete-comment {
            background: rgba(236, 72, 153, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-delete-comment:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-1px);
        }
        
        .no-comments {
            text-align: center;
            color: rgba(226, 232, 240, 0.5);
            font-size: 14px;
            padding: 20px;
        }
        
        .view-all-comments {
            display: block;
            text-align: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .view-all-comments:hover {
            color: var(--accent);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--light);
            text-decoration: none;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
        }
        
        .pagination a.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
        }
        
        /* Hidden sections */
        .hidden {
            display: none;
        }
        
        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 60px 0 30px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            margin-top: 80px;
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
            .comment-preview-actions {
                flex-direction: column;
                gap: 5px;
            }
            .reaction-buttons {
                flex-wrap: wrap;
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
                    <li class="nav-item"><a class="nav-link active" href="/crud-communaute - Copie/View/frontcommunaute/addPublication.php?id_utilisateur=1">Publications</a></li>
                    <li class="nav-item"><a class="nav-link" href="./quiz.html">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="./tournament.html">Tournoi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fas fa-user" style="font-size: 16px;"></i>
                            </div>
                            <span>John Doe</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(99, 102, 241, 0.3);">
                            <li><a class="dropdown-item" href="#" onclick="openProfileModal(); return false;" style="color: var(--light);"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="#" onclick="showMyPublications(); return false;" style="color: var(--light);"><i class="fas fa-newspaper me-2"></i>My Publications</a></li>
                            <li><hr class="dropdown-divider" style="border-color: rgba(99, 102, 241, 0.3);"></li>
                            <li><a class="dropdown-item" href="./logout.html" style="color: var(--accent);"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container community-container">
            <div class="section-title">
                <h2>Learning Exchange</h2>
                <p>Share valuable insights, files, or videos and enrich each other's learning experience</p>
            </div>
            
            <!-- My Publications Section -->
            <section id="mes-publications-section" class="<?= $activeSection === 'mes' ? '' : 'hidden' ?>">
                <!-- BOUTON RETURN TO FEED -->
                <a href="#" class="return-feed-btn" id="returnToFeed">
                    <i class="fas fa-arrow-left me-2"></i>Return to Feed
                </a>
                
                <!-- FORMULAIRE DE PUBLICATION -->
                <div class="publication-form">
                    <h4>Create New Publication</h4>
                    <form id="formPublication" action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="postText"><i class="fas fa-edit me-2"></i>Content</label>
                            <textarea name="texte" id="postText" rows="6" placeholder="Share your thoughts, questions, or cybersecurity insights..."></textarea>
                            <small class="text-muted" style="color: rgba(226, 232, 240, 0.5) !important; font-size: 12px;">Maximum 200 characters allowed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fileInput"><i class="fas fa-paperclip me-2"></i>Attach File</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="file" id="fileInput">
                                <div class="file-input-label">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-publish">
                            <i class="fas fa-paper-plane me-2"></i>Publish
                        </button>
                    </form>
                </div>
                
                <!-- My Publications List -->
                <h4 class="text-white mb-4">My Publications</h4>
                
                <?php if(!empty($myPublications)): ?>
                    <?php foreach($myPublications as $p): 
                        // Récupérer les réactions pour cette publication
                        $reactionsSummary = $reactionController->getReactionsSummary($p['id_publication']);
                        $userReaction = $reactionController->getUserReaction($p['id_publication'], $idUser);
                    ?>
                        <div class="publication-card">
                            <div class="publication-header">
                                <div class="publication-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="publication-author"><?= htmlspecialchars($p['nom']) ?></div>
                                    <div class="publication-date"><?= date('M j, Y \a\t g:i A', strtotime($p['date_publication'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="publication-content">
                                <?= nl2br(htmlspecialchars($p['texte'])) ?>
                            </div>
                            
                            <?php if(!empty($p['fichier'])): ?>
                                <div class="publication-file">
                                    <a href="../../<?= htmlspecialchars($p['fichier']) ?>" target="_blank" class="file-link">
                                        <i class="fas fa-file"></i>View attached file
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <!-- NOUVEAU SYSTÈME DE RÉACTIONS -->
                            <div class="reaction-buttons">
                                <button class="reaction-btn <?= $userReaction === 'like' ? 'active liked' : '' ?>" 
                                        data-publication-id="<?= $p['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="like">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="reaction-count like-count" data-publication-id="<?= $p['id_publication'] ?>">
                                        <?= $reactionsSummary['like'] ?>
                                    </span>
                                </button>
                                
                                <button class="reaction-btn <?= $userReaction === 'dislike' ? 'active disliked' : '' ?>" 
                                        data-publication-id="<?= $p['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="dislike">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="reaction-count dislike-count" data-publication-id="<?= $p['id_publication'] ?>">
                                        <?= $reactionsSummary['dislike'] ?>
                                    </span>
                                </button>
                                
                                <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" class="action-btn">
                                    <i class="fas fa-comments"></i>Comments
                                </a>

                                <!-- Boutons Edit/Delete pour mes publications -->
                                <a href="editPublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn">
                                    <i class="fas fa-edit"></i>Edit
                                </a>
                                <a href="deletePublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this publication?')">
                                    <i class="fas fa-trash"></i>Delete
                                </a>
                            </div>

                            <!-- SECTION COMMENTAIRES POUR CETTE PUBLICATION -->
                            <div class="comments-preview">
                                <?php
                                // Récupérer les commentaires pour cette publication
                                $commentairesPub = $commentController->getCommentairesByPublication($p['id_publication']);
                                ?>
                                
                                <div class="comments-header">
                                    <div class="comments-title">
                                        <i class="fas fa-comments me-2"></i>Comments (<?= count($commentairesPub) ?>)
                                    </div>
                                </div>

                                <?php if (!empty($commentairesPub)): ?>
                                    <!-- Afficher les 2 derniers commentaires -->
                                    <?php 
                                    $recentComments = array_slice($commentairesPub, 0, 2);
                                    foreach ($recentComments as $comment): ?>
                                        <div class="comment-preview">
                                            <div class="comment-preview-header">
                                                <div class="comment-preview-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="comment-preview-author">
                                                    <?= htmlspecialchars($comment['nom']) ?>
                                                </div>
                                                <div class="comment-preview-date">
                                                    <?= date('M j, Y', strtotime($comment['date_commentaire'])) ?>
                                                </div>
                                            </div>
                                            <div class="comment-preview-content">
                                                <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                            </div>
                                            
                                            <!-- Afficher les boutons Edit/Delete seulement pour les commentaires de l'utilisateur connecté -->
                                            <?php if ($comment['id_utilisateur'] == $idUser): ?>
                                                <div class="comment-preview-actions">
                                                    <a href="editcommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" 
                                                       class="btn-edit-comment">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <a href="deletecommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" 
                                                       class="btn-delete-comment"
                                                       onclick="return confirm('Are you sure you want to delete this comment?')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Lien pour voir tous les commentaires si plus de 2 -->
                                    <?php if (count($commentairesPub) > 2): ?>
                                        <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" class="view-all-comments">
                                            View all <?= count($commentairesPub) ?> comments
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-comments">
                                        <i class="fas fa-comment-slash me-2"></i>No comments yet
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="publication-card text-center">
                        <i class="fas fa-newspaper mb-3" style="font-size: 48px; color: rgba(226, 232, 240, 0.3);"></i>
                        <h5 class="text-white">No Publications Yet</h5>
                        <p class="text-light">Start by creating your first publication above!</p>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination Mes Publications -->
                <?php if($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?id_utilisateur=<?= $idUser ?>&page=<?= $i ?>&section=mes" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Feed Section -->
            <section id="feed-section" class="<?= $activeSection === 'feed' ? '' : 'hidden' ?>">
                <h4 class="text-white mb-4">Global Feed</h4>
                
                <?php if(!empty($feedPublications)): ?>
                    <?php foreach($feedPublications as $f): 
                        // Récupérer les réactions pour cette publication
                        $reactionsSummary = $reactionController->getReactionsSummary($f['id_publication']);
                        $userReaction = $reactionController->getUserReaction($f['id_publication'], $idUser);
                    ?>
                        <div class="publication-card">
                            <div class="publication-header">
                                <div class="publication-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="publication-author"><?= htmlspecialchars($f['nom']) ?></div>
                                    <div class="publication-date"><?= date('M j, Y \a\t g:i A', strtotime($f['date_publication'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="publication-content">
                                <?= nl2br(htmlspecialchars($f['texte'])) ?>
                            </div>
                            
                            <?php if(!empty($f['fichier'])): ?>
                                <div class="publication-file">
                                    <a href="../../<?= htmlspecialchars($f['fichier']) ?>" target="_blank" class="file-link">
                                        <i class="fas fa-file"></i>View attached file
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <!-- NOUVEAU SYSTÈME DE RÉACTIONS POUR LE FEED -->
                            <div class="reaction-buttons">
                                <button class="reaction-btn <?= $userReaction === 'like' ? 'active liked' : '' ?>" 
                                        data-publication-id="<?= $f['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="like">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="reaction-count like-count" data-publication-id="<?= $f['id_publication'] ?>">
                                        <?= $reactionsSummary['like'] ?>
                                    </span>
                                </button>
                                
                                <button class="reaction-btn <?= $userReaction === 'dislike' ? 'active disliked' : '' ?>" 
                                        data-publication-id="<?= $f['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="dislike">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="reaction-count dislike-count" data-publication-id="<?= $f['id_publication'] ?>">
                                        <?= $reactionsSummary['dislike'] ?>
                                    </span>
                                </button>
                                
                                <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="action-btn">
                                    <i class="fas fa-comments"></i>Comments
                                </a>
                            </div>

                            <!-- SECTION COMMENTAIRES POUR CETTE PUBLICATION DU FEED -->
                            <div class="comments-preview">
                                <?php
                                // Récupérer les commentaires pour cette publication
                                $commentairesFeed = $commentController->getCommentairesByPublication($f['id_publication']);
                                ?>
                                
                                <div class="comments-header">
                                    <div class="comments-title">
                                        <i class="fas fa-comments me-2"></i>Comments (<?= count($commentairesFeed) ?>)
                                    </div>
                                </div>

                                <?php if (!empty($commentairesFeed)): ?>
                                    <!-- Afficher les 2 derniers commentaires -->
                                    <?php 
                                    $recentComments = array_slice($commentairesFeed, 0, 2);
                                    foreach ($recentComments as $comment): ?>
                                        <div class="comment-preview">
                                            <div class="comment-preview-header">
                                                <div class="comment-preview-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="comment-preview-author">
                                                    <?= htmlspecialchars($comment['nom']) ?>
                                                </div>
                                                <div class="comment-preview-date">
                                                    <?= date('M j, Y', strtotime($comment['date_commentaire'])) ?>
                                                </div>
                                            </div>
                                            <div class="comment-preview-content">
                                                <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                            </div>
                                            
                                            <!-- Afficher les boutons Edit/Delete seulement pour les commentaires de l'utilisateur connecté -->
                                            <?php if ($comment['id_utilisateur'] == $idUser): ?>
                                                <div class="comment-preview-actions">
                                                    <a href="editcommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" 
                                                       class="btn-edit-comment">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <a href="deletecommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" 
                                                       class="btn-delete-comment"
                                                       onclick="return confirm('Are you sure you want to delete this comment?')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Lien pour voir tous les commentaires si plus de 2 -->
                                    <?php if (count($commentairesFeed) > 2): ?>
                                        <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="view-all-comments">
                                            View all <?= count($commentairesFeed) ?> comments
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-comments">
                                        <i class="fas fa-comment-slash me-2"></i>No comments yet
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="publication-card text-center">
                        <i class="fas fa-newspaper mb-3" style="font-size: 48px; color: rgba(226, 232, 240, 0.3);"></i>
                        <h5 class="text-white">No Publications Yet</h5>
                        <p class="text-light">Be the first to create a publication!</p>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination Feed -->
                <?php if($totalFeedPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalFeedPages; $i++): ?>
                            <a href="addPublication.php?id_utilisateur=<?= $idUser ?>&feed_page=<?= $i ?>&section=feed" class="<?= $i==$feedPage?'active':'' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </section>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Système de réactions avec JavaScript
        class ReactionSystem {
            constructor() {
                this.init();
            }

            init() {
                // Initialiser les événements pour tous les boutons de réaction
                document.querySelectorAll('.reaction-btn').forEach(btn => {
                    btn.addEventListener('click', this.handleReaction.bind(this));
                });

                // Gestion du double-clic
                document.querySelectorAll('.reaction-btn').forEach(btn => {
                    let clickCount = 0;
                    btn.addEventListener('click', (e) => {
                        clickCount++;
                        if (clickCount === 1) {
                            setTimeout(() => {
                                if (clickCount === 1) {
                                    // Simple click - déjà géré par handleReaction
                                } else {
                                    // Double click - supprimer la réaction
                                    this.handleDoubleClick(e);
                                }
                                clickCount = 0;
                            }, 300);
                        }
                    });
                });
            }

            async handleReaction(event) {
                event.preventDefault();
                
                const button = event.currentTarget;
                const publicationId = button.dataset.publicationId;
                const userId = button.dataset.userId;
                const reactionType = button.dataset.reactionType;
                
                // Désactiver le bouton pendant le traitement
                button.disabled = true;
                
                try {
                    const response = await this.sendReaction(publicationId, userId, reactionType);
                    
                    if (response.success) {
                        this.updateReactionUI(publicationId, response);
                    } else {
                        console.error('Erreur:', response.message);
                        alert('Erreur lors de l\'enregistrement de la réaction');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Erreur de connexion');
                } finally {
                    button.disabled = false;
                }
            }

            async handleDoubleClick(event) {
                const button = event.currentTarget;
                const publicationId = button.dataset.publicationId;
                const userId = button.dataset.userId;
                const reactionType = button.dataset.reactionType;
                
                // Vérifier si le bouton est déjà actif (double-clic sur la même réaction)
                if (button.classList.contains('active')) {
                    // Simuler un clic pour supprimer la réaction
                    await this.handleReaction(event);
                }
            }

            async sendReaction(publicationId, userId, reactionType) {
                const formData = new FormData();
                formData.append('id_publication', publicationId);
                formData.append('id_utilisateur', userId);
                formData.append('type', reactionType);

                const response = await fetch('addreaction.php', {
                    method: 'POST',
                    body: formData
                });

                return await response.json();
            }

            updateReactionUI(publicationId, data) {
                const likeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="like"]`);
                const dislikeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="dislike"]`);
                const likeCount = document.querySelector(`.like-count[data-publication-id="${publicationId}"]`);
                const dislikeCount = document.querySelector(`.dislike-count[data-publication-id="${publicationId}"]`);

                // Mettre à jour les compteurs
                if (likeCount) likeCount.textContent = data.likes;
                if (dislikeCount) dislikeCount.textContent = data.dislikes;

                // Mettre à jour l'état actif des boutons
                this.updateButtonState(likeBtn, 'like', data.userReaction);
                this.updateButtonState(dislikeBtn, 'dislike', data.userReaction);
            }

            updateButtonState(button, buttonType, userReaction) {
                if (!button) return;

                // Retirer toutes les classes d'état actif
                button.classList.remove('active', 'liked', 'disliked');

                // Ajouter la classe active si c'est la réaction actuelle de l'utilisateur
                if (userReaction === buttonType) {
                    button.classList.add('active', buttonType + 'd');
                }
            }
        }

        // Section Navigation
        const mesSection = document.getElementById('mes-publications-section');
        const feedSection = document.getElementById('feed-section');
        const returnToFeed = document.getElementById('returnToFeed');

        function showSection(section) {
            // Masquer toutes les sections
            mesSection.style.display = 'none';
            feedSection.style.display = 'none';
            
            // Afficher la section demandée
            if(section === 'mes') {
                mesSection.style.display = 'block';
            } else if(section === 'feed') {
                feedSection.style.display = 'block';
            }
            
            // Mettre à jour l'URL
            const url = new URL(window.location);
            url.searchParams.set('section', section);
            window.history.pushState({}, '', url);
        }

        // Fonction pour afficher mes publications depuis le dropdown
        function showMyPublications() {
            showSection('mes');
            // Fermer le dropdown
            const dropdown = document.querySelector('.dropdown-menu');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // Événements de clic
        returnToFeed.addEventListener('click', function(e) {
            e.preventDefault();
            showSection('feed');
        });

        // Vérifier l'état initial au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            
            if (section === 'mes') {
                showSection('mes');
            } else {
                showSection('feed');
            }

            // Initialiser le système de réactions
            new ReactionSystem();
        });

        // Gestion du formulaire
        const form = document.getElementById('formPublication');
        if (form) {
            form.addEventListener('submit', function(e){
                const texte = document.getElementById('postText').value.trim();
                if(texte === ''){ 
                    alert('Le texte ne peut pas être vide !'); 
                    e.preventDefault(); 
                    return;
                }
                if(texte.length > 200){ 
                    alert('Le texte ne peut pas dépasser 200 caractères !'); 
                    e.preventDefault(); 
                    return;
                }
            });
        }

        // Gestion de l'affichage du nom du fichier
        const fileInput = document.getElementById('fileInput');
        const fileInputLabel = document.querySelector('.file-input-label');
        
        if (fileInput && fileInputLabel) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileInputLabel.innerHTML = '<i class="fas fa-file me-2"></i>' + this.files[0].name;
                    fileInputLabel.style.background = 'rgba(99, 102, 241, 0.1)';
                    fileInputLabel.style.borderColor = 'var(--primary)';
                    fileInputLabel.style.color = 'white';
                } else {
                    fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload';
                    fileInputLabel.style.background = 'rgba(255, 255, 255, 0.05)';
                    fileInputLabel.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                    fileInputLabel.style.color = 'rgba(226, 232, 240, 0.7)';
                }
            });
        }

        // Confirmation pour la suppression des commentaires
        document.querySelectorAll('.btn-delete-comment').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });

        // Compteur de caractères pour le textarea
        const postText = document.getElementById('postText');
        if (postText) {
            postText.addEventListener('input', function() {
                const charCount = this.value.length;
                const maxChars = 200;
                
                if (charCount > maxChars) {
                    this.value = this.value.substring(0, maxChars);
                }
            });
        }
    </script>
</body>
</html>