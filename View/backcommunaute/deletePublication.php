<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';

if (!isset($_GET['id'])) {
    die("ID de publication manquant.");
}

$id_publication = intval($_GET['id']);
$id_utilisateur = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;

$controller = new PublicationController();

try {
    $controller->deletePublication($id_publication);
    
    // Redirection vers la même page (avec l'id utilisateur)
    header("Location: addPublications.php?id_utilisateur=" . $id_utilisateur . "&success=1");
    exit();
    
} catch (Exception $e) {
    // En cas d'erreur, rediriger avec un message d'erreur
    header("Location: addPublications.php?id_utilisateur=" . $id_utilisateur . "&error=" . urlencode("Erreur lors de la suppression : " . $e->getMessage()));
    exit();
}
?>