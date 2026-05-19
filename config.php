<?php
// ================================================================
// Database Connection (procedural mysqli)
// ================================================================
$conn = mysqli_connect('localhost', 'root', '', 'library_db');
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// One-time auto-seed of default admin (username: admin / password: admin123)
// Runs only when admins table is empty.
$check = mysqli_query($conn, "SELECT id FROM admins LIMIT 1");
if ($check && mysqli_num_rows($check) === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password) VALUES ('admin', ?)");
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
