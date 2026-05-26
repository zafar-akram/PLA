<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();

$stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE user_id = ? AND completed = 1 ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT subject, COUNT(*) as count, AVG(score) as avg_score FROM quizzes WHERE user_id = ? AND completed = 1 GROUP BY subject");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$subject_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Analytics - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Progress Analytics</h4>
                <p class="text-muted mb-0">Track your learning journey</p>
            </div>
            <?php renderTopActions($user ?? null); ?>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history text-primary fs-1 mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($progress['study_hours'] ?? 0, 1); ?></h3>
                        <p class="text-muted mb-0">Total Study Hours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-check text-success fs-1 mb-3"></i>
                        <h3 class="fw-bold"><?php echo $progress['quizzes_taken'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Quizzes Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-trophy text-warning fs-1 mb-3"></i>
                        <h3 class="fw-bold"><?php echo number_format($progress['average_score'] ?? 0, 1); ?>%</h3>
                        <p class="text-muted mb-0">Average Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-fire text-danger fs-1 mb-3"></i>
                        <h3 class="fw-bold"><?php echo $progress['streak_days'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Day Streak</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Recent Quiz Performance</h5>
                        <?php if (count($recent_quizzes) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Quiz Title</th>
                                            <th>Subject</th>
                                            <th>Difficulty</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_quizzes as $quiz): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                                <td><?php echo htmlspecialchars($quiz['subject']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $quiz['difficulty'] === 'easy' ? 'success' : ($quiz['difficulty'] === 'medium' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($quiz['difficulty']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-<?php echo $quiz['score'] >= 80 ? 'success' : ($quiz['score'] >= 60 ? 'warning' : 'danger'); ?>">
                                                        <?php echo number_format($quiz['score'], 0); ?>%
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-graph-up fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No quiz data available yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Subject Performance</h5>
                        <?php if (count($subject_stats) > 0): ?>
                            <?php foreach ($subject_stats as $stat): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><?php echo htmlspecialchars($stat['subject']); ?></span>
                                        <span class="text-primary"><?php echo number_format($stat['avg_score'], 0); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" style="width: <?php echo $stat['avg_score']; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $stat['count']; ?> quizzes completed</small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-book fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">No subject data available</p>
                            </div>
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
