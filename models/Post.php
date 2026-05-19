<?php
function getApprovedPosts($conn) {
    $r = mysqli_query($conn,
        "SELECT p.*, u.name AS scout_name
         FROM posts p
         LEFT JOIN users u ON p.scout_id = u.id
         WHERE p.status = 'approved'
         ORDER BY p.created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getPost($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.name AS scout_name
         FROM posts p
         LEFT JOIN users u ON p.scout_id = u.id
         WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function searchPosts($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, u.name AS scout_name
         FROM posts p
         LEFT JOIN users u ON p.scout_id = u.id
         WHERE p.status = 'approved'
         AND (p.title LIKE ? OR p.country LIKE ? OR u.name LIKE ?)
         ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApprovedPostCount($conn) {
    $r   = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM posts WHERE status = 'approved'");
    $row = mysqli_fetch_assoc($r);
    return (int) $row['cnt'];
}

function updatePost($conn, $id, $title, $short_history, $country, $genre, $cost_level, $travel_medium_info) {
    $stmt = mysqli_prepare($conn,
        "UPDATE posts
         SET title = ?, short_history = ?, country = ?,
             genre = ?, cost_level = ?, travel_medium_info = ?, updated_at = NOW()
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssssi',
        $title, $short_history, $country, $genre, $cost_level, $travel_medium_info, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deletePost($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
