<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

// ✅ Only user managers can access
if (!is_user_manager()) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: user_management.php");
    exit;
}

// ✅ Fetch query details
$stmt = $pdo->prepare("SELECT * FROM contact_queries WHERE id = ?");
$stmt->execute([$id]);
$query = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$query) {
    echo "<p>❌ Query not found</p>";
    exit;
}

// ✅ Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
    $reply = trim($_POST['reply_text']);
    $status = $_POST['status'] ?? $query['status'];
    $priority = $_POST['priority'] ?? $query['priority'];

    if ($reply !== "") {
        $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_query_id, admin_identifier, reply_text, replied_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$id, $_SESSION['username'] ?? 'admin', $reply]);
    }

    // ✅ Update status/priority
    $stmt = $pdo->prepare("UPDATE contact_queries SET status = ?, priority = ? WHERE id = ?");
    $stmt->execute([$status, $priority, $id]);

    header("Location: view_query.php?id=" . $id);
    exit;
}

// ✅ Fetch replies
$stmt = $pdo->prepare("SELECT * FROM contact_replies WHERE contact_query_id = ? ORDER BY replied_at ASC");
$stmt->execute([$id]);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Query</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .query-box {
            background: var(--panel);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .reply {
            background: #0f1730;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .reply.admin { border-left: 4px solid var(--brand); }
        .reply.user { border-left: 4px solid var(--brand-2); }
        .meta { font-size: 12px; color: var(--muted); margin-bottom: 6px; }
        textarea { min-height: 100px; }
        .status-controls {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }
        select {
            width: 150px;
            padding: 6px;
            border-radius: 8px;
            background: #0f1730;
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--text);
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="container no-sidebar">
    <main class="main">
        <div class="topbar">
            <h2>📩 Query #<?= htmlspecialchars($query['ticket_id']) ?> - <?= htmlspecialchars($query['subject']) ?></h2>
            <a href="user_management.php" class="btn outline">⬅ Back</a>
        </div>

        <!-- Query Details -->
        <div class="query-box">
            <p><strong>From:</strong> <?= htmlspecialchars($query['name']) ?> (<?= htmlspecialchars($query['email']) ?>)</p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($query['phone']) ?></p>
            <p><strong>Submitted:</strong> <?= htmlspecialchars($query['submitted_at']) ?></p>
            <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($query['message'])) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($query['status']) ?> | <strong>Priority:</strong> <?= ucfirst($query['priority']) ?></p>
        </div>

        <!-- Conversation -->
        <div class="card">
            <h3>Conversation</h3>
            <?php if (empty($replies)): ?>
                <p>No replies yet.</p>
            <?php else: ?>
                <?php foreach ($replies as $r): ?>
                    <div class="reply admin">
                        <div class="meta">👨‍💼 <?= htmlspecialchars($r['admin_identifier']) ?> | <?= htmlspecialchars($r['replied_at']) ?></div>
                        <div><?= nl2br(htmlspecialchars($r['reply_text'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Reply Form -->
        <div class="card">
            <h3>Send Reply</h3>
            <form method="post">
                <textarea name="reply_text" class="input" placeholder="Type your reply..."></textarea>
                
                <div class="status-controls">
                    <label>Status: 
                        <select name="status">
                            <option value="open" <?= $query['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="closed" <?= $query['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </label>
                    <label>Priority: 
                        <select name="priority">
                            <option value="low" <?= $query['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $query['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $query['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                        </select>
                    </label>
                </div>
                <button type="submit" class="btn">💬 Send Reply</button>
            </form>
        </div>
    </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>
