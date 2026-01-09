<?php
require_once __DIR__ . '/../Model/Tournoi.php';
require_once __DIR__ . '/BaseController.php';

class TournoiC extends BaseController
{
    // CREATE
    public function ajouterTournoi(Tournoi $tournoi): bool
    {
        return $this->executeQuery(function($db) use ($tournoi) {
            $sql = "INSERT INTO tournoi (nom, theme, niveau, date_debut, date_fin) 
                    VALUES (:nom, :theme, :niveau, :date_debut, :date_fin)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nom' => $tournoi->getNom(),
                ':theme' => $tournoi->getTheme(),
                ':niveau' => $tournoi->getNiveau(),
                ':date_debut' => $tournoi->getDateDebut(),
                ':date_fin' => $tournoi->getDateFin()
            ]);
            return true;
        }, false, "Erreur ajout tournoi");
    }

    // READ ALL
    public function afficherTournois(): array
    {
        return $this->executeQuery(function($db) {
            $sql = "SELECT * FROM tournoi ORDER BY date_debut DESC";
            $stmt = $db->query($sql);
            $tournois = [];
            
            while ($row = $stmt->fetch()) {
                $tournois[] = new Tournoi(
                    $row['id'],
                    $row['nom'],
                    $row['theme'],
                    $row['niveau'],
                    $row['date_debut'],
                    $row['date_fin']
                );
            }
            return $tournois;
        }, [], "Erreur affichage tournois");
    }

    // READ ONE
    public function getTournoiById(int $id): ?Tournoi
    {
        return $this->executeQuery(function($db) use ($id) {
            $sql = "SELECT * FROM tournoi WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            
            if ($row) {
                return new Tournoi(
                    $row['id'],
                    $row['nom'],
                    $row['theme'],
                    $row['niveau'],
                    $row['date_debut'],
                    $row['date_fin']
                );
            }
            return null;
        }, null, "Erreur récupération tournoi");
    }

    // UPDATE
    public function modifierTournoi(Tournoi $tournoi): bool
    {
        return $this->executeQuery(function($db) use ($tournoi) {
            $sql = "UPDATE tournoi 
                    SET nom = :nom, theme = :theme, niveau = :niveau, 
                        date_debut = :date_debut, date_fin = :date_fin 
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $tournoi->getId(),
                ':nom' => $tournoi->getNom(),
                ':theme' => $tournoi->getTheme(),
                ':niveau' => $tournoi->getNiveau(),
                ':date_debut' => $tournoi->getDateDebut(),
                ':date_fin' => $tournoi->getDateFin()
            ]);
            return true;
        }, false, "Erreur modification tournoi");
    }

    // DELETE
    public function supprimerTournoi(int $id): bool
    {
        return $this->executeQuery(function($db) use ($id) {
            $sql = "DELETE FROM tournoi WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        }, false, "Erreur suppression tournoi");
    }

    // VALIDATION HELPER
    public function validerTournoi(array $data): array
    {
        $errors = [];
        
        if (empty($data['nom']) || strlen($data['nom']) < 3) {
            $errors[] = "Le nom doit contenir au moins 3 caractères";
        }
        
        if (empty($data['theme'])) {
            $errors[] = "Le thème est obligatoire";
        }
        
        if (!in_array($data['niveau'], ['Débutant', 'Intermédiaire', 'Expert'])) {
            $errors[] = "Niveau invalide";
        }
        
        if (empty($data['date_debut']) || empty($data['date_fin'])) {
            $errors[] = "Les dates sont obligatoires";
        } elseif (strtotime($data['date_debut']) > strtotime($data['date_fin'])) {
            $errors[] = "La date de début doit être avant la date de fin";
        }
        
        return $errors;
    }
}
?>
