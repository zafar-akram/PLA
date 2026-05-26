<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$user = getUserData();

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bscs_courses = getBscsCourseOutlines();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaptive Quizzes - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Adaptive Quizzes</h4>
                <p class="text-muted mb-0">Test your knowledge with AI-generated quizzes</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                    <i class="bi bi-plus-circle me-2"></i>Create New Quiz
                </button>
                <?php renderTopActions($user); ?>
            </div>
        </div>

        <div class="row g-4">
            <?php if (count($quizzes) > 0): ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="content-icon">
                                        <i class="bi bi-clipboard-check text-primary fs-4"></i>
                                    </div>
                                    <span class="badge bg-<?php echo $quiz['difficulty'] === 'easy' ? 'success' : ($quiz['difficulty'] === 'medium' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($quiz['difficulty']); ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($quiz['subject']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-question-circle me-1"></i><?php echo $quiz['total_questions']; ?> Questions
                                    </small>
                                    <?php if ($quiz['completed']): ?>
                                        <small class="text-success fw-bold">
                                            <i class="bi bi-check-circle me-1"></i>Score: <?php echo $quiz['score']; ?>%
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($quiz['completed']): ?>
                                    <div class="d-grid gap-2">
                                        <a href="quiz_result.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-primary">
                                            <i class="bi bi-eye me-2"></i>View Results
                                        </a>
                                        <button type="button" class="btn btn-primary retake-quiz-btn" data-quiz-id="<?php echo $quiz['id']; ?>">
                                            <i class="bi bi-arrow-repeat me-2"></i>Retake New Quiz
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle me-2"></i>Start Quiz
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                            <h5 class="mt-3">No Quizzes Yet</h5>
                            <p class="text-muted">Create your first quiz to start testing your knowledge</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                                <i class="bi bi-plus-circle me-2"></i>Create Quiz
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="createQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createQuizForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">University</label>
                                <select class="form-select" name="university" id="quizUniversity">
                                    <option value="GCUF" selected>GCUF</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course</label>
                                <select class="form-select" name="course" id="quizCourse">
                                    <option value="BSCS" selected>BSCS</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" id="quizSemester">
                                    <?php for ($semester = 1; $semester <= 8; $semester++): ?>
                                        <option value="<?php echo $semester; ?>">Semester <?php echo $semester; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select" id="quizSubjectSelect"></select>
                            <input type="text" class="form-control d-none mt-2" id="quizCustomSubject" placeholder="Custom subject name">
                            <input type="hidden" name="subject" id="quizSubjectInput">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Outline</label>
                            <textarea class="form-control" name="outline" id="quizOutline" rows="5" placeholder="Subject outline will load automatically for BSCS"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Special Quiz Requirements</label>
                            <textarea class="form-control" name="special_features" rows="3" placeholder="e.g., more conceptual questions, include numerical problems, exam-style, focus on weak topics"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" name="difficulty" required>
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Questions</label>
                            <select class="form-select" name="total_questions" required>
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
                            <i class="bi bi-magic me-2"></i>Generate Quiz
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
        const quizUniversity = document.getElementById('quizUniversity');
        const quizCourse = document.getElementById('quizCourse');
        const quizSemester = document.getElementById('quizSemester');
        const quizSubjectSelect = document.getElementById('quizSubjectSelect');
        const quizCustomSubject = document.getElementById('quizCustomSubject');
        const quizSubjectInput = document.getElementById('quizSubjectInput');
        const quizOutline = document.getElementById('quizOutline');

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function populateQuizSubjects() {
            const semester = Number(quizSemester.value);
            const filteredCourses = bscsCourses.filter(course => Number(course.semester) === semester);
            quizSubjectSelect.innerHTML = '<option value="">Select subject</option>' + filteredCourses.map(course => {
                return `<option value="${escapeHtml(course.name)}">${escapeHtml(course.name)}</option>`;
            }).join('');
        }

        function updateQuizMode() {
            const isCustom = quizUniversity.value === 'Custom' || quizCourse.value === 'Custom';
            quizSubjectSelect.classList.toggle('d-none', isCustom);
            quizCustomSubject.classList.toggle('d-none', !isCustom);
            quizSemester.disabled = isCustom;
            if (isCustom) {
                quizOutline.placeholder = 'Paste the outline for this custom subject';
            } else {
                quizCustomSubject.value = '';
                quizOutline.placeholder = 'Subject outline will load automatically for BSCS';
            }
        }

        quizSubjectSelect.addEventListener('change', () => {
            const course = bscsCourses.find(item => item.name === quizSubjectSelect.value);
            quizOutline.value = course ? course.outline : '';
        });

        quizUniversity.addEventListener('change', updateQuizMode);
        quizCourse.addEventListener('change', updateQuizMode);
        quizSemester.addEventListener('change', () => {
            populateQuizSubjects();
            quizOutline.value = '';
        });
        populateQuizSubjects();
        updateQuizMode();

        document.getElementById('createQuizForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const subject = (quizUniversity.value === 'Custom' || quizCourse.value === 'Custom')
                ? quizCustomSubject.value.trim()
                : quizSubjectSelect.value.trim();

            if (!subject) {
                alert('Please select a subject');
                return;
            }

            quizSubjectInput.value = subject;
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Generating...';
            
            try {
                const response = await fetch('../api/create_quiz.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-magic me-2"></i>Generate Quiz';
                }
            } catch (error) {
                alert('Error creating quiz');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-magic me-2"></i>Generate Quiz';
            }
        });

        document.querySelectorAll('.retake-quiz-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="loading-spinner"></span> Creating...';

                try {
                    const response = await fetch('../api/retake_quiz.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ quiz_id: button.dataset.quizId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        window.location.href = `take_quiz.php?id=${data.data.quiz_id}`;
                    } else {
                        alert(data.message);
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
                } catch (error) {
                    alert('Error creating retake quiz');
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            });
        });
    </script>
</body>
</html>
