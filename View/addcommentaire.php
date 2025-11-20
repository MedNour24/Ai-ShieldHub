<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/CommentaireControllerfront.php';
require_once __DIR__ . '/../../Model/Commentaire.php';
require_once __DIR__ . '/../../Controller/PublicationControllerFront.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
$idPublication = isset($_GET['id_publication']) ? intval($_GET['id_publication']) : 0;

if ($idUser <= 0) die("ID utilisateur non spécifié !");
if ($idPublication <= 0) die("ID publication non spécifié !");

$commentController = new CommentaireControllerfront();
$pubController = new PublicationControllerFront();

// Récupérer les informations de la publication
$publication = $pubController->getPublicationById($idPublication);

// ---------- GESTION DE L'AJOUT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
    $contenu = $_POST['contenu'] ?? '';
    
    if (!empty($contenu)) {
        $commentaire = new Commentaire(null, $idPublication, $idUser, $contenu, new DateTime());
        $commentController->addCommentaire($commentaire);
        
        header("Location: addcommentaire.php?id_utilisateur=" . $idUser . "&id_publication=" . $idPublication);
        exit();
    }
}

// ---------- RÉCUPÉRATION DES COMMENTAIRES ----------
$commentaires = $commentController->getCommentairesByPublication($idPublication);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AICyberLearn | Comments Management</title>
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
        
        /* Comments Section */
        .comments-section {
            padding: 100px 0 50px;
            min-height: 100vh;
        }
        
        .comments-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .comment-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .comment-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
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
            margin-bottom: 15px;
            white-space: pre-line;
        }
        
        .comment-actions {
            display: flex;
            gap: 20px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 15px;
        }
        
        .comment-action {
            color: var(--light);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: none;
            border: none;
            display: flex;
            align-items: center;
        }
        
        .comment-action:hover {
            color: var(--primary);
        }
        
        .comment-action.delete:hover {
            color: var(--accent);
        }
        
        /* Style pour les boutons d'action de l'utilisateur connecté */
        .user-comment-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .btn-edit-comment {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-edit-comment:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-delete-comment {
            background: rgba(236, 72, 153, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-delete-comment:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Comment Form */
        .comment-form-container {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .comment-form-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: var(--light);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
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
            resize: vertical;
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
        
        .btn-comment {
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
        
        .btn-comment:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        .btn-comment:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .char-counter {
            text-align: right;
            color: var(--light);
            font-size: 12px;
            margin-top: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--light);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            color: white;
            margin-bottom: 10px;
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
        
        @media (max-width: 768px) {
            .comments-section {
                padding: 80px 0 30px;
            }
            
            .comment-card {
                padding: 20px;
            }
            
            .comment-form-container {
                padding: 20px;
            }
            
            .user-comment-actions {
                flex-direction: column;
                gap: 8px;
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

    <!-- Comments Section -->
    <section class="comments-section">
        <div class="container">
            <div class="comments-container">
                <!-- Back Link -->
                <a href="addPublication.php?id_utilisateur=<?= $idUser ?>" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Publications
                </a>

                <!-- Publication Info -->
                <?php if ($publication): ?>
                <div class="publication-preview mb-4 p-4" style="background: rgba(255, 255, 255, 0.03); border-radius: 15px; border-left: 4px solid var(--primary);">
                    <h5 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-file-alt me-2"></i>Publication
                    </h5>
                    <p style="color: var(--light); line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($publication['texte'])) ?>
                    </p>
                    <?php if(!empty($publication['fichier'])): ?>
                        <div class="mt-2">
                            <a href="../../<?= htmlspecialchars($publication['fichier']) ?>" target="_blank" class="file-link" style="color: var(--primary);">
                                <i class="fas fa-file me-2"></i>View attached file
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Comment Form -->
                <div class="comment-form-container">
                    <h3 class="comment-form-title">
                        <i class="fas fa-comment me-2"></i>Add a Comment
                    </h3>
                    <form id="commentForm" method="POST" action="">
                        <div class="form-group">
                            <label for="commentContent">
                                <i class="fas fa-edit me-2"></i>Your Comment
                            </label>
                            <textarea 
                                name="contenu" 
                                id="commentContent" 
                                class="form-control" 
                                placeholder="Share your thoughts about this publication..."
                                maxlength="500"
                                required
                            ></textarea>
                            <div class="char-counter">0/500 characters</div>
                        </div>
                        <button type="submit" class="btn-comment">
                            <i class="fas fa-paper-plane me-2"></i>Post Comment
                        </button>
                    </form>
                </div>

                <!-- Comments List -->
                <div class="comments-list">
                    <h3 style="color: white; margin-bottom: 30px; text-align: center;">
                        <i class="fas fa-comments me-2"></i>Comments (<?= count($commentaires) ?>)
                    </h3>

                    <?php if (!empty($commentaires)): ?>
                        <?php foreach ($commentaires as $commentaire): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <div class="comment-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="comment-author"><?= htmlspecialchars($commentaire['nom']) ?></div>
                                        <div class="comment-date">
                                            <?= date('M j, Y \a\t g:i A', strtotime($commentaire['date_commentaire'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                                </div>
                                
                                <!-- Afficher les boutons Edit/Delete seulement pour les commentaires de l'utilisateur connecté -->
                                <?php if ($commentaire['id_utilisateur'] == $idUser): ?>
                                    <div class="user-comment-actions">
                                        <a href="editcommentaire.php?id_commentaire=<?= $commentaire['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>" 
                                           class="btn-edit-comment">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="deletecommentaire.php?id_commentaire=<?= $commentaire['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>" 
                                           class="btn-delete-comment"
                                           onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comment-slash"></i>
                            <h4>No Comments Yet</h4>
                            <p>Be the first to share your thoughts about this publication!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for comment textarea
        const commentTextarea = document.getElementById('commentContent');
        const charCounter = document.querySelector('.char-counter');

        commentTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            const maxChars = 500;
            
            if (charCount > maxChars) {
                this.value = this.value.substring(0, maxChars);
            }
            
            charCounter.textContent = `${Math.min(charCount, maxChars)}/${maxChars} characters`;
            
            if (charCount > maxChars * 0.8) {
                charCounter.style.color = '#ec4899';
            } else {
                charCounter.style.color = '#e2e8f0';
            }
        });

        // Form validation
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            const content = commentTextarea.value.trim();
            
            if (content === '') {
                e.preventDefault();
                alert('Please enter your comment before posting.');
                commentTextarea.focus();
                return;
            }
            
            if (content.length > 500) {
                e.preventDefault();
                alert('Comment cannot exceed 500 characters.');
                return;
            }
        });

        // Auto-resize textarea
        commentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Initialize character counter
        commentTextarea.dispatchEvent(new Event('input'));

        // Confirmation pour la suppression
        document.querySelectorAll('.btn-delete-comment').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>