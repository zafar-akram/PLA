<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaptive Quizzes - AI Learning Assistant</title>
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
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                    <i class="bi bi-plus-circle me-2"></i>Create New Quiz
                </button>
            </div>
        </div>

        <div class="row g-4">
            <?php if (count($quizzes) > 0): ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
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
                                    <a href="quiz_result.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-eye me-2"></i>View Results
                                    </a>
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
        <div class="modal-dialog">
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
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="subject" placeholder="e.g., Mathematics, Science, History" required>
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
        document.getElementById('createQuizForm').addEventListener('submit', async (e) => {
            e.preventDefault();
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
    </script>
</body>
</html>
