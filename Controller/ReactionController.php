<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Reaction.php';

class ReactionController {

    // === MÉTHODES POUR LES RÉACTIONS (comme le front) ===
    
    public function addOrUpdateReaction(Reaction $r) {
        $db = config::getConnexion();

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
                return $result;
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
                return $result;
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
            return $result;
        }
    }

    public function countReactions($idPublication, $type) {
        $sql = "SELECT COUNT(*) as count FROM reaction 
                WHERE id_publication = :id AND type_reaction = :type";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $idPublication,
                'type' => $type
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error counting reactions: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserReaction($idPublication, $idUser) {
        $sql = "SELECT type_reaction FROM reaction 
                WHERE id_publication = :idPub AND id_utilisateur = :idUser";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'idPub' => $idPublication,
                'idUser' => $idUser
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['type_reaction'] : null;
        } catch (PDOException $e) {
            error_log("Error getting user reaction: " . $e->getMessage());
            return null;
        }
    }

    public function getReactionsSummary($idPublication) {
        $db = config::getConnexion();
        
        $sql = "SELECT type_reaction, COUNT(*) as count 
                FROM reaction 
                WHERE id_publication = :idPub 
                GROUP BY type_reaction";
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['idPub' => $idPublication]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $summary = ['like' => 0, 'dislike' => 0];
            foreach ($results as $row) {
                $summary[$row['type_reaction']] = (int)$row['count'];
            }
            
            return $summary;
        } catch (PDOException $e) {
            error_log("Error getting reactions summary: " . $e->getMessage());
            return ['like' => 0, 'dislike' => 0];
        }
    }

    // === MÉTHODES POUR L'ADMINISTRATION ===
    
    public function getAllReactions($limit = 50, $offset = 0) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, p.texte as publication_texte
                FROM reaction r
                JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                JOIN publication p ON r.id_publication = p.id_publication
                ORDER BY r.date_reaction DESC
                LIMIT :limit OFFSET :offset";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->bindValue(':offset', $offset, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all reactions: " . $e->getMessage());
            return [];
        }
    }

    public function countAllReactions() {
        $sql = "SELECT COUNT(*) as total FROM reaction";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error counting all reactions: " . $e->getMessage());
            return 0;
        }
    }

    public function deleteReaction($idReaction) {
        $sql = "DELETE FROM reaction WHERE id_reaction = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $result = $query->execute(['id' => $idReaction]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting reaction: " . $e->getMessage());
            return false;
        }
    }

    public function getReactionById($idReaction) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, p.texte as publication_texte
                FROM reaction r
                JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                JOIN publication p ON r.id_publication = p.id_publication
                WHERE r.id_reaction = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $idReaction]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reaction by ID: " . $e->getMessage());
            return null;
        }
    }

    // Statistiques pour le dashboard admin
    public function getReactionsStats() {
        $sql = "SELECT 
                    type_reaction,
                    COUNT(*) as total,
                    DATE(date_reaction) as date,
                    COUNT(DISTINCT id_utilisateur) as unique_users
                FROM reaction 
                WHERE date_reaction >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY type_reaction, DATE(date_reaction)
                ORDER BY date DESC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reactions stats: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer toutes les réactions d'une publication (pour la modal de détails)
    public function getReactionsByPublication($idPublication) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.email
                FROM reaction r
                JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                WHERE r.id_publication = :idPub
                ORDER BY r.date_reaction DESC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['idPub' => $idPublication]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reactions by publication: " . $e->getMessage());
            return [];
        }
    }
}
?>