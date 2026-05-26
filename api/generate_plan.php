<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$university = sanitize($_POST['university'] ?? '');
$course = sanitize($_POST['course'] ?? '');
$semester = sanitize($_POST['semester'] ?? '');
$outline = sanitize($_POST['outline'] ?? '');
$subjects = sanitize($_POST['subjects'] ?? '');
$special_features = sanitize($_POST['special_features'] ?? '');
$subject_outlines_json = $_POST['subject_outlines'] ?? '';
$start_date = sanitize($_POST['start_date'] ?? '');
$end_date = sanitize($_POST['end_date'] ?? '');
$start_time = sanitize($_POST['start_time'] ?? '09:00');
$end_time = sanitize($_POST['end_time'] ?? '11:00');
$duration_hours = floatval($_POST['duration_hours'] ?? 1);
$off_days_raw = $_POST['off_days'] ?? [];

if (!is_array($off_days_raw)) {
    $off_days_raw = [$off_days_raw];
}

$allowed_days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
$off_days = [];
foreach ($off_days_raw as $day) {
    $day = strtolower(sanitize($day));
    if (in_array($day, $allowed_days, true)) {
        $off_days[] = $day;
    }
}

if (empty($outline) || empty($subjects) || empty($start_date) || empty($end_date) || empty($start_time) || empty($end_time)) {
    $decoded_subjects = json_decode($subject_outlines_json, true);
    if (!is_array($decoded_subjects) || count($decoded_subjects) === 0) {
        jsonResponse(false, 'Select at least one subject with its outline, dates, and time window');
    }
}

if (!strtotime($start_date) || !strtotime($end_date) || strtotime($start_date) > strtotime($end_date)) {
    jsonResponse(false, 'Please select a valid date range');
}

if ($duration_hours <= 0 || $duration_hours > 8) {
    jsonResponse(false, 'Session duration must be between 0.5 and 8 hours');
}

$subject_outlines = json_decode($subject_outlines_json, true);
if (!is_array($subject_outlines)) {
    $subject_outlines = [];
}

$clean_subject_outlines = [];
foreach ($subject_outlines as $item) {
    $subject = sanitize($item['subject'] ?? '');
    $subject_outline = sanitize($item['outline'] ?? '');
    if ($subject !== '') {
        $clean_subject_outlines[] = [
            'subject' => $subject,
            'outline' => $subject_outline
        ];
    }
}

if (count($clean_subject_outlines) > 0) {
    $subjects = implode(', ', array_column($clean_subject_outlines, 'subject'));
    $outline = '';
}

$academic_context = $university;
if ($course !== '') {
    $academic_context .= ' - ' . $course;
}
if ($semester !== '') {
    $academic_context .= ' - Semester ' . $semester;
}

$sessions = generateStudyPlanSessions(
    $academic_context,
    $outline,
    $subjects,
    $special_features,
    $start_date,
    $end_date,
    $start_time,
    $end_time,
    array_values(array_unique($off_days)),
    $duration_hours,
    $clean_subject_outlines
);

$inserted = 0;
$range_start = strtotime($start_date);
$range_end = strtotime($end_date);
$window_start = strtotime($start_time);
$window_end = strtotime($end_time);

foreach ($sessions as $session) {
    $title = sanitize($session['title'] ?? '');
    $subject = sanitize($session['subject'] ?? '');
    $session_date = sanitize($session['date'] ?? '');
    $session_start = sanitize($session['start_time'] ?? $start_time);
    $session_end = sanitize($session['end_time'] ?? $end_time);
    $goal = sanitize($session['goal'] ?? '');

    $session_ts = strtotime($session_date);
    if (!$title || !$subject || !$session_ts || $session_ts < $range_start || $session_ts > $range_end) {
        continue;
    }

    $day_name = strtolower(date('l', $session_ts));
    if (in_array($day_name, $off_days, true)) {
        continue;
    }

    $session_start_ts = strtotime($session_start);
    $session_end_ts = strtotime($session_end);
    if (!$session_start_ts || !$session_end_ts || $session_start_ts < $window_start || $session_end_ts > $window_end || $session_start_ts >= $session_end_ts) {
        $session_start = $start_time;
        $duration_minutes = max(30, (int) round($duration_hours * 60));
        $date_time = DateTime::createFromFormat('H:i', $start_time);
        $date_time->modify("+{$duration_minutes} minutes");
        $session_end = $date_time->format('H:i');
    }

    $stmt = $conn->prepare("INSERT INTO study_plans (user_id, title, subject, start_date, end_date, start_time, end_time, goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $_SESSION['user_id'], $title, $subject, $session_date, $session_date, $session_start, $session_end, $goal);
    if ($stmt->execute()) {
        $inserted++;
    }
}

if ($inserted > 0) {
    jsonResponse(true, "Generated {$inserted} study sessions", ['created' => $inserted]);
}

jsonResponse(false, 'AI could not create valid sessions. Try a wider date range or fewer off days.');
?>
