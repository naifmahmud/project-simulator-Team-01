<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Post.php';

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
    $countries = getDistinctCountries($conn);
    $genres = getDistinctGenres($conn);
    
    require_once __DIR__ . '/../views/browse.php';
}

function showPost($conn) {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Location: index.php?page=browse');
        exit;
    }

    // Ensure remember me / verification logic same as browse
    require_once __DIR__ . '/AuthController.php';
    checkRememberMe($conn);
    if (needsVerification()) {
        showVerificationNotice($conn);
        return;
    }

    $post = getPost($conn, $id);
    if (!$post || $post['status'] !== 'approved') {
        header('Location: index.php?page=browse');
        exit;
    }

    $comments = getCommentsByPostId($conn, $id);

    require_once __DIR__ . '/../views/post.php';
}

?>
