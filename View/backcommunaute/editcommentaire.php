<?php
require_once __DIR__ . '/../../Controller/CommentaireController.php';
require_once __DIR__ . '/../../Model/Commentaire.php';

// Vérifier que les paramètres sont présents
if (!isset($_GET['id_commentaire']) || !isset($_GET['id_utilisateur']) || !isset($_GET['id_publication'])) {
    die("Paramètres manquants !");
}

$idCommentaire = intval($_GET['id_commentaire']);
$idUser = intval($_GET['id_utilisateur']);
$idPublication = intval($_GET['id_publication']);

if ($idCommentaire <= 0 || $idUser <= 0) {
    die("ID invalide !");
}

$commentaireController = new CommentaireController();

// Récupérer le commentaire à modifier
$commentaire = $commentaireController->getCommentaireById($idCommentaire);

if (!$commentaire) {
    die("Commentaire non trouvé !");
}

// Gestion de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte = $_POST['texte'] ?? '';
    
    // Validation côté serveur
    if (empty(trim($texte))) {
        $error = "Le texte du commentaire ne peut pas être vide !";
    } elseif (strlen(trim($texte)) > 500) {
        $error = "Le commentaire ne peut pas dépasser 500 caractères !";
    } else {
        // Créer l'objet Commentaire et mettre à jour
        $commentaireObj = new Commentaire(
            $idCommentaire,
            $commentaire['id_utilisateur'],
            $commentaire['id_publication'],
            trim($texte),
            new DateTime($commentaire['date_commentaire'])
        );
        
        $commentaireController->updateCommentaire($commentaireObj, $idCommentaire);
        
        // Rediriger vers la page des commentaires
        header("Location: addcommentaire.php?id_utilisateur=" . $idUser . "&id_publication=" . $idPublication);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Commentaire</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #1572e8, #0a58ca);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .char-counter {
            font-size: 12px;
            margin-top: 5px;
        }
        .char-counter.warning {
            color: #ffc107;
        }
        .char-counter.danger {
            color: #dc3545;
        }
        .btn-submit {
            background: linear-gradient(135deg, #1572e8, #0a58ca);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(21, 114, 232, 0.3);
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-cancel:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px;
        }
        .comment-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #1572e8;
        }
        .comment-author {
            font-weight: 600;
            color: #2c3e50;
        }
        .comment-date {
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier le Commentaire</h4>
            </div>
            <div class="card-body">
                <!-- Informations sur le commentaire -->
                <div class="comment-info">
                    <div class="comment-author">
                        <i class="fas fa-user me-2"></i><?= htmlspecialchars($commentaire['nom']) ?>
                    </div>
                    <div class="comment-date">
                        <i class="fas fa-calendar me-2"></i><?= date('d/m/Y à H:i', strtotime($commentaire['date_commentaire'])) ?>
                    </div>
                </div>

                <!-- Messages d'erreur -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de modification -->
                <form id="editCommentForm" action="" method="POST">
                    <div class="form-group">
                        <label for="commentText"><i class="fas fa-comment me-2"></i>Commentaire</label>
                        <textarea 
                            name="texte" 
                            id="commentText" 
                            class="form-control" 
                            rows="6" 
                            placeholder="Modifiez votre commentaire ici..."
                            maxlength="500"
                        ><?= htmlspecialchars($commentaire['texte']) ?></textarea>
                        <div class="char-counter" id="charCounter">
                            <span id="charCount">0</span>/500 caractères
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $idPublication ?>" class="btn-cancel">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript pour le contrôle de saisie -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            const textarea = document.getElementById('commentText');
            const charCounter = document.getElementById('charCounter');
            const charCount = document.getElementById('charCount');
            const maxChars = 500;

            // Fonction pour mettre à jour le compteur
            function updateCharCounter() {
                const currentLength = textarea.value.length;
                charCount.textContent = currentLength;

                // Changer la couleur selon le nombre de caractères
                if (currentLength > 450) {
                    charCounter.className = 'char-counter danger';
                } else if (currentLength > 400) {
                    charCounter.className = 'char-counter warning';
                } else {
                    charCounter.className = 'char-counter';
                }
            }

            // Initialiser le compteur
            updateCharCounter();

            // Événement sur la saisie
            textarea.addEventListener('input', function() {
                updateCharCounter();
                
                // Limiter automatiquement à 500 caractères
                if (this.value.length > maxChars) {
                    this.value = this.value.substring(0, maxChars);
                    updateCharCounter();
                }
            });

            // Validation du formulaire
            $('#editCommentForm').on('submit', function(e) {
                const texte = textarea.value.trim();
                
                // Validation côté client
                if (texte === '') {
                    e.preventDefault();
                    alert('Le commentaire ne peut pas être vide !');
                    textarea.focus();
                    return false;
                }

                if (texte.length > maxChars) {
                    e.preventDefault();
                    alert('Le commentaire ne peut pas dépasser 500 caractères !');
                    textarea.focus();
                    return false;
                }

                // Empêcher la double soumission
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

                return true;
            });

            // Empêcher la saisie de retours à la ligne multiples
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const currentValue = this.value;
                    const lines = currentValue.split('\n').length;
                    
                    // Limiter à 10 lignes maximum
                    if (lines >= 10 && e.key === 'Enter') {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Focus automatique sur le textarea
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        });
    </script>
</body>
</html>