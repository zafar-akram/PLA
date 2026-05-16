<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);
$quiz_id = intval($input['quiz_id'] ?? 0);
$answers = $input['answers'] ?? [];

if (!$quiz_id) {
    jsonResponse(false, 'Invalid quiz ID');
}

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $_SESSION['user_id']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    jsonResponse(false, 'Quiz not found');
}

$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$correct_count = 0;
$total_questions = count($questions);

foreach ($questions as $question) {
    $user_answer = $answers[$question['id']] ?? '';
    $is_correct = strcasecmp(trim($user_answer), trim($question['correct_answer'])) === 0;
    
    if ($is_correct) {
        $correct_count++;
    }
    
    $stmt = $conn->prepare("UPDATE quiz_questions SET user_answer = ?, is_correct = ? WHERE id = ?");
    $stmt->bind_param("sii", $user_answer, $is_correct, $question['id']);
    $stmt->execute();
}

$score = ($correct_count / $total_questions) * 100;

$stmt = $conn->prepare("UPDATE quizzes SET score = ?, completed = 1 WHERE id = ?");
$stmt->bind_param("di", $score, $quiz_id);
$stmt->execute();

$stmt = $conn->prepare("UPDATE user_progress SET quizzes_taken = quizzes_taken + 1, average_score = (average_score * (quizzes_taken - 1) + ?) / quizzes_taken WHERE user_id = ?");
$stmt->bind_param("di", $score, $_SESSION['user_id']);
$stmt->execute();

jsonResponse(true, 'Quiz submitted successfully', ['score' => $score, 'correct' => $correct_count, 'total' => $total_questions]);
?>
