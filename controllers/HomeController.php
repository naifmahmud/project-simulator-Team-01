<?php
// ================================================================
// Home Controller
// Handles home page display
// ================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Post.php';

/* ============== Show Home Page ============== */
function showHome($conn) {
    // Check for "Remember Me" auto-login
    require_once __DIR__ . '/AuthController.php';
    checkRememberMe($conn);
    
    // Check if user needs verification
    if (needsVerification()) {
        require_once __DIR__ . '/AuthController.php';
        showVerificationNotice($conn);
        return;
    }
    
    // Get latest approved post for home
    $latestPosts = getLatestApproved($conn, 1);
    
    require_once __DIR__ . '/../views/home.php';
}
?>
