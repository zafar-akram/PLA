<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);
$message = sanitize($input['message'] ?? '');

if (empty($message)) {
    jsonResponse(false, 'Message is required');
}

$context = "You are an AI learning assistant. Provide clear, educational responses to help students learn. Be encouraging and supportive.";
$answer = callAI($message, $context);

$stmt = $conn->prepare("INSERT INTO chat_history (user_id, question, answer) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $_SESSION['user_id'], $message, $answer);
$stmt->execute();

jsonResponse(true, 'Response generated', ['answer' => $answer]);
?>
