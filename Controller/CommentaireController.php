<?php
// CommentaireController.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Commentaire.php';
require_once __DIR__ . '/BaseController.php';

class CommentaireController extends BaseController {
    
    // Méthode pour ajouter un commentaire
    public function addCommentaire(Commentaire $commentaire) {
        return $this->executeQuery(function($db) use ($commentaire) {
            $sql = "INSERT INTO commentaire (id_publication, id_utilisateur, texte, date_commentaire) 
                    VALUES (:id_publication, :id_utilisateur, :texte, :date_commentaire)";
            
            $query = $db->prepare($sql);
            $result = $query->execute([
                'id_publication' => $commentaire->getIdPublication(),
                'id_utilisateur' => $commentaire->getIdUser(),
                'texte' => $commentaire->getContenu(),
                'date_commentaire' => $commentaire->getDateCommentaire()->format('Y-m-d H:i:s')
            ]);
            
            // Retourner l'ID du commentaire inséré pour le front, ou le résultat booléen pour le back
            return $result ? $db->lastInsertId() : false;
        }, false, "Error adding comment");
    }
    
    // Méthode pour récupérer les commentaires d'une publication
    public function getCommentairesByPublication($idPublication) {
        $sql = "SELECT c.id_commentaire as id, c.id_publication, c.id_utilisateur, c.texte as contenu, 
                       c.date_commentaire, u.name, u.email 
                FROM commentaire c 
                JOIN users u ON c.id_utilisateur = u.id
                WHERE c.id_publication = :id_publication 
                ORDER BY c.date_commentaire DESC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute(['id_publication' => $idPublication]);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Gérer les requêtes AJAX
            if (isset($_GET['action']) && $_GET['action'] === 'getCommentairesByPublication') {
                $this->sendJsonResponse(true, $result);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error listing comments: " . $e->getMessage());
            
            // Gérer les erreurs AJAX
            if (isset($_GET['action']) && $_GET['action'] === 'getCommentairesByPublication') {
                $this->sendJsonResponse(true, []);
            }
            
            return [];
        }
    }

    // Méthode pour récupérer un commentaire par son ID
    public function getCommentaireById($idCommentaire) {
        return $this->executeQuery(function($db) use ($idCommentaire) {
            $sql = "SELECT c.id_commentaire as id, c.id_publication, c.id_utilisateur, c.texte as contenu, 
                           c.date_commentaire, u.name, u.email 
                    FROM commentaire c 
                    JOIN users u ON c.id_utilisateur = u.id
                    WHERE c.id_commentaire = :id_commentaire";
            
            $query = $db->prepare($sql);
            $query->execute(['id_commentaire' => $idCommentaire]);
            return $query->fetch(PDO::FETCH_ASSOC);
        }, null, "Error getting comment by ID");
    }

    // Méthode pour mettre à jour un commentaire
    public function updateCommentaire(Commentaire $commentaire, $idCommentaire = null) {
        // Si l'ID n'est pas fourni en paramètre, on le prend de l'objet
        $id = $idCommentaire ?? $commentaire->getId();
        
        return $this->executeQuery(function($db) use ($commentaire, $id) {
            $sql = "UPDATE commentaire SET texte = :texte WHERE id_commentaire = :id";
            $query = $db->prepare($sql);
            return $query->execute([
                'texte' => $commentaire->getContenu(),
                'id' => $id
            ]);
        }, false, "Error updating comment");
    }

    // Méthode pour supprimer un commentaire
    public function deleteCommentaire($id) {
        $sql = "DELETE FROM commentaire WHERE id_commentaire = :id";
        
        try {
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $id]);
            
            // Gérer les requêtes AJAX
            if (isset($_POST['action']) && $_POST['action'] === 'deleteCommentaire') {
                $this->sendJsonResponse($result, null);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error deleting comment: " . $e->getMessage());
            return false;
        }
    }

    // Méthode pour compter les commentaires d'une publication
    public function countCommentairesByPublication($idPublication) {
        return $this->executeQuery(function($db) use ($idPublication) {
            $sql = "SELECT COUNT(*) as total FROM commentaire WHERE id_publication = :id_publication";
            $query = $db->prepare($sql);
            $query->execute(['id_publication' => $idPublication]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }, 0, "Error counting comments");
    }
}

// Gérer les requêtes AJAX
if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new CommentaireController();
    
    if (isset($_GET['action']) && $_GET['action'] === 'getCommentairesByPublication' && isset($_GET['id_publication'])) {
        $controller->getCommentairesByPublication($_GET['id_publication']);
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'deleteCommentaire' && isset($_POST['id_commentaire'])) {
        $controller->deleteCommentaire($_POST['id_commentaire']);
    }
}
?>