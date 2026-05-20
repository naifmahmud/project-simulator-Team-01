<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/csrf.php';

$conn = getDbConnection();

$page = $_GET['page'] ?? 'home';

$publicPages = ['login', 'register', 'home'];

$protectedPages = [
    'profile',
    'wishlist',
    'logout',
    'verification-notice',
    'browse'
];

if (!in_array($page, ['logout'])) {
    require_once __DIR__ . '/controllers/AuthController.php';
    checkRememberMe($conn);
}

if ($page === 'logout') {
    require_once __DIR__ . '/controllers/AuthController.php';
    logout($conn);
}

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

if ($page === 'verification-notice') {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    require_once __DIR__ . '/controllers/AuthController.php';
    showVerificationNotice($conn);
    exit;
}

if ($page === 'home') {
    require_once __DIR__ . '/controllers/HomeController.php';
    showHome($conn);
    exit;
}

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

if ($page === 'wishlist') {
    require_once __DIR__ . '/controllers/WishlistController.php';
    showWishlist($conn);
    exit;
}

if ($page === 'browse') {
    require_once __DIR__ . '/controllers/BrowseController.php';
    showBrowse($conn);
    exit;
}

if ($page === 'post') {
    require_once __DIR__ . '/controllers/BrowseController.php';
    if (isset($_GET['id'])) {
        showPost($conn);
    } else {
        header('Location: index.php?page=browse');
    }
    exit;
}

if (in_array($page, ['scout-create-request', 'scout-my-requests', 'scout-edit-request', 'scout-approved-posts'], true)) {
    require_once __DIR__ . '/controllers/ScoutController.php';

    if ($page === 'scout-create-request') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            scoutCreateRequestProcessCtrl($conn);
        } else {
            scoutCreateRequestCtrl($conn);
        }
        exit;
    }

    if ($page === 'scout-my-requests') {
        scoutMyRequestsCtrl($conn);
        exit;
    }

    if ($page === 'scout-edit-request') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            scoutEditRequestProcessCtrl($conn);
        } else {
            scoutEditRequestCtrl($conn);
        }
        exit;
    }

    if ($page === 'scout-approved-posts') {
        scoutApprovedPostsCtrl($conn);
        exit;
    }
}

if ($page === 'scout-delete-request') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    scoutDeleteRequestCtrl($conn);
    exit;
}

if ($page === 'scout-request-change') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    scoutRequestChangeCtrl($conn);
    exit;
}

if ($page === 'admin') {
    require_once __DIR__ . '/controllers/AdminController.php';
    requireAdminAccess();

    $section = $_GET['section'] ?? 'dashboard';

    if ($section === 'dashboard') {
        adminDashboardCtrl($conn);
        exit;
    }

    if ($section === 'users') {
        adminUsersCtrl($conn);
        exit;
    }

    if ($section === 'posts') {
        adminPostsCtrl($conn);
        exit;
    }

    if ($section === 'comments') {
        adminCommentsCtrl($conn);
        exit;
    }
}

if ($page === 'api') {
    $wishlistAction = $_GET['wishlist'] ?? '';
    if ($wishlistAction === 'add' || $wishlistAction === 'remove') {
        require_once __DIR__ . '/api/wishlist.php';
        exit;
    }

    $action = $_GET['action'] ?? '';
    if ($action === 'filter') {
        require_once __DIR__ . '/api/filter.php';
        exit;
    }

    $apiResource = $_GET['resource'] ?? '';
    if ($apiResource === 'comments') {
        require_once __DIR__ . '/api/comments.php';
        exit;
    }

    exit;
}

// Handle AJAX requests
if ($page === 'ajax') {
    header('Content-Type: application/json');

    require_once __DIR__ . '/config/session.php';
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    require_once __DIR__ . '/models/Post.php';
    $type = $_GET['type'] ?? '';
    $q = trim($_GET['q'] ?? '');

    if ($type === 'comments') {
        $conn = getDbConnection();
        $all_comments = getComments($conn);

        if (empty($q)) {
            echo json_encode($all_comments);
        } else {
            $q_lower = strtolower($q);
            $filtered = array_filter($all_comments, function ($c) use ($q_lower) {
                return strpos(strtolower($c['content']), $q_lower) !== false ||
                    strpos(strtolower($c['user_name'] ?? ''), $q_lower) !== false ||
                    strpos(strtolower($c['post_title'] ?? ''), $q_lower) !== false;
            });
            echo json_encode(array_values($filtered));
        }
        exit;
    }

    echo json_encode([]);
    exit;
}

header('Location: index.php?page=home');
exit;
