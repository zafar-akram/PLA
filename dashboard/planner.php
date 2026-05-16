<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();

$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_start = $current_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

$stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND start_date BETWEEN ? AND ? ORDER BY start_date ASC");
$stmt->bind_param("iss", $_SESSION['user_id'], $month_start, $month_end);
$stmt->execute();
$study_plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM study_goals WHERE user_id = ? AND status = 'active' ORDER BY target_date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Planner - AI Learning Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold">Study Planner</h4>
                <p class="text-muted mb-0">Organize your learning schedule</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Study Plan
                </button>
                <button class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                    <i class="bi bi-flag me-2"></i>Add Goal
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Study Schedule - <?php echo date('F Y', strtotime($month_start)); ?></h5>
                            <div class="btn-group">
                                <a href="?month=<?php echo date('Y-m', strtotime($month_start . ' -1 month')); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <a href="?month=<?php echo date('Y-m'); ?>" class="btn btn-outline-primary">Today</a>
                                <a href="?month=<?php echo date('Y-m', strtotime($month_start . ' +1 month')); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="calendar-grid mb-3">
                            <?php
                            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            foreach ($days as $day) {
                                echo '<div class="text-center fw-bold text-muted small">' . $day . '</div>';
                            }
                            ?>
                        </div>

                        <div class="calendar-grid">
                            <?php
                            $first_day = date('w', strtotime($month_start));
                            $days_in_month = date('t', strtotime($month_start));
                            
                            for ($i = 0; $i < $first_day; $i++) {
                                echo '<div class="calendar-day"></div>';
                            }
                            
                            for ($day = 1; $day <= $days_in_month; $day++) {
                                $current_date = $current_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                $has_event = false;
                                
                                foreach ($study_plans as $plan) {
                                    if ($plan['start_date'] === $current_date) {
                                        $has_event = true;
                                        break;
                                    }
                                }
                                
                                $is_today = $current_date === date('Y-m-d');
                                $class = $is_today ? 'today' : ($has_event ? 'has-event' : '');
                                
                                echo '<div class="calendar-day ' . $class . '">';
                                echo '<div class="fw-bold">' . $day . '</div>';
                                if ($has_event) {
                                    echo '<small class="text-primary">•</small>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Upcoming Study Sessions</h5>
                        <?php if (count($study_plans) > 0): ?>
                            <?php foreach ($study_plans as $plan): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-3">
                                            <i class="bi bi-book text-primary fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($plan['title']); ?></h6>
                                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($plan['subject']); ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($plan['start_date'])); ?>
                                            <i class="bi bi-clock ms-2 me-1"></i><?php echo date('g:i A', strtotime($plan['start_time'])); ?> - <?php echo date('g:i A', strtotime($plan['end_time'])); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?php echo $plan['status'] === 'completed' ? 'success' : ($plan['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($plan['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No study sessions scheduled for this month</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Active Goals</h5>
                        <?php if (count($goals) > 0): ?>
                            <?php foreach ($goals as $goal): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($goal['goal_title']); ?></h6>
                                        <span class="badge bg-primary"><?php echo $goal['progress']; ?>%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?php echo $goal['progress']; ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>Due: <?php echo date('M d, Y', strtotime($goal['target_date'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-flag fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">No active goals</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Study Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addPlanForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goal</label>
                            <textarea class="form-control" name="goal" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addGoalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addGoalForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Goal Title</label>
                            <input type="text" class="form-control" name="goal_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Date</label>
                            <input type="date" class="form-control" name="target_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        document.getElementById('addPlanForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/add_plan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error adding study plan');
            }
        });

        document.getElementById('addGoalForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('../api/add_goal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error adding goal');
            }
        });
    </script>
</body>
</html>
