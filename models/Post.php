<?php
// ================================================================
// Post Model - Database operations for posts table
// Uses PDO with prepared statements
// ================================================================

/**
 * Find post by ID
 */
function findPostById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get latest approved posts
 */
function getLatestApprovedPosts($pdo, $limit = 6) {
    $stmt = $pdo->prepare("SELECT id, title, short_history, country, genre, cost_level, status, created_at FROM posts WHERE status = 'approved' ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get all approved posts
 */
function getAllApprovedPosts($pdo) {
    $stmt = $pdo->prepare("SELECT id, title, short_history, country, genre, cost_level, status, created_at FROM posts WHERE status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get posts by user
 */
function getPostsByUserId($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get posts by status
 */
function getPostsByStatus($pdo, $status) {
    $stmt = $pdo->prepare("SELECT p.* FROM posts p WHERE p.status = ? ORDER BY p.created_at DESC");
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

/**
 * Create a new post
 */
function createPost($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO posts (title, short_history, country, genre, cost_level, travel_medium_info, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    return $stmt->execute([
        $data['title'],
        $data['short_history'],
        $data['country'],
        $data['genre'],
        $data['cost_level'],
        $data['travel_medium_info']
    ]);
}

/**
 * Update post
 */
function updatePost($pdo, $id, $data) {
    $fields = [];
    $values = [];
    
    if (!empty($data['title'])) {
        $fields[] = "title = ?";
        $values[] = $data['title'];
    }
    if (!empty($data['short_history'])) {
        $fields[] = "short_history = ?";
        $values[] = $data['short_history'];
    }
    if (!empty($data['country'])) {
        $fields[] = "country = ?";
        $values[] = $data['country'];
    }
    if (!empty($data['genre'])) {
        $fields[] = "genre = ?";
        $values[] = $data['genre'];
    }
    if (!empty($data['cost_level'])) {
        $fields[] = "cost_level = ?";
        $values[] = $data['cost_level'];
    }
    if (!empty($data['travel_medium_info'])) {
        $fields[] = "travel_medium_info = ?";
        $values[] = $data['travel_medium_info'];
    }
    if (!empty($data['status'])) {
        $fields[] = "status = ?";
        $values[] = $data['status'];
    }
    
    if (empty($fields)) {
        return true;
    }
    
    $values[] = $id;
    $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Delete post
 */
function deletePost($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Get all posts
 */
function getAllPosts($pdo) {
    $stmt = $pdo->query("SELECT p.* FROM posts p ORDER BY p.created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Get approved posts by scout ID
 */
function getApprovedPostsByScout($pdo, $scoutId) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt->execute([$scoutId]);
    return $stmt->fetchAll();
}
?>
