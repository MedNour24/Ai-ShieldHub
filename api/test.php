<?php
// Simple test endpoint for debugging
header('Content-Type: application/json; charset=utf-8');

ob_start();

try {
    // Test 1: Basic response
    $test = [
        'success' => true,
        'message' => 'API is working',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'extensions' => [
            'curl' => extension_loaded('curl') ? 'yes' : 'no',
            'json' => extension_loaded('json') ? 'yes' : 'no',
            'pdo' => extension_loaded('pdo') ? 'yes' : 'no'
        ]
    ];
    
    ob_clean();
    echo json_encode($test, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
