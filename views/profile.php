<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Travel Guide</title>
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
        <h1 class="page-title">My Profile</h1>
        <p class="page-sub">Manage your account information</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['errors'])): ?>
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if ($user['profile_picture']): ?>
                    <img src="uploads/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
                <?php else: ?>
                    <span class="avatar-placeholder">
                        <?= htmlspecialchars(substr($user['name'], 0, 1)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p class="muted"><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <form method="POST" action="index.php?page=profile" class="form" enctype="multipart/form-data" style="padding: 20px;">
            <?= getCsrfField() ?>
            
            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($user['name']) ?>"
                           placeholder="Your name" required>
                    <?php if (!empty($_SESSION['errors']['name'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>"
                           placeholder="your@email.com" required>
                    <?php if (!empty($_SESSION['errors']['email'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['email']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture"
                       accept="image/jpeg,image/png"
                       onchange="validateImage(this)">
                <small class="hint">Max size: 2MB. Allowed formats: JPEG, PNG</small>
                <?php if (!empty($_SESSION['errors']['profile_picture'])): ?>
                    <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['profile_picture']) ?></span>
                <?php endif; ?>
            </div>

            <h3 style="margin: 20px 0 15px; color: #1f2937;">Change Password</h3>
            <div class="field-row">
                <div class="field">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           placeholder="Enter current password">
                    <?php if (!empty($_SESSION['errors']['current_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['current_password']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="Min 8 characters">
                    <?php if (!empty($_SESSION['errors']['new_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['new_password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password"
                           placeholder="Repeat new password">
                    <?php if (!empty($_SESSION['errors']['confirm_new_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['confirm_new_password']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</main>

<script>
function validateImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const allowedTypes = ['image/jpeg', 'image/png'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPEG and PNG images are allowed');
            input.value = '';
        } else if (file.size > maxSize) {
            alert('File size must be less than 2MB');
            input.value = '';
        }
    }
}
</script>

</body>
</html>
