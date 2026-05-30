<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$user = getUserData();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_custom_course') {
        $custom_university = sanitize($_POST['university'] ?? '');
        $custom_course = sanitize($_POST['course'] ?? '');
        $custom_semester = intval($_POST['semester'] ?? 1);
        $custom_subject = sanitize($_POST['subject'] ?? '');
        $custom_outline = trim($_POST['outline'] ?? '');

        if ($custom_university === '' || $custom_course === '' || $custom_subject === '' || $custom_outline === '') {
            $error = 'Please fill all custom course fields.';
        } else {
            $custom_semester = max(1, min(16, $custom_semester));
            $stmt = $conn->prepare("INSERT INTO custom_course_outlines (user_id, university, course, semester, subject, outline) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ississ", $_SESSION['user_id'], $custom_university, $custom_course, $custom_semester, $custom_subject, $custom_outline);

            if ($stmt->execute()) {
                header('Location: courses.php?university=' . urlencode($custom_university) . '&course=' . urlencode($custom_course) . '&semester=' . $custom_semester . '&saved=1');
                exit();
            }

            $error = 'Unable to save custom course. Please try again.';
        }
    }

    if ($action === 'delete_custom_course') {
        $custom_id = intval($_POST['custom_id'] ?? 0);
        if ($custom_id > 0) {
            $stmt = $conn->prepare("DELETE FROM custom_course_outlines WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $custom_id, $_SESSION['user_id']);
            $stmt->execute();
            header('Location: courses.php?deleted=1');
            exit();
        }
    }
}

$courses = getCourseOutlinesForUser($_SESSION['user_id']);
$universities = array_values(array_unique(array_map(fn($course) => $course['university'], $courses)));
sort($universities, SORT_NATURAL | SORT_FLAG_CASE);
$selected_university = sanitize($_GET['university'] ?? ($universities[0] ?? 'GCUF'));
if (!in_array($selected_university, $universities, true)) {
    $selected_university = $universities[0] ?? 'GCUF';
}
$available_courses = array_values(array_unique(array_map(function ($course) use ($selected_university) {
    return $course['university'] === $selected_university ? $course['course'] : null;
}, $courses)));
$available_courses = array_values(array_filter($available_courses));
sort($available_courses, SORT_NATURAL | SORT_FLAG_CASE);
$selected_course = sanitize($_GET['course'] ?? ($available_courses[0] ?? 'BSCS'));
if (!in_array($selected_course, $available_courses, true)) {
    $selected_course = $available_courses[0] ?? 'BSCS';
}
$max_semester = max(8, ...array_map(fn($course) => intval($course['semester']), $courses ?: [['semester' => 8]]));
$semester = intval($_GET['semester'] ?? 1);
$semester = max(1, min($max_semester, $semester));
$semester_courses = array_values(array_filter($courses, function ($course) use ($semester, $selected_university, $selected_course) {
    return $course['university'] === $selected_university
        && $course['course'] === $selected_course
        && intval($course['semester']) === $semester;
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses & Semesters - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Courses & Semesters</h4>
                <p class="text-muted mb-0">Built-in and custom semester-wise subject outlines</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomCourseModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Custom
                </button>
                <?php renderTopActions($user); ?>
            </div>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success shadow-sm border-0">
                Custom subject saved successfully.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success shadow-sm border-0">
                Custom subject deleted successfully.
            </div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger shadow-sm border-0">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="GET">
                    <div class="col-md-4">
                        <label class="form-label">University</label>
                        <select class="form-select" name="university" id="universitySelect">
                            <?php foreach ($universities as $university): ?>
                                <option value="<?php echo htmlspecialchars($university); ?>" <?php echo $university === $selected_university ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($university); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Course</label>
                        <select class="form-select" name="course" id="courseSelect">
                            <?php foreach ($available_courses as $course_name): ?>
                                <option value="<?php echo htmlspecialchars($course_name); ?>" <?php echo $course_name === $selected_course ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester" id="semesterSelect">
                            <?php for ($i = 1; $i <= $max_semester; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i === $semester ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">University</small>
                        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($selected_university); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Course</small>
                        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($selected_course); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Subjects</small>
                        <h4 class="fw-bold mb-0"><?php echo count($semester_courses); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion" id="courseAccordion">
            <?php if (count($semester_courses) > 0): ?>
                <?php foreach ($semester_courses as $index => $course): ?>
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#course<?php echo $index; ?>">
                            <span class="fw-bold"><?php echo htmlspecialchars($course['name']); ?></span>
                            <?php if (!empty($course['is_custom'])): ?>
                                <span class="badge bg-info ms-2">Custom</span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="course<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#courseAccordion">
                        <div class="accordion-body">
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($course['outline']); ?></p>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-primary" href="quizzes.php">
                                    <i class="bi bi-clipboard-check me-1"></i>Create Quiz
                                </a>
                                <a class="btn btn-sm btn-outline-primary" href="planner.php">
                                    <i class="bi bi-calendar-plus me-1"></i>Add To Plan
                                </a>
                                <?php if (!empty($course['is_custom'])): ?>
                                    <form method="POST" onsubmit="return confirm('Delete this custom subject?');">
                                        <input type="hidden" name="action" value="delete_custom_course">
                                        <input type="hidden" name="custom_id" value="<?php echo intval($course['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                        <h5 class="mt-3">No Subjects Found</h5>
                        <p class="text-muted">Add a custom subject for this university, course, and semester.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomCourseModal">
                            <i class="bi bi-plus-circle me-2"></i>Add Custom Subject
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addCustomCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_custom_course">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">University</label>
                                <input type="text" class="form-control" name="university" value="<?php echo htmlspecialchars($selected_university); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course</label>
                                <input type="text" class="form-control" name="course" value="<?php echo htmlspecialchars($selected_course); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <input type="number" class="form-control" name="semester" min="1" max="16" value="<?php echo $semester; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" placeholder="Subject name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Outline</label>
                            <textarea class="form-control" name="outline" rows="6" placeholder="Paste or type the subject outline" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Custom Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        const courseMap = <?php echo json_encode(array_reduce($courses, function ($items, $course) {
            $items[$course['university']][] = $course['course'];
            return $items;
        }, [])); ?>;
        const universitySelect = document.getElementById('universitySelect');
        const courseSelect = document.getElementById('courseSelect');

        universitySelect.addEventListener('change', () => {
            const courses = [...new Set(courseMap[universitySelect.value] || [])].sort();
            courseSelect.innerHTML = courses.map(course => `<option value="${escapeHtml(course)}">${escapeHtml(course)}</option>`).join('');
        });

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }
    </script>
</body>
</html>
