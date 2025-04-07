<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
require_once '../inc/db_connect.php';
require_once __DIR__ . '/../classes/EmailService.php';

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Secure random token
}

$username = $password = $password2 = $firstName = $lastName = '';
$error_msg = '';
$success_msg = '';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; // Default to English

// Language translations
$translations = [
    'en' => [
        'title' => 'Register Page - UFO Disclosure Bulgaria',
        'registration' => 'REGISTRATION',
        'dashboard' => 'Dashboard',
        'choose_picture' => 'Choose Profile Picture',
        'register_btn' => 'REGISTER',
        'refresh_captcha' => 'Refresh',
        'captcha_placeholder' => 'Enter Code:',
        'requirements' => 'Password must contain:', 
        'req_length' => 'At least 8 characters',    
        'req_number' => 'At least one number (0-9)', 
        'req_upper' => 'At least one capital letter (A-Z)', 
        'req_special' => 'At least one special character (!@#$%)', 
        'err_empty' => 'Please provide more information',
        'err_weak' => 'Password is too weak',
        'err_mismatch' => 'Passwords do not match',
        'err_captcha_empty' => 'Please enter the CAPTCHA code',
        'err_captcha_invalid' => 'Invalid CAPTCHA code, please try again',
        'err_email_exists' => 'This email address already exists',
        'err_upload_failed' => 'Failed to upload image, please try again',
        'err_invalid_file' => 'Please upload a valid image file (JPG, PNG, or GIF)',
        'err_registration_failed' => 'Registration failed. Please try again later.',
        'err_csrf_invalid' => 'Invalid CSRF token, please try again',
        'success_verify' => 'Please check your email to verify your account'
    ],
    'bg' => [
        'title' => 'Регистрация - НЛО Разкритие България',
        'registration' => 'РЕГИСТРАЦИЯ',
        'dashboard' => 'Начален Панел',
        'choose_picture' => 'Изберете Профилна Снимка',
        'register_btn' => 'РЕГИСТРАЦИЯ',
        'refresh_captcha' => 'Обнови',
        'captcha_placeholder' => 'Въведете Кода:',
        'requirements' => 'Паролата трябва да съдържа:', 
        'req_length' => 'Най-малко 8 символа',         
        'req_number' => 'Поне една цифра (0-9)',      
        'req_upper' => 'Поне една главна буква (A-Z)',
        'req_special' => 'Поне един специален символ (!@#$%)', 
        'err_empty' => 'Моля, предоставете повече информация',
        'err_weak' => 'Паролата е твърде слаба',
        'err_mismatch' => 'Паролите не съвпадат',
        'err_captcha_empty' => 'Моля, въведете CAPTCHA кода',
        'err_captcha_invalid' => 'Невалиден CAPTCHA код, опитайте отново',
        'err_email_exists' => 'Този имейл адрес вече съществува',
        'err_upload_failed' => 'Неуспешно качване на снимка, моля опитайте отново',
        'err_invalid_file' => 'Моля, качете валиден файл с изображение (JPG, PNG или GIF)',
        'err_registration_failed' => 'Регистрацията бе неуспешна. Моля, опитайте отново по-късно.',
        'err_csrf_invalid' => 'Невалиден CSRF токен, моля опитайте отново',
        'success_verify' => 'Моля, проверете вашия имейл, за да потвърдите акаунта си'
    ]
];

$t = $translations[$lang] ?? $translations['en'];

if (isset($_POST['Register'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = $t['err_csrf_invalid'];
    } else {
        // Validate and sanitize user input
        $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
        $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');
        $password2 = filter_input(INPUT_POST, 'password2');
        $type = "client";
        
        // Check if passwords match and meet requirements
        if ($password != $password2) {
            $error_msg = $t['err_mismatch'];
        }
        
        if (empty($username) || empty($password) || empty($password2) || empty($firstName) || empty($lastName)) {
            $error_msg = $t['err_empty'];
        }
        
        elseif ((strlen($password) < 8) || 
                (!preg_match('/[A-Z]/', $password)) || 
                (!preg_match('/[0-9]/', $password)) ||
                (!preg_match('/[!@#$%&*]/', $password))) {
            $error_msg = $t['err_weak'];
        }
        
        // Verify CAPTCHA
        if (empty($_POST['captcha'])) {
            $error_msg = $t['err_captcha_empty'];
        } elseif (!isset($_SESSION['captcha']) || 
                  strtoupper($_POST['captcha']) !== $_SESSION['captcha']) {
            $error_msg = $t['err_captcha_invalid'];
        }
        
        // Check if email already exists
        if (empty($error_msg)) {
            $checkEmail = $db->prepare('SELECT userID FROM users WHERE userEmailAddress = :email');
            $checkEmail->bindValue(':email', $username);
            $checkEmail->execute();
            if ($checkEmail->fetch()) {
                $error_msg = $t['err_email_exists'];
            }
        }

        // If there are no validation errors, proceed with registration
        if (empty($error_msg)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Set default profile photo path
            $profilePhotoPath = '../images/default_profile.jpg';

            // Handle file upload if a file was selected
            if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../images/user_photos/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate unique filename
                $fileExtension = strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));
                $uniqueFilename = uniqid('profile_', true) . '.' . $fileExtension;
                $uploadFile = $uploadDir . $uniqueFilename;

                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = $_FILES['profilePhoto']['type'];

                if (in_array($fileType, $allowedTypes)) {
                    if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $uploadFile)) {
                        $profilePhotoPath = '../images/user_photos/' . $uniqueFilename; // Store relative path
                    } else {
                        $error_msg = $t['err_upload_failed'];
                    }
                } else {
                    $error_msg = $t['err_invalid_file'];
                }
            }

            if (empty($error_msg)) {
                try {
                    $token = bin2hex(random_bytes(32));  // Generate token
                    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $language = $_POST['language'] ?? 'en';
                    
                    // Begin transaction
                    $db->beginTransaction();
                    
                    // Insert user into database
                    $query = 'INSERT INTO users (userEmailAddress, userPassword, userFirstName, userLastName, 
                                               userType, userProfilePhoto, verification_token, token_expiry, account_status) 
                            VALUES (:username, :password, :firstName, :lastName, :type, :profilePhoto, 
                                    :token, :tokenExpiry, "pending")';
                    
                    $statement = $db->prepare($query);
                    $statement->bindValue(':username', $username);
                    $statement->bindValue(':password', $hashedPassword);
                    $statement->bindValue(':firstName', $firstName);
                    $statement->bindValue(':lastName', $lastName);
                    $statement->bindValue(':type', $type);
                    $statement->bindValue(':profilePhoto', $profilePhotoPath);
                    $statement->bindValue(':token', $token);
                    $statement->bindValue(':tokenExpiry', $tokenExpiry);

                    $statement->execute();
                    
                    // Create instance of EmailService
                    $emailService = new \Classes\EmailService();

                    // Send verification email
                    if ($emailService->sendVerificationEmail($username, $token, $language)) {
                        $db->commit();
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['verification_pending'] = true; // 
                        $_SESSION['success_msg'] = $t['success_verify'];
                        header("Location: login.php?lang=$lang");
                        exit();
                    } else {
                        throw new Exception('Failed to send verification email');
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log($e->getMessage());
                    $error_msg = $t['err_registration_failed'];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang === 'bg' ? 'bg' : 'en'; ?>">
<head>
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="../login_register/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <script>
        let currentImage = '../images/default_profile.jpg';

        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    currentImage = e.target.result; 
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = currentImage;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('profilePhoto').addEventListener('change', previewImage);

            document.getElementById('language').addEventListener('change', function() {
                document.getElementById('hidden-language').value = this.value;
            });
        });
    </script>
</head>
<body>
    <form class="register-form" action="register.php?lang=<?php echo $lang; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="language" id="hidden-language" value="<?php echo $lang; ?>">
        <div class="form-columns">
            <div class="input-container">
                <button type="button" onclick="window.location.href='../dashboard/index.php?lang=<?php echo $lang; ?>'" class="back-btn"><?php echo $t['dashboard']; ?></button>
                <div class="language-switch">
                    <a href="?lang=en">EN</a>
                    <a> / </a>
                    <a href="?lang=bg">BG</a>
                </div>
                <h1><?php echo $t['registration']; ?></h1>
                <?php if (!empty($error_msg)) : ?>
                    <div id="error-login-register"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>
                <div class="profile-picture-container">
                    <img id="imagePreview" src="../images/default_profile.jpg" alt="Default Profile" class="profile-picture">
                    <label for="profilePhoto" class="choose-picture-text"><?php echo $t['choose_picture']; ?></label>
                    <input type="file" name="profilePhoto" id="profilePhoto" accept="image/*">
                </div>
                <div class="form-columns">
                    <!-- Left Column -->
                    <div class="form-column left">
                        <div class="input-container">
                            <input type="text" name="firstName" maxlength="25" placeholder="First Name:">
                            <input type="text" name="lastName" maxlength="25" placeholder="Last Name:">
                            <input type="username" name="username" placeholder="Email Address:">
                        </div>
                    </div>
                    <!-- Right Column -->
                    <div class="form-column right">
                        <div class="input-container">
                            <input type="password" name="password" placeholder="Password:">
                            <input type="password" name="password2" placeholder="Confirm Password:">
                        </div>
                    </div>
                </div>
                <div class="password-requirements">
                    <?php echo $t['requirements']; ?>
                    <ul>
                        <li><?php echo $t['req_length']; ?></li>
                        <li><?php echo $t['req_number']; ?></li>
                        <li><?php echo $t['req_upper']; ?></li>
                        <li><?php echo $t['req_special']; ?></li>
                    </ul>
                </div>
                <div class="captcha-container">
                    <img src="captcha.php" alt="CAPTCHA" id="captcha-image">
                    <button type="button" onclick="document.getElementById('captcha-image').src='captcha.php?'+Math.random()"><?php echo $t['refresh_captcha']; ?></button>
                </div>
                <input type="text" class="captcha-input" name="captcha" maxlength="6" placeholder="<?php echo $t['captcha_placeholder']; ?>">
                <label>&nbsp;</label>
                <button type="submit" name="Register" id="register-btn" class="register-btn"><?php echo $t['register_btn']; ?></button>
            </div>
        </div>
    </form>
</body>
</html>