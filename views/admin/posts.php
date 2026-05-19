<?php
$isEdit = !empty($editing);
$messages = [
    'approved' => 'Post approved successfully.',
    'rejected' => 'Post request rejected.',
    'updated'  => 'Post updated successfully.',
    'deleted'  => 'Post deleted successfully.',
];
$msg = $messages[$_GET['msg'] ?? ''] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Moderation &mdash; Travel Guide Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=admin">
            <span class="brand-icon">&#127760;</span>
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
            <h1 class="page-title">Post Moderation</h1>
            <p class="page-sub">Approve, reject, edit and remove travel posts</p>
        </div>
        <div class="header-actions">
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Dashboard</a>
            <a href="index.php?page=admin&section=users" class="btn btn-ghost">Users</a>
            <a href="index.php?page=admin&section=comments" class="btn btn-ghost">Comments</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ============ Edit Post Form ============ -->
    <?php if ($isEdit): ?>
    <div class="card form-card">
        <h3 class="card-title">&#9998; Edit Post (#<?= intval($editing['id']) ?>)</h3>
        <form method="POST"
              action="index.php?page=admin&section=posts&action=edit&id=<?= intval($editing['id']) ?>"
              class="form" novalidate>

            <div class="field-row">
                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($editing['title'] ?? '') ?>"
                           placeholder="Post title" required>
                </div>
                <div class="field">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country"
                           value="<?= htmlspecialchars($editing['country'] ?? '') ?>"
                           placeholder="Country" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="genre">Genre</label>
                    <input type="text" id="genre" name="genre"
                           value="<?= htmlspecialchars($editing['genre'] ?? '') ?>"
                           placeholder="Genre">
                </div>
                <div class="field">
                    <label for="cost_level">Cost Level</label>
                    <select id="cost_level" name="cost_level">
                        <option value="free"   <?= ($editing['cost_level'] ?? '') === 'free'   ? 'selected' : '' ?>>Free</option>
                        <option value="low"    <?= ($editing['cost_level'] ?? '') === 'low'    ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($editing['cost_level'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high"   <?= ($editing['cost_level'] ?? '') === 'high'   ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="short_history">Short History</label>
                <textarea id="short_history" name="short_history" rows="3"
                          placeholder="Short history..."><?= htmlspecialchars($editing['short_history'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="travel_medium_info">Travel Medium Info</label>
                <textarea id="travel_medium_info" name="travel_medium_info" rows="3"
                          placeholder="Travel info..."><?= htmlspecialchars($editing['travel_medium_info'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?page=admin&section=posts&action=approved" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ============ Tabs ============ -->
    <div class="card">
        <div class="card-toolbar">
            <div class="tabs">
                <a href="index.php?page=admin&section=posts&action=pending"
                   class="tab <?= $action === 'pending' ? 'active' : '' ?>">
                    Pending Requests (<?= $pendingCount ?>)
                </a>
                <a href="index.php?page=admin&section=posts&action=approved"
                   class="tab <?= $action === 'approved' ? 'active' : '' ?>">
                    Approved Posts (<?= $approvedCount ?>)
                </a>
            </div>
        </div>

        <h3 class="card-title"><?= htmlspecialchars($title) ?></h3>

        <?php if (empty($posts)): ?>
            <p class="empty">No posts found.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="data-table" id="postsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Scout</th>
                        <th>Country</th>
                        <th>Genre</th>
                        <th>Cost</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="postsBody">
                    <?php foreach ($posts as $i => $post): ?>
                    <?php
                    $postData = isset($post['post_data']) ? json_decode($post['post_data'], true) : null;
                    $pTitle   = $postData ? ($postData['title']      ?? 'N/A') : ($post['title']      ?? 'N/A');
                    $pCountry = $postData ? ($postData['country']    ?? 'N/A') : ($post['country']    ?? 'N/A');
                    $pGenre   = $postData ? ($postData['genre']      ?? 'N/A') : ($post['genre']      ?? 'N/A');
                    $pCost    = $postData ? ($postData['cost_level'] ?? 'N/A') : ($post['cost_level'] ?? 'N/A');
                    $pDate    = $post['requested_at'] ?? $post['created_at'] ?? '';
                    ?>
                    <tr id="post-row-<?= $post['id'] ?>">
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($pTitle) ?></strong></td>
                        <td><?= htmlspecialchars($post['scout_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($pCountry) ?></td>
                        <td><?= htmlspecialchars($pGenre) ?></td>
                        <td><?= htmlspecialchars($pCost) ?></td>
                        <td><?= $pDate ? date('M d, Y', strtotime($pDate)) : 'N/A' ?></td>
                        <td class="text-right">
                            <?php if ($action === 'pending'): ?>
                                <!-- AJAX approve -->
                                <button class="btn-sm btn-edit"
                                        onclick="approvePost(<?= $post['id'] ?>)">Approve</button>
                                <!-- Regular link reject -->
                                <a class="btn-sm btn-delete"
                                   href="index.php?page=admin&section=posts&reject=1&id=<?= $post['id'] ?>"
                                   onclick="return confirm('Reject this post request?')">Reject</a>
                            <?php else: ?>
                                <a class="btn-sm btn-edit"
                                   href="index.php?page=admin&section=posts&action=edit&id=<?= $post['id'] ?>">Edit</a>
                                <a class="btn-sm btn-delete"
                                   href="index.php?page=admin&section=posts&action=delete&id=<?= $post['id'] ?>"
                                   onclick="return confirm('Delete this post and all related data?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide Admin</footer>

<!-- =========== AJAX: Approve post =========== -->
<script>
(function () {
    function approvePost(id) {
        if (!confirm('Approve this post request?')) return;

        fetch('api/admin/approve_post.php?id=' + id, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    // Remove row from pending table instantly
                    var row = document.getElementById('post-row-' + id);
                    if (row) row.remove();
                    // Update pending count in tab
                    var tab = document.querySelector('.tab.active');
                    if (tab) {
                        var match = tab.textContent.match(/\d+/);
                        if (match) {
                            var n = parseInt(match[0]) - 1;
                            tab.textContent = tab.textContent.replace(/\d+/, n < 0 ? 0 : n);
                        }
                    }
                } else {
                    alert(data.message || 'Failed to approve post.');
                }
            })
            .catch(function (e) { console.error(e); });
    }

    window.approvePost = approvePost;
}());
</script>

</body>
</html>
