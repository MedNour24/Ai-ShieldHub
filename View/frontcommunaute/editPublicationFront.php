<?php
require_once __DIR__ . '/../../Controller/PublicationControllerFront.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
$idPub  = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($idUser<=0 || $idPub<=0) die("Paramètres manquants !");

$pubController = new PublicationControllerFront();
$publication = $pubController->getPublicationById($idPub);

if(!$publication) die("Publication introuvable !");

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD']==='POST'){
    $texte = $_POST['texte'] ?? '';
    $filePath = $publication['fichier']; // garder ancien fichier par défaut

    if(!empty($_FILES['file']['name'])){
        $uploadDir = __DIR__ . "/../../uploads/";
        if(!file_exists($uploadDir)) mkdir($uploadDir,0777,true);

        $fileName = basename($_FILES['file']['name']);
        $targetPath = $uploadDir.$fileName;

        if(move_uploaded_file($_FILES['file']['tmp_name'],$targetPath)){
            $filePath = "uploads/".$fileName;
        }
    }

    // Créer un objet Publication pour l'update
    $p = new Publication($idPub, $idUser, $texte, $filePath, null, new DateTime());
    $pubController->updatePublication($p);

    // Redirection vers la même section
    $currentSection = isset($_GET['section']) ? $_GET['section'] : 'mes';
    header("Location: addPublication.php?id_utilisateur=".$idUser."&section=".$currentSection);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Publication | AI ShieldHub</title>
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
        
        /* Main Content */
        .main-content {
            padding: 120px 0 50px;
            min-height: 100vh;
        }
        
        .community-container {
            max-width: 800px;
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
        
        /* Return Button */
        .return-btn {
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
        
        .return-btn:hover {
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
        
        .current-file {
            margin-top: 10px;
            color: var(--light);
            font-size: 14px;
        }
        
        .current-file a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .current-file a:hover {
            color: var(--accent);
        }
        
        .btn-update {
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
        
        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        @media (max-width: 768px) {
            .section-title h2 { font-size: 32px; }
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
                    <li class="nav-item"><a class="nav-link" href="/crud-communaute - Copie/View/frontcommunaute/addPublication.php?id_utilisateur=<?= $idUser ?>">Publications</a></li>
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
                <h2>Edit Publication</h2>
                <p>Update your publication content and attachments</p>
            </div>
            
            <!-- Return Button -->
            <a href="addPublication.php?id_utilisateur=<?= $idUser ?>&section=mes" class="return-btn">
                <i class="fas fa-arrow-left me-2"></i>Back to My Publications
            </a>
            
            <!-- Edit Publication Form -->
            <div class="publication-form">
                <h4>Update Publication</h4>
                <form id="formEdit" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="postText"><i class="fas fa-edit me-2"></i>Content</label>
                        <textarea name="texte" id="postText" rows="6" placeholder="Update your publication content..."><?= htmlspecialchars($publication['texte']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="fileInput"><i class="fas fa-paperclip me-2"></i>Update File</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="file" id="fileInput">
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Choose a new file (optional)
                            </div>
                        </div>
                        
                        <?php if(!empty($publication['fichier'])): ?>
                            <div class="current-file">
                                <i class="fas fa-file me-2"></i>Current file: 
                                <a href="../../<?= htmlspecialchars($publication['fichier']) ?>" target="_blank">
                                    <?= basename($publication['fichier']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save me-2"></i>Update Publication
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Contrôle JS - Même validation que dans addPublication.php
        const form = document.getElementById('formEdit');
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
                    fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Choose a new file (optional)';
                    fileInputLabel.style.background = 'rgba(255, 255, 255, 0.05)';
                    fileInputLabel.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                    fileInputLabel.style.color = 'rgba(226, 232, 240, 0.7)';
                }
            });
        }
    </script>
</body>
</html>