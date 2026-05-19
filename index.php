<?php
session_start();

require 'config/database.php';
require 'models/User.php';
require 'models/Post.php';
require 'models/PostRequest.php';
require 'models/Comment.php';
require 'controllers/AdminController.php';

$page    = $_GET['page']    ?? 'admin';
$section = $_GET['section'] ?? '';

if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?page=admin');
    exit;
}

if (empty($_SESSION['role'])) {
    $check = mysqli_query($conn, "SELECT id, name, role FROM users WHERE role = 'admin' LIMIT 1");
    if ($check && $row = mysqli_fetch_assoc($check)) {
        $_SESSION['user_id']   = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['role']      = $row['role'];
    }
}

if ($page !== 'logout' && ($_SESSION['role'] ?? '') !== 'admin') {
    die('Access denied. Admin privileges required.');
}

if ($page === 'ajax') {
    header('Content-Type: application/json');

    $type = $_GET['type'] ?? '';
    $q    = trim($_GET['q'] ?? '');

    if ($type === 'users') {
        echo json_encode($q === '' ? getUsers($conn, 100, 0) : searchUsers($conn, $q));
    } elseif ($type === 'posts') {
        echo json_encode($q === '' ? getApprovedPosts($conn) : searchPosts($conn, $q));
    } elseif ($type === 'comments') {
        echo json_encode($q === '' ? getComments($conn) : searchComments($conn, $q));
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown type']);
    }
    exit;
}

switch ($page) {
    case 'admin':
        switch ($section) {
            case 'users':    adminUsersCtrl($conn);     break;
            case 'posts':    adminPostsCtrl($conn);     break;
            case 'comments': adminCommentsCtrl($conn);  break;
            default:         adminDashboardCtrl($conn); break;
        }
        break;

    default:
        header('Location: index.php?page=admin');
        exit;
}

mysqli_close($conn);
