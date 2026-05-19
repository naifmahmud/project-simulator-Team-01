<?php
function getPendingRequests($conn) {
    $r = mysqli_query($conn,
        "SELECT pr.*, u.name AS scout_name
         FROM post_requests pr
         LEFT JOIN users u ON pr.scout_id = u.id
         WHERE pr.status = 'pending'
         ORDER BY pr.requested_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getPostRequest($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT pr.*, u.name AS scout_name
         FROM post_requests pr
         LEFT JOIN users u ON pr.scout_id = u.id
         WHERE pr.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getPendingRequestCount($conn) {
    $r   = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM post_requests WHERE status = 'pending'");
    $row = mysqli_fetch_assoc($r);
    return (int) $row['cnt'];
}

function approvePostRequest($conn, $id) {
    $request = getPostRequest($conn, $id);
    if (!$request) return false;

    $postData           = json_decode($request['post_data'], true);
    $scoutId            = $request['scout_id'];
    $title              = $postData['title']              ?? '';
    $short_history      = $postData['short_history']      ?? '';
    $country            = $postData['country']            ?? '';
    $genre              = $postData['genre']              ?? '';
    $cost_level         = $postData['cost_level']         ?? 'free';
    $travel_medium_info = $postData['travel_medium_info'] ?? '';

    $stmt = mysqli_prepare($conn,
        "INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', NOW())");
    mysqli_stmt_bind_param($stmt, 'issssss',
        $scoutId, $title, $short_history, $country, $genre, $cost_level, $travel_medium_info);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = 'approved' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    return $ok;
}

function rejectPostRequest($conn, $id, $reason = '') {
    $stmt = mysqli_prepare($conn,
        "UPDATE post_requests SET status = 'rejected', rejection_reason = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $reason, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
