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

$university = sanitize($input['university'] ?? 'GCUF');
$course = sanitize($input['course'] ?? 'BSCS');
$semester = sanitize($input['semester'] ?? '');
$subject = sanitize($input['subject'] ?? '');
$outline = sanitize($input['outline'] ?? '');
$resource_type = sanitize($input['resource_type'] ?? 'study_guide');
$level = sanitize($input['level'] ?? 'intermediate');
$goal = sanitize($input['goal'] ?? '');

if ($subject === '' || $outline === '') {
    jsonResponse(false, 'Subject and outline are required');
}

$resource_labels = [
    'study_guide' => 'study guide',
    'video_plan' => 'video learning roadmap',
    'practice' => 'practice set',
    'notes' => 'quick notes and summaries',
    'web_resources' => 'recommended web resources',
    'exam_revision' => 'exam revision pack'
];

$resource_label = $resource_labels[$resource_type] ?? 'study guide';

$prompt = "Generate an AI learning resource for this student context:
University: {$university}
Course: {$course}
Semester: {$semester}
Subject: {$subject}
Level: {$level}
Student goal: " . ($goal ?: 'Prepare and understand the topic well') . "
Course outline:
{$outline}

Create a {$resource_label}.

Return the answer in clean HTML only, using these sections where useful:
<h5>Overview</h5>
<h5>Key Topics</h5>
<h5>Best Learning Path</h5>
<h5>Practice Tasks</h5>
<h5>Recommended Resources</h5>
<h5>Exam Tips</h5>

Rules:
- Keep it specific to the provided outline.
- For Recommended Resources, suggest search queries, book names, documentation topics, and trusted platforms. Do not invent fake URLs.
- Use <ul>, <ol>, <p>, <strong>, and <code> where helpful.
- Do not include markdown fences or a full HTML document.";

$context = 'You are an academic resource curator for a final year project AI learning assistant. Produce practical, student-ready learning resources.';
$answer = callAI($prompt, $context, 'gemini-2.5-flash');
$answer = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $answer);
$answer = preg_replace('/<\/?(html|head|body|meta|link|style)[^>]*>/i', '', $answer);
$answer = strip_tags($answer, '<h5><h6><p><ul><ol><li><strong><em><code><pre><br><hr><table><thead><tbody><tr><th><td>');

jsonResponse(true, 'Resources generated', ['html' => $answer]);
?>
