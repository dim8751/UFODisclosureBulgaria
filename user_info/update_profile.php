<?php
require_once('../inc/db_connect.php');

$status = session_status();
if ($status === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;

// Language translations for error messages
$error_translations = [
    'en' => [
        'csrf_failed' => 'CSRF validation failed',
        'unauthorized' => 'Unauthorized access attempt',
        'missing_names' => 'Please provide both your first and last name',
        'name_length' => 'Names must be 25 characters or less',
        'name_format' => 'Names can only contain letters, hyphens, and apostrophes',
        'invalid_image' => 'Please upload a valid image file (JPG, PNG, or GIF)',
        'file_size' => 'File size must be less than 2MB',
        'image_error' => 'Invalid image file',
        'upload_failed' => 'Failed to upload image. Please try again.',
        'update_error' => 'Error updating profile.',
        'db_error' => 'Database error occurred.'
    ],
    'bg' => [
        'csrf_failed' => 'Неуспешна валидация на CSRF',
        'unauthorized' => 'Опит за неупълномощен достъп',
        'missing_names' => 'Моля, предоставете и двете си имена',
        'name_length' => 'Имената трябва да са до 25 символа',
        'name_format' => 'Имената могат да съдържат само букви, тирета и апострофи',
        'invalid_image' => 'Моля, качете валиден файл с изображение (JPG, PNG или GIF)',
        'file_size' => 'Размерът на файла трябва да е под 2MB',
        'image_error' => 'Невалиден файл с изображение',
        'upload_failed' => 'Неуспешно качване на изображение. Моля, опитайте отново.',
        'update_error' => 'Грешка при актуализиране на профила.',
        'db_error' => 'Възникна грешка в базата данни.'
    ]
];
$t_error = $error_translations[$lang] ?? $error_translations['en'];

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die($t_error['csrf_failed']);
}

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?lang=$lang");
    exit();
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
$error_msg = '';
$success_msg = '';

// Validate user_id matches session
if ($user_id !== $_SESSION['user']['userID']) {
    $error_msg = $t_error['unauthorized'];
}

// Input validation
if (empty(trim($firstName)) || empty(trim($lastName))) {
    $error_msg = $t_error['missing_names'];
} elseif (strlen($firstName) > 25 || strlen($lastName) > 25) {
    $error_msg = $t_error['name_length'];
} elseif (!preg_match("/^[a-zA-Z-' ]*$/", $firstName) || !preg_match("/^[a-zA-Z-' ]*$/", $lastName)) {
    $error_msg = $t_error['name_format'];
}

// Handle file upload
$profilePhotoPath = null;
$oldPhotoPath = null;
if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../images/user_photos/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExtension = strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid('profile_', true) . '.' . $fileExtension;
    $uploadFile = $uploadDir . $uniqueFilename;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 2 * 1024 * 1024;
    
    $fileType = mime_content_type($_FILES['profilePhoto']['tmp_name']);
    $fileSize = $_FILES['profilePhoto']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        $error_msg = $t_error['invalid_image'];
    } elseif ($fileSize > $maxFileSize) {
        $error_msg = $t_error['file_size'];
    } elseif (!getimagesize($_FILES['profilePhoto']['tmp_name'])) {
        $error_msg = $t_error['image_error'];
    } else {
        if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $uploadFile)) {
            $profilePhotoPath = '../images/user_photos/' . $uniqueFilename;
            chmod($uploadFile, 0644);
            $query = 'SELECT userProfilePhoto FROM users WHERE userID = :user_id';
            $statement = $db->prepare($query);
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            $oldPhotoPath = $user['userProfilePhoto'] ?? null;
        } else {
            $error_msg = $t_error['upload_failed'];
        }
    }
}

if (empty($error_msg) && $profilePhotoPath === null) {
    $query = 'SELECT userProfilePhoto FROM users WHERE userID = :user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    $profilePhotoPath = $user['userProfilePhoto'] ?? '../images/default_profile.jpg';
}

if (empty($error_msg)) {
    try {
        if ($oldPhotoPath && $oldPhotoPath !== '../images/default_profile.jpg') {
            if (!unlink($oldPhotoPath)) {
                error_log("Failed to delete old profile photo: " . $oldPhotoPath);
            }
        }

        $query = 'UPDATE users 
                  SET userProfilePhoto = :profilePhoto,
                      userFirstName = :firstName,
                      userLastName = :lastName
                  WHERE userID = :user_id';
        
        $statement = $db->prepare($query);
        $statement->bindValue(':profilePhoto', $profilePhotoPath, PDO::PARAM_STR);
        $statement->bindValue(':firstName', $firstName, PDO::PARAM_STR);
        $statement->bindValue(':lastName', $lastName, PDO::PARAM_STR);
        $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        
        $success = $statement->execute();
        
        if ($success) {
            $_SESSION['success_msg'] = $lang === 'bg' ? 'Профилът е успешно актуализиран!' : 'Profile updated successfully!';
            $_SESSION['user']['userProfilePhoto'] = $profilePhotoPath;
            $_SESSION['user']['userFirstName'] = $firstName;
            $_SESSION['user']['userLastName'] = $lastName;
            header("Location: view_profile.php?lang=$lang");
            exit();
        } else {
            $error_msg = $t_error['update_error'];
        }
    } catch (PDOException $e) {
        $error_msg = $t_error['db_error'];
    } finally {
        $statement->closeCursor();
    }
}

if (!empty($error_msg)) {
    $_SESSION['error_msg'] = $error_msg;
    header("Location: view_profile.php?lang=$lang");
    exit();
}
?>