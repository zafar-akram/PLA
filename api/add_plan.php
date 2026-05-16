<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$title = sanitize($_POST['title'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$start_date = sanitize($_POST['start_date'] ?? '');
$end_date = sanitize($_POST['end_date'] ?? '');
$start_time = sanitize($_POST['start_time'] ?? '');
$end_time = sanitize($_POST['end_time'] ?? '');
$goal = sanitize($_POST['goal'] ?? '');

if (empty($title) || empty($subject) || empty($start_date) || empty($end_date) || empty($start_time) || empty($end_time)) {
    jsonResponse(false, 'All fields are required');
}

$stmt = $conn->prepare("INSERT INTO study_plans (user_id, title, subject, start_date, end_date, start_time, end_time, goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssss", $_SESSION['user_id'], $title, $subject, $start_date, $end_date, $start_time, $end_time, $goal);

if ($stmt->execute()) {
    jsonResponse(true, 'Study plan added successfully');
} else {
    jsonResponse(false, 'Failed to add study plan');
}
?>
