<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require_once 'config/database.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Learning Assistant</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="main-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="content-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Here's what's happening with your learning journey today.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="studyHours">0</h3>
                        <p>Study Hours</p>
                        <span class="stat-change positive">+12%</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="quizAverage">0%</h3>
                        <p>Quiz Score</p>
                        <span class="stat-change positive">+5</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-target"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="goalsCompleted">0/0</h3>
                        <p>Goals</p>
                        <span class="stat-change positive">+3</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-fire"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="dayStreak">0</h3>
                        <p>Day Streak</p>
                        <span class="stat-change positive">+2</span>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                            <a href="#" class="view-all">View All</a>
                        </div>
                        <div class="activity-list" id="activityList">
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="bi bi-book"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Data Structures & Algorithms</h4>
                                    <p>Completed: Binary Tree Chapter</p>
                                </div>
                                <div class="activity-progress">
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card">
                        <div class="card-header">
                            <h3>Notifications</h3>
                            <a href="#" class="mark-read">Mark All Read</a>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <i class="bi bi-lightbulb"></i>
                                </div>
                                <div class="notification-content">
                                    <h4>AI Tutor Suggestion</h4>
                                    <p>Based on your performance, consider reviewing SQL joins.</p>
                                    <span class="notification-time">2 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
