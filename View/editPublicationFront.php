<?php
require_once __DIR__ . '/../../Controller/PublicationControllerFront.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
$idPub  = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($idUser<=0 || $idPub<=0) die("Paramètres manquants !");

$pubController = new PublicationControllerFront();
$publication = $pubController->getPublicationById($idPub);

if(!$publication) die("Publication introuvable !");

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD']==='POST'){
    $texte = $_POST['texte'] ?? '';
    $filePath = $publication['fichier']; // garder ancien fichier par défaut

    if(!empty($_FILES['file']['name'])){
        $uploadDir = __DIR__ . "/../../uploads/";
        if(!file_exists($uploadDir)) mkdir($uploadDir,0777,true);

        $fileName = basename($_FILES['file']['name']);
        $targetPath = $uploadDir.$fileName;

        if(move_uploaded_file($_FILES['file']['tmp_name'],$targetPath)){
            $filePath = "uploads/".$fileName;
        }
    }

    // Créer un objet Publication pour l'update
    $p = new Publication($idPub, $idUser, $texte, $filePath, null, new DateTime());
    $pubController->updatePublication($p);

    header("Location: addPublication.php?id_utilisateur=".$idUser);
    exit();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Modifier Publication</title>
    <link rel="stylesheet" href="communautefront.css">
</head>
<body>

<div id="sidebar">
    <h2>Menu</h2>
    <button class="side-btn" onclick="location.href='addPublication.php?id_utilisateur=<?= $idUser ?>'">Mes Publications</button>
    <button class="side-btn" onclick="alert('Feed non disponible ici')">Feed</button>
</div>

<div id="main-content">
    <h2>Modifier la publication</h2>
    <form id="formEdit" action="" method="POST" enctype="multipart/form-data">
        <textarea name="texte" id="postText" rows="6"><?= htmlspecialchars($publication['texte']) ?></textarea><br><br>
        <input type="file" name="file" id="fileInput"><br><br>
        <button type="submit">Mettre à jour</button>
    </form>
</div>

<script>
// Contrôle JS
const form = document.getElementById('formEdit');
form.addEventListener('submit', function(e){
    const texte = document.getElementById('postText').value.trim();
    if(texte===''){ alert('Le texte ne peut pas être vide !'); e.preventDefault(); return; }
    if(texte.length>200){ alert('Le texte ne peut pas dépasser 200 caractères !'); e.preventDefault(); return; }
});
</script>

</body>
</html>
