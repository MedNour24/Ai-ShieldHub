<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/quiz.php';

class QuizC {
    
    // Ajouter un quiz
    public function ajouterQuiz(Quiz $quiz): bool {
        try {
            $db = config::getConnexion();
            $sql = "INSERT INTO quiz (titre, description, statut) VALUES (:titre, :description, :statut)";
            $stmt = $db->prepare($sql);
            
            $titre = $quiz->getTitre();
            $description = $quiz->getDescription();
            $statut = $quiz->getStatut();
            
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':statut', $statut);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur ajout quiz: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer tous les quiz
    public function afficherQuiz(): array {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM quiz ORDER BY date_creation DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur affichage quiz: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer un quiz par ID
    public function recupererQuiz(int $id_quiz): ?array {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM quiz WHERE id_quiz = :id_quiz";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur récupération quiz: " . $e->getMessage());
            return null;
        }
    }
    
    // Modifier un quiz
    public function modifierQuiz(Quiz $quiz, int $id_quiz): bool {
        try {
            $db = config::getConnexion();
            $sql = "UPDATE quiz SET titre = :titre, description = :description, statut = :statut WHERE id_quiz = :id_quiz";
            $stmt = $db->prepare($sql);
            
            $titre = $quiz->getTitre();
            $description = $quiz->getDescription();
            $statut = $quiz->getStatut();
            
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur modification quiz: " . $e->getMessage());
            return false;
        }
    }
    
    // Supprimer un quiz
    public function supprimerQuiz(int $id_quiz): bool {
        try {
            $db = config::getConnexion();
            $sql = "DELETE FROM quiz WHERE id_quiz = :id_quiz";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur suppression quiz: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer les quiz actifs
    public function recupererQuizActifs(): array {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM quiz WHERE statut = 'actif' ORDER BY date_creation DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération quiz actifs: " . $e->getMessage());
            return [];
        }
    }
}
?>