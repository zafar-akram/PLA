<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$user = getUserData();
$bscs_courses = getBscsCourseOutlines();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Resources - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">AI Learning Resources</h4>
                <p class="text-muted mb-0">Generate subject-specific study guides, practice packs, and revision resources</p>
            </div>
            <?php renderTopActions($user ?? null); ?>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Resource Builder</h5>
                        <form id="resourceForm">
                            <div class="mb-3">
                                <label class="form-label">University</label>
                                <select class="form-select" name="university" id="resourceUniversity">
                                    <option value="GCUF" selected>GCUF</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Course</label>
                                <select class="form-select" name="course" id="resourceCourse">
                                    <option value="BSCS" selected>BSCS</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" id="resourceSemester">
                                    <?php for ($semester = 1; $semester <= 8; $semester++): ?>
                                        <option value="<?php echo $semester; ?>">Semester <?php echo $semester; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select" id="resourceSubjectSelect"></select>
                                <input type="text" class="form-control mt-2 d-none" id="resourceCustomSubject" placeholder="Enter custom subject">
                                <input type="hidden" name="subject" id="resourceSubjectInput">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outline</label>
                                <textarea class="form-control" name="outline" id="resourceOutline" rows="6" required placeholder="Subject outline will load automatically"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Resource Type</label>
                                <select class="form-select" name="resource_type">
                                    <option value="study_guide" selected>Study Guide</option>
                                    <option value="video_plan">Video Learning Roadmap</option>
                                    <option value="practice">Practice Set</option>
                                    <option value="notes">Notes & Summaries</option>
                                    <option value="web_resources">Web Resource List</option>
                                    <option value="exam_revision">Exam Revision Pack</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <select class="form-select" name="level">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate" selected>Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="exam-focused">Exam-focused</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Goal</label>
                                <textarea class="form-control" name="goal" rows="3" placeholder="e.g., prepare for midterm, understand weak topics, make short notes"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-magic me-2"></i>Generate Resources
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <button class="resource-preset card border-0 shadow-sm text-start w-100 h-100" data-type="study_guide">
                            <div class="card-body">
                                <i class="bi bi-book text-primary fs-3"></i>
                                <h6 class="fw-bold mt-3">Study Guide</h6>
                                <small class="text-muted">Concepts, sequence, and learning path</small>
                            </div>
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="resource-preset card border-0 shadow-sm text-start w-100 h-100" data-type="practice">
                            <div class="card-body">
                                <i class="bi bi-pencil-square text-warning fs-3"></i>
                                <h6 class="fw-bold mt-3">Practice Pack</h6>
                                <small class="text-muted">Problems, tasks, and self-checks</small>
                            </div>
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="resource-preset card border-0 shadow-sm text-start w-100 h-100" data-type="exam_revision">
                            <div class="card-body">
                                <i class="bi bi-award text-success fs-3"></i>
                                <h6 class="fw-bold mt-3">Exam Revision</h6>
                                <small class="text-muted">Important topics and exam tips</small>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Generated Resource</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="saveResourceNoteBtn" disabled>
                                <i class="bi bi-journal-plus me-1"></i>Save as Note
                            </button>
                        </div>
                        <div id="resourceOutput" class="formatted-response">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-stars fs-1"></i>
                                <p class="mt-3 mb-0">Select a subject and generate AI learning resources.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        const bscsCourses = <?php echo json_encode($bscs_courses); ?>;
        const resourceUniversity = document.getElementById('resourceUniversity');
        const resourceCourse = document.getElementById('resourceCourse');
        const resourceSemester = document.getElementById('resourceSemester');
        const resourceSubjectSelect = document.getElementById('resourceSubjectSelect');
        const resourceCustomSubject = document.getElementById('resourceCustomSubject');
        const resourceSubjectInput = document.getElementById('resourceSubjectInput');
        const resourceOutline = document.getElementById('resourceOutline');
        const resourceOutput = document.getElementById('resourceOutput');
        const saveResourceNoteBtn = document.getElementById('saveResourceNoteBtn');
        let latestGeneratedResource = null;

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function populateResourceSubjects() {
            const semester = Number(resourceSemester.value);
            const filteredCourses = bscsCourses.filter(course => Number(course.semester) === semester);
            resourceSubjectSelect.innerHTML = '<option value="">Select subject</option>' + filteredCourses.map(course => {
                return `<option value="${escapeHtml(course.name)}">${escapeHtml(course.name)}</option>`;
            }).join('');
        }

        function isCustomMode() {
            return resourceUniversity.value === 'Custom' || resourceCourse.value === 'Custom';
        }

        function updateResourceMode() {
            const custom = isCustomMode();
            resourceSubjectSelect.classList.toggle('d-none', custom);
            resourceCustomSubject.classList.toggle('d-none', !custom);
            resourceSemester.disabled = custom;
            if (custom) {
                resourceOutline.placeholder = 'Paste your custom subject outline';
            } else {
                resourceCustomSubject.value = '';
                resourceOutline.placeholder = 'Subject outline will load automatically';
            }
        }

        resourceSubjectSelect.addEventListener('change', () => {
            const course = bscsCourses.find(item => item.name === resourceSubjectSelect.value);
            resourceOutline.value = course ? course.outline : '';
        });

        resourceUniversity.addEventListener('change', updateResourceMode);
        resourceCourse.addEventListener('change', updateResourceMode);
        resourceSemester.addEventListener('change', () => {
            populateResourceSubjects();
            resourceOutline.value = '';
        });

        document.querySelectorAll('.resource-preset').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelector('[name="resource_type"]').value = button.dataset.type;
                document.getElementById('resourceForm').requestSubmit();
            });
        });

        document.getElementById('resourceForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const subject = isCustomMode() ? resourceCustomSubject.value.trim() : resourceSubjectSelect.value.trim();

            if (!subject) {
                alert('Please select or enter a subject');
                return;
            }

            if (!resourceOutline.value.trim()) {
                alert('Please provide an outline');
                return;
            }

            resourceSubjectInput.value = subject;
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Generating...';
            latestGeneratedResource = null;
            saveResourceNoteBtn.disabled = true;
            saveResourceNoteBtn.innerHTML = '<i class="bi bi-journal-plus me-1"></i>Save as Note';
            resourceOutput.innerHTML = '<div class="text-center py-5 text-muted"><span class="loading-spinner"></span><p class="mt-3 mb-0">AI is preparing resources...</p></div>';

            try {
                const formData = new FormData(event.target);
                const payload = Object.fromEntries(formData.entries());
                const response = await fetch('../api/generate_resources.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (data.success) {
                    resourceOutput.innerHTML = data.data.html;
                    latestGeneratedResource = {
                        title: `${payload.subject} - ${document.querySelector('[name="resource_type"] option:checked').textContent}`,
                        subject: payload.subject,
                        content: data.data.html,
                        tags: `AI Resource, ${payload.resource_type}, ${payload.level}`
                    };
                    saveResourceNoteBtn.disabled = false;
                } else {
                    alert(data.message);
                    resourceOutput.innerHTML = '<div class="text-center py-5 text-muted">Unable to generate resources.</div>';
                }
            } catch (error) {
                alert('Error generating resources');
                resourceOutput.innerHTML = '<div class="text-center py-5 text-muted">Unable to generate resources.</div>';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });

        saveResourceNoteBtn.addEventListener('click', async () => {
            if (!latestGeneratedResource) {
                alert('Generate a resource first');
                return;
            }

            const originalHtml = saveResourceNoteBtn.innerHTML;
            saveResourceNoteBtn.disabled = true;
            saveResourceNoteBtn.innerHTML = '<span class="loading-spinner"></span> Saving...';

            try {
                const response = await fetch('../api/save_resource_note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(latestGeneratedResource)
                });
                const data = await response.json();

                if (data.success) {
                    saveResourceNoteBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Saved';
                } else {
                    alert(data.message);
                    saveResourceNoteBtn.disabled = false;
                    saveResourceNoteBtn.innerHTML = originalHtml;
                }
            } catch (error) {
                alert('Error saving note');
                saveResourceNoteBtn.disabled = false;
                saveResourceNoteBtn.innerHTML = originalHtml;
            }
        });

        populateResourceSubjects();
        updateResourceMode();
    </script>
</body>
</html>
