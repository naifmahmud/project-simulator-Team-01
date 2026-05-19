<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-body">

    <div class="auth-shell">
        <div class="auth-side">
            <div class="logo-big">&#127760;</div>
            <h1>Travel Guide</h1>
            <p>Explore amazing destinations and create your personal wishlist. Join our community of travel enthusiasts today!</p>
            <ul class="feature-list">
                <li>&#10003; Browse approved travel posts</li>
                <li>&#10003; Create your personal wishlist</li>
                <li>&#10003; Connect with travel scouts</li>
                <li>&#10003; Verified user benefits</li>
            </ul>
        </div>

        <div class="auth-form-wrap">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p class="muted">Sign in to continue your journey</p>

                <?php if (!empty($_SESSION['errors'])): ?>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?page=login" class="form" novalidate>
                    <?= getCsrfField() ?>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($_SESSION['old_email'] ?? '') ?>"
                            placeholder="Enter your email" required autofocus>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                            placeholder="Enter your password" required>
                    </div>
                    <label class="checkbox">
                        <input type="checkbox" name="remember" <?= !empty($_SESSION['old_email']) ? 'checked' : '' ?>>
                        <span>Remember me</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>

                <p class="auth-foot">Don't have an account?
                    <a href="index.php?page=register">Register</a>
                </p>
            </div>
        </div>
    </div>

</body>

</html>