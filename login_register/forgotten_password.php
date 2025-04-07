<?php
require_once '../inc/db_connect.php';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/EmailService.php';
use Classes\EmailService;

session_start(); 

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

// Initialize variables
$error_msg = '';
$success_msg = '';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

// Language translations
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'title' => 'Password Recovery - UFO Disclosure Bulgaria',
        'password_recovery' => 'PASSWORD RECOVERY',
        'request_reset_msg' => 'Enter your email address to receive a password reset link.',
        'send_link' => 'SEND LINK',
        'requirements' => 'Password must contain:',
        'req_length' => 'At least 8 characters',
        'req_number' => 'At least one number (0-9)',
        'req_upper' => 'At least one capital letter (A-Z)',
        'req_special' => 'At least one special character (!@#$%)',
        'back_to_login' => 'Back to Login',
        'set_new_password' => 'SET NEW PASSWORD',
        'new_password' => 'New Password:',
        'confirm_new_password' => 'Confirm New Password:',
        'confirm' => 'CONFIRM',
        'login_to_account' => 'Log in to your account',
        'err_empty_email' => 'Please enter your email address',
        'err_invalid_email' => 'Please enter a valid email address',
        'err_no_account' => 'No account exists with this email address',
        'err_request_failed' => 'There was a problem processing your request',
        'err_email_failed' => 'There was a problem sending the email',
        'err_invalid_token' => 'Invalid or expired reset token',
        'err_empty_password' => 'Please enter the new password',
        'err_weak_password' => 'Password is too weak',
        'err_password_mismatch' => 'Passwords do not match',
        'err_reset_failed' => 'There was a problem updating your password',
        'err_csrf_invalid' => 'Invalid CSRF token, please try again',
        'success_reset_request' => 'We have sent password recovery instructions to your email address',
        'success_reset' => 'Your password has been successfully reset'
    ],
    'bg' => [
        'dashboard' => 'Начален Панел',
        'title' => 'Възстановяване на Парола - НЛО Разкритие България',
        'password_recovery' => 'ВЪЗСТАНОВЯВАНЕ НА ПАРОЛА',
        'request_reset_msg' => 'Въведете вашия имейл адрес, за да получите линк за възстановяване на паролата.',
        'send_link' => 'ИЗПРАТИ ЛИНК',
        'requirements' => 'Паролата трябва да съдържа:',
        'req_length' => 'Най-малко 8 символа',
        'req_number' => 'Поне една цифра (0-9)',
        'req_upper' => 'Поне една главна буква (A-Z)',
        'req_special' => 'Поне един специален символ (!@#$%)',
        'back_to_login' => 'Връщане към Входа',
        'set_new_password' => 'ЗАДАЙТЕ НОВА ПАРОЛА',
        'new_password' => 'Нова Парола:',
        'confirm_new_password' => 'Потвърдете Новата Парола:',
        'confirm' => 'ПОТВЪРДИ',
        'login_to_account' => 'Вход в профила',
        'err_empty_email' => 'Моля, въведете вашия имейл адрес',
        'err_invalid_email' => 'Моля, въведете валиден имейл адрес',
        'err_no_account' => 'Не съществува акаунт с този имейл адрес',
        'err_request_failed' => 'Възникна проблем при обработката на вашата заявка',
        'err_email_failed' => 'Възникна проблем при изпращането на имейла',
        'err_invalid_token' => 'Невалиден или изтекъл код за възстановяване',
        'err_empty_password' => 'Моля, въведете новата парола',
        'err_weak_password' => 'Паролата е твърде слаба',
        'err_password_mismatch' => 'Паролите не съвпадат',
        'err_reset_failed' => 'Възникна проблем при обновяването на вашата парола',
        'err_csrf_invalid' => 'Невалиден CSRF токен, моля опитайте отново',
        'success_reset_request' => 'Изпратихме инструкции за възстановяване на паролата на вашия имейл адрес',
        'success_reset' => 'Вашата парола беше успешно променена'
    ]
];

$t = $translations[$lang] ?? $translations['en'];

// Handle step 1: Request password reset
if (isset($_POST['request_reset'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = $t['err_csrf_invalid'];
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (empty($email)) {
            $error_msg = $t['err_empty_email'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = $t['err_invalid_email'];
        } else {
            // Check if email exists in the database
            $stmt = $db->prepare("SELECT userID FROM USERS WHERE userEmailAddress = :email");
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($result) === 0) {
                $error_msg = $t['err_no_account'];
            } else {
                // Generate a token and set expiration time (24 hours from now)
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Update database with token
                $updateStmt = $db->prepare("UPDATE USERS SET password_reset_token = :token, password_reset_expires = :expiry WHERE userEmailAddress = :email");
                $updateStmt->bindParam(":token", $token, PDO::PARAM_STR);
                $updateStmt->bindParam(":expiry", $expiry, PDO::PARAM_STR);
                $updateStmt->bindParam(":email", $email, PDO::PARAM_STR);
                $updateStmt->execute();
                
                if ($updateStmt->rowCount() > 0) {
                    // Send reset email
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendPasswordResetEmail($email, $token, $lang);
                    
                    if ($emailSent) {
                        $success_msg = $t['success_reset_request'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token after success
                    } else {
                        $error_msg = $t['err_email_failed'];
                    }
                } else {
                    $error_msg = $t['err_request_failed'];
                }
            }
        }
    }
}

// Handle step 2: Reset password with token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token exists and is not expired
    $stmt = $db->prepare("SELECT userID FROM USERS WHERE password_reset_token = :token AND password_reset_expires > NOW()");
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($result) === 0) {
        $error_msg = $t['err_invalid_token'];
    }
    
    // Process password reset form
    if (isset($_POST['reset_password'])) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error_msg = $t['err_csrf_invalid'];
        } else {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
         
            // Validate password
            if (empty($password) || empty($confirm_password)) {
                $error_msg = $t['err_empty_password'];
            } elseif ($password !== $confirm_password) {
                $error_msg = $t['err_password_mismatch'];
            } elseif ((strlen($password) < 8) || 
                      (!preg_match('/[A-Z]/', $password)) || 
                      (!preg_match('/[0-9]/', $password)) ||
                      (!preg_match('/[!@#$%&*]/', $password))) {
                $error_msg = $t['err_weak_password'];
            }
            
            if (empty($error_msg)) {
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update user's password and clear reset token
                $updateStmt = $db->prepare("UPDATE USERS SET userPassword = :password, password_reset_token = NULL, password_reset_expires = NULL WHERE password_reset_token = :token");
                $updateStmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                $updateStmt->bindParam(":token", $token, PDO::PARAM_STR);
                $updateStmt->execute();
                
                if ($updateStmt->rowCount() > 0) {
                    $success_msg = $t['success_reset'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error_msg = $t['err_reset_failed'];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang === 'bg' ? 'bg' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" href="../login_register/main.css">
</head>
<body>
    <div class="reset-pass-container">
        <div class="input-container">
            <button type="button" onclick="window.location.href='../dashboard/index.php'" class="back-btn"><?php echo $t['dashboard']; ?></button>
            <div class="language-switch">
                <a href="?lang=en<?php echo isset($_GET['token']) ? '&token=' . $_GET['token'] : ''; ?>">EN</a>
                <a> / </a>
                <a href="?lang=bg<?php echo isset($_GET['token']) ? '&token=' . $_GET['token'] : ''; ?>">BG</a>
            </div>
            
            <?php if (isset($_GET['token'])): ?>
                <!-- Step 2: Reset Password Form -->
                <?php if (empty($success_msg)): ?>
                    <h1><?php echo $t['set_new_password']; ?></h1>
                    <?php if (!empty($error_msg)) : ?>
                        <div id="error-pass-reset"><?php echo htmlspecialchars($error_msg); ?></div>
                    <?php endif; ?>
                    <form method="post" action="forgotten_password.php?token=<?php echo $_GET['token']; ?>&lang=<?php echo $lang; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="New Password:">
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password:">
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
                        
                        <div class="form-group">
                            <button type="submit" name="reset_password" class="reset-password"><?php echo $t['confirm']; ?></button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-center">
                        <br><br><br>
                        <?php if (!empty($success_msg)) : ?>
                            <div id="success-pass-reset"><?php echo htmlspecialchars($success_msg); ?></div>
                        <?php endif; ?>
                        <a href="../login_register/login.php?lang=<?php echo $lang; ?>" class="link"><?php echo $t['login_to_account']; ?></a>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <h1><?php echo $t['password_recovery']; ?></h1>
                <?php if (!empty($error_msg)) : ?>
                    <div id="error-pass-reset"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>
                <?php if (!empty($success_msg)) : ?>
                    <div id="success-pass-reset"><?php echo htmlspecialchars($success_msg); ?></div>
                <?php endif; ?>
                <?php if (empty($success_msg)): ?>
                    <h2 style="text-align: center; margin-bottom: 20px;"><?php echo $t['request_reset_msg']; ?></h2>
                    <form method="post" action="forgotten_password.php?lang=<?php echo $lang; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group">                                                      
                            <input type="username" id="email" name="email" placeholder="Email Address:">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="request_reset" class="request-reset"><?php echo $t['send_link']; ?></button>
                        </div>
                    </form>
                <?php endif; ?>
                <br><hr class="divider">
                <a href="../login_register/login.php?lang=<?php echo $lang; ?>" class="link"><?php echo $t['back_to_login']; ?></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>