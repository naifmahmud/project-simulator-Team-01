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
        <h1 class="page-title">Browse Destinations</h1>
        <p class="page-sub">Explore all approved travel posts</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="browse-controls" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:18px;">
        <div style="flex:1 1 280px; min-width:220px;">
            <label for="search-box" style="display:block; margin-bottom:4px; font-weight:600;">Search</label>
            <input id="search-box" type="search" placeholder="Search by title or country" style="width:100%; padding:8px;">
        </div>

        <div style="min-width:200px;">
            <label for="country-filter" style="display:block; margin-bottom:4px; font-weight:600;">Country</label>
            <select id="country-filter" style="width:100%; padding:8px;">
                <option value="">All countries</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:240px;">
            <label style="display:block; margin-bottom:4px; font-weight:600;">Genre</label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ($genres as $g): ?>
                    <label style="line-height:1.4;"><input type="checkbox" class="genre-filter" value="<?= htmlspecialchars($g) ?>"> <?= htmlspecialchars($g) ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="min-width:180px;">
            <label style="display:block; margin-bottom:4px; font-weight:600;">Cost</label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <label><input type="radio" name="cost" value="" checked> Any</label>
                <label><input type="radio" name="cost" value="low"> Low</label>
                <label><input type="radio" name="cost" value="medium"> Medium</label>
                <label><input type="radio" name="cost" value="high"> High</label>
            </div>
        </div>

        <button id="clear-filters" class="btn btn-secondary" type="button" style="height:40px;">Reset</button>
    </div>

    <div id="posts-grid">
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
    <?php else: ?>
        <div class="empty-state">
            <p>No approved travel posts yet.</p>
        </div>
    <?php endif; ?>
    </div>
</main>

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

// Search & filter
let searchTimer = null;
const searchBox = document.getElementById('search-box');
const countryFilter = document.getElementById('country-filter');
const genreCheckboxes = Array.from(document.querySelectorAll('.genre-filter'));
const costRadios = Array.from(document.querySelectorAll('input[name="cost"]'));

function gatherFilters() {
    const q = searchBox.value.trim();
    const country = countryFilter.value;
    const genres = genreCheckboxes.filter(cb => cb.checked).map(cb => cb.value);
    const cost = (costRadios.find(r => r.checked) || {}).value || '';
    return { q, country, genres, cost };
}

function renderPosts(posts) {
    const grid = document.getElementById('posts-grid');
    if (!posts || posts.length === 0) {
        grid.innerHTML = '<div class="empty-state"><p>No posts match your filters.</p></div>';
        return;
    }
    const canWishlist = <?= ($_SESSION['user_role'] === 'user') ? 'true' : 'false' ?>;
    const costLabels = { free: 'Free', low: '$', medium: '$$', high: '$$$' };
    const html = posts.map(post => {
        const excerpt = String(post.short_history || '').substring(0, 100);
        return `
        <div class="post-card">
            <div class="post-card-content">
                <h3 class="post-title">${escapeHtml(post.title)}</h3>
                <p class="post-meta"><span class="post-country">📖 ${escapeHtml(post.country)}</span> <span class="post-cost">${escapeHtml(costLabels[post.cost_level] || post.cost_level)}</span></p>
                <p class="post-excerpt">${escapeHtml(excerpt)}...</p>
                <div class="post-actions">
                    ${canWishlist ? `<button class="btn btn-sm btn-outline" onclick="addToWishlist(${post.id})">+ Wishlist</button>` : ''}
                    <a href="index.php?page=post&id=${post.id}" class="btn btn-sm btn-primary">View Details</a>
                </div>
            </div>
        </div>
    `;
    }).join('');
    grid.innerHTML = '<div class="posts-grid">' + html + '</div>';
}

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];}); }

async function doSearchFilter() {
    const f = gatherFilters();
    const params = new URLSearchParams();
    if (f.q) params.append('q', f.q);
    if (f.country) params.append('country', f.country);
    f.genres.forEach(g => params.append('genre[]', g));
    if (f.cost) params.append('cost', f.cost);

    const url = 'index.php?page=api&action=filter&' + params.toString();

    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json();
    if (data.success) renderPosts(data.data);
}

searchBox.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(doSearchFilter, 300);
});

countryFilter.addEventListener('change', doSearchFilter);
genreCheckboxes.forEach(cb => cb.addEventListener('change', doSearchFilter));
costRadios.forEach(r => r.addEventListener('change', doSearchFilter));

document.getElementById('clear-filters').addEventListener('click', () => {
    searchBox.value = '';
    countryFilter.value = '';
    genreCheckboxes.forEach(cb => cb.checked = false);
    costRadios.find(r => r.value === '').checked = true;
    doSearchFilter();
});
</script>

</body>
</html>
