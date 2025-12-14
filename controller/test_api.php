<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test API Quiz</h2>";

require_once '../config.php';

try {
    $db = config::getConnexion();
    echo "✓ Connexion DB réussie<br><br>";
    
    // Test 1: Récupérer le quiz
    echo "<h3>Test 1: Quiz ID 1</h3>";
    $stmt = $db->prepare("SELECT * FROM quiz WHERE id_quiz = 1");
    $stmt->execute();
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($quiz);
    echo "</pre><br>";
    
    // Test 2: Récupérer les questions
    echo "<h3>Test 2: Questions du Quiz 1</h3>";
    $stmt = $db->prepare("SELECT * FROM reponse WHERE id_quiz = 1");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($questions);
    echo "</pre><br>";
    
    echo "<h3>Nombre de questions: " . count($questions) . "</h3>";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>