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

// Route based on method
switch ($requestMethod) {
    case 'POST':
        // Add to wishlist
        $input = json_decode(file_get_contents('php://input'), true);
        $_POST['post_id'] = $input['post_id'] ?? 0;
        handleAddToWishlist($conn);
        break;
        
    case 'DELETE':
        // Remove from wishlist
        $input = json_decode(file_get_contents('php://input'), true);
        $_POST['post_id'] = $input['post_id'] ?? 0;
        handleRemoveFromWishlist($conn);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
