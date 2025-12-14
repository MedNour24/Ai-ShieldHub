<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reponse.php';

class ReponseC {
    
    // Ajouter une réponse
    public function ajouterReponse(Reponse $reponse): bool {
        try {
            $db = config::getConnexion();
            $sql = "INSERT INTO reponse (id_quiz, question, option1, option2, option3, reponse_correcte) 
                    VALUES (:id_quiz, :question, :option1, :option2, :option3, :reponse_correcte)";
            $stmt = $db->prepare($sql);
            
            $id_quiz = $reponse->getIdQuiz();
            $question = $reponse->getQuestion();
            $option1 = $reponse->getOption1();
            $option2 = $reponse->getOption2();
            $option3 = $reponse->getOption3();
            $reponse_correcte = $reponse->getReponseCorrecte();
            
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            $stmt->bindParam(':question', $question);
            $stmt->bindParam(':option1', $option1);
            $stmt->bindParam(':option2', $option2);
            $stmt->bindParam(':option3', $option3);
            $stmt->bindParam(':reponse_correcte', $reponse_correcte, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur ajout réponse: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer toutes les réponses d'un quiz
    public function afficherReponsesByQuiz(int $id_quiz): array {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM reponse WHERE id_quiz = :id_quiz ORDER BY id_reponse";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur affichage réponses: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer une réponse par ID
    public function recupererReponse(int $id_reponse): ?array {
        try {
            $db = config::getConnexion();
            $sql = "SELECT * FROM reponse WHERE id_reponse = :id_reponse";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_reponse', $id_reponse, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur récupération réponse: " . $e->getMessage());
            return null;
        }
    }
    
    // Modifier une réponse
    public function modifierReponse(Reponse $reponse, int $id_reponse): bool {
        try {
            $db = config::getConnexion();
            $sql = "UPDATE reponse SET id_quiz = :id_quiz, question = :question, option1 = :option1, 
                    option2 = :option2, option3 = :option3, reponse_correcte = :reponse_correcte 
                    WHERE id_reponse = :id_reponse";
            $stmt = $db->prepare($sql);
            
            $id_quiz = $reponse->getIdQuiz();
            $question = $reponse->getQuestion();
            $option1 = $reponse->getOption1();
            $option2 = $reponse->getOption2();
            $option3 = $reponse->getOption3();
            $reponse_correcte = $reponse->getReponseCorrecte();
            
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            $stmt->bindParam(':question', $question);
            $stmt->bindParam(':option1', $option1);
            $stmt->bindParam(':option2', $option2);
            $stmt->bindParam(':option3', $option3);
            $stmt->bindParam(':reponse_correcte', $reponse_correcte, PDO::PARAM_INT);
            $stmt->bindParam(':id_reponse', $id_reponse, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur modification réponse: " . $e->getMessage());
            return false;
        }
    }
    
    // Supprimer une réponse
    public function supprimerReponse(int $id_reponse): bool {
        try {
            $db = config::getConnexion();
            $sql = "DELETE FROM reponse WHERE id_reponse = :id_reponse";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_reponse', $id_reponse, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur suppression réponse: " . $e->getMessage());
            return false;
        }
    }
    
    // Vérifier une réponse
    public function verifierReponse(int $id_reponse, int $reponse_utilisateur): bool {
        try {
            $reponse = $this->recupererReponse($id_reponse);
            if ($reponse) {
                return $reponse['reponse_correcte'] == $reponse_utilisateur;
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur vérification réponse: " . $e->getMessage());
            return false;
        }
    }
    
    // Compter les réponses d'un quiz
    public function compterReponses(int $id_quiz): int {
        try {
            $db = config::getConnexion();
            $sql = "SELECT COUNT(*) as total FROM reponse WHERE id_quiz = :id_quiz";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Erreur comptage réponses: " . $e->getMessage());
            return 0;
        }
    }
}
?>