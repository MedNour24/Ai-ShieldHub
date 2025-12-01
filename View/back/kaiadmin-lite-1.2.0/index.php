<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Rediriger vers la page de connexion
    header('Location: ../../future-ai/index.php');
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, le rediriger vers la page étudiante
    header('Location: ../../future-ai/index2.php');
    exit;
}

// Inclure la configuration
require_once '../../../config.php';
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
      /* Variables CSS pour une cohérence */
      :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --danger-gradient: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        --warning-gradient: linear-gradient(135deg, #f6e05e 0%, #ecc94b 100%);
        --info-gradient: linear-gradient(135deg, #0bc5ea 0%, #00b5d8 100%);
      }

      .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
      }
      .btn-action {
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 6px;
        transition: all 0.3s ease;
      }
      .loading-spinner {
        text-align: center;
        padding: 30px;
      }
      .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #6c757d;
      }
      .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
      }
      .badge {
        font-size: 11px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.3px;
      }
      
      /* Search and Filter Section améliorée */
      .search-filter-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid #e3e6f0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
      }
      .search-input-group {
        position: relative;
      }
      .search-input-group .form-control {
        padding-left: 45px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        height: 48px;
        font-size: 14px;
        transition: all 0.3s ease;
      }
      .search-input-group .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      .search-input-group i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        z-index: 10;
        font-size: 16px;
      }
      .filter-select {
        cursor: pointer;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        height: 48px;
        font-size: 14px;
        transition: all 0.3s ease;
      }
      .filter-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      .clear-filters-btn {
        display: none;
        border-radius: 10px;
        height: 48px;
        background: #6b7280;
        border: none;
        transition: all 0.3s ease;
      }
      .clear-filters-btn:hover {
        background: #4b5563;
        transform: translateY(-1px);
      }
      .clear-filters-btn.active {
        display: inline-block;
      }
      .highlight {
        background-color: #fff3cd;
        font-weight: 600;
        padding: 2px 4px;
        border-radius: 4px;
        color: #856404;
      }
      
      /* Styles pour les statistiques */
      .histogram-bar {
        transition: height 0.8s ease;
      }
      
      /* Styles pour l'activité des utilisateurs */
      .activity-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
      }
      .activity-stat-card {
        background: var(--primary-gradient);
        color: white;
        padding: 25px 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.1);
      }
      .activity-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.25);
      }
      .activity-stat-number {
        font-size: 2.5em;
        font-weight: 800;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .activity-stat-label {
        font-size: 0.95em;
        opacity: 0.95;
        font-weight: 500;
      }
      .top-users-list {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 5px;
      }
      .top-users-list::-webkit-scrollbar {
        width: 6px;
      }
      .top-users-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }
      .top-users-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
      }
      .user-activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 18px;
        background: #fff;
        border-radius: 12px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid #f1f3f4;
      }
      .user-activity-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      }
      .user-activity-item.admin {
        border-left-color: #fa709a;
      }
      .user-activity-item.student {
        border-left-color: #4facfe;
      }
      .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3em;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        background: var(--primary-gradient);
      }
      .user-info {
        flex: 1;
      }
      .user-name {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 0.95em;
      }
      .user-email {
        font-size: 0.8em;
        color: #718096;
      }
      .user-activity {
        text-align: right;
      }
      .last-seen {
        font-size: 0.75em;
        color: #6c757d;
        font-weight: 600;
      }
      .user-role {
        font-size: 0.7em;
        padding: 4px 10px;
        border-radius: 15px;
        background: white;
        color: #667eea;
        font-weight: 700;
        margin-top: 5px;
        display: inline-block;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      
      /* Styles améliorés pour le header */
      .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 25px;
        padding: 30px 0 20px 0;
        margin-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
      }
      .page-title {
        flex: 1;
        min-width: 300px;
      }
      .page-title h3 {
        color: #2d3748;
        font-weight: 800;
        margin-bottom: 8px;
        font-size: 1.8em;
      }
      .page-title h6 {
        color: #718096;
        font-weight: 500;
        font-size: 1em;
      }
      .add-user-btn-container {
        flex-shrink: 0;
      }
      .add-user-btn {
        white-space: nowrap;
        min-width: 160px;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        background: var(--primary-gradient);
        border: none;
        letter-spacing: 0.3px;
      }
      .add-user-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        background: var(--primary-gradient);
      }
      
      /* Styles améliorés pour le tableau */
      .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
      }
      .table {
        margin-bottom: 0;
        font-size: 0.9em;
      }
      .table thead {
        background: var(--primary-gradient);
      }
      .table thead th {
        color:#000000;
        font-weight: 700;
        padding: 18px 15px;
        border: none;
        font-size: 0.85em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
      }
      .table tbody tr {
        transition: all 0.3s ease;
        background: white;
      }
      .table tbody tr:nth-child(even) {
        background: #fafbfc;
      }
      .table tbody tr:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }
      .table tbody td {
        padding: 16px 15px;
        vertical-align: middle;
        border-color: #f1f3f4;
        color: #4a5568;
        font-weight: 500;
        border-bottom: 1px solid #f1f3f4;
      }
      
      /* Badges améliorés */
      .badge-success {
        background: var(--success-gradient);
        color: white;
      }
      .badge-danger {
        background: var(--danger-gradient);
        color: white;
      }
      .badge-primary {
        background: var(--primary-gradient);
        color: white;
      }
      .badge-info {
        background: var(--info-gradient);
        color: white;
      }
      .badge-warning {
        background: var(--warning-gradient);
        color: #744210;
      }
      
      /* Card header amélioré */
      .card-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 25px 30px;
        border-radius: 12px 12px 0 0 !important;
      }
      .card-title {
        color: #2d3748;
        font-weight: 800;
        font-size: 1.3em;
      }
      
      /* Responsive adjustments */
      @media (max-width: 768px) {
        .page-header {
          flex-direction: column;
          align-items: flex-start;
          gap: 20px;
          padding: 20px 0 15px 0;
        }
        .add-user-btn-container {
          width: 100%;
        }
        .add-user-btn {
          width: 100%;
        }
        .search-filter-section .row {
          gap: 15px;
        }
        .search-filter-section .col-md-5,
        .search-filter-section .col-md-3,
        .search-filter-section .col-md-1 {
          width: 100%;
        }
        .activity-stats {
          grid-template-columns: 1fr;
          gap: 15px;
        }
        .table-responsive {
          font-size: 0.8em;
        }
      }
      
      /* Section de résultats améliorée */
      .results-info {
        background: linear-gradient(135deg, #f0f4ff 0%, #f8f9fa 100%);
        padding: 15px 25px;
        border-radius: 10px;
        margin: 20px 0;
        border-left: 5px solid #667eea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      }
      .results-info .text-muted {
        font-weight: 600;
        color: #4a5568 !important;
        font-size: 0.95em;
      }
      .results-info i {
        color: #667eea;
      }
      
      /* Icônes d'action améliorées */
      .btn-edit {
        background: var(--warning-gradient);
        border: none;
        color: #744210;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
      }
      .btn-edit:hover {
        background: linear-gradient(135deg, #ecc94b 0%, #d69e2e 100%);
        color: #744210;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
      }
      .btn-delete {
        background: var(--danger-gradient);
        border: none;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
      }
      .btn-delete:hover {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
      }
      
      /* Labels de formulaire améliorés */
      .form-label {
        font-weight: 700;
        color: #374151;
        margin-bottom: 10px;
        font-size: 0.95em;
      }
      
      /* Stat cards améliorées */
      .card-stats .card-body {
        padding: 25px;
      }
      .card-stats .card-category {
        font-size: 0.9em;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .card-stats .card-title {
        font-size: 1.8em;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0;
      }
      .icon-big {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8em;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }
      
      /* Animation de chargement améliorée */
      .loading-spinner .spinner-border {
        width: 2rem;
        height: 2rem;
        color: #667eea;
      }
      
      /* Améliorations pour les modales */
      .modal-header {
        background: var(--primary-gradient);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 25px;
      }
      .modal-title {
        font-weight: 700;
        font-size: 1.3em;
      }
      .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      }
      .modal-body {
        padding: 25px;
      }
      .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #e9ecef;
      }
      
      /* Bouton Refresh amélioré */
      #refresh-users {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 0.85em;
        transition: all 0.3s ease;
        border: 1px solid #d1d5db;
      }
      #refresh-users:hover {
        background: #f8f9fa;
        transform: rotate(15deg);
      }
      
      /* Amélioration des cartes de dashboard */
      .card-round {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 25px;
      }
      .card-round:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-2px);
      }
      
      /* Profile Modal Styles avec styles d'avatars */
      #editProfileModal .profile-image-container {
        margin-bottom: 20px;
      }

      #editProfileModal .profile-image-container label {
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
      }

      #editProfileModal .profile-image-container label:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.5);
      }

      #editProfileModal #profile-preview {
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }

      #editProfileModal #profile-preview:hover {
        transform: scale(1.05);
      }

      /* Styles pour la sélection de style d'avatar */
      .avatar-style-selection {
        margin-bottom: 20px;
      }

      .style-options {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
      }

      .style-option {
        flex: 1;
        min-width: 120px;
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-weight: 600;
        color: #4a5568;
      }

      .style-option:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
      }

      .style-option.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }

      /* Styles pour la grille d'avatars */
      .avatar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 15px;
        max-height: 300px;
        overflow-y: auto;
        padding: 15px;
        border-radius: 10px;
        background: #f8f9fa;
        border: 1px solid #e2e8f0;
      }

      .avatar-grid::-webkit-scrollbar {
        width: 6px;
      }

      .avatar-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      .avatar-grid::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
      }

      .avatar-option {
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 50%;
        transition: all 0.3s ease;
        width: 80px;
        height: 80px;
        object-fit: cover;
        background: white;
        padding: 5px;
      }

      .avatar-option:hover {
        transform: scale(1.1);
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }

      .avatar-option.selected {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        transform: scale(1.05);
      }

      .upload-area {
        transition: all 0.3s ease;
      }

      .upload-area:hover {
        background: #f8f9fa;
        border-color: #4facfe !important;
      }

      .nav-pills .nav-link {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
      }

      .nav-pills .nav-link.active {
        background: var(--primary-gradient);
      }

      #editProfileModal .modal-dialog {
        max-width: 700px;
      }

      .background-color-section {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
      }

      .color-options {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
      }

      .color-option {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
      }

      .color-option:hover {
        transform: scale(1.2);
      }

      .color-option.selected {
        border-color: #2d3748;
        transform: scale(1.1);
      }

      .random-color-btn {
        margin-top: 10px;
        padding: 8px 15px;
        border: 1px solid #667eea;
        background: white;
        color: #667eea;
        border-radius: 6px;
        font-size: 0.9em;
        transition: all 0.3s ease;
      }

      .random-color-btn:hover {
        background: #667eea;
        color: white;
      }
      
      /* Styles pour les boutons Ban/Unban */
      .btn-ban {
        background: var(--danger-gradient);
        border: none;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
      }
      .btn-ban:hover {
        background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
      }
      .btn-unban {
        background: var(--success-gradient);
        border: none;
        color: white;
        box-shadow: 0 2px 8px rgba(79, 172, 254, 0.2);
      }
      .btn-unban:hover {
        background: linear-gradient(135deg, #4facfe 0%, #00c6fb 100%);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
      }

      /* Badge pour utilisateur banni */
      .badge-banned {
        background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        color: white;
        animation: pulse 2s infinite;
      }

      @keyframes pulse {
        0%, 100% {
          opacity: 1;
        }
        50% {
          opacity: 0.7;
        }
      }
      /* Notification Styles */
.notification-ping {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 10px;
  height: 10px;
  background-color: #f56565;
  border-radius: 50%;
  animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.notification-badge {
  position: absolute;
  top: 5px;
  right: 5px;
  font-size: 0.65em;
  padding: 2px 5px;
  min-width: 18px;
  text-align: center;
}

.notif-box {
  width: 380px;
  max-height: 500px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.dropdown-title {
  padding: 15px 20px;
  border-bottom: 1px solid #e9ecef;
  font-weight: 700;
  font-size: 1.1em;
  color: #2d3748;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px 12px 0 0;
}

.notif-scroll {
  max-height: 350px;
  overflow-y: auto;
}

.notif-scroll::-webkit-scrollbar {
  width: 6px;
}

.notif-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notif-scroll::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 10px;
}

.notif-center {
  padding: 20px;
}

.notif-item {
  padding: 15px 20px;
  border-bottom: 1px solid #f1f3f4;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
}

.notif-item:hover {
  background: #f8f9fa;
}

.notif-item.unread {
  background: #f0f4ff;
  border-left: 4px solid #667eea;
}

.notif-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2em;
  margin-right: 15px;
}

.notif-icon.registration {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.notif-icon.suspicious_activity {
  background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
  color: white;
}

.notif-icon.system {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.notif-icon.security {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  color: white;
}

.notif-content {
  flex: 1;
}

.notif-title {
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 4px;
  font-size: 0.9em;
}

.notif-message {
  font-size: 0.85em;
  color: #718096;
  line-height: 1.4;
  margin-bottom: 4px;
}

.notif-time {
  font-size: 0.75em;
  color: #a0aec0;
}

.notif-delete {
  position: absolute;
  top: 15px;
  right: 15px;
  opacity: 0;
  transition: opacity 0.3s ease;
  color: #f56565;
  cursor: pointer;
}

.notif-item:hover .notif-delete {
  opacity: 1;
}

.severity-badge {
  font-size: 0.7em;
  padding: 3px 8px;
  border-radius: 12px;
  margin-left: 8px;
}

.severity-low {
  background: #d1fae5;
  color: #065f46;
}

.severity-medium {
  background: #fed7aa;
  color: #92400e;
}

.severity-high {
  background: #fecaca;
  color: #991b1b;
}

.severity-critical {
  background: #fee2e2;
  color: #7f1d1d;
  font-weight: 700;
  animation: pulse 2s infinite;
}

.see-all {
  display: block;
  text-align: center;
  padding: 12px;
  color: #667eea;
  font-weight: 600;
  border-top: 1px solid #e9ecef;
  transition: all 0.3s ease;
  border-radius: 0 0 12px 12px;
}

.see-all:hover {
  background: #f8f9fa;
  color: #667eea;
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
            <a href="index.php" class="logo">
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
              <li class="nav-item active" id="dashboard-nav">
                <a href="#" id="dashboard-link">
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
              <li class="nav-item" id="base-nav">
                <a href="#" id="base-link">
                  <i class="fas fa-layer-group"></i>
                  <p>Base</p>
                </a>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#forms">
                  <i class="fas fa-pen-square"></i>
                  <p>Forms</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="forms">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Basic Form</span>
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
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#maps">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Maps</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="maps">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Google Maps</span>
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
        <!-- Dashboard View (visible par défaut) -->
        <div id="dashboard-view">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Dashboard</h3>
              <h6 class="op-7 mb-2">User Management System</h6>
            </div>
          </div>

          <!-- Statistics Cards -->
          <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Users</p>
                        <h4 class="card-title" id="total-users">0</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-user-graduate"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Students</p>
                        <h4 class="card-title" id="student-count">0</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-user-shield"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Admins</p>
                        <h4 class="card-title" id="admin-count">0</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-secondary bubble-shadow-small">
                        <i class="far fa-check-circle"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Active</p>
                        <h4 class="card-title" id="active-count">0</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Nouvelles statistiques innovantes -->
          <div class="row mt-4">
            <!-- HISTOGRAMME - Croissance Mensuelle -->
            <div class="col-md-7">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">
                      <i class="fas fa-chart-bar me-2"></i>Monthly Growth
                    </div>
                    <div class="card-tools">
                      <div class="d-flex align-items-center">
                        <span class="badge badge-info me-2">
                          <i class="fas fa-user-graduate"></i> Students
                        </span>
                        <span class="badge badge-warning">
                          <i class="fas fa-user-shield"></i> Admins
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div id="monthly-histogram" style="height: 350px;"></div>
                </div>
              </div>
            </div>

            <!-- ACTIVITÉ DES UTILISATEURS - Connexions Récentes -->
            <div class="col-md-5">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-title">
                    <i class="fas fa-user-clock me-2"></i>User Activity
                  </div>
                </div>
                <div class="card-body">
                  <div class="activity-stats">
                    <div class="activity-stat-card">
                      <div class="activity-stat-number" id="today-active">0</div>
                      <div class="activity-stat-label">Today</div>
                    </div>
                    <div class="activity-stat-card" style="background: var(--secondary-gradient);">
                      <div class="activity-stat-number" id="week-active">0</div>
                      <div class="activity-stat-label">This Week</div>
                    </div>
                    <div class="activity-stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                      <div class="activity-stat-number" id="month-active">0</div>
                      <div class="activity-stat-label">This Month</div>
                    </div>
                  </div>
                  
                  <h6 class="fw-bold mb-3">
                    <i class="fas fa-fire me-2"></i>Top Active Users
                  </h6>
                  <div class="top-users-list" id="top-users-list">
                    <div class="text-center text-muted p-4">
                      <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Base View (caché par défaut) -->
        <div id="base-view" style="display: none;">
          <div class="main-header">
            <div class="main-header-logo">
              <!-- Logo Header -->
              <div class="logo-header" data-background-color="dark">
                <a href="index.php" class="logo">
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
                  <!-- Notification Bell - À CÔTÉ DU PROFIL -->
                  <li class="nav-item topbar-icon dropdown hidden-caret me-3">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-bell"></i>
                      <span class="notification notification-ping"></span>
                      <span class="badge badge-danger badge-pill notification-badge" id="notification-count" style="display: none;">0</span>
                    </a>
                    <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notificationDropdown">
                      <li>
                        <div class="dropdown-title d-flex justify-content-between align-items-center">
                          <span>Notifications</span>
                          <a href="#" class="small" id="mark-all-read">Mark all as read</a>
                        </div>
                      </li>
                      <li>
                        <div class="notif-scroll scrollbar-outer" id="notifications-list">
                          <div class="notif-center text-center py-4">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p class="mt-2">Loading notifications...</p>
                          </div>
                        </div>
                      </li>
                      <li>
                        <a class="see-all" href="#" id="refresh-notifications">
                          <i class="fas fa-sync-alt me-2"></i>Refresh
                        </a>
                      </li>
                    </ul>
                  </li>

                  <!-- User Profile Dropdown -->
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
                          <a class="dropdown-item" href="#" id="open-profile-modal">
                            <i class="fas fa-user me-2"></i>My Profile
                          </a>
                          <a class="dropdown-item" href="#">Account Setting</a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item" href="#" id="logout-link">Logout</a>
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
              <!-- Header amélioré -->
              <div class="page-header">
                <div class="page-title">
                  <h3 class="fw-bold mb-2">User Management</h3>
                  <h6 class="op-7 mb-0">Manage your users efficiently</h6>
                </div>
                <div class="add-user-btn-container">
                  <button class="btn btn-primary add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                  </button>
                </div>
              </div>

              <!-- User Management Section -->
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-round">
                    <div class="card-header">
                      <div class="card-head-row">
                        <div class="card-title">Users List</div>
                        <div class="card-tools">
                          <button class="btn btn-sm btn-light" id="refresh-users">
                            <i class="fas fa-sync-alt"></i> Refresh
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="card-body">
                      <!-- Search and Filter Section améliorée -->
                      <div class="search-filter-section">
                        <div class="row g-3">
                          <div class="col-md-5">
                            <label class="form-label fw-bold">
                              <i class="fas fa-search me-2"></i>Search Users
                            </label>
                            <div class="search-input-group">
                              <i class="fas fa-search"></i>
                              <input 
                                type="text" 
                                class="form-control" 
                                id="search-input" 
                                placeholder="Search by name or email..."
                              >
                            </div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label fw-bold">
                              <i class="fas fa-filter me-2"></i>Filter by Role
                            </label>
                            <select class="form-control filter-select" id="filter-role">
                              <option value="">All Roles</option>
                              <option value="student">Student</option>
                              <option value="admin">Admin</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label fw-bold">
                              <i class="fas fa-toggle-on me-2"></i>Filter by Status
                            </label>
                            <select class="form-control filter-select" id="filter-status">
                              <option value="">All Status</option>
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-secondary w-100 clear-filters-btn" id="clear-filters">
                              <i class="fas fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- Results Info amélioré -->
                      <div class="results-info">
                        <small class="text-muted">
                          <i class="fas fa-info-circle me-1"></i>
                          <span id="filter-results-text">Showing all users</span>
                        </small>
                      </div>

                      <!-- Table améliorée -->
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="users-table">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>NAME</th>
                              <th>EMAIL</th>
                              <th>ROLE</th>
                              <th>STATUS</th>
                              <th>CREATED AT</th>
                              <th class="text-center">ACTIONS</th>
                            </tr>
                          </thead>
                          <tbody id="users-tbody">
                            <tr class="loading-spinner">
                              <td colspan="7" class="text-center">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Loading users...
                              </td>
                            </tr>
                          </tbody>
                        </table>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="add-user-form">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback" id="name-error"></div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback" id="email-error"></div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback" id="password-error"></div>
              </div>
              <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="invalid-feedback" id="confirm-password-error"></div>
              </div>
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role" required>
                  <option value="">Select Role</option>
                  <option value="student">Student</option>
                  <option value="admin">Admin</option>
                </select>
                <div class="invalid-feedback" id="role-error"></div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="submit-add-user">Add User</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="edit-user-form">
              <input type="hidden" id="edit-id" name="id">
              <div class="mb-3">
                <label for="edit-name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="edit-name" name="name" required>
                <div class="invalid-feedback" id="edit-name-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="edit-email" name="email" required>
                <div class="invalid-feedback" id="edit-email-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-role" class="form-label">Role</label>
                <select class="form-control" id="edit-role" name="role" required>
                  <option value="">Select Role</option>
                  <option value="student">Student</option>
                  <option value="admin">Admin</option>
                </select>
                <div class="invalid-feedback" id="edit-role-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-status" class="form-label">Status</label>
                <select class="form-control" id="edit-status" name="status" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="submit-edit-user">Update User</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal avec styles d'avatars -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProfileModalLabel">
              <i class="fas fa-user-edit me-2"></i>Update My Profile
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="edit-profile-form">
              <!-- Profile Image Selection -->
              <div class="mb-4">
                <label class="form-label fw-bold">
                  <i class="fas fa-image me-2"></i>Profile Picture
                </label>
                
                <!-- Current Profile Image -->
                <div class="text-center mb-3">
                  <img id="profile-preview" src="assets/img/profile.jpg" alt="Profile" 
                       class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #667eea;">
                </div>

                <!-- Tabs for Avatar Selection or Upload -->
                <ul class="nav nav-pills nav-primary mb-3" id="profileImageTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="avatars-tab" data-bs-toggle="pill" 
                            data-bs-target="#avatars-panel" type="button" role="tab">
                      <i class="fas fa-user-circle me-2"></i>Choose Avatar
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upload-tab" data-bs-toggle="pill" 
                            data-bs-target="#upload-panel" type="button" role="tab">
                      <i class="fas fa-upload me-2"></i>Upload Image
                    </button>
                  </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="profileImageTabContent">
                  <!-- Avatar Selection Panel -->
                  <div class="tab-pane fade show active" id="avatars-panel" role="tabpanel">
                    <!-- Style Selection -->
                    <div class="avatar-style-selection">
                      <label class="form-label fw-bold">Style</label>
                      <div class="style-options">
                        <div class="style-option active" data-style="robot">Robot</div>
                        <div class="style-option" data-style="humain">Humain (Avataaars)</div>
                        <div class="style-option" data-style="geometrique">Géométrique</div>
                        <div class="style-option" data-style="illustration">Illustration</div>
                      </div>
                    </div>

                    <!-- Background Color Selection -->
                    <div class="background-color-section">
                      <label class="form-label fw-bold">Couleur de fond</label>
                      <div class="color-options">
                        <div class="color-option selected" style="background-color: #667eea;" data-color="#667eea"></div>
                        <div class="color-option" style="background-color: #fa709a;" data-color="#fa709a"></div>
                        <div class="color-option" style="background-color: #4facfe;" data-color="#4facfe"></div>
                        <div class="color-option" style="background-color: #43e97b;" data-color="#43e97b"></div>
                        <div class="color-option" style="background-color: #f6d365;" data-color="#f6d365"></div>
                        <div class="color-option" style="background-color: #a8edea;" data-color="#a8edea"></div>
                        <div class="color-option" style="background-color: #fed6e3;" data-color="#fed6e3"></div>
                        <div class="color-option" style="background-color: #9890e3;" data-color="#9890e3"></div>
                      </div>
                      <button type="button" class="random-color-btn" id="random-color">
                        <i class="fas fa-random me-1"></i>Aléatoire
                      </button>
                    </div>

                    <!-- Avatar Grid -->
                    <div class="avatar-grid" id="avatar-grid">
                      <!-- Avatars will be loaded here dynamically -->
                    </div>
                    <input type="hidden" id="selected-avatar" name="selected_avatar">
                    <input type="hidden" id="avatar-bg-color" name="avatar_bg_color" value="#667eea">
                  </div>

                  <!-- Upload Panel -->
                  <div class="tab-pane fade" id="upload-panel" role="tabpanel">
                    <div class="upload-area text-center p-4" style="border: 2px dashed #667eea; border-radius: 10px;">
                      <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #667eea;"></i>
                      <p class="mb-3">Click to upload or drag and drop</p>
                      <input type="file" id="profile-image" name="profile_image" accept="image/*" class="form-control">
                      <small class="text-muted">Max file size: 2MB (JPG, PNG, GIF)</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Full Name -->
              <div class="mb-3">
                <label for="profile-name" class="form-label">
                  <i class="fas fa-user me-2"></i>Full Name
                </label>
                <input type="text" class="form-control" id="profile-name" name="name" required>
                <div class="invalid-feedback" id="profile-name-error"></div>
              </div>

              <!-- Email Address -->
              <div class="mb-3">
                <label for="profile-email" class="form-label">
                  <i class="fas fa-envelope me-2"></i>Email Address
                </label>
                <input type="email" class="form-control" id="profile-email" name="email" required>
                <div class="invalid-feedback" id="profile-email-error"></div>
              </div>

              <!-- New Password (Optional) -->
              <div class="mb-3">
                <label for="profile-new-password" class="form-label">
                  <i class="fas fa-lock me-2"></i>New Password
                  <small class="text-muted">(Leave blank to keep current password)</small>
                </label>
                <input type="password" class="form-control" id="profile-new-password" name="new_password">
                <div class="invalid-feedback" id="profile-new-password-error"></div>
              </div>

              <!-- Confirm Password -->
              <div class="mb-3">
                <label for="profile-confirm-password" class="form-label">
                  <i class="fas fa-lock me-2"></i>Confirm New Password
                </label>
                <input type="password" class="form-control" id="profile-confirm-password" name="confirm_password">
                <div class="invalid-feedback" id="profile-confirm-password-error"></div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="button" class="btn btn-primary" id="submit-profile-update">
              <i class="fas fa-save me-2"></i>Save Changes
            </button>
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
      $(document).ready(function() {
        const controllerUrl = '../../../controller/UserController.php';
        let allUsers = [];
        let filteredUsers = [];
         
        // Profile Management avec styles d'avatars
        const avatarsByStyle = {
          robot: [
            'robot1.png', 'robot2.png', 'robot3.png', 'robot4.png',
            'robot5.png', 'robot6.png', 'robot7.png', 'robot8.png'
          ],
          humain: [
            'humain1.png', 'humain2.png', 'humain3.png', 'humain4.png',
            'humain5.png', 'humain6.png', 'humain7.png', 'humain8.png'
          ],
          geometrique: [
            'geometrique1.png', 'geometrique2.png', 'geometrique3.png', 'geometrique4.png',
            'geometrique5.png', 'geometrique6.png', 'geometrique7.png', 'geometrique8.png'
          ],
          illustration: [
            'illustration1.png', 'illustration2.png', 'illustration3.png', 'illustration4.png',
            'illustration5.png', 'illustration6.png', 'illustration7.png', 'illustration8.png'
          ]
        };

        const availableColors = [
          '#667eea', '#fa709a', '#4facfe', '#43e97b', 
          '#f6d365', '#a8edea', '#fed6e3', '#9890e3'
        ];

        let currentStyle = 'robot';
        let currentBgColor = '#667eea';

        function loadAvatars(style, avatarToSelect = null) {
          currentStyle = style;
          const avatarGrid = $('#avatar-grid');
          avatarGrid.empty();
          
          if (!avatarsByStyle[style]) {
            console.error('Style not found:', style);
            return;
          }
          
          avatarsByStyle[style].forEach((avatar, index) => {
            const avatarPath = `assets/img/avatars/${style}/${avatar}`;
            const avatarElement = $(`
              <img src="${avatarPath}" 
                   class="avatar-option" 
                   data-avatar="${avatar}"
                   data-style="${style}"
                   alt="Avatar ${index + 1}"
                   onerror="this.src='assets/img/profile.jpg'"
                   style="background-color: ${currentBgColor};">
            `);
            
            avatarElement.on('click', function() {
              $('.avatar-option').removeClass('selected');
              $(this).addClass('selected');
              $('#selected-avatar').val(`${style}/${avatar}`);
              $('#profile-preview').attr('src', avatarPath);
              updateAvatarBackground();
            });
            
            avatarGrid.append(avatarElement);
          });

          if (avatarToSelect) {
            $(`.avatar-option[data-avatar="${avatarToSelect}"]`).click();
          }
        }

        function updateAvatarBackground() {
          $('.avatar-option').css('background-color', currentBgColor);
          $('#avatar-bg-color').val(currentBgColor);
        }

        function getRandomColor() {
          const letters = '0123456789ABCDEF';
          let color = '#';
          for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
          }
          return color;
        }

        $('#open-profile-modal').on('click', function(e) {
          e.preventDefault();
          loadAvatars(currentStyle);
          loadCurrentProfile();
          $('#editProfileModal').modal('show');
        });

        $('.style-option').on('click', function() {
          $('.style-option').removeClass('active');
          $(this).addClass('active');
          const style = $(this).data('style');
          loadAvatars(style);
        });

        $('.color-option').on('click', function() {
          $('.color-option').removeClass('selected');
          $(this).addClass('selected');
          currentBgColor = $(this).data('color');
          updateAvatarBackground();
        });

        $('#random-color').on('click', function() {
          const randomColor = getRandomColor();
          currentBgColor = randomColor;
          $('.color-option').removeClass('selected');
          updateAvatarBackground();
          
          const tempColorElement = $('<div>').addClass('color-option').css('background-color', randomColor);
          $('.color-options').append(tempColorElement);
          setTimeout(() => tempColorElement.remove(), 1000);
        });

        $('#profile-image').on('change', function(e) {
          const file = e.target.files[0];
          if (file) {
            if (file.size > 2 * 1024 * 1024) {
              showError('Image size must be less than 2MB');
              this.value = '';
              return;
            }
            
            $('.avatar-option').removeClass('selected');
            $('.style-option').removeClass('active');
            $('#selected-avatar').val('');
            
            const reader = new FileReader();
            reader.onload = function(e) {
              $('#profile-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
          }
        });

        function loadCurrentProfile() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'get_current_profile' },
            dataType: 'json',
            success: function(response) {
              if (response.success && response.data) {
                $('#profile-name').val(response.data.name || '');
                $('#profile-email').val(response.data.email || '');
                
                if (response.data.profile_image) {
                  $('#profile-preview').attr('src', response.data.profile_image);
                  
                  if (response.data.profile_image.includes('avatars/')) {
                    const pathParts = response.data.profile_image.split('/');
                    const avatarPart = pathParts[pathParts.length - 2] + '/' + pathParts[pathParts.length - 1];
                    const [style, avatar] = avatarPart.split('/');
                    
                    if (style && avatar) {
                      $(`.style-option[data-style="${style}"]`).click();
                      setTimeout(() => {
                        loadAvatars(style, avatar);
                      }, 100);
                    }
                  } else {
                    $('#upload-tab').tab('show');
                  }
                }
              } else {
                showError('Failed to load profile information');
              }
            },
            error: function() {
              showError('Error loading profile');
            }
          });
        }

        $('#submit-profile-update').on('click', function() {
          updateProfile();
        });

        function updateProfile() {
          const formData = new FormData();
          formData.append('action', 'update_profile');
          formData.append('name', $('#profile-name').val().trim());
          formData.append('email', $('#profile-email').val().trim());
          formData.append('new_password', $('#profile-new-password').val());
          formData.append('confirm_password', $('#profile-confirm-password').val());
          
          const selectedAvatar = $('#selected-avatar').val();
          if (selectedAvatar) {
            formData.append('selected_avatar', selectedAvatar);
          }
          
          const imageFile = $('#profile-image')[0].files[0];
          if (imageFile) {
            formData.append('profile_image', imageFile);
          }

          if (!validateProfileForm()) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
              $('#submit-profile-update')
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
            },
            success: function(response) {
              if (response.success) {
                $('#editProfileModal').modal('hide');
                showSuccess('Profile updated successfully!');
                
                if (response.data && response.data.name) {
                  $('.profile-username .fw-bold').text(response.data.name);
                  $('.u-text h4').text(response.data.name);
                }
                
                if (response.data && response.data.profile_image) {
                  $('.avatar-img').attr('src', response.data.profile_image);
                  $('.avatar-sm img').attr('src', response.data.profile_image);
                }
              } else {
                showError(response.message || 'Failed to update profile');
              }
            },
            error: function(xhr, status, error) {
              showError('Error updating profile: ' + error);
            },
            complete: function() {
              $('#submit-profile-update')
                .prop('disabled', false)
                .html('<i class="fas fa-save me-2"></i>Save Changes');
            }
          });
        }

        function validateProfileForm() {
          let isValid = true;
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');

          const name = $('#profile-name').val().trim();
          const email = $('#profile-email').val().trim();
          const newPassword = $('#profile-new-password').val();
          const confirmPassword = $('#profile-confirm-password').val();

          if (!name) {
            $('#profile-name').addClass('is-invalid');
            $('#profile-name-error').text('Name is required').show();
            isValid = false;
          }

          if (!email) {
            $('#profile-email').addClass('is-invalid');
            $('#profile-email-error').text('Email is required').show();
            isValid = false;
          } else if (!isValidEmail(email)) {
            $('#profile-email').addClass('is-invalid');
            $('#profile-email-error').text('Please enter a valid email address').show();
            isValid = false;
          }

          if (newPassword) {
            if (newPassword.length < 6) {
              $('#profile-new-password').addClass('is-invalid');
              $('#profile-new-password-error').text('Password must be at least 6 characters').show();
              isValid = false;
            }

            if (newPassword !== confirmPassword) {
              $('#profile-confirm-password').addClass('is-invalid');
              $('#profile-confirm-password-error').text('Passwords do not match').show();
              isValid = false;
            }
          }

          return isValid;
        }

        $('#editProfileModal').on('hidden.bs.modal', function() {
          $('#edit-profile-form')[0].reset();
          $('#profile-preview').attr('src', 'assets/img/profile.jpg');
          $('#selected-avatar').val('');
          $('.avatar-option').removeClass('selected');
          $('.style-option').removeClass('active');
          $('.style-option[data-style="robot"]').addClass('active');
          $('.color-option').removeClass('selected');
          $('.color-option[data-color="#667eea"]').addClass('selected');
          currentStyle = 'robot';
          currentBgColor = '#667eea';
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');
          $('#avatars-tab').tab('show');
        });

        // Navigation entre Dashboard et Base
        $('#dashboard-link').on('click', function(e) {
          e.preventDefault();
          showDashboard();
        });

        $('#base-link').on('click', function(e) {
          e.preventDefault();
          showBase();
        });

        function showDashboard() {
          $('#base-view').hide();
          $('#dashboard-view').show();
          $('#dashboard-nav').addClass('active');
          $('#base-nav').removeClass('active');
        }

        function showBase() {
          $('#dashboard-view').hide();
          $('#base-view').show();
          $('#base-nav').addClass('active');
          $('#dashboard-nav').removeClass('active');
        }

        // Load users on page load
        loadUsers();
        loadAdvancedStats();
        
        // ==========================================
        // NOTIFICATION MANAGEMENT
        // ==========================================
        let notificationCheckInterval;

        // Charger les notifications au démarrage
        loadNotifications();

        // Vérifier les nouvelles notifications toutes les 30 secondes
        notificationCheckInterval = setInterval(function() {
          loadNotifications();
        }, 30000);

        // Vérifier l'activité suspecte toutes les 5 minutes
        setInterval(function() {
          checkSuspiciousActivity();
        }, 300000);

        function loadNotifications() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'get_notifications' },
            dataType: 'json',
            success: function(response) {
              if (response.success && response.data) {
                updateNotificationUI(response.data.notifications, response.data.unread_count);
              }
            },
            error: function(xhr, status, error) {
              console.error('Error loading notifications:', error);
            }
          });
        }

        function updateNotificationUI(notifications, unreadCount) {
          const badge = $('#notification-count');
          const ping = $('.notification-ping');
          
          if (unreadCount > 0) {
            badge.text(unreadCount).show();
            ping.show();
          } else {
            badge.hide();
            ping.hide();
          }
          
          const notificationsList = $('#notifications-list');
          notificationsList.empty();
          
          if (notifications.length === 0) {
            notificationsList.html(`
              <div class="notif-center text-center py-4">
                <i class="fas fa-bell-slash fa-2x mb-3" style="color: #cbd5e0;"></i>
                <p class="text-muted">No notifications</p>
              </div>
            `);
            return;
          }
          
          notifications.forEach(notification => {
            const notifItem = createNotificationItem(notification);
            notificationsList.append(notifItem);
          });
        }

        function createNotificationItem(notification) {
          const isUnread = !notification.is_read;
          const timeAgo = getTimeAgo(notification.created_at);
          const iconClass = getNotificationIcon(notification.type);
          const severityBadge = notification.severity !== 'low' ? 
            `<span class="severity-badge severity-${notification.severity}">${notification.severity.toUpperCase()}</span>` : '';
          
          return $(`
            <div class="notif-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
              <div class="d-flex align-items-start">
                <div class="notif-icon ${notification.type}">
                  <i class="${iconClass}"></i>
                </div>
                <div class="notif-content">
                  <div class="notif-title">
                    ${escapeHtml(notification.title)}
                    ${severityBadge}
                  </div>
                  <div class="notif-message">${escapeHtml(notification.message)}</div>
                  <div class="notif-time">${timeAgo}</div>
                </div>
                <div class="notif-delete" data-id="${notification.id}">
                  <i class="fas fa-times"></i>
                </div>
              </div>
            </div>
          `).on('click', function(e) {
            if (!$(e.target).closest('.notif-delete').length) {
              markNotificationAsRead(notification.id);
            }
          });
        }

        function getNotificationIcon(type) {
          const icons = {
            'registration': 'fas fa-user-plus',
            'suspicious_activity': 'fas fa-exclamation-triangle',
            'system': 'fas fa-cog',
            'security': 'fas fa-shield-alt'
          };
          return icons[type] || 'fas fa-bell';
        }

        function getTimeAgo(dateString) {
          const now = new Date();
          const date = new Date(dateString);
          const seconds = Math.floor((now - date) / 1000);
          
          if (seconds < 60) return 'Just now';
          if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
          if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
          if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
          
          return date.toLocaleDateString();
        }

        function markNotificationAsRead(notificationId) {
          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: {
              action: 'mark_notification_read',
              notification_id: notificationId
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                $(`.notif-item[data-id="${notificationId}"]`).removeClass('unread');
                
                if (response.data && response.data.unread_count !== undefined) {
                  const badge = $('#notification-count');
                  const ping = $('.notification-ping');
                  
                  if (response.data.unread_count > 0) {
                    badge.text(response.data.unread_count).show();
                  } else {
                    badge.hide();
                    ping.hide();
                  }
                }
              }
            },
            error: function(xhr, status, error) {
              console.error('Error marking notification as read:', error);
            }
          });
        }

        $('#mark-all-read').on('click', function(e) {
          e.preventDefault();
          
          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { action: 'mark_all_notifications_read' },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                $('.notif-item').removeClass('unread');
                $('#notification-count').hide();
                $('.notification-ping').hide();
                showSuccess('All notifications marked as read');
              }
            },
            error: function(xhr, status, error) {
              console.error('Error marking all notifications as read:', error);
            }
          });
        });

        $('#refresh-notifications').on('click', function(e) {
          e.preventDefault();
          loadNotifications();
          showSuccess('Notifications refreshed');
        });

        $(document).on('click', '.notif-delete', function(e) {
          e.stopPropagation();
          const notificationId = $(this).data('id');
          
          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: {
              action: 'delete_notification',
              notification_id: notificationId
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                $(`.notif-item[data-id="${notificationId}"]`).fadeOut(300, function() {
                  $(this).remove();
                  
                  if ($('.notif-item').length === 0) {
                    $('#notifications-list').html(`
                      <div class="notif-center text-center py-4">
                        <i class="fas fa-bell-slash fa-2x mb-3" style="color: #cbd5e0;"></i>
                        <p class="text-muted">No notifications</p>
                      </div>
                    `);
                  }
                });
                
                if (response.data && response.data.unread_count !== undefined) {
                  const badge = $('#notification-count');
                  if (response.data.unread_count > 0) {
                    badge.text(response.data.unread_count).show();
                  } else {
                    badge.hide();
                    $('.notification-ping').hide();
                  }
                }
              }
            },
            error: function(xhr, status, error) {
              console.error('Error deleting notification:', error);
            }
          });
        });

        function checkSuspiciousActivity() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'check_suspicious_activity' },
            dataType: 'json',
            success: function(response) {
              if (response.success && response.data) {
                if (response.data.suspicious_count > 0) {
                  loadNotifications();
                }
              }
            },
            error: function(xhr, status, error) {
              console.error('Error checking suspicious activity:', error);
            }
          });
        }

        $(window).on('unload', function() {
          if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
          }
        });

        // Refresh users button
        $('#refresh-users').on('click', function() {
          loadUsers();
          loadAdvancedStats();
          showSuccess('Data refreshed successfully!');
        });

        // Add user functionality
        $('#submit-add-user').on('click', function() {
          addUser();
        });

        // Edit user functionality
        $('#submit-edit-user').on('click', function() {
          updateUser();
        });

        // Search functionality
        $('#search-input').on('keyup', function() {
          applyFilters();
        });

        // Filter by role
        $('#filter-role').on('change', function() {
          applyFilters();
        });

        // Filter by status
        $('#filter-status').on('change', function() {
          applyFilters();
        });

        // Clear filters
        $('#clear-filters').on('click', function() {
          $('#search-input').val('');
          $('#filter-role').val('');
          $('#filter-status').val('');
          applyFilters();
          showSuccess('Filters cleared!');
        });

        function loadUsers() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            beforeSend: function() {
              $('#users-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="7" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading users...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              if (response.success) {
                allUsers = response.data;
                applyFilters();
                updateStatistics(response.data);
              } else {
                showError('Failed to load users: ' + (response.message || 'Unknown error'));
                $('#users-tbody').html(`
                  <tr>
                    <td colspan="7" class="text-center text-danger">
                      <i class="fas fa-exclamation-triangle me-2"></i>
                      Error: ${response.message || 'Failed to load users'}
                    </td>
                  </tr>
                `);
              }
            },
            error: function(xhr, status, error) {
              showError('Error loading users: ' + error);
              $('#users-tbody').html(`
                <tr>
                  <td colspan="7" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Network error: Cannot load users. Check console for details.
                  </td>
                </tr>
              `);
            }
          });
        }

        function loadAdvancedStats() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'get_stats' },
            dataType: 'json',
            success: function(response) {
              if (response.success && response.data) {
                createHistogram(response.data.monthly_stats);
                createActivityStats(response.data.activity_stats);
              }
            },
            error: function(xhr, status, error) {
              console.error('Error loading stats:', error);
              createHistogram([]);
              createActivityStats({
                today_active: 0,
                week_active: 0,
                month_active: 0,
                top_users: []
              });
            }
          });
        }

        function createHistogram(data) {
          const container = $('#monthly-histogram');
          container.empty();
          
          if (!data || data.length === 0) {
            container.html('<div class="text-center text-muted p-5"><i class="fas fa-chart-bar fa-3x mb-3"></i><p>No data available</p></div>');
            return;
          }

          const maxValue = Math.max(...data.map(d => Math.max(parseInt(d.students || 0), parseInt(d.admins || 0))));
          const maxHeight = maxValue > 0 ? maxValue : 1;
          
          let html = '<div style="display: flex; align-items: flex-end; justify-content: space-around; height: 300px; gap: 15px;">';
          
          data.forEach((item, index) => {
            const studentCount = parseInt(item.students || 0);
            const adminCount = parseInt(item.admins || 0);
            const studentHeight = (studentCount / maxHeight) * 100;
            const adminHeight = (adminCount / maxHeight) * 100;
            
            html += `
              <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                <div style="width: 100%; height: 280px; display: flex; gap: 8px; align-items: flex-end; justify-content: center;">
                  <div class="histogram-bar" style="flex: 1; max-width: 45px; height: 0%; 
                       background: var(--success-gradient);
                       border-radius: 8px 8px 0 0; position: relative; transition: height 0.8s ease;"
                       data-height="${studentHeight}">
                    <div style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); 
                                font-weight: 800; font-size: 0.8em; color: #4facfe; white-space: nowrap;">
                      ${studentCount}
                    </div>
                  </div>
                  <div class="histogram-bar" style="flex: 1; max-width: 45px; height: 0%; 
                       background: var(--secondary-gradient);
                       border-radius: 8px 8px 0 0; position: relative; transition: height 0.8s ease;"
                       data-height="${adminHeight}">
                    <div style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); 
                                font-weight: 800; font-size: 0.8em; color: #fa709a; white-space: nowrap;">
                      ${adminCount}
                    </div>
                  </div>
                </div>
                <div style="margin-top: 20px; font-size: 0.85em; color: #718096; font-weight: 700; text-align: center;">
                  ${item.month_label || item.month}
                </div>
              </div>
            `;
          });
          
          html += '</div>';
          container.html(html);
          
          setTimeout(() => {
            $('.histogram-bar').each(function() {
              const targetHeight = $(this).data('height');
              $(this).css('height', targetHeight + '%');
            });
          }, 300);
        }

        function createActivityStats(activityData) {
          if (!activityData) {
            activityData = {
              today_active: 0,
              week_active: 0,
              month_active: 0,
              top_users: []
            };
          }
          
          $('#today-active').text(activityData.today_active || 0);
          $('#week-active').text(activityData.week_active || 0);
          $('#month-active').text(activityData.month_active || 0);
          
          const topUsersList = $('#top-users-list');
          topUsersList.empty();
          
          if (!activityData.top_users || activityData.top_users.length === 0) {
            topUsersList.html(`
              <div class="text-center text-muted p-4">
                <i class="fas fa-users fa-2x mb-3"></i>
                <p>No recent activity</p>
              </div>
            `);
            return;
          }
          
          activityData.top_users.forEach(user => {
            const userClass = user.role === 'admin' ? 'admin' : 'student';
            const roleLabel = user.role === 'admin' ? 'Administrator' : 'Student';
            const userInitial = user.name ? user.name.charAt(0).toUpperCase() : 'U';
            
            const userItem = `
              <div class="user-activity-item ${userClass}">
                <div class="user-avatar" style="background: ${user.color || '#667eea'};">
                  ${userInitial}
                </div>
                <div class="user-info">
                  <div class="user-name">${escapeHtml(user.name || 'Unknown')}</div>
                  <div class="user-email">${escapeHtml(user.email || 'No email')}</div>
                  <span class="user-role">${roleLabel}</span>
                </div>
                <div class="user-activity">
                  <div class="last-seen">${user.last_seen || 'Unknown'}</div>
                </div>
              </div>
            `;
            topUsersList.append(userItem);
          });
        }

        function applyFilters() {
          const searchTerm = $('#search-input').val().toLowerCase().trim();
          const roleFilter = $('#filter-role').val();
          const statusFilter = $('#filter-status').val();

          const hasActiveFilters = searchTerm !== '' || roleFilter !== '' || statusFilter !== '';
          
          if (hasActiveFilters) {
            $('#clear-filters').addClass('active');
          } else {
            $('#clear-filters').removeClass('active');
          }

          filteredUsers = allUsers.filter(user => {
            const matchesSearch = searchTerm === '' || 
              (user.name && user.name.toLowerCase().includes(searchTerm)) || 
              (user.email && user.email.toLowerCase().includes(searchTerm));

            const matchesRole = roleFilter === '' || user.role === roleFilter;
            const matchesStatus = statusFilter === '' || user.status === statusFilter;

            return matchesSearch && matchesRole && matchesStatus;
          });

          updateFilterResultsText(filteredUsers.length, allUsers.length, searchTerm, roleFilter, statusFilter);
          displayUsers(filteredUsers, searchTerm);
        }

        function updateFilterResultsText(filteredCount, totalCount, searchTerm, roleFilter, statusFilter) {
          let text = '';
          
          if (filteredCount === totalCount) {
            text = `Showing all ${totalCount} users`;
          } else {
            text = `Showing ${filteredCount} of ${totalCount} users`;
            
            const filters = [];
            if (searchTerm) filters.push(`search: "${searchTerm}"`);
            if (roleFilter) filters.push(`role: ${roleFilter}`);
            if (statusFilter) filters.push(`status: ${statusFilter}`);
            
            if (filters.length > 0) {
              text += ` (filtered by ${filters.join(', ')})`;
            }
          }
          
          $('#filter-results-text').text(text);
        }

        function displayUsers(users, highlightTerm = '') {
          const tbody = $('#users-tbody');
          tbody.empty();

          if (users.length === 0) {
            const isFiltered = $('#search-input').val() !== '' || 
                              $('#filter-role').val() !== '' || 
                              $('#filter-status').val() !== '';
            
            if (isFiltered) {
              tbody.html(`
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty-state">
                      <i class="fas fa-search fa-3x mb-3"></i>
                      <p>No users found matching your search criteria</p>
                      <button class="btn btn-secondary mt-3" id="clear-search-inline">
                        <i class="fas fa-times me-2"></i>Clear Filters
                      </button>
                    </div>
                  </td>
                </tr>
              `);
              
              $('#clear-search-inline').on('click', function() {
                $('#search-input').val('');
                $('#filter-role').val('');
                $('#filter-status').val('');
                applyFilters();
              });
            } else {
              tbody.html(`
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty-state">
                      <i class="fas fa-users fa-3x mb-3"></i>
                      <p>No users found</p>
                      <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-2"></i>Add First User
                      </button>
                    </div>
                  </td>
                </tr>
              `);
            }
            return;
          }

          users.forEach(user => {
            const statusBadge = user.status === 'active' 
              ? '<span class="badge badge-success">Active</span>'
              : '<span class="badge badge-danger">Inactive</span>';

            const roleBadge = user.role === 'admin'
              ? '<span class="badge badge-primary">Admin</span>'
              : '<span class="badge badge-info">Student</span>';

            const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown';

            let displayName = escapeHtml(user.name || 'Unknown');
            let displayEmail = escapeHtml(user.email || 'No email');
            
            if (highlightTerm) {
              const regex = new RegExp(`(${escapeRegex(highlightTerm)})`, 'gi');
              displayName = displayName.replace(regex, '<span class="highlight">$1</span>');
              displayEmail = displayEmail.replace(regex, '<span class="highlight">$1</span>');
            }

            const row = `
              <tr ${user.status === 'inactive' ? 'style="opacity: 0.6; background-color: #fff5f5;"' : ''}>
                <td><strong>${user.id}</strong></td>
                <td>${displayName}</td>
                <td>${displayEmail}</td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td>${createdDate}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-warning btn-action btn-edit" 
                            data-id="${user.id}"
                            data-name="${escapeHtml(user.name || '')}"
                            data-email="${escapeHtml(user.email || '')}"
                            data-role="${user.role}"
                            data-status="${user.status}"
                            title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    ${user.status === 'active' ? `
                      <button class="btn btn-sm btn-danger btn-action btn-ban" 
                              data-id="${user.id}"
                              data-name="${escapeHtml(user.name || '')}"
                              title="Ban User">
                        <i class="fas fa-ban"></i>
                      </button>
                    ` : `
                      <button class="btn btn-sm btn-success btn-action btn-unban" 
                              data-id="${user.id}"
                              data-name="${escapeHtml(user.name || '')}"
                              title="Unban User">
                        <i class="fas fa-check-circle"></i>
                      </button>
                    `}
                    <button class="btn btn-sm btn-danger btn-action btn-delete" 
                            data-id="${user.id}"
                            data-name="${escapeHtml(user.name || '')}"
                            title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `;
            tbody.append(row);
          });

          $('.btn-edit').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const role = $(this).data('role');
            const status = $(this).data('status');
            openEditModal(id, name, email, role, status);
          });

          $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            deleteUser(id, name);
          });

          $('.btn-ban').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            banUser(id, name);
          });

          $('.btn-unban').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            unbanUser(id, name);
          });
        }

        function updateStatistics(users) {
          const totalUsers = users.length;
          const studentCount = users.filter(u => u.role === 'student').length;
          const adminCount = users.filter(u => u.role === 'admin').length;
          const activeCount = users.filter(u => u.status === 'active').length;

          $('#total-users').text(totalUsers);
          $('#student-count').text(studentCount);
          $('#admin-count').text(adminCount);
          $('#active-count').text(activeCount);
        }

        function addUser() {
          const formData = {
            action: 'add',
            name: $('#name').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val(),
            confirm_password: $('#confirm_password').val(),
            role: $('#role').val()
          };

          if (!validateAddForm(formData)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
              $('#submit-add-user').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
            },
            success: function(response) {
              if (response.success) {
                $('#addUserModal').modal('hide');
                $('#add-user-form')[0].reset();
                showSuccess('User added successfully!');
                loadUsers();
                loadAdvancedStats();
              } else {
                showError(response.message || 'Failed to add user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error adding user: ' + error);
            },
            complete: function() {
              $('#submit-add-user').prop('disabled', false).html('Add User');
            }
          });
        }

        function openEditModal(id, name, email, role, status) {
          $('#edit-id').val(id);
          $('#edit-name').val(name);
          $('#edit-email').val(email);
          $('#edit-role').val(role);
          $('#edit-status').val(status);
          $('#editUserModal').modal('show');
        }

        function updateUser() {
          const formData = {
            action: 'update',
            id: $('#edit-id').val(),
            name: $('#edit-name').val().trim(),
            email: $('#edit-email').val().trim(),
            role: $('#edit-role').val(),
            status: $('#edit-status').val()
          };

          if (!validateEditForm(formData)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
              $('#submit-edit-user').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
            },
            success: function(response) {
              if (response.success) {
                $('#editUserModal').modal('hide');
                showSuccess('User updated successfully!');
                loadUsers();
                loadAdvancedStats();
              } else {
                showError(response.message || 'Failed to update user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error updating user: ' + error);
            },
            complete: function() {
              $('#submit-edit-user').prop('disabled', false).html('Update User');
            }
          });
        }

        function deleteUser(id, name) {
          if (!confirm(`Are you sure you want to delete user "${name}"? This action cannot be undone.`)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('User deleted successfully!');
                loadUsers();
                loadAdvancedStats();
              } else {
                showError(response.message || 'Failed to delete user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error deleting user: ' + error);
            }
          });
        }

        function banUser(id, name) {
          if (!confirm(`Are you sure you want to BAN user "${name}"?\n\nThis will prevent them from logging in, but their data will be preserved.\n\nYou can unban them later if needed.`)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { 
              action: 'ban_user', 
              user_id: id 
            },
            dataType: 'json',
            beforeSend: function() {
              $(`.btn-ban[data-id="${id}"]`).prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
              if (response.success) {
                showSuccess(`User "${name}" has been banned successfully!`);
                loadUsers();
                loadAdvancedStats();
              } else {
                showError(response.message || 'Failed to ban user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error banning user: ' + error);
            },
            complete: function() {
              $(`.btn-ban[data-id="${id}"]`).prop('disabled', false)
                .html('<i class="fas fa-ban"></i>');
            }
          });
        }

        function unbanUser(id, name) {
          if (!confirm(`Are you sure you want to UNBAN user "${name}"?\n\nThis will restore their access to the system.`)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { 
              action: 'unban_user', 
              user_id: id 
            },
            dataType: 'json',
            beforeSend: function() {
              $(`.btn-unban[data-id="${id}"]`).prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
              if (response.success) {
                showSuccess(`User "${name}" has been unbanned successfully!`);
                loadUsers();
                loadAdvancedStats();
              } else {
                showError(response.message || 'Failed to unban user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error unbanning user: ' + error);
            },
            complete: function() {
              $(`.btn-unban[data-id="${id}"]`).prop('disabled', false)
                .html('<i class="fas fa-check-circle"></i>');
            }
          });
        }

        function validateAddForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');

          if (!data.name) {
            $('#name').addClass('is-invalid');
            $('#name-error').text('Name is required').show();
            isValid = false;
          }

          if (!data.email) {
            $('#email').addClass('is-invalid');
            $('#email-error').text('Email is required').show();
            isValid = false;
          } else if (!isValidEmail(data.email)) {
            $('#email').addClass('is-invalid');
            $('#email-error').text('Please enter a valid email address').show();
            isValid = false;
          }

          if (!data.password) {
            $('#password').addClass('is-invalid');
            $('#password-error').text('Password is required').show();
            isValid = false;
          } else if (data.password.length < 6) {
            $('#password').addClass('is-invalid');
            $('#password-error').text('Password must be at least 6 characters').show();
            isValid = false;
          }

          if (data.password !== data.confirm_password) {
            $('#confirm_password').addClass('is-invalid');
            $('#confirm-password-error').text('Passwords do not match').show();
            isValid = false;
          }

          if (!data.role) {
            $('#role').addClass('is-invalid');
            $('#role-error').text('Role is required').show();
            isValid = false;
          }

          return isValid;
        }

        function validateEditForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');

          if (!data.name) {
            $('#edit-name').addClass('is-invalid');
            $('#edit-name-error').text('Name is required').show();
            isValid = false;
          }

          if (!data.email) {
            $('#edit-email').addClass('is-invalid');
            $('#edit-email-error').text('Email is required').show();
            isValid = false;
          } else if (!isValidEmail(data.email)) {
            $('#edit-email').addClass('is-invalid');
            $('#edit-email-error').text('Please enter a valid email address').show();
            isValid = false;
          }

          if (!data.role) {
            $('#edit-role').addClass('is-invalid');
            $('#edit-role-error').text('Role is required').show();
            isValid = false;
          }

          return isValid;
        }

        function isValidEmail(email) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return emailRegex.test(email);
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

        function escapeRegex(text) {
          return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
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

        // Clear validation errors when modal is shown
        $('#addUserModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');
          $('#add-user-form')[0].reset();
        });

        $('#editUserModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');
        });
        
        // Dans la partie JavaScript, remplacez la fonction de logout par :
        $('#logout-link').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to logout?')) {
                $.ajax({
                    url: '../../../controller/UserController.php',
                    type: 'POST',
                    data: { action: 'logout' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // CORRECTION : Utiliser window.location.href pour une redirection absolue
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                // Fallback vers la page d'accueil
                                window.location.href = '/user1/view/FutureAi/index.php';
                            }
                        } else {
                            showError(response.message || 'Logout failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Logout error:', error);
                        // En cas d'erreur, rediriger quand même
                        window.location.href = '/user1/view/FutureAi/index.php';
                    }
                });
            }
        });

      });
    </script>
  </body>
</html>