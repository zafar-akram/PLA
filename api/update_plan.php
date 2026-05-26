<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$plan_id = intval($_POST['plan_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$start_date = sanitize($_POST['start_date'] ?? '');
$end_date = sanitize($_POST['end_date'] ?? '');
$start_time = sanitize($_POST['start_time'] ?? '');
$end_time = sanitize($_POST['end_time'] ?? '');
$goal = sanitize($_POST['goal'] ?? '');
$status = sanitize($_POST['status'] ?? 'pending');
$allowed_statuses = ['pending', 'in_progress', 'completed'];

if (!$plan_id || empty($title) || empty($subject) || empty($start_date) || empty($end_date) || empty($start_time) || empty($end_time)) {
    jsonResponse(false, 'All required fields must be filled');
}

if (!in_array($status, $allowed_statuses, true)) {
    jsonResponse(false, 'Invalid status');
}

$stmt = $conn->prepare("UPDATE study_plans SET title = ?, subject = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ?, goal = ?, status = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssssssssii", $title, $subject, $start_date, $end_date, $start_time, $end_time, $goal, $status, $plan_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    jsonResponse(true, 'Study plan updated successfully');
}

jsonResponse(false, 'Failed to update study plan');
?>
