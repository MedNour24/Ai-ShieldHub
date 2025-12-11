<?php
require_once __DIR__ . '/../../Controller/CommentaireController.php';
require_once __DIR__ . '/../../Model/Commentaire.php';

session_start();

// Vérifier que l'ID du commentaire est présent
if (!isset($_GET['id_commentaire']) || !isset($_GET['id_publication']) || !isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "Paramètres manquants !";
    header("Location: addPublications.php?id_utilisateur=" . ($_GET['id_utilisateur'] ?? ''));
    exit();
}

$idCommentaire = intval($_GET['id_commentaire']);
$idPublication = intval($_GET['id_publication']);
$idUser = intval($_GET['id_utilisateur']);

if ($idCommentaire <= 0 || $idUser <= 0) {
    $_SESSION['error'] = "ID invalide !";
    header("Location: addPublications.php?id_utilisateur=" . $idUser);
    exit();
}

try {
    // Supprimer le commentaire
    $commentaireController = new CommentaireController();
    $success = $commentaireController->deleteCommentaire($idCommentaire);
    
    if ($success) {
        $_SESSION['success'] = "Commentaire supprimé avec succès !";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du commentaire.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

// Rediriger vers la page des commentaires
header("Location: addcommentaire.php?id_utilisateur=" . $idUser . "&id_publication=" . $idPublication);
exit();
?>