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

    // Redirection vers addPublications avec l’utilisateur
    header("Location: addPublications.php?id_utilisateur=" . $publication['id_utilisateur']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Publication</title>
    <link rel="stylesheet" href="communauteback.css" />
</head>
<body>
<main class="container">
<section class="left">
  <div class="card">
    <h2>Modifier la publication</h2>
    <form action="" method="POST" enctype="multipart/form-data" id="formPublication">
        <input type="hidden" name="id_publication" value="<?= $publication['id_publication'] ?>" />
        <input type="hidden" name="id_utilisateur" value="<?= $publication['id_utilisateur'] ?>" />

        <label>ID Utilisateur :</label>
        <input type="number" name="id_utilisateur_display" value="<?= $publication['id_utilisateur'] ?>" disabled><br><br>

        <label>Texte :</label>
        <textarea name="texte" rows="5"><?= htmlspecialchars($publication['texte']) ?></textarea><br><br>

        <label>Fichier actuel :</label><br>
        <?php if (!empty($publication['fichier'])): ?>
            <a href="../../<?= htmlspecialchars($publication['fichier']) ?>" target="_blank">📄 Voir fichier</a><br><br>
        <?php else: ?>
            Aucun fichier actuel<br><br>
        <?php endif; ?>

        <label>Nouveau fichier (optionnel) :</label>
        <input type="file" name="file"><br><br>

        <div class="form-row">
            <button type="submit" class="btn primary">Mettre à jour</button>
            <a href="addPublications.php?id_utilisateur=<?= $publication['id_utilisateur'] ?>" class="btn">Annuler</a>
        </div>
    </form>
  </div>
</section>
</main>
</body>
</html>
