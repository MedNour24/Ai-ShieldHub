<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Model/Publication.php';
require_once __DIR__ . '/../../Controller/ReactionController.php';
require_once __DIR__ . '/../../Model/Reaction.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
if ($idUser <= 0) die("ID utilisateur non spécifié !");

$pubController = new PublicationController();
$reactionController = new ReactionController();

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

    header("Location: addPublications.php?id_utilisateur=".$idUser);
    exit();
}

// ---------- PAGINATION ----------
$limit = 10;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

// Récupérer le total pour la pagination
$total = $pubController->countPublications();
$totalPages = ceil($total/$limit);

// Récupérer les publications pour la page actuelle
$publications = $pubController->listAdminPublications($limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - User Management Dashboard</title>
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
      .page-content {
        display: none;
      }
      .page-content.active {
        display: block;
      }
      .publication-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #1572e8;
      }
      .publication-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
      }
      .publication-avatar {
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
      .publication-author {
        font-weight: 600;
        color: #2c3e50;
        font-size: 16px;
      }
      .publication-date {
        color: #6c757d;
        font-size: 12px;
      }
      .publication-content {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 15px;
        white-space: pre-line;
      }
      .publication-file {
        margin-bottom: 15px;
      }
      .file-link {
        display: inline-flex;
        align-items: center;
        color: #1572e8;
        text-decoration: none;
        font-weight: 500;
      }
      .file-link:hover {
        color: #0a58ca;
      }
      
      /* Système de réactions */
      .reaction-buttons {
        display: flex;
        gap: 15px;
        align-items: center;
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
      }
      
      .reaction-btn {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        color: #6c757d;
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
        background: #e9ecef;
        border-color: #1572e8;
        color: #1572e8;
      }
      
      .reaction-btn.active.liked {
        background: linear-gradient(135deg, #1572e8, #0a58ca);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 15px rgba(21, 114, 232, 0.3);
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
        color: #6c757d;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
      }
      
      .action-btn:hover {
        color: #1572e8;
      }
      
      .action-btn i {
        margin-right: 8px;
      }

      .form-group {
        margin-bottom: 20px;
      }
      .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
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
        padding: 12px 16px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        color: #6c757d;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
      }
      .file-input-label:hover {
        background: #e9ecef;
        border-color: #1572e8;
      }
      .btn-publish {
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
      .btn-publish:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(21, 114, 232, 0.3);
      }
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
        border-radius: 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        text-decoration: none;
        margin: 0 5px;
        transition: all 0.3s ease;
      }
      .pagination a:hover {
        background: #1572e8;
        border-color: #1572e8;
        color: white;
      }
      .pagination a.active {
        background: #1572e8;
        color: white;
        border-color: #1572e8;
      }
      /* Styles pour la page PubUser */
      .publication-content-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      .content-box {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        padding: 15px;
        border-radius: 8px;
        background: #f8f9fa;
      }
      .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
      }
      .page-pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 20px 0 0 0;
        justify-content: center;
      }
      .page-item.active .page-link {
        background-color: #1572e8;
        border-color: #1572e8;
        color: white;
      }
      .page-link {
        color: #495057;
        border: 1px solid #dee2e6;
        margin: 0 2px;
        border-radius: 4px;
        padding: 8px 12px;
        text-decoration: none;
      }
      .page-link:hover {
        color: #1572e8;
        background-color: #e9ecef;
        border-color: #dee2e6;
      }
      .search-box {
        max-width: 300px;
      }
      /* Styles pour les commentaires et réactions dans la modal */
      .comment-item, .reaction-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 3px solid #1572e8;
      }
      .reaction-item {
        border-left: 3px solid #28a745;
      }
      .comment-avatar-sm, .reaction-avatar-sm {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1572e8, #0a58ca);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
      }
      .reaction-avatar-sm {
        background: linear-gradient(135deg, #28a745, #20c997);
      }
      .comment-author, .reaction-author {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
      }
      .comment-content, .reaction-type {
        font-size: 14px;
        color: #495057;
        line-height: 1.4;
        margin-top: 8px;
      }
      .reaction-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 12px;
        background: #e9ecef;
      }
      .reaction-type.like {
        background: #d1ecf1;
        color: #0c5460;
      }
      .reaction-type.dislike {
        background: #f8d7da;
        color: #721c24;
      }
      .comments-section, .reactions-section {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
        margin-top: 20px;
      }
      .btn-delete-comment-modal {
        padding: 2px 6px;
        font-size: 10px;
      }
      .comment-header-modal, .reaction-header-modal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
      }
      
      /* Indicateur de réactions dans le tableau */
      .reactions-indicator {
        display: flex;
        gap: 10px;
        font-size: 12px;
      }
      .likes-indicator {
        color: #1572e8;
      }
      .dislikes-indicator {
        color: #dc3545;
      }
      
      /* Styles pour les réactions admin dans la modal */
      .admin-reactions-section {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
        margin-top: 20px;
      }
      .admin-reaction-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
      }
      .admin-reaction-btn {
        padding: 8px 16px;
        border-radius: 20px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #495057;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
      }
      .admin-reaction-btn:hover {
        background: #e9ecef;
      }
      .admin-reaction-btn.active.like {
        background: linear-gradient(135deg, #1572e8, #0a58ca);
        color: white;
        border-color: transparent;
      }
      .admin-reaction-btn.active.dislike {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border-color: transparent;
      }
      .reactions-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
      }
      .reaction-stat-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
      }
      .reaction-stat-card.likes {
        border-left: 4px solid #1572e8;
      }
      .reaction-stat-card.dislikes {
        border-left: 4px solid #dc3545;
      }
      .reaction-stat-count {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
      }
      .reaction-stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
      }

      /* Styles pour la validation */
      .is-invalid {
          border-color: #dc3545 !important;
          box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
      }

      .is-valid {
          border-color: #198754 !important;
          box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
      }

      .char-counter {
          font-weight: 500;
          margin-top: 5px;
      }

      /* Style pour l'aperçu du fichier */
      .file-preview {
          margin-top: 10px;
          padding: 10px;
          background: #f8f9fa;
          border-radius: 5px;
          border-left: 4px solid #1572e8;
      }

      .file-preview .file-info {
          display: flex;
          align-items: center;
          gap: 10px;
      }

      .file-preview .file-icon {
          font-size: 20px;
          color: #1572e8;
      }

      .file-preview .file-name {
          font-weight: 500;
          color: #2c3e50;
      }

      .file-preview .file-size {
          font-size: 12px;
          color: #6c757d;
      }

      /* Styles pour les totaux de réactions */
      .reactions-totals {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 10px;
      }
      .reaction-total-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .reaction-total-card.likes {
        border-top: 4px solid #1572e8;
      }
      .reaction-total-card.dislikes {
        border-top: 4px solid #dc3545;
      }
      .reaction-total-count {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
      }
      .reaction-total-label {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
      }
      .reaction-total-icon {
        font-size: 20px;
        margin-bottom: 8px;
      }
      .reaction-total-icon.likes {
        color: #1572e8;
      }
      .reaction-total-icon.dislikes {
        color: #dc3545;
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar - IDENTIQUE À L'INDEX.PHP -->
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
                      <a href="addPublications.php?id_utilisateur=<?= $idUser ?>" class="nav-link active" data-page="publications">
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
            <!-- Publications Page Content -->
            <div id="publications-page" class="page-content active">
              <div
                class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
              >
                <div>
                  <h3 class="fw-bold mb-3">Publications Management</h3>
                  <h6 class="op-7 mb-2">Manage all community publications</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                  <span class="badge badge-primary">
                    Total Publications: <?= $total ?>
                  </span>
                </div>
              </div>

              <!-- Publication Form -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">Create New Publication</div>
                      </div>
                    </div>
                    <div class="card-body">
                      <form id="formPublication" action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                          <label for="postText"><i class="fas fa-edit me-2"></i>Publication Content</label>
                          <textarea name="texte" id="postText" class="form-control" rows="6" placeholder="Write your publication content here..."></textarea>
                          <small class="form-text text-muted">Maximum 200 characters allowed</small>
                        </div>
                        
                        <div class="form-group">
                          <label for="fileInput"><i class="fas fa-paperclip me-2"></i>Attach File</label>
                          <div class="file-input-wrapper">
                            <input type="file" name="file" id="fileInput">
                            <div class="file-input-label">
                              <i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload (optional)
                            </div>
                          </div>
                        </div>
                        
                        <button type="submit" class="btn-publish">
                          <i class="fas fa-paper-plane me-2"></i>Publish
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Publications List -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">All Publications</div>
                        <div class="card-tools">
                          <button class="btn btn-sm btn-light" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <?php if(!empty($publications)): ?>
                        <?php foreach($publications as $p): 
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
                                  <i class="fas fa-file me-2"></i>View attached file
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
                              
                              <a href="editPublication.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn">
                                <i class="fas fa-edit me-2"></i>Edit
                              </a>
                              <a href="deletePublication.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this publication?')">
                                <i class="fas fa-trash me-2"></i>Delete
                              </a>
                              <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" class="action-btn">
                                <i class="fas fa-comments me-2"></i>Comments
                              </a>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="empty-state">
                          <i class="fas fa-newspaper fa-3x mb-3 text-muted"></i>
                          <h5>No Publications Yet</h5>
                          <p class="text-muted">Start by creating the first publication above!</p>
                        </div>
                      <?php endif; ?>
                      
                      <!-- Pagination -->
                      <?php if($totalPages > 1): ?>
                        <div class="pagination">
                          <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?id_utilisateur=<?= $idUser ?>&page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
                          <?php endfor; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PubUser Page Content -->
            <div id="pubuser-page" class="page-content">
              <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                  <h3 class="fw-bold mb-3">User Publications</h3>
                  <h6 class="op-7 mb-2">Publications from users with role "user"</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                  <button class="btn btn-sm btn-light" id="refresh-pubuser">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                  </button>
                </div>
              </div>

              <!-- User Publications List -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">All User Publications</div>
                        <div class="card-tools">
                          <div class="input-group input-group-sm search-box">
                            <input type="text" id="search-pubuser" class="form-control" placeholder="Search publications...">
                            <button class="btn btn-light" type="button">
                              <i class="fas fa-search"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="pubuser-table">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>Author</th>
                              <th>Content</th>
                              <th>Date</th>
                              <th>File</th>
                              <th class="text-center">Actions</th>
                            </tr>
                          </thead>
                          <tbody id="pubuser-tbody">
                            <tr class="loading-spinner">
                              <td colspan="6" class="text-center">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Loading publications...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!-- Pagination -->
                      <div class="page-pagination mt-3" id="pubuser-pagination">
                        <!-- Pagination will be generated by JavaScript -->
                      </div>
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
          }

          async handleReaction(event) {
              event.preventDefault();
              
              const button = event.currentTarget;
              const publicationId = button.dataset.publicationId;
              const userId = button.dataset.userId;
              const reactionType = button.dataset.reactionType;
              
              console.log('Reaction clicked:', { publicationId, userId, reactionType });
              
              // Désactiver le bouton pendant le traitement
              const originalHTML = button.innerHTML;
              button.disabled = true;
              button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
              
              try {
                  const response = await this.sendReaction(publicationId, userId, reactionType);
                  console.log('Server response:', response);
                  
                  if (response.success) {
                      this.updateReactionUI(publicationId, response);
                  } else {
                      console.error('Erreur:', response.message);
                      alert('Erreur: ' + (response.message || 'Erreur lors de l\'enregistrement de la réaction'));
                  }
              } catch (error) {
                  console.error('Erreur:', error);
                  alert('Erreur de connexion au serveur');
              } finally {
                  button.disabled = false;
                  button.innerHTML = originalHTML;
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

              if (!response.ok) {
                  throw new Error('HTTP error ' + response.status);
              }

              return await response.json();
          }

          updateReactionUI(publicationId, data) {
              const likeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="like"]`);
              const dislikeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="dislike"]`);
              const likeCount = document.querySelector(`.like-count[data-publication-id="${publicationId}"]`);
              const dislikeCount = document.querySelector(`.dislike-count[data-publication-id="${publicationId}"]`);

              console.log('Updating UI:', { publicationId, data, likeBtn, dislikeBtn, likeCount, dislikeCount });

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

      // Contrôle de saisie amélioré
      function showError(message, element = null) {
          // Créer une alerte plus stylée
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-danger alert-dismissible fade show';
          alertDiv.innerHTML = `
              <strong>Erreur:</strong> ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          
          // Insérer avant le formulaire
          const form = document.getElementById('formPublication');
          form.parentNode.insertBefore(alertDiv, form);
          
          // Focus sur l'élément problématique si spécifié
          if (element) {
              element.focus();
              element.classList.add('is-invalid');
              
              // Retirer la classe après correction
              element.addEventListener('input', function() {
                  this.classList.remove('is-invalid');
              }, { once: true });
          }
          
          // Auto-supprimer après 5 secondes
          setTimeout(() => {
              if (alertDiv.parentNode) {
                  alertDiv.remove();
              }
          }, 5000);
      }

      function validateForm() {
          const textarea = document.getElementById('postText');
          const texte = textarea.value.trim();
          
          // Supprimer les anciennes alertes
          document.querySelectorAll('.alert').forEach(alert => alert.remove());
          
          // Validation du texte
          if (texte === '') {
              showError('Le texte de la publication ne peut pas être vide !', textarea);
              return false;
          }
          
          if (texte.length > 200) {
              showError('Le texte ne peut pas dépasser 200 caractères ! Actuellement: ' + texte.length + ' caractères', textarea);
              return false;
          }
          
          // Validation du fichier (optionnel)
          const fileInput = document.getElementById('fileInput');
          if (fileInput.files.length > 0) {
              const file = fileInput.files[0];
              const maxSize = 10 * 1024 * 1024; // 10MB
              const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
              
              if (file.size > maxSize) {
                  showError('Le fichier est trop volumineux. Taille maximum: 10MB', fileInput);
                  return false;
              }
              
              if (!allowedTypes.includes(file.type)) {
                  showError('Type de fichier non autorisé. Types acceptés: JPG, PNG, GIF, PDF, TXT', fileInput);
                  return false;
              }
          }
          
          return true;
      }

      $(document).ready(function() {
        // Initialiser le système de réactions
        new ReactionSystem();
        console.log('Reaction system initialized');

        // Gestion de l'affichage du nom du fichier
        const fileInput = document.getElementById('fileInput');
        const fileInputLabel = document.querySelector('.file-input-label');
        
        if (fileInput && fileInputLabel) {
          fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
              fileInputLabel.innerHTML = '<i class="fas fa-file me-2"></i>' + this.files[0].name;
              fileInputLabel.style.background = '#e3f2fd';
              fileInputLabel.style.borderColor = '#1572e8';
              fileInputLabel.style.color = '#1572e8';
            } else {
              fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload (optional)';
              fileInputLabel.style.background = '#f8f9fa';
              fileInputLabel.style.borderColor = '#dee2e6';
              fileInputLabel.style.color = '#6c757d';
            }
          });
        }

        // Contrôle JS du formulaire
        const form = document.getElementById('formPublication');
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-resize textarea
        const textarea = document.getElementById('postText');
        textarea.addEventListener('input', function() {
          const charCount = this.value.length;
          const maxChars = 200;
          
          // Mettre à jour le compteur
          let counter = this.parentElement.querySelector('.char-counter');
          if (!counter) {
              counter = document.createElement('small');
              counter.className = 'form-text char-counter';
              this.parentElement.appendChild(counter);
          }
          
          counter.innerHTML = `${charCount}/${maxChars} caractères`;
          
          // Changer la couleur selon le nombre de caractères
          if (charCount > maxChars) {
              counter.style.color = '#dc3545';
              this.classList.add('is-invalid');
          } else if (charCount > maxChars * 0.8) {
              counter.style.color = '#ffc107';
              this.classList.remove('is-invalid');
          } else {
              counter.style.color = '#6c757d';
              this.classList.remove('is-invalid');
          }
          
          // Empêcher de dépasser la limite
          if (charCount > maxChars) {
              this.value = this.value.substring(0, maxChars);
              counter.innerHTML = `${maxChars}/${maxChars} caractères (limite atteinte)`;
          }
        });

        // Validation du fichier
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const maxSize = 10 * 1024 * 1024;
                    
                    if (file.size > maxSize) {
                        showError('Fichier trop volumineux. Maximum 10MB autorisés.', this);
                        this.value = ''; // Réinitialiser le fichier
                    }
                }
            });
        }

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
          
          // Load pubuser publications if switching to pubuser page
          if (page === 'pubuser') {
            loadPubUserPublications();
          }
        });

        // Refresh pubuser publications
        $('#refresh-pubuser').on('click', function() {
          loadPubUserPublications();
        });

        // Search functionality for pubuser
        $('#search-pubuser').on('input', function() {
          const searchTerm = $(this).val().toLowerCase();
          filterPubUserPublications(searchTerm);
        });

        let allPubUserPublications = [];

        function loadPubUserPublications(page = 1) {
          $.ajax({
            url: '../../controller/PublicationController.php',
            type: 'GET',
            data: { 
              action: 'listUserPublications',
              page: page,
              limit: 10
            },
            dataType: 'json',
            beforeSend: function() {
              $('#pubuser-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="6" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading publications...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              if (response.success) {
                allPubUserPublications = response.data;
                displayPubUserPublications(response.data, page);
                updatePubUserPagination(response.total, response.currentPage, response.totalPages);
              } else {
                showError('Failed to load publications: ' + (response.message || 'Unknown error'));
              }
            },
            error: function(xhr, status, error) {
              showError('Error loading publications: ' + error);
            }
          });
        }

        function displayPubUserPublications(publications, currentPage = 1) {
          const tbody = $('#pubuser-tbody');
          tbody.empty();

          if (publications.length === 0) {
            tbody.html(`
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div class="empty-state">
                    <i class="fas fa-newspaper fa-3x mb-3 text-muted"></i>
                    <h5>No Publications Found</h5>
                    <p class="text-muted">No user publications available at the moment.</p>
                  </div>
                </td>
              </tr>
            `);
            return;
          }

          publications.forEach(pub => {
            const fileBadge = pub.fichier 
              ? `<span class="badge badge-info"><i class="fas fa-file me-1"></i>Has file</span>`
              : `<span class="badge badge-secondary">No file</span>`;

            const contentPreview = pub.texte.length > 100 
              ? pub.texte.substring(0, 100) + '...' 
              : pub.texte;

            const row = `
              <tr>
                <td>${pub.id_publication}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm mr-3">
                      <div class="avatar-title rounded-circle bg-primary text-white" style="width: 35px; height: 35px; line-height: 35px;">
                        ${pub.nom ? pub.nom.charAt(0).toUpperCase() : 'U'}
                      </div>
                    </div>
                    <div>
                      <div class="font-weight-bold">${escapeHtml(pub.nom || 'Unknown User')}</div>
                      <small class="text-muted">${escapeHtml(pub.email || '')}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="publication-content-preview">
                    ${escapeHtml(contentPreview)}
                  </div>
                </td>
                <td>
                  <small class="text-muted">${new Date(pub.date_publication).toLocaleDateString()}</small>
                  <br>
                  <small class="text-muted">${new Date(pub.date_publication).toLocaleTimeString()}</small>
                </td>
                <td>${fileBadge}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-info btn-action btn-view" 
                            data-id="${pub.id_publication}"
                            data-content="${escapeHtml(pub.texte)}"
                            data-author="${escapeHtml(pub.nom)}"
                            data-date="${pub.date_publication}"
                            data-file="${pub.fichier || ''}"
                            title="View Details">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-action btn-delete-pub" 
                            data-id="${pub.id_publication}"
                            data-author="${escapeHtml(pub.nom)}"
                            title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                    <!-- NOUVEAU BOUTON COMMENTS POUR LES PUBLICATIONS UTILISATEURS -->
                    <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=${pub.id_publication}" 
                       class="btn btn-sm btn-success btn-action" 
                       title="Comments">
                      <i class="fas fa-comments"></i>
                    </a>
                  </div>
                </td>
              </tr>
            `;
            tbody.append(row);
          });

          // Attach event listeners
          $('.btn-view').on('click', function() {
            const id = $(this).data('id');
            const content = $(this).data('content');
            const author = $(this).data('author');
            const date = $(this).data('date');
            const file = $(this).data('file');
            viewPublicationDetails(id, content, author, date, file);
          });

          $('.btn-delete-pub').on('click', function() {
            const id = $(this).data('id');
            const author = $(this).data('author');
            deleteUserPublication(id, author);
          });
        }

        function filterPubUserPublications(searchTerm) {
          if (!searchTerm) {
            displayPubUserPublications(allPubUserPublications);
            return;
          }

          const filtered = allPubUserPublications.filter(pub => 
            pub.texte.toLowerCase().includes(searchTerm) ||
            (pub.nom && pub.nom.toLowerCase().includes(searchTerm)) ||
            (pub.email && pub.email.toLowerCase().includes(searchTerm))
          );
          
          displayPubUserPublications(filtered);
        }

        function updatePubUserPagination(total, currentPage, totalPages) {
          const pagination = $('#pubuser-pagination');
          pagination.empty();

          if (totalPages <= 1) return;

          // Previous button
          if (currentPage > 1) {
            pagination.append(`
              <li class="page-item">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                  <i class="fas fa-chevron-left"></i>
                </a>
              </li>
            `);
          }

          // Page numbers
          for (let i = 1; i <= totalPages; i++) {
            const active = i === currentPage ? 'active' : '';
            pagination.append(`
              <li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
              </li>
            `);
          }

          // Next button
          if (currentPage < totalPages) {
            pagination.append(`
              <li class="page-item">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                  <i class="fas fa-chevron-right"></i>
                </a>
              </li>
            `);
          }

          // Pagination event listeners
          pagination.find('.page-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            loadPubUserPublications(page);
          });
        }

        function viewPublicationDetails(id, content, author, date, file) {
          console.log('Loading details for publication:', id);
          
          // Charger les commentaires et statistiques via AJAX
          $.ajax({
            url: '../../controller/CommentaireController.php',
            type: 'GET',
            data: { 
              action: 'listCommentairesByPublication',
              id_publication: id
            },
            dataType: 'json',
            success: function(commentsResponse) {
              console.log('Comments loaded:', commentsResponse);
              
              // Charger les statistiques de réactions (TOTAUX seulement)
              $.ajax({
                url: '../../controller/ReactionController.php',
                type: 'GET',
                data: {
                  action: 'getReactionsSummary',
                  id_publication: id
                },
                dataType: 'json',
                success: function(statsResponse) {
                  console.log('Stats loaded:', statsResponse);
                  
                  // Charger la réaction de l'admin
                  $.ajax({
                    url: '../../controller/ReactionController.php',
                    type: 'GET',
                    data: {
                      action: 'getUserReaction',
                      id_publication: id,
                      id_utilisateur: <?= $idUser ?>
                    },
                    dataType: 'json',
                    success: function(userReactionResponse) {
                      console.log('User reaction loaded:', userReactionResponse);
                      showPublicationDetailsModal(id, content, author, date, file, 
                        commentsResponse, statsResponse, userReactionResponse);
                    },
                    error: function(xhr, status, error) {
                      console.error('Error loading user reaction:', error);
                      showPublicationDetailsModal(id, content, author, date, file, 
                        commentsResponse, statsResponse, null);
                    }
                  });
                },
                error: function(xhr, status, error) {
                  console.error('Error loading stats:', error);
                  showPublicationDetailsModal(id, content, author, date, file, 
                    commentsResponse, null, null);
                }
              });
            },
            error: function(xhr, status, error) {
              console.error('Error loading comments:', error);
              showPublicationDetailsModal(id, content, author, date, file, 
                null, null, null);
            }
          });
        }

        function showPublicationDetailsModal(id, content, author, date, file, 
                                           commentsResponse, statsResponse, userReactionResponse) {
          
          let commentsHtml = '';
          let reactionsTotalsHtml = '';
          let adminReactionHtml = '';

          // Afficher les commentaires
          if (commentsResponse && commentsResponse.length > 0) {
            commentsResponse.forEach(comment => {
              commentsHtml += `
              <div class="comment-item">
                <div class="comment-header-modal">
                  <div class="d-flex align-items-center">
                    <div class="comment-avatar-sm me-2">
                      <i class="fas fa-user"></i>
                    </div>
                    <div>
                      <strong class="comment-author">${escapeHtml(comment.nom || 'User ' + comment.id_utilisateur)}</strong>
                      <small class="text-muted ms-2">${new Date(comment.date_commentaire).toLocaleString()}</small>
                    </div>
                  </div>
                </div>
                <div class="comment-content">
                  ${escapeHtml(comment.texte).replace(/\n/g, '<br>')}
                </div>
              </div>
              `;
            });
          } else {
            commentsHtml = `
            <div class="text-center text-muted py-3">
              <i class="fas fa-comment-slash fa-2x mb-2"></i>
              <p>No comments yet</p>
            </div>
            `;
          }

          // Afficher les TOTAUX de réactions (depuis le front)
          const likesCount = statsResponse ? (statsResponse.like || 0) : 0;
          const dislikesCount = statsResponse ? (statsResponse.dislike || 0) : 0;
          
          reactionsTotalsHtml = `
          <div class="reactions-totals">
            <div class="reaction-total-card likes">
              <div class="reaction-total-icon likes">
                <i class="fas fa-thumbs-up"></i>
              </div>
              <div class="reaction-total-count">${likesCount}</div>
              <div class="reaction-total-label">LIKES</div>
            </div>
            <div class="reaction-total-card dislikes">
              <div class="reaction-total-icon dislikes">
                <i class="fas fa-thumbs-down"></i>
              </div>
              <div class="reaction-total-count">${dislikesCount}</div>
              <div class="reaction-total-label">DISLIKES</div>
            </div>
          </div>
          `;

          // Section des réactions de l'admin
          const userReaction = userReactionResponse ? userReactionResponse.type_reaction : null;
          adminReactionHtml = `
          <div class="admin-reactions-section">
            <h6 class="text-muted mb-2">
              <i class="fas fa-user-shield me-2"></i>Your Reaction (Admin)
            </h6>
            <div class="admin-reaction-buttons">
              <button class="admin-reaction-btn like ${userReaction === 'like' ? 'active' : ''}" 
                      data-publication-id="${id}" data-reaction-type="like">
                <i class="fas fa-thumbs-up me-1"></i>Like
              </button>
              <button class="admin-reaction-btn dislike ${userReaction === 'dislike' ? 'active' : ''}" 
                      data-publication-id="${id}" data-reaction-type="dislike">
                <i class="fas fa-thumbs-down me-1"></i>Dislike
              </button>
              ${userReaction ? `
              <button class="admin-reaction-btn remove" data-publication-id="${id}">
                <i class="fas fa-times me-1"></i>Remove Reaction
              </button>
              ` : ''}
            </div>
          </div>
          `;

          const modalContent = `
          <div class="publication-details">
            <div class="publication-header mb-3">
              <div class="d-flex align-items-center">
                <div class="avatar-lg mr-3">
                  <div class="avatar-title rounded-circle bg-primary text-white" style="width: 60px; height: 60px; line-height: 60px; font-size: 24px;">
                    ${author ? author.charAt(0).toUpperCase() : 'U'}
                  </div>
                </div>
                <div>
                  <h5 class="mb-1">${escapeHtml(author || 'Unknown User')}</h5>
                  <p class="text-muted mb-0">${new Date(date).toLocaleString()}</p>
                </div>
              </div>
            </div>
            
            <div class="publication-content mb-3">
              <h6 class="text-muted mb-2">Content:</h6>
              <div class="content-box">
                ${escapeHtml(content).replace(/\n/g, '<br>')}
              </div>
            </div>
            
            ${file ? `
            <div class="publication-file mb-3">
              <h6 class="text-muted mb-2">Attached File:</h6>
              <a href="../../../${file}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-download me-2"></i>Download File
              </a>
            </div>
            ` : ''}

            ${adminReactionHtml}

            <!-- Reactions Section (TOTAUX seulement) -->
            <div class="reactions-section">
              <h6 class="text-muted mb-2">
                <i class="fas fa-heart me-2"></i>All Reactions from Users
              </h6>
              ${reactionsTotalsHtml}
            </div>

            <!-- Comments Section -->
            <div class="comments-section">
              <h6 class="text-muted mb-2">
                <i class="fas fa-comments me-2"></i>Comments (${commentsResponse ? commentsResponse.length : 0})
              </h6>
              <div class="comments-list">
                ${commentsHtml}
              </div>
            </div>
          </div>
          `;

          // Create and show modal
          const modalId = 'publication-details-modal';
          if ($(`#${modalId}`).length) {
            $(`#${modalId}`).remove();
          }

          const modal = $(`
            <div class="modal fade" id="${modalId}" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Publication Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    ${modalContent}
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          `);

          $('body').append(modal);
          modal.modal('show');

          // Attacher les événements pour les réactions admin
          attachAdminReactionEvents(id);
        }

        function attachAdminReactionEvents(publicationId) {
          $('.admin-reaction-btn').off('click').on('click', function() {
            const reactionType = $(this).data('reaction-type');
            const isRemove = $(this).hasClass('remove');
            
            if (isRemove) {
              removeAdminReaction(publicationId);
            } else {
              addAdminReaction(publicationId, reactionType);
            }
          });
        }

        function addAdminReaction(publicationId, reactionType) {
          $.ajax({
            url: 'addreaction.php',
            type: 'POST',
            data: {
              id_publication: publicationId,
              id_utilisateur: <?= $idUser ?>,
              type: reactionType
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('Reaction added successfully!');
                // Recharger la modal avec les nouvelles données
                $('.btn-close').click();
                // Trouver et re-cliquer sur le bouton view pour rafraîchir
                setTimeout(() => {
                  $(`.btn-view[data-id="${publicationId}"]`).click();
                }, 500);
              } else {
                showError('Error: ' + (response.message || 'Failed to add reaction'));
              }
            },
            error: function(xhr, status, error) {
              showError('Error adding reaction: ' + error);
            }
          });
        }

        function removeAdminReaction(publicationId) {
          $.ajax({
            url: '../../controller/ReactionController.php',
            type: 'POST',
            data: {
              action: 'removeReaction',
              id_publication: publicationId,
              id_utilisateur: <?= $idUser ?>
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('Reaction removed successfully!');
                // Recharger la modal avec les nouvelles données
                $('.btn-close').click();
                // Trouver et re-cliquer sur le bouton view pour rafraîchir
                setTimeout(() => {
                  $(`.btn-view[data-id="${publicationId}"]`).click();
                }, 500);
              } else {
                showError('Error: ' + (response.message || 'Failed to remove reaction'));
              }
            },
            error: function(xhr, status, error) {
              showError('Error removing reaction: ' + error);
            }
          });
        }

        function deleteUserPublication(id, author) {
          if (!confirm(`Are you sure you want to delete publication from "${author}"? This action cannot be undone.`)) {
            return;
          }

          $.ajax({
            url: '../../controller/PublicationController.php',
            type: 'POST',
            data: { 
              action: 'deleteUserPublication',
              id: id
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('Publication deleted successfully!');
                loadPubUserPublications();
              } else {
                showError(response.message || 'Failed to delete publication');
              }
            },
            error: function(xhr, status, error) {
              showError('Error deleting publication: ' + error);
            }
          });
        }

        function escapeHtml(text) {
          const s = String(text || '');
          const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
          };
          return s.replace(/[&<>\"']/g, m => map[m]);
        }

        function showSuccess(message) {
          $.notify({
            message: message
          }, {
            type: 'success',
            placement: {
              from: 'top',
              align: 'right'
            },
            delay: 3000
          });
        }

        function showError(message) {
          $.notify({
            message: message
          }, {
            type: 'danger',
            placement: {
              from: 'top',
              align: 'right'
            },
            delay: 5000
          });
        }
      });
    </script>
  </body>
</html>