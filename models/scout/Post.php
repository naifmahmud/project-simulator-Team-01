<?php

function findPostById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function getLatestApprovedPosts($conn, $limit = 6) {
    $stmt = mysqli_prepare($conn, "SELECT id, title, short_history, country, genre, cost_level, status, created_at FROM posts WHERE status = 'approved' ORDER BY created_at DESC LIMIT ?");
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getAllApprovedPosts($conn) {
    $result = mysqli_query($conn, "SELECT id, title, short_history, country, genre, cost_level, status, created_at FROM posts WHERE status = 'approved' ORDER BY created_at DESC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getPostsByUserId($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE scout_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getPostsByStatus($conn, $status) {
    $stmt = mysqli_prepare($conn, "SELECT p.* FROM posts p WHERE p.status = ? ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function createPost($conn, $data) {
    $stmt = mysqli_prepare($conn, "INSERT INTO posts (title, short_history, country, genre, cost_level, travel_medium_info, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    mysqli_stmt_bind_param($stmt, 'ssssss', $data['title'], $data['short_history'], $data['country'], $data['genre'], $data['cost_level'], $data['travel_medium_info']);
    return mysqli_stmt_execute($stmt);
}

function updatePost($conn, $id, $data) {
    $fields = [];
    $types = '';
    $values = [];

    if (!empty($data['title'])) {
        $fields[] = 'title = ?';
        $types .= 's';
        $values[] = $data['title'];
    }
    if (!empty($data['short_history'])) {
        $fields[] = 'short_history = ?';
        $types .= 's';
        $values[] = $data['short_history'];
    }
    if (!empty($data['country'])) {
        $fields[] = 'country = ?';
        $types .= 's';
        $values[] = $data['country'];
    }
    if (!empty($data['genre'])) {
        $fields[] = 'genre = ?';
        $types .= 's';
        $values[] = $data['genre'];
    }
    if (!empty($data['cost_level'])) {
        $fields[] = 'cost_level = ?';
        $types .= 's';
        $values[] = $data['cost_level'];
    }
    if (!empty($data['travel_medium_info'])) {
        $fields[] = 'travel_medium_info = ?';
        $types .= 's';
        $values[] = $data['travel_medium_info'];
    }
    if (!empty($data['status'])) {
        $fields[] = 'status = ?';
        $types .= 's';
        $values[] = $data['status'];
    }

    if (empty($fields)) {
        return true;
    }

    $sql = 'UPDATE posts SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $types .= 'i';
    $values[] = $id;

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    return mysqli_stmt_execute($stmt);
}

function deletePost($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

function getAllPosts($conn) {
    $result = mysqli_query($conn, "SELECT p.* FROM posts p ORDER BY p.created_at DESC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Get approved posts by scout ID
 */
function getApprovedPostsByScout($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}
?>
