<?php
require_once __DIR__ . '/../../Controller/CommentaireController.php';
require_once __DIR__ . '/../../Model/Commentaire.php';
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
$idPublication = isset($_GET['id_publication']) ? intval($_GET['id_publication']) : 0;

if ($idUser <= 0) die("ID utilisateur non spécifié !");
if ($idPublication <= 0) die("ID publication non spécifié !");

$commentController = new CommentaireController();
$pubController = new PublicationController();

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
// CORRECTION : Utiliser getCommentairesByPublication() au lieu de listCommentairesByPublication()
$commentaires = $commentController->getCommentairesByPublication($idPublication);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - Comments Management</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    
    <style>
      .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: center;
      }
      .btn-action {
        padding: 4px 8px;
        font-size: 12px;
      }
      .loading-spinner {
        text-align: center;
        padding: 20px;
      }
      .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
      }
      .comment-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #1572e8;
      }
      .comment-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
      }
      .comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1572e8, #0a58ca);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: white;
        font-size: 16px;
      }
      .comment-author {
        font-weight: 600;
        color: #2c3e50;
        font-size: 16px;
      }
      .comment-date {
        color: #6c757d;
        font-size: 12px;
      }
      .comment-content {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 15px;
        white-space: pre-line;
      }
      .comment-actions {
        display: flex;
        gap: 15px;
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
      }
      .action-btn {
        display: flex;
        align-items: center;
        color: #6c757d;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
      }
      .action-btn:hover {
        color: #1572e8;
      }
      .form-group {
        margin-bottom: 20px;
        position: relative;
      }
      .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
      }
      .btn-comment {
        background: linear-gradient(135deg, #1572e8, #0a58ca);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
      }
      .btn-comment:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(21, 114, 232, 0.3);
      }
      .btn-comment:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
      }
      .char-counter {
        text-align: right;
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
      }
      .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
      }
      .publication-preview {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        border-left: 4px solid #1572e8;
      }
      .back-link {
        color: #1572e8;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin-bottom: 20px;
        font-weight: 500;
      }
      .back-link:hover {
        color: #0a58ca;
      }
      .user-comment-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
      }
      .btn-edit-comment {
        background: rgba(21, 114, 232, 0.1);
        border: 1px solid #1572e8;
        color: #1572e8;
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
        background: #1572e8;
        color: white;
        transform: translateY(-2px);
      }
      .btn-delete-comment {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid #dc3545;
        color: #dc3545;
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
        background: #dc3545;
        color: white;
        transform: translateY(-2px);
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo nav-link" data-page="dashboard">
              <img
                src="assets/img/kaiadmin/logo_light.svg"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item">
                <a href="index.php" class="nav-link" data-page="dashboard">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Components</h4>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-layer-group"></i>
                  <p>Base</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="base">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Avatars</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Buttons</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#forms">
                  <i class="fas fa-pen-square"></i>
                  <p>Tournament Management</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="forms">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#" class="nav-link" data-page="tournoi">
                        <span class="sub-item">Tournoi</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#tables">
                  <i class="fas fa-table"></i>
                  <p>Tables</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="tables">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Basic Table</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item active">
                <a data-bs-toggle="collapse" href="#maps">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Publications</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse show" id="maps">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="addPublications.php?id_utilisateur=<?= $idUser ?>" class="nav-link" data-page="publications">
                        <span class="sub-item">PubAdmin</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" class="nav-link" data-page="pubuser">
                        <span class="sub-item">PubUser</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="index.php" class="logo nav-link" data-page="dashboard">
                <img
                  src="assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      <img
                        src="assets/img/profile.jpg"
                        alt="..."
                        class="avatar-img rounded-circle"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold">Admin</span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4>Admin</h4>
                            <p class="text-muted">admin@example.com</p>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">My Profile</a>
                        <a class="dropdown-item" href="#">Account Setting</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Logout</a>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            <!-- Comments Page Content -->
            <div class="page-content active">
              <div
                class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
              >
                <div>
                  <h3 class="fw-bold mb-3">Comments Management</h3>
                  <h6 class="op-7 mb-2">Manage comments for this publication</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                  <span class="badge badge-primary">
                    Total Comments: <?= count($commentaires) ?>
                  </span>
                </div>
              </div>

              <!-- Back Link -->
              <a href="addPublications.php?id_utilisateur=<?= $idUser ?>" class="back-link">
                <i class="fas fa-arrow-left me-2"></i>Back to Publications
              </a>

              <!-- Publication Info -->
              <?php if ($publication): ?>
              <div class="publication-preview">
                <h5 style="color: #2c3e50; margin-bottom: 10px;">
                  <i class="fas fa-file-alt me-2"></i>Publication
                </h5>
                <p style="color: #495057; line-height: 1.6;">
                  <?= nl2br(htmlspecialchars($publication['texte'])) ?>
                </p>
                <?php if(!empty($publication['fichier'])): ?>
                  <div class="mt-2">
                    <a href="../../<?= htmlspecialchars($publication['fichier']) ?>" target="_blank" class="file-link" style="color: #1572e8;">
                      <i class="fas fa-file me-2"></i>View attached file
                    </a>
                  </div>
                <?php endif; ?>
              </div>
              <?php endif; ?>

              <!-- Comment Form -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">Add New Comment</div>
                      </div>
                    </div>
                    <div class="card-body">
                      <form id="formComment" action="" method="POST">
                        <div class="form-group">
                          <label for="commentContent"><i class="fas fa-edit me-2"></i>Your Comment</label>
                          <textarea name="contenu" id="commentContent" class="form-control" rows="4" placeholder="Write your comment here..."></textarea>
                          <div class="char-counter" id="charCounter">0/500 characters</div>
                          <div class="error-message" id="commentError"></div>
                        </div>
                        
                        <button type="submit" class="btn-comment" id="submitButton">
                          <i class="fas fa-paper-plane me-2"></i>Post Comment
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Comments List -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">All Comments</div>
                        <div class="card-tools">
                          <button class="btn btn-sm btn-light" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <?php if(!empty($commentaires)): ?>
                        <?php foreach($commentaires as $commentaire): ?>
                          <div class="comment-card">
                            <div class="comment-header">
                              <div class="comment-avatar">
                                <i class="fas fa-user"></i>
                              </div>
                              <div>
                                <div class="comment-author"><?= htmlspecialchars($commentaire['name']) ?></div>
                                <div class="comment-date"><?= date('M j, Y \a\t g:i A', strtotime($commentaire['date_commentaire'])) ?></div>
                              </div>
                            </div>
                            
                            <div class="comment-content">
                              <?= nl2br(htmlspecialchars($commentaire['contenu'] ?? $commentaire['texte'])) ?>
                            </div>
                            
                            <!-- Afficher les boutons Edit/Delete pour tous les commentaires (admin peut tout modifier/supprimer) -->
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
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="empty-state">
                          <i class="fas fa-comment-slash fa-3x mb-3 text-muted"></i>
                          <h5>No Comments Yet</h5>
                          <p class="text-muted">Be the first to comment on this publication!</p>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>

    <script>
      // Validation du formulaire de commentaire
      class CommentValidator {
          constructor() {
              this.commentTextarea = document.getElementById('commentContent');
              this.charCounter = document.getElementById('charCounter');
              this.commentError = document.getElementById('commentError');
              this.submitButton = document.getElementById('submitButton');
              this.maxChars = 500;
              
              this.init();
          }

          init() {
              // Événements de validation
              this.commentTextarea.addEventListener('input', this.updateCharCounter.bind(this));
              this.commentTextarea.addEventListener('blur', this.validateForm.bind(this));
              
              // Validation à la soumission
              document.getElementById('formComment').addEventListener('submit', this.handleSubmit.bind(this));
              
              // Validation initiale
              this.updateCharCounter();
              this.validateForm();
          }

          updateCharCounter() {
              const charCount = this.commentTextarea.value.length;
              
              // Mettre à jour le compteur
              this.charCounter.textContent = `${charCount}/${this.maxChars} characters`;
              
              // Changer la couleur selon le nombre de caractères
              if (charCount > this.maxChars * 0.8) {
                  this.charCounter.style.color = '#dc3545';
              } else {
                  this.charCounter.style.color = '#6c757d';
              }
              
              // Limiter automatiquement à 500 caractères
              if (charCount > this.maxChars) {
                  this.commentTextarea.value = this.commentTextarea.value.substring(0, this.maxChars);
                  this.updateCharCounter();
              }
              
              // Valider en temps réel
              this.validateForm();
          }

          validateForm() {
              const content = this.commentTextarea.value.trim();
              let isValid = true;
              
              // Réinitialiser les erreurs
              this.commentError.style.display = 'none';
              this.commentError.textContent = '';
              this.commentTextarea.style.borderColor = '#ced4da';
              
              // Validation du contenu vide
              if (content === '') {
                  this.commentError.textContent = 'Please enter your comment before posting.';
                  this.commentError.style.display = 'block';
                  this.commentTextarea.style.borderColor = '#dc3545';
                  isValid = false;
              }
              
              // Validation de la longueur
              if (content.length > this.maxChars) {
                  this.commentError.textContent = `Comment cannot exceed ${this.maxChars} characters.`;
                  this.commentError.style.display = 'block';
                  this.commentTextarea.style.borderColor = '#dc3545';
                  isValid = false;
              }
              
              // Activer/désactiver le bouton de soumission
              this.submitButton.disabled = !isValid;
              
              return isValid;
          }

          handleSubmit(event) {
              if (!this.validateForm()) {
                  event.preventDefault();
                  this.commentTextarea.focus();
                  return;
              }
              
              // Empêcher la double soumission
              this.submitButton.disabled = true;
              this.submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Posting...';
          }
      }

      // Auto-resize textarea
      const commentTextarea = document.getElementById('commentContent');
      commentTextarea.addEventListener('input', function() {
          this.style.height = 'auto';
          this.style.height = (this.scrollHeight) + 'px';
      });

      // Confirmation pour la suppression
      document.querySelectorAll('.btn-delete-comment').forEach(button => {
          button.addEventListener('click', function(e) {
              if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
                  e.preventDefault();
              }
          });
      });

      // Page Navigation
      $('.nav-link[data-page]').on('click', function(e) {
          e.preventDefault();
          const page = $(this).data('page');
          
          // Update active state in sidebar
          $('.nav-item').removeClass('active');
          $(this).closest('.nav-item').addClass('active');
          
          // Show corresponding page content
          $('.page-content').removeClass('active');
          $(`#${page}-page`).addClass('active');
      });

      // Initialiser la validation au chargement
      document.addEventListener('DOMContentLoaded', function() {
          new CommentValidator();
      });
    </script>
  </body>
</html>