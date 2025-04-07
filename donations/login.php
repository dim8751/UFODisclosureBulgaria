<?php
require_once('../inc/db_connect.php');

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Secure random token
}

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : ($_SESSION['lang'] ?? 'en');
$_SESSION['lang'] = $lang;

// Language translations
$translations = [
    'en' => [
        'title' => 'Login Page - UFO Disclosure Bulgaria',
        'login' => 'LOGIN',
        'login_msg' => 'Log in to donate',
        'dashboard' => 'Dashboard',
        'register' => 'Register',
        'forgot_password' => 'Forgotten Password',
        'refresh_captcha' => 'Refresh',
        'captcha_placeholder' => 'Enter Code:',
        'err_captcha_empty' => 'Please enter the CAPTCHA code',
        'err_captcha_invalid' => 'Invalid CAPTCHA code, please try again',
        'err_email_unverified' => 'Please verify your email address before logging in',
        'err_wrong_password' => 'Wrong password, please try again',
        'err_no_profile' => 'This profile does not exist',
        'err_csrf_invalid' => 'Invalid CSRF token, please try again'
    ],
    'bg' => [
        'title' => 'Вход - НЛО Разкритие България',
        'login' => 'ВХОД',
        'login_msg' => 'Влезте, за да дарите',
        'dashboard' => 'Начален Панел',
        'register' => 'Регистрация',
        'forgot_password' => 'Забравена Парола',
        'refresh_captcha' => 'Обнови',
        'captcha_placeholder' => 'Въведете Кода:',
        'err_captcha_empty' => 'Моля, въведете CAPTCHA кода',
        'err_captcha_invalid' => 'Невалиден CAPTCHA код, опитайте отново',
        'err_email_unverified' => 'Моля, потвърдете имейл адреса си преди да влезете',
        'err_wrong_password' => 'Грешна парола, моля опитайте отново',
        'err_no_profile' => 'Този профил не съществува',
        'err_csrf_invalid' => 'Невалиден CSRF токен, моля опитайте отново'
    ]
];

$t = $translations[$lang] ?? $translations['en'];

$error_msg = '';
$login_msg = '';

// Set initial login message only on first load (no POST request)
if (!isset($_POST['Login']) && empty($error_msg)) {
    $login_msg = $t['login_msg']; // Use translation directly
}

// Check if the user is logged in and redirect if true
if (isset($_SESSION['user'])) {
    header("Location: ../dashboard/index.php?lang=$lang");
    exit();
}

// Initialize failed attempts if not set
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

// Function to check if CAPTCHA is required
function isCaptchaRequired() {
    return isset($_SESSION['failed_attempts']) && $_SESSION['failed_attempts'] >= 3;
}

if (isset($_POST['Login'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = $t['err_csrf_invalid'];
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $proceed_with_login = true;
        
        // Verify CAPTCHA if required
        if (isCaptchaRequired()) {
            if (!isset($_POST['captcha']) || empty($_POST['captcha'])) {
                $error_msg = $t['err_captcha_empty'];
                $proceed_with_login = false;
            } elseif (!isset($_SESSION['captcha']) || 
                     strtoupper($_POST['captcha']) !== $_SESSION['captcha']) {
                $error_msg = $t['err_captcha_invalid'];
                $proceed_with_login = false;
            }
        }
        
        if ($proceed_with_login) {
            $query = 'SELECT * FROM users WHERE userEmailAddress = :username';
            $statement = $db->prepare($query);
            $statement->bindValue(':username', $username);
            $statement->execute();
            $user = $statement->fetch();
            
            if ($user) {
                if (!$user['email_verified']) {
                    $error_msg = $t['err_email_unverified'];
                } elseif (password_verify($password, $user['userPassword'])) {
                    // Successful login - reset failed attempts and regenerate CSRF token
                    $_SESSION['failed_attempts'] = 0;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token after successful login
                    
                    $_SESSION['user'] = array(
                        'userID' => $user['userID'],
                        'userEmailAddress' => $user['userEmailAddress'],
                        'userPassword' => $user['userPassword'],
                        'userFirstName' => $user['userFirstName'],
                        'userLastName' => $user['userLastName'],
                        'userType' => $user['userType'],
                        'userProfilePhoto' => $user['userProfilePhoto']
                    );
                    
                    header("Location: ../dashboard/index.php?lang=$lang");
                    exit();
                } else {
                    $_SESSION['failed_attempts']++;
                    $error_msg = $t['err_wrong_password'];
                }
            } else {
                $_SESSION['failed_attempts']++;
                $error_msg = $t['err_no_profile'];
            }
        }
    }
    // If there's an error, ensure login_msg is cleared
    if (!empty($error_msg)) {
        $login_msg = '';
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
            window.location.href = '../dashboard/index.php?lang=<?php echo $lang; ?>';
        };
    </script>
</head>
<body>
    <form class="login-form" action="login.php?lang=<?php echo $lang; ?>" method="post">
        <div class="input-container">
            <button type="button" onclick="window.location.href='../dashboard/index.php?lang=<?php echo $lang; ?>'" class="back-btn"><?php echo $t['dashboard']; ?></button>
            
            <div class="language-switch">
                <a href="?lang=en">EN</a>
                <a> / </a>
                <a href="?lang=bg">BG</a>
            </div>
            
            <h1><?php echo $t['login']; ?></h1>
            
            <?php if (!empty($error_msg)) : ?>
                <div id="error-login-register"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
            <?php if (!empty($login_msg)) : ?>
                <div id="login-msg"><?php echo htmlspecialchars($login_msg); ?></div>
            <?php endif; ?>
            
            <!-- Add CSRF token as a hidden input -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <input type="username" name="username" placeholder="Email Address:">
            <input type="password" name="password" placeholder="Password:">
            
            <?php if (isCaptchaRequired()): ?>
                <div class="captcha-container">
                    <img src="captcha.php" alt="CAPTCHA" id="captcha-image">
                    <button type="button" onclick="document.getElementById('captcha-image').src='captcha.php?'+Math.random()"><?php echo $t['refresh_captcha']; ?></button>
                </div>
                <input type="text" class="captcha-input" name="captcha" maxlength="6" placeholder="<?php echo $t['captcha_placeholder']; ?>">
            <?php endif; ?>
            
            <label> </label>
            <button type="submit" name="Login" class="login-btn"><?php echo $t['login']; ?></button> 
            <br>
            <hr class="divider">
            <a class="link" href="../login_register/register.php?lang=<?php echo $lang; ?>"><?php echo $t['register']; ?></a>
            <a> / </a>
            <a class="link" href="../login_register/forgotten_password.php?lang=<?php echo $lang; ?>"><?php echo $t['forgot_password']; ?></a>
        </div>
    </form>     
</body>
</html>