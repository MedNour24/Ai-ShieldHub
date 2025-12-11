<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';

$controller = new PublicationController();

// ---------- PAGINATION ----------
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$total = $controller->countPublications();
$totalPages = ceil($total / $limit);

// Publications paginées
$publications = $controller->listPublications($limit, $offset);
?>

<section class="right">
  <div class="card table-card">
    <h2>Publications (utilisateurs)</h2>
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
        <?php foreach ($publications as $post): ?>
          <tr>
            <td><?= htmlspecialchars($post['id_publication']) ?></td>
            <td><?= nl2br(htmlspecialchars($post['texte'])) ?></td>
            <td>
              <?php if (!empty($post['fichier'])): ?>
                <a href="../../<?= htmlspecialchars($post['fichier']) ?>" target="_blank">Voir fichier</a>
              <?php else: ?>
                Aucun fichier
              <?php endif; ?>
            </td>
            <td><?= $post['nb_likes'] ?? 0 ?></td>
            <td><?= $post['nb_dislikes'] ?? 0 ?></td>
            <td>
              <a href="editPublication.php?id=<?= $post['id_publication'] ?>">Modifier</a> |
              <a href="deletePublication.php?id=<?= $post['id_publication'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($publications)): ?>
          <tr><td colspan="7">Aucune publication trouvée.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card table-card">
    <h2>Commentaires (tous)</h2>
    <table id="commentsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Post ID</th>
          <th>Texte</th>
          <th>Auteur</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($publications as $post){
            $comments = $controller->getCommentsByPublication($post['id_publication']);
            foreach($comments as $c){
                echo "<tr>";
                echo "<td>{$c['id']}</td>";
                echo "<td>{$c['id_publication']}</td>";
                echo "<td>".nl2br(htmlspecialchars($c['contenu']))."</td>";
                echo "<td>{$c['nom_utilisateur']}</td>";
                echo "<td>
                        <a href='#' onclick=\"alert('Fonction éditer à implémenter')\">Modifier</a> | 
                        <a href='#' onclick=\"alert('Fonction supprimer à implémenter')\">Supprimer</a>
                      </td>";
                echo "</tr>";
            }
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION -->
  <div class="pagination">
    <a href="?page=<?= max(1, $page-1) ?>" class="<?= $page==1?'disabled':'' ?>">⬅ Précédent</a>
    <?php for($i=1;$i<=$totalPages;$i++): ?>
      <a href="?page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($totalPages,$page+1) ?>" class="<?= $page==$totalPages?'disabled':'' ?>">Suivant ➡</a>
  </div>
</section>
