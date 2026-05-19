<?php
// ================================================================
// Wishlist Controller
// Handles wishlist operations
// ================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../models/Post.php';

/* ============== Show Wishlist Page ============== */
function showWishlist($conn) {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    
    if (!isUserVerified()) {
        header('Location: index.php?page=verification-notice');
        exit;
    }
    
    $wishlistItems = getUserWishlist($conn, $_SESSION['user_id']);
    
    require_once __DIR__ . '/../views/wishlist.php';
}

/* ============== Add to Wishlist (AJAX) ============== */
function handleAddToWishlist($conn) {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
        return;
    }
    
    if (!isUserVerified()) {
        jsonResponse(false, 'Account not verified');
        return;
    }
    
    $postId = intval($_POST['post_id'] ?? 0);
    
    if ($postId <= 0) {
        jsonResponse(false, 'Invalid post ID');
        return;
    }
    
    // Check if post exists and is approved
    $post = findPostById($conn, $postId);
    if (!$post || $post['status'] !== 'approved') {
        jsonResponse(false, 'Post not found or not approved');
        return;
    }
    
    // Check if already in wishlist
    if (isInWishlist($conn, $_SESSION['user_id'], $postId)) {
        jsonResponse(false, 'Post already in wishlist');
        return;
    }
    
    // Add to wishlist
    if (addToWishlist($conn, $_SESSION['user_id'], $postId)) {
        jsonResponse(true, 'Added to wishlist');
    } else {
        jsonResponse(false, 'Failed to add to wishlist');
    }
}

/* ============== Remove from Wishlist (AJAX) ============== */
function handleRemoveFromWishlist($conn) {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
        return;
    }
    
    if (!isUserVerified()) {
        jsonResponse(false, 'Account not verified');
        return;
    }
    
    $postId = intval($_POST['post_id'] ?? 0);
    
    if ($postId <= 0) {
        jsonResponse(false, 'Invalid post ID');
        return;
    }
    
    // Check if item exists and belongs to user
    $userId = $_SESSION['user_id'];
    if (!isInWishlist($conn, $userId, $postId)) {
        jsonResponse(false, 'Post not in wishlist');
        return;
    }
    
    // Remove from wishlist
    if (removeFromWishlist($conn, $userId, $postId)) {
        jsonResponse(true, 'Removed from wishlist');
    } else {
        jsonResponse(false, 'Failed to remove from wishlist');
    }
}

/* ============== Send JSON Response ============== */
function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}
?>
