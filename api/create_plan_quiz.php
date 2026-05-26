<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
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
$scope = sanitize($input['scope'] ?? 'session');
$difficulty = sanitize($input['difficulty'] ?? 'medium');
$total_questions = intval($input['total_questions'] ?? 10);

if (!$plan_id) {
    jsonResponse(false, 'Invalid study plan');
}

if (!in_array($scope, ['session', 'day', 'subject'], true)) {
    $scope = 'session';
}

if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
    $difficulty = 'medium';
}

if (!in_array($total_questions, [5, 10, 15, 20], true)) {
    $total_questions = 10;
}

$stmt = $conn->prepare("SELECT * FROM study_plans WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $plan_id, $_SESSION['user_id']);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    jsonResponse(false, 'Study plan not found');
}

$plans = [$plan];
$quiz_title = 'Quiz - ' . $plan['title'];
$quiz_subject = $plan['subject'];

if ($scope === 'day') {
    $stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND start_date = ? ORDER BY start_time ASC");
    $stmt->bind_param("is", $_SESSION['user_id'], $plan['start_date']);
    $stmt->execute();
    $plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $quiz_title = 'Daily Quiz - ' . date('M d, Y', strtotime($plan['start_date']));
    $quiz_subject = 'Daily Study Plan';
} elseif ($scope === 'subject') {
    $stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND subject = ? ORDER BY start_date ASC, start_time ASC");
    $stmt->bind_param("is", $_SESSION['user_id'], $plan['subject']);
    $stmt->execute();
    $plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $quiz_title = 'Full Subject Quiz - ' . $plan['subject'];
}

$outline_parts = [];
foreach ($plans as $item) {
    $outline_parts[] = "Session: {$item['title']}\nSubject: {$item['subject']}\nGoal/Outline: {$item['goal']}\nDate: {$item['start_date']}";
}

$outline = implode("\n\n", $outline_parts);
$special_features = 'Generate questions only from the selected study plan scope. Focus on the session goals, planned topics, and practical understanding.';

$stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, subject, difficulty, total_questions) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isssi", $_SESSION['user_id'], $quiz_title, $quiz_subject, $difficulty, $total_questions);

if (!$stmt->execute()) {
    jsonResponse(false, 'Failed to create quiz');
}

$quiz_id = $conn->insert_id;
$questions = generateQuizQuestions($quiz_subject, $difficulty, $total_questions, $outline, '', $special_features);

foreach ($questions as $q) {
    $question = $q['question'];
    $options = json_encode($q['options']);
    $correct_answer = $q['options'][$q['correct_answer']];

    $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, question_type, options, correct_answer) VALUES (?, ?, 'mcq', ?, ?)");
    $stmt->bind_param("isss", $quiz_id, $question, $options, $correct_answer);
    $stmt->execute();
}

jsonResponse(true, 'Quiz created from study plan', [
    'quiz_id' => $quiz_id,
    'url' => '../dashboard/take_quiz.php?id=' . $quiz_id
]);
?>
