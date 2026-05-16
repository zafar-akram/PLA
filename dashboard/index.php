<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();

$stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

if (!$progress) {
    $stmt = $conn->prepare("INSERT INTO user_progress (user_id, subject) VALUES (?, 'General')");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $progress = ['study_hours' => 0, 'average_score' => 0, 'streak_days' => 0, 'quizzes_taken' => 0];
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM study_goals WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$active_goals = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as completed FROM study_goals WHERE user_id = ? AND status = 'completed'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$completed_goals = $stmt->get_result()->fetch_assoc()['completed'];

$total_goals = $active_goals + $completed_goals;

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND status != 'completed' ORDER BY start_date DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Learning Assistant</title>
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
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <i class="bi bi-bell fs-5 cursor-pointer" data-bs-toggle="dropdown"></i>
                    <?php if (count($notifications) > 0): ?>
                        <span class="notification-badge"><?php echo count($notifications); ?></span>
                    <?php endif; ?>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3 text-center text-muted">No new notifications</div>
                        <?php endif; ?>
                    </div>
                </div>
                <i class="bi bi-moon-fill fs-5 theme-toggle" id="themeToggle"></i>
                <div class="dropdown">
                    <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Study Hours</p>
                                <h3 class="fw-bold mb-0"><?php echo number_format($progress['study_hours'], 0); ?></h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +12%</small>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10">
                                <i class="bi bi-clock-history text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Quiz Score</p>
                                <h3 class="fw-bold mb-0"><?php echo number_format($progress['average_score'], 0); ?>%</h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +5%</small>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-trophy text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Goals</p>
                                <h3 class="fw-bold mb-0"><?php echo $completed_goals; ?>/<?php echo $total_goals ?: 15; ?></h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +3</small>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10">
                                <i class="bi bi-flag text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Day Streak</p>
                                <h3 class="fw-bold mb-0"><?php echo $progress['streak_days']; ?></h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +2</small>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10">
                                <i class="bi bi-fire text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Recent Activity</h5>
                            <a href="planner.php" class="text-primary text-decoration-none">View All</a>
                        </div>
                        <?php if (count($recent_activities) > 0): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-2">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($activity['subject']); ?></p>
                                    </div>
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar" style="width: <?php echo rand(30, 90); ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No recent activities</p>
                                <a href="planner.php" class="btn btn-primary">Create Study Plan</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Notifications</h5>
                            <a href="#" class="text-primary text-decoration-none small">Mark All Read</a>
                        </div>
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-lightbulb text-primary me-2"></i>
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                            <p class="text-muted small mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($notif['created_at'])); ?></small>
                                        </div>
                                    </div>
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
    <script src="../assets/js/theme.js"></script>
</body>
</html>
