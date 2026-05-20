<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (isLoggedIn()): ?>
    <!-- Navbar for logged-in users -->
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
            <div class="nav-user">
                <span class="user-pill">
                    <span class="user-avatar">
                        <?= htmlspecialchars(substr($_SESSION['user_name'], 0, 1)) ?>
                    </span>
                    <span class="user-meta">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'user')) ?></span>
                    </span>
                </span>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?php if (needsVerification()): ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <div class="verification-icon">&#9888;</div>
                <h2>Account Verification Pending</h2>
                <p class="muted" style="margin: 15px 0;">Your account is pending admin approval. Please wait for verification to access all features.</p>
                <a href="index.php?page=logout" class="btn btn-primary">Logout</a>
            </div>
        <?php else: ?>
            <div class="page-header">
                <h1 class="page-title">Discover Travel Destinations</h1>
                <p class="page-sub">Explore the latest approved travel posts</p>
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
                                    <span class="post-country">📖 <?= htmlspecialchars($post['country']) ?></span>
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
                                    <a href="index.php?page=post&id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center" style="margin-top: 30px;">
                    <a href="index.php?page=browse" class="btn btn-primary">Browse All Destinations</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No approved travel posts yet.</p>
                    <a href="index.php?page=browse" class="btn btn-primary">Browse Destinations</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

<?php else: ?>
    <!-- Landing page for non-logged-in users -->
    <div class="landing-page">
        <div class="landing-content">
            <h1>Discover the World with Travel Guide</h1>
            <p>Explore amazing destinations, read travel stories, and create your personal wishlist. Join our community of travel enthusiasts today!</p>
            <div class="landing-actions">
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Get Started</a>
                <a href="index.php?page=login" class="btn btn-secondary btn-lg">Sign In</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function addToWishlist(postId) {
    if (!confirm('Add this destination to your wishlist?')) {
        return;
    }

    fetch('index.php?page=api&wishlist=add', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
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
