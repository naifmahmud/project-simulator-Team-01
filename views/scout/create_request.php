<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post Request - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#127760;</span>
            <span>Travel Guide</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php?page=scout-create-request" class="active">Create Request</a>
            <a href="index.php?page=scout-my-requests">My Requests</a>
            <a href="index.php?page=scout-approved-posts">Approved Posts</a>
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
        <h1 class="page-title">Create Post Request</h1>
        <p class="page-sub">Submit a new travel destination for admin approval</p>
    </div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="index.php?page=scout-create-request" enctype="multipart/form-data" id="createRequestForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            
            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control" 
                    maxlength="200"
                    value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="short_history">Short History <span class="required">*</span></label>
                <textarea 
                    id="short_history" 
                    name="short_history" 
                    class="form-control" 
                    rows="5"
                    required
                ><?= htmlspecialchars($_SESSION['old']['short_history'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="country">Country Representation <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="country" 
                    name="country" 
                    class="form-control"
                    value="<?= htmlspecialchars($_SESSION['old']['country'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="genre">Genre <span class="required">*</span></label>
                <select id="genre" name="genre" class="form-control" required>
                    <option value="">-- Select Genre --</option>
                    <option value="beach" <?= ($_SESSION['old']['genre'] ?? '') === 'beach' ? 'selected' : '' ?>>Beach</option>
                    <option value="mountain" <?= ($_SESSION['old']['genre'] ?? '') === 'mountain' ? 'selected' : '' ?>>Mountain</option>
                    <option value="city" <?= ($_SESSION['old']['genre'] ?? '') === 'city' ? 'selected' : '' ?>>City</option>
                    <option value="historical" <?= ($_SESSION['old']['genre'] ?? '') === 'historical' ? 'selected' : '' ?>>Historical</option>
                    <option value="forest" <?= ($_SESSION['old']['genre'] ?? '') === 'forest' ? 'selected' : '' ?>>Forest</option>
                    <option value="desert" <?= ($_SESSION['old']['genre'] ?? '') === 'desert' ? 'selected' : '' ?>>Desert</option>
                    <option value="island" <?= ($_SESSION['old']['genre'] ?? '') === 'island' ? 'selected' : '' ?>>Island</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cost_level">Cost Level <span class="required">*</span></label>
                <select id="cost_level" name="cost_level" class="form-control" required>
                    <option value="">-- Select Cost Level --</option>
                    <option value="free" <?= ($_SESSION['old']['cost_level'] ?? '') === 'free' ? 'selected' : '' ?>>Free</option>
                    <option value="low" <?= ($_SESSION['old']['cost_level'] ?? '') === 'low' ? 'selected' : '' ?>>Low ($)</option>
                    <option value="medium" <?= ($_SESSION['old']['cost_level'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium ($$)</option>
                    <option value="high" <?= ($_SESSION['old']['cost_level'] ?? '') === 'high' ? 'selected' : '' ?>>High ($$$)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="travel_medium_info">Travel Medium Info</label>
                <textarea 
                    id="travel_medium_info" 
                    name="travel_medium_info" 
                    class="form-control" 
                    rows="3"
                    placeholder="e.g., Best reached by car, 2 hours from airport..."
                ><?= htmlspecialchars($_SESSION['old']['travel_medium_info'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Image (Optional)</label>
                <input 
                    type="file" 
                    id="image" 
                    name="image" 
                    class="form-control"
                    accept="image/jpeg,image/jpg,image/png"
                >
                <small class="form-text">Allowed: JPG, JPEG, PNG. Max size: 5MB</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="index.php?page=scout-my-requests" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
// Client-side validation
document.getElementById('createRequestForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const shortHistory = document.getElementById('short_history').value.trim();
    const country = document.getElementById('country').value.trim();
    const genre = document.getElementById('genre').value;
    const costLevel = document.getElementById('cost_level').value;
    const imageInput = document.getElementById('image');
    
    let errors = [];
    
    if (title === '') {
        errors.push('Title is required');
    }
    
    if (shortHistory === '') {
        errors.push('Short history is required');
    }
    
    if (country === '') {
        errors.push('Country is required');
    }
    
    if (genre === '') {
        errors.push('Please select a genre');
    }
    
    if (costLevel === '') {
        errors.push('Please select a cost level');
    }
    
    // Validate image if selected
    if (imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            errors.push('Only JPG, JPEG, and PNG files are allowed');
        }
        
        if (file.size > maxSize) {
            errors.push('Image size must not exceed 5MB');
        }
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php unset($_SESSION['old']); ?>

</body>
</html>
