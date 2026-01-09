<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Publication.php';
require_once __DIR__ . '/BaseController.php';

class PublicationController extends BaseController {
   
    // ========== MÉTHODES COMMUNES ==========

    // Ajouter une publication (CORRIGÉ avec gestion d'erreur)
    public function addPublication(Publication $p) {
        try {
            $sql = "INSERT INTO publication (id_utilisateur, texte, fichier, type_fichier, date_publication) 
                    VALUES (:id_utilisateur, :texte, :fichier, :type_fichier, :date_publication)";
            $query = $this->db->prepare($sql);
            
            $success = $query->execute([
                'id_utilisateur' => $p->getIdUtilisateur(),
                'texte' => $p->getTexte(),
                'fichier' => $p->getFichier(),
                'type_fichier' => $p->getTypeFichier(),
                'date_publication' => $p->getDatePublication() 
                    ? $p->getDatePublication()->format('Y-m-d H:i:s') 
                    : date('Y-m-d H:i:s') // Valeur par défaut si null
            ]);
            
            if (!$success) {
                throw new Exception("Erreur lors de l'insertion dans la base de données");
            }
            
            return $this->db->lastInsertId(); // Retourne l'ID de la publication insérée
            
        } catch (Exception $e) {
            error_log("Erreur addPublication: " . $e->getMessage());
            throw $e;
        }
    }

    // Récupérer une publication par ID
    public function getPublicationById($id_publication) {
        $sql = "SELECT p.*, u.name 
                FROM publication p
                JOIN users u ON p.id_utilisateur = u.id
                WHERE p.id_publication = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id_publication]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Modifier une publication
    public function updatePublication(Publication $p, $id_publication = null) {
        // Gestion de l'ID (pour compatibilité avec les deux anciennes versions)
        $publicationId = $id_publication ?: $p->getIdPublication();
        
        $sql = "UPDATE publication SET 
                    texte = :texte,
                    fichier = :fichier,
                    type_fichier = :type_fichier
                WHERE id_publication = :id";
        $query = $this->db->prepare($sql);
        $query->execute([
            'id' => $publicationId,
            'texte' => $p->getTexte(),
            'fichier' => $p->getFichier(),
            'type_fichier' => $p->getTypeFichier()
        ]);
    }

    // Supprimer une publication (CORRIGÉ avec gestion des contraintes)
    public function deletePublication($id_publication) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // 1. Supprimer d'abord les réactions associées
            $sqlReactions = "DELETE FROM reaction WHERE id_publication = :id";
            $queryReactions = $this->db->prepare($sqlReactions);
            $queryReactions->execute(['id' => $id_publication]);
            
            // 2. Supprimer d'abord les commentaires associés
            $sqlComments = "DELETE FROM commentaire WHERE id_publication = :id";
            $queryComments = $this->db->prepare($sqlComments);
            $queryComments->execute(['id' => $id_publication]);
            
            // 3. Supprimer d'abord l'historique associé
            $sqlHistory = "DELETE FROM historique_publication WHERE id_publication = :id";
            $queryHistory = $this->db->prepare($sqlHistory);
            $queryHistory->execute(['id' => $id_publication]);
            
            // 4. Maintenant supprimer la publication
            $sql = "DELETE FROM publication WHERE id_publication = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id_publication]);
            
            // Valider la transaction
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw $e;
        }
    }

    // ========== MÉTHODES FRONT ==========

        // ========== FEED INTELLIGENT ==========

    /**
     * Récupère le fil d'actualité intelligent
     * 
     * @param string $mode : 'recent', 'popular', 'commented', 'relevant', 'trending'
     * @return array
     */
    public function getFilIntelligent($mode = 'recent') {
        $sql = "SELECT p.*, u.name,
                       (SELECT COUNT(*) FROM reaction r WHERE r.id_publication = p.id_publication AND r.type_reaction = 'like') AS nb_likes,
                       (SELECT COUNT(*) FROM commentaire c WHERE c.id_publication = p.id_publication) AS nb_commentaires
                FROM publication p
                JOIN users u ON p.id_utilisateur = u.id ";

        switch ($mode) {
            case 'recent':
                $sql .= " ORDER BY p.date_publication DESC";
                break;

            case 'popular':
                $sql .= " ORDER BY nb_likes DESC, p.date_publication DESC";
                break;

            case 'commented':
                $sql .= " ORDER BY nb_commentaires DESC, p.date_publication DESC";
                break;

            case 'relevant':
                $sql .= " ORDER BY (nb_likes + nb_commentaires) DESC, p.date_publication DESC";
                break;

            case 'trending':
                $sql .= " WHERE DATE(p.date_publication) = CURDATE()
                          ORDER BY (nb_likes + nb_commentaires) DESC, p.date_publication DESC";
                break;

            default:
                $sql .= " ORDER BY p.date_publication DESC";
                break;
        }

        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les publications de l'utilisateur (avec pagination)
    public function listUserPublications($idUser, $limit = null, $offset = null) {
        $sql = "SELECT p.*, u.name 
                FROM publication p
                JOIN users u ON p.id_utilisateur = u.id
                WHERE p.id_utilisateur = :idUser
                ORDER BY p.id_publication DESC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $query = $this->db->prepare($sql);
        $query->bindValue(':idUser', $idUser, PDO::PARAM_INT);

        if ($limit !== null && $offset !== null) {
            $this->bindPaginationParams($query, $limit, $offset);
        }

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les publications utilisateur
    public function countUserPublications($id_utilisateur = null) {
        if ($id_utilisateur) {
            // Version front : compter pour un utilisateur spécifique
            $sql = "SELECT COUNT(*) AS total FROM publication WHERE id_utilisateur = :id_utilisateur";
            $query = $this->db->prepare($sql);
            $query->execute(['id_utilisateur' => $id_utilisateur]);
            return $query->fetch(PDO::FETCH_ASSOC)['total'];
        } else {
            // Version back : compter toutes les publications utilisateur (role user)
            $sql = "SELECT COUNT(*) AS total
                    FROM publication p
                    JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                    WHERE u.role = 'user'";
            $result = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        }
    }

    // Feed global
    public function listAllPublications($limit = 5, $offset = 0) {
        $sql = "SELECT p.*, u.name 
                FROM publication p
                -- JOIN users u ON p.id_utilisateur = u.id
                ORDER BY id_publication DESC
                LIMIT :limit OFFSET :offset";

        $query = $this->db->prepare($sql);
        $this->bindPaginationParams($query, $limit, $offset);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== MÉTHODES BACK ==========

    // Récupérer les publications des administrateurs
    public function listAdminPublications($limit = 5, $offset = 0) {
        $sql = "SELECT p.*, u.name 
                FROM publication p
                JOIN users u ON p.id_utilisateur = u.id
                WHERE u.role = 'admin'
                ORDER BY p.id_publication DESC
                LIMIT :limit OFFSET :offset";

        $query = $this->db->prepare($sql);
        $this->bindPaginationParams($query, $limit, $offset);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les publications des utilisateurs (pour le backoffice)
    public function listUserPublicationsBack($limit, $offset) {
        $sql = "SELECT p.*, u.name, u.email 
                FROM publication p
                JOIN users u ON p.id_utilisateur = u.id
                WHERE u.role = 'student'
                ORDER BY p.id_publication DESC
                LIMIT :limit OFFSET :offset";

        $query = $this->db->prepare($sql);
        $this->bindPaginationParams($query, $limit, $offset);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter toutes les publications
    public function countAllPublications() {
        $sql = "SELECT COUNT(*) AS total FROM publication";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Récupérer les réactions d'une publication
    public function getReactionsByPublication($id_publication) {
        $sql = "SELECT type_reaction, COUNT(*) as total
                FROM reaction
                WHERE id_publication = :id
                GROUP BY type_reaction";

        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id_publication]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter à l'historique
    public function addToHistory(Publication $p, $id_utilisateur) {
        $sql = "INSERT INTO historique_publication
                (id_publication, texte, fichier, type_fichier, date_modification, id_utilisateur)
                VALUES (:id_publication, :texte, :fichier, :type_fichier, NOW(), :id_utilisateur)";

        $query = $this->db->prepare($sql);
        $query->execute([
            'id_publication' => $p->getIdPublication(),
            'texte' => $p->getTexte(),
            'fichier' => $p->getFichier(),
            'type_fichier' => $p->getTypeFichier(),
            'id_utilisateur' => $id_utilisateur
        ]);
    }

    // Supprimer publication utilisateur (avec gestion des erreurs)
    public function deleteUserPublication($id) {
        try {
            $result = $this->deletePublication($id);
            return ['success' => true, 'message' => 'Publication deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting publication: ' . $e->getMessage()];
        }
    }
}

// ========== GESTION DES REQUÊTES AJAX (Backoffice) ==========
if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new PublicationController();
    
    // Headers pour JSON
    header('Content-Type: application/json');
    
    try {
        if (isset($_GET['action']) && $_GET['action'] === 'listUserPublications') {
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $offset = ($page - 1) * $limit;
            
            // Récupérer les publications
            $publications = $controller->listUserPublicationsBack($limit, $offset);
            $total = $controller->countUserPublications(); // Sans paramètre = count users
            $totalPages = ceil($total / $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $publications,
                'total' => $total,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
            exit;
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'deleteUserPublication') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid publication ID']);
                exit;
            }
            
            $result = $controller->deleteUserPublication($id);
            echo json_encode($result);
            exit;
        }
        
        // Si aucune action valide n'est trouvée
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}
?>