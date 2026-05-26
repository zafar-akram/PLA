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

if (!$plan_id) {
    jsonResponse(false, 'Invalid study plan');
}

$stmt = $conn->prepare("DELETE FROM study_plans WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $plan_id, $_SESSION['user_id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    jsonResponse(true, 'Study plan deleted successfully');
}

jsonResponse(false, 'Study plan not found or already deleted');
?>
