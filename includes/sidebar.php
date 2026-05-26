<?php
if (!function_exists('renderTopActions')) {
    function renderTopActions($user = null) {
        global $conn;

        if (!$user && function_exists('getUserData')) {
            $user = getUserData();
        }

        $fullName = $user['full_name'] ?? ($_SESSION['user_name'] ?? 'Student');
        $email = $user['email'] ?? ($_SESSION['user_email'] ?? '');
        $initial = strtoupper(substr($fullName, 0, 1));
        $profilePicture = trim($user['profile_picture'] ?? '');
        $profilePictureUrl = $profilePicture !== '' ? '../uploads/profiles/' . rawurlencode($profilePicture) : '';
        $unreadCount = 0;

        if (isset($conn, $_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
            if ($stmt) {
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $unreadCount = intval($row['total'] ?? 0);
            }
        }
        ?>
        <div class="top-actions">
            <a href="notifications.php" class="top-icon-btn position-relative" title="Notifications">
                <i class="bi bi-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?php echo min($unreadCount, 9); ?></span>
                <?php endif; ?>
            </a>
            <button type="button" class="top-icon-btn theme-toggle" title="Toggle theme">
                <i class="bi bi-moon-fill"></i>
            </button>
            <div class="dropdown">
                <button class="profile-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="profile-avatar">
                        <?php if ($profilePictureUrl): ?>
                            <img src="<?php echo htmlspecialchars($profilePictureUrl); ?>" alt="<?php echo htmlspecialchars($fullName); ?>">
                        <?php else: ?>
                            <?php echo htmlspecialchars($initial); ?>
                        <?php endif; ?>
                    </span>
                    <span class="profile-meta">
                        <strong><?php echo htmlspecialchars($fullName); ?></strong>
                        <?php if ($email): ?>
                            <small><?php echo htmlspecialchars($email); ?></small>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end profile-dropdown shadow-sm">
                    <li class="dropdown-header">
                        <div class="fw-semibold"><?php echo htmlspecialchars($fullName); ?></div>
                        <?php if ($email): ?>
                            <small><?php echo htmlspecialchars($email); ?></small>
                        <?php endif; ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="settings.php">
                            <i class="bi bi-person-circle me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="settings.php">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="../dashboard/index.php" class="text-decoration-none">
            <div class="d-flex align-items-center">
                <i class="bi bi-mortarboard-fill text-primary fs-3 me-2"></i>
                <div>
                    <h5 class="mb-0 fw-bold">AI Learning Assistant</h5>
                </div>
            </div>
        </a>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="../dashboard/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/chat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                <span>AI Chat Assistant</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/planner.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'planner.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i>
                <span>Study Planner</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span>Courses & Semesters</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/assignments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'active' : ''; ?>">
                <i class="bi bi-card-checklist"></i>
                <span>Assignments</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/quizzes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'quizzes.php' ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check"></i>
                <span>Adaptive Quizzes</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/exam_prep.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'exam_prep.php' ? 'active' : ''; ?>">
                <i class="bi bi-award"></i>
                <span>Exam Prep</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/notes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i>
                <span>Study Notes</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/progress.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'progress.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>Progress Analytics</span>
            </a>
        </li>
        <li>
            <a href="../dashboard/resources.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'resources.php' ? 'active' : ''; ?>">
                <i class="bi bi-book"></i>
                <span>Learning Resources</span>
            </a>
        </li>
        <!-- <li>
            <a href="../dashboard/notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                <i class="bi bi-bell"></i>
                <span>Notifications</span>
            </a>
        </li> -->
        <li>
            <a href="../dashboard/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
</div>
