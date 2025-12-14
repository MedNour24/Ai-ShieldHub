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
        elseif ($action === 'getLeaderboard') {
            $stmt = $db->query("
                SELECT u.username, u.email, 
                       COALESCE(SUM(qr.score), 0) as total_score, 
                       COALESCE(COUNT(qr.id), 0) as quizzes_taken,
                       COALESCE(AVG(qr.score), 0) as avg_score
                FROM users u
                LEFT JOIN quiz_results qr ON u.id = qr.user_id
                WHERE DATE(qr.completed_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                       OR qr.completed_at IS NULL
                GROUP BY u.id
                ORDER BY total_score DESC, quizzes_taken DESC
                LIMIT 20
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        elseif ($action === 'getQuizAnalytics') {
            $id = intval($_GET['id']);
            
            // CORRECTION: Utiliser les résultats en session si la table quiz_results n'existe pas
            // D'abord, essayer de vérifier si la table existe
            try {
                $stmt = $db->prepare("
                    SELECT 
                        COUNT(DISTINCT user_id) as participants,
                        COALESCE(AVG(score), 0) as avg_score,
                        COALESCE(MIN(score), 0) as min_score,
                        COALESCE(MAX(score), 0) as max_score,
                        COUNT(*) as total_attempts
                    FROM quiz_results 
                    WHERE quiz_id = :id
                ");
                $stmt->execute(['id' => $id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Si aucune donnée, créer des statistiques simulées basées sur la session
                if ($result['total_attempts'] == 0) {
                    session_start();
                    $sessionResults = isset($_SESSION['quiz_results']) ? $_SESSION['quiz_results'] : [];
                    
                    // Filtrer les résultats pour ce quiz
                    $quizResults = array_filter($sessionResults, function($r) use ($id) {
                        return isset($r['quiz_id']) && $r['quiz_id'] == $id;
                    });
                    
                    if (!empty($quizResults)) {
                        $scores = array_column($quizResults, 'score');
                        $result = [
                            'participants' => 1,
                            'avg_score' => array_sum($scores) / count($scores),
                            'min_score' => min($scores),
                            'max_score' => max($scores),
                            'total_attempts' => count($quizResults)
                        ];
                    }
                }
                
                echo json_encode($result);
            } catch (PDOException $e) {
                // Si la table n'existe pas, utiliser les données de session
                session_start();
                $sessionResults = isset($_SESSION['quiz_results']) ? $_SESSION['quiz_results'] : [];
                
                $quizResults = array_filter($sessionResults, function($r) use ($id) {
                    return isset($r['quiz_id']) && $r['quiz_id'] == $id;
                });
                
                if (!empty($quizResults)) {
                    $scores = array_column($quizResults, 'score');
                    $result = [
                        'participants' => 1,
                        'avg_score' => array_sum($scores) / count($scores),
                        'min_score' => min($scores),
                        'max_score' => max($scores),
                        'total_attempts' => count($quizResults)
                    ];
                } else {
                    $result = [
                        'participants' => 0,
                        'avg_score' => 0,
                        'min_score' => 0,
                        'max_score' => 0,
                        'total_attempts' => 0
                    ];
                }
                
                echo json_encode($result);
            }
        }
        elseif ($action === 'getAIRecommendations') {
            $user_id = intval($_GET['user_id']);
            
            // Simuler des recommandations basées sur les performances
            $stmt = $db->prepare("
                SELECT q.id_quiz, q.titre, q.description,
                       CASE 
                           WHEN r.score IS NULL THEN 'new'
                           WHEN r.score < 70 THEN 'needs_improvement'
                           ELSE 'mastered'
                       END as recommendation,
                       COALESCE(r.score, 0) as previous_score
                FROM quiz q
                LEFT JOIN quiz_results r ON q.id_quiz = r.quiz_id AND r.user_id = :user_id
                WHERE q.statut = 'actif'
                ORDER BY 
                    CASE 
                        WHEN r.score IS NULL THEN 1
                        WHEN r.score < 70 THEN 2
                        ELSE 3
                    END,
                    q.date_creation DESC
                LIMIT 5
            ");
            $stmt->execute(['user_id' => $user_id]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        elseif ($action === 'getQuizReports') {
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            
            try {
                $stmt = $db->prepare("
                    SELECT 
                        q.id_quiz,
                        q.titre,
                        COUNT(DISTINCT qr.user_id) as unique_participants,
                        COUNT(qr.id) as total_attempts,
                        COALESCE(AVG(qr.score), 0) as avg_score,
                        COALESCE(MIN(qr.score), 0) as min_score,
                        COALESCE(MAX(qr.score), 0) as max_score,
                        DATE(qr.completed_at) as attempt_date
                    FROM quiz q
                    LEFT JOIN quiz_results qr ON q.id_quiz = qr.quiz_id 
                        AND DATE(qr.completed_at) BETWEEN :start_date AND :end_date
                    GROUP BY q.id_quiz, DATE(qr.completed_at)
                    ORDER BY attempt_date DESC, total_attempts DESC
                ");
                $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Si pas de données dans la table, utiliser les sessions
                if (empty($results) || ($results[0]['total_attempts'] == 0 && count($results) == 1)) {
                    session_start();
                    $sessionResults = isset($_SESSION['quiz_results']) ? $_SESSION['quiz_results'] : [];
                    
                    $filteredResults = array_filter($sessionResults, function($r) use ($start_date, $end_date) {
                        $date = date('Y-m-d', strtotime($r['date']));
                        return $date >= $start_date && $date <= $end_date;
                    });
                    
                    $grouped = [];
                    foreach ($filteredResults as $result) {
                        $quizId = $result['quiz_id'];
                        $date = date('Y-m-d', strtotime($result['date']));
                        
                        if (!isset($grouped[$quizId][$date])) {
                            $grouped[$quizId][$date] = [
                                'scores' => [],
                                'count' => 0
                            ];
                        }
                        $grouped[$quizId][$date]['scores'][] = $result['score'];
                        $grouped[$quizId][$date]['count']++;
                    }
                    
                    $reports = [];
                    foreach ($grouped as $quizId => $dates) {
                        foreach ($dates as $date => $data) {
                            $reports[] = [
                                'id_quiz' => $quizId,
                                'titre' => 'Quiz ' . $quizId,
                                'unique_participants' => 1,
                                'total_attempts' => $data['count'],
                                'avg_score' => array_sum($data['scores']) / count($data['scores']),
                                'min_score' => min($data['scores']),
                                'max_score' => max($data['scores']),
                                'attempt_date' => $date
                            ];
                        }
                    }
                    
                    echo json_encode($reports);
                } else {
                    echo json_encode($results);
                }
            } catch (PDOException $e) {
                // Fallback aux sessions
                session_start();
                $sessionResults = isset($_SESSION['quiz_results']) ? $_SESSION['quiz_results'] : [];
                
                $filteredResults = array_filter($sessionResults, function($r) use ($start_date, $end_date) {
                    $date = date('Y-m-d', strtotime($r['date']));
                    return $date >= $start_date && $date <= $end_date;
                });
                
                $grouped = [];
                foreach ($filteredResults as $result) {
                    $quizId = $result['quiz_id'];
                    $date = date('Y-m-d', strtotime($result['date']));
                    
                    if (!isset($grouped[$quizId][$date])) {
                        $grouped[$quizId][$date] = [
                            'scores' => [],
                            'count' => 0
                        ];
                    }
                    $grouped[$quizId][$date]['scores'][] = $result['score'];
                    $grouped[$quizId][$date]['count']++;
                }
                
                $reports = [];
                foreach ($grouped as $quizId => $dates) {
                    foreach ($dates as $date => $data) {
                        $reports[] = [
                            'id_quiz' => $quizId,
                            'titre' => 'Quiz ' . $quizId,
                            'unique_participants' => 1,
                            'total_attempts' => $data['count'],
                            'avg_score' => array_sum($data['scores']) / count($data['scores']),
                            'min_score' => min($data['scores']),
                            'max_score' => max($data['scores']),
                            'attempt_date' => $date
                        ];
                    }
                }
                
                echo json_encode($reports);
            }
        }
        // AJOUT: Récupérer les notifications
        elseif ($action === 'getNotifications') {
            $user_id = intval($_GET['user_id']);
            
            try {
                // Vérifier si la table notifications existe
                $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("
                        SELECT n.*, 
                               CASE 
                                   WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'Just now'
                                   WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), 'h ago')
                                   ELSE DATE_FORMAT(n.created_at, '%Y-%m-%d %H:%i')
                               END as time_ago
                        FROM notifications n
                        WHERE n.user_id = :user_id OR n.user_id = 0
                        ORDER BY n.is_read ASC, n.created_at DESC
                        LIMIT 20
                    ");
                    $stmt->execute(['user_id' => $user_id]);
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode($notifications);
                } else {
                    // Table n'existe pas, créer des données simulées
                    echo json_encode([
                        [
                            'id' => 1,
                            'title' => 'New Quiz Available!',
                            'message' => 'Test your knowledge with our new cybersecurity quiz.',
                            'type' => 'quiz',
                            'reference_id' => 1,
                            'is_read' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'time_ago' => 'Just now',
                            'icon' => 'fas fa-shield-alt',
                            'color' => '#6861ce'
                        ]
                    ]);
                }
            } catch (PDOException $e) {
                // Données simulées en cas d'erreur
                echo json_encode([
                    [
                        'id' => 1,
                        'title' => 'New Quiz Available!',
                        'message' => 'Test your knowledge with our new cybersecurity quiz.',
                        'type' => 'quiz',
                        'reference_id' => 1,
                        'is_read' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'time_ago' => 'Just now',
                        'icon' => 'fas fa-shield-alt',
                        'color' => '#6861ce'
                    ]
                ]);
            }
        }
        
        // AJOUT: Compter les notifications non lues
        elseif ($action === 'countUnreadNotifications') {
            $user_id = intval($_GET['user_id']);
            
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("
                        SELECT COUNT(*) as count 
                        FROM notifications 
                        WHERE (user_id = :user_id OR user_id = 0) AND is_read = 0
                    ");
                    $stmt->execute(['user_id' => $user_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['count' => $result['count']]);
                } else {
                    echo json_encode(['count' => 1]);
                }
            } catch (PDOException $e) {
                echo json_encode(['count' => 1]);
            }
        }
        
        // AJOUT: Récupérer les nouvelles notifications seulement (pour le toast)
        elseif ($action === 'getNewQuizNotifications') {
            $last_check = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
            
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("
                        SELECT n.* 
                        FROM notifications n
                        WHERE n.type = 'quiz' 
                          AND n.is_read = 0
                          AND n.created_at > :last_check
                        ORDER BY n.created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute(['last_check' => $last_check]);
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['notifications' => $notifications]);
                } else {
                    echo json_encode(['notifications' => []]);
                }
            } catch (PDOException $e) {
                echo json_encode(['notifications' => []]);
            }
        }
        
    }
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'addQuiz' || $action === 'updateQuiz') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            $titre = $_POST['titre'];
            $desc = $_POST['description'];
            $statut = $_POST['statut'];

            if ($action === 'addQuiz') {
                $stmt = $db->prepare("INSERT INTO quiz (titre, description, statut, date_creation) VALUES (:titre, :desc, :statut, NOW())");
                $res = $stmt->execute(['titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
                
                // AJOUT: Créer une notification pour le nouveau quiz
                if ($res && $statut === 'actif') {
                    $quiz_id = $db->lastInsertId();
                    createQuizNotification($db, $quiz_id, $titre);
                }
                
            } else {
                $stmt = $db->prepare("UPDATE quiz SET titre = :titre, description = :desc, statut = :statut WHERE id_quiz = :id");
                $res = $stmt->execute(['id' => $id, 'titre' => $titre, 'desc' => $desc, 'statut' => $statut]);
                
                // AJOUT: Si le quiz devient actif, créer une notification
                if ($res && $statut === 'actif') {
                    createQuizNotification($db, $id, $titre);
                }
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
        // --- AJOUT: Marquer une notification comme lue ---
        elseif ($action === 'markNotificationRead') {
            $notification_id = intval($_POST['id'] ?? 0);
            
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
                    $res = $stmt->execute(['id' => $notification_id]);
                    ob_clean(); echo json_encode(['success' => $res]);
                } else {
                    ob_clean(); echo json_encode(['success' => true]);
                }
            } catch (PDOException $e) {
                ob_clean(); echo json_encode(['success' => true]);
            }
        }
        
        // AJOUT: Marquer toutes les notifications comme lues
        elseif ($action === 'markAllNotificationsRead') {
            $user_id = intval($_POST['user_id'] ?? 0);
            
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id OR user_id = 0");
                    $res = $stmt->execute(['user_id' => $user_id]);
                    ob_clean(); echo json_encode(['success' => $res]);
                } else {
                    ob_clean(); echo json_encode(['success' => true]);
                }
            } catch (PDOException $e) {
                ob_clean(); echo json_encode(['success' => true]);
            }
        }
        
        // --- METIERS AVANCES ---
        elseif ($action === 'exportQuizData') {
            $quiz_id = intval($_POST['quiz_id']);
            $export_type = $_POST['export_type']; // 'csv', 'json', 'pdf'
            
            // Récupérer les données du quiz
            $stmt = $db->prepare("SELECT * FROM quiz WHERE id_quiz = :id");
            $stmt->execute(['id' => $quiz_id]);
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quiz) {
                throw new Exception("Quiz not found");
            }
            
            // Récupérer les questions
            $stmt = $db->prepare("SELECT * FROM reponse WHERE id_quiz = :id ORDER BY id_reponse");
            $stmt->execute(['id' => $quiz_id]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les statistiques
            $stmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT user_id) as participants,
                    COALESCE(AVG(score), 0) as avg_score,
                    COALESCE(MIN(score), 0) as min_score,
                    COALESCE(MAX(score), 0) as max_score,
                    COUNT(*) as total_attempts
                FROM quiz_results 
                WHERE quiz_id = :id
            ");
            $stmt->execute(['id' => $quiz_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $data = [
                'quiz' => $quiz,
                'questions' => $questions,
                'statistics' => $stats,
                'exported_at' => date('Y-m-d H:i:s'),
                'export_type' => $export_type
            ];
            
            if ($export_type === 'csv') {
                ob_clean();
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=quiz_' . $quiz_id . '_' . date('Ymd') . '.csv');
                
                $output = fopen('php://output', 'w');
                
                // En-têtes du quiz
                fputcsv($output, ['Quiz Information']);
                fputcsv($output, ['ID', 'Title', 'Description', 'Status', 'Creation Date']);
                fputcsv($output, [
                    $quiz['id_quiz'],
                    $quiz['titre'],
                    $quiz['description'],
                    $quiz['statut'],
                    $quiz['date_creation']
                ]);
                
                fputcsv($output, []); // Ligne vide
                fputcsv($output, ['Questions']);
                fputcsv($output, ['ID', 'Question', 'Option 1', 'Option 2', 'Option 3', 'Correct Answer']);
                
                foreach ($questions as $question) {
                    fputcsv($output, [
                        $question['id_reponse'],
                        $question['question'],
                        $question['option1'],
                        $question['option2'],
                        $question['option3'],
                        $question['reponse_correcte']
                    ]);
                }
                
                fputcsv($output, []); // Ligne vide
                fputcsv($output, ['Statistics']);
                fputcsv($output, ['Participants', 'Total Attempts', 'Average Score', 'Min Score', 'Max Score']);
                fputcsv($output, [
                    $stats['participants'] ?? 0,
                    $stats['total_attempts'] ?? 0,
                    $stats['avg_score'] ?? 0,
                    $stats['min_score'] ?? 0,
                    $stats['max_score'] ?? 0
                ]);
                
                fclose($output);
                exit;
                
            } elseif ($export_type === 'json') {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename=quiz_' . $quiz_id . '_' . date('Ymd') . '.json');
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
                
            } else {
                // Pour PDF, on retourne les données et le front-end utilisera une librairie JS
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'data' => $data,
                    'export_type' => $export_type,
                    'filename' => 'quiz_' . $quiz_id . '_' . date('Ymd') . '.' . $export_type
                ]);
            }
        }
        elseif ($action === 'bulkImportQuestions') {
            $quiz_id = intval($_POST['quiz_id']);
            $import_data = json_decode($_POST['questions_data'], true);
            
            if (!is_array($import_data) || empty($import_data)) {
                throw new Exception("Invalid import data");
            }
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            
            foreach ($import_data as $index => $question) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO reponse (id_quiz, question, option1, option2, option3, reponse_correcte) 
                        VALUES (:id_quiz, :question, :option1, :option2, :option3, :correct)
                    ");
                    
                    $result = $stmt->execute([
                        'id_quiz' => $quiz_id,
                        'question' => $question['question'] ?? '',
                        'option1' => $question['option1'] ?? '',
                        'option2' => $question['option2'] ?? '',
                        'option3' => $question['option3'] ?? '',
                        'correct' => intval($question['correct_option'] ?? 1)
                    ]);
                    
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Question " . ($index + 1) . ": Failed to insert";
                    }
                } catch (Exception $e) {
                    $error_count++;
                    $errors[] = "Question " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            ob_clean();
            echo json_encode([
                'success' => $success_count > 0,
                'imported' => $success_count,
                'failed' => $error_count,
                'errors' => $errors
            ]);
        }
        elseif ($action === 'generateQuizReport') {
            $report_type = $_POST['report_type']; // 'performance', 'engagement', 'completion'
            $start_date = $_POST['start_date'] ?? date('Y-m-01');
            $end_date = $_POST['end_date'] ?? date('Y-m-d');
            
            try {
                if ($report_type === 'performance') {
                    $stmt = $db->prepare("
                        SELECT 
                            q.id_quiz,
                            q.titre,
                            COUNT(DISTINCT qr.user_id) as participants,
                            COUNT(qr.id) as attempts,
                            COALESCE(AVG(qr.score), 0) as avg_score,
                            COALESCE(MIN(qr.score), 0) as min_score,
                            COALESCE(MAX(qr.score), 0) as max_score,
                            COUNT(CASE WHEN qr.score >= 70 THEN 1 END) as passed,
                            COUNT(CASE WHEN qr.score < 70 THEN 1 END) as failed
                        FROM quiz q
                        LEFT JOIN quiz_results qr ON q.id_quiz = qr.quiz_id 
                            AND DATE(qr.completed_at) BETWEEN :start_date AND :end_date
                        GROUP BY q.id_quiz
                        ORDER BY avg_score DESC
                    ");
                } elseif ($report_type === 'engagement') {
                    $stmt = $db->prepare("
                        SELECT 
                            DATE(qr.completed_at) as date,
                            COUNT(DISTINCT qr.user_id) as daily_users,
                            COUNT(qr.id) as daily_attempts,
                            COALESCE(AVG(qr.score), 0) as daily_avg_score
                        FROM quiz_results qr
                        WHERE DATE(qr.completed_at) BETWEEN :start_date AND :end_date
                        GROUP BY DATE(qr.completed_at)
                        ORDER BY date DESC
                    ");
                } else { // completion
                    $stmt = $db->prepare("
                        SELECT 
                            q.titre,
                            COUNT(DISTINCT qr.user_id) as total_participants,
                            COUNT(qr.id) as total_attempts,
                            (COUNT(DISTINCT qr.user_id) * 100.0 / 
                                (SELECT COUNT(DISTINCT id) FROM users WHERE role = 'student')) as participation_rate,
                            COALESCE(AVG(qr.score), 0) as completion_score
                        FROM quiz q
                        LEFT JOIN quiz_results qr ON q.id_quiz = qr.quiz_id
                        GROUP BY q.id_quiz
                        ORDER BY participation_rate DESC
                    ");
                }
                
                $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
                $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'report_type' => $report_type,
                    'period' => ['start' => $start_date, 'end' => $end_date],
                    'data' => $report_data,
                    'generated_at' => date('Y-m-d H:i:s')
                ]);
                
            } catch (PDOException $e) {
                // Fallback aux données simulées
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'report_type' => $report_type,
                    'period' => ['start' => $start_date, 'end' => $end_date],
                    'data' => $this->generateMockReport($report_type, $start_date, $end_date),
                    'generated_at' => date('Y-m-d H:i:s'),
                    'note' => 'Using simulated data'
                ]);
            }
        }
        elseif ($action === 'submitQuizResult') {
            $user_id = intval($_POST['user_id']);
            $quiz_id = intval($_POST['quiz_id']);
            $score = floatval($_POST['score']);
            $total_questions = intval($_POST['total_questions']);
            $time_taken = intval($_POST['time_taken']); // en secondes
            $answers = $_POST['answers']; // JSON des réponses
            
            // CORRECTION: Vérifier si la table quiz_results existe, sinon utiliser la session
            try {
                $stmt = $db->prepare("
                    INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, time_taken, answers, completed_at)
                    VALUES (:user_id, :quiz_id, :score, :total_questions, :time_taken, :answers, NOW())
                ");
                
                $res = $stmt->execute([
                    'user_id' => $user_id,
                    'quiz_id' => $quiz_id,
                    'score' => $score,
                    'total_questions' => $total_questions,
                    'time_taken' => $time_taken,
                    'answers' => $answers
                ]);
                
                echo json_encode(['success' => $res, 'result_id' => $db->lastInsertId()]);
            } catch (PDOException $e) {
                // Si la table n'existe pas, sauvegarder en session
                session_start();
                if (!isset($_SESSION['quiz_results'])) {
                    $_SESSION['quiz_results'] = [];
                }
                
                $result = [
                    'date' => date('Y-m-d H:i:s'),
                    'score' => $score,
                    'total_questions' => $total_questions,
                    'quiz_id' => $quiz_id,
                    'time_taken' => $time_taken,
                    'answers' => $answers
                ];
                
                $_SESSION['quiz_results'][] = $result;
                
                echo json_encode(['success' => true, 'result_id' => count($_SESSION['quiz_results'])]);
            }
        }
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Fonction pour générer des rapports simulés
function generateMockReport($type, $start_date, $end_date) {
    $data = [];
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $days = ceil(($end - $start) / (60 * 60 * 24));
    
    if ($type === 'performance') {
        for ($i = 1; $i <= 5; $i++) {
            $data[] = [
                'id_quiz' => $i,
                'titre' => 'Quiz ' . $i,
                'participants' => rand(10, 100),
                'attempts' => rand(15, 150),
                'avg_score' => rand(60, 95),
                'min_score' => rand(20, 50),
                'max_score' => rand(95, 100),
                'passed' => rand(5, 90),
                'failed' => rand(1, 20)
            ];
        }
    } elseif ($type === 'engagement') {
        for ($d = 0; $d < min($days, 30); $d++) {
            $date = date('Y-m-d', strtotime($start_date . " + $d days"));
            $data[] = [
                'date' => $date,
                'daily_users' => rand(5, 50),
                'daily_attempts' => rand(10, 100),
                'daily_avg_score' => rand(65, 90)
            ];
        }
    } else { // completion
        for ($i = 1; $i <= 5; $i++) {
            $data[] = [
                'titre' => 'Quiz ' . $i,
                'total_participants' => rand(20, 200),
                'total_attempts' => rand(30, 300),
                'participation_rate' => rand(30, 95),
                'completion_score' => rand(70, 95)
            ];
        }
    }
    
    return $data;
}

// AJOUT: Fonction pour créer une notification de quiz
function createQuizNotification($db, $quiz_id, $quiz_title) {
    try {
        // Vérifier si la table notifications existe, sinon la créer
        $db->query("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT DEFAULT 0,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                type VARCHAR(50) DEFAULT 'quiz',
                reference_id INT,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_read (user_id, is_read),
                INDEX idx_created (created_at),
                INDEX idx_type_reference (type, reference_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Ajouter une notification pour tous les utilisateurs (user_id = 0)
        $notification_title = "New Quiz Available!";
        $notification_message = "$quiz_title is now available! Test your knowledge.";
        
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id) 
            VALUES (0, :title, :message, 'quiz', :quiz_id)
        ");
        $stmt->execute([
            'title' => $notification_title,
            'message' => $notification_message,
            'quiz_id' => $quiz_id
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        // Ignorer les erreurs de notification, mais continuer
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}
?>