<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ai_learning_assistant');

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
            if (!$this->conn->query($sql)) {
                throw new Exception("Error creating database: " . $this->conn->error);
            }
            
            $this->conn->select_db(DB_NAME);
            $this->createTables();
            
        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
                profile_picture VARCHAR(255) DEFAULT NULL,
                institution VARCHAR(255) DEFAULT NULL,
                theme ENUM('light', 'dark') DEFAULT 'light',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS study_plans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                goal TEXT,
                status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS study_goals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                goal_title VARCHAR(255) NOT NULL,
                target_date DATE NOT NULL,
                progress INT DEFAULT 0,
                status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS chat_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                question TEXT NOT NULL,
                answer TEXT NOT NULL,
                subject VARCHAR(100),
                bookmarked BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS quizzes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
                total_questions INT NOT NULL,
                score INT DEFAULT NULL,
                completed BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS quiz_questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                quiz_id INT NOT NULL,
                question TEXT NOT NULL,
                question_type ENUM('mcq', 'true_false', 'short_answer') NOT NULL,
                options JSON,
                correct_answer TEXT NOT NULL,
                user_answer TEXT DEFAULT NULL,
                is_correct BOOLEAN DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) DEFAULT 'info',
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                description TEXT,
                due_date DATE NOT NULL,
                priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                status ENUM('pending', 'submitted', 'late') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS study_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                content TEXT NOT NULL,
                tags VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS custom_course_outlines (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                university VARCHAR(100) NOT NULL,
                course VARCHAR(100) NOT NULL,
                semester INT NOT NULL,
                subject VARCHAR(150) NOT NULL,
                outline TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS user_progress (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                subject VARCHAR(100) NOT NULL,
                study_hours DECIMAL(10,2) DEFAULT 0,
                quizzes_taken INT DEFAULT 0,
                average_score DECIMAL(5,2) DEFAULT 0,
                streak_days INT DEFAULT 0,
                last_activity DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )"
        ];
        
        foreach ($tables as $sql) {
            if (!$this->conn->query($sql)) {
                throw new Exception("Error creating table: " . $this->conn->error);
            }
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>
