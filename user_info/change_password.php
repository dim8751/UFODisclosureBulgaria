<?php
session_start();
require_once '../inc/db_connect.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : ($_SESSION['lang'] ?? 'en');
$_SESSION['lang'] = $lang;

// Language translations
$translations = [
    'en' => [
        'title' => 'Change Password - UFO Disclosure Bulgaria',
        'change_password_title' => 'CHANGE PASSWORD',
        'dashboard' => 'Dashboard',
        'confirm' => 'CONFIRM',
        'requirements' => 'Password must contain:',
        'req_length' => 'At least 8 characters',
        'req_number' => 'At least one number (0-9)',
        'req_upper' => 'At least one capital letter (A-Z)',
        'req_special' => 'At least one special character (!@#$%)',
        'err_weak' => 'Password is too weak',
        'err_empty' => 'Please enter both passwords',
        'err_mismatch' => 'Passwords do not match',
        'err_current' => 'Please enter your current password',
        'err_incorrect' => 'Current password is incorrect',
        'err_same' => 'New password must be different from the current one',
        'err_update' => "Couldn't update the password, please try again later",
        'err_db' => 'Error updating password',
        'err_csrf' => 'Invalid security token',
        'success' => 'Please log back in'
    ],
    'bg' => [
        'title' => 'Смяна на Парола - НЛО Разкритие България',
        'change_password_title'=> 'СМЯНА НА ПАРОЛА',
        'dashboard' => 'Начален Панел',
        'confirm' => 'ПОТВЪРДИ',
        'requirements' => 'Паролата трябва да съдържа:',
        'req_length' => 'Най-малко 8 символа',
        'req_number' => 'Поне една цифра (0-9)',
        'req_upper' => 'Поне една главна буква (A-Z)',
        'req_special' => 'Поне един специален символ (!@#$%)',
        'err_weak' => 'Паролата е твърде слаба',
        'err_empty' => 'Моля, въведете и двете пароли',
        'err_mismatch' => 'Паролите не съвпадат',
        'err_current' => 'Моля, въведете текущата си парола',
        'err_incorrect' => 'Текущата парола е неправилна',
        'err_same' => 'Новата парола трябва да бъде различна от текущата',
        'err_update' => 'Не можа да се актуализира паролата, опитайте отново по-късно',
        'err_db' => 'Грешка при актуализиране на паролата',
        'err_csrf' => 'Невалиден защитен токен',
        'success' => 'Моля, влезте отново'
    ]
];

$t = $translations[$lang] ?? $translations['en'];

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login_register/login.php?lang=$lang"); // Redirect to login instead of dashboard
    exit();
}

$isLoggedIn = isset($_SESSION['user']);
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token');
    if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
        $error_msg = $t['err_csrf'];
    } else {
        $currentPassword = filter_input(INPUT_POST, 'current_password');
        $password = filter_input(INPUT_POST, 'password');
        $password2 = filter_input(INPUT_POST, 'password2');
        
        // Check if fields are empty first
        if (empty($password) || empty($password2)) {
            $error_msg = $t['err_empty'];
        } elseif (empty($currentPassword)) {
            $error_msg = $t['err_current'];
        } elseif ($password !== $password2) {
            $error_msg = $t['err_mismatch'];
        } else {
            // Verify current password before checking new password strength
            try {
                $user_id = $_SESSION['user']['userID'];
                $query = 'SELECT userPassword FROM users WHERE userID = :user_id';
                $statement = $db->prepare($query);
                $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $statement->execute();
                $storedPassword = $statement->fetchColumn();
                $statement->closeCursor();

                if (!password_verify($currentPassword, $storedPassword)) {
                    $error_msg = $t['err_incorrect'];
                } elseif ($currentPassword === $password) {
                    $error_msg = $t['err_same'];
                } elseif ((strlen($password) < 8) || 
                         (!preg_match('/[A-Z]/', $password)) || 
                         (!preg_match('/[0-9]/', $password)) || 
                         (!preg_match('/[!@#$%&*]/', $password))) {
                    $error_msg = $t['err_weak'];
                } else {
                    // Proceed with password update
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateQuery = 'UPDATE users SET userPassword = :password WHERE userID = :user_id';
                    $statement = $db->prepare($updateQuery);
                    $statement->bindValue(':password', $hashedPassword);
                    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    
                    $success = $statement->execute();
                    $statement->closeCursor();

                    if ($success) {
                        // Regenerate CSRF token and session
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        session_regenerate_id(true);
                        $_SESSION = array(); // Clear all session data
                        session_start(); // Start new session
                        $_SESSION['success_msg'] = $t['success'];
                        $_SESSION['lang'] = $lang; // Preserve language selection
                        
                        header("Location: ../login_register/login.php?lang=$lang");
                        session_write_close();
                        exit();
                    } else {
                        $error_msg = $t['err_update'];
                    }
                }
            } catch (Exception $e) {
                $error_msg = $t['err_db'] . ': ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang === 'bg' ? 'bg' : 'en'; ?>">
<head>
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
            window.location.href = '../login_register/login.php?lang=<?php echo $lang; ?>';
        };
    </script>
</head>
<body>
    <form action="change_password.php?lang=<?php echo $lang; ?>" method="post" class="change-password-form">
        <div class="input-container">
            <button type="button" onclick="window.location.href='../dashboard/index.php?lang=<?php echo $lang; ?>'" class="back-btn"><?php echo $t['dashboard']; ?></button>
            
            <div class="language-switch">
                <a href="?lang=en">EN</a>
                <a> / </a>
                <a href="?lang=bg">BG</a>
            </div>
            
            <h1><?php echo $t['change_password_title']; ?></h1>
            
            <?php if (!empty($error_msg)) : ?>
                <div id="error-message"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_msg)) : ?>
                <div id="success"><?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="password" name="current_password" id="current_password" placeholder="Current Password:"><br>        
            <input type="password" name="password" id="password" placeholder="New Password:"><br>
            <input type="password" name="password2" id="password2" placeholder="Confirm New Password:"><br>
            
            <div class="password-requirements">
                <?php echo $t['requirements']; ?>
                <ul>
                    <li><?php echo $t['req_length']; ?></li>
                    <li><?php echo $t['req_number']; ?></li>
                    <li><?php echo $t['req_upper']; ?></li>
                    <li><?php echo $t['req_special']; ?></li>
                </ul>
            </div>
            
            <button type="submit" name="change_password" class="change-password"><?php echo $t['confirm']; ?></button>
        </div>
    </form>
</body>
</html>