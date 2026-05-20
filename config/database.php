<?php
// ================================================================
// Database Connection (procedural mysqli)
// ================================================================

function getDbConnection() {
    $conn = mysqli_connect('localhost', 'root', '', 'testing');
    if (!$conn) {
        die('Database connection failed: ' . mysqli_connect_error());
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

// One-time auto-seed of default admin (email: admin@travelguide.com / password: admin123)
// Runs only when users table has no admin.
$conn = getDbConnection();
$check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($check && mysqli_num_rows($check) === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
    mysqli_stmt_bind_param($stmt, 'ss', $name, $email);
    $name = 'Admin User';
    $email = 'admin@travelguide.com';
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
