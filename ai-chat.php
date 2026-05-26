<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require_once 'config/database.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant - AI Learning Assistant</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="main-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="chat-container">
                <div class="chat-header">
                    <div class="chat-title">
                        <i class="bi bi-robot"></i>
                        <h3>AI Learning Assistant</h3>
                    </div>
                    <button class="btn btn-outline-primary" id="chatHistoryBtn">
                        <i class="bi bi-clock-history"></i> Chat History
                    </button>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="message ai-message">
                        <div class="message-avatar">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="message-content">
                            <p>Hello! I'm your AI learning assistant. How can I help you with your studies today?</p>
                        </div>
                    </div>
                </div>
                <div class="chat-input-container">
                    <form id="chatForm">
                        <div class="chat-input-wrapper">
                            <input type="text" class="form-control" id="chatInput" placeholder="Ask me anything about your studies..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-fill"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="chatHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clock-history"></i> Chat History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="chatHistoryList"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/ai-chat.js"></script>
</body>
</html>
