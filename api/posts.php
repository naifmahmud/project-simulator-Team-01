<?php
// Posts API - search, filter, detail, comments
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/WishlistController.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/scout/PostRequest.php';

header('Content-Type: application/json');
$conn = getDbConnection();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
action:
$action = $_GET['action'] ?? $_GET['a'] ?? '';

// Read JSON body for POST/DELETE
$input = [];
if (in_array($method, ['POST', 'DELETE'])) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
}

try {
    if ($method === 'GET') {
        if ($action === 'search') {
            $q = trim($_GET['q'] ?? '');
            $country = $_GET['country'] ?? '';
            $genres = $_GET['genre'] ?? [];
            if (!is_array($genres) && $genres !== '') $genres = [$genres];
            $cost = $_GET['cost'] ?? '';
            $rows = filterApprovedPosts($conn, $country, $genres, $cost, $q);
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }
        if ($action === 'filter') {
            $country = $_GET['country'] ?? '';
            $genres = $_GET['genre'] ?? [];
            if (!is_array($genres) && $genres !== '') $genres = [$genres];
            $cost = $_GET['cost'] ?? '';
            $q = trim($_GET['q'] ?? '');
            $rows = filterApprovedPosts($conn, $country, $genres, $cost, $q);
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }
        if ($action === 'detail') {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid post id');
            $post = getPost($conn, $id);
            $comments = getCommentsByPostId($conn, $id);
            echo json_encode(['success' => true, 'data' => ['post' => $post, 'comments' => $comments]]);
            exit;
        }
    }

    if ($method === 'POST') {
        if ($action === 'comment') {
            // create comment
            if (!isLoggedIn()) throw new Exception('Unauthorized');
            $user = getCurrentUser();
            if ($user['role'] !== 'user' || $user['is_verified'] != 1) throw new Exception('Only verified general users can post comments');
            $postId = intval($input['post_id'] ?? ($_POST['post_id'] ?? 0));
            $content = trim($input['content'] ?? ($_POST['content'] ?? ''));
            if ($postId <= 0 || $content === '') throw new Exception('Invalid data');
            $ok = addComment($conn, $user['id'], $postId, $content);
            echo json_encode(['success' => $ok]);
            exit;
        }
        if ($action === 'delete_comment') {
            if (!isLoggedIn()) throw new Exception('Unauthorized');
            $user = getCurrentUser();
            $commentId = intval($input['comment_id'] ?? ($_POST['comment_id'] ?? 0));
            if ($commentId <= 0) throw new Exception('Invalid comment id');
            // only owner can delete (or admin)
            if ($user['role'] === 'admin') {
                $ok = deleteCommentById($conn, $commentId, null);
            } else {
                $ok = deleteCommentById($conn, $commentId, $user['id']);
            }
            echo json_encode(['success' => $ok]);
            exit;
        }
        // fallback: support filter via POST with JSON body
        if ($action === 'filter') {
            $country = $input['country'] ?? '';
            $genres = $input['genre'] ?? [];
            if (!is_array($genres) && $genres !== '') $genres = [$genres];
            $cost = $input['cost'] ?? '';
            $rows = filterApprovedPosts($conn, $country, $genres, $cost);
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }
    }

    if ($method === 'DELETE') {
        if ($action === 'comment') {
            if (!isLoggedIn()) throw new Exception('Unauthorized');
            $user = getCurrentUser();
            $commentId = intval($input['comment_id'] ?? 0);
            if ($commentId <= 0) throw new Exception('Invalid comment id');
            if ($user['role'] === 'admin') {
                $ok = deleteCommentById($conn, $commentId, null);
            } else {
                $ok = deleteCommentById($conn, $commentId, $user['id']);
            }
            echo json_encode(['success' => $ok]);
            exit;
        }
    }

    throw new Exception('Unknown action');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

?>