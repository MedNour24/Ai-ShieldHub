<?php
require_once __DIR__ . '/../../Controller/PublicationController.php';

if (!isset($_GET['id'])) {
    die("ID de publication manquant.");
}

$id_publication = intval($_GET['id']);
$controller = new PublicationController();

$publication = $controller->getPublicationById($id_publication);
$comments = $controller->getCommentsByPublication($id_publication);
$reactions = $controller->getReactionsByPublication($id_publication);

// Historique
$history = $controller->getPublicationHistory($id_publication);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail Publication</title>
</head>
<body>
    <h2>Détail Publication #<?= $publication['id_publication'] ?></h2>

    <p><strong>Utilisateur :</strong> <?= htmlspecialchars($publication['id_utilisateur']) ?></p>
    <p><strong>Texte :</strong><br><?= nl2br(htmlspecialchars($publication['texte'])) ?></p>

    <p><strong>Fichier :</strong> 
        <?php if (!empty($publication['fichier'])): ?>
            <a href="<?= htmlspecialchars($publication['fichier']) ?>" target="_blank">📄 Voir fichier</a>
        <?php else: ?>
            Aucun fichier
        <?php endif; ?>
    </p>

    <p><strong>Date publication :</strong> <?= $publication['date_publication'] ?></p>

    <h3>Réactions</h3>
    <ul>
        <?php foreach ($reactions as $r): ?>
            <li><?= htmlspecialchars($r['type_reaction']) ?> : <?= $r['total'] ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Commentaires</h3>
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $c): ?>
            <p><strong><?= htmlspecialchars($c['nom_utilisateur']) ?> :</strong> <?= nl2br(htmlspecialchars($c['contenu'])) ?>
            <br><em><?= $c['date_commentaire'] ?></em></p>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire.</p>
    <?php endif; ?>

    <h3>Historique des modifications</h3>
    <?php if (!empty($history)): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Date modification</th>
                <th>Texte</th>
                <th>Fichier</th>
                <th>Modifié par</th>
            </tr>
            <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= $h['date_modification'] ?></td>
                    <td><?= nl2br(htmlspecialchars($h['texte'])) ?></td>
                    <td>
                        <?php if (!empty($h['fichier'])): ?>
                            <a href="<?= htmlspecialchars($h['fichier']) ?>" target="_blank">📄 Voir fichier</a>
                        <?php else: ?>
                            Aucun fichier
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($h['nom_utilisateur']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Aucune modification précédente.</p>
    <?php endif; ?>

    <br>
    <a href="editPublication.php?id=<?= $id_publication ?>">✏ Modifier</a> |
    <a href="deletePublication.php?id=<?= $id_publication ?>" onclick="return confirm('Supprimer cette publication ?')">🗑 Supprimer</a> |
    <a href="listPublications.php">⬅ Retour à la liste</a>
</body>
</html>
