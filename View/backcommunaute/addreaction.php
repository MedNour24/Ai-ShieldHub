<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Controller/ReactionController.php';
require_once __DIR__ . '/../../Model/Reaction.php';

// Vérifier que les paramètres sont présents
if (!isset($_POST['id_publication']) || !isset($_POST['id_utilisateur']) || !isset($_POST['type'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

$idPublication = intval($_POST['id_publication']);
$idUser = intval($_POST['id_utilisateur']);
$type = $_POST['type'];

if ($idPublication <= 0 || $idUser <= 0 || !in_array($type, ['like', 'dislike'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit();
}

try {
    $reactionController = new ReactionController();
    
    // Créer l'objet Reaction avec les bons paramètres
    $reaction = new Reaction(null, $idPublication, $idUser, $type);
    
    // Ajouter ou mettre à jour la réaction
    $result = $reactionController->addOrUpdateReaction($reaction);
    
    if ($result) {
        // Compter les nouvelles réactions
        $likesCount = $reactionController->countReactions($idPublication, 'like');
        $dislikesCount = $reactionController->countReactions($idPublication, 'dislike');
        
        // Vérifier la réaction actuelle de l'utilisateur
        $userReaction = $reactionController->getUserReaction($idPublication, $idUser);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'likes' => $likesCount,
            'dislikes' => $dislikesCount,
            'userReaction' => $userReaction
        ]);
    } else {
        throw new Exception("Erreur lors de l'opération sur la base de données");
    }
    
} catch (Exception $e) {
    error_log("Reaction error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>