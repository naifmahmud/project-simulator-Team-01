<?php
// ================================================================
// PostRequest Model - Database operations for post_requests table
// Uses PDO with prepared statements
// Adapted for table structure: id, scout_id, post_data (JSON), requested_at, status
// ================================================================

/**
 * Find post request by ID
 */
function findPostRequestById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM post_requests WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result && !empty($result['post_data'])) {
        $result['post_data_decoded'] = json_decode($result['post_data'], true);
    }
    
    return $result;
}

/**
 * Get all requests by scout ID
 */
function getPostRequestsByUserId($pdo, $scoutId) {
    $stmt = $pdo->prepare("SELECT * FROM post_requests WHERE scout_id = ? ORDER BY requested_at DESC");
    $stmt->execute([$scoutId]);
    $results = $stmt->fetchAll();
    
    foreach ($results as &$result) {
        if (!empty($result['post_data'])) {
            $result['post_data_decoded'] = json_decode($result['post_data'], true);
        }
    }
    
    return $results;
}

/**
 * Get pending requests by scout ID
 */
function getPendingPostRequestsByUserId($pdo, $scoutId) {
    $stmt = $pdo->prepare("SELECT * FROM post_requests WHERE scout_id = ? AND status = 'pending' ORDER BY requested_at DESC");
    $stmt->execute([$scoutId]);
    $results = $stmt->fetchAll();
    
    foreach ($results as &$result) {
        if (!empty($result['post_data'])) {
            $result['post_data_decoded'] = json_decode($result['post_data'], true);
        }
    }
    
    return $results;
}

/**
 * Get requests by status
 */
function getPostRequestsByStatus($pdo, $status) {
    $stmt = $pdo->prepare("SELECT pr.*, u.name as scout_name, u.email as scout_email FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.status = ? ORDER BY pr.requested_at DESC");
    $stmt->execute([$status]);
    $results = $stmt->fetchAll();
    
    foreach ($results as &$result) {
        if (!empty($result['post_data'])) {
            $result['post_data_decoded'] = json_decode($result['post_data'], true);
        }
    }
    
    return $results;
}

/**
 * Create a new post request
 */
function createPostRequest($pdo, $data) {
    $postData = [
        'title' => $data['title'],
        'short_history' => $data['short_history'],
        'country' => $data['country'],
        'genre' => $data['genre'],
        'cost_level' => $data['cost_level'],
        'travel_medium_info' => $data['travel_medium_info'],
        'image' => $data['image'] ?? null,
        'original_post_id' => $data['original_post_id'] ?? null
    ];
    
    $stmt = $pdo->prepare("INSERT INTO post_requests (scout_id, post_data, status, requested_at) VALUES (?, ?, 'pending', NOW())");
    $result = $stmt->execute([
        $data['scout_id'],
        json_encode($postData)
    ]);
    
    return $result ? $pdo->lastInsertId() : false;
}

/**
 * Update post request
 */
function updatePostRequest($pdo, $id, $data) {
    $existing = findPostRequestById($pdo, $id);
    if (!$existing) {
        return false;
    }
    
    $postData = json_decode($existing['post_data'], true);
    
    if (isset($data['title'])) {
        $postData['title'] = $data['title'];
    }
    if (isset($data['short_history'])) {
        $postData['short_history'] = $data['short_history'];
    }
    if (isset($data['country'])) {
        $postData['country'] = $data['country'];
    }
    if (isset($data['genre'])) {
        $postData['genre'] = $data['genre'];
    }
    if (isset($data['cost_level'])) {
        $postData['cost_level'] = $data['cost_level'];
    }
    if (isset($data['travel_medium_info'])) {
        $postData['travel_medium_info'] = $data['travel_medium_info'];
    }
    if (isset($data['image'])) {
        $postData['image'] = $data['image'];
    }
    
    $stmt = $pdo->prepare("UPDATE post_requests SET post_data = ? WHERE id = ?");
    return $stmt->execute([json_encode($postData), $id]);
}

/**
 * Update status
 */
function updatePostRequestStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE post_requests SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

/**
 * Delete post request
 */
function deletePostRequest($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM post_requests WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Check if request belongs to scout
 */
function postRequestBelongsToUser($pdo, $requestId, $scoutId) {
    $stmt = $pdo->prepare("SELECT id FROM post_requests WHERE id = ? AND scout_id = ?");
    $stmt->execute([$requestId, $scoutId]);
    return $stmt->rowCount() > 0;
}

/**
 * Get all requests (for admin)
 */
function getAllPostRequests($pdo) {
    $stmt = $pdo->query("SELECT pr.*, u.name as scout_name, u.email as scout_email FROM post_requests pr JOIN users u ON pr.scout_id = u.id ORDER BY pr.requested_at DESC");
    $results = $stmt->fetchAll();
    
    foreach ($results as &$result) {
        if (!empty($result['post_data'])) {
            $result['post_data_decoded'] = json_decode($result['post_data'], true);
        }
    }
    
    return $results;
}
?>