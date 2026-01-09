<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Reaction.php';
require_once __DIR__ . '/BaseController.php';

class ReactionController extends BaseController {

    // === MÉTHODES POUR LE FRONT ET LE BACK ===
    
    public function addOrUpdateReaction(Reaction $r) {
        $db = $this->db;

        // Vérifier si une réaction existe déjà
        $check = $db->prepare("SELECT * FROM reaction WHERE id_publication = :idPub AND id_utilisateur = :idUser");
        $check->execute([
            'idPub' => $r->getIdPublication(),
            'idUser' => $r->getIdUser()
        ]);

        $existingReaction = $check->fetch(PDO::FETCH_ASSOC);

        if ($existingReaction) {
            // Si l'utilisateur clique sur le même type, supprimer la réaction
            if ($existingReaction['type_reaction'] === $r->getTypeReaction()) {
                $sql = "DELETE FROM reaction WHERE id_publication = :idPub AND id_utilisateur = :idUser";
                $query = $db->prepare($sql);
                $result = $query->execute([
                    'idPub' => $r->getIdPublication(),
                    'idUser' => $r->getIdUser()
                ]);
                return $result ? 'deleted' : false;
            } else {
                // Sinon, mettre à jour le type
                $sql = "UPDATE reaction SET type_reaction = :type 
                        WHERE id_publication = :idPub AND id_utilisateur = :idUser";
                $query = $db->prepare($sql);
                $result = $query->execute([
                    'type' => $r->getTypeReaction(),
                    'idPub' => $r->getIdPublication(),
                    'idUser' => $r->getIdUser()
                ]);
                return $result ? 'updated' : false;
            }
        } else {
            // Insert nouvelle réaction
            $sql = "INSERT INTO reaction (id_publication, id_utilisateur, type_reaction, date_reaction)
                    VALUES (:idPub, :idUser, :type, NOW())";
            $query = $db->prepare($sql);
            $result = $query->execute([
                'type' => $r->getTypeReaction(),
                'idPub' => $r->getIdPublication(),
                'idUser' => $r->getIdUser()
            ]);
            return $result ? 'added' : false;
        }
    }

    public function countReactions($idPublication, $type) {
        return $this->executeQuery(function($db) use ($idPublication, $type) {
            $sql = "SELECT COUNT(*) as count FROM reaction 
                    WHERE id_publication = :id AND type_reaction = :type";
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $idPublication,
                'type' => $type
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        }, 0, "Error counting reactions");
    }

    public function getUserReaction($idPublication, $idUser) {
        return $this->executeQuery(function($db) use ($idPublication, $idUser) {
            $sql = "SELECT type_reaction FROM reaction 
                    WHERE id_publication = :idPub AND id_utilisateur = :idUser";
            $query = $db->prepare($sql);
            $query->execute([
                'idPub' => $idPublication,
                'idUser' => $idUser
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['type_reaction'] : null;
        }, null, "Error getting user reaction");
    }

    public function getReactionsSummary($idPublication) {
        return $this->executeQuery(function($db) use ($idPublication) {
            $sql = "SELECT type_reaction, COUNT(*) as count 
                    FROM reaction 
                    WHERE id_publication = :idPub 
                    GROUP BY type_reaction";
            
            $query = $db->prepare($sql);
            $query->execute(['idPub' => $idPublication]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $summary = ['like' => 0, 'dislike' => 0];
            foreach ($results as $row) {
                $summary[$row['type_reaction']] = (int)$row['count'];
            }
            
            return $summary;
        }, ['like' => 0, 'dislike' => 0], "Error getting reactions summary");
    }

    // === MÉTHODES SPÉCIFIQUES POUR L'ADMINISTRATION ===
    
    public function getAllReactions($limit = 50, $offset = 0) {
        return $this->executeQuery(function($db) use ($limit, $offset) {
            $sql = "SELECT r.*, u.nom as utilisateur_nom, p.texte as publication_texte
                    FROM reaction r
                    JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                    JOIN publication p ON r.id_publication = p.id_publication
                    ORDER BY r.date_reaction DESC
                    LIMIT :limit OFFSET :offset";
            
            $query = $db->prepare($sql);
            $this->bindPaginationParams($query, $limit, $offset);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }, [], "Error getting all reactions");
    }

    public function countAllReactions() {
        return $this->executeQuery(function($db) {
            $sql = "SELECT COUNT(*) as total FROM reaction";
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }, 0, "Error counting all reactions");
    }

    public function deleteReaction($idReaction) {
        $sql = "DELETE FROM reaction WHERE id_reaction = :id";
        
        try {
            $query = $this->db->prepare($sql);
            $result = $query->execute(['id' => $idReaction]);
            
            // Gérer les requêtes AJAX
            if (isset($_POST['action']) && $_POST['action'] === 'deleteReaction') {
                $this->sendJsonResponse($result, null);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting reaction: " . $e->getMessage());
            return false;
        }
    }

    public function getReactionById($idReaction) {
        return $this->executeQuery(function($db) use ($idReaction) {
            $sql = "SELECT r.*, u.nom as utilisateur_nom, p.texte as publication_texte
                    FROM reaction r
                    JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                    JOIN publication p ON r.id_publication = p.id_publication
                    WHERE r.id_reaction = :id";
            $query = $db->prepare($sql);
            $query->execute(['id' => $idReaction]);
            return $query->fetch(PDO::FETCH_ASSOC);
        }, null, "Error getting reaction by ID");
    }

    // Statistiques pour le dashboard admin
    public function getReactionsStats() {
        return $this->executeQuery(function($db) {
            $sql = "SELECT 
                        type_reaction,
                        COUNT(*) as total,
                        DATE(date_reaction) as date,
                        COUNT(DISTINCT id_utilisateur) as unique_users
                    FROM reaction 
                    WHERE date_reaction >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY type_reaction, DATE(date_reaction)
                    ORDER BY date DESC";
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }, [], "Error getting reactions stats");
    }
    
    // Récupérer toutes les réactions d'une publication (pour la modal de détails)
    public function getReactionsByPublication($idPublication) {
        return $this->executeQuery(function($db) use ($idPublication) {
            $sql = "SELECT r.*, u.nom as utilisateur_nom, u.email
                    FROM reaction r
                    JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                    WHERE r.id_publication = :idPub
                    ORDER BY r.date_reaction DESC";
            $query = $db->prepare($sql);
            $query->execute(['idPub' => $idPublication]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }, [], "Error getting reactions by publication");
    }
}

// Gérer les requêtes AJAX pour le back-office
if (isset($_POST['action'])) {
    $controller = new ReactionController();
    
    if ($_POST['action'] === 'deleteReaction' && isset($_POST['id_reaction'])) {
        $controller->deleteReaction($_POST['id_reaction']);
    }
}
?>