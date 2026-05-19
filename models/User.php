<?php
function getUsers($conn, $limit = 20, $offset = 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, role, is_verified, created_at
         FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getUser($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, role, is_verified FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function searchUsers($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, role, is_verified, created_at
         FROM users
         WHERE name LIKE ? OR email LIKE ? OR role LIKE ?
         ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getUserCount($conn) {
    $r   = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM users");
    $row = mysqli_fetch_assoc($r);
    return (int) $row['cnt'];
}

function getUserCountByRole($conn, $role) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM users WHERE role = ?");
    mysqli_stmt_bind_param($stmt, 's', $role);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (int) $row['cnt'];
}

function emailExists($conn, $email, $excludeId = 0) {
    if ($excludeId) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function addUser($conn, $name, $email, $password, $role, $is_verified) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, is_verified, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $hash, $role, $is_verified);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUser($conn, $id, $name, $email, $role) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $role, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function verifyUser($conn, $id, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteUser($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM post_requests WHERE scout_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE scout_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
