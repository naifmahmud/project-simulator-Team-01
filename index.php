<?php
// ================================================================
// Front Controller - Main entry point
// Handles routing for all pages
// ================================================================

session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/csrf.php';

// Get database connection
$conn = getDbConnection();

// Get page parameter
$page = $_GET['page'] ?? 'home';

// Define public pages (no login required)
$publicPages = ['login', 'register', 'home'];

// Define pages that require login
$protectedPages = [
    'profile', 'wishlist', 'logout',
    'verification-notice', 'browse'
];

// Check for "Remember Me" cookie on every page load
if (!in_array($page, ['logout'])) {
    require_once __DIR__ . '/controllers/AuthController.php';
    checkRememberMe($conn);
}

// Handle logout
if ($page === 'logout') {
    require_once __DIR__ . '/controllers/AuthController.php';
    logout($conn);
}

// Handle registration
if ($page === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/controllers/AuthController.php';
        register($conn);
    } else {
        require_once __DIR__ . '/controllers/AuthController.php';
        showRegister($conn);
    }
    exit;
}

// Handle login
if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/controllers/AuthController.php';
        login($conn);
    } else {
        require_once __DIR__ . '/controllers/AuthController.php';
        showLogin($conn);
    }
    exit;
}

// Handle verification notice
if ($page === 'verification-notice') {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    require_once __DIR__ . '/controllers/AuthController.php';
    showVerificationNotice($conn);
    exit;
}

// Handle home page
if ($page === 'home') {
    require_once __DIR__ . '/controllers/HomeController.php';
    showHome($conn);
    exit;
}

// Handle profile page
if ($page === 'profile') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/controllers/ProfileController.php';
        updateProfile($conn);
    } else {
        require_once __DIR__ . '/controllers/ProfileController.php';
        showProfile($conn);
    }
    exit;
}

// Handle wishlist page
if ($page === 'wishlist') {
    require_once __DIR__ . '/controllers/WishlistController.php';
    showWishlist($conn);
    exit;
}

// Handle browse page
if ($page === 'browse') {
    require_once __DIR__ . '/controllers/BrowseController.php';
    showBrowse($conn);
    exit;
}

// Handle API requests
if ($page === 'api') {
    $action = $_GET['wishlist'] ?? '';
    
    if ($action === 'add') {
        require_once __DIR__ . '/api/wishlist.php';
        exit;
    }
    
    if ($action === 'remove') {
        require_once __DIR__ . '/api/wishlist.php';
        exit;
    }
    
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    exit;
}

// Default: redirect to home
header('Location: index.php?page=home');
exit;
?>
