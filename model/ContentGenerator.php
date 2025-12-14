<?php
// AI Content Generator Model - handles prompts and API calls

include_once __DIR__ . '/../config/AIConfig.php';

class ContentGenerator {
    private $apiKey;
    private $apiEndpoint;
    private $model;
    
    public function __construct() {
        $this->apiKey = AIConfig::getApiKey();
        $this->apiEndpoint = AIConfig::getApiEndpoint();
        $this->model = AIConfig::getModel();
    }
    
    /**
     * Generate content for a course part (module/lesson)
     * @param string $courseTitle
     * @param string $partTitle
     * @param string $description
     * @return array ['success' => bool, 'content' => string, 'error' => string]
     */
    public function generatePartContent($courseTitle, $partTitle, $description = '') {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key not configured. Set OPENAI_API_KEY environment variable or update config/AIConfig.php'
            ];
        }
        
        $prompt = $this->buildPartContentPrompt($courseTitle, $partTitle, $description);
        
        return $this->callOpenAiApi($prompt);
    }
    
    /**
     * Generate full course content
     * @param string $courseTitle
     * @param string $courseDescription
     * @return array
     */
    public function generateCourseContent($courseTitle, $courseDescription) {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key not configured.'
            ];
        }
        
        $prompt = $this->buildCourseContentPrompt($courseTitle, $courseDescription);
        
        return $this->callOpenAiApi($prompt);
    }
    
    /**
     * Build prompt for a course part
     */
    private function buildPartContentPrompt($courseTitle, $partTitle, $description = '') {
        $desc_note = $description ? " Description: $description" : '';
        
        return <<<PROMPT
You are an expert course content writer. Generate a SHORT, educational HTML content snippet for a course part.

Course: $courseTitle
Part/Lesson: $partTitle$desc_note

Requirements:
- Write 150-250 words only
- Use HTML (h3, p, ul, li, pre tags)
- Include learning objectives as bullet points
- Include one practical example or exercise
- Keep it concise and focused
- NO external links

Generate the HTML content now:
PROMPT;
    }
    
    /**
     * Build prompt for full course
     */
    private function buildCourseContentPrompt($courseTitle, $courseDescription) {
        return <<<PROMPT
You are an expert course content writer. Generate a SHORT overview and main sections for a course.

Course: $courseTitle
Description: $courseDescription

Requirements:
- Generate 200-300 words only
- Use HTML (h2, h3, p, ul, li tags)
- Include overview section
- Include 3-4 main topics/modules
- Keep it educational and practical
- NO external links

Generate the HTML content now:
PROMPT;
    }
    
    /**
     * Call OpenAI API via curl
     */
    private function callOpenAiApi($prompt) {
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful course content creator. Always respond with clean, valid HTML.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => AIConfig::getTemperature(),
            'max_tokens' => AIConfig::getMaxTokens()
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return [
                'success' => false,
                'error' => 'Network error: ' . $curlError
            ];
        }
        
        if ($httpCode !== 200) {
            $decoded = json_decode($response, true);
            $errorMsg = $decoded['error']['message'] ?? 'Unknown error';
            return [
                'success' => false,
                'error' => "API Error (HTTP $httpCode): $errorMsg"
            ];
        }
        
        $decoded = json_decode($response, true);
        if (!isset($decoded['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Unexpected API response format'
            ];
        }
        
        $content = trim($decoded['choices'][0]['message']['content']);
        
        return [
            'success' => true,
            'content' => $content
        ];
    }
}
?>
