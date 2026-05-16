<?php
require_once '../config/config.php';
requireLogin();

$user = getUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Resources - AI Learning Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h4 class="mb-0 fw-bold">Learning Resources</h4>
                <p class="text-muted mb-0">Curated materials to enhance your learning</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-primary bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-book text-primary fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Study Guides</h5>
                        <p class="text-muted">Comprehensive guides covering various subjects and topics</p>
                        <a href="#" class="btn btn-outline-primary w-100">Browse Guides</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-success bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-play-circle text-success fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Video Tutorials</h5>
                        <p class="text-muted">Watch educational videos to understand complex concepts</p>
                        <a href="#" class="btn btn-outline-success w-100">Watch Videos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-warning bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-file-earmark-text text-warning fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Practice Papers</h5>
                        <p class="text-muted">Test your knowledge with practice questions and papers</p>
                        <a href="#" class="btn btn-outline-warning w-100">View Papers</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-info bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-link-45deg text-info fs-1"></i>
                        </div>
                        <h5 class="fw-bold">External Resources</h5>
                        <p class="text-muted">Curated links to helpful external learning platforms</p>
                        <a href="#" class="btn btn-outline-info w-100">Explore Links</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-danger bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-journal-text text-danger fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Notes & Summaries</h5>
                        <p class="text-muted">Quick reference notes and topic summaries</p>
                        <a href="#" class="btn btn-outline-danger w-100">View Notes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="bg-secondary bg-opacity-10 rounded p-3 mb-3 text-center">
                            <i class="bi bi-lightbulb text-secondary fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Tips & Tricks</h5>
                        <p class="text-muted">Study tips and learning strategies for better results</p>
                        <a href="#" class="btn btn-outline-secondary w-100">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
