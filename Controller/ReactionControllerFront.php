<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Reaction.php';

class ReactionControllerFront {

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
}
?>