<?php

define('GEMINI_API_KEY', 'AIzaSyAqt_qqCLcIM9SExfWEnSV7lfOhBxQ5x6g');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1/models/');
define('GROQ_API_KEY', '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');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

define('MAX_INPUT_TOKENS', 12000);
define('MAX_OUTPUT_TOKENS', 16000);

$MODEL_CONFIG = [
    'gemini-2.5-flash' => ['provider' => 'gemini', 'model' => 'gemini-2.5-flash'],
    'gemini-2.0-flash' => ['provider' => 'gemini', 'model' => 'gemini-2.0-flash'],
    'gemini-1.5-flash' => ['provider' => 'gemini', 'model' => 'gemini-1.5-flash'],
    'llama-3.3-70b-versatile' => ['provider' => 'groq', 'model' => 'llama-3.3-70b-versatile'],
    'llama-3.1-8b-instant' => ['provider' => 'groq', 'model' => 'llama-3.1-8b-instant'],
    'mixtral-8x7b-32768' => ['provider' => 'groq', 'model' => 'mixtral-8x7b-32768'],
    'gemma2-9b-it' => ['provider' => 'groq', 'model' => 'gemma2-9b-it']
];

$AI_APIS = [
    [
        'name' => 'Gemini',
        'provider' => 'gemini',
        'model' => 'gemini-2.5-flash',
        'active' => true
    ],
    [
        'name' => 'Groq',
        'provider' => 'groq',
        'model' => 'llama-3.3-70b-versatile',
        'active' => true
    ]
];

function callAI($prompt, $context = '', $modelId = 'gemini-2.5-flash') {
    global $MODEL_CONFIG;
    
    $fullPrompt = $context ? $context . "\n\n" . $prompt : $prompt;
    
    if (!isset($MODEL_CONFIG[$modelId])) {
        error_log("Unknown model: $modelId, falling back to gemini-2.5-flash");
        $modelId = 'gemini-2.5-flash';
    }
    
    $config = $MODEL_CONFIG[$modelId];
    $provider = $config['provider'];
    $model = $config['model'];
    
    $result = null;
    
    switch ($provider) {
        case 'gemini':
            $result = callGeminiAPI($fullPrompt, $model);
            if ($result === null) {
                $fallbacks = ['gemini-2.0-flash', 'gemini-1.5-flash'];
                foreach ($fallbacks as $fbModel) {
                    if ($fbModel !== $model) {
                        $result = callGeminiAPI($fullPrompt, $fbModel);
                        if ($result !== null) break;
                    }
                }
                if ($result === null) {
                    $result = callGroqAPI($fullPrompt, 'llama-3.3-70b-versatile');
                }
            }
            break;
            
        case 'groq':
            $result = callGroqAPI($fullPrompt, $model);
            if ($result === null) {
                $result = callGroqAPI($fullPrompt, 'llama-3.3-70b-versatile');
                if ($result === null) {
                    $result = callGeminiAPI($fullPrompt, 'gemini-2.5-flash');
                }
            }
            break;
    }
    
    return $result ?: "I apologize, but I'm currently unable to process your request. Please try again later.";
}

function callGeminiAPI($prompt, $model = 'gemini-2.5-flash') {
    if (empty(GEMINI_API_KEY)) {
        return null;
    }
    
    $maxChars = MAX_INPUT_TOKENS * 4;
    if (strlen($prompt) > $maxChars) {
        $prompt = substr($prompt, 0, $maxChars) . "\n\n[Content truncated due to length...]";
    }
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 8000,
            'topP' => 0.95,
            'topK' => 40
        ]
    ];
    
    $url = GEMINI_API_URL . $model . ':generateContent?key=' . GEMINI_API_KEY;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $decoded = json_decode($response, true);
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return $decoded['candidates'][0]['content']['parts'][0]['text'];
        }
    }
    
    return null;
}

function callGroqAPI($prompt, $model = 'llama-3.3-70b-versatile') {
    if (empty(GROQ_API_KEY)) {
        return null;
    }
    
    $maxChars = MAX_INPUT_TOKENS * 4;
    if (strlen($prompt) > $maxChars) {
        $prompt = substr($prompt, 0, $maxChars) . "\n\n[Content truncated due to length...]";
    }
    
    $data = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI learning assistant. Provide clear, educational, and encouraging responses to help students learn effectively.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => MAX_OUTPUT_TOKENS,
        'top_p' => 0.95
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GROQ_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $decoded = json_decode($response, true);
        if (isset($decoded['choices'][0]['message']['content'])) {
            return $decoded['choices'][0]['message']['content'];
        }
    }
    
    return null;
}

function generateQuizQuestions($subject, $difficulty, $count = 10) {
    $prompt = "Generate exactly {$count} {$difficulty} level multiple choice questions about {$subject}.

For each question, provide:
1. A clear question text
2. Exactly 4 answer options (labeled A, B, C, D)
3. The correct answer (specify which option letter is correct)

Format your response as a JSON array. Each question should be an object with this structure:
{
    \"question\": \"Question text here?\",
    \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],
    \"correct_answer\": 0
}

Where correct_answer is the index (0-3) of the correct option.

Return ONLY the JSON array, no additional text or explanation.";
    
    $response = callAI($prompt, '', 'gemini-2.5-flash');
    
    $response = trim($response);
    $response = preg_replace('/^```json\s*/i', '', $response);
    $response = preg_replace('/\s*```$/i', '', $response);
    $response = trim($response);
    
    try {
        $questions = json_decode($response, true);
        if (is_array($questions) && count($questions) > 0) {
            $validQuestions = [];
            foreach ($questions as $q) {
                if (isset($q['question']) && isset($q['options']) && isset($q['correct_answer'])) {
                    if (is_array($q['options']) && count($q['options']) === 4) {
                        $validQuestions[] = $q;
                    }
                }
            }
            if (count($validQuestions) > 0) {
                return array_slice($validQuestions, 0, $count);
            }
        }
    } catch (Exception $e) {
        error_log("Quiz generation error: " . $e->getMessage());
    }
    
    return generateFallbackQuestions($subject, $difficulty, $count);
}

function generateFallbackQuestions($subject, $difficulty, $count) {
    $questions = [];
    $templates = [
        "What is the primary concept of {topic}?",
        "Which statement best describes {topic}?",
        "How does {topic} work?",
        "What is the main purpose of {topic}?"
    ];
    
    for ($i = 0; $i < $count; $i++) {
        $questions[] = [
            'question' => str_replace('{topic}', $subject, $templates[$i % count($templates)]),
            'options' => [
                "Option A related to " . $subject,
                "Option B related to " . $subject,
                "Option C related to " . $subject,
                "Option D related to " . $subject
            ],
            'correct_answer' => rand(0, 3)
        ];
    }
    
    return $questions;
}
?>
