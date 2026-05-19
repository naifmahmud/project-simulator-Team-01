<?php
session_start();

require '../../config/database.php';
require '../../models/User.php';

header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$id     = intval($_GET['id']     ?? 0);
$status = intval($_GET['status'] ?? 1);

if ($id <= 0 || $id === intval($_SESSION['user_id'] ?? 0)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ok = verifyUser($conn, $id, $status);
echo json_encode(['success' => $ok]);

mysqli_close($conn);
