<?php
$messages = ['deleted' => 'Comment deleted successfully.'];
$msg = $messages[$_GET['msg'] ?? ''] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comment Moderation &mdash; Travel Guide Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=admin">
            <span class="brand-icon">📖</span>
            <span>Travel Guide</span>
        </a>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">A</span>
                <span class="user-meta">
                    <span class="user-name">Administrator</span>
                    <span class="user-role">Admin</span>
                </span>
            </span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Comment Moderation</h1>
            <p class="page-sub">Review and remove user comments</p>
        </div>
        <div class="header-actions">
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Dashboard</a>
            <a href="index.php?page=admin&section=users" class="btn btn-ghost">Users</a>
            <a href="index.php?page=admin&section=posts" class="btn btn-ghost">Posts</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <!-- ============ Comments Table ============ -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Search by comment, user or post...">
            </div>
            <span class="badge" id="resultCount"><?= count($comments) ?> total</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Comment</th>
                        <th>User</th>
                        <th>Post</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($comments)): ?>
                        <tr><td colspan="6" class="empty">No comments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($comments as $i => $c): ?>
                        <tr id="comment-row-<?= $c['id'] ?>">
                            <td><?= $i + 1 ?></td>
                            <td title="<?= htmlspecialchars($c['content']) ?>">
                                <?= htmlspecialchars(mb_strimwidth($c['content'], 0, 80, '...')) ?>
                            </td>
                            <td><?= htmlspecialchars($c['user_name'] ?? 'Unknown User') ?></td>
                            <td><?= htmlspecialchars($c['post_title'] ?? 'Unknown Post') ?></td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td class="text-right">
                                <!-- AJAX delete -->
                                <button class="btn-sm btn-delete"
                                        onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide Admin</footer>

<script>
(function () {
    /* =========== AJAX: Delete comment =========== */
    function deleteComment(id) {
        if (!confirm('Delete this comment?')) return;

        fetch('api/admin/delete_comment.php?id=' + id, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    var row = document.getElementById('comment-row-' + id);
                    if (row) row.remove();
                    // Update counter
                    var counter = document.getElementById('resultCount');
                    var n = parseInt(counter.textContent) - 1;
                    counter.textContent = (n < 0 ? 0 : n) + ' total';
                } else {
                    alert(data.message || 'Failed to delete comment.');
                }
            })
            .catch(function (e) { console.error(e); });
    }

    window.deleteComment = deleteComment;

    /* =========== Inline AJAX search =========== */
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function render(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="empty">No matching results.</td></tr>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (c, i) {
            var content = esc(c.content);
            if (content.length > 80) content = content.substring(0, 80) + '...';
            var date = c.created_at ? c.created_at.substring(0, 10) : '';
            html += '<tr id="comment-row-' + c.id + '">'
                + '<td>' + (i + 1) + '</td>'
                + '<td title="' + esc(c.content) + '">' + content + '</td>'
                + '<td>' + esc(c.user_name) + '</td>'
                + '<td>' + esc(c.post_title) + '</td>'
                + '<td>' + date + '</td>'
                + '<td class="text-right">'
                + '<button class="btn-sm btn-delete" onclick="deleteComment(' + c.id + ')">Delete</button>'
                + '</td>'
                + '</tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=comments&q=' + encodeURIComponent(input.value.trim()),
                { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 200);
    });
}());
</script>

</body>
</html>
