<?php
session_start();

define('BASE_URL', 'http://localhost:8080/PLA/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

date_default_timezone_set('UTC');

require_once __DIR__ . '/database.php';

$db = new Database();
$conn = $db->getConnection();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

function getUserData() {
    global $conn;
    if (!isLoggedIn()) return null;
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
?>
