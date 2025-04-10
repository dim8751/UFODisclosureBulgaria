<?php
// Start session first
session_start();
require_once '../inc/db_connect.php';

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if language is set via GET parameter, otherwise use session or default to 'en'
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bg'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Save the language choice in the session
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang']; // Use the session language if set
} else {
    $lang = 'en'; // Default language
    $_SESSION['lang'] = $lang; // Set default in session
}

// Language-specific text arrays
$texts = [
    'en' => [
        'title' => 'Forum - UFO Disclosure Bulgaria',
        'forum_title'=> 'FORUM',
        'forum' => 'Forum',
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'settings' => 'SETTINGS',
        'language' => 'LANGUAGE',
        'logout_popup' => 'Are you sure you want to log out?',
        'delete_category_popup' => 'Are you sure you want to delete this category?',
        'delete_topic_popup' => 'Are you sure you want to delete this topic?',
        'delete_comment_popup' => 'Are you sure you want to delete this comment?',
        'yes' => 'Yes',
        'cancel' => 'CANCEL',
        'topic_content' => 'Topic Content:',
        // Home menu
        'dashboard' => 'Dashboard',
        'our_team' => 'Our Team',
        // Content menu
        'latest_videos' => 'Latest Videos',
        'live_stream' => 'Live Stream',
        'merchandise' => 'Merchandise',
        // Community menu
        'forum_menu' => 'Forum',
        'events' => 'Events',
        'report_sighting' => 'Report Sighting',
        'donors' => 'Donors List',
        // Profile menu
        'view_profile' => 'View Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations',
        // Forum-specific
        'category' => 'Category:',
        'description' => 'Description:',
        'topics' => '# Topics',
        'actions' => 'Actions',
        'create_category' => 'CREATE CATEGORY',
        'create_new_topic' => 'CREATE NEW TOPIC',
        'topic' => 'Topic:',
        'author' => 'Author',
        'comments' => '# Comments',
        'date' => 'Date',
        'no_topics' => 'No topics in this category yet.',
        'login_to_create' => 'Please <a href="../login_register/login.php">login</a> to create a new topic.',
        'add_comment' => 'Add Comment',
        'no_comments' => 'No comments yet. Be the first to comment!',
        'login_to_comment' => 'Please <a href="../login_register/login.php">login</a> to join the discussion.',
        'submit' => 'SUBMIT',
        'save_changes' => 'SAVE CHANGES',
        'create' => 'CREATE',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'reply' => 'Reply',
        'pin' => 'Pin',
        'unpin' => 'Unpin',
        'edited' => 'Edited',
        // Error messages
        'error_db' => 'Database error occurred. Please try again later.',
        'error_category_empty' => 'Category cannot be empty',
        'error_description_empty' => 'Description cannot be empty',
        'error_create_failed' => 'Create failed. Please try again.',
        'error_topic_title_empty' => 'Topic title cannot be empty',
        'error_topic_content_empty' => 'Topic content cannot be empty',
        'error_topic_title_length' => 'Topic title cannot exceed 80 characters.',
        'error_comments_empty' => 'Comments cannot be empty',
        'error_submit_failed' => 'Submit failed. Please try again.',
        'error_save_changes_failed' => 'Save changes failed. Please try again.',
        'error_reply_empty' => 'Reply cannot be empty',
        'error_csrf' => 'Invalid CSRF token. Please try again.'
    ],
    'bg' => [
        'title' => 'Форум - НЛО Разкритие България',
        'forum_title' => 'ФОРУМ',
        'forum' => 'Форум',
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'topic_content' => 'Съдържание на Темата:',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'settings' => 'НАСТРОЙКИ',
        'language' => 'ЕЗИК',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        'delete_category_popup' => 'Сигурни ли сте, че искате да изтриете тази категория?',
        'delete_topic_popup' => 'Сигурни ли сте, че искате да изтриете тази тема?',
        'delete_comment_popup' => 'Сигурни ли сте, че искате да изтриете този коментар?',
        'yes' => 'Да',
        'cancel' => 'ОТКАЗ',
        // Home menu
        'dashboard' => 'Начален Панел',
        'our_team' => 'Нашият Екип',
        // Content menu
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Излъчване на Живо',
        'merchandise' => 'Стоки',
        // Community menu
        'forum_menu' => 'Форум',
        'events' => 'Събития',
        'report_sighting' => 'Докладвай Наблюдение',
        'donors' => 'Списък с Дарители',
        // Profile menu
        'view_profile' => 'Преглед на Профила',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения',
        // Forum-specific
        'category' => 'Категория:',
        'description' => 'Описание:',
        'topics' => '# Теми',
        'actions' => 'Действия',
        'create_category' => 'СЪЗДАЙ КАТЕГОРИЯ',
        'create_new_topic' => 'СЪЗДАЙ НОВА ТЕМА',
        'topic' => 'Тема:',
        'author' => 'Автор',
        'comments' => '# Коментари',
        'date' => 'Дата',
        'no_topics' => 'Все още няма теми в тази категория.',
        'login_to_create' => 'Моля, <a href="../login_register/login.php">влезте</a>, за да създадете нова тема.',
        'add_comment' => 'Добави Коментар',
        'no_comments' => 'Все още няма коментари. Бъдете първи!',
        'login_to_comment' => 'Моля, <a href="../login_register/login.php">влезте</a>, за да се включите в дискусията.',
        'submit' => 'ИЗПРАТИ',
        'save_changes' => 'ЗАПАЗИ ПРОМЕНИТЕ',
        'create' => 'СЪЗДАЙ',
        'edit' => 'Редактирай',
        'delete' => 'Изтрий',
        'reply' => 'Отговори',
        'pin' => 'Закачи',
        'unpin' => 'Откачи',
        'edited' => 'Редактиран',
        // Error messages
        'error_db' => 'Възникна грешка в базата данни. Моля, опитайте отново по-късно.',
        'error_category_empty' => 'Категорията не може да бъде празна',
        'error_description_empty' => 'Описанието не може да бъде празно',
        'error_create_failed' => 'Създаването е неуспешно. Моля, опитайте отново.',
        'error_topic_title_empty' => 'Заглавието на темата не може да бъде празно',
        'error_topic_content_empty' => 'Съдържанието на темата не може да бъде празно',
        'error_topic_title_length' => 'Заглавието на темата не може да надвишава 80 символа.',
        'error_comments_empty' => 'Коментарите не могат да бъдат празни',
        'error_submit_failed' => 'Изпращането е неуспешно. Моля, опитайте отново.',
        'error_save_changes_failed' => 'Запазването на промените е неуспешно. Моля, опитайте отново.',
        'error_reply_empty' => 'Отговорът не може да бъде празен',
        'error_csrf' => 'Невалиден CSRF токен. Моля, опитайте отново.'
    ]
];

$error_msg = '';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']['userID']);
$userType = $isLoggedIn ? $_SESSION['user']['userType'] : '';

// Default profile image path
$profilePhotoPath = '../images/default_profile.jpg';

// If user is logged in, get their profile photo
if ($isLoggedIn) {
    require_once '../inc/db_connect.php';
    $userID = $_SESSION['user']['userID'];
    $query = 'SELECT userProfilePhoto FROM users WHERE userID = :userID';
    $statement = $db->prepare($query);
    $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['userProfilePhoto']) && $user['userProfilePhoto'] !== '../images/default_profile.jpg') {
        $profilePhotoPath = $user['userProfilePhoto'];
    }
}

function getBadgeDetails($amount, $lang) {
    $amount = $amount / 100;
    
    if ($amount >= 50) {
        return ['color' => '#FFD700', 'border' => '#B8860B', 'title' => $lang === 'bg' ? 'Златен Дарител' : 'Gold Donor'];
    } elseif ($amount >= 25) {
        return ['color' => '#C0C0C0', 'border' => '#808080', 'title' => $lang === 'bg' ? 'Сребърен Дарител' : 'Silver Donor'];
    } elseif ($amount >= 10) {
        return ['color' => '#CD7F32', 'border' => '#8B4513', 'title' => $lang === 'bg' ? 'Бронзов Дарител' : 'Bronze Donor'];
    } else {
        return null;
    }
}

// Fetch donations grouped by user with SUM of amounts
try {
    $stmt = $db->prepare("
        SELECT 
            u.userID,
            u.userFirstName,
            u.userLastName,
            u.userEmailAddress,
            u.userProfilePhoto,
            SUM(d.donationAmount) as totalDonation,
            MAX(d.donationDate) as lastDonationDate
        FROM 
            DONATIONS d
        JOIN 
            USERS u ON d.userID = u.userID
        WHERE 
            d.donationStatus = 'Completed'
        GROUP BY 
            u.userID
        ORDER BY 
            totalDonation DESC, lastDonationDate DESC
    ");
    $stmt->execute();
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = $texts[$lang]['error_db'];
    error_log("Database Error: " . $e->getMessage());
}

// Function to validate CSRF token
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Handle category creation (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category']) && $isLoggedIn && $userType === 'admin') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_msg = $texts[$lang]['error_csrf'];
    } else {
        $categoryNameEn = trim($_POST['categoryNameEn']);
        $categoryNameBg = trim($_POST['categoryNameBg']);
        $categoryDescriptionEn = trim($_POST['categoryDescriptionEn']);
        $categoryDescriptionBg = trim($_POST['categoryDescriptionBg']);
        $displayOrder = $_POST['displayOrder'] ?? 1;
        $isPinned = isset($_POST['isPinned']) ? 1 : 0;

        if (empty($categoryNameEn) || empty($categoryNameBg)) {
            $error_msg = $texts[$lang]['error_category_empty'];
        } elseif (empty($categoryDescriptionEn) || empty($categoryDescriptionBg)) {
            $error_msg = $texts[$lang]['error_description_empty'];
        } else {
            try {
                $db->beginTransaction();

                $stmt = $db->prepare("INSERT INTO FORUM_CATEGORIES (displayOrder, isPinned) VALUES (?, ?)");
                $stmt->bindParam(1, $displayOrder, PDO::PARAM_INT);
                $stmt->bindParam(2, $isPinned, PDO::PARAM_INT);
                $stmt->execute();
                $categoryID = $db->lastInsertId();

                $stmt = $db->prepare("INSERT INTO FORUM_CATEGORY_TRANSLATIONS (categoryID, languageCode, categoryName, categoryDescription) VALUES (?, 'en', ?, ?)");
                $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
                $stmt->bindParam(2, $categoryNameEn, PDO::PARAM_STR);
                $stmt->bindParam(3, $categoryDescriptionEn, PDO::PARAM_STR);
                $stmt->execute();

                $stmt = $db->prepare("INSERT INTO FORUM_CATEGORY_TRANSLATIONS (categoryID, languageCode, categoryName, categoryDescription) VALUES (?, 'bg', ?, ?)");
                $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
                $stmt->bindParam(2, $categoryNameBg, PDO::PARAM_STR);
                $stmt->bindParam(3, $categoryDescriptionBg, PDO::PARAM_STR);
                $stmt->execute();

                $db->commit();
                header("Location: forum.php?lang=$lang");
                exit();
            } catch (PDOException $e) {
                $db->rollBack();
                $error_msg = $texts[$lang]['error_create_failed'];
                error_log("Category Creation Error: " . $e->getMessage());
            }
        }
    }
}

// Handle category deletion (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_category']) && $isLoggedIn && $userType === 'admin') {
    $categoryID = $_GET['delete_category'];
    $stmt = $db->prepare("DELETE FROM FORUM_COMMENTS WHERE topicID IN (SELECT topicID FROM FORUM_TOPICS WHERE categoryID = ?)");
    $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $db->prepare("DELETE FROM FORUM_TOPICS WHERE categoryID = ?");
    $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $db->prepare("DELETE FROM FORUM_CATEGORIES WHERE categoryID = ?");
    $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: forum.php?lang=$lang");
    exit();
}

// Handle pinning/unpinning categories (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['toggle_pin_category']) && $isLoggedIn && $userType === 'admin') {
    $categoryID = $_GET['toggle_pin_category'];
    $stmt = $db->prepare("UPDATE FORUM_CATEGORIES SET isPinned = NOT isPinned WHERE categoryID = ?");
    $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: forum.php?lang=$lang");
    exit();
}

// Handle topic deletion (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_topic']) && $isLoggedIn && $userType === 'admin') {
    $topicID = $_GET['delete_topic'];
    $stmt = $db->prepare("DELETE FROM FORUM_COMMENTS WHERE topicID = ?");
    $stmt->bindParam(1, $topicID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $db->prepare("DELETE FROM FORUM_TOPICS WHERE topicID = ?");
    $stmt->bindParam(1, $topicID, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: forum.php?category=" . $currentCategory . "&lang=$lang");
    exit();
}

// Handle pinning/unpinning topics (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['toggle_pin_topic']) && $isLoggedIn && $userType === 'admin') {
    $topicID = $_GET['toggle_pin_topic'];
    $stmt = $db->prepare("UPDATE FORUM_TOPICS SET isPinned = NOT isPinned WHERE topicID = ?");
    $stmt->bindParam(1, $topicID, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: forum.php?category=" . $currentCategory . "&lang=$lang");
    exit();
}

// Handle new topic submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_topic']) && $isLoggedIn) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_msg = $texts[$lang]['error_csrf'];
    } else {
        $categoryID = $_POST['categoryID'];
        $topicTitle = trim($_POST['topicTitle']);
        $topicContent = trim($_POST['topicContent']);
        $isPinned = isset($_POST['isPinned']) && $userType === 'admin' ? 1 : 0;
        $currentCategory = (int)$categoryID;

        if (empty($topicTitle)) {
            $error_msg = $texts[$lang]['error_topic_title_empty'];
        } elseif (empty($topicContent)) {
            $error_msg = $texts[$lang]['error_topic_content_empty'];
        } elseif (strlen($topicTitle) > 80) {
            $error_msg = $texts[$lang]['error_topic_title_length'];
        } else {
            $stmt = $db->prepare("INSERT INTO FORUM_TOPICS (categoryID, userID, topicTitle, topicContent, isPinned) VALUES (?, ?, ?, ?, ?)");
            $stmt->bindParam(1, $categoryID, PDO::PARAM_INT);
            $stmt->bindParam(2, $userID, PDO::PARAM_INT);
            $stmt->bindParam(3, $topicTitle, PDO::PARAM_STR);
            $stmt->bindParam(4, $topicContent, PDO::PARAM_STR);
            $stmt->bindParam(5, $isPinned, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: forum.php?category=" . $categoryID . "&lang=$lang");
                exit();
            } else {
                $error_msg = $texts[$lang]['error_create_failed'];
            }
        }
    }
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && $isLoggedIn) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_msg = $texts[$lang]['error_csrf'];
    } else {
        $topicID = $_POST['topicID'];
        $commentContent = trim($_POST['commentContent']);
        $parentCommentID = isset($_POST['parentCommentID']) && !empty($_POST['parentCommentID']) ? $_POST['parentCommentID'] : null;
        
        if (empty($commentContent)) {
            $error_msg = $texts[$lang]['error_comments_empty'];
        } else {
            $stmt = $db->prepare("INSERT INTO FORUM_COMMENTS (topicID, userID, parentCommentID, commentContent) VALUES (?, ?, ?, ?)");
            $stmt->bindParam(1, $topicID, PDO::PARAM_INT);
            $stmt->bindParam(2, $userID, PDO::PARAM_INT);
            $stmt->bindParam(3, $parentCommentID, PDO::PARAM_INT);
            $stmt->bindParam(4, $commentContent, PDO::PARAM_STR);
            if ($stmt->execute()) {
                header("Location: forum.php?topic=" . $topicID . "&lang=$lang");
                exit();
            } else {
                $error_msg = $texts[$lang]['error_submit_failed'];
            }
        }
    }
}

// Handle comment editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment']) && $isLoggedIn) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error_msg = $texts[$lang]['error_csrf'];
    } else {
        $commentID = $_POST['commentID'];
        $topicID = $_POST['topicID'];
        $commentContent = trim($_POST['commentContent']);
        
        if (empty($commentContent)) {
            $error_msg = $texts[$lang]['error_comments_empty'];
        } else {
            $stmt = $db->prepare("SELECT userID FROM FORUM_COMMENTS WHERE commentID = ?");
            $stmt->bindParam(1, $commentID, PDO::PARAM_INT);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($comment && ($comment['userID'] == $userID || $userType === 'admin')) {
                $stmt = $db->prepare("UPDATE FORUM_COMMENTS SET commentContent = ?, isEdited = 1 WHERE commentID = ?");
                $stmt->bindParam(1, $commentContent, PDO::PARAM_STR);
                $stmt->bindParam(2, $commentID, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    header("Location: forum.php?topic=" . $topicID . "&lang=$lang");
                    exit();
                } else {
                    $error_msg = $texts[$lang]['error_save_changes_failed'];
                }
            }
        }
    }
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_comment']) && $isLoggedIn) {
    $commentID = $_GET['delete_comment'];
    $topicID = $_GET['topicID'];
    $stmt = $db->prepare("SELECT userID FROM FORUM_COMMENTS WHERE commentID = ?");
    $stmt->bindParam(1, $commentID, PDO::PARAM_INT);
    $stmt->execute();
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($comment && ($comment['userID'] == $userID || $userType === 'admin')) {
        $stmt = $db->prepare("DELETE FROM FORUM_COMMENTS WHERE parentCommentID = ?");
        $stmt->bindParam(1, $commentID, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $db->prepare("DELETE FROM FORUM_COMMENTS WHERE commentID = ?");
        $stmt->bindParam(1, $commentID, PDO::PARAM_INT);
        $stmt->execute();
        
        header("Location: forum.php?topic=" . $topicID . "&lang=$lang");
        exit();
    }
}

// Get categories
$categories = [];
$stmt = $db->prepare("
    SELECT fc.categoryID, fc.displayOrder, fc.isPinned, fct.categoryName, fct.categoryDescription
    FROM FORUM_CATEGORIES fc
    JOIN FORUM_CATEGORY_TRANSLATIONS fct ON fc.categoryID = fct.categoryID
    WHERE fct.languageCode = ?
    ORDER BY fc.isPinned DESC, fc.displayOrder ASC
");
$stmt->bindParam(1, $lang, PDO::PARAM_STR);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$currentTopic = isset($_GET['topic']) ? (int)$_GET['topic'] : null;

if ($currentCategory && !$currentTopic) {
    $stmt = $db->prepare("
        SELECT fct.categoryName 
        FROM FORUM_CATEGORIES fc
        JOIN FORUM_CATEGORY_TRANSLATIONS fct ON fc.categoryID = fct.categoryID
        WHERE fc.categoryID = ? AND fct.languageCode = ?
    ");
    $stmt->bindParam(1, $currentCategory, PDO::PARAM_INT);
    $stmt->bindParam(2, $lang, PDO::PARAM_STR);
    $stmt->execute();
    $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT t.*, u.userFirstName, u.userLastName, u.userProfilePhoto, u.userType,
            (SELECT SUM(d.donationAmount) FROM DONATIONS d WHERE d.userID = t.userID AND d.donationStatus = 'Completed') as totalDonation,
            (SELECT COUNT(*) FROM FORUM_COMMENTS WHERE topicID = t.topicID) as comment_count
        FROM FORUM_TOPICS t
        JOIN USERS u ON t.userID = u.userID
        WHERE t.categoryID = ?
        ORDER BY t.isPinned DESC, t.created_at DESC
    ");
    $stmt->bindParam(1, $currentCategory, PDO::PARAM_INT);
    $stmt->execute();
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($currentTopic) {
    $stmt = $db->prepare("
        SELECT t.*, fct.categoryName, fc.categoryID, u.userFirstName, u.userLastName, u.userProfilePhoto, u.userType,
            (SELECT SUM(d.donationAmount) FROM DONATIONS d WHERE d.userID = t.userID AND d.donationStatus = 'Completed') as totalDonation
        FROM FORUM_TOPICS t
        JOIN FORUM_CATEGORIES fc ON t.categoryID = fc.categoryID
        JOIN FORUM_CATEGORY_TRANSLATIONS fct ON fc.categoryID = fct.categoryID
        JOIN USERS u ON t.userID = u.userID
        WHERE t.topicID = ? AND fct.languageCode = ?
    ");
    $stmt->bindParam(1, $currentTopic, PDO::PARAM_INT);
    $stmt->bindParam(2, $lang, PDO::PARAM_STR);
    $stmt->execute();
    $topicInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT c.*, u.userFirstName, u.userLastName, u.userProfilePhoto, u.userType,
            (SELECT SUM(d.donationAmount) FROM DONATIONS d WHERE d.userID = c.userID AND d.donationStatus = 'Completed') as totalDonation
        FROM FORUM_COMMENTS c
        JOIN USERS u ON c.userID = u.userID
        WHERE c.topicID = ? AND c.parentCommentID IS NULL
        ORDER BY c.created_at ASC
    ");
    $stmt->bindParam(1, $currentTopic, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $commentReplies = [];
    if (!empty($comments)) {
        $commentIDs = array_column($comments, 'commentID');
        $placeholders = implode(',', array_fill(0, count($commentIDs), '?'));
        $query = "
            SELECT r.*, u.userFirstName, u.userLastName, u.userProfilePhoto, u.userType,
                (SELECT SUM(d.donationAmount) FROM DONATIONS d WHERE d.userID = r.userID AND d.donationStatus = 'Completed') as totalDonation
            FROM FORUM_COMMENTS r
            JOIN USERS u ON r.userID = u.userID
            WHERE r.parentCommentID IN ($placeholders)
            ORDER BY r.created_at ASC
        ";
        $stmt = $db->prepare($query);
        for ($i = 0; $i < count($commentIDs); $i++) {
            $stmt->bindParam($i + 1, $commentIDs[$i], PDO::PARAM_INT);
        }
        $stmt->execute();
        while ($reply = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $commentReplies[$reply['parentCommentID']][] = $reply;
        }
    }

    $stmt = $db->prepare("UPDATE FORUM_TOPICS SET views = views + 1 WHERE topicID = ?");
    $stmt->bindParam(1, $currentTopic, PDO::PARAM_INT);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>    
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 999;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
            width: 350px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            font-size: 18px;
        }

        .popup h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 700;
            font-size: 22px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            -webkit-text-stroke: 1px;
        }

        .popup button {
            padding: 12px 25px;
            margin: 5px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .popup button.confirm {
            background-color: #744769;
            color: white;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .popup button.confirm:hover {
            background-color: #442538;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .popup button.cancel {
            background-color: #d3d3d3;
            color: white;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .popup button.cancel:hover {
            background-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        #error {
            color: #d23100;
            font-style: italic;
            text-align: center;
        }

        .toggle-form-btn {
            background-color: #744769;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            margin: 20px 0;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 16px;
        }

        .toggle-form-btn:hover {
            background-color: #442538;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .edit-form button {
            background-color: #744769;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .edit-form {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(90, 54, 81, 0.2);
        }

        .hidden-form {
            display: none;
            margin-top: 20px;
        }

        .hidden-form textarea {
            width: 70%;
            height: 200px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        #show-comment-form {
            display: block;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 600px;
            height: auto;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            text-align: center;
        }

        #category-popup {
            height: 800px;
            overflow: auto;
        }

        .form-popup h1 {
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            margin-bottom: 20px;
            color: #744769;
            font-size: 22px;
            -webkit-text-stroke: 1.5px;
        }

        .form-popup form input[type="text"],
        .form-popup form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .form-popup form textarea {
            min-height: 300px;
            resize: none;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .form-popup form input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            margin-bottom: -10px;
        }

        .form-popup form button {
            background-color: #744769;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .form-popup form button:hover {
            background-color: #5a3651;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .form-popup .cancel-btn {
            background-color: #d3d3d3;
            color: white;
        }

        .form-popup .cancel-btn:hover {
            background-color: #ccc;
        }

        .hidden-form .cancel-btn {
            background-color: #d3d3d3;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .hidden-form .cancel-btn:hover {
            background-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(90, 54, 81, 0.2);
        }

        .edit-form button {
            background-color: #744769;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .edit-form button:hover {
            background-color: #5a3651;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .edit-form .cancel-btn {
            background-color: #d3d3d3;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .edit-form .cancel-btn:hover {
            background-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(90, 54, 81, 0.2);
        }

        .reply-form .cancel-btn {
            background-color: #d3d3d3;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .reply-form .cancel-btn:hover {
            background-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(90, 54, 81, 0.2);
        }

        .pinned-category,
        .pinned-topic {
            background-color: #f0e8f0;
            font-weight: bold;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .pinned-category i.fa-thumbtack,
        .pinned-topic i.fa-thumbtack {
            color: #744769;
            margin-right: 5px;
        }

        .pin-action {
            margin-left: 10px;
            color: #744769;
            text-decoration: underline;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .pin-action:hover {
            color: #442538;
            text-decoration: underline;
        }

        .donor-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border-radius: 50%;
            z-index: 1;
        }

        #isPinned {
            margin-top: -15px;
        }
    </style>
</head>
<body>
    <main>
        <div class="ribbon">
            <img src="../images/logo.jpg" alt="logo_img" class="logo_img">
            <div class="fa-container">
                <a href="https://www.facebook.com/UFODisclosureBulgaria" class="fa-brands fa-facebook" target="_blank"></a>
                <a href="https://www.youtube.com/@ufodisclosurebulgaria1249/videos" class="fa-brands fa-youtube" target="_blank"></a>
                <a href="https://www.instagram.com/ufodisclosurebulgaria/" class="fa-brands fa-instagram" target="_blank"></a>
                <a href="https://www.patreon.com/user?u=55698119&fbclid=IwY2xjawILL0VleHRuA2FlbQIxMAABHdmd3qR2vt8hYkxFB7ENp1iUcyr7vu6ewhXuomndmWEpPjjU4TJ0WYozAg_aem_VLsRldGoI_lx40W7MBHyhQ" class="fa-brands fa-patreon" target="_blank"></a>
            </div>
            <nav class="navbar">
                <ul class="nav-list">
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['home']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="../dashboard/index.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['dashboard']; ?></a></li>
                            <li><a href="../dashboard/our_team.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['our_team']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['content']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="../content/ytvideos.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['latest_videos']; ?></a></li>
                            <li><a href="../content/live_stream.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['live_stream']; ?></a></li>
                            <li><a href="../content/merch.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['merchandise']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['community']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['forum_menu']; ?></a></li>
                            <li><a href="../events/events_calendar.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['events']; ?></a></li>
                            <li><a href="../content/sightings_form.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['report_sighting']; ?></a></li>
                            <li><a href="../donations/donations_list.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['donors']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['profile']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="../user_info/view_profile.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['view_profile']; ?></a></li>
                            <?php endif; ?>                       
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="#" onclick="showLogoutPopup()"><?php echo $texts[$lang]['logout']; ?></a></li>
                            <?php else : ?>
                                <li><a href="../login_register/login.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['login']; ?></a></li>
                            <?php endif; ?>
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="../user_info/change_password.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['change_password']; ?></a></li>
                            <?php else : ?>
                                <li><a href="../login_register/register.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['registration']; ?></a></li>
                            <?php endif; ?>
                            <li><a href="../donations/donations.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['donations']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['language']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="?lang=en<?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?><?php echo $currentTopic ? '&topic=' . $currentTopic : ''; ?>">English (EN)</a></li>
                            <li><a href="?lang=bg<?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?><?php echo $currentTopic ? '&topic=' . $currentTopic : ''; ?>">Български (BG)</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="content-board">
        <?php if (!$currentCategory && !$currentTopic): ?>
                    <div class="forum-header">
                        <h1 style="border: none;"><?php echo $texts[$lang]['forum_title']; ?></h1>
                    </div>
                <?php endif; ?>
            <div class="forum-container">
                <?php if (!$currentCategory && !$currentTopic): ?>
                    <table class="category-list">
                        <thead>
                            <tr>
                                <th><?php echo $texts[$lang]['category']; ?></th>
                                <th><?php echo $texts[$lang]['description']; ?></th>
                                <th style="white-space: nowrap;"><?php echo $texts[$lang]['topics']; ?></th>
                                <?php if ($isLoggedIn && $userType === 'admin'): ?>
                                    <th><?php echo $texts[$lang]['actions']; ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $stmt = $db->prepare("SELECT COUNT(*) as topic_count FROM FORUM_TOPICS WHERE categoryID = ?");
                                $stmt->bindParam(1, $category['categoryID'], PDO::PARAM_INT);
                                $stmt->execute();
                                $topicCount = $stmt->fetch(PDO::FETCH_ASSOC)['topic_count'];
                                ?>
                                <tr class="<?= $category['isPinned'] ? 'pinned-category' : '' ?>">
                                    <td>
                                        <?php if ($category['isPinned']): ?>
                                            <i class="fas fa-thumbtack" style="color: #744769; margin-right: 5px;"></i>
                                        <?php endif; ?>
                                        <a href="forum.php?category=<?= $category['categoryID'] ?>&lang=<?php echo $lang; ?>" class="topic-title"><?= htmlspecialchars($category['categoryName']) ?></a>
                                    </td>
                                    <td><?= htmlspecialchars($category['categoryDescription']) ?></td>
                                    <td><?= $topicCount ?></td>
                                    <?php if ($isLoggedIn && $userType === 'admin'): ?>
                                        <td>
                                            <a href="#" class="delete-comment" onclick="showDeleteCategoryPopup(<?= $category['categoryID'] ?>)"><?php echo $texts[$lang]['delete']; ?></a>
                                            <a href="forum.php?toggle_pin_category=<?= $category['categoryID'] ?>&lang=<?php echo $lang; ?>" class="pin-action">
                                                <?= $category['isPinned'] ? $texts[$lang]['unpin'] : $texts[$lang]['pin'] ?>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($isLoggedIn && $userType === 'admin' && !$currentCategory && !$currentTopic): ?>
    <button type="button" id="show-category-form" class="toggle-form-btn" onclick="toggleCategoryPopup()"><?php echo $texts[$lang]['create_category']; ?></button>
    <div id="category-popup" class="form-popup">
        <h1><?php echo $texts[$lang]['create_category']; ?></h1><br>
        <div id="error" <?php echo !empty($error_msg) ? 'style="display:block"' : 'style="display:none"'; ?>>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
        <form method="POST" action="forum.php?lang=<?php echo $lang; ?>" onsubmit="return validateCategoryForm()">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="text" name="categoryNameEn" id="categoryNameEn" placeholder="<?php echo $texts[$lang]['category']; ?> (English)" value="<?php echo isset($_POST['categoryNameEn']) ? htmlspecialchars($_POST['categoryNameEn']) : ''; ?>">
            <textarea name="categoryDescriptionEn" id="categoryDescriptionEn" placeholder="<?php echo $texts[$lang]['description']; ?> (English)"><?php echo isset($_POST['categoryDescriptionEn']) ? htmlspecialchars($_POST['categoryDescriptionEn']) : ''; ?></textarea>
            <input type="text" name="categoryNameBg" id="categoryNameBg" placeholder="<?php echo $texts[$lang]['category']; ?> (Български)" value="<?php echo isset($_POST['categoryNameBg']) ? htmlspecialchars($_POST['categoryNameBg']) : ''; ?>">
            <textarea name="categoryDescriptionBg" id="categoryDescriptionBg" placeholder="<?php echo $texts[$lang]['description']; ?> (Български)"><?php echo isset($_POST['categoryDescriptionBg']) ? htmlspecialchars($_POST['categoryDescriptionBg']) : ''; ?></textarea>
            <div class="checkbox-container">
                <input type="checkbox" name="isPinned" id="isPinned" <?php echo isset($_POST['isPinned']) ? 'checked' : ''; ?>>
                <label for="isPinned"><?php echo $texts[$lang]['pin']; ?></label>
            </div>
            <button type="submit" name="create_category"><?php echo $texts[$lang]['create']; ?></button>
            <button type="button" class="cancel-btn" onclick="toggleCategoryPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </form>
    </div>
<?php endif; ?>
                <?php elseif ($currentCategory && !$currentTopic): ?>
                    <br>
                    <h3><?= htmlspecialchars($categoryInfo['categoryName']) ?></h3>
                    <div class="nav-breadcrumb">
                        <a href="forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['forum']; ?></a> <span>></span> <?= htmlspecialchars($categoryInfo['categoryName']) ?>
                    </div>
                    <table class="topic-list">
                        <thead>
                            <tr>
                                <th><?php echo $texts[$lang]['topic']; ?></th>
                                <th><?php echo $texts[$lang]['author']; ?></th>
                                <th style="white-space: nowrap;"><?php echo $texts[$lang]['comments']; ?></th>
                                <th><?php echo $texts[$lang]['date']; ?></th>
                                <?php if ($isLoggedIn && $userType === 'admin'): ?>
                                    <th><?php echo $texts[$lang]['actions']; ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($topics) === 0): ?>
                                <tr>
                                    <td colspan="<?php echo $isLoggedIn && $userType === 'admin' ? 5 : 4; ?>"><?php echo $texts[$lang]['no_topics']; ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topics as $topic): ?>
                                    <?php $badgeDetails = getBadgeDetails($topic['totalDonation'] ?? 0, $lang); ?>
                                    <tr class="<?= $topic['isPinned'] ? 'pinned-topic' : '' ?>">
                                        <td>
                                            <?php if ($topic['isPinned']): ?>
                                                <i class="fas fa-thumbtack" style="color: #744769; margin-right: 5px;"></i>
                                            <?php endif; ?>
                                            <a href="forum.php?topic=<?= $topic['topicID'] ?>&lang=<?php echo $lang; ?>" class="topic-title"><?= htmlspecialchars($topic['topicTitle']) ?></a>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <?= htmlspecialchars($topic['userFirstName'] . ' ' . $topic['userLastName']) ?>
                                            <?php if ($topic['userType'] === 'admin'): ?>
                                            <span class="admin-badge">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $topic['comment_count'] ?></td>
                                        <td><?= date('M d, Y', strtotime($topic['created_at'])) ?></td>
                                        <?php if ($isLoggedIn && $userType === 'admin'): ?>
                                            <td>
                                                <a href="#" class="delete-comment" onclick="showDeleteTopicPopup(<?= $topic['topicID'] ?>)"><?php echo $texts[$lang]['delete']; ?></a>
                                                <a href="forum.php?toggle_pin_topic=<?= $topic['topicID'] ?>&lang=<?php echo $lang; ?>" class="pin-action">
                                                    <?= $topic['isPinned'] ? $texts[$lang]['unpin'] : $texts[$lang]['pin'] ?>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if ($isLoggedIn): ?>
                        <button type="button" id="show-topic-form" class="toggle-form-btn" onclick="toggleTopicPopup()"><?php echo $texts[$lang]['create_new_topic']; ?></button>
                        <div id="topic-popup" class="form-popup">
                            <h1><?php echo $texts[$lang]['create_new_topic']; ?></h1><br>
                            <div id="error" <?php echo !empty($error_msg) ? 'style="display:block"' : 'style="display:none"'; ?>>
                                <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                            <form method="POST" action="forum.php?lang=<?php echo $lang; ?>" onsubmit="return validateTopicForm()">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="categoryID" value="<?= $currentCategory ?>">
                                <input type="text" name="topicTitle" id="topicTitle" placeholder="<?php echo $texts[$lang]['topic']; ?>" maxlength="80" value="<?php echo isset($_POST['topicTitle']) ? htmlspecialchars($_POST['topicTitle']) : ''; ?>">
                                <textarea name="topicContent" id="topicContent" placeholder="<?php echo $texts[$lang]['topic_content']; ?>"></textarea>
                                <?php if ($userType === 'admin'): ?>
                                    <div class="checkbox-container">
                                        <input type="checkbox" name="isPinned" id="isPinned" <?php echo isset($_POST['isPinned']) ? 'checked' : ''; ?>>
                                        <label for="isPinned"><?php echo $texts[$lang]['pin']; ?></label>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" name="create_topic"><?php echo $texts[$lang]['create']; ?></button>
                                <button type="button" class="cancel-btn" onclick="toggleTopicPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="login-message">
                            <?php echo $texts[$lang]['login_to_create']; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ($currentTopic): ?>
                    <?php $topicBadgeDetails = getBadgeDetails($topicInfo['totalDonation'] ?? 0, $lang); ?>
                    <br>
                    <h3><?= htmlspecialchars($topicInfo['topicTitle']) ?></h3>
                    <div class="nav-breadcrumb">
                        <a href="forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['forum']; ?></a> <span>></span>
                        <a href="forum.php?category=<?= $topicInfo['categoryID'] ?>&lang=<?php echo $lang; ?>"><?= htmlspecialchars($topicInfo['categoryName']) ?></a> <span>></span>
                        <?= htmlspecialchars($topicInfo['topicTitle']) ?>
                    </div>
                    <div class="comment">
                        <div class="comment-avatar">
                            <?php if ($topicInfo['userProfilePhoto']): ?>
                                <img src="<?= htmlspecialchars($topicInfo['userProfilePhoto']) ?>" alt="Profile Photo">
                            <?php else: ?>
                                <img src="../images/default_profile.jpg" alt="Default Profile">
                            <?php endif; ?>

                            <?php if ($topicBadgeDetails !== null): ?>
                                <div class="donor-badge" 
                                     title="<?= $topicBadgeDetails['title'] ?>"
                                     style="background-color: <?= $topicBadgeDetails['color'] ?>; 
                                            border: 2px solid <?= $topicBadgeDetails['border'] ?>;">
                                    <i class="fas fa-star" style="color: #000;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <?= htmlspecialchars($topicInfo['userFirstName'] . ' ' . $topicInfo['userLastName']) ?>
                                    <?php if ($topicInfo['userType'] === 'admin'): ?>
                                    <span class="admin-badge">Admin</span>
                                     <?php endif; ?>
                                </div>
                                <div class="comment-date"><?= date('M d, Y', strtotime($topicInfo['created_at'])) ?></div>
                            </div>
                            <div class="topic-content"><?= nl2br(htmlspecialchars($topicInfo['topicContent'])) ?></div>
                        </div>
                    </div>
                    <div class="comment-section">
                        <h2><?php echo $texts[$lang]['comments']; ?> (<?= count($comments) ?>)</h2>
                        <?php if (empty($comments)): ?>
                            <p><?php echo $texts[$lang]['no_comments']; ?></p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <?php $commentBadgeDetails = getBadgeDetails($comment['totalDonation'] ?? 0, $lang); ?>
                                <div class="comment" id="comment-<?= $comment['commentID'] ?>">
                                    <div class="comment-avatar">
                                        <?php if ($comment['userProfilePhoto']): ?>
                                            <img src="<?= htmlspecialchars($comment['userProfilePhoto']) ?>" alt="Profile Photo">
                                        <?php else: ?>
                                            <img src="../images/default_profile.jpg" alt="Default Profile">
                                        <?php endif; ?>

                                        <?php if ($commentBadgeDetails !== null): ?>
                                            <div class="donor-badge" 
                                                 title="<?= $commentBadgeDetails['title'] ?>"
                                                 style="background-color: <?= $commentBadgeDetails['color'] ?>; 
                                                        border: 2px solid <?= $commentBadgeDetails['border'] ?>;">
                                                <i class="fas fa-star" style="color: #000;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <div class="comment-author">
                                                <?= htmlspecialchars($comment['userFirstName'] . ' ' . $comment['userLastName']) ?>
                                                <?php if ($comment['userType'] === 'admin'): ?>
                                                <span class="admin-badge">Admin</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-date"><?= date('M d, Y', strtotime($comment['created_at'])) ?></div>
                                        </div>
                                        <div class="comment-text" id="comment-text-<?= $comment['commentID'] ?>"><?= nl2br(htmlspecialchars($comment['commentContent'])) ?></div>
                                        <?php if ($comment['isEdited']): ?>
                                            <div class="comment-status"><?php echo $texts[$lang]['edited']; ?></div>
                                        <?php endif; ?>
                                        <div class="comment-actions">
                                            <?php if ($isLoggedIn): ?>
                                                <?php if ($comment['userID'] == $userID): ?>
                                                    <div class="edit-comment" onclick="toggleEditForm(<?= $comment['commentID'] ?>)"><?php echo $texts[$lang]['edit']; ?></div>
                                                <?php endif; ?>
                                                <?php if ($comment['userID'] == $userID || $userType === 'admin'): ?>
                                                    <div class="delete-comment <?= $userType === 'admin' ? 'admin-delete' : '' ?>" onclick="showDeleteCommentPopup(<?= $comment['commentID'] ?>, <?= $currentTopic ?>)"><?php echo $texts[$lang]['delete']; ?></div>
                                                <?php endif; ?>
                                                <div class="reply-link" onclick="toggleReplyForm(<?= $comment['commentID'] ?>)"><?php echo $texts[$lang]['reply']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($isLoggedIn && ($comment['userID'] == $userID)): ?>
                                            <div id="edit-form-<?= $comment['commentID'] ?>" class="edit-form">
                                                <div id="edit-error-<?= $comment['commentID'] ?>" class="error" style="display:none;"></div>
                                                <form method="POST" action="forum.php?lang=<?php echo $lang; ?>" onsubmit="return validateEditForm(this)">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                    <input type="hidden" name="commentID" value="<?= $comment['commentID'] ?>">
                                                    <input type="hidden" name="topicID" value="<?= $currentTopic ?>">
                                                    <textarea name="commentContent" oninput="toggleButtonState(this, 'editSubmit-<?= $comment['commentID'] ?>')"><?= htmlspecialchars($comment['commentContent']) ?></textarea>
                                                    <button type="submit" name="edit_comment" id="editSubmit-<?= $comment['commentID'] ?>" disabled><?php echo $texts[$lang]['save_changes']; ?></button>
                                                    <button type="button" class="cancel-btn" onclick="toggleEditForm(<?= $comment['commentID'] ?>)"><?php echo $texts[$lang]['cancel']; ?></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($isLoggedIn): ?>
                                            <div id="reply-form-<?= $comment['commentID'] ?>" class="reply-form" style="display:none">
    <div id="reply-error-<?= $comment['commentID'] ?>" class="error" style="display:none;"></div>
    <form onsubmit="return submitComment(this, event)">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="topicID" value="<?= $currentTopic ?>">
        <input type="hidden" name="parentCommentID" value="<?= $comment['commentID'] ?>">
        <textarea name="commentContent" placeholder="<?php echo $texts[$lang]['reply']; ?>" oninput="toggleButtonState(this, 'replySubmit-<?= $comment['commentID'] ?>')"></textarea>
        <button type="submit" name="add_comment" id="replySubmit-<?= $comment['commentID'] ?>" disabled><?php echo $texts[$lang]['submit']; ?></button>
        <button type="button" class="cancel-btn" onclick="toggleReplyForm(<?= $comment['commentID'] ?>)"><?php echo $texts[$lang]['cancel']; ?></button>
    </form>
</div>
                                        <?php endif; ?>
                                        <?php if (isset($commentReplies[$comment['commentID']])): ?>
                                            <?php foreach ($commentReplies[$comment['commentID']] as $reply): ?>
                                                <?php $replyBadgeDetails = getBadgeDetails($reply['totalDonation'] ?? 0, $lang); ?>
                                                <div class="comment nested-comment" id="comment-<?= $reply['commentID'] ?>">
                                                    <div class="comment-avatar">
                                                        <?php if ($reply['userProfilePhoto']): ?>
                                                            <img src="<?= htmlspecialchars($reply['userProfilePhoto']) ?>" alt="Profile Photo">
                                                        <?php else: ?>
                                                            <img src="../images/default_profile.jpg" alt="Default Profile">
                                                        <?php endif; ?>
                                                        <?php if ($replyBadgeDetails !== null): ?>
                                                            <div class="donor-badge" 
                                                                 title="<?= $replyBadgeDetails['title'] ?>"
                                                                 style="background-color: <?= $replyBadgeDetails['color'] ?>; 
                                                                        border: 2px solid <?= $replyBadgeDetails['border'] ?>;">
                                                                <i class="fas fa-star" style="color: #000;"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="comment-content">
                                                        <div class="comment-header">
                                                            <div class="comment-author">
                                                                <?= htmlspecialchars($reply['userFirstName'] . ' ' . $reply['userLastName']) ?>
                                                                <?php if ($reply['userType'] === 'admin'): ?>
                                                                <span class="admin-badge">Admin</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="comment-date"><?= date('M d, Y', strtotime($reply['created_at'])) ?></div>
                                                        </div>
                                                        <div class="comment-text" id="comment-text-<?= $reply['commentID'] ?>"><?= nl2br(htmlspecialchars($reply['commentContent'])) ?></div>
                                                        <?php if ($reply['isEdited']): ?>
                                                            <div class="comment-status"><?php echo $texts[$lang]['edited']; ?></div>
                                                        <?php endif; ?>
                                                        <div class="comment-actions">
                                                            <?php if ($isLoggedIn): ?>
                                                                <?php if ($reply['userID'] == $userID): ?>
                                                                    <div class="edit-comment" onclick="toggleEditForm(<?= $reply['commentID'] ?>)"><?php echo $texts[$lang]['edit']; ?></div>
                                                                <?php endif; ?>
                                                                <?php if ($reply['userID'] == $userID || $userType === 'admin'): ?>
                                                                    <div class="delete-comment <?= $userType === 'admin' ? 'admin-delete' : '' ?>" onclick="showDeleteCommentPopup(<?= $reply['commentID'] ?>, <?= $currentTopic ?>)"><?php echo $texts[$lang]['delete']; ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($isLoggedIn && ($reply['userID'] == $userID)): ?>
                                                            <div id="edit-form-<?= $reply['commentID'] ?>" class="edit-form">
                                                                <form method="POST" action="forum.php?lang=<?php echo $lang; ?>">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                                    <input type="hidden" name="commentID" value="<?= $reply['commentID'] ?>">
                                                                    <input type="hidden" name="topicID" value="<?= $currentTopic ?>">
                                                                    <textarea name="commentContent" oninput="toggleButtonState(this, 'editSubmit-<?= $reply['commentID'] ?>')"><?= htmlspecialchars($reply['commentContent']) ?></textarea>
                                                                    <button type="submit" name="edit_comment" id="editSubmit-<?= $reply['commentID'] ?>" disabled><?php echo $texts[$lang]['save_changes']; ?></button>
                                                                    <button type="button" class="cancel-btn" onclick="toggleEditForm(<?= $reply['commentID'] ?>)"><?php echo $texts[$lang]['cancel']; ?></button>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($isLoggedIn): ?>
                            <button type="button" id="show-comment-form" class="toggle-form-btn" onclick="toggleCommentForm()"><?php echo $texts[$lang]['add_comment']; ?></button>
                            <div id="comment-form" class="hidden-form">
    <div class="comment-form">
        <div id="comment-error" class="error" style="display:none;"></div>
        <form onsubmit="return submitComment(this, event)">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="topicID" value="<?= $currentTopic ?>">
            <textarea name="commentContent" id="mainCommentContent" placeholder="<?php echo $texts[$lang]['add_comment']; ?>" oninput="toggleButtonState(this, 'mainCommentSubmit')"></textarea><br>
            <button type="submit" name="add_comment" id="mainCommentSubmit" disabled><?php echo $texts[$lang]['submit']; ?></button>
            <button type="button" class="cancel-btn" onclick="toggleCommentForm()"><?php echo $texts[$lang]['cancel']; ?></button>
        </form>
    </div>
</div>
                        <?php else: ?>
                            <div class="login-message">
                                <?php echo $texts[$lang]['login_to_comment']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="overlay" class="overlay"></div>
        <div id="logoutPopup" class="popup">
            <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
            <button class="confirm" onclick="logout()"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideLogoutPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
        <div id="deleteCategoryPopup" class="popup">
            <h2><?php echo $texts[$lang]['delete_category_popup']; ?></h2>
            <button class="confirm" id="confirmDeleteCategory"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideDeleteCategoryPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
        <div id="deleteTopicPopup" class="popup">
            <h2><?php echo $texts[$lang]['delete_topic_popup']; ?></h2>
            <button class="confirm" id="confirmDeleteTopic"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideDeleteTopicPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
        <div id="deleteCommentPopup" class="popup">
            <h2><?php echo $texts[$lang]['delete_comment_popup']; ?></h2>
            <button class="confirm" id="confirmDeleteComment"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideDeleteCommentPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
    </main>
    <script type="text/javascript">
// Pass translations from PHP to JavaScript as a JSON object
const translations = <?php echo json_encode([
    'create_category' => $texts[$lang]['create_category'],
    'create_new_topic' => $texts[$lang]['create_new_topic'],
    'add_comment' => $texts[$lang]['add_comment'],
    'error_category_empty' => $texts[$lang]['error_category_empty'],
    'error_description_empty' => $texts[$lang]['error_description_empty'],
    'error_topic_title_empty' => $texts[$lang]['error_topic_title_empty'],
    'error_topic_content_empty' => $texts[$lang]['error_topic_content_empty'],
    'error_topic_title_length' => $texts[$lang]['error_topic_title_length'],
    'error_comments_empty' => $texts[$lang]['error_comments_empty'],
    'error_reply_empty' => $texts[$lang]['error_reply_empty'],
    'error_csrf' => $texts[$lang]['error_csrf'],
    // Add missing translations
    'edit' => $texts[$lang]['edit'],
    'delete' => $texts[$lang]['delete'],
    'reply' => $texts[$lang]['reply'],
    'save_changes' => $texts[$lang]['save_changes'],
    'submit' => $texts[$lang]['submit'],
    'cancel' => $texts[$lang]['cancel'],
    'comments' => $texts[$lang]['comments']
]); ?>;

function showLogoutPopup() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('logoutPopup').style.display = 'block';
}

function hideLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function logout() {
    window.location.href = '../login_register/logout.php';
}

function createCommentElement(comment, parentCommentID = null, lang) {
    const badgeDetails = getBadgeDetails(comment.totalDonation || 0);
    const isNested = parentCommentID !== null;
    const currentUserID = <?php echo $isLoggedIn ? $userID : 'null'; ?>;
    const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
    const userType = '<?php echo $userType; ?>';

    // Match the exact structure of PHP-rendered comments
    let commentHTML = `
        <div class="comment${isNested ? ' nested-comment' : ''}" id="comment-${comment.commentID}">
            <div class="comment-avatar">
                <img src="${comment.userProfilePhoto || '../images/default_profile.jpg'}" alt="Profile Photo">
                ${badgeDetails ? `
                    <div class="donor-badge" 
                         title="${badgeDetails.title}"
                         style="background-color: ${badgeDetails.color}; border: 2px solid ${badgeDetails.border};">
                        <i class="fas fa-star" style="color: #000;"></i>
                    </div>
                ` : ''}
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <div class="comment-author">
                        ${comment.userFirstName} ${comment.userLastName}
                        ${comment.userType === 'admin' ? '<span class="admin-badge">Admin</span>' : ''}
                    </div>
                    <div class="comment-date">${comment.created_at}</div>
                </div>
                <div class="comment-text" id="comment-text-${comment.commentID}">${comment.commentContent.replace(/\n/g, '<br>')}</div>
                ${comment.isEdited ? '<div class="comment-status">' + translations.edited + '</div>' : ''}
                <div class="comment-actions">
    `;

    if (isLoggedIn) {
        if (currentUserID === comment.userID) {
            commentHTML += `
                <div class="edit-comment" onclick="toggleEditForm(${comment.commentID})">${translations.edit}</div>
            `;
        }
        if (currentUserID === comment.userID || userType === 'admin') {
            commentHTML += `
                <div class="delete-comment${userType === 'admin' ? ' admin-delete' : ''}" onclick="showDeleteCommentPopup(${comment.commentID}, ${comment.topicID})">${translations.delete}</div>
            `;
        }
        if (!isNested) { // Only add reply option to top-level comments
            commentHTML += `
                <div class="reply-link" onclick="toggleReplyForm(${comment.commentID})">${translations.reply}</div>
            `;
        }
    }

    commentHTML += `
                </div>
    `;

    if (isLoggedIn && currentUserID === comment.userID) {
        commentHTML += `
                <div id="edit-form-${comment.commentID}" class="edit-form" style="display:none">
                    <div id="edit-error-${comment.commentID}" class="error" style="display:none;"></div>
                    <form method="POST" action="forum.php?lang=${lang}" onsubmit="return validateEditForm(this)">
                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name=csrf_token]').value}">
                        <input type="hidden" name="commentID" value="${comment.commentID}">
                        <input type="hidden" name="topicID" value="${comment.topicID}">
                        <textarea name="commentContent" oninput="toggleButtonState(this, 'editSubmit-${comment.commentID}')">${comment.commentContent}</textarea>
                        <button type="submit" name="edit_comment" id="editSubmit-${comment.commentID}" disabled>${translations.save_changes}</button>
                        <button type="button" class="cancel-btn" onclick="toggleEditForm(${comment.commentID})">${translations.cancel}</button>
                    </form>
                </div>
        `;
    }

    if (isLoggedIn && !isNested) {
        commentHTML += `
                <div id="reply-form-${comment.commentID}" class="reply-form" style="display:none">
                    <div id="reply-error-${comment.commentID}" class="error" style="display:none;"></div>
                    <form onsubmit="return submitComment(this, event)">
                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name=csrf_token]').value}">
                        <input type="hidden" name="topicID" value="${comment.topicID}">
                        <input type="hidden" name="parentCommentID" value="${comment.commentID}">
                        <textarea name="commentContent" placeholder="${translations.reply}" oninput="toggleButtonState(this, 'replySubmit-${comment.commentID}')"></textarea>
                        <button type="submit" name="add_comment" id="replySubmit-${comment.commentID}" disabled>${translations.submit}</button>
                        <button type="button" class="cancel-btn" onclick="toggleReplyForm(${comment.commentID})">${translations.cancel}</button>
                    </form>
                </div>
        `;
    }

    commentHTML += `
            </div>
        </div>
    `;

    return commentHTML;
}

// Modified function to get badge details (client-side version)
function getBadgeDetails(amount) {
    amount = amount / 100;
    if (amount >= 50) {
        return { color: '#FFD700', border: '#B8860B', title: translations['bg'] ? 'Златен Дарител' : 'Gold Donor' };
    } else if (amount >= 25) {
        return { color: '#C0C0C0', border: '#808080', title: translations['bg'] ? 'Сребърен Дарител' : 'Silver Donor' };
    } else if (amount >= 10) {
        return { color: '#CD7F32', border: '#8B4513', title: translations['bg'] ? 'Бронзов Дарител' : 'Bronze Donor' };
    }
    return null;
}

// New function to handle AJAX comment submission
function submitComment(form, event) {
    event.preventDefault();
    
    const formData = new FormData(form);
    formData.append('action', 'add_comment');
    
    const commentSection = document.querySelector('.comment-section');
    const commentCount = commentSection.querySelector('h2');
    const currentCount = parseInt(commentCount.textContent.match(/\d+/)[0]);
    const showCommentButton = document.getElementById('show-comment-form');
    const commentFormContainer = document.getElementById('comment-form');
    
    fetch('../forum/ajax_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentHTML = createCommentElement(data.comment, data.parentCommentID, '<?php echo $lang; ?>');
            
            if (data.parentCommentID) {
                // For replies, append to parent comment
                const parentCommentContent = document.getElementById(`comment-${data.parentCommentID}`).querySelector('.comment-content');
                parentCommentContent.insertAdjacentHTML('beforeend', commentHTML);
            } else {
                // For main comments, append to the comment section
                commentSection.insertAdjacentHTML('beforeend', commentHTML);
                commentCount.textContent = `${translations.comments} (${currentCount + 1})`;
                // Move the button and form to the bottom after adding the comment
                commentSection.appendChild(showCommentButton);
                commentSection.appendChild(commentFormContainer);
            }
            
            form.reset();
            if (form.closest('#comment-form')) {
                toggleCommentForm();
            } else {
                const commentID = form.querySelector('input[name="parentCommentID"]').value;
                toggleReplyForm(commentID);
            }
            
            updateContentBoardHeight();
        } else {
            const errorDiv = form.previousElementSibling;
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorDiv = form.previousElementSibling;
        errorDiv.textContent = 'An error occurred while posting the comment';
        errorDiv.style.display = 'block';
    });
    
    return false;
}

// Update existing form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Modify main comment form
    const mainCommentForm = document.querySelector('#comment-form form');
    if (mainCommentForm) {
        mainCommentForm.onsubmit = function(event) {
            return submitComment(this, event);
        };
    }

    // Modify existing reply forms
    document.querySelectorAll('.reply-form form').forEach(form => {
        form.onsubmit = function(event) {
            return submitComment(this, event);
        };
    });
});

// Update validateCommentForm to work with AJAX
function validateCommentForm(form) {
    const commentContent = form.querySelector('textarea[name="commentContent"]').value.trim();
    const errorDiv = form.previousElementSibling;
    
    if (commentContent === '') {
        errorDiv.textContent = translations.error_comments_empty;
        errorDiv.style.display = 'block';
        return false;
    }
    
    return true;
}

function toggleCategoryPopup() {
    const popup = document.getElementById('category-popup');
    const overlay = document.getElementById('overlay');
    if (popup.style.display === 'block') {
        popup.style.display = 'none';
        overlay.style.display = 'none';
        document.getElementById('show-category-form').innerText = translations.create_category;
    } else {
        popup.style.display = 'block';
        overlay.style.display = 'block';
        resetError('error');
    }
}

function validateCategoryForm() {
    const categoryNameEn = document.getElementById('categoryNameEn').value.trim();
    const categoryNameBg = document.getElementById('categoryNameBg').value.trim();
    const categoryDescriptionEn = document.getElementById('categoryDescriptionEn').value.trim();
    const categoryDescriptionBg = document.getElementById('categoryDescriptionBg').value.trim();
    const errorDiv = document.getElementById('error');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    if (!csrfToken) {
        errorDiv.textContent = translations.error_csrf;
        errorDiv.style.display = 'block';
        return false;
    }

    if (categoryNameEn === '' || categoryNameBg === '') {
        errorDiv.textContent = translations.error_category_empty;
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (categoryDescriptionEn === '' || categoryDescriptionBg === '') {
        errorDiv.textContent = translations.error_description_empty;
        errorDiv.style.display = 'block';
        return false;
    }
    
    return true;
}

function toggleTopicPopup() {
    const popup = document.getElementById('topic-popup');
    const overlay = document.getElementById('overlay');
    if (popup.style.display === 'block') {
        popup.style.display = 'none';
        overlay.style.display = 'none';
        document.getElementById('show-topic-form').innerText = translations.create_new_topic;
    } else {
        popup.style.display = 'block';
        overlay.style.display = 'block';
        resetError('error');
    }
}

function validateTopicForm() {
    const topicTitle = document.getElementById('topicTitle').value.trim();
    const topicContent = document.getElementById('topicContent').value.trim();
    const errorDiv = document.getElementById('error');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    if (!csrfToken) {
        errorDiv.textContent = translations.error_csrf;
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (topicTitle === '') {
        errorDiv.textContent = translations.error_topic_title_empty;
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (topicTitle.length > 80) {
        errorDiv.textContent = translations.error_topic_title_length;
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (topicContent === '') {
        errorDiv.textContent = translations.error_topic_content_empty;
        errorDiv.style.display = 'block';
        return false;
    }
    
    return true;
}

function toggleCommentForm() {
    var form = document.getElementById('comment-form');
    var button = document.getElementById('show-comment-form');
    if (form.style.display === 'block') {
        form.style.display = 'none';
        button.style.display = 'block';
        button.innerText = translations.add_comment;
    } else {
        form.style.display = 'block';
        button.style.display = 'none';
        resetError('comment-error');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    updateContentBoardHeight();
}

function validateCommentForm(form) {
    const commentContent = form.querySelector('textarea[name="commentContent"]').value.trim();
    const csrfToken = form.querySelector('input[name="csrf_token"]').value;
    let errorDiv;
    
    if (form.closest('#comment-form')) {
        errorDiv = document.getElementById('comment-error');
    } else {
        const parentDiv = form.closest('.reply-form');
        if (parentDiv) {
            const commentId = parentDiv.id.replace('reply-form-', '');
            errorDiv = document.getElementById('reply-error-' + commentId);
        }
    }
    
    if (!csrfToken) {
        if (errorDiv) {
            errorDiv.textContent = translations.error_csrf;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_csrf);
        }
        return false;
    }
    
    if (commentContent === '') {
        if (errorDiv) {
            errorDiv.textContent = translations.error_comments_empty;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_comments_empty);
        }
        return false;
    }
    
    return true;
}

function showDeleteCategoryPopup(categoryID) {
    const popup = document.getElementById('deleteCategoryPopup');
    const overlay = document.getElementById('overlay');
    popup.style.display = 'block';
    overlay.style.display = 'block';
    document.getElementById('confirmDeleteCategory').onclick = function() {
        window.location.href = 'forum.php?delete_category=' + categoryID + '&lang=<?php echo $lang; ?>';
    };
}

function hideDeleteCategoryPopup() {
    document.getElementById('deleteCategoryPopup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function showDeleteTopicPopup(topicID) {
    const popup = document.getElementById('deleteTopicPopup');
    const overlay = document.getElementById('overlay');
    popup.style.display = 'block';
    overlay.style.display = 'block';
    document.getElementById('confirmDeleteTopic').onclick = function() {
        window.location.href = 'forum.php?delete_topic=' + topicID + '&lang=<?php echo $lang; ?>';
    };
}

function hideDeleteTopicPopup() {
    document.getElementById('deleteTopicPopup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function showDeleteCommentPopup(commentID, topicID) {
    const popup = document.getElementById('deleteCommentPopup');
    const overlay = document.getElementById('overlay');
    popup.style.display = 'block';
    overlay.style.display = 'block';
    document.getElementById('confirmDeleteComment').onclick = function() {
        window.location.href = 'forum.php?delete_comment=' + commentID + '&topicID=' + topicID + '&lang=<?php echo $lang; ?>';
    };
}

function hideDeleteCommentPopup() {
    document.getElementById('deleteCommentPopup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

document.getElementById('overlay').addEventListener('click', function() {
    document.getElementById('category-popup').style.display = 'none';
    document.getElementById('topic-popup').style.display = 'none';
    document.getElementById('logoutPopup').style.display = 'none';
    document.getElementById('deleteCategoryPopup').style.display = 'none';
    document.getElementById('deleteTopicPopup').style.display = 'none';
    document.getElementById('deleteCommentPopup').style.display = 'none';
    this.style.display = 'none';
    document.getElementById('show-category-form').innerText = translations.create_category;
    document.getElementById('show-topic-form').innerText = translations.create_new_topic;
});

function toggleButtonState(textarea, buttonId) {
    const button = document.getElementById(buttonId);
    if (textarea.value.trim() === '') {
        button.disabled = true;
    } else {
        button.disabled = false;
    }
}

function updateContentBoardHeight() {
    const contentBoard = document.querySelector(".content-board");
    contentBoard.style.height = "auto";
    let newHeight = contentBoard.scrollHeight + "px";
    contentBoard.style.height = newHeight;
}

function validateReplyForm(form) {
    const commentContent = form.querySelector('textarea[name="commentContent"]').value.trim();
    const parentCommentId = form.querySelector('input[name="parentCommentID"]').value;
    const csrfToken = form.querySelector('input[name="csrf_token"]').value;
    const errorDiv = document.getElementById('reply-error-' + parentCommentId);
    
    if (!csrfToken) {
        if (errorDiv) {
            errorDiv.textContent = translations.error_csrf;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_csrf);
        }
        return false;
    }
    
    if (commentContent === '') {
        if (errorDiv) {
            errorDiv.textContent = translations.error_reply_empty;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_reply_empty);
        }
        return false;
    }
    
    return true;
}

function toggleReplyForm(commentID) {
    const replyForm = document.getElementById('reply-form-' + commentID);
    const editForm = document.getElementById('edit-form-' + commentID);
    if (replyForm.style.display === 'block') {
        replyForm.style.display = 'none';
    } else {
        replyForm.style.display = 'block';
        resetError('reply-error-' + commentID);
        if (editForm && editForm.style.display === 'block') {
            editForm.style.display = 'none';
            const commentText = document.getElementById('comment-text-' + commentID);
            if (commentText) commentText.style.display = 'block';
        }
    }
    updateContentBoardHeight();
}

function validateEditForm(form) {
    const commentContent = form.querySelector('textarea[name="commentContent"]').value.trim();
    const commentId = form.querySelector('input[name="commentID"]').value;
    const csrfToken = form.querySelector('input[name="csrf_token"]').value;
    const errorDiv = document.getElementById('edit-error-' + commentId);
    
    if (!csrfToken) {
        if (errorDiv) {
            errorDiv.textContent = translations.error_csrf;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_csrf);
        }
        return false;
    }
    
    if (commentContent === '') {
        if (errorDiv) {
            errorDiv.textContent = translations.error_comments_empty;
            errorDiv.style.display = 'block';
        } else {
            alert(translations.error_comments_empty);
        }
        return false;
    }
    
    return true;
}

function toggleEditForm(commentID) {
    const editForm = document.getElementById('edit-form-' + commentID);
    const commentText = document.getElementById('comment-text-' + commentID);
    const replyForm = document.getElementById('reply-form-' + commentID);
    if (editForm.style.display === 'block') {
        editForm.style.display = 'none';
        commentText.style.display = 'block';
    } else {
        editForm.style.display = 'block';
        resetError('edit-error-' + commentID);
        commentText.style.display = 'none';
        if (replyForm && replyForm.style.display === 'block') {
            replyForm.style.display = 'none';
        }
    }
    updateContentBoardHeight();
}

// Helper function to reset error messages
function resetError(errorId) {
    const errorDiv = document.getElementById(errorId);
    if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const contentBoard = document.querySelector(".content-board");
    contentBoard.style.height = "auto";
    let actualHeight = contentBoard.scrollHeight + "px";
    contentBoard.style.height = "0";
    setTimeout(() => {
        contentBoard.style.height = actualHeight;
        contentBoard.classList.add("loaded");
    }, 100);

    const editTextareas = document.querySelectorAll('.edit-form textarea');
    editTextareas.forEach(textarea => {
        const form = textarea.closest('form');
        const submitButton = form.querySelector('button[type="submit"]');
        if (textarea.value.trim() !== '') {
            submitButton.disabled = false;
        }
    });

    // Add CSRF token check on form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const csrfToken = form.querySelector('input[name="csrf_token"]').value;
            if (!csrfToken) {
                e.preventDefault();
                const errorDiv = form.querySelector('.error') || document.createElement('div');
                if (!errorDiv.classList.contains('error')) {
                    errorDiv.classList.add('error');
                    form.prepend(errorDiv);
                }
                errorDiv.textContent = translations.error_csrf;
                errorDiv.style.display = 'block';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Create hamburger menu element if it doesn't exist
    if (!document.querySelector('.hamburger-menu')) {
        const hamburgerMenu = document.createElement('div');
        hamburgerMenu.className = 'hamburger-menu';
        
        // Create three bars for the hamburger icon
        for (let i = 0; i < 3; i++) {
            const bar = document.createElement('div');
            bar.className = 'bar';
            hamburgerMenu.appendChild(bar);
        }
        
        // Add hamburger menu to the document
        document.querySelector('.ribbon').prepend(hamburgerMenu);
    }
    
    // Create overlay element if it doesn't exist
    if (!document.querySelector('.menu-overlay')) {
        const menuOverlay = document.createElement('div');
        menuOverlay.className = 'menu-overlay';
        document.body.appendChild(menuOverlay);
    }
    
    // Get navbar element
    const navbar = document.querySelector('.navbar');
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const menuOverlay = document.querySelector('.menu-overlay');
    
    // Toggle menu function
    function toggleMenu() {
        navbar.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        hamburgerMenu.classList.toggle('active');
    }
    
    // Event listeners
    hamburgerMenu.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);
    
    // Function to update the dropdown arrows based on screen size
    function updateDropdownArrows() {
        const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
        
        if (window.innerWidth <= 768) {
            // Mobile view - add arrows
            dropdownItems.forEach(item => {
                const mainLink = item.querySelector('a');
                
                // Only modify if we haven't already (check for existing dropdown-arrow)
                if (mainLink && !mainLink.querySelector('.dropdown-arrow')) {
                    // Save the original text content
                    const originalText = mainLink.textContent.replace(' ▾', '');
                    
                    // Clear and rebuild the content with the arrow span
                    mainLink.innerHTML = originalText + 
                        '<span class="dropdown-arrow"><i class="fa fa-chevron-right"></i></span>';
                }
            });
        } else {
            // Desktop view - restore original text with ▾
            dropdownItems.forEach(item => {
                const mainLink = item.querySelector('a');
                
                if (mainLink && mainLink.querySelector('.dropdown-arrow')) {
                    // Get text content without the arrow span
                    const textContent = mainLink.childNodes[0].nodeValue.trim();
                    
                    // Restore original format with ▾
                    mainLink.innerHTML = textContent + ' ▾';
                }
            });
        }
    }
    
    // Handle dropdown menu clicks for mobile
    document.addEventListener('click', function(e) {
    // Only for mobile view
    if (window.innerWidth <= 768) {
        // Check if click is on a dropdown parent item
        if (e.target.closest('.nav-item.dropdown > a')) {
            const link = e.target.closest('.nav-item.dropdown > a');
            const dropdownItem = link.parentNode;
            
            // Prevent default only for parent dropdowns in mobile
            e.preventDefault();
            
            // Toggle dropdown visibility
            const wasActive = dropdownItem.classList.contains('active');
            
            // Close all other open dropdowns first
            document.querySelectorAll('.nav-item.dropdown.active').forEach(item => {
                if (item !== dropdownItem) {
                    item.classList.remove('active');
                    const menu = item.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.style.maxHeight = '0px';
                        // Don't hide it immediately
                        setTimeout(() => {
                            if (!item.classList.contains('active')) {
                                menu.style.display = 'none';
                            }
                        }, 300);
                    }
                    
                    // Reset arrow if exists
                    const arrow = item.querySelector('.dropdown-arrow');
                    if (arrow) {
                        arrow.classList.remove('rotate');
                    }
                }
            });
            
            // Toggle current dropdown
            dropdownItem.classList.toggle('active');
            
            // Toggle arrow rotation
            const arrow = link.querySelector('.dropdown-arrow');
            if (arrow) {
                arrow.classList.toggle('rotate');
            }
            
            // Toggle dropdown content visibility with animation
            const dropdownMenu = dropdownItem.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                if (wasActive) {
                    // Closing the dropdown
                    dropdownMenu.style.maxHeight = '0px';
                    setTimeout(() => {
                        if (!dropdownItem.classList.contains('active')) {
                            dropdownMenu.style.display = 'none';
                        }
                    }, 300);
                } else {
                    // Opening the dropdown
                    dropdownMenu.style.display = 'block';
                    // Force a reflow to ensure the browser processes the display change
                    void dropdownMenu.offsetHeight;
                    dropdownMenu.style.maxHeight = dropdownMenu.scrollHeight + 'px';
                }
            }
        }
    }
});
    
    updateDropdownArrows();
    
    window.addEventListener('resize', function() {
        updateDropdownArrows();
        
        if (window.innerWidth > 768) {
            navbar.classList.remove('active');
            menuOverlay.classList.remove('active');
            hamburgerMenu.classList.remove('active');
            
            document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                item.classList.remove('active');
                
                const dropdownMenu = item.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.style.display = '';
                    dropdownMenu.style.maxHeight = '';
                }
            });
        }
    });
});
</script>
</body>
</html>