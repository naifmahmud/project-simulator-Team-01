<?php
session_start();

require '../../config/database.php';
require '../../models/User.php';
require '../../models/Post.php';
require '../../models/PostRequest.php';

header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$ok = approvePostRequest($conn, $id);
echo json_encode(['success' => $ok]);

mysqli_close($conn);
