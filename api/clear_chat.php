<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$stmt = $conn->prepare("DELETE FROM chat_history WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);

if ($stmt->execute()) {
    jsonResponse(true, 'Chat history cleared');
} else {
    jsonResponse(false, 'Failed to clear chat history');
}
?>
