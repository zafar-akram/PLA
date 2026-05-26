<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$user = getUserData();

$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_start = $current_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

$stmt = $conn->prepare("SELECT * FROM study_plans WHERE user_id = ? AND start_date BETWEEN ? AND ? ORDER BY start_date ASC");
$stmt->bind_param("iss", $_SESSION['user_id'], $month_start, $month_end);
$stmt->execute();
$study_plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$plan_counts = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
foreach ($study_plans as $plan_count_item) {
    if (isset($plan_counts[$plan_count_item['status']])) {
        $plan_counts[$plan_count_item['status']]++;
    }
}

$stmt = $conn->prepare("SELECT * FROM study_goals WHERE user_id = ? AND status = 'active' ORDER BY target_date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bscs_courses = getBscsCourseOutlines();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Planner - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Study Planner</h4>
                <p class="text-muted mb-0">Organize your learning schedule</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Study Plan
                </button>
                <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#generatePlanModal">
                    <i class="bi bi-magic me-2"></i>AI Generate Plan
                </button>
                <button class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                    <i class="bi bi-flag me-2"></i>Add Goal
                </button>
                <?php renderTopActions($user); ?>
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
                        <div class="row g-2 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold"><?php echo $plan_counts['pending']; ?></div>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold"><?php echo $plan_counts['in_progress']; ?></div>
                                    <small class="text-muted">In Progress</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold"><?php echo $plan_counts['completed']; ?></div>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                        <?php if (count($study_plans) > 0): ?>
                            <?php foreach ($study_plans as $plan): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="me-3">
                                        <div class="content-icon">
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
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $plan['status'] === 'completed' ? 'success' : ($plan['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $plan['status'])); ?>
                                        </span>
                                        <div class="btn-group btn-group-sm mt-2 d-flex study-plan-actions" role="group">
                                            <?php if ($plan['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-outline-warning plan-status-btn" title="Start" data-plan-id="<?php echo $plan['id']; ?>" data-status="in_progress">
                                                    <i class="bi bi-play-circle"></i> Start
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($plan['status'] !== 'completed'): ?>
                                                <button type="button" class="btn btn-outline-success plan-status-btn" title="Complete" data-plan-id="<?php echo $plan['id']; ?>" data-status="completed">
                                                    <i class="bi bi-check-circle"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-primary plan-quiz-btn" title="Create quiz" data-plan-id="<?php echo $plan['id']; ?>" data-plan-title="<?php echo htmlspecialchars($plan['title']); ?>">
                                                <i class="bi bi-clipboard-check"></i> Quiz
                                            </button>
                                            <a class="btn btn-outline-info" title="Teach me" href="chat.php?teach=<?php echo urlencode('Teach me this planned study session step by step. Subject: ' . $plan['subject'] . '. Topic: ' . $plan['title'] . '. Goal: ' . $plan['goal']); ?>">
                                                <i class="bi bi-mortarboard"></i> Teach
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-secondary plan-edit-btn"
                                                    title="Edit"
                                                    data-plan-id="<?php echo $plan['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($plan['title'], ENT_QUOTES); ?>"
                                                    data-subject="<?php echo htmlspecialchars($plan['subject'], ENT_QUOTES); ?>"
                                                    data-start-date="<?php echo htmlspecialchars($plan['start_date'], ENT_QUOTES); ?>"
                                                    data-end-date="<?php echo htmlspecialchars($plan['end_date'], ENT_QUOTES); ?>"
                                                    data-start-time="<?php echo htmlspecialchars(substr($plan['start_time'], 0, 5), ENT_QUOTES); ?>"
                                                    data-end-time="<?php echo htmlspecialchars(substr($plan['end_time'], 0, 5), ENT_QUOTES); ?>"
                                                    data-goal="<?php echo htmlspecialchars($plan['goal'] ?? '', ENT_QUOTES); ?>"
                                                    data-status="<?php echo htmlspecialchars($plan['status'], ENT_QUOTES); ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-outline-danger plan-delete-btn" title="Delete" data-plan-id="<?php echo $plan['id']; ?>" data-plan-title="<?php echo htmlspecialchars($plan['title'], ENT_QUOTES); ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
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

    <div class="modal fade" id="generatePlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">AI Generate Study Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="generatePlanForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">University</label>
                                <select class="form-select" name="university" id="planUniversity">
                                    <option value="GCUF" selected>GCUF</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course</label>
                                <select class="form-select" name="course" id="planCourse">
                                    <option value="BSCS" selected>BSCS</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" id="planSemester">
                                    <?php for ($semester = 1; $semester <= 8; $semester++): ?>
                                        <option value="<?php echo $semester; ?>">Semester <?php echo $semester; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Subjects</label>
                                <button type="button" class="btn btn-outline-primary w-100" id="addPlanSubjectBtn">
                                    <i class="bi bi-plus-circle me-2"></i>Add Subject
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="planSubjectsContainer"></div>
                            <input type="hidden" name="subjects" id="planSubjectsInput">
                            <input type="hidden" name="outline" id="planOutlineInput">
                            <input type="hidden" name="subject_outlines" id="planSubjectOutlinesInput">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Special Features / Requirements</label>
                            <textarea class="form-control" name="special_features" rows="3" placeholder="e.g., include revision days, focus on weak topics, add practice problems, prepare for midterm"></textarea>
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Daily Start Time</label>
                                <input type="time" class="form-control" name="start_time" value="09:00" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Daily End Time</label>
                                <input type="time" class="form-control" name="end_time" value="11:00" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Session Duration</label>
                                <input type="number" class="form-control" name="duration_hours" value="1" min="0.5" max="8" step="0.5" required>
                            </div>
                        </div>
                        <label class="form-label">Off Days</label>
                        <div class="row g-2">
                            <?php foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day): ?>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="off_days[]" value="<?php echo $day; ?>" id="off_<?php echo $day; ?>">
                                        <label class="form-check-label" for="off_<?php echo $day; ?>"><?php echo ucfirst($day); ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-magic me-2"></i>Generate Plan
                        </button>
                    </div>
                </form>
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

    <div class="modal fade" id="editPlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Study Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPlanForm">
                    <div class="modal-body">
                        <input type="hidden" name="plan_id" id="editPlanId">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="editPlanTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" id="editPlanSubject" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="editPlanStartDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="editPlanEndDate" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time" id="editPlanStartTime" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" id="editPlanEndTime" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editPlanStatus">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goal</label>
                            <textarea class="form-control" name="goal" id="editPlanGoal" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
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

    <div class="modal fade" id="planQuizModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Quiz From Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="planQuizForm">
                    <div class="modal-body">
                        <input type="hidden" id="planQuizPlanId" name="plan_id">
                        <p class="text-muted mb-3" id="planQuizTitle"></p>
                        <div class="mb-3">
                            <label class="form-label">Quiz Scope</label>
                            <select class="form-select" name="scope" id="planQuizScope">
                                <option value="session" selected>This session only</option>
                                <option value="day">Full day plan</option>
                                <option value="subject">Full subject plan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" name="difficulty">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Questions</label>
                            <select class="form-select" name="total_questions">
                                <option value="5">5 Questions</option>
                                <option value="10" selected>10 Questions</option>
                                <option value="15">15 Questions</option>
                                <option value="20">20 Questions</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-magic me-2"></i>Create Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        const bscsCourses = <?php echo json_encode($bscs_courses); ?>;
        let planSubjectIndex = 0;

        function getPlanSubjectRows() {
            return Array.from(document.querySelectorAll('.plan-subject-row'));
        }

        function createSubjectOptions(selected = '') {
            const customOption = '<option value="">Select BSCS subject</option>';
            const semester = Number(document.getElementById('planSemester').value);
            const filteredCourses = bscsCourses.filter(course => Number(course.semester) === semester);
            return customOption + filteredCourses.map(course => {
                const isSelected = course.name === selected ? 'selected' : '';
                return `<option value="${escapeHtml(course.name)}" ${isSelected}>${escapeHtml(course.name)}</option>`;
            }).join('');
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function addPlanSubjectRow(subject = '', outline = '') {
            const container = document.getElementById('planSubjectsContainer');
            const index = planSubjectIndex++;
            const row = document.createElement('div');
            row.className = 'border rounded p-3 mb-3 plan-subject-row';
            row.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Subject</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-plan-subject" aria-label="Remove subject">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <select class="form-select mb-2 plan-subject-select" data-index="${index}">
                    ${createSubjectOptions(subject)}
                </select>
                <input type="text" class="form-control mb-2 plan-custom-subject d-none" placeholder="Custom subject name" value="${escapeHtml(subject)}">
                <textarea class="form-control plan-subject-outline" rows="4" placeholder="Subject-specific outline">${escapeHtml(outline)}</textarea>
            `;

            container.appendChild(row);
            bindPlanSubjectRow(row);
            updatePlanSubjectMode(row);
        }

        function bindPlanSubjectRow(row) {
            row.querySelector('.plan-subject-select').addEventListener('change', () => {
                const selected = row.querySelector('.plan-subject-select').value;
                const course = bscsCourses.find(item => item.name === selected);
                if (course) {
                    row.querySelector('.plan-subject-outline').value = course.outline;
                    row.querySelector('.plan-custom-subject').value = selected;
                }
            });

            row.querySelector('.remove-plan-subject').addEventListener('click', () => {
                row.remove();
                if (getPlanSubjectRows().length === 0) {
                    addPlanSubjectRow();
                }
            });
        }

        function updatePlanSubjectMode(row) {
            const isCustom = document.getElementById('planUniversity').value === 'Custom' || document.getElementById('planCourse').value === 'Custom';
            row.querySelector('.plan-subject-select').classList.toggle('d-none', isCustom);
            row.querySelector('.plan-custom-subject').classList.toggle('d-none', !isCustom);
        }

        function updateAllPlanSubjectModes() {
            getPlanSubjectRows().forEach(updatePlanSubjectMode);
        }

        function collectPlanSubjectOutlines() {
            const isCustom = document.getElementById('planUniversity').value === 'Custom' || document.getElementById('planCourse').value === 'Custom';
            return getPlanSubjectRows().map(row => {
                const subject = isCustom
                    ? row.querySelector('.plan-custom-subject').value.trim()
                    : row.querySelector('.plan-subject-select').value.trim();
                return {
                    subject,
                    outline: row.querySelector('.plan-subject-outline').value.trim(),
                    semester: document.getElementById('planSemester').value
                };
            }).filter(item => item.subject !== '');
        }

        document.getElementById('addPlanSubjectBtn').addEventListener('click', () => addPlanSubjectRow());
        document.getElementById('planUniversity').addEventListener('change', updateAllPlanSubjectModes);
        document.getElementById('planCourse').addEventListener('change', updateAllPlanSubjectModes);
        document.getElementById('planSemester').addEventListener('change', () => {
            getPlanSubjectRows().forEach(row => {
                row.querySelector('.plan-subject-select').innerHTML = createSubjectOptions();
                row.querySelector('.plan-subject-outline').value = '';
                row.querySelector('.plan-custom-subject').value = '';
            });
            updateAllPlanSubjectModes();
        });
        addPlanSubjectRow();

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

        const editPlanModal = new bootstrap.Modal(document.getElementById('editPlanModal'));
        document.querySelectorAll('.plan-edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('editPlanId').value = button.dataset.planId;
                document.getElementById('editPlanTitle').value = button.dataset.title || '';
                document.getElementById('editPlanSubject').value = button.dataset.subject || '';
                document.getElementById('editPlanStartDate').value = button.dataset.startDate || '';
                document.getElementById('editPlanEndDate').value = button.dataset.endDate || '';
                document.getElementById('editPlanStartTime').value = button.dataset.startTime || '';
                document.getElementById('editPlanEndTime').value = button.dataset.endTime || '';
                document.getElementById('editPlanGoal').value = button.dataset.goal || '';
                document.getElementById('editPlanStatus').value = button.dataset.status || 'pending';
                editPlanModal.show();
            });
        });

        document.getElementById('editPlanForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Saving...';

            try {
                const response = await fetch('../api/update_plan.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            } catch (error) {
                alert('Error updating study plan');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });

        document.querySelectorAll('.plan-delete-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const planTitle = button.dataset.planTitle || 'this study plan';
                if (!confirm(`Delete "${planTitle}"?`)) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch('../api/delete_plan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: button.dataset.planId
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                        button.disabled = false;
                    }
                } catch (error) {
                    alert('Error deleting study plan');
                    button.disabled = false;
                }
            });
        });

        document.getElementById('generatePlanForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const subjectOutlines = collectPlanSubjectOutlines();
            if (subjectOutlines.length === 0) {
                alert('Please select at least one subject');
                return;
            }

            document.getElementById('planSubjectOutlinesInput').value = JSON.stringify(subjectOutlines);
            document.getElementById('planSubjectsInput').value = subjectOutlines.map(item => item.subject).join(', ');
            document.getElementById('planOutlineInput').value = subjectOutlines.map(item => `${item.subject}: ${item.outline}`).join("\n\n");

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Generating...';

            try {
                const response = await fetch('../api/generate_plan.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            } catch (error) {
                alert('Error generating study plan');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });

        document.querySelectorAll('.plan-status-btn').forEach(button => {
            button.addEventListener('click', async () => {
                button.disabled = true;

                try {
                    const response = await fetch('../api/update_plan_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: button.dataset.planId,
                            status: button.dataset.status
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                        button.disabled = false;
                    }
                } catch (error) {
                    alert('Error updating study plan');
                    button.disabled = false;
                }
            });
        });

        const planQuizModal = new bootstrap.Modal(document.getElementById('planQuizModal'));
        document.querySelectorAll('.plan-quiz-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('planQuizPlanId').value = button.dataset.planId;
                document.getElementById('planQuizTitle').textContent = button.dataset.planTitle;
                planQuizModal.show();
            });
        });

        document.getElementById('planQuizForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            const formData = new FormData(e.target);

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Creating...';

            try {
                const response = await fetch('../api/create_plan_quiz.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    window.location.href = `take_quiz.php?id=${data.data.quiz_id}`;
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            } catch (error) {
                alert('Error creating quiz from plan');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });
    </script>
</body>
</html>
