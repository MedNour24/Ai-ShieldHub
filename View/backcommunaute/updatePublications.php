<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Model/Publication.php';


$pubCtrl = new PublicationController();
$pub = $pubCtrl->getPublicationById($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $filePath = $pub['filePath'];

    if (!empty($_FILES['file']['name'])) {
        $uploadDir = "../uploads/";
        $filePath = $uploadDir . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
    }

    $p = new Publication(
        null,
        $pub['idUser'],
        $_POST['titre'],
        $_POST['contenu'],
        $filePath
    );

    $pubCtrl->updatePublication($p, $_GET['id']);
    header("Location: listPublications.php");
}
?>

<h2>Modifier une publication</h2>

<form action="" method="POST" enctype="multipart/form-data">
    <label>Titre :</label>
    <input type="text" name="titre" value="<?= $pub['titre'] ?>" required><br><br>

    <label>Contenu :</label><br>
    <textarea name="contenu" rows="5" required><?= $pub['contenu'] ?></textarea><br><br>

    <label>Fichier actuel :</label>
    <?php if ($pub['filePath']): ?>
        <a href="<?= $pub['filePath'] ?>" target="_blank"><?= $pub['filePath'] ?></a><br>
    <?php endif; ?>

    <label>Nouveau fichier :</label>
    <input type="file" name="file"><br><br>

    <button type="submit">Mettre à jour</button>
</form>
