<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Publication.php';

class PublicationController {

   public function listAdminPublications($limit = 5, $offset = 0) {
     $sql = "SELECT p.*, u.nom 
            FROM publication p
            JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
            WHERE u.role = 'admin'
            ORDER BY p.id_publication DESC
            LIMIT :limit OFFSET :offset";

    $db = config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':limit', $limit, PDO::PARAM_INT);
    $query->bindValue(':offset', $offset, PDO::PARAM_INT);
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

    // -------- COUNT --------
    public function countPublications() {
        $sql = "SELECT COUNT(*) AS total FROM publication";
        $db = config::getConnexion();

        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // -------- ADD --------
    public function addPublication(Publication $p) {
        $sql = "INSERT INTO publication 
                (id_utilisateur, texte, fichier, type_fichier, date_publication) 
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

    // -------- DELETE --------
    public function deletePublication($id_publication) {
        $sql = "DELETE FROM publication WHERE id_publication = :id";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id_publication);

        $query->execute();
    }

    // -------- UPDATE --------
    public function updatePublication(Publication $p, $id_publication) {
        $sql = "UPDATE publication SET 
                    texte = :texte,
                    fichier = :fichier,
                    type_fichier = :type_fichier
                WHERE id_publication = :id";

        $db = config::getConnexion();
        $query = $db->prepare($sql);

        $query->execute([
            'id' => $id_publication,
            'texte' => $p->getTexte(),
            'fichier' => $p->getFichier(),
            'type_fichier' => $p->getTypeFichier()
        ]);
    }

    // -------- GET BY ID --------
    public function getPublicationById($id_publication) {
        $sql = "SELECT * FROM publication WHERE id_publication = :id";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id_publication]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getReactionsByPublication($id_publication) {
        $sql = "SELECT type_reaction, COUNT(*) as total
                FROM reaction
                WHERE id_publication = :id
                GROUP BY type_reaction";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id_publication]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addToHistory(Publication $p, $id_utilisateur) {
        $sql = "INSERT INTO historique_publication
                (id_publication, texte, fichier, type_fichier, date_modification, id_utilisateur)
                VALUES (:id_publication, :texte, :fichier, :type_fichier, NOW(), :id_utilisateur)";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id_publication' => $p->getIdPublication(),
            'texte' => $p->getTexte(),
            'fichier' => $p->getFichier(),
            'type_fichier' => $p->getTypeFichier(),
            'id_utilisateur' => $id_utilisateur
        ]);
    }

    // -------- MÉTHODES PUBUSER (UNE SEULE DÉCLARATION) --------
    public function listUserPublications($limit, $offset) {
        $sql = "SELECT p.*, u.nom, u.email 
                FROM publication p
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                WHERE u.role = 'user'
                ORDER BY p.id_publication DESC
                LIMIT :limit OFFSET :offset";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUserPublications() {
        $sql = "SELECT COUNT(*) AS total
                FROM publication p
                JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
                WHERE u.role = 'user'";
        $db = config::getConnexion();
        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function deleteUserPublication($id) {
        try {
            $sql = "DELETE FROM publication WHERE id_publication = :id";
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            
            return ['success' => true, 'message' => 'Publication deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting publication: ' . $e->getMessage()];
        }
    }
}

// Gestion des requêtes AJAX
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
            $publications = $controller->listUserPublications($limit, $offset);
            $total = $controller->countUserPublications();
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