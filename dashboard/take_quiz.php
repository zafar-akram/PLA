<?php
require_once '../config/config.php';
requireLogin();

$quiz_id = intval($_GET['id'] ?? 0);

if (!$quiz_id) {
    header('Location: quizzes.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $_SESSION['user_id']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: quizzes.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - AI Learning Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['subject']); ?> - <?php echo ucfirst($quiz['difficulty']); ?> Level</p>
            </div>
            <div>
                <span class="badge bg-primary fs-6">Question <span id="currentQuestion">1</span> of <?php echo count($questions); ?></span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div id="quizContainer">
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="quiz-question" data-question="<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>">
                                    <h5 class="mb-4">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h5>
                                    <p class="fs-5 mb-4"><?php echo htmlspecialchars($question['question']); ?></p>
                                    
                                    <?php 
                                    $options = json_decode($question['options'], true);
                                    foreach ($options as $opt_index => $option): 
                                    ?>
                                        <div class="quiz-option" data-question-id="<?php echo $question['id']; ?>" data-answer="<?php echo htmlspecialchars($option); ?>">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_<?php echo $opt_index; ?>" value="<?php echo htmlspecialchars($option); ?>">
                                                <label class="form-check-label w-100" for="q<?php echo $question['id']; ?>_<?php echo $opt_index; ?>">
                                                    <?php echo htmlspecialchars($option); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-primary" id="prevBtn" disabled>
                                <i class="bi bi-arrow-left me-2"></i>Previous
                            </button>
                            <button class="btn btn-primary" id="nextBtn">
                                Next<i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="bi bi-check-circle me-2"></i>Submit Quiz
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        let currentQuestion = 0;
        const totalQuestions = <?php echo count($questions); ?>;
        const answers = {};

        const questions = document.querySelectorAll('.quiz-question');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const currentQuestionSpan = document.getElementById('currentQuestion');

        document.querySelectorAll('.quiz-option').forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                const questionId = this.dataset.questionId;
                const answer = this.dataset.answer;
                answers[questionId] = answer;
                
                this.closest('.quiz-question').querySelectorAll('.quiz-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });

        function showQuestion(index) {
            questions.forEach((q, i) => {
                q.style.display = i === index ? 'block' : 'none';
            });
            
            currentQuestion = index;
            currentQuestionSpan.textContent = index + 1;
            
            prevBtn.disabled = index === 0;
            
            if (index === totalQuestions - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            }
        }

        prevBtn.addEventListener('click', () => {
            if (currentQuestion > 0) {
                showQuestion(currentQuestion - 1);
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentQuestion < totalQuestions - 1) {
                showQuestion(currentQuestion + 1);
            }
        });

        submitBtn.addEventListener('click', async () => {
            if (Object.keys(answers).length < totalQuestions) {
                if (!confirm('You have not answered all questions. Submit anyway?')) {
                    return;
                }
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Submitting...';

            try {
                const response = await fetch('../api/submit_quiz.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        quiz_id: <?php echo $quiz_id; ?>,
                        answers: answers
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'quiz_result.php?id=<?php echo $quiz_id; ?>';
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Submit Quiz';
                }
            } catch (error) {
                alert('Error submitting quiz');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Submit Quiz';
            }
        });
    </script>
</body>
</html>
