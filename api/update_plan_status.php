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

$plan_id = intval($input['plan_id'] ?? 0);
$status = sanitize($input['status'] ?? '');
$allowed_statuses = ['pending', 'in_progress', 'completed'];

if (!$plan_id || !in_array($status, $allowed_statuses, true)) {
    jsonResponse(false, 'Invalid plan or status');
}

$stmt = $conn->prepare("UPDATE study_plans SET status = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $status, $plan_id, $_SESSION['user_id']);

if ($stmt->execute() && $stmt->affected_rows >= 0) {
    jsonResponse(true, 'Study plan updated');
}

jsonResponse(false, 'Failed to update study plan');
?>
