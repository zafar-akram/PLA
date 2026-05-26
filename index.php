<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Assistant - Home</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-mortarboard-fill text-primary fs-3 me-2"></i>
                <span class="fw-bold">AI Learning Assistant</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary ms-2 px-4" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ms-2 px-4" href="auth/register.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Transform Your Learning Journey with AI</h1>
                    <p class="lead  mb-4">Personalized study plans, instant doubt resolution, and adaptive quizzes powered by artificial intelligence.</p>
                    <div class="d-flex gap-3">
                        <a href="auth/register.php" class="btn btn-primary btn-lg px-5">Get Started</a>
                        <a href="#features" class="btn btn-outline-primary btn-lg px-5">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/hero-illustration.svg" alt="Learning" class="img-fluid" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Powerful Features</h2>
                <p class="text-muted">Everything you need for effective learning</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-calendar-check text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">Personalized Study Planner</h5>
                            <p class="card-text text-muted">Create custom study schedules with AI-powered recommendations based on your goals and learning pace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-chat-dots text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">AI Doubt Resolution</h5>
                            <p class="card-text text-muted">Get instant answers to your questions with detailed explanations and examples from our AI tutor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-clipboard-check text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">Adaptive Quizzes</h5>
                            <p class="card-text text-muted">Take personalized quizzes that adapt to your skill level and track your progress over time.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-graph-up text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">Progress Analytics</h5>
                            <p class="card-text text-muted">Monitor your learning journey with detailed analytics and performance insights.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-bell text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">Smart Reminders</h5>
                            <p class="card-text text-muted">Never miss a study session with intelligent notifications and reminders.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-book text-primary fs-2"></i>
                            </div>
                            <h5 class="card-title fw-bold">Learning Resources</h5>
                            <p class="card-text text-muted">Access curated learning materials and resources tailored to your subjects.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Why Choose AI Learning Assistant?</h2>
                    <p class="text-muted mb-4">Our platform combines cutting-edge AI technology with proven learning methodologies to help students achieve their academic goals efficiently.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Personalized learning experience</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> 24/7 AI tutor availability</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Adaptive difficulty levels</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Comprehensive progress tracking</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Multi-subject support</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-5">
                            <h4 class="fw-bold mb-4">Start Your Journey Today</h4>
                            <p class="text-muted mb-4">Join thousands of students who are already improving their learning outcomes with AI assistance.</p>
                            <a href="auth/register.php" class="btn btn-primary btn-lg w-100">Create Free Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3">AI Learning Assistant</h5>
                    <p class="text-muted">Empowering students with AI-driven learning solutions.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2025 AI Learning Assistant. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
