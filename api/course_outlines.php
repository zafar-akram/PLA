<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

header('Content-Type: application/json');

$program = strtolower(sanitize($_GET['program'] ?? 'bscs'));

if ($program === 'bscs') {
    jsonResponse(true, 'Course outlines loaded', ['courses' => getCourseOutlinesForUser($_SESSION['user_id'])]);
}

jsonResponse(true, 'No saved outlines for this program', ['courses' => []]);
?>
