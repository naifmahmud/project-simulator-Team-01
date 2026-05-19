<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Request - Travel Guide</title>
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
            <a href="index.php?page=scout-create-request">Create Request</a>
            <a href="index.php?page=scout-my-requests" class="active">My Requests</a>
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
        <h1 class="page-title">Edit Request</h1>
        <p class="page-sub">Update your pending post request</p>
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
        <form method="POST" action="index.php?page=scout-edit-request" enctype="multipart/form-data" id="editRequestForm">
            <?php $old = $_SESSION['old'] ?? []; ?>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id'] ?? '') ?>">
            
            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control" 
                    maxlength="200"
                    value="<?= htmlspecialchars($old['title'] ?? $request['title'] ?? '') ?>"
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
                ><?= htmlspecialchars($old['short_history'] ?? $request['short_history'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="country">Country Representation <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="country" 
                    name="country" 
                    class="form-control"
                    value="<?= htmlspecialchars($old['country'] ?? $request['country'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="genre">Genre <span class="required">*</span></label>
                <select id="genre" name="genre" class="form-control" required>
                    <option value="">-- Select Genre --</option>
                    <?php
                    $genres = ['beach', 'mountain', 'city', 'historical', 'forest', 'desert', 'island'];
                    $selectedGenre = $old['genre'] ?? $request['genre'] ?? '';
                    foreach ($genres as $genre):
                    ?>
                        <option value="<?= $genre ?>" <?= $selectedGenre === $genre ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($genre)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cost_level">Cost Level <span class="required">*</span></label>
                <select id="cost_level" name="cost_level" class="form-control" required>
                    <option value="">-- Select Cost Level --</option>
                    <?php
                    $costLevels = [
                        'free' => 'Free',
                        'low' => 'Low ($)',
                        'medium' => 'Medium ($$)',
                        'high' => 'High ($$$)'
                    ];
                    $selectedCost = $old['cost_level'] ?? $request['cost_level'] ?? '';
                    foreach ($costLevels as $value => $label):
                    ?>
                        <option value="<?= $value ?>" <?= $selectedCost === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
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
                ><?= htmlspecialchars($old['travel_medium_info'] ?? $request['travel_medium_info'] ?? '') ?></textarea>
            </div>

            <?php if (!empty($request['image'])): ?>
                <div class="form-group">
                    <label>Current Image</label>
                    <div>
                        <img src="<?= htmlspecialchars($request['image']) ?>" 
                             alt="Current image" 
                             style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">Change Image (Optional)</label>
                <input 
                    type="file" 
                    id="image" 
                    name="image" 
                    class="form-control"
                    accept="image/jpeg,image/jpg,image/png"
                >
                <small class="form-text">Allowed: JPG, JPEG, PNG. Max size: 5MB. Leave empty to keep current image.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Request</button>
                <a href="index.php?page=scout-my-requests" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
// Client-side validation
document.getElementById('editRequestForm').addEventListener('submit', function(e) {
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
