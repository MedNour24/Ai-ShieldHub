<?php
require_once __DIR__ . '/../Model/Tournoi.php';

class TournoiC
{
    // CREATE
    public function ajouterTournoi(Tournoi $tournoi): bool
    {
        try {
            $pdo = config::getConnexion();
            $sql = "INSERT INTO tournoi (nom, theme, niveau, date_debut, date_fin) 
                    VALUES (:nom, :theme, :niveau, :date_debut, :date_fin)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $tournoi->getNom(),
                ':theme' => $tournoi->getTheme(),
                ':niveau' => $tournoi->getNiveau(),
                ':date_debut' => $tournoi->getDateDebut(),
                ':date_fin' => $tournoi->getDateFin()
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur ajout tournoi: " . $e->getMessage());
            return false;
        }
    }

    // READ ALL
    public function afficherTournois(): array
    {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT * FROM tournoi ORDER BY date_debut DESC";
            $stmt = $pdo->query($sql);
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
        } catch (PDOException $e) {
            error_log("Erreur affichage tournois: " . $e->getMessage());
            return [];
        }
    }

    // READ ONE
    public function getTournoiById(int $id): ?Tournoi
    {
        try {
            $pdo = config::getConnexion();
            $sql = "SELECT * FROM tournoi WHERE id = :id";
            $stmt = $pdo->prepare($sql);
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
        } catch (PDOException $e) {
            error_log("Erreur récupération tournoi: " . $e->getMessage());
            return null;
        }
    }

    // UPDATE
    public function modifierTournoi(Tournoi $tournoi): bool
    {
        try {
            $pdo = config::getConnexion();
            $sql = "UPDATE tournoi 
                    SET nom = :nom, theme = :theme, niveau = :niveau, 
                        date_debut = :date_debut, date_fin = :date_fin 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $tournoi->getId(),
                ':nom' => $tournoi->getNom(),
                ':theme' => $tournoi->getTheme(),
                ':niveau' => $tournoi->getNiveau(),
                ':date_debut' => $tournoi->getDateDebut(),
                ':date_fin' => $tournoi->getDateFin()
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur modification tournoi: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    public function supprimerTournoi(int $id): bool
    {
        try {
            $pdo = config::getConnexion();
            $sql = "DELETE FROM tournoi WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur suppression tournoi: " . $e->getMessage());
            return false;
        }
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
