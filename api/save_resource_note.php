<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$title = sanitize($input['title'] ?? '');
$subject = sanitize($input['subject'] ?? '');
$content = $input['content'] ?? '';
$tags = sanitize($input['tags'] ?? 'AI Resource');

$content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
$content = strip_tags($content, '<h5><h6><p><ul><ol><li><strong><em><code><pre><br><hr><table><thead><tbody><tr><th><td>');
$content = trim($content);

if ($title === '' || $subject === '' || $content === '') {
    jsonResponse(false, 'Title, subject, and generated content are required');
}

$stmt = $conn->prepare("INSERT INTO study_notes (user_id, title, subject, content, tags) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $_SESSION['user_id'], $title, $subject, $content, $tags);

if ($stmt->execute()) {
    jsonResponse(true, 'Resource saved as note', ['note_id' => $conn->insert_id]);
}

jsonResponse(false, 'Failed to save note');
?>
