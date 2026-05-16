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
            <a href="../dashboard/quizzes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'quizzes.php' ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check"></i>
                <span>Adaptive Quizzes</span>
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
        <li>
            <a href="../dashboard/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
</div>
