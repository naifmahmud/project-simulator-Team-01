<?php
// ================================================================
// Wishlist API Endpoint
// Handles AJAX requests for wishlist operations
// ================================================================

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../controllers/WishlistController.php';

// Set content type
header('Content-Type: application/json');

// Get database connection
$conn = getDbConnection();

// Get request method
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Determine action from query string
$action = $_GET['wishlist'] ?? '';

// Read JSON body if present
$input = [];
if (in_array($requestMethod, ['POST', 'DELETE'])) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $_POST['post_id'] = $input['post_id'] ?? 0;
}

// Route: support POST (add) and DELETE (remove). Allow POST remove as a fallback when clients can't send DELETE.
if ($requestMethod === 'POST') {
    if ($action === 'remove') {
        handleRemoveFromWishlist($conn);
    } else {
        handleAddToWishlist($conn);
    }
} elseif ($requestMethod === 'DELETE') {
    handleRemoveFromWishlist($conn);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
