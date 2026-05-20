<?php
// Post detail view
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($post['title']) ?> — Travel Guide</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">📖</span>
            <span>Travel Guide</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=browse">Browse</a>
            <a href="index.php?page=wishlist">Wishlist</a>
            <a href="index.php?page=profile">Profile</a>
            <a href="index.php?page=logout">Logout</a>
        </nav>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($post['title']) ?></h1>
        <p class="page-sub"><?= htmlspecialchars($post['country']) ?> — <?= htmlspecialchars($post['genre']) ?></p>
    </div>

    <div class="card">
        <h3>Overview</h3>
        <p><?= nl2br(htmlspecialchars($post['short_history'])) ?></p>
        <p><strong>Travel medium info:</strong> <?= htmlspecialchars($post['travel_medium_info'] ?? '') ?></p>
    </div>

    <?php $baseCost = getBaseCostForLevel($post['cost_level']); ?>
    <div class="card">
        <h3>Cost Estimate</h3>
        <p>
            Base cost: <strong><?= $baseCost > 0 ? '$' . number_format($baseCost) : 'Free' ?></strong>.
            Total cost = base cost × travelers × days / 7.
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
            <label>Number of travelers:
                <input id="est-travelers" type="number" min="1" max="10" value="1" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
            <label>Number of days:
                <input id="est-days" type="number" min="1" value="3" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
            <button id="calc-est" class="btn btn-primary">Calculate</button>
        </div>
        <div id="est-result" style="margin-top:10px; font-weight:bold;"></div>
    </div>

    <div class="card">
        <h3>Comments</h3>
        <div id="comments-list">
            <?php foreach ($comments as $c): ?>
                <div class="comment" data-id="<?= $c['id'] ?>">
                    <div><strong><?= htmlspecialchars($c['user_name']) ?></strong> <span class="muted"><?= htmlspecialchars($c['created_at']) ?></span></div>
                    <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $c['user_id']): ?>
                        <button class="btn btn-sm btn-delete" onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isLoggedIn() && $_SESSION['user_role'] === 'user' && isUserVerified()): ?>
            <div class="comment-form" style="margin-top:20px;">
                <h4>Post a comment</h4>
                <div style="margin-bottom:12px;">
                    <label style="display:block;">Name
                        <input id="comment-author" type="text" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" maxlength="100" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                    </label>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block;">Comment
                        <textarea id="comment-text" rows="4" maxlength="500" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;"></textarea>
                    </label>
                </div>
                <div id="comment-error" class="alert alert-error" style="display:none;margin-bottom:12px;color:#b71c1c;background:#ffebee;padding:10px;border-radius:4px;"></div>
                <button id="post-comment" class="btn btn-primary">Post Comment</button>
            </div>
        <?php else: ?>
            <p class="muted">Only verified general users can post comments.</p>
        <?php endif; ?>
    </div>
</main>

<script>
const postId = <?= intval($post['id']) ?>;
const currentUserId = <?= isLoggedIn() ? intval($_SESSION['user_id']) : 0 ?>;
const currentUserRole = '<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_role']) : '' ?>';
const COMMENT_MAX_LENGTH = 500;
const baseCost = <?= $baseCost ?>;

function renderComments(list) {
    const container = document.getElementById('comments-list');
    container.innerHTML = '';

    if (!Array.isArray(list) || list.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No comments yet. Be the first to share your thoughts.</p></div>';
        return;
    }

    list.forEach(c => {
        const div = document.createElement('div');
        div.className = 'comment';
        div.dataset.id = c.id;
        div.innerHTML = `
            <div><strong>${escapeHtml(c.user_name)}</strong> <span class="muted">${escapeHtml(c.created_at)}</span></div>
            <div>${escapeHtml(c.content).replace(/\n/g,'<br>')}</div>
        `;

        if (currentUserId && (currentUserRole === 'admin' || currentUserId === parseInt(c.user_id, 10))) {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-delete';
            btn.textContent = 'Delete';
            btn.onclick = () => deleteComment(c.id);
            div.appendChild(btn);
        }

        container.appendChild(div);
    });
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function(m) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];
    });
}

function clearCommentError() {
    const error = document.getElementById('comment-error');
    if (error) {
        error.textContent = '';
        error.style.display = 'none';
    }
}

function showCommentError(message) {
    const error = document.getElementById('comment-error');
    if (error) {
        error.textContent = message;
        error.style.display = 'block';
    }
}

async function fetchComments() {
    try {
        const res = await fetch('index.php?page=api&resource=comments&sub=list&post_id=' + postId, { credentials: 'same-origin' });
        const data = await res.json();
        if (data.success) {
            renderComments(data.data);
        }
    } catch (err) {
        console.error('Unable to fetch comments', err);
    }
}

document.getElementById('calc-est').addEventListener('click', () => {
    const travelers = parseInt(document.getElementById('est-travelers').value, 10) || 1;
    const days = parseInt(document.getElementById('est-days').value, 10) || 1;
    if (travelers < 1 || travelers > 10) {
        document.getElementById('est-result').textContent = 'Travelers must be between 1 and 10.';
        return;
    }
    if (days < 1) {
        document.getElementById('est-result').textContent = 'Days must be a positive integer.';
        return;
    }
    const total = baseCost * travelers * days / 7;
    document.getElementById('est-result').textContent = 'Estimated total cost: $' + total.toFixed(2);
});

<?php if (isLoggedIn() && $_SESSION['user_role'] === 'user' && isUserVerified()): ?>
document.getElementById('post-comment').addEventListener('click', async () => {
    clearCommentError();
    const content = document.getElementById('comment-text').value.trim();
    const name = document.getElementById('comment-author').value.trim();

    if (!content) {
        showCommentError('Comment cannot be empty.');
        return;
    }
    if (content.length > COMMENT_MAX_LENGTH) {
        showCommentError('Comment must be 500 characters or less.');
        return;
    }

    const res = await fetch('index.php?page=api&resource=comments&sub=add', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ post_id: postId, content, name })
    });
    const data = await res.json();
    if (!data.success) {
        showCommentError(data.message || 'Failed to post comment.');
        return;
    }
    document.getElementById('comment-text').value = '';
    renderComments(data.data);
});

async function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;
    try {
        const res = await fetch('index.php?page=api&resource=comments&sub=delete&id=' + id, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) {
            alert(data.message || 'Failed to delete comment.');
            return;
        }
        renderComments(data.data);
    } catch (err) {
        console.error(err);
        alert('Unable to delete comment.');
    }
}
<?php endif; ?>
</script>
</body>
</html>