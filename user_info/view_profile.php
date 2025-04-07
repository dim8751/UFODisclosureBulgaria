<?php
require_once('../inc/db_connect.php');

$status = session_status();
if ($status === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : ($_SESSION['lang'] ?? 'en');
$_SESSION['lang'] = $lang; // Store language in session

// Language translations
$translations = [
    'en' => [
        'title' => 'View Profile - UFO Disclosure Bulgaria',
        'personal_info' => 'PERSONAL INFORMATION',
        'choose_picture' => 'Choose Profile Picture',
        'save' => 'Save',
        'dashboard' => 'Dashboard',
        'change_password' => 'Change Password'
    ],
    'bg' => [
        'title' => 'Преглед на Профила - НЛО Разкритие България',
        'personal_info' => 'ЛИЧНА ИНФОРМАЦИЯ',
        'choose_picture' => 'Изберете Профилна Снимка',
        'save' => 'Запази',
        'dashboard' => 'Начален Панел',
        'change_password' => 'Смяна на Парола'
    ]
];

// Use selected language
$t = $translations[$lang] ?? $translations['en'];

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_msg = $_SESSION['error_msg'] ?? '';
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['error_msg'], $_SESSION['success_msg']);

$user = $_SESSION['user'];
$isLoggedIn = true;

$profilePhotoPath = '../images/default_profile.jpg';
if ($isLoggedIn) {
    try {
        $query = 'SELECT userProfilePhoto FROM users WHERE userID = :userID';
        $statement = $db->prepare($query);
        $statement->bindValue(':userID', $user['userID'], PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['userProfilePhoto']) && $result['userProfilePhoto'] !== '../images/default_profile.jpg') {
            $profilePhotoPath = $result['userProfilePhoto'];
        }
    } catch (PDOException $e) {
        $error_msg = "Error loading profile data.";
    } finally {
        $statement->closeCursor();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <form action="update_profile.php?lang=<?php echo $lang; ?>" method="post" enctype="multipart/form-data" class="personal-info-form">
        <div class="input-container">
            <button type="button" onclick="window.location.href='../dashboard/index.php?lang=<?php echo $lang; ?>'" class="back-btn"><?php echo $t['dashboard']; ?></button>
            
            <div class="language-switch">
                <a href="?lang=en">EN</a>
                <a> / </a>
                <a href="?lang=bg">BG</a>
            </div>
            
            <h1><?php echo $t['personal_info']; ?></h1>
            
            <?php if (!empty($error_msg)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

			<?php if (!empty($success_msg)): ?>
                <div id="success"><?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            
            <?php if ($isLoggedIn): ?>
                <div class="profile-picture-container">
                    <img id="imagePreview" 
                         src="<?php echo htmlspecialchars($profilePhotoPath); ?>" 
                         alt="Profile Picture" 
                         class="profile-picture">
                    <label for="profilePhoto" class="choose-picture-text"><?php echo $t['choose_picture']; ?></label>
                    <input type="file" name="profilePhoto" id="profilePhoto" accept="image/jpeg,image/png,image/gif">
                </div>
                <br>
            <?php endif; ?>
            
            <input type="text" 
                   name="firstName" 
                   maxlength="25"
				   placeholder="First Name:" 
                   value="<?php echo htmlspecialchars($user['userFirstName']); ?>">
            <br>
            <input type="text" 
                   name="lastName" 
                   maxlength="25"
				   placeholder="Last Name:"
                   value="<?php echo htmlspecialchars($user['userLastName']); ?>">
            <br>
            <input type="email" 
                   name="emailAddress" 
                   value="<?php echo htmlspecialchars($user['userEmailAddress']); ?>" 
                   readonly 
                   style="cursor: not-allowed !important; background-color: #f0f0f0 !important;">
            <br>
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['userID']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <br>
            <button type="submit" name="update_btn" class="update-btn"><?php echo $t['save']; ?></button>
            <br>
            <hr class="divider">
            <a class="link" href="change_password.php?lang=<?php echo $lang; ?>"><?php echo $t['change_password']; ?></a>
        </div>
    </form>

    <script>
    const currentImage = '<?php echo htmlspecialchars($profilePhotoPath); ?>';
    
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                event.target.value = '';
                preview.src = currentImage;
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = currentImage;
        }
    }
    
    document.getElementById('profilePhoto')?.addEventListener('change', previewImage);
    </script>
</body>
</html>