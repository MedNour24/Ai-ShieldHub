<?php
// Désactiver l'affichage des erreurs (elles seront loguées)
error_reporting(0);
ini_set('display_errors', 0);

// Headers AVANT tout output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Fonction pour retourner une erreur JSON
function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Chemins absolus
require_once dirname(__FILE__) . '/../config.php';

try {
    $db = config::getConnexion();
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'getQuiz':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                jsonError('Invalid quiz ID');
            }
            
            $stmt = $db->prepare("SELECT * FROM quiz WHERE id_quiz = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quiz) {
                jsonError('Quiz not found', 404);
            }
            
            echo json_encode($quiz, JSON_UNESCAPED_UNICODE);
            break;

        case 'getQuestions':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                jsonError('Invalid quiz ID');
            }
            
            $stmt = $db->prepare("SELECT * FROM reponse WHERE id_quiz = :id ORDER BY id_reponse");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($questions, JSON_UNESCAPED_UNICODE);
            break;

        case 'getActiveQuizzes':
            $stmt = $db->query("SELECT * FROM quiz WHERE statut = 'actif' ORDER BY date_creation DESC");
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($quizzes, JSON_UNESCAPED_UNICODE);
            break;

        default:
            jsonError('Invalid action');
    }
} catch (PDOException $e) {
    jsonError('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}
?>