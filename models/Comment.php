<?php
function getComments($conn) {
    $r = mysqli_query($conn,
        "SELECT c.*, u.name AS user_name, p.title AS post_title
         FROM comments c
         LEFT JOIN users u ON c.user_id = u.id
         LEFT JOIN posts p ON c.post_id = p.id
         ORDER BY c.created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function searchComments($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT c.*, u.name AS user_name, p.title AS post_title
         FROM comments c
         LEFT JOIN users u ON c.user_id = u.id
         LEFT JOIN posts p ON c.post_id = p.id
         WHERE c.content LIKE ? OR u.name LIKE ? OR p.title LIKE ?
         ORDER BY c.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getCommentCount($conn) {
    $r   = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM comments");
    $row = mysqli_fetch_assoc($r);
    return (int) $row['cnt'];
}

function deleteComment($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
