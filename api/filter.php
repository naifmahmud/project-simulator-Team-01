<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';

$conn = getDbConnection();

$q = $_GET['q'] ?? '';
$country = $_GET['country'] ?? '';
$cost = $_GET['cost'] ?? '';
$genres = $_GET['genre'] ?? [];

if (!is_array($genres)) {
    $genres = [];
}

$posts = filterApprovedPosts($conn, $country, $genres, $cost, $q);

echo json_encode([
    'success' => true,
    'data' => $posts
]);
?>
