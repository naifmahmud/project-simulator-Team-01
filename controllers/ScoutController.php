<?php
// ================================================================
// SCOUT CONTROLLER - request handling + role-based logic
// ================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/PostRequest.php';
require_once __DIR__ . '/../models/Post.php';

/* ============== Show Create Request Form ============== */
function scoutCreateRequestCtrl($pdo) {
    requireScoutAccess();
    validateCsrfOnPost();
    require_once __DIR__ . '/../views/scout/create_request.php';
}

/* ============== Process Create Request ============== */
function scoutCreateRequestProcessCtrl($pdo) {
    requireScoutAccess();
    validateCsrfOnPost();
    
    $error = '';
    $old = ['title' => '', 'short_history' => '', 'country' => '', 'genre' => '', 'cost_level' => '', 'travel_medium_info' => ''];
    
    $title = trim($_POST['title'] ?? '');
    $shortHistory = trim($_POST['short_history'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $costLevel = $_POST['cost_level'] ?? '';
    $travelMediumInfo = trim($_POST['travel_medium_info'] ?? '');
    
    $old = compact('title', 'shortHistory', 'country', 'genre', 'costLevel', 'travelMediumInfo');
    
    if ($title === '' || $shortHistory === '' || $country === '') {
        $error = 'Title, short history, and country are required.';
    } elseif (strlen($title) > 200) {
        $error = 'Title must not exceed 200 characters.';
    } elseif (!in_array($genre, ['beach', 'mountain', 'city', 'historical', 'forest', 'desert', 'island'])) {
        $error = 'Invalid genre selected.';
    } elseif (!in_array($costLevel, ['free', 'low', 'medium', 'high'])) {
        $error = 'Invalid cost level selected.';
    } else {
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = scoutHandleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if ($error === '') {
            $requestData = [
                'scout_id' => $_SESSION['user_id'],
                'title' => $title,
                'short_history' => $shortHistory,
                'country' => $country,
                'genre' => $genre,
                'cost_level' => $costLevel,
                'travel_medium_info' => $travelMediumInfo,
                'image' => $imagePath
            ];
            
            if (createPostRequest($pdo, $requestData)) {
                header('Location: index.php?page=scout-my-requests&msg=added');
                exit;
            } else {
                $error = 'Failed to submit request. Try again.';
            }
        }
    }
    
    if ($error !== '') {
        $_SESSION['error'] = $error;
        $_SESSION['old'] = $old;
        header('Location: index.php?page=scout-create-request');
        exit;
    }
}

/* ============== Show My Requests ============== */
function scoutMyRequestsCtrl($pdo) {
    requireScoutAccess();
    
    $requests = getPostRequestsByUserId($pdo, $_SESSION['user_id']);
    require_once __DIR__ . '/../views/scout/my_requests.php';
}

/* ============== Show Edit Request Form ============== */
function scoutEditRequestCtrl($pdo) {
    requireScoutAccess();
    
    $id = intval($_GET['id'] ?? 0);
    $error = '';
    $request = null;
    
    if (!postRequestBelongsToUser($pdo, $id, $_SESSION['user_id'])) {
        $_SESSION['error'] = 'Request not found or access denied.';
        header('Location: index.php?page=scout-my-requests');
        exit;
    }
    
    $request = findPostRequestById($pdo, $id);
    
    if ($request && $request['status'] !== 'pending') {
        $_SESSION['error'] = 'Only pending requests can be edited.';
        header('Location: index.php?page=scout-my-requests');
        exit;
    }
    
    if ($request && !empty($request['post_data_decoded']) && is_array($request['post_data_decoded'])) {
        $request = array_merge($request, $request['post_data_decoded']);
    }
    
    validateCsrfOnPost();
    require_once __DIR__ . '/../views/scout/edit_request.php';
}

/* ============== Process Edit Request ============== */
function scoutEditRequestProcessCtrl($pdo) {
    requireScoutAccess();
    validateCsrfOnPost();
    
    $id = intval($_POST['request_id'] ?? 0);
    $error = '';
    $editing = null;
    
    if (!postRequestBelongsToUser($pdo, $id, $_SESSION['user_id'])) {
        $_SESSION['error'] = 'Request not found or access denied.';
        header('Location: index.php?page=scout-my-requests');
        exit;
    }
    
    $editing = findPostRequestById($pdo, $id);
    
    if ($editing && $editing['status'] !== 'pending') {
        $_SESSION['error'] = 'Only pending requests can be edited.';
        header('Location: index.php?page=scout-my-requests');
        exit;
    }
    
    $title = trim($_POST['title'] ?? '');
    $shortHistory = trim($_POST['short_history'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $costLevel = $_POST['cost_level'] ?? '';
    $travelMediumInfo = trim($_POST['travel_medium_info'] ?? '');
    
    if ($title === '' || $shortHistory === '' || $country === '') {
        $error = 'Title, short history, and country are required.';
        $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo];
    } elseif (strlen($title) > 200) {
        $error = 'Title must not exceed 200 characters.';
        $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo];
    } elseif (!in_array($genre, ['beach', 'mountain', 'city', 'historical', 'forest', 'desert', 'island'])) {
        $error = 'Invalid genre selected.';
        $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo];
    } elseif (!in_array($costLevel, ['free', 'low', 'medium', 'high'])) {
        $error = 'Invalid cost level selected.';
        $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo];
    } else {
        $imagePath = $editing['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = scoutHandleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                if ($editing['image'] && file_exists(__DIR__ . '/../' . $editing['image'])) {
                    unlink(__DIR__ . '/../' . $editing['image']);
                }
                $imagePath = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
                $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo, 'image' => $editing['image']];
            }
        }
        
        if ($error === '') {
            $updateData = [
                'title' => $title,
                'short_history' => $shortHistory,
                'country' => $country,
                'genre' => $genre,
                'cost_level' => $costLevel,
                'travel_medium_info' => $travelMediumInfo,
                'image' => $imagePath
            ];
            
            if (updatePostRequest($pdo, $id, $updateData)) {
                header('Location: index.php?page=scout-my-requests&msg=updated');
                exit;
            } else {
                $error = 'Failed to update request. Try again.';
                $editing = ['id' => $id, 'title' => $title, 'short_history' => $shortHistory, 'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel, 'travel_medium_info' => $travelMediumInfo, 'image' => $imagePath];
            }
        }
    }
    
    if ($error !== '') {
        $_SESSION['error'] = $error;
        $_SESSION['old'] = $request;
        header('Location: index.php?page=scout-edit-request&id=' . $id);
        exit;
    }
}

/* ============== Show Approved Posts ============== */
function scoutApprovedPostsCtrl($pdo) {
    requireScoutAccess();
    
    $approvedPosts = getApprovedPostsByScout($pdo, $_SESSION['user_id']);
    require_once __DIR__ . '/../views/scout/approved_posts.php';
}

/* ============== Handle Image Upload ============== */
function scoutHandleImageUpload($file) {
    $uploadDir = __DIR__ . '/../uploads/posts/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size must not exceed 5MB'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, and PNG files are allowed'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('post_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => 'uploads/posts/' . $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to save file'];
    }
}
?>