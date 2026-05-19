<?php
// ================================================================
// AJAX Endpoint - Delete Post Request
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
require_once __DIR__ . '/../models/PostRequest.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$requestId = $input['request_id'] ?? 0;

if (!$requestId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

$pdo = getDbConnection();
$postRequestModel = new PostRequest($pdo);

// Validate request exists and belongs to user
if (!$postRequestModel->belongsToUser($requestId, $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
    exit;
}

$request = $postRequestModel->findById($requestId);

// Only allow deleting pending requests
if ($request['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Only pending requests can be deleted']);
    exit;
}

// Delete image file if exists
if (!empty($request['image'])) {
    $imagePath = __DIR__ . '/../' . $request['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Delete request from database
if ($postRequestModel->delete($requestId)) {
    echo json_encode(['success' => true, 'message' => 'Request deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete request']);
}
?>
