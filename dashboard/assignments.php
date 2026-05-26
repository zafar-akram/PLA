<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$courses = getBscsCourseOutlines();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? 'add');
    if ($action === 'add') {
        $title = sanitize($_POST['title'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $custom_subject = sanitize($_POST['custom_subject'] ?? '');
        $outline = sanitize($_POST['outline'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $due_date = sanitize($_POST['due_date'] ?? '');
        $priority = sanitize($_POST['priority'] ?? 'medium');
        if ($subject === 'Custom') {
            $subject = $custom_subject;
        }
        if ($outline !== '') {
            $description = trim($description . "\n\nOutline: " . $outline);
        }

        if ($title && $subject && $due_date) {
            $stmt = $conn->prepare("INSERT INTO assignments (user_id, title, subject, description, due_date, priority) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $_SESSION['user_id'], $title, $subject, $description, $due_date, $priority);
            $stmt->execute();
        }
    } elseif ($action === 'status') {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'pending');
        if ($assignment_id && in_array($status, ['pending', 'submitted', 'late'], true)) {
            $stmt = $conn->prepare("UPDATE assignments SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $status, $assignment_id, $_SESSION['user_id']);
            $stmt->execute();
        }
    }

    header('Location: assignments.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM assignments WHERE user_id = ? ORDER BY due_date ASC, created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Assignments</h4>
                <p class="text-muted mb-0">Track due dates, priority, and submission status</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Assignment
                </button>
                <?php renderTopActions($user ?? null); ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($assignment['title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($assignment['description']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['subject']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                                    <td><span class="badge bg-<?php echo $assignment['priority'] === 'high' ? 'danger' : ($assignment['priority'] === 'medium' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($assignment['priority']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $assignment['status'] === 'submitted' ? 'success' : ($assignment['status'] === 'late' ? 'danger' : 'secondary'); ?>"><?php echo ucfirst($assignment['status']); ?></span></td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                            <input type="hidden" name="status" value="submitted">
                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle me-1"></i>Submitted</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($assignments) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-5">No assignments added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Assignment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select custom-subject-select" name="subject" required>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['name']); ?>"><?php echo htmlspecialchars('S' . $course['semester'] . ' - ' . $course['name']); ?></option>
                                <?php endforeach; ?>
                                <option value="Custom">Custom Subject</option>
                            </select>
                            <input class="form-control mt-2 custom-subject-input d-none" name="custom_subject" placeholder="Enter custom subject">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Outline</label>
                            <textarea class="form-control" name="outline" rows="3" placeholder="Paste outline or key assignment topics"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        document.querySelectorAll('.custom-subject-select').forEach(select => {
            select.addEventListener('change', () => {
                const input = select.closest('.mb-3').querySelector('.custom-subject-input');
                if (input) {
                    input.classList.toggle('d-none', select.value !== 'Custom');
                    input.required = select.value === 'Custom';
                }
            });
        });
    </script>
</body>
</html>
