<?php
session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/csrf.php';

// Get page parameter
$page = $_GET['page'] ?? 'scout-my-requests';

// Handle scout create request page
if ($page === 'scout-create-request') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    $pdo = getDbConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        scoutCreateRequestProcessCtrl($pdo);
    } else {
        scoutCreateRequestCtrl($pdo);
    }
    exit;
}

// Handle scout my requests page
if ($page === 'scout-my-requests') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    $pdo = getDbConnection();
    scoutMyRequestsCtrl($pdo);
    exit;
}

// Handle scout edit request page
if ($page === 'scout-edit-request') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    $pdo = getDbConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        scoutEditRequestProcessCtrl($pdo);
    } else {
        scoutEditRequestCtrl($pdo);
    }
    exit;
}

// Handle scout approved posts page
if ($page === 'scout-approved-posts') {
    require_once __DIR__ . '/controllers/ScoutController.php';
    $pdo = getDbConnection();
    scoutApprovedPostsCtrl($pdo);
    exit;
}

// Default: redirect to scout my requests dashboard
header('Location: index.php?page=scout-my-requests');
exit;
?>
