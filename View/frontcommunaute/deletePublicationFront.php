<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';

// Vérification paramètres
if (!isset($_GET['id']) || !isset($_GET['id_utilisateur'])) {
    die("Paramètres manquants.");
}

$idPublication = intval($_GET['id']);
$idUser = intval($_GET['id_utilisateur']);

$controller = new PublicationController();

// Suppression
$controller->deletePublication($idPublication);

// CORRECTION : Récupérer la section actuelle et rediriger vers elle
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'mes';
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Redirection vers la même section
header("Location: addPublication.php?id_utilisateur=" . $idUser . "&section=" . $currentSection . "&page=" . $currentPage);
exit;
?>