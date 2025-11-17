<?php 
require_once __DIR__ . '/../../Controller/PublicationControllerFront.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
if ($idUser <= 0) die("ID utilisateur non spécifié !");

$pubController = new PublicationControllerFront();

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

    header("Location: addPublication.php?id_utilisateur=".$idUser);
    exit();
}

// ---------- PAGINATION MES PUBLICATIONS ----------
$limit = 5;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$total = $pubController->countUserPublications($idUser);
$totalPages = ceil($total/$limit);

$myPublications = $pubController->listUserPublications($idUser, $limit, $offset);

// ---------- PAGINATION FEED ----------
$feedLimit = 5;
$feedPage = isset($_GET['feed_page']) ? max(1,intval($_GET['feed_page'])) : 1;
$feedOffset = ($feedPage-1)*$feedLimit;

$totalFeed = $pubController->countAllPublications();
$totalFeedPages = ceil($totalFeed/$feedLimit);

$feedPublications = $pubController->listAllPublications($feedLimit, $feedOffset);

// Section active
$activeSection = isset($_GET['section']) && $_GET['section'] === 'feed' ? 'feed' : 'mes';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Communauté - Cyber Security</title>
    <link rel="stylesheet" href="communautefront.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #0c0c1a;
            color: #fff;
        }

        #sidebar {
            width: 220px;
            background-color: #1f1f2e;
            height: 100vh;
            padding: 20px;
        }
        #sidebar h2 { color: #7a3ff2; }
        .side-btn {
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background-color: #4a4aff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            transition: 0.3s;
        }
        .side-btn.btn-active { background-color: #00bfff; color: #fff; }

        #main-content { padding: 20px; width: 100%; }

        section h2 { color: #00bfff; margin-bottom: 15px; }

        /* FORMULAIRE */
        #formPublication textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: none;
            background-color: #1a1a2e;
            color: #fff;
            resize: none;
            font-size: 1em;
            margin-bottom: 10px;
        }
        #formPublication input[type="file"] { margin-bottom: 10px; }
        #formPublication button {
            background: linear-gradient(90deg, #7a3ff2, #00bfff);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }
        #formPublication button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px #7a3ff2;
        }

        /* CARDS */
        .post-card {
            border: 1px solid #3a3a7a;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #1a1a2e;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.5);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0px 5px 15px rgba(0,0,0,0.7);
        }
        .post-header { font-size: 14px; color: #88aaff; margin-bottom: 8px; }
        .post-content { font-size: 16px; margin-bottom: 12px; color: #fff; }
        .post-file a { color: #00bfff; text-decoration: none; font-size: 14px; }
        .post-file a:hover { text-decoration: underline; }
        .post-actions a { margin-right: 10px; text-decoration: none; font-size: 14px; color: #88aaff; transition: color 0.2s; }
        .post-actions a:hover { color: #00bfff; }

        /* PAGINATION */
        .pagination { margin-top: 10px; }
        .pagination a { margin-right: 5px; text-decoration: none; color:#00bfff; padding: 5px 10px; border-radius: 5px; background: #1f1f2e; transition: 0.3s; }
        .pagination a:hover { background: #4a4aff; color: #fff; }
        .pagination a[style] { font-weight:bold; background:#7a3ff2; color:white; }

        .hidden { display: none; }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>Menu</h2>
    <button class="side-btn <?= $activeSection === 'mes' ? 'btn-active' : '' ?>" id="btnMesPublications">Mes Publications</button>
    <button class="side-btn <?= $activeSection === 'feed' ? 'btn-active' : '' ?>" id="btnFeed">Feed</button>
</div>

<div id="main-content">

    <!-- MES PUBLICATIONS -->
    <section id="mes-publications-section" class="<?= $activeSection === 'mes' ? '' : 'hidden' ?>">
        <h2>Ajouter une publication</h2>
        <form id="formPublication" action="" method="POST" enctype="multipart/form-data">
            <textarea name="texte" id="postText" rows="6" placeholder="Écrivez quelque chose..."></textarea><br>
            <input type="file" name="file" id="fileInput"><br>
            <button type="submit">Publier</button>
        </form>

        <h2>Mes publications</h2>
        <div id="my-publications-list">
            <?php if(!empty($myPublications)): ?>
                <?php foreach($myPublications as $p): ?>
                    <div class="post-card">
                        <div class="post-header"><strong><?= htmlspecialchars($p['nom']) ?></strong></div>
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($p['texte'])) ?>
                            <?php if(!empty($p['fichier'])): ?>
                                <div class="post-file">
                                    <a href="../../<?= htmlspecialchars($p['fichier']) ?>" target="_blank">Voir fichier</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="post-actions">
                            <a href="editPublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Modifier</a>
                            <a href="deletePublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                            <a href="likePublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Like</a>
                            <a href="dislikePublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Dislike</a>
                            <a href="commentPublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Commenter</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune publication.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination Mes Publications -->
        <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                    <a href="?id_utilisateur=<?= $idUser ?>&page=<?= $i ?>" <?= $i==$page?'style="font-weight:bold;"':'' ?>><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- FEED -->
    <section id="feed-section" class="<?= $activeSection === 'feed' ? '' : 'hidden' ?>">
        <h2>Feed global</h2>
        <div id="feed-list">
            <?php if(!empty($feedPublications)): ?>
                <?php foreach($feedPublications as $f): ?>
                    <div class="post-card">
                        <div class="post-header"><strong><?= htmlspecialchars($f['nom']) ?></strong></div>
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($f['texte'])) ?>
                            <?php if(!empty($f['fichier'])): ?>
                                <div class="post-file">
                                    <a href="../../<?= htmlspecialchars($f['fichier']) ?>" target="_blank">Voir fichier</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="post-actions">
                            <a href="likePublicationFront.php?id=<?= $f['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Like</a>
                            <a href="dislikePublicationFront.php?id=<?= $f['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Dislike</a>
                            <a href="commentPublicationFront.php?id=<?= $f['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Commenter</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune publication.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination Feed -->
        <?php if($totalFeedPages > 1): ?>
            <div class="pagination">
                <?php for($i=1; $i<=$totalFeedPages; $i++): ?>
                    <a href="addPublication.php?id_utilisateur=<?= $idUser ?>&feed_page=<?= $i ?>&section=feed" <?= $i==$feedPage?'style="font-weight:bold;"':'' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<script>
// Gestion boutons
const btnMes = document.getElementById('btnMesPublications');
const btnFeed = document.getElementById('btnFeed');
const secMes = document.getElementById('mes-publications-section');
const secFeed = document.getElementById('feed-section');

function showSection(section) {
    if(section === 'feed'){
        secFeed.style.display='block';
        secMes.style.display='none';
        btnFeed.classList.add('btn-active');
        btnMes.classList.remove('btn-active');
    } else {
        secMes.style.display='block';
        secFeed.style.display='none';
        btnMes.classList.add('btn-active');
        btnFeed.classList.remove('btn-active');
    }
}

btnMes.onclick = () => showSection('mes');
btnFeed.onclick = () => showSection('feed');
</script>

<script>
// Contrôle JS du formulaire (compatible HTML4)
const form = document.getElementById('formPublication');
form.addEventListener('submit', function(e){
    const texte = document.getElementById('postText').value.trim();
    if(texte===''){ 
        alert('Le texte ne peut pas être vide !'); 
        e.preventDefault(); 
    }
    if(texte.length>200){ 
        alert('Le texte ne peut pas dépasser 200 caractères !'); 
        e.preventDefault(); 
    }
});
</script>


</body>
</html>
