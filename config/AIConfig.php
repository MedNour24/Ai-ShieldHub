<?php
// AI Configuration - OpenAI or compatible API

class AIConfig {
    // API provider: 'openai' or 'custom'
    private static $provider = 'openai';
    
    // OpenAI API Key (set via environment variable or directly)
    private static $apiKey = '';
    
    // API endpoint
    private static $apiEndpoint = 'https://api.openai.com/v1/chat/completions';
    
    // Model to use
    private static $model = 'gpt-3.5-turbo';
    
    // Temperature (0-2, lower = more deterministic)
    private static $temperature = 0.7;
    
    // Max tokens per response
    private static $maxTokens = 500;
    
    public static function getApiKey() {
        // Try environment variable first
        $key = getenv('OPENAI_API_KEY');
        if ($key) return $key;
        
        // Fall back to config value
        return self::$apiKey;
    }
    
    public static function setApiKey($key) {
        self::$apiKey = $key;
    }
    
    public static function getProvider() {
        return self::$provider;
    }
    
    public static function getApiEndpoint() {
        return self::$apiEndpoint;
    }
    
    public static function getModel() {
        return self::$model;
    }
    
    public static function getTemperature() {
        return self::$temperature;
    }
    
    public static function getMaxTokens() {
        return self::$maxTokens;
    }
}
?>
