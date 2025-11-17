<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Publication.php';

class PublicationControllerFront {

    // Ajouter une publication
    public function addPublication(Publication $p) {
        $sql = "INSERT INTO publication (id_utilisateur, texte, fichier, type_fichier, date_publication) 
                VALUES (:id_utilisateur, :texte, :fichier, :type_fichier, :date_publication)";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id_utilisateur' => $p->getIdUtilisateur(),
            'texte' => $p->getTexte(),
            'fichier' => $p->getFichier(),
            'type_fichier' => $p->getTypeFichier(),
            'date_publication' => $p->getDatePublication() 
                ? $p->getDatePublication()->format('Y-m-d H:i:s') 
                : null
        ]);
    }

    // Récupérer toutes les publications de l'utilisateur (avec pagination)
    public function listUserPublications($idUser, $limit = null, $offset = null) {
        $sql = "SELECT p.*, u.nom 
                FROM publication p
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                WHERE p.id_utilisateur = :idUser
                ORDER BY p.id_publication DESC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':idUser', $idUser, PDO::PARAM_INT);

        if ($limit !== null && $offset !== null) {
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les publications utilisateur
    public function countUserPublications($id_utilisateur) {
        $sql = "SELECT COUNT(*) AS total FROM publication WHERE id_utilisateur = :id_utilisateur";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id_utilisateur' => $id_utilisateur]);
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Feed global
    public function listAllPublications($limit = 5, $offset = 0) {
        $sql = "SELECT p.*, u.nom 
                FROM publication p
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                ORDER BY id_publication DESC
                LIMIT :limit OFFSET :offset";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter toutes les publications
    public function countAllPublications() {
        $sql = "SELECT COUNT(*) AS total FROM publication";
        $db = config::getConnexion();
        return $db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Récupérer une publication
    public function getPublicationById($id) {
        $sql = "SELECT * FROM publication WHERE id_publication = ?";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Modifier une publication
    public function updatePublication(Publication $p) {
    $sql = "UPDATE publication SET texte = :texte, fichier = :fichier WHERE id_publication = :id";
    $db = config::getConnexion();
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':texte' => $p->getTexte(),
        ':fichier' => $p->getFichier(),
        ':id' => $p->getIdPublication()
    ]);
}


    // Supprimer une publication
    public function deletePublication($id) {
        $sql = "DELETE FROM publication WHERE id_publication = ?";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
    }
}
?>
