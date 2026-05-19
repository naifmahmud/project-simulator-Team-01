<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Destinations - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#127760;</span>
            <span>Travel Guide</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=browse">Browse</a>
            <a href="index.php?page=wishlist">Wishlist</a>
            <a href="index.php?page=profile">Profile</a>
            <a href="index.php?page=logout">Logout</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">
                    <?= htmlspecialchars(substr($_SESSION['user_name'], 0, 1)) ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="user-role">User</span>
                </span>
            </span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Browse Destinations</h1>
        <p class="page-sub">Explore all approved travel posts</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($latestPosts)): ?>
        <div class="posts-grid">
            <?php foreach ($latestPosts as $post): ?>
                <div class="post-card">
                    <div class="post-card-content">
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="post-meta">
                            <span class="post-country">&#127760; <?= htmlspecialchars($post['country']) ?></span>
                            <span class="post-cost">
                                <?php
                                $costLabels = [
                                    'free' => 'Free',
                                    'low' => '$',
                                    'medium' => '$$',
                                    'high' => '$$$'
                                ];
                                echo $costLabels[$post['cost_level']] ?? '';
                                ?>
                            </span>
                        </p>
                        <p class="post-excerpt">
                            <?= htmlspecialchars(substr($post['short_history'], 0, 100)) ?>...
                        </p>
                        <div class="post-actions">
                            <?php if ($_SESSION['user_role'] === 'user'): ?>
                                <button class="btn btn-sm btn-outline" onclick="addToWishlist(<?= $post['id'] ?>)">
                                    + Wishlist
                                </button>
                            <?php endif; ?>
                            <a href="index.php?page=browse" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No approved travel posts yet.</p>
        </div>
    <?php endif; ?>
</main>

<script>
function addToWishlist(postId) {
    if (!confirm('Add this destination to your wishlist?')) {
        return;
    }
    
    fetch('index.php?page=api&wishlist=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Added to wishlist!');
        } else {
            alert(data.message || 'Failed to add to wishlist');
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
