<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Model/Publication.php';

$controller = new PublicationController();

if (!isset($_GET['id'])) {
    die("ID de publication manquant.");
}

$id_publication = intval($_GET['id']);
$publication = $controller->getPublicationById($id_publication);

if (!$publication) {
    die("Publication introuvable !");
}

// Vérification si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $texte = $_POST['texte'] ?? '';
    $fichier = $_FILES['file'] ?? null;

    $filePath = $publication['fichier'];
    $type_fichier = $publication['type_fichier'];

    // Gestion de l’upload du fichier
    if ($fichier && $fichier['error'] === 0) {
        $uploadDir = __DIR__ . "/../../uploads/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $destination = $uploadDir . basename($fichier['name']);
        move_uploaded_file($fichier['tmp_name'], $destination);
        $filePath = "uploads/" . basename($fichier['name']);
        $type_fichier = pathinfo($fichier['name'], PATHINFO_EXTENSION);
    }

    $pubObj = new Publication(
        $id_publication,
        $publication['id_utilisateur'],
        $texte,
        $filePath,
        $type_fichier,
        new DateTime($publication['date_publication'])
    );

    // Historique avant mise à jour
    $controller->addToHistory($pubObj, $publication['id_utilisateur']);

    // Mise à jour
    $controller->updatePublication($pubObj, $id_publication);

    // Redirection vers addPublications avec l'utilisateur
    header("Location: addPublications.php?id_utilisateur=" . $publication['id_utilisateur']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Publication</title>
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
            padding: 30px;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #1572e8;
            box-shadow: 0 0 0 0.2rem rgba(21, 114, 232, 0.25);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn.primary {
            background: linear-gradient(135deg, #1572e8, #0a58ca);
            color: white;
        }
        .btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(21, 114, 232, 0.3);
        }
        .btn.secondary {
            background: #6c757d;
            color: white;
        }
        .btn.secondary:hover {
            background: #5a6268;
        }
        .file-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #1572e8;
        }
        .file-link {
            color: #1572e8;
            text-decoration: none;
            font-weight: 500;
        }
        .file-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
        .char-counter {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 500;
        }
        .char-counter.warning {
            color: #ffc107;
            font-weight: bold;
        }
        .char-counter.danger {
            color: #dc3545;
            font-weight: bold;
        }
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .validation-alert {
            display: none;
            margin-bottom: 20px;
        }
        .current-file {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<main class="container">
    <div class="card">
        <h2><i class="fas fa-edit me-2"></i>Modifier la Publication</h2>
        
        <!-- Alerte de validation JavaScript -->
        <div id="jsValidationAlert" class="alert alert-danger validation-alert">
            <i class="fas fa-exclamation-triangle me-2"></i><span id="jsValidationMessage"></span>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="formPublication">
            <input type="hidden" name="id_publication" value="<?= $publication['id_publication'] ?>" />
            <input type="hidden" name="id_utilisateur" value="<?= $publication['id_utilisateur'] ?>" />

            <div class="form-group">
                <label for="id_utilisateur_display"><i class="fas fa-user me-2"></i>ID Utilisateur :</label>
                <input type="number" id="id_utilisateur_display" class="form-control" value="<?= $publication['id_utilisateur'] ?>" disabled>
            </div>

            <div class="form-group">
                <label for="texte"><i class="fas fa-edit me-2"></i>Texte de la Publication :</label>
                <textarea name="texte" id="texte" class="form-control" rows="5" placeholder="Modifiez le texte de votre publication..."><?= htmlspecialchars($publication['texte']) ?></textarea>
                <div class="char-counter" id="charCounter">
                    <span id="charCount">0</span>/200 caractères
                </div>
                <small class="form-text text-muted">Maximum 200 caractères autorisés</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-paperclip me-2"></i>Fichier Actuel :</label>
                <?php if (!empty($publication['fichier'])): ?>
                    <div class="current-file">
                        <i class="fas fa-file me-2"></i>
                        <a href="../../<?= htmlspecialchars($publication['fichier']) ?>" target="_blank" class="file-link">
                            Voir le fichier actuel
                        </a>
                        <small class="text-muted d-block mt-1"><?= htmlspecialchars($publication['fichier']) ?></small>
                    </div>
                <?php else: ?>
                    <div class="text-muted">
                        <i class="fas fa-times-circle me-2"></i>Aucun fichier actuellement attaché
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="fileInput"><i class="fas fa-upload me-2"></i>Nouveau Fichier (optionnel) :</label>
                <div class="file-input-wrapper">
                    <input type="file" name="file" id="fileInput" class="form-control">
                    <small class="form-text text-muted">Types autorisés: JPG, PNG, GIF, PDF, TXT - Maximum 10MB</small>
                </div>
                <div id="filePreview" class="file-preview mt-2" style="display: none;">
                    <div class="file-info">
                        <i class="fas fa-file file-icon"></i>
                        <span class="file-name" id="fileName"></span>
                        <span class="file-size" id="fileSize"></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <a href="addPublications.php?id_utilisateur=<?= $publication['id_utilisateur'] ?>" class="btn secondary">
                    <i class="fas fa-arrow-left me-2"></i>Annuler
                </a>
                <button type="submit" class="btn primary">
                    <i class="fas fa-save me-2"></i>Mettre à jour
                </button>
            </div>
        </form>
    </div>
</main>

<!-- JavaScript pour le contrôle de saisie STRICT -->
<script src="assets/js/core/jquery-3.7.1.min.js"></script>
<script>
    class PublicationEditValidator {
        constructor() {
            this.maxTextLength = 200;
            this.maxFileSize = 10 * 1024 * 1024; // 10MB
            this.allowedFileTypes = [
                'image/jpeg', 
                'image/png', 
                'image/gif', 
                'application/pdf', 
                'text/plain'
            ];
            this.init();
        }

        init() {
            this.attachFormValidation();
            this.attachRealTimeValidation();
            this.attachFileValidation();
            this.updateCharCounter(this.getTextarea().value.length);
        }

        getTextarea() {
            return document.getElementById('texte');
        }

        getFileInput() {
            return document.getElementById('fileInput');
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

        getFilePreview() {
            return document.getElementById('filePreview');
        }

        attachFormValidation() {
            const form = document.getElementById('formPublication');
            
            form.addEventListener('submit', (e) => {
                console.log('Form submission intercepted by JavaScript validation');
                
                // EMPÊCHER TOUJOURS la soumission par défaut
                e.preventDefault();
                
                // Valider le formulaire
                if (this.validateForm()) {
                    console.log('Form validation successful, submitting...');
                    // Désactiver le bouton pour éviter les doubles soumissions
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mise à jour...';
                    
                    // Si validation OK, soumettre le formulaire
                    form.submit();
                } else {
                    console.log('Form validation failed');
                }
            });
        }

        validateForm() {
            const textarea = this.getTextarea();
            const fileInput = this.getFileInput();
            const texte = textarea.value.trim();
            
            // Réinitialiser les états de validation
            this.resetValidationStates();
            
            let isValid = true;
            
            // Validation du texte (obligatoire)
            if (texte === '') {
                this.showError('Le texte de la publication est obligatoire !', textarea);
                isValid = false;
            } else if (texte.length > this.maxTextLength) {
                this.showError(`Le texte ne peut pas dépasser ${this.maxTextLength} caractères ! Actuellement: ${texte.length} caractères`, textarea);
                isValid = false;
            }
            
            // Validation du fichier (optionnel mais contrôlé)
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                if (file.size > this.maxFileSize) {
                    this.showError(`Le fichier est trop volumineux. Taille maximum: ${this.formatFileSize(this.maxFileSize)}`, fileInput);
                    isValid = false;
                }
                
                if (!this.allowedFileTypes.includes(file.type)) {
                    this.showError('Type de fichier non autorisé. Types acceptés: JPG, PNG, GIF, PDF, TXT', fileInput);
                    isValid = false;
                }
            }
            
            if (isValid) {
                this.showSuccess('Validation réussie ! Mise à jour en cours...');
            }
            
            return isValid;
        }

        attachRealTimeValidation() {
            const textarea = this.getTextarea();
            
            textarea.addEventListener('input', () => {
                const charCount = textarea.value.length;
                this.updateCharCounter(charCount);
                
                // Validation visuelle en temps réel
                if (charCount > this.maxTextLength) {
                    textarea.classList.add('is-invalid');
                } else {
                    textarea.classList.remove('is-invalid');
                }
                
                // Limiter automatiquement à 200 caractères
                if (charCount > this.maxTextLength) {
                    textarea.value = textarea.value.substring(0, this.maxTextLength);
                    this.updateCharCounter(this.maxTextLength);
                }
            });
        }

        attachFileValidation() {
            const fileInput = this.getFileInput();
            const filePreview = this.getFilePreview();
            
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    
                    // Afficher l'aperçu du fichier
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('fileSize').textContent = `(${this.formatFileSize(file.size)})`;
                    filePreview.style.display = 'block';
                    
                    // Validation immédiate du fichier
                    if (file.size > this.maxFileSize) {
                        this.showError(`Fichier trop volumineux. Maximum ${this.formatFileSize(this.maxFileSize)} autorisé.`, fileInput);
                        fileInput.value = '';
                        filePreview.style.display = 'none';
                    } else if (!this.allowedFileTypes.includes(file.type)) {
                        this.showError('Type de fichier non autorisé', fileInput);
                        fileInput.value = '';
                        filePreview.style.display = 'none';
                    }
                } else {
                    filePreview.style.display = 'none';
                }
            });
        }

        updateCharCounter(charCount) {
            const counter = this.getCharCounter();
            const countSpan = this.getCharCount();
            
            countSpan.textContent = charCount;
            
            // Changer la couleur selon le nombre de caractères
            if (charCount > 180) {
                counter.className = 'char-counter danger';
            } else if (charCount > 150) {
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
            this.getFileInput().classList.remove('is-invalid');
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

        showSuccess(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <strong>Succès:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const form = document.getElementById('formPublication');
            form.parentNode.insertBefore(alertDiv, form);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le validateur
        new PublicationEditValidator();
        console.log('PublicationEditValidator initialized - Pure JavaScript validation active');
        
        // Focus automatique sur le textarea
        const textarea = document.getElementById('texte');
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        
        // Initialiser le compteur avec la valeur actuelle
        const initialLength = textarea.value.length;
        document.getElementById('charCount').textContent = initialLength;
        
        // Mettre à jour le style du compteur
        const counter = document.getElementById('charCounter');
        if (initialLength > 180) {
            counter.className = 'char-counter danger';
        } else if (initialLength > 150) {
            counter.className = 'char-counter warning';
        }
    });
</script>
</body>
</html>