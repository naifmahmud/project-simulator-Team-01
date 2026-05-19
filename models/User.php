<?php
// ================================================================
// User Model - Database operations for users table
// Uses procedural mysqli with prepared statements
// ================================================================

/* ------------------- User ------------------- */
function findUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function findUserById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function findUserByRememberToken($conn, $tokenHash) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE remember_token = ?");
    mysqli_stmt_bind_param($stmt, 's', $tokenHash);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function createUser($conn, $name, $email, $passwordHash, $role) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO users (name, email, password_hash, role, is_verified, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $isVerified = 0;
    mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $passwordHash, $role, $isVerified);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserProfile($conn, $id, $name, $email, $profilePicture = null, $passwordHash = null) {
    $fields = [];
    $types = '';
    $params = [];
    
    if ($name !== null) {
        $fields[] = "name = ?";
        $types .= 's';
        $params[] = $name;
    }
    if ($email !== null) {
        $fields[] = "email = ?";
        $types .= 's';
        $params[] = $email;
    }
    if ($profilePicture !== null) {
        $fields[] = "profile_picture = ?";
        $types .= 's';
        $params[] = $profilePicture;
    }
    if ($passwordHash !== null) {
        $fields[] = "password_hash = ?";
        $types .= 's';
        $params[] = $passwordHash;
    }
    
    if (empty($fields)) {
        return true;
    }
    
    $fields[] = "id = ?";
    $types .= 'i';
    $params[] = $id;
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateRememberToken($conn, $id, $tokenHash) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $tokenHash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function clearRememberToken($conn, $id) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function verifyUser($conn, $id) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function emailExists($conn, $email, $excludeId = null) {
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

function getAllUsers($conn) {
    $result = mysqli_query($conn, "SELECT id, name, email, role, is_verified, created_at FROM users ORDER BY id DESC");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getUsersByRole($conn, $role) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE role = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 's', $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
