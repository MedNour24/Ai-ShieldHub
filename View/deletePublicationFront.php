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

// Redirection vers la liste du front office
header("Location: addPublication.php?id_utilisateur=" . $idUser . "&my_page=1");
exit;
?>
