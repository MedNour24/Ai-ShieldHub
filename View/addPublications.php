<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Model/Publication.php';

$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
if ($idUser <= 0) die("ID utilisateur non spécifié dans l'URL !");

$pubController = new PublicationController();

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

    // Redirection après ajout
    header("Location: addPublications.php?id_utilisateur=".$idUser."&page=1");
    exit();
}

// ---------- PAGINATION ADMIN ----------
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$total = $pubController->countPublications();
$totalPages = ceil($total / $limit);

// Récupération paginée
$publications = $pubController->listAdminPublications($limit, $offset);



// Pagination pour les users
$userLimit = 5;
$userPage = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
$userOffset = ($userPage - 1) * $userLimit;

$totalUser = $pubController->countUserPublications();
$totalUserPages = ceil($totalUser / $userLimit);

// Publications des utilisateurs normaux
$userPublications = $pubController->listUserPublications($userLimit, $userOffset);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Module Communaute</title>
<link rel="stylesheet" href="communauteback.css" />
</head>
<body>
<header class="topbar">
<div class="brand">
  <h1>Admin Communaute Cyber Security</h1>
  <p>Tableau de controle moderation & gestion</p>
</div>
</header>

<main class="container">
<section class="left">
  <div class="card">
    <h2>Ajouter / Modifier une publication</h2>
    <form action="" method="POST" enctype="multipart/form-data" id="formPublication">
      <input type="hidden" name="id_utilisateur" value="<?= $idUser ?>" />
      <input type="hidden" name="id_publication" id="idPublication">

      <label>Texte :</label>
      <textarea name="texte" id="textePublication" rows="5" placeholder="Texte de la publication..."></textarea><br><br>

      <label>Fichier (PDF / Video / TXT / DOCX) :</label>
      <input type="file" name="file" id="filePublication" accept=".pdf,video/*,.txt,.docx"><br><br>

      <div class="form-row">
          <button type="submit" class="btn primary" id="submitPublication">Publier</button>
          <button type="reset" class="btn" id="resetPublication">Annuler</button>
      </div>
    </form>
    <div id="messages" style="color:red; margin-top:10px;"></div>
  </div>

  <div class="card">
    <h2>Actions rapides</h2>
    <button id="seedDataBtn" class="btn">Generer exemples (reset)</button>
    <button id="clearAllBtn" class="btn danger">Supprimer tout</button>
  </div>
</section>

<section class="right">
  <div class="card table-card">
    <h2>Mes Publications (admin)</h2>
    <table id="postsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Texte</th>
          <th>Fichier</th>
          <th>Likes</th>
          <th>Dislikes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($publications)): ?>
          <?php foreach ($publications as $post): ?>
            <tr>
              <td><?= htmlspecialchars($post['id_publication']) ?></td>
              <td><?= nl2br(htmlspecialchars($post['texte'])) ?></td>
              <td>
                <?php if(!empty($post['fichier'])): ?>
                  <a href="../../<?= htmlspecialchars($post['fichier']) ?>" target="_blank">Voir fichier</a>
                <?php else: ?>Aucun fichier<?php endif; ?>
              </td>
              <td><?= $post['nb_likes'] ?? 0 ?></td>
              <td><?= $post['nb_dislikes'] ?? 0 ?></td>
              <td>
                <a href="editPublication.php?id=<?= $post['id_publication'] ?>&id_utilisateur=<?= $idUser ?>">Modifier</a> |
                <a href="deletePublication.php?id=<?= $post['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" 
                   onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6">Aucune publication trouvée.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <div class="pagination">
      <a href="?id_utilisateur=<?= $idUser ?>&page=<?= max(1,$page-1) ?>" class="<?= $page==1?'disabled':'' ?>">⬅ Precedent</a>
      <?php for($i=1;$i<=$totalPages;$i++): ?>
        <a href="?id_utilisateur=<?= $idUser ?>&page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a href="?id_utilisateur=<?= $idUser ?>&page=<?= min($totalPages,$page+1) ?>" class="<?= $page==$totalPages?'disabled':'' ?>">Suivant ➡</a>
    </div>
  </div>

 <div class="card table-card">
  <h2>Publications des Users</h2>
  <table id="userPostsTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Texte</th>
        <th>Fichier</th>
        <th>Auteur</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($userPublications)): ?>
        <?php foreach($userPublications as $post): ?>
          <tr>
            <td><?= htmlspecialchars($post['id_publication']) ?></td>
            <td><?= nl2br(htmlspecialchars($post['texte'])) ?></td>
            <td>
              <?php if(!empty($post['fichier'])): ?>
                <a href="../../<?= htmlspecialchars($post['fichier']) ?>" target="_blank">Voir fichier</a>
              <?php else: ?>Aucun fichier<?php endif; ?>
            </td>
            <td><?= htmlspecialchars($post['nom']) ?></td>
            <td>
              <a href="deletePublication.php?id=<?= $post['id_publication'] ?>&id_utilisateur=<?= $post['id_utilisateur'] ?>" 
                 onclick="return confirm('Confirmer la suppression ?')">Supprimer</a> |
              <a href="detailsPublication.php?id=<?= $post['id_publication'] ?>">Détails</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5">Aucune publication trouvée.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination pour les utilisateurs -->
  <div class="pagination">
    <a href="?id_utilisateur=<?= $idUser ?>&user_page=<?= max(1,$userPage-1) ?>" class="<?= $userPage==1?'disabled':'' ?>">⬅ Précédent</a>
    <?php for($i=1;$i<=$totalUserPages;$i++): ?>
      <a href="?id_utilisateur=<?= $idUser ?>&user_page=<?= $i ?>" class="<?= $i==$userPage?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?id_utilisateur=<?= $idUser ?>&user_page=<?= min($totalUserPages,$userPage+1) ?>" class="<?= $userPage==$totalUserPages?'disabled':'' ?>">Suivant ➡</a>
  </div>
</div>


</section>
</main>

<!-- Contrôle de saisie avec JS -->
<script src="communauteback.js"></script>

</body>
</html>
