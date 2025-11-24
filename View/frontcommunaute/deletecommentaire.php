<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/CommentaireControllerfront.php';
require_once __DIR__ . '/../../Model/Commentaire.php';

// Récupérer les paramètres
$idCommentaire = isset($_GET['id_commentaire']) ? intval($_GET['id_commentaire']) : 0;
$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
$idPublication = isset($_GET['id_publication']) ? intval($_GET['id_publication']) : 0;

if ($idCommentaire <= 0) die("ID commentaire non spécifié !");
if ($idUser <= 0) die("ID utilisateur non spécifié !");
if ($idPublication <= 0) die("ID publication non spécifié !");

$commentController = new CommentaireControllerfront();

// Récupérer le commentaire à supprimer pour vérification
$commentaire = $commentController->getCommentaireById($idCommentaire);
if (!$commentaire) {
    die("Commentaire non trouvé !");
}

// Vérifier que l'utilisateur est bien le propriétaire du commentaire
if ($commentaire['id_utilisateur'] != $idUser) {
    die("Vous n'êtes pas autorisé à supprimer ce commentaire !");
}

// ---------- GESTION DE LA SUPPRESSION ----------
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Supprimer le commentaire
    $commentController->deleteCommentaire($idCommentaire);
    
    // Rediriger vers la page des commentaires
    header("Location: addcommentaire.php?id_utilisateur=" . $idUser . "&id_publication=" . $idPublication);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AICyberLearn | Delete Comment</title>
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
        
        /* Delete Comment Section */
        .delete-comment-section {
            padding: 100px 0 50px;
            min-height: 100vh;
        }
        
        .delete-comment-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .delete-comment-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(236, 72, 153, 0.3);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            text-align: center;
        }
        
        .delete-comment-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.2);
        }
        
        .warning-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 35px;
            border: 3px solid rgba(236, 72, 153, 0.3);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--accent);
        }
        
        .comment-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 18px;
            border: 2px solid rgba(99, 102, 241, 0.3);
        }
        
        .comment-author {
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .comment-date {
            color: var(--light);
            font-size: 12px;
            opacity: 0.7;
        }
        
        .comment-content {
            color: var(--light);
            line-height: 1.6;
            margin: 15px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border-left: 3px solid var(--accent);
        }
        
        .back-link {
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--accent);
            transform: translateX(-5px);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.5);
            color: white;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        .warning-text {
            color: var(--accent);
            font-weight: 600;
            margin: 20px 0;
            font-size: 18px;
        }
        
        .info-text {
            color: var(--light);
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .delete-comment-section {
                padding: 80px 0 30px;
            }
            
            .delete-comment-card {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .warning-icon {
                width: 60px;
                height: 60px;
                font-size: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="particle" style="width: 100px; height: 100px; background: rgba(236, 72, 153, 0.3); top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; background: rgba(245, 158, 11, 0.3); top: 60%; left: 80%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 80px; height: 80px; background: rgba(99, 102, 241, 0.3); top: 80%; left: 20%; animation-delay: 6s;"></div>
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
                    <li class="nav-item"><a class="nav-link" href="addPublication.php?id_utilisateur=<?= $idUser ?>">Publications</a></li>
                    <li class="nav-item"><a class="nav-link" href="./quiz.html">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="./tournament.html">Tournoi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fas fa-user" style="font-size: 16px;"></i>
                            </div>
                            <span>User <?= $idUser ?></span>
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

    <!-- Delete Comment Section -->
    <section class="delete-comment-section">
        <div class="container">
            <div class="delete-comment-container">
                <!-- Back Link -->
                <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Comments
                </a>

                <!-- Delete Confirmation Card -->
                <div class="delete-comment-card">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    
                    <h2 style="color: white; margin-bottom: 20px; text-align: center;">
                        Delete Comment
                    </h2>
                    
                    <p class="warning-text">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Warning: This action cannot be undone!
                    </p>
                    
                    <p class="info-text">
                        You are about to permanently delete your comment. This action will remove the comment from the publication and it cannot be recovered.
                    </p>

                    <!-- Comment Preview -->
                    <div class="comment-header">
                        <div class="comment-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="comment-author"><?= htmlspecialchars($commentaire['nom']) ?></div>
                            <div class="comment-date">
                                <?= date('M j, Y \a\t g:i A', strtotime($commentaire['date_commentaire'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                    </div>

                    <div class="action-buttons">
                        <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>" class="btn-cancel">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <a href="deletecommentaire.php?id_commentaire=<?= $idCommentaire ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>&confirm=yes" 
                           class="btn-delete"
                           onclick="return confirm('Are you absolutely sure you want to delete this comment? This action cannot be undone.')">
                            <i class="fas fa-trash me-2"></i>Delete Comment
                        </a>
                    </div>
                </div>
                
                <!-- Additional Warning -->
                <div style="text-align: center; margin-top: 20px;">
                    <p style="color: var(--light); font-size: 14px; opacity: 0.7;">
                        <i class="fas fa-info-circle me-2"></i>
                        Once deleted, this comment will be permanently removed from the system.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Double confirmation pour la suppression
        document.querySelector('.btn-delete').addEventListener('click', function(e) {
            if (!confirm('⚠️ FINAL WARNING: Are you absolutely sure you want to delete this comment?\n\nThis action is PERMANENT and cannot be undone.')) {
                e.preventDefault();
                return false;
            }
            
            // Ajouter un indicateur de chargement
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
            
            // La redirection se fera naturellement via le lien
        });

        // Empêcher la soumission multiple
        let deletionInProgress = false;
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') && deletionInProgress) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>