<?php

function findPostRequestById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($row && !empty($row['post_data'])) {
        $row['post_data_decoded'] = json_decode($row['post_data'], true);
    }

    return $row;
}

function getPostRequestsByUserId($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE scout_id = ? ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $results = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

    foreach ($results as &$request) {
        if (!empty($request['post_data'])) {
            $request['post_data_decoded'] = json_decode($request['post_data'], true);
        }
    }

    return $results;
}

function getPendingPostRequestsByUserId($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE scout_id = ? AND status = 'pending' ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $results = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

    foreach ($results as &$request) {
        if (!empty($request['post_data'])) {
            $request['post_data_decoded'] = json_decode($request['post_data'], true);
        }
    }

    return $results;
}

function getPostRequestsByStatus($conn, $status) {
    $stmt = mysqli_prepare($conn, "SELECT pr.*, u.name as scout_name, u.email as scout_email FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.status = ? ORDER BY pr.requested_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $results = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

    foreach ($results as &$request) {
        if (!empty($request['post_data'])) {
            $request['post_data_decoded'] = json_decode($request['post_data'], true);
        }
    }

    return $results;
}

function createPostRequest($conn, $data) {
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

    $jsonData = json_encode($postData);
    $stmt = mysqli_prepare($conn, "INSERT INTO post_requests (scout_id, post_data, status, requested_at) VALUES (?, ?, 'pending', NOW())");
    mysqli_stmt_bind_param($stmt, 'is', $data['scout_id'], $jsonData);
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }

    return false;
}

function updatePostRequest($conn, $id, $data) {
    $existing = findPostRequestById($conn, $id);
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

    $jsonData = json_encode($postData);
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET post_data = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $jsonData, $id);
    return mysqli_stmt_execute($stmt);
}

function updatePostRequestStatus($conn, $id, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    return mysqli_stmt_execute($stmt);
}

function deletePostRequest($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}

function postRequestBelongsToUser($conn, $requestId, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM post_requests WHERE id = ? AND scout_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $requestId, $scoutId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result && mysqli_num_rows($result) > 0;
}

function getAllPostRequests($conn) {
    $result = mysqli_query($conn, "SELECT pr.*, u.name as scout_name, u.email as scout_email FROM post_requests pr JOIN users u ON pr.scout_id = u.id ORDER BY pr.requested_at DESC");
    $results = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

    foreach ($results as &$request) {
        if (!empty($request['post_data'])) {
            $request['post_data_decoded'] = json_decode($request['post_data'], true);
        }
    }

    return $results;
}

function getPendingRequestCount($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM post_requests WHERE status = 'pending'");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row ? intval($row['cnt']) : 0;
}
?>
