<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/ai.php';

require_login();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$_POST['csrf_token'] = (string) ($payload['csrf_token'] ?? '');
verify_csrf();

$message = trim((string) ($payload['message'] ?? ''));

if ($message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Message is required.']);
    exit;
}

$context = [
    'role' => current_user()['role'] ?? 'student',
    'name' => user_name(),
    'department' => current_user()['department'] ?? '',
    'semester' => current_user()['semester'] ?? '',
];

$systemPrompt = "You are the CUSIT Smart Campus AI assistant. Help users with portal tasks, campus workflows, complaints, events, FYP guidance, and announcements. Keep answers concise, practical, and friendly. If you mention data, frame it as portal guidance rather than inventing private records.";

$requestBody = [
    'system_instruction' => [
        'parts' => [
            ['text' => $systemPrompt],
        ],
    ],
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => "Portal context: " . json_encode($context, JSON_UNESCAPED_SLASHES)],
                ['text' => $message],
            ],
        ],
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 500,
    ],
];

$endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode(GEMINI_MODEL) . ':generateContent?key=' . rawurlencode(GEMINI_API_KEY);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($requestBody, JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode(['error' => 'AI service is temporarily unavailable.']);
    exit;
}

$decoded = json_decode($response, true);
$reply = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
$upstreamError = (string) ($decoded['error']['message'] ?? '');

if ($statusCode >= 400 || $reply === '') {
    http_response_code(502);
    if ($upstreamError !== '') {
        $friendlyError = str_contains($upstreamError, 'quota')
            ? 'Gemini API quota is exhausted or not enabled for this API key. Please enable billing or use a key with active quota.'
            : 'AI service error: ' . $upstreamError;
        echo json_encode(['error' => $friendlyError]);
        exit;
    }

    echo json_encode(['error' => 'AI service returned an unexpected response.']);
    exit;
}

echo json_encode(['reply' => trim((string) $reply)]);
