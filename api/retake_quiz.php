<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
require_once '../config/course_outlines.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$quiz_id = intval($input['quiz_id'] ?? 0);
if (!$quiz_id) {
    jsonResponse(false, 'Invalid quiz');
}

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $_SESSION['user_id']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    jsonResponse(false, 'Quiz not found');
}

$course = findBscsCourseOutlineBySubject($quiz['subject']);
$outline = $course ? $course['outline'] : '';
$academic_context = $course ? "{$course['university']} - {$course['course']} - Semester {$course['semester']}" : 'Custom';
$title = 'Retake - ' . preg_replace('/^Retake\s*-\s*/i', '', $quiz['title']);
$special_features = 'Create a fresh retake quiz with new questions. Do not repeat the exact wording from the previous attempt.';

$stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, subject, difficulty, total_questions) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isssi", $_SESSION['user_id'], $title, $quiz['subject'], $quiz['difficulty'], $quiz['total_questions']);

if (!$stmt->execute()) {
    jsonResponse(false, 'Failed to create retake quiz');
}

$new_quiz_id = $conn->insert_id;
$questions = generateQuizQuestions($quiz['subject'], $quiz['difficulty'], intval($quiz['total_questions']), $outline, $academic_context, $special_features);

foreach ($questions as $q) {
    $question = $q['question'];
    $options = json_encode($q['options']);
    $correct_answer = $q['options'][$q['correct_answer']];

    $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, question_type, options, correct_answer) VALUES (?, ?, 'mcq', ?, ?)");
    $stmt->bind_param("isss", $new_quiz_id, $question, $options, $correct_answer);
    $stmt->execute();
}

jsonResponse(true, 'Retake quiz created', ['quiz_id' => $new_quiz_id]);
?>
