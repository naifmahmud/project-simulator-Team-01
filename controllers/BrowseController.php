<?php
// ================================================================
// Browse Controller
// Handles browsing all approved posts
// ================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Post.php';

/* ============== Show Browse Page ============== */
function showBrowse($conn) {
    // Check for "Remember Me" auto-login
    require_once __DIR__ . '/AuthController.php';
    checkRememberMe($conn);
    
    // Check if user needs verification
    if (needsVerification()) {
        require_once __DIR__ . '/AuthController.php';
        showVerificationNotice($conn);
        return;
    }
    
    // Get all approved posts
    $latestPosts = getAllApproved($conn);
    
    require_once __DIR__ . '/../views/browse.php';
}
?>
