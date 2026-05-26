<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$courses = getBscsCourseOutlines();
$semester = intval($_GET['semester'] ?? 1);
$semester = max(1, min(8, $semester));
$semester_courses = array_values(array_filter($courses, function ($course) use ($semester) {
    return intval($course['semester']) === $semester;
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
                <p class="text-muted mb-0">GCUF BSCS outline with semester-wise subjects</p>
            </div>
            <div class="topbar-action-group">
                <select class="form-select" id="semesterSelect">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i === $semester ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <?php renderTopActions($user ?? null); ?>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">University</small>
                        <h4 class="fw-bold mb-0">GCUF</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body">
                        <small class="text-muted">Course</small>
                        <h4 class="fw-bold mb-0">BSCS</h4>
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
            <?php foreach ($semester_courses as $index => $course): ?>
                <div class="accordion-item border-0 shadow-sm mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#course<?php echo $index; ?>">
                            <span class="fw-bold"><?php echo htmlspecialchars($course['name']); ?></span>
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
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        document.getElementById('semesterSelect').addEventListener('change', (event) => {
            window.location.href = `courses.php?semester=${event.target.value}`;
        });
    </script>
</body>
</html>
