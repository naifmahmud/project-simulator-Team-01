<?php
// ================================================================
// Wishlist Model - Database operations for wishlist table
// Uses procedural mysqli with prepared statements
// ================================================================

/* ------------------- Wishlist ------------------- */
function getUserWishlist($conn, $userId) {
    $stmt = mysqli_prepare($conn, "
        SELECT w.*, p.title, p.country, p.cost_level, p.genre, p.short_history
        FROM wishlist w
        LEFT JOIN posts p ON w.post_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function isInWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "
        SELECT id FROM wishlist WHERE user_id = ? AND post_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function addToWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO wishlist (user_id, post_id, added_at)
        VALUES (?, ?, NOW())
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function removeFromWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "
        DELETE FROM wishlist WHERE user_id = ? AND post_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function belongsToUser($conn, $wishlistId, $userId) {
    $stmt = mysqli_prepare($conn, "
        SELECT id FROM wishlist WHERE id = ? AND user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $wishlistId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function getWishlistById($conn, $wishlistId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $wishlistId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
?>
