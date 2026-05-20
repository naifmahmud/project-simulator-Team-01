<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
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
        <h1 class="page-title">My Wishlist</h1>
        <p class="page-sub">Your saved travel destinations</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($wishlistItems)): ?>
        <div class="card">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Country</th>
                            <th>Cost Level</th>
                            <th>Genre</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wishlistItems as $item): ?>
                            <tr id="wishlist-item-<?= $item['post_id'] ?>">
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td>
                                    <span class="badge badge-country">📖 <?= htmlspecialchars($item['country']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $costLabels = [
                                        'free' => 'Free',
                                        'low' => '$',
                                        'medium' => '$$',
                                        'high' => '$$$'
                                    ];
                                    echo $costLabels[$item['cost_level']] ?? '';
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($item['genre']) ?></td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-delete" onclick="removeFromWishlist(<?= $item['post_id'] ?>)">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Your wishlist is empty.</p>
            <a href="index.php?page=browse" class="btn btn-primary">Browse Destinations</a>
        </div>
    <?php endif; ?>
</main>

<script>
function removeFromWishlist(postId) {
    if (!confirm('Remove this destination from your wishlist?')) {
        return;
    }

    fetch('index.php?page=api&wishlist=remove', {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('wishlist-item-' + postId);
            if (row) {
                row.remove();
                const tbody = document.querySelector('table tbody');
                if (tbody.children.length === 0) {
                    location.reload();
                }
            }
        } else {
            alert(data.message || 'Failed to remove from wishlist');
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
