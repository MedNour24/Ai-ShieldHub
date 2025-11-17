<?php
// 1. DISABLE standard error display to prevent HTML breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Force JSON header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 3. Buffer output to catch unexpected text
ob_start();

try {
    if (!file_exists(__DIR__ . '/../config.php')) {
        throw new Exception("Config file not found at " . __DIR__ . '/../config.php');
    }
    require_once __DIR__ . '/../config.php';

    $method = $_SERVER['REQUEST_METHOD'];
    $db = config::getConnexion(); 

    // ==========================================
    // GET REQUESTS (Reading Data)
    // ==========================================
    if ($method === 'GET') {
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        // --- QUIZ ACTIONS ---
        if ($action === 'getQuizzes') {
            $stmt = $db->query("SELECT * FROM quiz ORDER BY id_quiz DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode($data);
        }
        elseif ($action === 'getQuiz') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $stmt = $db->prepare("SELECT * FROM quiz WHERE id_quiz = :id");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            ob_clean();
            if($data) echo json_encode($data);
            else { http_response_code(404); echo json_encode(['error' => 'Not found']); }
        }

        // --- QUESTION/REPONSE ACTIONS ---
        elseif ($action === 'getQuestions' || $action === 'getAllQuestions') {
            $sql = "SELECT r.*, q.titre as quiz_titre 
                    FROM reponse r 
                    LEFT JOIN quiz q ON r.id_quiz = q.id_quiz 
                    ORDER BY r.id_reponse DESC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode($data);
        } 
        elseif ($action === 'getQuestion') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $stmt = $db->prepare("SELECT * FROM reponse WHERE id_reponse = :id");
            $stmt->execute(['id' => $id]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
            ob_clean();
            if ($question) echo json_encode($question);
            else { http_response_code(404); echo json_encode(['error' => 'Not found']); }
        }
        elseif ($action === 'getOptions') {
            $id = isset($_GET['id_question']) ? intval($_GET['id_question']) : 0;
            $stmt = $db->prepare("SELECT * FROM reponse WHERE id_reponse = :id");
            $stmt->execute(['id' => $id]);
            $q = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $options = [];
            if ($q) {
                $options[] = ['option_text' => $q['option1'], 'is_correct' => ($q['reponse_correcte'] == 1) ? 1 : 0];
                $options[] = ['option_text' => $q['option2'], 'is_correct' => ($q['reponse_correcte'] == 2) ? 1 : 0];
                $options[] = ['option_text' => $q['option3'], 'is_correct' => ($q['reponse_correcte'] == 3) ? 1 : 0];
            }
            ob_clean();
            echo json_encode($options);
        }
        elseif ($action === 'getResults') {
             ob_clean();
             echo json_encode([]); 
        }
        else {
            ob_clean();
            echo json_encode(['error' => 'Unknown action']);
        }
    }
    // ==========================================
    // POST REQUESTS (Creating/Updating/Deleting)
    // ==========================================
    elseif ($method === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // --- QUIZ ACTIONS ---
        if ($action === 'addQuiz' || $action === 'updateQuiz') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            $titre = $_POST['titre'];
            $desc = $_POST['description'];
            $statut = $_POST['statut'];

            if ($action === 'addQuiz') {
                $stmt = $db->prepare("INSERT INTO quiz (titre, description, statut) VALUES (:titre, :desc, :statut)");
                $res = $stmt->execute(['titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
            } else {
                $stmt = $db->prepare("UPDATE quiz SET titre = :titre, description = :desc, statut = :statut WHERE id_quiz = :id");
                $res = $stmt->execute(['id' => $id, 'titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
            }
            ob_clean();
            echo json_encode(['success' => $res]);
        }
        elseif ($action === 'deleteQuiz') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id > 0) {
                // Delete associated questions first (Cascading delete handled by DB usually, but safe to try)
                try { $db->prepare("DELETE FROM reponse WHERE id_quiz = :id")->execute(['id' => $id]); } catch(Exception $e) {}
                
                $stmt = $db->prepare("DELETE FROM quiz WHERE id_quiz = :id");
                $res = $stmt->execute(['id' => $id]);
                ob_clean();
                echo json_encode(['success' => $res]);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            }
        }

        // --- QUESTION ACTIONS ---
        elseif ($action === 'addQuestion' || $action === 'updateQuestion') {
             $id = isset($_POST['id']) ? intval($_POST['id']) : null;
             $id_quiz = 1; 
             $titre = $_POST['titre']; 
             $opt1 = $_POST['option1'];
             $opt2 = $_POST['option2'];
             $opt3 = $_POST['option3'];
             $correct = intval($_POST['correct_option']);

             if ($action === 'addQuestion') {
                 $stmt = $db->prepare("INSERT INTO reponse (id_quiz, question, option1, option2, option3, reponse_correcte) VALUES (:id_quiz, :question, :opt1, :opt2, :opt3, :correct)");
                 $res = $stmt->execute(['id_quiz' => $id_quiz, 'question' => $titre, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'correct' => $correct]);
             } else {
                 $stmt = $db->prepare("UPDATE reponse SET question = :question, option1 = :opt1, option2 = :opt2, option3 = :opt3, reponse_correcte = :correct WHERE id_reponse = :id");
                 $res = $stmt->execute(['id' => $id, 'question' => $titre, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'correct' => $correct]);
             }
             ob_clean();
             echo json_encode(['success' => $res]);
        }
        elseif ($action === 'deleteQuestion') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id > 0) {
                $stmt = $db->prepare("DELETE FROM reponse WHERE id_reponse = :id");
                $res = $stmt->execute(['id' => $id]);
                ob_clean();
                echo json_encode(['success' => $res]);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            }
        }
        elseif ($action === 'deleteResult') {
            ob_clean();
            echo json_encode(['success' => true]);
        }
    }

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Server Error', 'message' => $e->getMessage()]);
}
?>