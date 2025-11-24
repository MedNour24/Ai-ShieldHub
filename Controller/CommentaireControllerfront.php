<?php
// CommentaireControllerfront.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Commentaire.php';

class CommentaireControllerfront {
    
    public function addCommentaire(Commentaire $commentaire) {
        $sql = "INSERT INTO commentaire (id_publication, id_utilisateur, texte, date_commentaire) 
                VALUES (:id_publication, :id_utilisateur, :texte, :date_commentaire)";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id_publication' => $commentaire->getIdPublication(),
            'id_utilisateur' => $commentaire->getIdUser(),
            'texte' => $commentaire->getContenu(),
            'date_commentaire' => $commentaire->getDateCommentaire()->format('Y-m-d H:i:s')
        ]);
        
        // Retourner l'ID du commentaire inséré
        return $db->lastInsertId();
    }
    
    public function getCommentairesByPublication($idPublication) {
        $sql = "SELECT c.id_commentaire as id, c.id_publication, c.id_utilisateur, c.texte as contenu, c.date_commentaire, u.nom 
                FROM commentaire c 
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                WHERE c.id_publication = :id_publication 
                ORDER BY c.date_commentaire DESC";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id_publication' => $idPublication]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentaireById($idCommentaire) {
        $sql = "SELECT c.id_commentaire as id, c.id_publication, c.id_utilisateur, c.texte as contenu, c.date_commentaire, u.nom 
                FROM commentaire c 
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                WHERE c.id_commentaire = :id_commentaire";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id_commentaire' => $idCommentaire]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCommentaire(Commentaire $commentaire) {
        $sql = "UPDATE commentaire SET texte = :texte WHERE id_commentaire = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'texte' => $commentaire->getContenu(),
            'id' => $commentaire->getId()
        ]);
    }

    public function deleteCommentaire($id) {
        $sql = "DELETE FROM commentaire WHERE id_commentaire = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
    }
}
?>