<?php
class CreditCardVerification {
    
    private static $cardPatterns = [
        'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
        'mastercard' => '/^5[1-5][0-9]{14}$|^2(?:2(?:2[1-9]|[3-9][0-9])|[3-6][0-9][0-9]|7(?:[01][0-9]|20))[0-9]{12}$/',
        'amex' => '/^3[47][0-9]{13}$/',
        'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
        'diners' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        'jcb' => '/^(?:2131|1800|35[0-9]{3})[0-9]{11}$/',
        'unionpay' => '/^62[0-9]{14,17}$/'
    ];

    /**
     * Comprehensive credit card validation and verification
     * 
     * @param array $paymentData Array containing payment and card details
     * @return array Validation result with transaction details
     */
    public static function processPayment($paymentData) {
        $errors = [];
        $verificationData = [];
        
        // Basic required fields
        $requiredFields = ['course_id', 'user_id', 'amount', 'card_number', 'cardholder_name', 'expiry_month', 'expiry_year', 'cvv'];
        foreach ($requiredFields as $field) {
            if (empty($paymentData[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Missing required fields'
            ];
        }
        
        // Extract card details
        $cardData = [
            'card_number' => $paymentData['card_number'],
            'cardholder_name' => $paymentData['cardholder_name'],
            'expiry_month' => $paymentData['expiry_month'],
            'expiry_year' => $paymentData['expiry_year'],
            'cvv' => $paymentData['cvv'],
            'billing_address' => $paymentData['billing_address'] ?? '',
            'email' => $paymentData['email'] ?? ''
        ];
        
        // Step 1: Validate card number format and type
        $cardValidation = self::validateCardNumber($cardData['card_number']);
        if (!$cardValidation['valid']) {
            $errors['card_number'] = $cardValidation['message'];
        } else {
            $cardType = $cardValidation['card_type'];
            $verificationData['card_type'] = $cardType;
            $verificationData['bin'] = substr(preg_replace('/\D/', '', $cardData['card_number']), 0, 6);
        }
        
        // Step 2: Validate cardholder name
        $nameValidation = self::validateCardholderName($cardData['cardholder_name']);
        if (!$nameValidation['valid']) {
            $errors['cardholder_name'] = $nameValidation['message'];
        }
        
        // Step 3: Validate expiry date
        $expiryValidation = self::validateExpiryDate($cardData['expiry_month'], $cardData['expiry_year']);
        if (!$expiryValidation['valid']) {
            $errors['expiry_date'] = $expiryValidation['message'];
        } else {
            $verificationData['expiry_date'] = $cardData['expiry_month'] . '/' . $cardData['expiry_year'];
        }
        
        // Step 4: Validate CVV based on card type
        $cvvValidation = self::validateCVV($cardData['cvv'], $cardType ?? 'unknown');
        if (!$cvvValidation['valid']) {
            $errors['cvv'] = $cvvValidation['message'];
        }
        
        // Step 5: Validate billing address if provided
        if (!empty($cardData['billing_address'])) {
            $addressValidation = self::validateBillingAddress($cardData['billing_address']);
            if (!$addressValidation['valid']) {
                $errors['billing_address'] = $addressValidation['message'];
            }
        }
        
        // Step 6: Validate email if provided
        if (!empty($cardData['email'])) {
            $emailValidation = self::validateEmail($cardData['email']);
            if (!$emailValidation['valid']) {
                $errors['email'] = $emailValidation['message'];
            }
        }
        
        // Step 7: Security checks
        $securityChecks = self::performSecurityChecks($cardData);
        if (!$securityChecks['valid']) {
            $errors['security'] = $securityChecks['message'];
        }
        
        // If validation errors exist, return them
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Credit card validation failed'
            ];
        }
        
        // Step 8: Generate transaction details
        $transactionResult = self::processTransaction($paymentData, $verificationData);
        
        return $transactionResult;
    }
    
    /**
     * Validate and detect card type
     */
    private static function validateCardNumber($cardNumber) {
        // Remove non-digits
        $cleanNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check length
        if (strlen($cleanNumber) < 13 || strlen($cleanNumber) > 19) {
            return ['valid' => false, 'message' => 'Card number must be between 13 and 19 digits'];
        }
        
        // Check Luhn algorithm
        if (!self::isValidLuhn($cleanNumber)) {
            return ['valid' => false, 'message' => 'Invalid card number'];
        }
        
        // Detect card type
        $cardType = 'unknown';
        foreach (self::$cardPatterns as $type => $pattern) {
            if (preg_match($pattern, $cleanNumber)) {
                $cardType = $type;
                break;
            }
        }
        
        // Check for test card numbers (for development)
        if (self::isTestCard($cleanNumber)) {
            return [
                'valid' => true, 
                'card_type' => 'test',
                'message' => 'Test card detected'
            ];
        }
        
        return ['valid' => true, 'card_type' => $cardType];
    }
    
    /**
     * Luhn algorithm validation
     */
    private static function isValidLuhn($number) {
        $sum = 0;
        $reverseDigits = strrev($number);
        
        for ($i = 0; $i < strlen($reverseDigits); $i++) {
            $digit = intval($reverseDigits[$i]);
            
            if ($i % 2 == 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return $sum % 10 == 0;
    }
    
    /**
     * Check if card is a test card
     */
    private static function isTestCard($cardNumber) {
        $testCards = [
            '4111111111111111', // Visa test
            '4242424242424242', // Visa test 2
            '5555555555554444', // MasterCard test
            '378282246310005',  // Amex test
            '6011111111111117', // Discover test
            '30569309025904',   // Diners test
            '3530111333300000'  // JCB test
        ];
        
        return in_array($cardNumber, $testCards);
    }
    
    /**
     * Validate cardholder name
     */
    private static function validateCardholderName($name) {
        $name = trim($name);
        
        // Check length
        if (strlen($name) < 2) {
            return ['valid' => false, 'message' => 'Name must be at least 2 characters'];
        }
        
        if (strlen($name) > 50) {
            return ['valid' => false, 'message' => 'Name cannot exceed 50 characters'];
        }
        
        // Check for valid characters
        if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
            return ['valid' => false, 'message' => 'Name contains invalid characters'];
        }
        
        // Check for minimum two parts (first and last name)
        $nameParts = explode(' ', $name);
        if (count($nameParts) < 2) {
            return ['valid' => false, 'message' => 'Please enter your full name (first and last)'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate expiry date
     */
    private static function validateExpiryDate($month, $year) {
        // Convert two-digit year to four-digit
        if (strlen($year) == 2) {
            $year = '20' . $year;
        }
        
        // Validate month
        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return ['valid' => false, 'message' => 'Invalid month'];
        }
        
        // Validate year
        if (!is_numeric($year) || $year < date('Y') || $year > (date('Y') + 20)) {
            return ['valid' => false, 'message' => 'Invalid year'];
        }
        
        // Check if card is expired
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
            return ['valid' => false, 'message' => 'Card has expired'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate CVV based on card type
     */
    private static function validateCVV($cvv, $cardType = 'unknown') {
        // Remove non-digits
        $cvv = preg_replace('/\D/', '', $cvv);
        
        // Check length
        if (strlen($cvv) < 3 || strlen($cvv) > 4) {
            return ['valid' => false, 'message' => 'CVV must be 3 or 4 digits'];
        }
        
        // Check for only digits
        if (!ctype_digit($cvv)) {
            return ['valid' => false, 'message' => 'CVV must contain only numbers'];
        }
        
        // Card type specific validation
        switch(strtolower($cardType)) {
            case 'amex':
                if (strlen($cvv) != 4) {
                    return ['valid' => false, 'message' => 'American Express requires 4-digit CVV'];
                }
                break;
            case 'visa':
            case 'mastercard':
            case 'discover':
                if (strlen($cvv) != 3) {
                    return ['valid' => false, 'message' => 'This card type requires 3-digit CVV'];
                }
                break;
            default:
                // For unknown cards, accept 3 or 4 digits
                break;
        }
        
        // Check for suspicious patterns
        if (preg_match('/^(\d)\1{2,3}$/', $cvv)) { // All same digits
            return ['valid' => false, 'message' => 'Invalid CVV pattern'];
        }
        
        if (preg_match('/^123$|^234$|^345$|^456$|^567$|^678$|^789$/', $cvv)) {
            return ['valid' => false, 'message' => 'CVV appears to be sequential'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate billing address
     */
    private static function validateBillingAddress($address) {
        $address = trim($address);
        
        // Check length
        if (strlen($address) < 10) {
            return ['valid' => false, 'message' => 'Address is too short'];
        }
        
        if (strlen($address) > 200) {
            return ['valid' => false, 'message' => 'Address is too long'];
        }
        
        // Check for suspicious patterns
        if (preg_match('/test|example|demo|fake/i', $address)) {
            return ['valid' => false, 'message' => 'Address appears to be invalid'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate email address
     */
    private static function validateEmail($email) {
        $email = trim($email);
        
        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        // Check for disposable/temporary email domains
        $disposableDomains = [
            'tempmail.com', 'mailinator.com', 'guerrillamail.com', 
            '10minutemail.com', 'throwawaymail.com', 'fakeinbox.com',
            'yopmail.com', 'trashmail.com', 'temp-mail.org'
        ];
        
        $emailDomain = strtolower(substr($email, strpos($email, '@') + 1));
        if (in_array($emailDomain, $disposableDomains)) {
            return ['valid' => false, 'message' => 'Disposable email addresses are not allowed'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Perform security checks
     */
    private static function performSecurityChecks($cardData) {
        $issues = [];
        
        // Check for test data patterns
        if (isset($cardData['cardholder_name']) && preg_match('/test|demo|fake/i', $cardData['cardholder_name'])) {
            $issues[] = 'Suspicious name pattern';
        }
        
        if (isset($cardData['email']) && preg_match('/test|demo|fake/i', $cardData['email'])) {
            $issues[] = 'Suspicious email pattern';
        }
        
        if (isset($cardData['billing_address']) && preg_match('/test|demo|fake/i', $cardData['billing_address'])) {
            $issues[] = 'Suspicious address pattern';
        }
        
        // Check for common fake CVVs
        $commonFakeCVVs = ['000', '111', '222', '333', '444', '555', '666', '777', '888', '999', '123', '321'];
        if (isset($cardData['cvv']) && in_array($cardData['cvv'], $commonFakeCVVs)) {
            $issues[] = 'Common fake CVV detected';
        }
        
        if (!empty($issues)) {
            return [
                'valid' => false,
                'message' => 'Security check failed: ' . implode(', ', $issues)
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Process transaction (simulated payment gateway)
     */
    private static function processTransaction($paymentData, $verificationData) {
        // Simulate payment gateway processing
        $amount = floatval($paymentData['amount']);
        $cardNumber = preg_replace('/\D/', '', $paymentData['card_number']);
        
        // Generate unique transaction ID
        $transactionId = 'TXN_' . strtoupper(uniqid()) . '_' . time();
        
        // Simulate different scenarios based on test cards
        if (self::isTestCard($cardNumber)) {
            // Test cards always succeed
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'approved',
                'auth_code' => 'AUTH' . rand(100000, 999999),
                'card_type' => $verificationData['card_type'] ?? 'unknown',
                'card_last4' => substr($cardNumber, -4),
                'timestamp' => date('Y-m-d H:i:s'),
                'gateway_response' => [
                    'code' => '00',
                    'message' => 'APPROVED',
                    'gateway' => 'Test Gateway',
                    'avs_result' => 'Y',
                    'cvv_result' => 'M'
                ]
            ];
        }
        
        // Simulate random failures for demo purposes (10% failure rate for non-test cards)
        $random = rand(1, 100);
        
        if ($random <= 10) {
            // Simulate declined transaction
            $declineReasons = [
                'Insufficient funds',
                'Card declined by issuer',
                'Invalid transaction',
                'Daily limit exceeded',
                'Suspected fraud'
            ];
            
            $reason = $declineReasons[array_rand($declineReasons)];
            
            return [
                'success' => false,
                'message' => 'Transaction declined: ' . $reason,
                'transaction_id' => $transactionId,
                'status' => 'declined',
                'gateway_response' => [
                    'code' => '05',
                    'message' => 'DECLINED',
                    'reason' => $reason
                ]
            ];
        }
        
        // Successful transaction
        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'approved',
            'auth_code' => 'AUTH' . rand(100000, 999999),
            'card_type' => $verificationData['card_type'] ?? 'unknown',
            'card_last4' => substr($cardNumber, -4),
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway_response' => [
                'code' => '00',
                'message' => 'APPROVED',
                'gateway' => 'Secure Payment Gateway',
                'avs_result' => 'Y',
                'cvv_result' => 'M'
            ]
        ];
    }
    
    /**
     * Mask sensitive card information
     */
    public static function maskCardData($cardData) {
        $masked = $cardData;
        
        // Mask CVV
        if (isset($masked['cvv'])) {
            $masked['cvv'] = '***';
        }
        
        // Mask card number
        if (isset($masked['card_number'])) {
            $cleanNumber = preg_replace('/\D/', '', $masked['card_number']);
            if (strlen($cleanNumber) > 4) {
                $masked['card_number'] = '**** **** **** ' . substr($cleanNumber, -4);
            }
        }
        
        return $masked;
    }
    
    /**
     * Get card type name for display
     */
    public static function getCardTypeName($cardType) {
        $cardNames = [
            'visa' => 'Visa',
            'mastercard' => 'MasterCard',
            'amex' => 'American Express',
            'discover' => 'Discover',
            'diners' => 'Diners Club',
            'jcb' => 'JCB',
            'unionpay' => 'UnionPay',
            'test' => 'Test Card'
        ];
        
        return $cardNames[$cardType] ?? 'Credit Card';
    }
}
?>