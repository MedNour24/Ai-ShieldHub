<?php
/**
 * Tournament AI Assistant API
 * Handles chat requests - ONLY for tournament-related questions
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log function for debugging
function logDebug($message) {
    error_log('[Tournament API] ' . $message);
}

// Configuration
$apiKey = getenv("PERPLEXITY_API_KEY");
define('PPLX_API_URL', 'https://api.perplexity.ai/chat/completions');

/**
 * Check if question is tournament-related
 */
function isTournamentRelated($message) {
    $tournamentKeywords = [
        'tournament', 'tournoi', 'competition', 'compétition', 'contest',
        'championship', 'match', 'game', 'player', 'team', 'score',
        'bracket', 'round', 'final', 'semi-final', 'qualifier',
        'register', 'registration', 'participant', 'prize', 'winner',
        'ctf', 'capture the flag', 'cybersecurity', 'hacking',
        'challenge', 'event', 'esport', 'gaming', 'league'
    ];
    
    $messageLower = strtolower($message);
    
    foreach ($tournamentKeywords as $keyword) {
        if (strpos($messageLower, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Call Perplexity AI API
 */
function askPerplexity($prompt, $history = []) {
    // Check if question is tournament-related
    if (!isTournamentRelated($prompt)) {
        return [
            'success' => true,
            'content' => "I can only assist you with tournament questions. Please ask me about tournaments, competitions, registrations, or related topics."
        ];
    }
    
    $messages = [
        [
            'role' => 'system',
            'content' => 'You are a specialized AI assistant focused EXCLUSIVELY on tournaments, competitions, and cybersecurity events.

YOUR EXPERTISE:
- Tournament formats and rules
- Registration and participation
- CTF (Capture The Flag) competitions
- Cybersecurity challenges
- Esports and gaming tournaments
- Competition scheduling and brackets
- Prize information
- Team/player management

RESPONSE FORMAT:
### 📌 Quick Answer
Brief summary

### 🔑 Key Points
• Point 1
• Point 2
• Point 3

### ✅ Tips (if relevant)
• Helpful tip

FORMATTING RULES:
- NEVER include citation numbers like [1], [2], [3]
- Use ### for headers
- Use bullet points (•) for lists
- Keep answers under 150 words unless detail is needed
- Be conversational and helpful
- Focus only on tournament-related information'
        ]
    ];

    // Add conversation history
    if (!empty($history)) {
        $lastRole = 'system';
        foreach ($history as $msg) {
            if (isset($msg['role']) && isset($msg['content']) && !empty(trim($msg['content']))) {
                if ($msg['role'] === $lastRole && $msg['role'] !== 'system') {
                    continue;
                }
                
                if ($msg['role'] === 'user' || $msg['role'] === 'assistant') {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => trim($msg['content'])
                    ];
                    $lastRole = $msg['role'];
                }
            }
        }
    }

    // Add current prompt
    $lastMessage = end($messages);
    $needsPrompt = true;
    
    if ($lastMessage && $lastMessage['role'] === 'user' && $lastMessage['content'] === $prompt) {
        $needsPrompt = false;
    }
    
    if ($needsPrompt) {
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
    }

    $data = [
        'model' => 'sonar-pro',
        'messages' => $messages,
        'max_tokens' => 400,
        'temperature' => 0.7,
        'top_p' => 0.9
    ];

    $ch = curl_init(PPLX_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . PPLX_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'error' => 'Erreur de connexion: ' . $error
        ];
    }

    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => "Erreur API {$httpCode}: {$response}"
        ];
    }

    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return [
            'success' => true,
            'content' => $result['choices'][0]['message']['content']
        ];
    }

    return [
        'success' => false,
        'error' => 'Réponse invalide de l\'API'
    ];
}

// Main API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logDebug('Received POST request');
    
    $rawInput = file_get_contents('php://input');
    logDebug('Raw input: ' . substr($rawInput, 0, 200));
    
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logDebug('JSON decode error: ' . json_last_error_msg());
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    if (!isset($input['message']) || empty(trim($input['message']))) {
        logDebug('Missing message field');
        echo json_encode([
            'success' => false,
            'error' => 'Message requis'
        ]);
        exit;
    }

    $message = trim($input['message']);
    $history = $input['history'] ?? [];
    
    logDebug('Processing message: ' . substr($message, 0, 50));

    $response = askPerplexity($message, $history);
    logDebug('Response: ' . ($response['success'] ? 'Success' : 'Failed'));
    
    echo json_encode($response);
    exit;
}

// If not POST, return API info
echo json_encode([
    'name' => 'Tournament AI Assistant API',
    'version' => '1.0 - Tournament Questions Only',
    'status' => 'active',
    'endpoint' => '/api.php',
    'method' => 'POST',
    'required_fields' => ['message'],
    'optional_fields' => ['history'],
    'restriction' => 'Only responds to tournament-related questions'
]);
?>