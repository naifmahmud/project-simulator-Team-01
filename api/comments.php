<?php
// Comments API - add, delete, list for post comments
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Post.php';

header('Content-Type: application/json');
$conn = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';
$resource = $_GET['resource'] ?? '';
$subAction = $_GET['sub'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: [];

if ($resource === 'comments') {
    $action = $subAction ?: $action;
}

// Support direct REST-style /api/comments/add and /api/comments/{id}
if ($action === '') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $prefix = rtrim($scriptDir, '/') . '/comments';
    if (strpos($requestUri, $prefix) === 0) {
        $path = trim(substr($requestUri, strlen($prefix)), '/');
        $segments = explode('/', $path);
        if ($segments[0] === 'add') {
            $action = 'add';
        } elseif ($segments[0] === 'post' && isset($segments[1])) {
            $_GET['post_id'] = intval($segments[1]);
            $action = 'list';
        } elseif (is_numeric($segments[0])) {
            $_GET['id'] = intval($segments[0]);
            $action = 'delete';
        }
    }
}

try {
    if ($method === 'GET' && $action === 'list') {
        $postId = intval($_GET['post_id'] ?? $input['post_id'] ?? 0);
        if ($postId <= 0) {
            throw new Exception('Invalid post id');
        }
        $comments = getCommentsByPostId($conn, $postId);
        echo json_encode(['success' => true, 'data' => $comments]);
        exit;
    }

    if ($method === 'POST' && $action === 'add') {
        if (!isLoggedIn()) {
            throw new Exception('Unauthorized');
        }
        $user = getCurrentUser();
        if ($user['role'] !== 'user' || !$user['is_verified']) {
            throw new Exception('Only verified general users can post comments');
        }

        $postId = intval($input['post_id'] ?? $_POST['post_id'] ?? 0);
        $content = trim($input['content'] ?? $_POST['content'] ?? '');
        $name = trim($input['name'] ?? $_POST['name'] ?? $user['name']);

        if ($postId <= 0) {
            throw new Exception('Invalid post id');
        }
        if ($content === '') {
            throw new Exception('Comment cannot be empty');
        }
        if (mb_strlen($content) > 500) {
            throw new Exception('Comment must be 500 characters or less');
        }

        $ok = addComment($conn, $user['id'], $postId, $content);
        if (!$ok) {
            throw new Exception('Failed to save comment');
        }

        $comments = getCommentsByPostId($conn, $postId);
        echo json_encode(['success' => true, 'data' => $comments]);
        exit;
    }

    if ($method === 'DELETE' && $action === 'delete') {
        if (!isLoggedIn()) {
            throw new Exception('Unauthorized');
        }
        $user = getCurrentUser();
        $commentId = intval($_GET['id'] ?? $input['comment_id'] ?? 0);
        if ($commentId <= 0) {
            throw new Exception('Invalid comment id');
        }

        $comment = getCommentById($conn, $commentId);
        if (!$comment) {
            throw new Exception('Comment not found');
        }

        if ($user['role'] === 'admin') {
            $ok = deleteCommentById($conn, $commentId, null);
        } else {
            $ok = deleteCommentById($conn, $commentId, $user['id']);
        }

        if (!$ok) {
            throw new Exception('Unable to delete comment');
        }

        $comments = getCommentsByPostId($conn, $comment['post_id']);
        echo json_encode(['success' => true, 'data' => $comments]);
        exit;
    }

    throw new Exception('Unknown action');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
