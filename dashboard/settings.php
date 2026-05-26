<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $institution = sanitize($_POST['institution']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, institution = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $institution, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success = 'Settings updated successfully';
        $_SESSION['user_name'] = $full_name;
        $user = getUserData();
    } else {
        $error = 'Failed to update settings';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Settings</h4>
                <p class="text-muted mb-0">Manage your account preferences</p>
            </div>
            <?php renderTopActions($user); ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Profile Information</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Institution/School Name</label>
                                <input type="text" class="form-control" name="institution" value="<?php echo htmlspecialchars($user['institution']); ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Appearance</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Theme</h6>
                                <p class="text-muted mb-0">Switch between light and dark mode</p>
                            </div>
                            <div>
                                <i class="bi bi-moon-fill fs-4 theme-toggle" id="themeToggle" style="cursor: pointer;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm border-danger">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-danger">Danger Zone</h5>
                        <p class="text-muted mb-3">Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
