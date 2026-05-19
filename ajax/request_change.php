<?php
// ================================================================
// AJAX Endpoint - Request Changes for Approved Post
// ================================================================

session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a verified scout
if (!isset($_SESSION['user_id']) || 
    $_SESSION['user_role'] !== 'scout' || 
    $_SESSION['user_is_verified'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/PostRequest.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['post_id'] ?? 0;

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

$pdo = getDbConnection();
$postModel = new Post($pdo);
$postRequestModel = new PostRequest($pdo);

// Get the approved post
$post = $postModel->findById($postId);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

// Verify the post belongs to the logged-in scout
if ($post['scout_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You can only request changes for your own posts']);
    exit;
}

// Verify the post is approved
if ($post['status'] !== 'approved') {
    echo json_encode(['success' => false, 'message' => 'Only approved posts can have change requests']);
    exit;
}

// Create a new post request with the approved post data
$requestData = [
    'user_id' => $_SESSION['user_id'],
    'original_post_id' => $postId,
    'title' => $post['title'],
    'short_history' => $post['short_history'],
    'country' => $post['country'],
    'genre' => $post['genre'],
    'cost_level' => $post['cost_level'],
    'travel_medium_info' => $post['travel_medium_info'],
    'image' => $post['image']
];

$newRequestId = $postRequestModel->create($requestData);

if ($newRequestId) {
    echo json_encode([
        'success' => true, 
        'message' => 'Change request created successfully',
        'request_id' => $newRequestId
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create change request']);
}
?>
