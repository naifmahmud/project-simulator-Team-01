<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Posts - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">📖</span>
            <span>Travel Guide</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php?page=scout-create-request">Create Request</a>
            <a href="index.php?page=scout-my-requests">My Requests</a>
            <a href="index.php?page=scout-approved-posts" class="active">Approved Posts</a>
            <a href="index.php?page=logout">Logout</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">
                    <?= htmlspecialchars(substr($_SESSION['user_name'], 0, 1)) ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="user-role">Scout</span>
                </span>
            </span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Approved Posts</h1>
        <p class="page-sub">View your approved travel posts</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <?php if (!empty($approvedPosts)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Country</th>
                            <th>Genre</th>
                            <th>Status</th>
                            <th>Approved Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvedPosts as $post): ?>
                            <tr>
                                <td><?= htmlspecialchars($post['id']) ?></td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['country']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($post['genre'])) ?></td>
                                <td>
                                    <span class="badge badge-success">Approved</span>
                                </td>
                                <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <button onclick="requestChange(<?= $post['id'] ?>)" 
                                            class="btn btn-sm btn-warning">Request Changes</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>You don't have any approved posts yet.</p>
                <a href="index.php?page=scout-create-request" class="btn btn-primary">Create a Request</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function requestChange(postId) {
    if (!confirm('Request changes for this approved post? This will create a new pending request with the current post data.')) {
        return;
    }
    
    fetch('index.php?page=scout-request-change', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Change request created successfully! You can edit it in My Requests.');
            window.location.href = 'index.php?page=scout-my-requests';
        } else {
            alert(data.message || 'Failed to create change request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

</body>
</html>
