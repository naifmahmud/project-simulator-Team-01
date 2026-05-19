<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/User.php';

function showRegister($conn) {
    validateCsrfOnPost();
    require_once __DIR__ . '/../views/register.php';
}

function register($conn) {
    validateCsrfOnPost();
    
    $errors = [];
    
    // Get and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validation
    if ($name === '') {
        $errors['name'] = 'Name is required';
    }
    
    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (emailExists($conn, $email)) {
        $errors['email'] = 'Email already registered';
    }
    
    if ($password === '') {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!in_array($role, ['admin', 'scout', 'user'])) {
        $errors['role'] = 'Invalid role selected';
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];
        header('Location: index.php?page=register');
        exit;
    }
    
    // Create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if (createUser($conn, $name, $email, $passwordHash, $role)) {
        $_SESSION['success'] = 'Registration successful! Please wait for admin verification.';
        header('Location: index.php?page=login');
        exit;
    } else {
        $errors['general'] = 'Registration failed. Please try again.';
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];
        header('Location: index.php?page=register');
        exit;
    }
}

/* ============== Show Login Form ============== */
function showLogin($conn) {
    validateCsrfOnPost();
    require_once __DIR__ . '/../views/login.php';
}

/* ============== Process Login ============== */
function login($conn) {
    validateCsrfOnPost();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    if ($email === '' || $password === '') {
        $errors[] = 'Please fill in both email and password';
    } else {
        $user = findUserByEmail($conn, $email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            performLogin($conn, $user, $remember);
            return;
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
    
    $_SESSION['errors'] = $errors;
    $_SESSION['old_email'] = $email;
    header('Location: index.php?page=login');
    exit;
}

/* ============== Perform Login ============== */
function performLogin($conn, $user, $remember = false) {
    storeUserInSession($user);
    
    if ($remember) {
        // Generate secure random token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Store token hash in database
        updateRememberToken($conn, $user['id'], $tokenHash);
        
        // Set cookie (30 days)
        setcookie('remember_me', $token, time() + 86400 * 30, '/', '', false, true);
    }
    
    // Redirect to home for all users
    header('Location: index.php?page=home');
    exit;
}

/* ============== Logout User ============== */
function logout($conn) {
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        
        // Clear remember token in database
        clearRememberToken($conn, $userId);
        
        // Delete cookie
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    destroySession();
    header('Location: index.php?page=login');
    exit;
}

function checkRememberMe($conn) {
    if (isLoggedIn()) {
        return;
    }
    
    if (!empty($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $tokenHash = hash('sha256', $token);
        
        $user = findUserByRememberToken($conn, $tokenHash);
        
        if ($user && $user['is_verified'] == 1) {
            performLogin($conn, $user, false);
        }
    }
}

function showVerificationNotice($conn) {
    require_once __DIR__ . '/../views/verification-notice.php';
}
?>
