<?php
// Test script for payment API

function testPaymentAPI() {
    echo "<h1>Payment API Test</h1>";
    
    // Test data
    $testData = [
        'course_id' => 1,
        'user_id' => 1,
        'amount' => 99.99,
        'card_number' => '4111111111111111', // Visa test card
        'cardholder_name' => 'John Doe',
        'expiry_month' => '12',
        'expiry_year' => '2025',
        'cvv' => '123',
        'billing_address' => '123 Main St, New York, NY 10001',
        'email' => 'john@example.com'
    ];
    
    echo "<h2>Test Data:</h2>";
    echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Make API request
    $url = 'http://localhost/courses/api/payment.php?action=process';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    echo "<h2>API Response:</h2>";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "<div style='color: red;'>CURL Error: " . curl_error($ch) . "</div>";
    } else {
        echo "<div>HTTP Status: $httpCode</div>";
        echo "<pre>" . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "</pre>";
    }
    
    curl_close($ch);
    
    // Test different card types
    echo "<h2>Test Different Card Types:</h2>";
    
    $testCards = [
        ['type' => 'MasterCard', 'number' => '5555555555554444'],
        ['type' => 'American Express', 'number' => '378282246310005'],
        ['type' => 'Discover', 'number' => '6011111111111117'],
        ['type' => 'Invalid', 'number' => '1234567890123456']
    ];
    
    foreach ($testCards as $card) {
        echo "<h3>Testing {$card['type']}: {$card['number']}</h3>";
        
        $testData['card_number'] = $card['number'];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        curl_close($ch);
    }
    
    // Test validation errors
    echo "<h2>Test Validation Errors:</h2>";
    
    $invalidData = [
        'course_id' => 1,
        'user_id' => 1,
        'amount' => 'invalid',
        'card_number' => '1234',
        'cardholder_name' => '',
        'expiry_month' => '13',
        'expiry_year' => '2020',
        'cvv' => 'abc'
    ];
    
    echo "<h3>Invalid Data Test:</h3>";
    echo "<pre>" . json_encode($invalidData, JSON_PRETTY_PRINT) . "</pre>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    curl_close($ch);
}

// Run tests
testPaymentAPI();
?>