<?php
require_once '../config/config.php';
require_once '../config/ai_config.php';
requireLogin();

$user = getUserData();

function formatAIResponse($text) {
    $text = htmlspecialchars($text);
    
    $text = preg_replace('/#{4,}\s*(\d+)\.\s*/', '<strong>$1.</strong> ', $text);
    $text = preg_replace('/#{4,}\s*/', '', $text);
    
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<!\*)\*(?!\*)([^\*]+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    
    $text = preg_replace('/^######\s+(.*?)$/m', '<h6 class="mt-2 mb-1 fw-bold">$1</h6>', $text);
    $text = preg_replace('/^#####\s+(.*?)$/m', '<h6 class="mt-2 mb-1 fw-bold">$1</h6>', $text);
    $text = preg_replace('/^####\s+(.*?)$/m', '<h5 class="mt-3 mb-2 fw-bold text-primary">$1</h5>', $text);
    $text = preg_replace('/^###\s+(.*?)$/m', '<h5 class="mt-3 mb-2 fw-bold text-primary">$1</h5>', $text);
    $text = preg_replace('/^##\s+(.*?)$/m', '<h4 class="mt-3 mb-2 fw-bold text-primary">$1</h4>', $text);
    $text = preg_replace('/^#\s+(.*?)$/m', '<h3 class="mt-3 mb-2 fw-bold text-primary">$1</h3>', $text);
    
    $text = preg_replace_callback('/```(.*?)```/s', function($matches) {
        return '<pre class="bg-dark text-white p-3 rounded mt-2 mb-2"><code>' . trim($matches[1]) . '</code></pre>';
    }, $text);
    
    $text = preg_replace('/---+/', '<hr class="my-3">', $text);
    
    $lines = explode("\n", $text);
    $inList = false;
    $inTable = false;
    $formatted = [];
    $tableRows = [];
    
    foreach ($lines as $line) {
        $originalLine = $line;
        $line = trim($line);
        
        if (preg_match('/^\|(.+)\|$/', $line)) {
            if (!$inTable) {
                $inTable = true;
                $tableRows = [];
            }
            $tableRows[] = $line;
            continue;
        } else {
            if ($inTable) {
                $formatted[] = formatTable($tableRows);
                $inTable = false;
                $tableRows = [];
            }
        }
        
        if (preg_match('/^[\*\-\+]\s+(.+)$/', $line, $matches)) {
            if (!$inList) {
                $formatted[] = '<ul class="mt-2 mb-2 ps-4">';
                $inList = 'ul';
            }
            $formatted[] = '<li class="mb-1">' . $matches[1] . '</li>';
        } elseif (preg_match('/^(\d+)\.\s+(.+)$/', $line, $matches)) {
            if ($inList !== 'ol') {
                if ($inList) {
                    $formatted[] = '</ul>';
                }
                $formatted[] = '<ol class="mt-2 mb-2 ps-4">';
                $inList = 'ol';
            }
            $formatted[] = '<li class="mb-1">' . $matches[2] . '</li>';
        } else {
            if ($inList) {
                $formatted[] = $inList === 'ol' ? '</ol>' : '</ul>';
                $inList = false;
            }
            if (!empty($line) && !preg_match('/<h[3-6]/', $line) && !preg_match('/<hr/', $line) && !preg_match('/<pre/', $line)) {
                $formatted[] = '<p class="mb-2">' . $line . '</p>';
            } elseif (!empty($line)) {
                $formatted[] = $line;
            }
        }
    }
    
    if ($inList) {
        $formatted[] = $inList === 'ol' ? '</ol>' : '</ul>';
    }
    
    if ($inTable) {
        $formatted[] = formatTable($tableRows);
    }
    
    return implode("\n", $formatted);
}

function formatTable($rows) {
    if (empty($rows)) return '';
    
    $html = '<div class="table-responsive mt-3 mb-3"><table class="table table-bordered table-striped">';
    
    $isFirstRow = true;
    foreach ($rows as $row) {
        if (preg_match('/^\|[\s\-:]+\|$/', $row)) {
            continue;
        }
        
        $cells = array_map('trim', explode('|', trim($row, '|')));
        
        if ($isFirstRow) {
            $html .= '<thead class="table-primary"><tr>';
            foreach ($cells as $cell) {
                $html .= '<th>' . $cell . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            $isFirstRow = false;
        } else {
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody></table></div>';
    return $html;
}

$stmt = $conn->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$chat_history = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
$teach_prompt = trim($_GET['teach'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant - AI Learning Assistant</title>
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
                <h4 class="mb-0 fw-bold">AI Chat Assistant</h4>
                <p class="text-muted mb-0">Ask me anything about your studies</p>
            </div>
            <div class="topbar-action-group">
                <button class="btn btn-outline-primary" id="chatHistoryBtn">
                    <i class="bi bi-clock-history me-2"></i>Chat History
                </button>
                <button class="btn btn-outline-danger" id="clearChatBtn">
                    <i class="bi bi-trash me-2"></i>Clear Chat
                </button>
                <?php renderTopActions($user); ?>
            </div>
        </div>

        <div class="chat-page">
            <div class="chat-shell">
                <div class="card border-0 shadow-sm chat-card">
                    <div class="chat-container">
                        <div class="chat-hero">
                            <div>
                                <h5 class="fw-bold mb-1">Study Tutor</h5>
                                <p class="text-muted mb-0">Ask, revise, solve, or get a topic explained step by step.</p>
                            </div>
                            <div class="chat-status">
                                <span></span> Online
                            </div>
                        </div>

                        <div class="chat-messages" id="chatMessages">
                            <?php if (count($chat_history) > 0): ?>
                                <?php foreach ($chat_history as $chat): ?>
                                    <div class="message user">
                                        <div class="message-avatar">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="message-content">
                                            <?php echo nl2br(htmlspecialchars($chat['question'])); ?>
                                        </div>
                                    </div>
                                    <div class="message ai">
                                        <div class="message-avatar">
                                            <i class="bi bi-robot"></i>
                                        </div>
                                        <div class="message-content formatted-response">
                                            <?php echo formatAIResponse($chat['answer']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="message ai">
                                    <div class="message-avatar">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content">
                                        Hello! I'm your AI learning assistant. How can I help you with your studies today?
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="chat-input-container">
                            <div class="chat-suggestions mb-3">
                                <button type="button" class="chat-chip" data-prompt="Explain this topic step by step with simple examples.">Step-by-step</button>
                                <button type="button" class="chat-chip" data-prompt="Give me practice questions and then check my answers.">Practice me</button>
                                <button type="button" class="chat-chip" data-prompt="Summarize this topic into exam notes.">Exam notes</button>
                                <button type="button" class="chat-chip" data-prompt="Teach this like I am a beginner and ask me questions after each part.">Teach mode</button>
                            </div>
                            <form id="chatForm">
                                <div class="chat-composer">
                                    <textarea class="form-control" id="messageInput" placeholder="Ask me anything about your studies..." rows="1" required><?php echo htmlspecialchars($teach_prompt); ?></textarea>
                                    <button class="btn btn-primary px-4" type="submit" id="sendBtn">
                                        <i class="bi bi-send"></i> Send
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const clearChatBtn = document.getElementById('clearChatBtn');

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function autoResizeInput() {
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 140) + 'px';
        }

        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'} fade-in`;
            
            const formattedContent = isUser ? escapeHtml(content) : formatResponse(content);
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="bi bi-${isUser ? 'person' : 'robot'}"></i>
                </div>
                <div class="message-content ${isUser ? '' : 'formatted-response'}">
                    ${formattedContent}
                </div>
            `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }

        function formatResponse(text) {
            let escaped = text.replace(/&/g, '&amp;')
                             .replace(/</g, '&lt;')
                             .replace(/>/g, '&gt;')
                             .replace(/"/g, '&quot;')
                             .replace(/'/g, '&#039;');
            
            escaped = escaped.replace(/#{4,}\s*(\d+)\.\s*/g, '<strong>$1.</strong> ');
            escaped = escaped.replace(/#{4,}\s*/g, '');
            
            escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            escaped = escaped.replace(/(?<!\*)\*(?!\*)([^\*]+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
            escaped = escaped.replace(/`([^`]+)`/g, '<code>$1</code>');
            
            escaped = escaped.replace(/^######\s+(.+?)$/gm, '<h6 class="mt-2 mb-1 fw-bold">$1</h6>');
            escaped = escaped.replace(/^#####\s+(.+?)$/gm, '<h6 class="mt-2 mb-1 fw-bold">$1</h6>');
            escaped = escaped.replace(/^####\s+(.+?)$/gm, '<h5 class="mt-3 mb-2 fw-bold text-primary">$1</h5>');
            escaped = escaped.replace(/^###\s+(.+?)$/gm, '<h5 class="mt-3 mb-2 fw-bold text-primary">$1</h5>');
            escaped = escaped.replace(/^##\s+(.+?)$/gm, '<h4 class="mt-3 mb-2 fw-bold text-primary">$1</h4>');
            escaped = escaped.replace(/^#\s+(.+?)$/gm, '<h3 class="mt-3 mb-2 fw-bold text-primary">$1</h3>');
            
            escaped = escaped.replace(/---+/g, '<hr class="my-3">');
            
            const lines = escaped.split('\n');
            let formatted = [];
            let inList = false;
            let inTable = false;
            let tableRows = [];
            
            for (let line of lines) {
                line = line.trim();
                
                if (line.match(/^\|(.+)\|$/)) {
                    if (!inTable) {
                        inTable = true;
                        tableRows = [];
                    }
                    tableRows.push(line);
                    continue;
                } else {
                    if (inTable) {
                        formatted.push(formatTable(tableRows));
                        inTable = false;
                        tableRows = [];
                    }
                }
                
                if (line.match(/^[\*\-\+]\s+(.+)$/)) {
                    if (!inList) {
                        formatted.push('<ul class="mt-2 mb-2 ps-4">');
                        inList = 'ul';
                    }
                    formatted.push('<li class="mb-1">' + line.replace(/^[\*\-\+]\s+/, '') + '</li>');
                } else if (line.match(/^(\d+)\.\s+(.+)$/)) {
                    if (inList !== 'ol') {
                        if (inList) {
                            formatted.push('</ul>');
                        }
                        formatted.push('<ol class="mt-2 mb-2 ps-4">');
                        inList = 'ol';
                    }
                    formatted.push('<li class="mb-1">' + line.replace(/^\d+\.\s+/, '') + '</li>');
                } else {
                    if (inList) {
                        formatted.push(inList === 'ol' ? '</ol>' : '</ul>');
                        inList = false;
                    }
                    if (line && !line.match(/<h[3-6]/) && !line.match(/<hr/) && !line.match(/<pre/)) {
                        formatted.push('<p class="mb-2">' + line + '</p>');
                    } else if (line) {
                        formatted.push(line);
                    }
                }
            }
            
            if (inList) {
                formatted.push(inList === 'ol' ? '</ol>' : '</ul>');
            }
            
            if (inTable) {
                formatted.push(formatTable(tableRows));
            }
            
            return formatted.join('\n');
        }

        function formatTable(rows) {
            if (!rows || rows.length === 0) return '';
            
            let html = '<div class="table-responsive mt-3 mb-3"><table class="table table-bordered table-striped">';
            let isFirstRow = true;
            
            for (let row of rows) {
                if (row.match(/^\|[\s\-:]+\|$/)) {
                    continue;
                }
                
                let cells = row.split('|').map(c => c.trim()).filter(c => c);
                
                if (isFirstRow) {
                    html += '<thead class="table-primary"><tr>';
                    for (let cell of cells) {
                        html += '<th>' + cell + '</th>';
                    }
                    html += '</tr></thead><tbody>';
                    isFirstRow = false;
                } else {
                    html += '<tr>';
                    for (let cell of cells) {
                        html += '<td>' + cell + '</td>';
                    }
                    html += '</tr>';
                }
            }
            
            html += '</tbody></table></div>';
            return html;
        }

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;

            addMessage(message, true);
            messageInput.value = '';
            autoResizeInput();
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="loading-spinner"></span> Thinking...';

            try {
                const response = await fetch('../api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                
                if (data.success) {
                    addMessage(data.data.answer, false);
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', false);
                }
            } catch (error) {
                addMessage('Sorry, I encountered an error. Please try again.', false);
            }

            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send"></i> Send';
        });

        clearChatBtn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to clear the chat history?')) return;

            try {
                const response = await fetch('../api/clear_chat.php', {
                    method: 'POST'
                });

                const data = await response.json();
                
                if (data.success) {
                    chatMessages.innerHTML = `
                        <div class="message ai">
                            <div class="message-avatar">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="message-content">
                                Hello! I'm your AI learning assistant. How can I help you with your studies today?
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                alert('Error clearing chat history');
            }
        });

        document.querySelectorAll('.chat-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                messageInput.value = chip.dataset.prompt;
                autoResizeInput();
                messageInput.focus();
            });
        });

        messageInput.addEventListener('input', autoResizeInput);
        messageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                chatForm.requestSubmit();
            }
        });

        autoResizeInput();
        scrollToBottom();
    </script>
</body>
</html>
