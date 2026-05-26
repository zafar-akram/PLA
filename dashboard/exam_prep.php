<?php
require_once '../config/config.php';
requireLogin();

$stmt = $conn->prepare("SELECT COUNT(*) as total, AVG(score) as avg_score FROM quizzes WHERE user_id = ? AND completed = 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$quiz_stats = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT subject, COUNT(*) as attempts, AVG(score) as avg_score FROM quizzes WHERE user_id = ? AND completed = 1 GROUP BY subject ORDER BY avg_score ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$subject_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND status != 'completed' ORDER BY start_date ASC, start_time ASC LIMIT 8");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$upcoming_plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM assignments WHERE user_id = ? AND status = 'pending' ORDER BY due_date ASC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pending_assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Prep - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Exam Prep</h4>
                <p class="text-muted mb-0">Revision dashboard for quizzes, weak subjects, and pending work</p>
            </div>
            <div class="topbar-action-group">
                <a href="quizzes.php" class="btn btn-primary"><i class="bi bi-magic me-2"></i>Generate Quiz</a>
                <?php renderTopActions($user ?? null); ?>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Completed Quizzes</small>
                        <h3 class="fw-bold mb-0"><?php echo intval($quiz_stats['total'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Average Score</small>
                        <h3 class="fw-bold mb-0"><?php echo round(floatval($quiz_stats['avg_score'] ?? 0)); ?>%</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Pending Assignments</small>
                        <h3 class="fw-bold mb-0"><?php echo count($pending_assignments); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Weak Areas</h5>
                        <?php foreach ($subject_stats as $stat): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><?php echo htmlspecialchars($stat['subject']); ?></span>
                                    <span class="fw-bold"><?php echo round($stat['avg_score']); ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: <?php echo round($stat['avg_score']); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($subject_stats) === 0): ?>
                            <p class="text-muted mb-0">Complete quizzes to see weak areas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Revision Queue</h5>
                        <?php foreach ($upcoming_plans as $plan): ?>
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <i class="bi bi-calendar-check text-primary me-3"></i>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($plan['title']); ?></div>
                                    <small class="text-muted"><?php echo date('M d', strtotime($plan['start_date'])); ?> - <?php echo htmlspecialchars($plan['subject']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($upcoming_plans) === 0): ?>
                            <p class="text-muted mb-0">No pending revision sessions.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
