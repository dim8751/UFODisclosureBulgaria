<?php
session_start(); // Start session for lightweight verification check
require_once '../inc/db_connect.php';

$error_msg = '';
$error_msg2 = '';
$success_msg = '';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; // Default to English if not set

// Language translations
$translations = [
    'en' => [
        'title' => 'Email Verification - UFO Disclosure Bulgaria',
        'success_verify' => 'Email verified successfully! You can now login.',
        'click_to_login' => 'Click here to login',
        'err_already_verified' => 'This email has already been verified.',
        'err_expired_deleted' => 'This verification link has expired. Your account has been deleted. Please register again.',
        'err_expired_error' => 'This verification link has expired, but we encountered an error removing your account. Please contact support.',
        'err_verification_failed' => 'Verification failed. Please try again or contact support.',
        'err_invalid_request' => 'Invalid verification request. Please use the link from your email.',
        'return_to_register' => 'Return to register page',
        'return_to_dashboard' => 'Return to dashboard'
    ],
    'bg' => [
        'title' => 'Потвърждение на Имейл - НЛО Разкритие България',
        'success_verify' => 'Имейлът е успешно потвърден! Вече можете да влезете.',
        'click_to_login' => 'Кликнете тук, за да влезете',
        'err_already_verified' => 'Този имейл вече е потвърден.',
        'err_expired_deleted' => 'Този линк за потвърждение е изтекъл. Вашият акаунт е изтрит. Моля, регистрирайте се отново.',
        'err_expired_error' => 'Този линк за потвърждение е изтекъл, но възникна грешка при премахването на акаунта ви. Моля, свържете се с поддръжката.',
        'err_verification_failed' => 'Потвърждението не успя. Моля, опитайте отново или се свържете с поддръжката.',
        'err_invalid_request' => 'Невалидна заявка за потвърждение. Моля, използвайте линка от вашия имейл.',
        'return_to_register' => 'Върнете се към страницата за регистрация',
        'return_to_dashboard' => 'Върнете се към началния панел'
    ]
];

$t = $translations[$lang] ?? $translations['en']; // Fallback to English if lang is invalid

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Lightweight session check: Ensure this request follows registration
    if (!isset($_SESSION['verification_pending'])) {
        // If no session flag exists, this might be an unsolicited request
        $error_msg = $t['err_invalid_request'];
    } else {
        // Find user with this token
        $query = 'SELECT userID, verification_token, token_expiry, email_verified 
                  FROM users 
                  WHERE verification_token = :token';
                  
        $statement = $db->prepare($query);
        $statement->bindValue(':token', $token);
        $statement->execute();
        $user = $statement->fetch();
        
        if ($user) {
            if ($user['email_verified']) {
                $error_msg = $t['err_already_verified'];
            } elseif (strtotime($user['token_expiry']) < time()) {
                // Delete the account if verification link has expired
                $deleteQuery = 'DELETE FROM users WHERE userID = :userID';
                $deleteStmt = $db->prepare($deleteQuery);
                $deleteStmt->bindValue(':userID', $user['userID']);
                
                if ($deleteStmt->execute()) {
                    $error_msg = $t['err_expired_deleted'];
                } else {
                    $error_msg = $t['err_expired_error'];
                }
            } else {
                // Update user status
                $updateQuery = 'UPDATE users 
                              SET email_verified = 1, 
                                  account_status = "active",
                                  verification_token = NULL 
                              WHERE userID = :userID';
                              
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindValue(':userID', $user['userID']);
                
                if ($updateStmt->execute()) {
                    $success_msg = $t['success_verify'];
                    unset($_SESSION['verification_pending']); // Clear session flag after success
                } else {
                    $error_msg = $t['err_verification_failed'];
                }
            }
        } else {
            $error_msg2 = $t['err_already_verified'];
        }
    }
} else {
    $error_msg = $t['err_invalid_request']; // No token provided
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang === 'bg' ? 'bg' : 'en'; ?>">
<head>
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="../login_register/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
</head>
<body>
    <?php if ($success_msg): ?>
        <div id="success-verification">
            <?php echo htmlspecialchars($success_msg); ?>
            <br><br>
            <a class="link" href="../login_register/login.php?lang=<?php echo $lang; ?>"><?php echo $t['click_to_login']; ?></a>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div id="error-verification">
            <?php echo htmlspecialchars($error_msg); ?>
            <br><br>
            <a class="link" href="../login_register/register.php?lang=<?php echo $lang; ?>"><?php echo $t['return_to_register']; ?></a>
        </div>
    <?php endif; ?>
    
    <?php if ($error_msg2): ?>
        <div id="error-verification">
            <?php echo htmlspecialchars($error_msg2); ?>
            <br><br>
            <a class="link" href="../dashboard/index.php?lang=<?php echo $lang; ?>"><?php echo $t['return_to_dashboard']; ?></a>
        </div>
    <?php endif; ?>
</body>
</html>