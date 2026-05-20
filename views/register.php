<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-body">

    <div class="auth-shell">
        <div class="auth-side">
            <div class="logo-big">📖</div>
            <h1>Join Travel Guide</h1>
            <p>Create your account and start exploring amazing destinations. Connect with travel scouts and build your personal wishlist.</p>
            <ul class="feature-list">
                <li>&#10003; Browse approved travel posts</li>
                <li>&#10003; Create your personal wishlist</li>
                <li>&#10003; Connect with travel scouts</li>
                <li>&#10003; Verified user benefits</li>
            </ul>
        </div>

        <div class="auth-form-wrap">
            <div class="auth-card">
                <h2>Create Account</h2>
                <p class="muted">Join our travel community today</p>

                <?php if (!empty($_SESSION['errors'])): ?>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?page=register" class="form" novalidate>
                    <?= getCsrfField() ?>

                    <div class="field">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                            value="<?= htmlspecialchars($_SESSION['old']['name'] ?? '') ?>"
                            placeholder="e.g. John Doe" required>
                        <?php if (!empty($_SESSION['errors']['name'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>"
                            placeholder="john@example.com" required>
                        <?php if (!empty($_SESSION['errors']['email'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['email']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="role">I am a...</label>
                        <select id="role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="admin" <?= ($_SESSION['old']['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="scout" <?= ($_SESSION['old']['role'] ?? '') === 'scout' ? 'selected' : '' ?>>Scout</option>
                            <option value="user" <?= ($_SESSION['old']['role'] ?? '') === 'user' ? 'selected' : '' ?>>General User</option>
                        </select>
                        <?php if (!empty($_SESSION['errors']['role'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['role']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password"
                                placeholder="Min 8 characters" required>
                            <?php if (!empty($_SESSION['errors']['password'])): ?>
                                <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['password']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Repeat password" required>
                            <?php if (!empty($_SESSION['errors']['confirm_password'])): ?>
                                <span class="error-msg"><?= htmlspecialchars($_SESSION['errors']['confirm_password']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>

                <p class="auth-foot">Already have an account?
                    <a href="index.php?page=login">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;

            // Clear existing errors
            document.querySelectorAll('.error-msg').forEach(el => el.remove());

            // Name validation
            const name = document.getElementById('name').value.trim();
            if (name === '') {
                document.getElementById('name').insertAdjacentHTML('afterend', '<span class="error-msg">Name is required</span>');
                valid = false;
            }

            // Email validation
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                document.getElementById('email').insertAdjacentHTML('afterend', '<span class="error-msg">Email is required</span>');
                valid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('email').insertAdjacentHTML('afterend', '<span class="error-msg">Invalid email format</span>');
                valid = false;
            }

            // Password validation
            const password = document.getElementById('password').value;
            if (password === '') {
                document.getElementById('password').insertAdjacentHTML('afterend', '<span class="error-msg">Password is required</span>');
                valid = false;
            } else if (password.length < 8) {
                document.getElementById('password').insertAdjacentHTML('afterend', '<span class="error-msg">Password must be at least 8 characters</span>');
                valid = false;
            }

            // Confirm password validation
            const confirmPassword = document.getElementById('confirm_password').value;
            if (confirmPassword !== password) {
                document.getElementById('confirm_password').insertAdjacentHTML('afterend', '<span class="error-msg">Passwords do not match</span>');
                valid = false;
            }

            // Role validation
            const role = document.getElementById('role').value;
            if (role === '') {
                document.getElementById('role').insertAdjacentHTML('afterend', '<span class="error-msg">Please select a role</span>');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>

</body>

</html>