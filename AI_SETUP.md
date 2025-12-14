# AI Content Generator Setup

## Overview
This feature uses OpenAI's GPT API to automatically generate short, educational content snippets for your courses.

## Files Added

1. **`config/AIConfig.php`** — Configuration for AI API settings (model, temperature, max tokens, etc.)
2. **`model/ContentGenerator.php`** — Handles prompt generation and API calls to OpenAI
3. **`api/generate-content.php`** — REST endpoint to trigger content generation
4. **`admin-generate-content.php`** — Admin page to generate content for specific courses

## Setup Instructions

### 1. Get an OpenAI API Key
- Go to https://platform.openai.com/account/api-keys
- Create a new API key
- Copy it (starts with `sk-`)

### 2. Set the API Key
Choose one of two methods:

**Option A: Environment Variable (Recommended)**
```bash
# On Windows (PowerShell), set environment variable
$env:OPENAI_API_KEY = "sk-your-key-here"
```

**Option B: Edit config/AIConfig.php**
```php
private static $apiKey = 'sk-your-key-here';
```

### 3. Use the Content Generator

**Via Admin Page:**
1. Open `http://localhost/courses/admin-generate-content.php`
2. Select a course
3. Click "Generate Content"
4. The AI will create content and save it to the course_contents table

**Via API (programmatic):**
```bash
curl -X POST http://localhost/courses/api/generate-content.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "generate-full-course",
    "course_id": 1,
    "part_title": "Introduction",
    "part_description": "Basic intro"
  }'
```

## API Endpoints

### `POST /api/generate-content.php`

**Parameters:**
- `action` (string): `generate-part-content` or `generate-full-course`
- `course_id` (int): ID of the course
- `part_title` (string): Title of the part/lesson
- `part_description` (optional): Description for context

**Response:**
```json
{
  "success": true,
  "content": "<h2>Generated HTML content...</h2>",
  "message": "Content generated successfully"
}
```

## Configuration

Edit `config/AIConfig.php` to adjust:
- `$model` — Change to `gpt-4`, `gpt-3.5-turbo`, etc.
- `$temperature` — 0-2 (lower = more deterministic)
- `$maxTokens` — 500 (default; increase for longer content)

## Costs

Using OpenAI API incurs charges per token used. Check your OpenAI account for pricing and usage.

## Troubleshooting

**"API key not configured"**
- Set the `OPENAI_API_KEY` environment variable or edit `config/AIConfig.php`

**"API Error (HTTP 401)"**
- Your API key is invalid or expired
- Regenerate it at https://platform.openai.com/account/api-keys

**"Network error"**
- Check your internet connection and firewall rules
- Ensure cURL is enabled in PHP

## Example Usage

Generate content for all courses:
```php
$generator = new ContentGenerator();
$courses = $db->prepare('SELECT id, title, description FROM courses')->fetchAll();

foreach ($courses as $course) {
    $result = $generator->generateCourseContent($course['title'], $course['description']);
    if ($result['success']) {
        // Save $result['content'] to course_contents table
    }
}
```
