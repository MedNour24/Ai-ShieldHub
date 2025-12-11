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
        // CORRECTION : Ordre correct des paramètres du constructeur Commentaire
        $commentaireObj = new Commentaire(
            $idCommentaire,
            $idPublication,  // CORRECTION : id_publication en 2ème paramètre
            $idUser,         // CORRECTION : id_utilisateur en 3ème paramètre  
            trim($texte),
            new DateTime($commentaire['date_commentaire'])
        );
        
        // MODIFIÉ : Utiliser updateCommentaire sans le 2ème paramètre (l'ID est déjà dans l'objet)
        $commentaireController->updateCommentaire($commentaireObj);
        
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
            font-weight: bold;
        }
        .char-counter.danger {
            color: #dc3545;
            font-weight: bold;
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
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .validation-alert {
            display: none;
            margin-top: 10px;
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

                <!-- Messages d'erreur PHP -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Alerte de validation JavaScript -->
                <div id="jsValidationAlert" class="alert alert-danger validation-alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><span id="jsValidationMessage"></span>
                </div>

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
                        ><?= htmlspecialchars($commentaire['contenu'] ?? $commentaire['texte']) ?></textarea>
                        <div class="char-counter" id="charCounter">
                            <span id="charCount">0</span>/500 caractères
                        </div>
                        <small class="form-text text-muted">Maximum 500 caractères autorisés</small>
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

    <!-- JavaScript pour le contrôle de saisie STRICT -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script>
        class CommentValidator {
            constructor() {
                this.maxChars = 500;
                this.init();
            }

            init() {
                this.attachFormValidation();
                this.attachRealTimeValidation();
                this.updateCharCounter(this.getTextarea().value.length);
            }

            getTextarea() {
                return document.getElementById('commentText');
            }

            getCharCounter() {
                return document.getElementById('charCounter');
            }

            getCharCount() {
                return document.getElementById('charCount');
            }

            getValidationAlert() {
                return document.getElementById('jsValidationAlert');
            }

            getValidationMessage() {
                return document.getElementById('jsValidationMessage');
            }

            attachFormValidation() {
                const form = document.getElementById('editCommentForm');
                
                form.addEventListener('submit', (e) => {
                    console.log('Form submission intercepted by JavaScript validation');
                    
                    // EMPÊCHER TOUJOURS la soumission par défaut
                    e.preventDefault();
                    
                    // Valider le formulaire
                    if (this.validateForm()) {
                        console.log('Form validation successful, submitting...');
                        // Si validation OK, soumettre le formulaire
                        form.submit();
                    } else {
                        console.log('Form validation failed');
                    }
                });
            }

            validateForm() {
                const textarea = this.getTextarea();
                const texte = textarea.value.trim();
                
                // Réinitialiser les états de validation
                this.resetValidationStates();
                
                let isValid = true;
                
                // Validation du texte (obligatoire)
                if (texte === '') {
                    this.showError('Le commentaire ne peut pas être vide !', textarea);
                    isValid = false;
                } else if (texte.length > this.maxChars) {
                    this.showError(`Le commentaire ne peut pas dépasser ${this.maxChars} caractères ! Actuellement: ${texte.length} caractères`, textarea);
                    isValid = false;
                }
                
                return isValid;
            }

            attachRealTimeValidation() {
                const textarea = this.getTextarea();
                
                textarea.addEventListener('input', () => {
                    const charCount = textarea.value.length;
                    this.updateCharCounter(charCount);
                    
                    // Validation visuelle en temps réel
                    if (charCount > this.maxChars) {
                        textarea.classList.add('is-invalid');
                    } else {
                        textarea.classList.remove('is-invalid');
                    }
                    
                    // Limiter automatiquement à 500 caractères
                    if (charCount > this.maxChars) {
                        textarea.value = textarea.value.substring(0, this.maxChars);
                        this.updateCharCounter(this.maxChars);
                    }
                });
            }

            updateCharCounter(charCount) {
                const counter = this.getCharCounter();
                const countSpan = this.getCharCount();
                
                countSpan.textContent = charCount;
                
                // Changer la couleur selon le nombre de caractères
                if (charCount > 450) {
                    counter.className = 'char-counter danger';
                } else if (charCount > 400) {
                    counter.className = 'char-counter warning';
                } else {
                    counter.className = 'char-counter';
                }
            }

            resetValidationStates() {
                // Cacher l'alerte de validation
                this.getValidationAlert().style.display = 'none';
                
                // Réinitialiser les styles des champs
                this.getTextarea().classList.remove('is-invalid');
            }

            showError(message, element = null) {
                const alert = this.getValidationAlert();
                const messageSpan = this.getValidationMessage();
                
                messageSpan.textContent = message;
                alert.style.display = 'block';
                
                if (element) {
                    element.classList.add('is-invalid');
                    element.focus();
                    
                    // Retirer la classe après correction
                    element.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                }
                
                // Faire défiler jusqu'à l'alerte
                alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le validateur
            new CommentValidator();
            console.log('CommentValidator initialized - Pure JavaScript validation active');
            
            // Focus automatique sur le textarea
            const textarea = document.getElementById('commentText');
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            
            // Initialiser le compteur avec la valeur actuelle
            const initialLength = textarea.value.length;
            document.getElementById('charCount').textContent = initialLength;
            
            // Mettre à jour le style du compteur
            const counter = document.getElementById('charCounter');
            if (initialLength > 450) {
                counter.className = 'char-counter danger';
            } else if (initialLength > 400) {
                counter.className = 'char-counter warning';
            }
        });

        // Empêcher la saisie de retours à la ligne multiples (optionnel)
        document.getElementById('commentText').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const currentValue = this.value;
                const lines = currentValue.split('\n').length;
                
                // Limiter à 10 lignes maximum
                if (lines >= 10) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>