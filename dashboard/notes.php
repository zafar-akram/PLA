<?php
require_once '../config/config.php';
require_once '../config/course_outlines.php';
requireLogin();

$courses = getBscsCourseOutlines();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? 'add');
    if ($action === 'add') {
        $title = sanitize($_POST['title'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $custom_subject = sanitize($_POST['custom_subject'] ?? '');
        $outline = sanitize($_POST['outline'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $tags = sanitize($_POST['tags'] ?? '');
        if ($subject === 'Custom') {
            $subject = $custom_subject;
        }
        if ($outline !== '') {
            $content = "Outline:\n" . $outline . "\n\nNotes:\n" . $content;
        }
        if ($title && $subject && $content) {
            $stmt = $conn->prepare("INSERT INTO study_notes (user_id, title, subject, content, tags) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $_SESSION['user_id'], $title, $subject, $content, $tags);
            $stmt->execute();
        }
    } elseif ($action === 'delete') {
        $note_id = intval($_POST['note_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM study_notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $_SESSION['user_id']);
        $stmt->execute();
    }

    header('Location: notes.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM study_notes WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function renderNoteContent($content) {
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
    $allowed = '<h5><h6><p><ul><ol><li><strong><em><code><pre><br><hr><table><thead><tbody><tr><th><td>';
    $content = strip_tags($content, $allowed);

    if ($content === strip_tags($content)) {
        return nl2br(htmlspecialchars($content));
    }

    return $content;
}

function notePreview($content) {
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(strip_tags($content));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Notes - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">Study Notes</h4>
                <p class="text-muted mb-0">Create subject-wise notes and quick summaries</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#noteModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Note
                </button>
                <?php renderTopActions($user ?? null); ?>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($notes as $note): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($note['subject']); ?></span>
                                <form method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                            <h5 class="fw-bold"><?php echo htmlspecialchars($note['title']); ?></h5>
                            <?php $preview = notePreview($note['content']); ?>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($preview, 0, 220)); ?><?php echo strlen($preview) > 220 ? '...' : ''; ?></p>
                            <?php if ($note['tags']): ?>
                                <small class="text-primary"><i class="bi bi-tags me-1"></i><?php echo htmlspecialchars($note['tags']); ?></small>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-primary w-100 mt-3 view-note-btn" data-bs-toggle="modal" data-bs-target="#viewNoteModal" data-title="<?php echo htmlspecialchars($note['title']); ?>" data-subject="<?php echo htmlspecialchars($note['subject']); ?>" data-content-id="noteContent<?php echo $note['id']; ?>">
                                <i class="bi bi-eye me-2"></i>View Note
                            </button>
                            <template id="noteContent<?php echo $note['id']; ?>"><?php echo renderNoteContent($note['content']); ?></template>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($notes) === 0): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5 text-muted">No notes yet</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="viewNoteModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="viewNoteTitle">View Note</h5>
                        <small class="text-muted" id="viewNoteSubject"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewNoteContent" class="formatted-response"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="noteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Study Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select custom-subject-select" name="subject" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['name']); ?>"><?php echo htmlspecialchars('S' . $course['semester'] . ' - ' . $course['name']); ?></option>
                                    <?php endforeach; ?>
                                    <option value="Custom">Custom Subject</option>
                                </select>
                                <input class="form-control mt-2 custom-subject-input d-none" name="custom_subject" placeholder="Enter custom subject">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Outline</label>
                            <textarea class="form-control" name="outline" rows="3" placeholder="Paste outline or topics for these notes"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="8" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input class="form-control" name="tags" placeholder="e.g., midterm, important, formulas">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        document.querySelectorAll('.custom-subject-select').forEach(select => {
            select.addEventListener('change', () => {
                const input = select.closest('.col-md-6').querySelector('.custom-subject-input');
                if (input) {
                    input.classList.toggle('d-none', select.value !== 'Custom');
                    input.required = select.value === 'Custom';
                }
            });
        });

        document.querySelectorAll('.view-note-btn').forEach(button => {
            button.addEventListener('click', () => {
                const template = document.getElementById(button.dataset.contentId);
                document.getElementById('viewNoteTitle').textContent = button.dataset.title;
                document.getElementById('viewNoteSubject').textContent = button.dataset.subject;
                document.getElementById('viewNoteContent').innerHTML = template ? template.innerHTML : '';
            });
        });
    </script>
</body>
</html>
