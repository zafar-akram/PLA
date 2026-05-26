<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();
$user_id = $_SESSION['user_id'];

function fetchAllRows($sql, $types = '', ...$params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function fetchOneRow($sql, $types = '', ...$params) {
    $rows = fetchAllRows($sql, $types, ...$params);
    return $rows[0] ?? [];
}

$progress = fetchOneRow("SELECT * FROM user_progress WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1", 'i', $user_id);
if (!$progress) {
    $stmt = $conn->prepare("INSERT INTO user_progress (user_id, subject) VALUES (?, 'General')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $progress = ['study_hours' => 0, 'average_score' => 0, 'streak_days' => 0, 'quizzes_taken' => 0];
}

$all_plans = fetchAllRows("SELECT * FROM study_plans WHERE user_id = ? ORDER BY start_date ASC, start_time ASC", 'i', $user_id);
$completed_quizzes = fetchAllRows("SELECT * FROM quizzes WHERE user_id = ? AND completed = 1 ORDER BY created_at ASC", 'i', $user_id);
$all_quizzes = fetchAllRows("SELECT * FROM quizzes WHERE user_id = ? ORDER BY created_at DESC", 'i', $user_id);
$notifications = fetchAllRows("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5", 'i', $user_id);
$recent_activities = fetchAllRows("SELECT * FROM study_plans WHERE user_id = ? ORDER BY start_date DESC, start_time DESC LIMIT 6", 'i', $user_id);

$goal_counts = fetchOneRow("SELECT SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count, COUNT(*) AS total_count FROM study_goals WHERE user_id = ?", 'i', $user_id);
$assignment_counts = fetchOneRow("SELECT COUNT(*) AS total_count, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count, SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count, SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) AS high_priority_count FROM assignments WHERE user_id = ?", 'i', $user_id);
$note_counts = fetchOneRow("SELECT COUNT(*) as total FROM study_notes WHERE user_id = ?", 'i', $user_id);
$chat_counts = fetchOneRow("SELECT COUNT(*) as total FROM chat_history WHERE user_id = ?", 'i', $user_id);

$quiz_summary = fetchOneRow("SELECT COUNT(*) as total, AVG(score) as avg_score, MAX(score) as best_score FROM quizzes WHERE user_id = ? AND completed = 1", 'i', $user_id);
$subject_stats = fetchAllRows("SELECT subject, COUNT(*) as attempts, AVG(score) as avg_score, MAX(score) as best_score FROM quizzes WHERE user_id = ? AND completed = 1 GROUP BY subject ORDER BY avg_score DESC", 'i', $user_id);
$difficulty_stats = fetchAllRows("SELECT difficulty, COUNT(*) as total FROM quizzes WHERE user_id = ? GROUP BY difficulty", 'i', $user_id);
$plan_status_stats = fetchAllRows("SELECT status, COUNT(*) as total FROM study_plans WHERE user_id = ? GROUP BY status", 'i', $user_id);
$plan_subject_stats = fetchAllRows("SELECT subject, COUNT(*) as total FROM study_plans WHERE user_id = ? GROUP BY subject ORDER BY total DESC LIMIT 8", 'i', $user_id);
$assignment_status_stats = fetchAllRows("SELECT status, COUNT(*) as total FROM assignments WHERE user_id = ? GROUP BY status", 'i', $user_id);
$assignment_priority_stats = fetchAllRows("SELECT priority, COUNT(*) as total FROM assignments WHERE user_id = ? GROUP BY priority", 'i', $user_id);
$notes_subject_stats = fetchAllRows("SELECT subject, COUNT(*) as total FROM study_notes WHERE user_id = ? GROUP BY subject ORDER BY total DESC LIMIT 8", 'i', $user_id);

$study_hours = 0;
foreach ($all_plans as $plan) {
    if ($plan['status'] === 'completed') {
        $start = strtotime($plan['start_time']);
        $end = strtotime($plan['end_time']);
        if ($start && $end && $end > $start) {
            $study_hours += ($end - $start) / 3600;
        }
    }
}
if ($study_hours <= 0) {
    $study_hours = floatval($progress['study_hours'] ?? 0);
}

$labels14 = [];
$quiz_activity = [];
$plan_activity = [];
$note_activity = [];
$chat_activity = [];
$assignment_activity = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $labels14[] = date('M d', strtotime($date));
    $quiz_activity[$date] = 0;
    $plan_activity[$date] = 0;
    $note_activity[$date] = 0;
    $chat_activity[$date] = 0;
    $assignment_activity[$date] = 0;
}

foreach (fetchAllRows("SELECT DATE(created_at) as day, COUNT(*) as total FROM quizzes WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)", 'i', $user_id) as $row) {
    if (isset($quiz_activity[$row['day']])) $quiz_activity[$row['day']] = intval($row['total']);
}
foreach (fetchAllRows("SELECT start_date as day, COUNT(*) as total FROM study_plans WHERE user_id = ? AND start_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY start_date", 'i', $user_id) as $row) {
    if (isset($plan_activity[$row['day']])) $plan_activity[$row['day']] = intval($row['total']);
}
foreach (fetchAllRows("SELECT DATE(created_at) as day, COUNT(*) as total FROM study_notes WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)", 'i', $user_id) as $row) {
    if (isset($note_activity[$row['day']])) $note_activity[$row['day']] = intval($row['total']);
}
foreach (fetchAllRows("SELECT DATE(created_at) as day, COUNT(*) as total FROM chat_history WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)", 'i', $user_id) as $row) {
    if (isset($chat_activity[$row['day']])) $chat_activity[$row['day']] = intval($row['total']);
}
foreach (fetchAllRows("SELECT DATE(created_at) as day, COUNT(*) as total FROM assignments WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)", 'i', $user_id) as $row) {
    if (isset($assignment_activity[$row['day']])) $assignment_activity[$row['day']] = intval($row['total']);
}

$quiz_labels = array_map(fn($quiz) => date('M d', strtotime($quiz['created_at'])), array_slice($completed_quizzes, -12));
$quiz_scores = array_map(fn($quiz) => round(floatval($quiz['score'])), array_slice($completed_quizzes, -12));

$subject_labels = array_map(fn($row) => $row['subject'], $subject_stats);
$subject_scores = array_map(fn($row) => round(floatval($row['avg_score'])), $subject_stats);
$subject_attempts = array_map(fn($row) => intval($row['attempts']), $subject_stats);

$plan_status_map = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
foreach ($plan_status_stats as $row) {
    $plan_status_map[$row['status']] = intval($row['total']);
}

$difficulty_map = ['easy' => 0, 'medium' => 0, 'hard' => 0];
foreach ($difficulty_stats as $row) {
    $difficulty_map[$row['difficulty']] = intval($row['total']);
}

$assignment_status_map = ['pending' => 0, 'submitted' => 0, 'late' => 0];
foreach ($assignment_status_stats as $row) {
    $assignment_status_map[$row['status']] = intval($row['total']);
}

$assignment_priority_map = ['low' => 0, 'medium' => 0, 'high' => 0];
foreach ($assignment_priority_stats as $row) {
    $assignment_priority_map[$row['priority']] = intval($row['total']);
}

$chart_data = [
    'labels14' => $labels14,
    'quizActivity' => array_values($quiz_activity),
    'planActivity' => array_values($plan_activity),
    'noteActivity' => array_values($note_activity),
    'chatActivity' => array_values($chat_activity),
    'assignmentActivity' => array_values($assignment_activity),
    'quizLabels' => $quiz_labels,
    'quizScores' => $quiz_scores,
    'subjectLabels' => $subject_labels,
    'subjectScores' => $subject_scores,
    'subjectAttempts' => $subject_attempts,
    'planStatusLabels' => ['Pending', 'In Progress', 'Completed'],
    'planStatusData' => array_values($plan_status_map),
    'difficultyLabels' => ['Easy', 'Medium', 'Hard'],
    'difficultyData' => array_values($difficulty_map),
    'assignmentStatusLabels' => ['Pending', 'Submitted', 'Late'],
    'assignmentStatusData' => array_values($assignment_status_map),
    'assignmentPriorityLabels' => ['Low', 'Medium', 'High'],
    'assignmentPriorityData' => array_values($assignment_priority_map),
    'planSubjectLabels' => array_map(fn($row) => $row['subject'], $plan_subject_stats),
    'planSubjectData' => array_map(fn($row) => intval($row['total']), $plan_subject_stats),
    'noteSubjectLabels' => array_map(fn($row) => $row['subject'], $notes_subject_stats),
    'noteSubjectData' => array_map(fn($row) => intval($row['total']), $notes_subject_stats),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Learning Assistant</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold">Dashboard</h4>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
            </div>
            <?php renderTopActions($user); ?>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Study Hours</p>
                        <h3 class="fw-bold mb-0"><?php echo number_format($study_hours, 1); ?></h3>
                        <small class="text-muted">Completed plan time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Average Quiz Score</p>
                        <h3 class="fw-bold mb-0"><?php echo number_format(floatval($quiz_summary['avg_score'] ?? 0), 0); ?>%</h3>
                        <small class="text-muted"><?php echo intval($quiz_summary['total'] ?? 0); ?> completed quizzes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Goals</p>
                        <h3 class="fw-bold mb-0"><?php echo intval($goal_counts['completed_count'] ?? 0); ?>/<?php echo intval($goal_counts['total_count'] ?? 0); ?></h3>
                        <small class="text-muted"><?php echo intval($goal_counts['active_count'] ?? 0); ?> active</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Assignments</p>
                        <h3 class="fw-bold mb-0"><?php echo intval($assignment_counts['pending_count'] ?? 0); ?></h3>
                        <small class="text-muted"><?php echo intval($assignment_counts['submitted_count'] ?? 0); ?> submitted</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">14-Day Learning Activity</h5>
                        <div class="chart-box"><canvas id="activityLineChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Plan Status</h5>
                        <div class="chart-box"><canvas id="planStatusChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Quiz Score Trend</h5>
                        <div class="chart-box"><canvas id="quizScoreChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Subject Performance</h5>
                        <div class="chart-box"><canvas id="subjectBarChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Quiz Difficulty Mix</h5>
                        <div class="chart-box"><canvas id="difficultyPieChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Assignments by Priority</h5>
                        <div class="chart-box"><canvas id="assignmentPriorityChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Assignments by Status</h5>
                        <div class="chart-box"><canvas id="assignmentStatusChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Study Sessions by Subject</h5>
                        <div class="chart-box"><canvas id="planSubjectChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Notes by Subject</h5>
                        <div class="chart-box"><canvas id="noteSubjectChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Recent Study Sessions</h5>
                            <a href="planner.php" class="text-primary text-decoration-none">View All</a>
                        </div>
                        <?php if (count($recent_activities) > 0): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="me-3">
                                        <div class="content-icon content-icon-sm">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($activity['subject']); ?> - <?php echo date('M d, Y', strtotime($activity['start_date'])); ?></p>
                                    </div>
                                    <span class="badge bg-<?php echo $activity['status'] === 'completed' ? 'success' : ($activity['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No study sessions yet</p>
                                <a href="planner.php" class="btn btn-primary">Create Study Plan</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Learning Totals</h5>
                        <div class="d-flex justify-content-between mb-3"><span>All Quizzes</span><strong><?php echo count($all_quizzes); ?></strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Notes</span><strong><?php echo intval($note_counts['total'] ?? 0); ?></strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>AI Chat Questions</span><strong><?php echo intval($chat_counts['total'] ?? 0); ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Best Quiz Score</span><strong><?php echo number_format(floatval($quiz_summary['best_score'] ?? 0), 0); ?>%</strong></div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Notifications</h5>
                            <a href="notifications.php" class="text-primary text-decoration-none small">Open</a>
                        </div>
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($notif['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-bell-slash fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">No new notifications</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        const chartData = <?php echo json_encode($chart_data); ?>;
        const palette = ['#4361ee', '#06d6a0', '#ffd60a', '#ef476f', '#4cc9f0', '#3f37c9', '#ff9f1c', '#8338ec'];
        const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor = isDarkTheme ? '#c1cad7' : '#6c757d';
        const titleColor = isDarkTheme ? '#f3f6fb' : '#212529';
        const gridColor = isDarkTheme ? 'rgba(193, 202, 215, 0.16)' : 'rgba(108, 117, 125, 0.16)';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        function hasData(values) {
            return values.some(value => Number(value) > 0);
        }

        function emptyPlugin(message) {
            return {
                id: 'emptyMessage',
                afterDraw(chart) {
                    const datasets = chart.data.datasets || [];
                    const values = datasets.flatMap(dataset => dataset.data || []);
                    if (hasData(values)) return;
                    const { ctx, chartArea } = chart;
                    ctx.save();
                    ctx.fillStyle = textColor;
                    ctx.textAlign = 'center';
                    ctx.font = '14px sans-serif';
                    ctx.fillText(message, (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
                    ctx.restore();
                }
            };
        }

        const defaultScales = {
            x: { grid: { color: gridColor }, ticks: { color: textColor } },
            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } }
        };

        const legendOptions = {
            labels: {
                color: textColor
            }
        };

        new Chart(document.getElementById('activityLineChart'), {
            type: 'line',
            data: {
                labels: chartData.labels14,
                datasets: [
                    { label: 'Quizzes', data: chartData.quizActivity, borderColor: palette[0], backgroundColor: 'rgba(67,97,238,.12)', tension: .35, fill: true },
                    { label: 'Plans', data: chartData.planActivity, borderColor: palette[1], backgroundColor: 'rgba(6,214,160,.12)', tension: .35, fill: true },
                    { label: 'Notes', data: chartData.noteActivity, borderColor: palette[2], backgroundColor: 'rgba(255,214,10,.12)', tension: .35, fill: true },
                    { label: 'AI Chat', data: chartData.chatActivity, borderColor: palette[3], backgroundColor: 'rgba(239,71,111,.08)', tension: .35, fill: true },
                    { label: 'Assignments', data: chartData.assignmentActivity, borderColor: palette[4], backgroundColor: 'rgba(76,201,240,.1)', tension: .35, fill: true }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: defaultScales, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No activity in the last 14 days')]
        });

        new Chart(document.getElementById('planStatusChart'), {
            type: 'doughnut',
            data: { labels: chartData.planStatusLabels, datasets: [{ data: chartData.planStatusData, backgroundColor: [palette[2], palette[4], palette[1]] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No study plans yet')]
        });

        new Chart(document.getElementById('quizScoreChart'), {
            type: 'line',
            data: { labels: chartData.quizLabels, datasets: [{ label: 'Score %', data: chartData.quizScores, borderColor: palette[0], backgroundColor: 'rgba(67,97,238,.14)', tension: .35, fill: true }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { ...defaultScales, y: { ...defaultScales.y, max: 100 } }, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('Complete quizzes to see trend')]
        });

        new Chart(document.getElementById('subjectBarChart'), {
            type: 'bar',
            data: {
                labels: chartData.subjectLabels,
                datasets: [
                    { label: 'Average Score %', data: chartData.subjectScores, backgroundColor: palette[0] },
                    { label: 'Attempts', data: chartData.subjectAttempts, backgroundColor: palette[1] }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: defaultScales, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No subject performance yet')]
        });

        new Chart(document.getElementById('difficultyPieChart'), {
            type: 'pie',
            data: { labels: chartData.difficultyLabels, datasets: [{ data: chartData.difficultyData, backgroundColor: [palette[1], palette[2], palette[3]] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No quizzes yet')]
        });

        new Chart(document.getElementById('assignmentPriorityChart'), {
            type: 'polarArea',
            data: { labels: chartData.assignmentPriorityLabels, datasets: [{ data: chartData.assignmentPriorityData, backgroundColor: ['rgba(6,214,160,.7)', 'rgba(255,214,10,.75)', 'rgba(239,71,111,.75)'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No assignments yet')]
        });

        new Chart(document.getElementById('assignmentStatusChart'), {
            type: 'radar',
            data: { labels: chartData.assignmentStatusLabels, datasets: [{ label: 'Assignments', data: chartData.assignmentStatusData, borderColor: palette[0], backgroundColor: 'rgba(67,97,238,.18)' }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, grid: { color: gridColor }, angleLines: { color: gridColor }, pointLabels: { color: textColor }, ticks: { precision: 0, color: textColor, backdropColor: 'transparent' } } }, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No assignments yet')]
        });

        new Chart(document.getElementById('planSubjectChart'), {
            type: 'bar',
            data: { labels: chartData.planSubjectLabels, datasets: [{ label: 'Study Sessions', data: chartData.planSubjectData, backgroundColor: palette[5] }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: defaultScales, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No study sessions yet')]
        });

        new Chart(document.getElementById('noteSubjectChart'), {
            type: 'bar',
            data: { labels: chartData.noteSubjectLabels, datasets: [{ label: 'Notes', data: chartData.noteSubjectData, backgroundColor: palette[6] }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: defaultScales, plugins: { legend: legendOptions } },
            plugins: [emptyPlugin('No notes yet')]
        });
    </script>
</body>
</html>
