<?php 
// CONTROLLER UNIFIÉS
require_once __DIR__ . '/../../Controller/PublicationController.php';
require_once __DIR__ . '/../../Controller/CommentaireController.php';
require_once __DIR__ . '/../../Controller/ReactionController.php';
require_once __DIR__ . '/../../Model/Publication.php';
require_once __DIR__ . '/../../Model/Commentaire.php';
require_once __DIR__ . '/../../Model/Reaction.php';

// ID utilisateur connecté
$idUser = isset($_GET['id_utilisateur']) ? intval($_GET['id_utilisateur']) : 0;
if ($idUser <= 0) die("ID utilisateur non spécifié !");
$userName = $_SESSION['user_name'] ?? 'Student';
// Instanciation des contrôleurs
$pubController = new PublicationController();
$commentController = new CommentaireController();
$reactionController = new ReactionController();

// Section active
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'feed';

// ---------- CONFIGURATION DE L'API AI ----------
define('OPENAI_API_KEY', 'VOTRE_CLE_API_OPENAI_ICI'); // À remplacer par votre clé API
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

// ---------- GESTION DE L'ASSISTANT AI ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_question'])) {
    $publicationId = intval($_POST['publication_id']);
    $question = trim($_POST['ai_question']);
    $publicationText = trim($_POST['publication_text']);
    
    // Appel à l'API OpenAI ChatGPT
    function getAIResponse($publicationText, $question) {
        $apiKey = OPENAI_API_KEY;
        
        // Préparation du prompt pour ChatGPT
        $prompt = "Tu es un expert en cybersécurité qui aide des étudiants. 
        
        CONTEXTE DE LA PUBLICATION :
        {$publicationText}
        
        QUESTION DE L'ÉTUDIANT :
        {$question}
        
        Donne une réponse :
        1. Claire et pédagogique
        2. Précise techniquement
        3. Avec des exemples concrets si possible
        4. En français (sauf pour les termes techniques en anglais)
        5. En indiquant les bonnes pratiques de sécurité
        6. En mentionnant si le sujet est avancé ou débutant
        
        Réponds directement à la question sans faire d'introduction longue.";
        
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un expert en cybersécurité avec 15 ans d\'expérience. Tu aides des étudiants à comprendre des concepts complexes.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 800,
            'temperature' => 0.7
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, OPENAI_API_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("Erreur cURL : " . $error_msg);
        }
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if (isset($responseData['error'])) {
            throw new Exception("Erreur OpenAI : " . $responseData['error']['message']);
        }
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception("Réponse OpenAI invalide");
        }
        
        return $responseData['choices'][0]['message']['content'];
    }
    
    try {
        $aiResponse = getAIResponse($publicationText, $question);
        
        // Retourner la réponse en JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'response' => $aiResponse,
            'question' => $question,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        // En cas d'erreur, retourner une réponse par défaut
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'response' => "Je suis désolé, je ne peux pas répondre pour le moment. Erreur technique : " . $e->getMessage(),
            'question' => $question,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    exit();
}

// ---------- GESTION DE L'AJOUT DE PUBLICATION ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['texte'])) {

    $filePath = null;
    $fileType = null;

    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . "/../../uploads/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = basename($_FILES['file']['name']);
        $targetPath = $uploadDir . $fileName;

        if(move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $filePath = "uploads/" . $fileName;
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        }
    }

    $texte = $_POST['texte'] ?? '';

    $publication = new Publication(null, $idUser, $texte, $filePath, $fileType, new DateTime());
    $pubController->addPublication($publication);

    // Redirection pour éviter le resubmission
    header("Location: addPublication.php?id_utilisateur=".$idUser."&section=".$activeSection);
    exit();
}

// ---------- PAGINATION MES PUBLICATIONS ----------
$limit = 5;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$total = $pubController->countUserPublications($idUser);
$totalPages = ceil($total/$limit);
$myPublications = $pubController->listUserPublications($idUser, $limit, $offset);

// ---------- FIL D'ACTUALITÉ INTELLIGENT ----------
$feedMode = isset($_GET['feed_mode']) ? $_GET['feed_mode'] : 'recent';
$feedLimit = 10;
$feedPage = isset($_GET['feed_page']) ? max(1,intval($_GET['feed_page'])) : 1;
$feedOffset = ($feedPage-1)*$feedLimit;

// Récupérer toutes les publications triées selon le mode choisi
$feedPublicationsAll = $pubController->getFilIntelligent($feedMode); 
$totalFeedPages = ceil(count($feedPublicationsAll)/$feedLimit);
$feedPublications = array_slice($feedPublicationsAll, $feedOffset, $feedLimit);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AICyberLearn | Smarter Minds, Safer Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #0f172a;
            --darker: #020617;
            --light: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: var(--darker);
        }
        
        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #1e1b4b 100%);
        }
        
        .animated-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, -50px); }
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.3;
            animation: float 20s infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-100px) translateX(50px); }
            50% { transform: translateY(-200px) translateX(-50px); }
            75% { transform: translateY(-100px) translateX(100px); }
        }
        
        /* Header */
        .navbar {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
            padding: 20px 0;
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--light) !important;
            font-weight: 500;
            padding: 10px 20px !important;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 60%;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        /* Main Content */
        .main-content {
            padding: 120px 0 50px;
            min-height: 100vh;
        }
        
        .community-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-size: 48px;
            font-weight: 900;
            color: white;
            margin-bottom: 20px;
        }
        
        .section-title p {
            color: var(--light);
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Add Publication Button */
        .add-publication-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .add-publication-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
            color: white;
        }
        
        /* Return to Feed Button */
        .return-feed-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: var(--light);
            padding: 12px 25px;
            font-weight: 600;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .return-feed-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateX(-5px);
        }
        
        /* Feed Filter Buttons */
        .feed-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .feed-filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 12px;
            color: var(--light);
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }
        
        .feed-filter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: 0.5s;
        }
        
        .feed-filter-btn:hover::before {
            left: 100%;
        }
        
        .feed-filter-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
        }
        
        .feed-filter-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .feed-filter-btn i {
            font-size: 16px;
        }
        
        /* Modal Styles */
        .modal-content {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            color: var(--light);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(99, 102, 241, 0.3);
            padding: 25px;
        }
        
        .modal-title {
            color: white;
            font-weight: 700;
            font-size: 24px;
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(99, 102, 241, 0.3);
            padding: 20px 25px;
        }
        
        /* Publication Form */
        .publication-form {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .publication-form h4 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            color: var(--light);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            resize: none;
            min-height: 120px;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-group textarea::placeholder {
            color: rgba(226, 232, 240, 0.5);
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: block;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            color: rgba(226, 232, 240, 0.7);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
        }
        
        .btn-publish {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            width: 100%;
        }
        
        .btn-publish:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }
        
        .btn-publish:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .char-counter {
            text-align: right;
            color: var(--light);
            font-size: 12px;
            margin-top: 5px;
        }
        
        .error-message {
            color: var(--accent);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        /* Publication Cards */
        .publication-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.4s ease;
        }
        
        .publication-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
        }
        
        .publication-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .publication-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
        }
        
        .publication-author {
            color: white;
            font-weight: 700;
            font-size: 18px;
        }
        
        .publication-date {
            color: rgba(226, 232, 240, 0.6);
            font-size: 14px;
        }
        
        .publication-content {
            color: var(--light);
            line-height: 1.7;
            margin-bottom: 20px;
            white-space: pre-line;
        }
        
        .publication-file {
            margin-bottom: 20px;
        }
        
        .file-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .file-link:hover {
            color: var(--accent);
        }
        
        .file-link i {
            margin-right: 8px;
        }
        
        /* Système de Réactions */
        .reaction-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 20px;
        }
        
        .reaction-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            color: rgba(226, 232, 240, 0.7);
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .reaction-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .reaction-btn.active.liked {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .reaction-btn.active.disliked {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .reaction-count {
            font-size: 14px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        .reaction-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            color: rgba(226, 232, 240, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }
        
        .action-btn:hover {
            color: var(--primary);
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        /* NOUVEAU STYLE POUR L'ASSISTANT AI */
        .ai-assistant-btn {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 8px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            text-decoration: none;
            margin-left: auto;
        }
        
        .ai-assistant-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        .ai-assistant-btn i {
            font-size: 16px;
        }
        
        /* Comments Section */
        .comments-preview {
            margin-top: 20px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            padding-top: 20px;
        }
        
        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .comments-title {
            color: white;
            font-size: 16px;
            font-weight: 600;
        }
        
        .comment-preview {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--primary);
        }
        
        .comment-preview-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .comment-preview-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
            font-size: 12px;
        }
        
        .comment-preview-author {
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .comment-preview-date {
            color: rgba(226, 232, 240, 0.5);
            font-size: 11px;
            margin-left: auto;
        }
        
        .comment-preview-content {
            color: var(--light);
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-line;
        }
        
        /* Boutons Edit/Delete pour les commentaires */
        .comment-preview-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        
        .btn-edit-comment {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-edit-comment:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-delete-comment {
            background: rgba(236, 72, 153, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-delete-comment:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-1px);
        }
        
        .no-comments {
            text-align: center;
            color: rgba(226, 232, 240, 0.5);
            font-size: 14px;
            padding: 20px;
        }
        
        .view-all-comments {
            display: block;
            text-align: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .view-all-comments:hover {
            color: var(--accent);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--light);
            text-decoration: none;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: white;
        }
        
        .pagination a.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
        }
        
        /* Hidden sections */
        .hidden {
            display: none;
        }
        
        /* Modal AI Assistant */
        .ai-assistant-modal .modal-content {
            background: linear-gradient(135deg, #0f172a, #1e1b4b);
            border: 2px solid #10b981;
            border-radius: 20px;
        }
        
        .ai-header {
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .ai-response {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            color: #e2e8f0;
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .ai-thinking {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
        }
        
        .ai-thinking i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .ai-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .ai-question {
            background: rgba(99, 102, 241, 0.1);
            border-left: 4px solid #6366f1;
        }
        
        .ai-answer {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid #10b981;
        }
        
        .ai-timestamp {
            font-size: 12px;
            color: #94a3b8;
            text-align: right;
            margin-top: 5px;
        }
        
        .ai-code-block {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        
        .ai-warning {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        
        .ai-tip {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        
        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 60px 0 30px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            margin-top: 80px;
        }
        
        .footer-brand {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        footer h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        footer a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
            margin-bottom: 10px;
        }
        
        footer a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transform: translateY(-5px);
            padding-left: 0;
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--light);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .section-title h2 { font-size: 32px; }
            .comment-preview-actions {
                flex-direction: column;
                gap: 5px;
            }
            .reaction-buttons {
                flex-wrap: wrap;
            }
            .feed-filter-container {
                justify-content: center;
            }
            .feed-filter-btn {
                padding: 10px 15px;
                font-size: 13px;
            }
            .ai-assistant-btn {
                margin-left: 0;
                margin-top: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .feed-filter-container {
                gap: 10px;
            }
            .feed-filter-btn {
                flex: 1;
                min-width: 140px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="particle" style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.3); top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 60px; height: 60px; background: rgba(139, 92, 246, 0.3); top: 60%; left: 80%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 80px; height: 80px; background: rgba(236, 72, 153, 0.3); top: 80%; left: 20%; animation-delay: 6s;"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="./index.html">
                <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="./index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./courses.html">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Ai-shieldhub/View/frontcommunaute/addPublication.php?id_utilisateur=<?= $userId ?>">Publications</a></li>                    <li class="nav-item"><a class="nav-link" href="./quiz.html">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="./tournament.html">Tournoi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fas fa-user" style="font-size: 16px;"></i>
                            </div>
                             <span><?php echo htmlspecialchars($userName); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(99, 102, 241, 0.3);">
                            <li><a class="dropdown-item" href="#" onclick="openProfileModal(); return false;" style="color: var(--light);"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="#" onclick="showMyPublications(); return false;" style="color: var(--light);"><i class="fas fa-newspaper me-2"></i>My Publications</a></li>
                            <li><hr class="dropdown-divider" style="border-color: rgba(99, 102, 241, 0.3);"></li>
                            <li><a class="dropdown-item" href="./logout.html" style="color: var(--accent);"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container community-container">
            <div class="section-title">
                <h2>Learning Exchange</h2>
                <p>Share valuable insights, files, or videos and enrich each other's learning experience</p>
            </div>
            
            <!-- Bouton pour ouvrir le modal d'ajout -->
            <div class="text-center mb-4">
                <button type="button" class="add-publication-btn" data-bs-toggle="modal" data-bs-target="#addPublicationModal">
                    <i class="fas fa-plus-circle"></i>
                    Create New Publication
                </button>
            </div>
            
            <!-- My Publications Section -->
            <section id="mes-publications-section" class="<?= $activeSection === 'mes' ? '' : 'hidden' ?>">
                <!-- BOUTON RETURN TO FEED -->
                <a href="#" class="return-feed-btn" id="returnToFeed">
                    <i class="fas fa-arrow-left me-2"></i>Return to Feed
                </a>
                
                <!-- My Publications List -->
                <h4 class="text-white mb-4">My Publications</h4>
                
                <?php if(!empty($myPublications)): ?>
                    <?php foreach($myPublications as $p): 
                        // Récupérer les réactions pour cette publication
                        $reactionsSummary = $reactionController->getReactionsSummary($p['id_publication']);
                        $userReaction = $reactionController->getUserReaction($p['id_publication'], $idUser);
                    ?>
                        <div class="publication-card">
                            <div class="publication-header">
                                <div class="publication-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="publication-author"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="publication-date"><?= date('M j, Y \a\t g:i A', strtotime($p['date_publication'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="publication-content">
                                <?= nl2br(htmlspecialchars($p['texte'])) ?>
                            </div>
                            
                            <?php if(!empty($p['fichier'])): ?>
                                <div class="publication-file">
                                    <a href="../../<?= htmlspecialchars($p['fichier']) ?>" target="_blank" class="file-link">
                                        <i class="fas fa-file"></i>View attached file
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <!-- NOUVEAU SYSTÈME DE RÉACTIONS AVEC BOUTON AI -->
                            <div class="reaction-buttons">
                                <button class="reaction-btn <?= $userReaction === 'like' ? 'active liked' : '' ?>" 
                                        data-publication-id="<?= $p['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="like">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="reaction-count like-count" data-publication-id="<?= $p['id_publication'] ?>">
                                        <?= $reactionsSummary['like'] ?>
                                    </span>
                                </button>
                                
                                <button class="reaction-btn <?= $userReaction === 'dislike' ? 'active disliked' : '' ?>" 
                                        data-publication-id="<?= $p['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="dislike">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="reaction-count dislike-count" data-publication-id="<?= $p['id_publication'] ?>">
                                        <?= $reactionsSummary['dislike'] ?>
                                    </span>
                                </button>
                                
                                <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" class="action-btn">
                                    <i class="fas fa-comments"></i>Comments
                                </a>

                                <!-- Boutons Edit/Delete pour mes publications -->
                                <a href="editPublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn">
                                    <i class="fas fa-edit"></i>Edit
                                </a>
                                <a href="deletePublicationFront.php?id=<?= $p['id_publication'] ?>&id_utilisateur=<?= $idUser ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this publication?')">
                                    <i class="fas fa-trash"></i>Delete
                                </a>
                                
                                <!-- BOUTON AI ASSISTANT -->
                                <button class="ai-assistant-btn" data-publication-id="<?= $p['id_publication'] ?>">
                                    <i class="fas fa-robot"></i>AI Assistant
                                </button>
                            </div>

                            <!-- SECTION COMMENTAIRES POUR CETTE PUBLICATION -->
                            <div class="comments-preview">
                                <?php
                                // Récupérer les commentaires pour cette publication
                                $commentairesPub = $commentController->getCommentairesByPublication($p['id_publication']);
                                ?>
                                
                                <div class="comments-header">
                                    <div class="comments-title">
                                        <i class="fas fa-comments me-2"></i>Comments (<?= count($commentairesPub) ?>)
                                    </div>
                                </div>

                                <?php if (!empty($commentairesPub)): ?>
                                    <!-- Afficher les 2 derniers commentaires -->
                                    <?php 
                                    $recentComments = array_slice($commentairesPub, 0, 2);
                                    foreach ($recentComments as $comment): ?>
                                        <div class="comment-preview">
                                            <div class="comment-preview-header">
                                                <div class="comment-preview-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="comment-preview-author">
                                                    <?= htmlspecialchars($comment['name']) ?>
                                                </div>
                                                <div class="comment-preview-date">
                                                    <?= date('M j, Y', strtotime($comment['date_commentaire'])) ?>
                                                </div>
                                            </div>
                                            <div class="comment-preview-content">
                                                <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                            </div>
                                            
                                            <!-- Afficher les boutons Edit/Delete seulement pour les commentaires de l'utilisateur connecté -->
                                            <?php if ($comment['id_utilisateur'] == $idUser): ?>
                                                <div class="comment-preview-actions">
                                                    <a href="editcommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" 
                                                       class="btn-edit-comment">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <a href="deletecommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" 
                                                       class="btn-delete-comment"
                                                       onclick="return confirm('Are you sure you want to delete this comment?')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Lien pour voir tous les commentaires si plus de 2 -->
                                    <?php if (count($commentairesPub) > 2): ?>
                                        <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $p['id_publication'] ?>" class="view-all-comments">
                                            View all <?= count($commentairesPub) ?> comments
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-comments">
                                        <i class="fas fa-comment-slash me-2"></i>No comments yet
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="publication-card text-center">
                        <i class="fas fa-newspaper mb-3" style="font-size: 48px; color: rgba(226, 232, 240, 0.3);"></i>
                        <h5 class="text-white">No Publications Yet</h5>
                        <p class="text-light">Start by creating your first publication using the button above!</p>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination Mes Publications -->
                <?php if($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?id_utilisateur=<?= $idUser ?>&page=<?= $i ?>&section=mes" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Feed Section -->
            <section id="feed-section" class="<?= $activeSection === 'feed' ? '' : 'hidden' ?>">
                <h4 class="text-white mb-4">Global Feed</h4>

                <!-- Boutons de tri du feed -->
                <div class="feed-filter-container">
                    <a href="?id_utilisateur=<?= $idUser ?>&feed_mode=recent&section=feed" 
                       class="feed-filter-btn <?= $feedMode==='recent' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i>
                        <span>Most Recent</span>
                    </a>
                    
                    <a href="?id_utilisateur=<?= $idUser ?>&feed_mode=popular&section=feed" 
                       class="feed-filter-btn <?= $feedMode==='popular' ? 'active' : '' ?>">
                        <i class="fas fa-fire"></i>
                        <span>Most Popular</span>
                    </a>
                    
                    <a href="?id_utilisateur=<?= $idUser ?>&feed_mode=commented&section=feed" 
                       class="feed-filter-btn <?= $feedMode==='commented' ? 'active' : '' ?>">
                        <i class="fas fa-comments"></i>
                        <span>Most Commented</span>
                    </a>
                    
                    <a href="?id_utilisateur=<?= $idUser ?>&feed_mode=trending&section=feed" 
                       class="feed-filter-btn <?= $feedMode==='trending' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Trending Today</span>
                    </a>
                    
                    <a href="?id_utilisateur=<?= $idUser ?>&feed_mode=relevant&section=feed" 
                       class="feed-filter-btn <?= $feedMode==='relevant' ? 'active' : '' ?>">
                        <i class="fas fa-star"></i>
                        <span>Most Relevant</span>
                    </a>
                </div>

                <?php if(!empty($feedPublications)): ?>
                    <?php foreach($feedPublications as $f): 
                        $reactionsSummary = $reactionController->getReactionsSummary($f['id_publication']);
                        $userReaction = $reactionController->getUserReaction($f['id_publication'], $idUser);
                    ?>
                        <div class="publication-card">
                            <div class="publication-header">
                                <div class="publication-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="publication-author"><?= htmlspecialchars($f['name']) ?></div>
                                    <div class="publication-date"><?= date('M j, Y \a\t g:i A', strtotime($f['date_publication'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="publication-content">
                                <?= nl2br(htmlspecialchars($f['texte'])) ?>
                            </div>
                            
                            <?php if(!empty($f['fichier'])): ?>
                                <div class="publication-file">
                                    <a href="../../<?= htmlspecialchars($f['fichier']) ?>" target="_blank" class="file-link">
                                        <i class="fas fa-file"></i>View attached file
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="reaction-buttons">
                                <button class="reaction-btn <?= $userReaction==='like'?'active liked':'' ?>" 
                                        data-publication-id="<?= $f['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="like">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="reaction-count like-count" data-publication-id="<?= $f['id_publication'] ?>">
                                        <?= $reactionsSummary['like'] ?>
                                    </span>
                                </button>
                                
                                <button class="reaction-btn <?= $userReaction==='dislike'?'active disliked':'' ?>" 
                                        data-publication-id="<?= $f['id_publication'] ?>" 
                                        data-user-id="<?= $idUser ?>" 
                                        data-reaction-type="dislike">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="reaction-count dislike-count" data-publication-id="<?= $f['id_publication'] ?>">
                                        <?= $reactionsSummary['dislike'] ?>
                                    </span>
                                </button>
                                
                                <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="action-btn">
                                    <i class="fas fa-comments"></i>Comments
                                </a>
                                
                                <!-- BOUTON AI ASSISTANT -->
                                <button class="ai-assistant-btn" data-publication-id="<?= $f['id_publication'] ?>">
                                    <i class="fas fa-robot"></i>AI Assistant
                                </button>
                            </div>

                            <div class="comments-preview">
                                <?php $commentairesFeed = $commentController->getCommentairesByPublication($f['id_publication']); ?>
                                <div class="comments-header">
                                    <div class="comments-title">
                                        <i class="fas fa-comments me-2"></i>Comments (<?= count($commentairesFeed) ?>)
                                    </div>
                                </div>
                                
                                <?php if (!empty($commentairesFeed)): ?>
                                    <?php foreach(array_slice($commentairesFeed, 0, 2) as $comment): ?>
                                        <div class="comment-preview">
                                            <div class="comment-preview-header">
                                                <div class="comment-preview-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="comment-preview-author">
                                                    <?= htmlspecialchars($comment['name']) ?>
                                                </div>
                                                <div class="comment-preview-date">
                                                    <?= date('M j, Y', strtotime($comment['date_commentaire'])) ?>
                                                </div>
                                            </div>
                                            <div class="comment-preview-content">
                                                <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                            </div>
                                            <?php if($comment['id_utilisateur'] == $idUser): ?>
                                                <div class="comment-preview-actions">
                                                    <a href="editcommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="btn-edit-comment">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <a href="deletecommentaire.php?id_commentaire=<?= $comment['id'] ?>&id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="btn-delete-comment" onclick="return confirm('Are you sure?')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if(count($commentairesFeed) > 2): ?>
                                        <a href="addcommentaire.php?id_utilisateur=<?= $idUser ?>&id_publication=<?= $f['id_publication'] ?>" class="view-all-comments">
                                            View all <?= count($commentairesFeed) ?> comments
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-comments">
                                        <i class="fas fa-comment-slash me-2"></i>No comments yet
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="publication-card text-center">
                        <i class="fas fa-newspaper mb-3" style="font-size: 48px; color: rgba(226, 232, 240, 0.3);"></i>
                        <h5 class="text-white">No Publications Yet</h5>
                        <p class="text-light">Be the first to create a publication using the button above!</p>
                    </div>
                <?php endif; ?>

                <!-- Pagination Feed -->
                <?php if($totalFeedPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalFeedPages; $i++): ?>
                            <a href="addPublication.php?id_utilisateur=<?= $idUser ?>&feed_page=<?= $i ?>&feed_mode=<?= $feedMode ?>&section=feed" class="<?= $i==$feedPage?'active':'' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- Modal d'ajout de publication -->
    <div class="modal fade" id="addPublicationModal" tabindex="-1" aria-labelledby="addPublicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPublicationModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Create New Publication
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formPublicationModal" action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="postTextModal"><i class="fas fa-edit me-2"></i>Content</label>
                            <textarea name="texte" id="postTextModal" rows="6" placeholder="Share your thoughts, questions, or cybersecurity insights..."></textarea>
                            <div class="char-counter" id="charCounterModal">0/200 characters</div>
                            <div class="error-message" id="textErrorModal"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fileInputModal"><i class="fas fa-paperclip me-2"></i>Attach File</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="file" id="fileInputModal">
                                <div class="file-input-label" id="fileInputLabelModal">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="formPublicationModal" class="btn-publish" id="submitButtonModal">
                        <i class="fas fa-paper-plane me-2"></i>Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal AI Assistant -->
    <div class="modal fade ai-assistant-modal" id="aiAssistantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title ai-header">
                        <i class="fas fa-robot me-2"></i>CyberSecurity AI Assistant
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ai-context mb-4">
                        <h6 class="text-light mb-2"><i class="fas fa-file-alt me-2"></i>Publication Context:</h6>
                        <div class="publication-preview p-3 bg-dark rounded">
                            <p id="aiPublicationPreview" class="mb-0 text-light"></p>
                        </div>
                    </div>
                    
                    <div id="aiChatHistory"></div>
                    
                    <div class="ai-thinking" id="aiThinking" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>AI Assistant is analyzing your question...</p>
                    </div>
                    
                    <form id="aiQuestionForm">
                        <input type="hidden" id="aiPublicationId">
                        <input type="hidden" id="aiPublicationText">
                        
                        <div class="form-group">
                            <label for="aiQuestionInput" class="text-light mb-2">
                                <i class="fas fa-question-circle me-2"></i>Ask your question:
                            </label>
                            <textarea 
                                id="aiQuestionInput" 
                                class="form-control bg-dark text-light" 
                                rows="3" 
                                placeholder="Ask a question about this publication, request clarification, or ask for related resources..."
                                required></textarea>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="ai-assistant-btn w-100">
                                <i class="fas fa-paper-plane me-2"></i>Ask AI Assistant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-shield-halved me-2"></i>AI ShieldHub
                    </div>
                    <p style="color: var(--light); margin-top: 15px;">Empowering students with AI-powered cybersecurity education for a safer digital future.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Platform</h5>
                    <a href="./courses.html">Courses</a>
                    <a href="./community.html">Community</a>
                    <a href="./tools.html">Tools</a>
                    <a href="./pricing.html">Pricing</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <a href="./blog.html">Blog</a>
                    <a href="./docs.html">Documentation</a>
                    <a href="./faq.html">FAQ</a>
                    <a href="./support.html">Support</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Company</h5>
                    <a href="./about.html">About Us</a>
                    <a href="./contact.html">Contact</a>
                    <a href="./careers.html">Careers</a>
                    <a href="./press.html">Press Kit</a>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <a href="./privacy.html">Privacy Policy</a>
                    <a href="./terms.html">Terms of Service</a>
                    <a href="./cookies.html">Cookie Policy</a>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-0">© 2023 AI ShieldHub. All rights reserved. Built with <i class="fas fa-heart" style="color: var(--accent);"></i> for student safety.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire de publication dans le modal
        class PublicationModalValidator {
            constructor() {
                this.postText = document.getElementById('postTextModal');
                this.charCounter = document.getElementById('charCounterModal');
                this.textError = document.getElementById('textErrorModal');
                this.submitButton = document.getElementById('submitButtonModal');
                this.fileInput = document.getElementById('fileInputModal');
                this.fileInputLabel = document.getElementById('fileInputLabelModal');
                this.maxChars = 200;
                this.maxFileSize = 10 * 1024 * 1024; // 10MB
                this.allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
                
                this.init();
            }

            init() {
                // Événements de validation
                this.postText.addEventListener('input', this.updateCharCounter.bind(this));
                this.postText.addEventListener('blur', this.validateForm.bind(this));
                this.fileInput.addEventListener('change', this.handleFileSelect.bind(this));
                
                // Validation à la soumission
                document.getElementById('formPublicationModal').addEventListener('submit', this.handleSubmit.bind(this));
                
                // Validation initiale
                this.updateCharCounter();
                this.validateForm();
            }

            updateCharCounter() {
                const charCount = this.postText.value.length;
                
                // Mettre à jour le compteur
                this.charCounter.textContent = `${charCount}/${this.maxChars} characters`;
                
                // Changer la couleur selon le nombre de caractères
                if (charCount > this.maxChars * 0.8) {
                    this.charCounter.style.color = '#ec4899';
                } else {
                    this.charCounter.style.color = '#e2e8f0';
                }
                
                // Limiter automatiquement à 200 caractères
                if (charCount > this.maxChars) {
                    this.postText.value = this.postText.value.substring(0, this.maxChars);
                    this.updateCharCounter();
                }
                
                // Valider en temps réel
                this.validateForm();
            }

            handleFileSelect() {
                if (this.fileInput.files && this.fileInput.files[0]) {
                    const file = this.fileInput.files[0];
                    
                    // Mettre à jour l'affichage du fichier
                    this.fileInputLabel.innerHTML = '<i class="fas fa-file me-2"></i>' + file.name;
                    this.fileInputLabel.style.background = 'rgba(99, 102, 241, 0.1)';
                    this.fileInputLabel.style.borderColor = 'var(--primary)';
                    this.fileInputLabel.style.color = 'white';
                    
                    // Validation du fichier
                    this.validateFile(file);
                } else {
                    this.resetFileInput();
                }
            }

            validateFile(file) {
                let isValid = true;
                
                // Validation de la taille
                if (file.size > this.maxFileSize) {
                    this.showFileError(`File is too large. Maximum size is ${this.formatFileSize(this.maxFileSize)}`);
                    isValid = false;
                }
                
                // Validation du type
                if (!this.allowedFileTypes.includes(file.type)) {
                    this.showFileError('File type not allowed. Allowed types: JPG, PNG, GIF, PDF, TXT');
                    isValid = false;
                }
                
                if (!isValid) {
                    this.fileInput.value = '';
                    this.resetFileInput();
                }
                
                return isValid;
            }

            resetFileInput() {
                this.fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload';
                this.fileInputLabel.style.background = 'rgba(255, 255, 255, 0.05)';
                this.fileInputLabel.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                this.fileInputLabel.style.color = 'rgba(226, 232, 240, 0.7)';
            }

            showFileError(message) {
                // Créer ou mettre à jour le message d'erreur
                let fileError = document.getElementById('fileErrorModal');
                if (!fileError) {
                    fileError = document.createElement('div');
                    fileError.id = 'fileErrorModal';
                    fileError.className = 'error-message';
                    this.fileInput.parentNode.appendChild(fileError);
                }
                fileError.textContent = message;
                fileError.style.display = 'block';
                
                // Cacher l'erreur après 5 secondes
                setTimeout(() => {
                    fileError.style.display = 'none';
                }, 5000);
            }

            validateForm() {
                const content = this.postText.value.trim();
                let isValid = true;
                
                // Réinitialiser les erreurs
                this.textError.style.display = 'none';
                this.textError.textContent = '';
                this.postText.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                
                // Validation du contenu vide
                if (content === '') {
                    this.textError.textContent = 'Please enter your publication content.';
                    this.textError.style.display = 'block';
                    this.postText.style.borderColor = '#ec4899';
                    isValid = false;
                }
                
                // Validation de la longueur
                if (content.length > this.maxChars) {
                    this.textError.textContent = `Publication cannot exceed ${this.maxChars} characters.`;
                    this.textError.style.display = 'block';
                    this.postText.style.borderColor = '#ec4899';
                    isValid = false;
                }
                
                // Activer/désactiver le bouton de soumission
                this.submitButton.disabled = !isValid;
                
                return isValid;
            }

            handleSubmit(event) {
                if (!this.validateForm()) {
                    event.preventDefault();
                    this.postText.focus();
                    return;
                }
                
                // Validation du fichier si sélectionné
                if (this.fileInput.files.length > 0) {
                    const file = this.fileInput.files[0];
                    if (!this.validateFile(file)) {
                        event.preventDefault();
                        return;
                    }
                }
                
                // Empêcher la double soumission
                this.submitButton.disabled = true;
                this.submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Publishing...';
                
                // Fermer le modal après soumission
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPublicationModal'));
                    if (modal) {
                        modal.hide();
                    }
                }, 1000);
            }

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }

        // Système de réactions avec JavaScript
        class ReactionSystem {
            constructor() {
                this.init();
            }

            init() {
                // Initialiser les événements pour tous les boutons de réaction
                document.querySelectorAll('.reaction-btn').forEach(btn => {
                    btn.addEventListener('click', this.handleReaction.bind(this));
                });

                // Gestion du double-clic
                document.querySelectorAll('.reaction-btn').forEach(btn => {
                    let clickCount = 0;
                    btn.addEventListener('click', (e) => {
                        clickCount++;
                        if (clickCount === 1) {
                            setTimeout(() => {
                                if (clickCount === 1) {
                                    // Simple click - déjà géré par handleReaction
                                } else {
                                    // Double click - supprimer la réaction
                                    this.handleDoubleClick(e);
                                }
                                clickCount = 0;
                            }, 300);
                        }
                    });
                });
            }

            async handleReaction(event) {
                event.preventDefault();
                
                const button = event.currentTarget;
                const publicationId = button.dataset.publicationId;
                const userId = button.dataset.userId;
                const reactionType = button.dataset.reactionType;
                
                // Désactiver le bouton pendant le traitement
                button.disabled = true;
                
                try {
                    const response = await this.sendReaction(publicationId, userId, reactionType);
                    
                    if (response.success) {
                        this.updateReactionUI(publicationId, response);
                    } else {
                        console.error('Erreur:', response.message);
                        alert('Erreur lors de l\'enregistrement de la réaction');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Erreur de connexion');
                } finally {
                    button.disabled = false;
                }
            }

            async handleDoubleClick(event) {
                const button = event.currentTarget;
                const publicationId = button.dataset.publicationId;
                const userId = button.dataset.userId;
                const reactionType = button.dataset.reactionType;
                
                // Vérifier si le bouton est déjà actif (double-clic sur la même réaction)
                if (button.classList.contains('active')) {
                    // Simuler un clic pour supprimer la réaction
                    await this.handleReaction(event);
                }
            }

            async sendReaction(publicationId, userId, reactionType) {
                const formData = new FormData();
                formData.append('id_publication', publicationId);
                formData.append('id_utilisateur', userId);
                formData.append('type', reactionType);

                const response = await fetch('addreaction.php', {
                    method: 'POST',
                    body: formData
                });

                return await response.json();
            }

            updateReactionUI(publicationId, data) {
                const likeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="like"]`);
                const dislikeBtn = document.querySelector(`.reaction-btn[data-publication-id="${publicationId}"][data-reaction-type="dislike"]`);
                const likeCount = document.querySelector(`.like-count[data-publication-id="${publicationId}"]`);
                const dislikeCount = document.querySelector(`.dislike-count[data-publication-id="${publicationId}"]`);

                // Mettre à jour les compteurs
                if (likeCount) likeCount.textContent = data.likes;
                if (dislikeCount) dislikeCount.textContent = data.dislikes;

                // Mettre à jour l'état actif des boutons
                this.updateButtonState(likeBtn, 'like', data.userReaction);
                this.updateButtonState(dislikeBtn, 'dislike', data.userReaction);
            }

            updateButtonState(button, buttonType, userReaction) {
                if (!button) return;

                // Retirer toutes les classes d'état actif
                button.classList.remove('active', 'liked', 'disliked');

                // Ajouter la classe active si c'est la réaction actuelle de l'utilisateur
                if (userReaction === buttonType) {
                    button.classList.add('active', buttonType + 'd');
                }
            }
        }

        // NOUVEAU CODE POUR L'ASSISTANT AI AVEC CHATGPT RÉEL
        class AIAssistant {
            constructor() {
                this.modal = null;
                this.currentPublicationId = null;
                this.currentPublicationText = null;
                this.chatHistory = [];
                this.maxChatHistory = 10; // Limiter l'historique
                this.init();
            }

            init() {
                // Initialiser tous les boutons AI
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.ai-assistant-btn')) {
                        const btn = e.target.closest('.ai-assistant-btn');
                        this.openAssistant(btn);
                    }
                });

                // Gérer la soumission du formulaire
                const aiForm = document.getElementById('aiQuestionForm');
                if (aiForm) {
                    aiForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.submitQuestion();
                    });
                }

                // Initialiser le modal
                const aiModalElement = document.getElementById('aiAssistantModal');
                if (aiModalElement) {
                    this.modal = new bootstrap.Modal(aiModalElement);
                }
            }

            openAssistant(button) {
                const publicationCard = button.closest('.publication-card');
                const publicationId = button.dataset.publicationId;
                const publicationText = publicationCard.querySelector('.publication-content').textContent;
                
                // Stocker les informations
                this.currentPublicationId = publicationId;
                this.currentPublicationText = publicationText;
                
                // Mettre à jour le modal
                document.getElementById('aiPublicationPreview').textContent = 
                    publicationText.length > 200 ? 
                    publicationText.substring(0, 200) + '...' : 
                    publicationText;
                document.getElementById('aiPublicationId').value = publicationId;
                document.getElementById('aiPublicationText').value = publicationText;
                
                // Effacer l'historique précédent
                this.clearChatHistory();
                
                // Afficher le modal
                if (this.modal) {
                    this.modal.show();
                }
                
                // Focus sur l'input
                setTimeout(() => {
                    const questionInput = document.getElementById('aiQuestionInput');
                    if (questionInput) {
                        questionInput.focus();
                    }
                }, 500);
            }

            clearChatHistory() {
                this.chatHistory = [];
                const chatHistoryElement = document.getElementById('aiChatHistory');
                if (chatHistoryElement) {
                    chatHistoryElement.innerHTML = '';
                }
            }

            formatAIResponse(text) {
                // Formater la réponse AI pour afficher le code correctement
                let formattedText = text;
                
                // Détecter les blocs de code
                const codeBlocks = text.match(/```([\s\S]*?)```/g);
                if (codeBlocks) {
                    codeBlocks.forEach((block, index) => {
                        const codeContent = block.replace(/```[\w]*\n?/g, '').replace(/```/g, '');
                        const formattedBlock = `<div class="ai-code-block">${codeContent}</div>`;
                        formattedText = formattedText.replace(block, formattedBlock);
                    });
                }
                
                // Détecter les notes importantes
                if (text.includes('Important:') || text.includes('Attention:')) {
                    formattedText = formattedText.replace(/(Important:|Attention:)(.*?)(\n\n|$)/g, 
                        '<div class="ai-warning"><strong>$1</strong>$2</div>');
                }
                
                // Détecter les conseils
                if (text.includes('Conseil:') || text.includes('Tip:')) {
                    formattedText = formattedText.replace(/(Conseil:|Tip:)(.*?)(\n\n|$)/g, 
                        '<div class="ai-tip"><strong>$1</strong>$2</div>');
                }
                
                return formattedText;
            }

            addMessageToHistory(type, content, timestamp = null) {
                const chatHistoryElement = document.getElementById('aiChatHistory');
                if (!chatHistoryElement) return;
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `ai-message ai-${type}`;
                
                const icon = type === 'question' ? 'fas fa-user' : 'fas fa-robot';
                const label = type === 'question' ? 'Your question' : 'AI Assistant';
                const formattedContent = type === 'answer' ? this.formatAIResponse(content) : content;
                
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start mb-2">
                        <i class="${icon} me-2" style="color: ${type === 'question' ? '#6366f1' : '#10b981'}"></i>
                        <strong class="text-light">${label}</strong>
                    </div>
                    <div class="text-light">${formattedContent}</div>
                    ${timestamp ? `<div class="ai-timestamp">${timestamp}</div>` : ''}
                `;
                
                chatHistoryElement.appendChild(messageDiv);
                chatHistoryElement.scrollTop = chatHistoryElement.scrollHeight;
                
                // Garder l'historique en mémoire
                this.chatHistory.push({
                    type: type,
                    content: content,
                    timestamp: timestamp || new Date().toLocaleTimeString()
                });
                
                // Limiter la taille de l'historique
                if (this.chatHistory.length > this.maxChatHistory) {
                    this.chatHistory.shift();
                }
            }

            async submitQuestion() {
                const questionInput = document.getElementById('aiQuestionInput');
                const question = questionInput.value.trim();
                
                if (!question) {
                    alert('Please enter a question');
                    return;
                }
                
                // Validation de la longueur de la question
                if (question.length > 500) {
                    alert('Question is too long. Maximum 500 characters.');
                    return;
                }
                
                // Ajouter la question à l'historique
                this.addMessageToHistory('question', question, new Date().toLocaleTimeString());
                
                // Effacer l'input
                questionInput.value = '';
                
                // Afficher l'indicateur de chargement
                const thinkingElement = document.getElementById('aiThinking');
                if (thinkingElement) {
                    thinkingElement.style.display = 'block';
                }
                
                // Désactiver le bouton de soumission
                const submitButton = document.querySelector('#aiQuestionForm button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                }
                
                try {
                    // Envoyer la requête au serveur
                    const formData = new FormData();
                    formData.append('ai_question', question);
                    formData.append('publication_id', this.currentPublicationId);
                    formData.append('publication_text', this.currentPublicationText);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Ajouter la réponse AI à l'historique
                        this.addMessageToHistory('answer', data.response, data.timestamp);
                    } else {
                        throw new Error(data.response || 'AI Assistant error');
                    }
                } catch (error) {
                    console.error('AI Assistant error:', error);
                    
                    // En cas d'erreur, fournir une réponse de secours
                    const fallbackResponse = `I apologize, but I'm currently experiencing technical difficulties. Here's some general cybersecurity advice:\n\n` +
                        `1. Always keep your software updated\n` +
                        `2. Use strong, unique passwords for each account\n` +
                        `3. Enable two-factor authentication whenever possible\n` +
                        `4. Be cautious with email attachments and links\n` +
                        `5. Regularly backup your important data\n\n` +
                        `Error details: ${error.message}`;
                    
                    this.addMessageToHistory('answer', fallbackResponse, new Date().toLocaleTimeString());
                } finally {
                    // Cacher l'indicateur de chargement
                    if (thinkingElement) {
                        thinkingElement.style.display = 'none';
                    }
                    
                    // Réactiver le bouton de soumission
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Ask AI Assistant';
                    }
                }
            }
        }

        // Section Navigation
        const mesSection = document.getElementById('mes-publications-section');
        const feedSection = document.getElementById('feed-section');
        const returnToFeed = document.getElementById('returnToFeed');

        function showSection(section) {
            // Masquer toutes les sections
            mesSection.style.display = 'none';
            feedSection.style.display = 'none';
            
            // Afficher la section demandée
            if(section === 'mes') {
                mesSection.style.display = 'block';
            } else if(section === 'feed') {
                feedSection.style.display = 'block';
            }
            
            // Mettre à jour l'URL
            const url = new URL(window.location);
            url.searchParams.set('section', section);
            window.history.pushState({}, '', url);
        }

        // Fonction pour afficher mes publications depuis le dropdown
        function showMyPublications() {
            showSection('mes');
            // Fermer le dropdown
            const dropdown = document.querySelector('.dropdown-menu');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // Événements de clic
        if (returnToFeed) {
            returnToFeed.addEventListener('click', function(e) {
                e.preventDefault();
                showSection('feed');
            });
        }

        // Vérifier l'état initial au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section');
            
            if (section === 'mes') {
                showSection('mes');
            } else {
                showSection('feed');
            }

            // Initialiser les validateurs
            new PublicationModalValidator();
            new ReactionSystem();
            
            // Initialiser l'assistant AI avec ChatGPT réel
            window.aiAssistant = new AIAssistant();

            // Réinitialiser le modal quand il est fermé
            const addPublicationModal = document.getElementById('addPublicationModal');
            if (addPublicationModal) {
                addPublicationModal.addEventListener('hidden.bs.modal', function() {
                    // Réinitialiser le formulaire
                    document.getElementById('formPublicationModal').reset();
                    document.getElementById('charCounterModal').textContent = '0/200 characters';
                    document.getElementById('charCounterModal').style.color = '#e2e8f0';
                    document.getElementById('textErrorModal').style.display = 'none';
                    
                    // Réinitialiser l'affichage du fichier
                    const fileInputLabel = document.getElementById('fileInputLabelModal');
                    fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Choose a file to upload';
                    fileInputLabel.style.background = 'rgba(255, 255, 255, 0.05)';
                    fileInputLabel.style.borderColor = 'rgba(99, 102, 241, 0.3)';
                    fileInputLabel.style.color = 'rgba(226, 232, 240, 0.7)';
                    
                    // Réactiver le bouton de soumission
                    document.getElementById('submitButtonModal').disabled = false;
                    document.getElementById('submitButtonModal').innerHTML = '<i class="fas fa-paper-plane me-2"></i>Publish';
                });
            }
            
            // Réinitialiser le modal AI quand il est fermé
            const aiModal = document.getElementById('aiAssistantModal');
            if (aiModal) {
                aiModal.addEventListener('hidden.bs.modal', function() {
                    // Réinitialiser le formulaire AI
                    document.getElementById('aiQuestionForm').reset();
                    const thinkingElement = document.getElementById('aiThinking');
                    if (thinkingElement) {
                        thinkingElement.style.display = 'none';
                    }
                    
                    // Réactiver le bouton de soumission
                    const submitButton = document.querySelector('#aiQuestionForm button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Ask AI Assistant';
                    }
                });
            }

            // Confirmation pour la suppression des commentaires
            document.querySelectorAll('.btn-delete-comment').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>