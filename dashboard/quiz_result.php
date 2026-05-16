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

if (!$quiz || !$quiz['completed']) {
    header('Location: quizzes.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$correct_count = 0;
foreach ($questions as $q) {
    if ($q['is_correct']) $correct_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - AI Learning Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold">Quiz Results</h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['title']); ?></p>
            </div>
            <div>
                <a href="quizzes.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Quizzes
                </a>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <?php if ($quiz['score'] >= 80): ?>
                                <i class="bi bi-trophy-fill text-warning" style="font-size: 5rem;"></i>
                            <?php elseif ($quiz['score'] >= 60): ?>
                                <i class="bi bi-emoji-smile-fill text-success" style="font-size: 5rem;"></i>
                            <?php else: ?>
                                <i class="bi bi-emoji-neutral-fill text-warning" style="font-size: 5rem;"></i>
                            <?php endif; ?>
                        </div>
                        <h2 class="fw-bold mb-3">Your Score: <?php echo number_format($quiz['score'], 0); ?>%</h2>
                        <p class="text-muted fs-5 mb-4">You got <?php echo $correct_count; ?> out of <?php echo count($questions); ?> questions correct</p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="bg-success bg-opacity-10 rounded p-3">
                                    <h4 class="text-success mb-0"><?php echo $correct_count; ?></h4>
                                    <small class="text-muted">Correct</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-danger bg-opacity-10 rounded p-3">
                                    <h4 class="text-danger mb-0"><?php echo count($questions) - $correct_count; ?></h4>
                                    <small class="text-muted">Incorrect</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <h4 class="text-primary mb-0"><?php echo count($questions); ?></h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>

                        <?php if ($quiz['score'] >= 80): ?>
                            <p class="text-success fw-bold">Excellent work! You have a strong understanding of this topic.</p>
                        <?php elseif ($quiz['score'] >= 60): ?>
                            <p class="text-warning fw-bold">Good job! Review the incorrect answers to improve further.</p>
                        <?php else: ?>
                            <p class="text-danger fw-bold">Keep practicing! Review the material and try again.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Detailed Results</h5>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-4 pb-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="fw-bold">Question <?php echo $index + 1; ?></h6>
                                    <?php if ($question['is_correct']): ?>
                                        <span class="badge bg-success">Correct</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Incorrect</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-3"><?php echo htmlspecialchars($question['question']); ?></p>
                                
                                <?php $options = json_decode($question['options'], true); ?>
                                <?php foreach ($options as $option): ?>
                                    <div class="quiz-option mb-2 <?php 
                                        if ($option === $question['correct_answer']) {
                                            echo 'correct';
                                        } elseif ($option === $question['user_answer'] && !$question['is_correct']) {
                                            echo 'incorrect';
                                        }
                                    ?>">
                                        <div class="d-flex align-items-center">
                                            <span><?php echo htmlspecialchars($option); ?></span>
                                            <?php if ($option === $question['correct_answer']): ?>
                                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                            <?php elseif ($option === $question['user_answer'] && !$question['is_correct']): ?>
                                                <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
