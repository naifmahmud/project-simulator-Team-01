<?php
// ================================================================
// AJAX Endpoint - Update Post Request
// ================================================================

session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a verified scout
if (!isset($_SESSION['user_id']) || 
    $_SESSION['user_role'] !== 'scout' || 
    $_SESSION['user_is_verified'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PostRequest.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$requestId = $input['request_id'] ?? 0;
$title = trim($input['title'] ?? '');
$shortHistory = trim($input['short_history'] ?? '');
$country = trim($input['country'] ?? '');
$genre = $input['genre'] ?? '';
$costLevel = $input['cost_level'] ?? '';
$travelMediumInfo = trim($input['travel_medium_info'] ?? '');

// Validation
$errors = [];

if (!$requestId) {
    $errors[] = 'Invalid request ID';
}

if ($title === '') {
    $errors[] = 'Title is required';
} elseif (strlen($title) > 200) {
    $errors[] = 'Title must not exceed 200 characters';
}

if ($shortHistory === '') {
    $errors[] = 'Short history is required';
}

if ($country === '') {
    $errors[] = 'Country is required';
}

$validGenres = ['beach', 'mountain', 'city', 'historical', 'forest', 'desert', 'island'];
if (!in_array($genre, $validGenres)) {
    $errors[] = 'Invalid genre selected';
}

$validCostLevels = ['free', 'low', 'medium', 'high'];
if (!in_array($costLevel, $validCostLevels)) {
    $errors[] = 'Invalid cost level selected';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$pdo = getDbConnection();
$postRequestModel = new PostRequest($pdo);

// Validate request exists and belongs to user
if (!$postRequestModel->belongsToUser($requestId, $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
    exit;
}

$request = $postRequestModel->findById($requestId);

// Only allow updating pending requests
if ($request['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Only pending requests can be updated']);
    exit;
}

// Update request
$updateData = [
    'title' => $title,
    'short_history' => $shortHistory,
    'country' => $country,
    'genre' => $genre,
    'cost_level' => $costLevel,
    'travel_medium_info' => $travelMediumInfo
];

if ($postRequestModel->update($requestId, $updateData)) {
    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update request']);
}
?>
