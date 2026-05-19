<?php
// ================================================================
// Post Model - Database operations for posts table
// Uses procedural mysqli with prepared statements
// ================================================================

/* ------------------- Post ------------------- */
function findPostById($conn, $id) {
    $stmt = mysqli_prepare($conn, "
        SELECT p.*
        FROM posts p
        WHERE p.id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getLatestApproved($conn, $limit = 6) {
    $stmt = mysqli_prepare($conn, "
        SELECT id, title, short_history, country, genre, cost_level, status, created_at
        FROM posts
        WHERE status = 'approved'
        ORDER BY created_at DESC
        LIMIT ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllApproved($conn) {
    $result = mysqli_query($conn, "
        SELECT id, title, short_history, country, genre, cost_level, status, created_at
        FROM posts
        WHERE status = 'approved'
        ORDER BY created_at DESC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPostsByUserId($conn, $userId) {
    $stmt = mysqli_prepare($conn, "
        SELECT * FROM posts WHERE id = ?
        ORDER BY created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPostsByStatus($conn, $status) {
    $stmt = mysqli_prepare($conn, "
        SELECT p.*
        FROM posts p
        WHERE p.status = ?
        ORDER BY p.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function createPost($conn, $title, $shortHistory, $country, $genre, $costLevel, $travelMediumInfo) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO posts (title, short_history, country, genre, cost_level, travel_medium_info, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    mysqli_stmt_bind_param($stmt, 'ssssss', $title, $shortHistory, $country, $genre, $costLevel, $travelMediumInfo);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updatePost($conn, $id, $title = null, $shortHistory = null, $country = null, $genre = null, $costLevel = null, $travelMediumInfo = null, $status = null) {
    $fields = [];
    $types = '';
    $params = [];
    
    if ($title !== null) {
        $fields[] = "title = ?";
        $types .= 's';
        $params[] = $title;
    }
    if ($shortHistory !== null) {
        $fields[] = "short_history = ?";
        $types .= 's';
        $params[] = $shortHistory;
    }
    if ($country !== null) {
        $fields[] = "country = ?";
        $types .= 's';
        $params[] = $country;
    }
    if ($genre !== null) {
        $fields[] = "genre = ?";
        $types .= 's';
        $params[] = $genre;
    }
    if ($costLevel !== null) {
        $fields[] = "cost_level = ?";
        $types .= 's';
        $params[] = $costLevel;
    }
    if ($travelMediumInfo !== null) {
        $fields[] = "travel_medium_info = ?";
        $types .= 's';
        $params[] = $travelMediumInfo;
    }
    if ($status !== null) {
        $fields[] = "status = ?";
        $types .= 's';
        $params[] = $status;
    }
    
    if (empty($fields)) {
        return true;
    }
    
    $fields[] = "id = ?";
    $types .= 'i';
    $params[] = $id;
    
    $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deletePost($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getAllPosts($conn) {
    $result = mysqli_query($conn, "
        SELECT p.*
        FROM posts p
        ORDER BY p.created_at DESC
    ");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
