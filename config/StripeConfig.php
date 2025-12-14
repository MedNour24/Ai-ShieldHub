<?php
// config/StripeConfig.php

class StripeConfig {
    // Test keys - replace with your actual keys from Stripe Dashboard
    private static $publishableKey = 'pk_test_your_publishable_key_here';
    private static $secretKey = 'sk_test_your_secret_key_here';
    private static $webhookSecret = 'whsec_your_webhook_secret_here';
    
    public static function getPublishableKey() {
        return self::$publishableKey;
    }
    
    public static function getSecretKey() {
        return self::$secretKey;
    }
    
    public static function getWebhookSecret() {
        return self::$webhookSecret;
    }
    
    // Currency settings
    public static function getCurrency() {
        return 'usd';
    }
    
    // Minimum charge amount (in cents)
    public static function getMinimumCharge() {
        return 50; // $0.50
    }
    
    // Supported payment methods
    public static function getPaymentMethods() {
        return [
            'card', // Credit/debit cards
            // 'alipay', // Alipay
            // 'wechat_pay', // WeChat Pay
            // 'paypal', // PayPal
        ];
    }
    
    // Webhook events to handle
    public static function getWebhookEvents() {
        return [
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'charge.succeeded',
            'charge.failed',
            'charge.refunded',
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];
    }
}
?>