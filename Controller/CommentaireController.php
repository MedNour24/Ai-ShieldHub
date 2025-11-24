<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Commentaire.php';

class CommentaireController {

    public function addCommentaire(Commentaire $c) {
        $sql = "INSERT INTO commentaire (id_publication, id_utilisateur, texte, date_commentaire) 
                VALUES (:id_publication, :id_utilisateur, :texte, :date_commentaire)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $result = $query->execute([
                'id_publication' => $c->getIdPublication(),
                'id_utilisateur' => $c->getIdUser(),
                'texte' => $c->getContenu(),
                'date_commentaire' => $c->getDateCommentaire()->format('Y-m-d H:i:s')
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return false;
        }
    }

    public function listCommentairesByPublication($idPublication) {
        $sql = "SELECT c.*, u.nom, u.email 
                FROM commentaire c 
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                WHERE c.id_publication = :id_publication
                ORDER BY c.date_commentaire DESC";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_publication' => $idPublication]);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Handle AJAX request
            if (isset($_GET['action']) && $_GET['action'] === 'listCommentairesByPublication') {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error listing comments: " . $e->getMessage());
            
            // Handle AJAX request error
            if (isset($_GET['action']) && $_GET['action'] === 'listCommentairesByPublication') {
                header('Content-Type: application/json');
                echo json_encode([]);
                exit();
            }
            
            return [];
        }
    }

    public function deleteCommentaire($idCommentaire) {
        $sql = "DELETE FROM commentaire WHERE id_commentaire = :id_commentaire";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $result = $query->execute(['id_commentaire' => $idCommentaire]);
            
            // Handle AJAX request
            if (isset($_POST['action']) && $_POST['action'] === 'deleteCommentaire') {
                header('Content-Type: application/json');
                echo json_encode(['success' => $result]);
                exit();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting comment: " . $e->getMessage());
            return false;
        }
    }

    public function updateCommentaire(Commentaire $c, $idCommentaire) {
        $sql = "UPDATE commentaire SET texte = :texte WHERE id_commentaire = :id_commentaire";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $result = $query->execute([
                'id_commentaire' => $idCommentaire,
                'texte' => $c->getContenu()
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating comment: " . $e->getMessage());
            return false;
        }
    }

    public function getCommentaireById($idCommentaire) {
        $sql = "SELECT c.*, u.nom, u.email 
                FROM commentaire c 
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                WHERE c.id_commentaire = :id_commentaire";
        
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_commentaire' => $idCommentaire]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting comment by ID: " . $e->getMessage());
            return null;
        }
    }

    public function countCommentairesByPublication($idPublication) {
        $sql = "SELECT COUNT(*) as total FROM commentaire WHERE id_publication = :id_publication";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_publication' => $idPublication]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error counting comments: " . $e->getMessage());
            return 0;
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new CommentaireController();
    
    if (isset($_GET['action']) && $_GET['action'] === 'listCommentairesByPublication' && isset($_GET['id_publication'])) {
        $controller->listCommentairesByPublication($_GET['id_publication']);
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'deleteCommentaire' && isset($_POST['id_commentaire'])) {
        $controller->deleteCommentaire($_POST['id_commentaire']);
    }
}
?>