<?php
// ================================================================
// Session Helper Functions
// ================================================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

/**
 * Get current user data from session
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'is_verified' => $_SESSION['user_is_verified'] ?? 0,
        'profile_picture' => $_SESSION['user_profile_picture'] ?? null
    ];
}

/**
 * Check if user is verified
 */
function isUserVerified() {
    return !empty($_SESSION['user_is_verified']) && $_SESSION['user_is_verified'] == 1;
}

/**
 * Store user in session
 */
function storeUserInSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_is_verified'] = $user['is_verified'];
    $_SESSION['user_profile_picture'] = $user['profile_picture'];
}

/**
 * Destroy session and cleanup
 */
function destroySession() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Check if user needs verification
 */
function needsVerification() {
    return isLoggedIn() && !isUserVerified();
}

function requireScoutAccess() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }

    if ($_SESSION['user_role'] !== 'scout') {
        header('Location: index.php?page=home');
        exit;
    }

    if (needsVerification()) {
        header('Location: index.php?page=verification-notice');
        exit;
    }
}

function requireAdminAccess() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }

    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: index.php?page=home');
        exit;
    }

    if (needsVerification()) {
        header('Location: index.php?page=verification-notice');
        exit;
    }
}
?>
