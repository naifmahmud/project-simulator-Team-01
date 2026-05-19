<?php
// ================================================================
// Profile Controller
// Handles profile viewing and updating
// ================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/User.php';

/* ============== Show Profile Page ============== */
function showProfile($conn) {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    
    $user = findUserById($conn, $_SESSION['user_id']);
    
    if (!$user) {
        header('Location: index.php?page=logout');
        exit;
    }
    
    require_once __DIR__ . '/../views/profile.php';
}

/* ============== Process Profile Update ============== */
function updateProfile($conn) {
    validateCsrfOnPost();
    
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $user = findUserById($conn, $userId);
    
    $errors = [];
    $updatedData = [];
    
    // Get input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
    
    // Validate name
    if ($name === '') {
        $errors['name'] = 'Name is required';
    } else {
        $updatedData['name'] = $name;
    }
    
    // Validate email
    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif ($email !== $user['email'] && emailExists($conn, $email, $userId)) {
        $errors['email'] = 'Email already in use';
    } else {
        $updatedData['email'] = $email;
    }
    
    // Handle password change
    if ($newPassword !== '') {
        if ($currentPassword === '') {
            $errors['current_password'] = 'Current password is required to change password';
        } elseif (!password_verify($currentPassword, $user['password_hash'])) {
            $errors['current_password'] = 'Current password is incorrect';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        } elseif ($newPassword !== $confirmNewPassword) {
            $errors['confirm_new_password'] = 'Passwords do not match';
        } else {
            $updatedData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if ($file['size'] > $maxSize) {
            $errors['profile_picture'] = 'File size must be less than 2MB';
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $errors['profile_picture'] = 'Only JPEG and PNG images are allowed';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = __DIR__ . '/../uploads/' . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old profile picture if exists
                if ($user['profile_picture'] && file_exists(__DIR__ . '/../uploads/' . $user['profile_picture'])) {
                    unlink(__DIR__ . '/../uploads/' . $user['profile_picture']);
                }
                $updatedData['profile_picture'] = $newFilename;
            } else {
                $errors['profile_picture'] = 'Failed to upload image';
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = array_merge($updatedData, [
            'name' => $name,
            'email' => $email
        ]);
        header('Location: index.php?page=profile');
        exit;
    }
    
    // Update profile
    $name = $updatedData['name'] ?? null;
    $email = $updatedData['email'] ?? null;
    $profilePicture = $updatedData['profile_picture'] ?? null;
    $passwordHash = $updatedData['password_hash'] ?? null;
    
    if (updateUserProfile($conn, $userId, $name, $email, $profilePicture, $passwordHash)) {
        // Update session if email changed
        if (isset($updatedData['email'])) {
            $_SESSION['user_email'] = $updatedData['email'];
        }
        
        $_SESSION['success'] = 'Profile updated successfully!';
        header('Location: index.php?page=profile');
        exit;
    } else {
        $errors['general'] = 'Profile update failed. Please try again.';
        $_SESSION['errors'] = $errors;
        header('Location: index.php?page=profile');
        exit;
    }
}
?>
