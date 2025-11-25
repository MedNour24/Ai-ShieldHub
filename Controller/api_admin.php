<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
ob_start();

try {
    if (!file_exists(__DIR__ . '/../config.php')) {
        throw new Exception("Config file not found");
    }
    require_once __DIR__ . '/../config.php';
    $db = config::getConnexion(); 
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    if ($method === 'GET') {
        if ($action === 'getQuizzes') {
            $stmt = $db->query("SELECT * FROM quiz ORDER BY date_creation DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        elseif ($action === 'getQuiz') {
            $id = intval($_GET['id']);
            $stmt = $db->prepare("SELECT * FROM quiz WHERE id_quiz = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        }
        elseif ($action === 'getQuestions') {
            // JOIN to get Quiz Title
            $sql = "SELECT r.*, q.titre as quiz_titre 
                    FROM reponse r 
                    LEFT JOIN quiz q ON r.id_quiz = q.id_quiz 
                    ORDER BY r.id_reponse DESC";
            $stmt = $db->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } 
        elseif ($action === 'getQuestion') {
            $id = intval($_GET['id']);
            $stmt = $db->prepare("SELECT * FROM reponse WHERE id_reponse = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        }
    }
    elseif ($method === 'POST') {
        if ($action === 'addQuiz' || $action === 'updateQuiz') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            $titre = $_POST['titre'];
            $desc = $_POST['description'];
            $statut = $_POST['statut'];

            if ($action === 'addQuiz') {
                $stmt = $db->prepare("INSERT INTO quiz (titre, description, statut, date_creation) VALUES (:titre, :desc, :statut, NOW())");
                $res = $stmt->execute(['titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
            } else {
                $stmt = $db->prepare("UPDATE quiz SET titre = :titre, description = :desc, statut = :statut WHERE id_quiz = :id");
                $res = $stmt->execute(['id' => $id, 'titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
            }
            ob_clean(); echo json_encode(['success' => $res]);
        }
        elseif ($action === 'deleteQuiz') {
            $id = intval($_POST['id']);
            // Delete questions first to avoid constraint error if cascading isn't on
            $db->prepare("DELETE FROM reponse WHERE id_quiz = :id")->execute(['id' => $id]);
            $stmt = $db->prepare("DELETE FROM quiz WHERE id_quiz = :id");
            ob_clean(); echo json_encode(['success' => $stmt->execute(['id' => $id])]);
        }
        // --- QUESTION LOGIC (Matching your DB dump) ---
        elseif ($action === 'addQuestion' || $action === 'updateQuestion') {
             $id = isset($_POST['id']) ? intval($_POST['id']) : null;
             $id_quiz = intval($_POST['id_quiz']); 
             $question = $_POST['titre']; 
             $o1 = $_POST['option1'];
             $o2 = $_POST['option2'];
             $o3 = $_POST['option3'];
             $correct = intval($_POST['correct_option']);

             if ($action === 'addQuestion') {
                 $stmt = $db->prepare("INSERT INTO reponse (id_quiz, question, option1, option2, option3, reponse_correcte) VALUES (:id_quiz, :question, :o1, :o2, :o3, :correct)");
                 $res = $stmt->execute(['id_quiz' => $id_quiz, 'question' => $question, 'o1' => $o1, 'o2' => $o2, 'o3' => $o3, 'correct' => $correct]);
             } else {
                 $stmt = $db->prepare("UPDATE reponse SET id_quiz = :id_quiz, question = :question, option1 = :o1, option2 = :o2, option3 = :o3, reponse_correcte = :correct WHERE id_reponse = :id");
                 $res = $stmt->execute(['id' => $id, 'id_quiz' => $id_quiz, 'question' => $question, 'o1' => $o1, 'o2' => $o2, 'o3' => $o3, 'correct' => $correct]);
             }
             ob_clean(); echo json_encode(['success' => $res]);
        }
        elseif ($action === 'deleteQuestion') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM reponse WHERE id_reponse = :id");
            ob_clean(); echo json_encode(['success' => $stmt->execute(['id' => $id])]);
        }
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>