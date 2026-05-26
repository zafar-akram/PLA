<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$title = sanitize($_POST['title'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$university = sanitize($_POST['university'] ?? '');
$course = sanitize($_POST['course'] ?? '');
$semester = sanitize($_POST['semester'] ?? '');
$outline = sanitize($_POST['outline'] ?? '');
$special_features = sanitize($_POST['special_features'] ?? '');
$difficulty = sanitize($_POST['difficulty'] ?? 'medium');
$total_questions = intval($_POST['total_questions'] ?? 10);

if (empty($title) || empty($subject)) {
    jsonResponse(false, 'Quiz title and subject are required');
}

$stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, subject, difficulty, total_questions) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isssi", $_SESSION['user_id'], $title, $subject, $difficulty, $total_questions);

if ($stmt->execute()) {
    $quiz_id = $conn->insert_id;

    $academic_context = $university;
    if ($course !== '') {
        $academic_context .= ' - ' . $course;
    }
    if ($semester !== '') {
        $academic_context .= ' - Semester ' . $semester;
    }

    $questions = generateQuizQuestions($subject, $difficulty, $total_questions, $outline, $academic_context, $special_features);
    
    foreach ($questions as $q) {
        $question = $q['question'];
        $options = json_encode($q['options']);
        $correct_answer = $q['options'][$q['correct_answer']];
        
        $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, question_type, options, correct_answer) VALUES (?, ?, 'mcq', ?, ?)");
        $stmt->bind_param("isss", $quiz_id, $question, $options, $correct_answer);
        $stmt->execute();
    }
    
    jsonResponse(true, 'Quiz created successfully', ['quiz_id' => $quiz_id]);
} else {
    jsonResponse(false, 'Failed to create quiz');
}
?>
