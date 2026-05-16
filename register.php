<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Assistant - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card register-card">
            <div class="auth-header">
                <i class="bi bi-mortarboard-fill"></i>
                <h2>Create Account</h2>
                <p>Join us and start your learning journey</p>
            </div>
            <form id="registerForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-envelope"></i> Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-lock"></i> Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-lock-fill"></i> Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person-badge"></i> User Role</label>
                            <select class="form-control" name="role" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-building"></i> Institution Name</label>
                            <input type="text" class="form-control" name="institution" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-image"></i> Profile Picture (Optional)</label>
                    <input type="file" class="form-control" name="profile_picture" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>
            <div class="auth-footer">
                <p>Already have an account? <a href="index.php">Sign In</a></p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
