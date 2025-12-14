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

        case 'saveQuizResult':
            // Simulation de sauvegarde - données en session
            session_start();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($_SESSION['quiz_results'])) {
                $_SESSION['quiz_results'] = [];
            }
            
            $result = [
                'date' => date('Y-m-d H:i:s'),
                'score' => floatval($input['score']),
                'total_questions' => intval($input['total_questions']),
                'quiz_id' => intval($input['quiz_id'])
            ];
            
            $_SESSION['quiz_results'][] = $result;
            
            echo json_encode(['success' => true, 'message' => 'Result saved in session']);
            break;

        case 'getUserProgress':
            // Simulation des données de progression
            session_start();
            $progressData = [];
            
            if (isset($_SESSION['quiz_results']) && !empty($_SESSION['quiz_results'])) {
                // Grouper par date
                $groupedByDate = [];
                foreach ($_SESSION['quiz_results'] as $result) {
                    $date = date('Y-m-d', strtotime($result['date']));
                    if (!isset($groupedByDate[$date])) {
                        $groupedByDate[$date] = [
                            'scores' => [],
                            'count' => 0
                        ];
                    }
                    $groupedByDate[$date]['scores'][] = $result['score'];
                    $groupedByDate[$date]['count']++;
                }
                
                // Formater pour l'affichage
                foreach ($groupedByDate as $date => $data) {
                    $progressData[] = [
                        'date' => $date,
                        'avg_score' => array_sum($data['scores']) / count($data['scores']),
                        'quiz_count' => $data['count']
                    ];
                }
                
                // Trier par date décroissante
                usort($progressData, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                
                // Limiter aux 30 derniers jours
                $progressData = array_slice($progressData, 0, 30);
            } else {
                // Données simulées pour démonstration
                $progressData = [
                    [
                        'date' => date('Y-m-d'),
                        'avg_score' => 85,
                        'quiz_count' => 2
                    ],
                    [
                        'date' => date('Y-m-d', strtotime('-1 day')),
                        'avg_score' => 70,
                        'quiz_count' => 1
                    ],
                    [
                        'date' => date('Y-m-d', strtotime('-3 days')),
                        'avg_score' => 60,
                        'quiz_count' => 1
                    ]
                ];
            }
            
            echo json_encode($progressData);
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