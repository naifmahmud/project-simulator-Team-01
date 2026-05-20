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

function getApprovedPostCount($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM posts WHERE status = 'approved'");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row ? intval($row['cnt']) : 0;
}

function getApprovedPosts($conn) {
    $result = mysqli_query($conn, "SELECT p.* FROM posts p WHERE p.status = 'approved' ORDER BY p.created_at DESC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT p.* FROM posts p WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getComments($conn) {
    $result = mysqli_query($conn, "
        SELECT c.*, u.name as user_name, p.title as post_title
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN posts p ON c.post_id = p.id
        ORDER BY c.created_at DESC
    ");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getCommentCount($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM comments");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row ? intval($row['cnt']) : 0;
}

function deleteComment($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getPendingRequests($conn) {
    $result = mysqli_query($conn, "SELECT pr.*, u.name as scout_name, u.email as scout_email FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.status = 'pending' ORDER BY pr.requested_at DESC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getDistinctCountries($conn) {
    $result = mysqli_query($conn, "SELECT DISTINCT country FROM posts WHERE status = 'approved' ORDER BY country ASC");
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_NUM) : [];
    return array_map(function($r){ return $r[0]; }, $rows);
}

function getDistinctGenres($conn) {
    $result = mysqli_query($conn, "SELECT DISTINCT genre FROM posts WHERE status = 'approved' ORDER BY genre ASC");
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_NUM) : [];
    return array_map(function($r){ return $r[0]; }, $rows);
}

// Comments and search/filter helpers for posts
function getCommentsByPostId($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name as user_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

function getCommentById($conn, $commentId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $commentId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getBaseCostForLevel($costLevel) {
    $map = [
        'free' => 0,
        'low' => 500,
        'medium' => 1500,
        'high' => 3000,
    ];
    return $map[$costLevel] ?? 0;
}

function addComment($conn, $userId, $postId, $content) {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (user_id, post_id, content, created_at) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, 'iis', $userId, $postId, $content);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteCommentById($conn, $commentId, $userId = null) {
    if ($userId === null) {
        $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $commentId);
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $commentId, $userId);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function searchApprovedPosts($conn, $q) {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, title, short_history, country, genre, cost_level FROM posts WHERE status = 'approved' AND (title LIKE ? OR country LIKE ?) ORDER BY created_at DESC LIMIT 50");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

function filterApprovedPosts($conn, $country = '', $genres = [], $cost = '', $q = '') {
    $sql = "SELECT id, title, short_history, country, genre, cost_level FROM posts WHERE status = 'approved'";
    $params = [];
    $types = '';

    if ($q !== '') {
        $sql .= " AND (title LIKE ? OR country LIKE ?)";
        $types .= 'ss';
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
    }

    if ($country !== '') {
        $sql .= " AND country = ?";
        $types .= 's';
        $params[] = $country;
    }

    if (!empty($genres)) {
        // build IN clause
        $placeholders = implode(',', array_fill(0, count($genres), '?'));
        $sql .= " AND genre IN ($placeholders)";
        $types .= str_repeat('s', count($genres));
        foreach ($genres as $g) $params[] = $g;
    }

    if ($cost !== '') {
        $sql .= " AND cost_level = ?";
        $types .= 's';
        $params[] = $cost;
    }

    $sql .= " ORDER BY created_at DESC LIMIT 100";

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

function approvePostRequest($conn, $id) {
    // Fetch request
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$request) return false;

    $postData = json_decode($request['post_data'], true);
    if (!$postData) return false;

    $title = $postData['title'] ?? '';
    $short_history = $postData['short_history'] ?? '';
    $country = $postData['country'] ?? '';
    $genre = $postData['genre'] ?? '';
    $cost_level = $postData['cost_level'] ?? 'free';
    $travel_medium_info = $postData['travel_medium_info'] ?? '';
    $scout_id = intval($request['scout_id'] ?? 0);

    if ($scout_id <= 0) {
        return false;
    }

    // Insert into posts with scout reference
    $stmt2 = mysqli_prepare($conn, "INSERT INTO posts (title, short_history, country, genre, cost_level, travel_medium_info, scout_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', NOW())");
    mysqli_stmt_bind_param($stmt2, 'sssssis', $title, $short_history, $country, $genre, $cost_level, $travel_medium_info, $scout_id);
    $ok = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    if ($ok) {
        // Mark request approved
        $stmt3 = mysqli_prepare($conn, "UPDATE post_requests SET status = 'approved' WHERE id = ?");
        mysqli_stmt_bind_param($stmt3, 'i', $id);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);
        return true;
    }

    return false;
}

function rejectPostRequest($conn, $id, $reason = '') {
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = 'rejected' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
?>
