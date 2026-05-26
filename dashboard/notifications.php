<?php
require_once '../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    header('Location: notifications.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Notifications</h4>
                <p class="text-muted mb-0">System alerts, study reminders, and quiz updates</p>
            </div>
            <div class="topbar-action-group">
                <form method="POST">
                    <button class="btn btn-outline-primary"><i class="bi bi-check2-all me-2"></i>Mark All Read</button>
                </form>
                <?php renderTopActions($user ?? null); ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php foreach ($notifications as $notification): ?>
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <div class="content-icon">
                                <i class="bi bi-bell text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small class="text-muted"><?php echo date('M d, Y g:i A', strtotime($notification['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($notifications) === 0): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bell-slash fs-1"></i>
                        <p class="mt-3 mb-0">No notifications yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
