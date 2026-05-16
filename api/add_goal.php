<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$goal_title = sanitize($_POST['goal_title'] ?? '');
$target_date = sanitize($_POST['target_date'] ?? '');

if (empty($goal_title) || empty($target_date)) {
    jsonResponse(false, 'All fields are required');
}

$stmt = $conn->prepare("INSERT INTO study_goals (user_id, goal_title, target_date) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $_SESSION['user_id'], $goal_title, $target_date);

if ($stmt->execute()) {
    jsonResponse(true, 'Goal added successfully');
} else {
    jsonResponse(false, 'Failed to add goal');
}
?>
