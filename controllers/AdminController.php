<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/scout/PostRequest.php';

function adminDashboardCtrl($conn) {

    $totalUsers      = getUserCount($conn);
    $adminCount      = getUserCountByRole($conn, 'admin');
    $scoutCount      = getUserCountByRole($conn, 'scout');
    $userCount       = getUserCountByRole($conn, 'user');
    $pendingRequests = getPendingRequestCount($conn);
    $approvedPosts   = getApprovedPostCount($conn);
    $totalComments   = getCommentCount($conn);

    require 'views/admin/dashboard.php';
}

function adminUsersCtrl($conn) {
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name        = trim($_POST['name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $password    = $_POST['password'] ?? '';
        $role        = $_POST['role'] ?? 'user';
        $is_verified = isset($_POST['is_verified']) ? 1 : 0;

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (emailExists($conn, $email)) {
            $error = 'Email already registered.';
        } elseif (!in_array($role, ['admin', 'scout', 'user'])) {
            $error = 'Invalid role selected.';
        } else {
            if (addUser($conn, $name, $email, $password, $role, $is_verified)) {
                header('Location: index.php?page=admin&section=users&msg=added');
                exit;
            }
            $error = 'Failed to add user.';
        }
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id    = intval($_GET['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? 'user';

        if ($name === '' || $email === '') {
            $error   = 'Name and email are required.';
            $editing = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error   = 'Invalid email format.';
            $editing = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role];
        } elseif (emailExists($conn, $email, $id)) {
            $error   = 'Email is used by another user.';
            $editing = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role];
        } else {
            if (updateUser($conn, $id, $name, $email, $role)) {
                header('Location: index.php?page=admin&section=users&msg=updated');
                exit;
            }
            $error   = 'Update failed.';
            $editing = ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role];
        }
    }

    if ($action === 'edit' && !$editing) {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getUser($conn, $id);
    }

    if ($action === 'verify') {
        $id     = intval($_GET['id'] ?? 0);
        $status = intval($_GET['status'] ?? 1);
        if ($id > 0) verifyUserStatus($conn, $id, $status);
        header('Location: index.php?page=admin&section=users&msg=updated');
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0 && $id !== intval($_SESSION['user_id'] ?? 0)) {
            deleteUser($conn, $id);
        }
        header('Location: index.php?page=admin&section=users&msg=deleted');
        exit;
    }

    $currentPage = intval($_GET['p'] ?? 1);
    $limit       = 20;
    $offset      = ($currentPage - 1) * $limit;

    $users      = getUsers($conn, $limit, $offset);
    $totalUsers = getUserCount($conn);
    $totalPages = ceil($totalUsers / $limit);

    require 'views/admin/users.php';
}

function adminPostsCtrl($conn) {
    $action  = $_GET['action'] ?? 'pending';
    $error   = '';
    $editing = null;

    if (isset($_GET['approve'])) {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) approvePostRequest($conn, $id);
        header('Location: index.php?page=admin&section=posts&action=pending&msg=approved');
        exit;
    }

    if (isset($_GET['reject'])) {
        $id     = intval($_GET['id'] ?? 0);
        $reason = trim($_GET['reason'] ?? '');
        if ($id > 0) rejectPostRequest($conn, $id, $reason);
        header('Location: index.php?page=admin&section=posts&action=pending&msg=rejected');
        exit;
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id                 = intval($_GET['id'] ?? 0);
        $title              = trim($_POST['title'] ?? '');
        $short_history      = trim($_POST['short_history'] ?? '');
        $country            = trim($_POST['country'] ?? '');
        $genre              = trim($_POST['genre'] ?? '');
        $cost_level         = $_POST['cost_level'] ?? 'free';
        $travel_medium_info = trim($_POST['travel_medium_info'] ?? '');

        if ($title === '' || $country === '') {
            $error   = 'Title and country are required.';
            $editing = getPost($conn, $id);
        } else {
            if (updatePost($conn, $id, $title, $short_history, $country, $genre, $cost_level, $travel_medium_info)) {
                header('Location: index.php?page=admin&section=posts&action=approved&msg=updated');
                exit;
            }
            $error   = 'Update failed.';
            $editing = getPost($conn, $id);
        }
    }

    if ($action === 'edit' && !$editing) {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getPost($conn, $id);
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deletePost($conn, $id);
        header('Location: index.php?page=admin&section=posts&action=approved&msg=deleted');
        exit;
    }

    if ($action === 'pending' || $action === 'approve' || $action === 'reject') {
        $posts  = getPendingRequests($conn);
        $title  = 'Pending Post Requests';
        $action = 'pending';
    } else {
        $posts  = getApprovedPosts($conn);
        $title  = 'Approved Posts';
        $action = 'approved';
    }

    $pendingCount  = getPendingRequestCount($conn);
    $approvedCount = getApprovedPostCount($conn);

    require 'views/admin/posts.php';
}

function adminCommentsCtrl($conn) {
    if (isset($_GET['delete'])) {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deleteComment($conn, $id);
        header('Location: index.php?page=admin&section=comments&msg=deleted');
        exit;
    }

    $comments = getComments($conn);

    require 'views/admin/comments.php';
}
