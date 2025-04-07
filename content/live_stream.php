<?php
session_start();
require_once '../inc/db_connect.php';

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);
$userID = $isLoggedIn ? $_SESSION['user']['userID'] : null;
$userType = $isLoggedIn ? $_SESSION['user']['userType'] : '';
$isAdmin = ($isLoggedIn && $userType === 'admin');

// Language handling
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bg'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'en';
    $_SESSION['lang'] = $lang;
}

// Language-specific text arrays
$texts = [
    'en' => [
        'title' => 'Live Stream - UFO Disclosure Bulgaria',
        'stream_title' => 'LIVE STREAM',
        'description' => "Welcome to UFO Disclosure Bulgaria's live stream!",
        'chat_instruction_logged_out' => 'Log in to participate in the chat.',
        'upcoming_streams' => 'Upcoming Streams',
        'no_streams' => 'No upcoming webinars scheduled at this time.',
        'live_chat' => 'LIVE CHAT',
        'chat_offline' => 'CHAT OFFLINE',
        'chat_placeholder_logged_in' => 'Type your message here...',
        'chat_placeholder_logged_out' => 'Please log in to chat',
        'chat_unavailable' => 'Chat unavailable while stream is offline',
        'input_chat_unavailable' => 'Chat currently unavailable',
        'send' => 'SEND',
        'offline_message' => 'The live stream is currently offline. Check back later or see upcoming streams below!',
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'language' => 'LANGUAGE',
        'dashboard' => 'Dashboard',
        'our_team' => 'Our Team',
        'latest_videos' => 'Latest Videos',
        'live_stream' => 'Live Stream',
        'merchandise' => 'Merchandise',
        'forum_menu' => 'Forum',
        'events' => 'Events',
        'report_sighting' => 'Report Sighting',
        'donors' => 'Donors List',
        'view_profile' => 'View Profile',
        'logout_popup' => 'Are you sure you want to log out?',
        'yes' => 'Yes',
        'cancel' => 'Cancel',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations',
        'toggle_stream_on' => 'Turn Stream On',
        'toggle_stream_off' => 'Turn Stream Off',
    ],
    'bg' => [
        'title' => 'Излъчване на Живо - НЛО Разкритие България',
        'stream_title' => 'ИЗЛЪЧВАНЕ НА ЖИВО',
        'description' => 'Добре дошли в живото излъчване на НЛО Разкритие България.',
        'chat_instruction_logged_out' => 'Влезте, за да участвате в чата.',
        'upcoming_streams' => 'Предстоящи Излъчвания',
        'no_streams' => 'Няма насрочени предстоящи уебинари в момента.',
        'live_chat' => 'ЧАТ НА ЖИВО',
        'chat_offline' => 'ЧАТ ИЗКЛЮЧЕН',
        'chat_placeholder_logged_in' => 'Напишете съобщението си тук...',
        'chat_placeholder_logged_out' => 'Моля, влезте, за да пишете в чата',
        'chat_unavailable' => 'Чатът не е достъпен, докато излъчването е офлайн',
        'input_chat_unavailable' => 'Чатът не е достъпен',
        'send' => 'ИЗПРАТИ',
        'offline_message' => 'Живото излъчване в момента е офлайн. Проверете отново по-късно или вижте предстоящите предавания по-долу!',
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'language' => 'ЕЗИК',
        'dashboard' => 'Начален Панел',
        'our_team' => 'Нашият Екип',
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Излъчване на Живо',
        'merchandise' => 'Стоки',
        'forum_menu' => 'Форум',
        'events' => 'Събития',
        'report_sighting' => 'Докладвай Наблюдение',
        'donors' => 'Списък с Дарители',
        'view_profile' => 'Преглед на Профила',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        'yes' => 'Да',
        'cancel' => 'Отказ',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения',
        'toggle_stream_on' => 'Включи Излъчването',
        'toggle_stream_off' => 'Изключи Излъчването',
    ]
];

// Fetch user data
$profilePhotoPath = '../images/default_profile.jpg';
$user_data = ['userFirstName' => 'Guest', 'userLastName' => ''];
$admin_info = ['userFirstName' => 'Admin', 'userLastName' => ''];

if ($isLoggedIn) {
    $query = 'SELECT userFirstName, userLastName, userProfilePhoto FROM users WHERE userID = :userID';
    $stmt = $db->prepare($query);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_data = $user;
        if (!empty($user['userProfilePhoto']) && $user['userProfilePhoto'] !== '../images/default_profile.jpg') {
            $profilePhotoPath = $user['userProfilePhoto'];
        }
    }

    $admin_query = "SELECT userFirstName, userLastName FROM USERS WHERE userType = 'admin' LIMIT 1";
    $admin_stmt = $db->prepare($admin_query);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin_result) {
        $admin_info = $admin_result;
    }
}

// Fetch stream state from database
$stream_query = "SELECT setting_value FROM stream_settings WHERE setting_name = 'is_stream_live'";
$stream_stmt = $db->prepare($stream_query);
$stream_stmt->execute();
$stream_result = $stream_stmt->fetch(PDO::FETCH_ASSOC);
$is_stream_live = $stream_result && $stream_result['setting_value'] == '1' ? true : false;

// Validate CSRF token for POST requests
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Handle stream toggle (admin only) with CSRF protection
if ($isAdmin && isset($_POST['toggle_stream']) && isset($_POST['csrf_token'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $new_state = $is_stream_live ? '0' : '1';
    $update_query = "UPDATE stream_settings SET setting_value = :value WHERE setting_name = 'is_stream_live'";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindValue(':value', $new_state, PDO::PARAM_STR);
    if ($update_stmt->execute()) {
        // Regenerate CSRF token after successful POST
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        if ($new_state === '0') {
            $clear_query = "TRUNCATE TABLE chat_messages";
            $clear_stmt = $db->prepare($clear_query);
            $clear_stmt->execute();
        }
        
        header("Location: live_stream.php?lang=$lang");
        exit;
    }
}

// Handle chat message submission with CSRF protection
if ($is_stream_live && $isLoggedIn && isset($_POST['chat_message']) && isset($_POST['csrf_token'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $message = trim($_POST['chat_message']);
    if (!empty($message)) {
        $insert_query = "INSERT INTO chat_messages (user_id, message, timestamp) VALUES (:user_id, :message, NOW())";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindValue(':user_id', $userID, PDO::PARAM_INT);
        $insert_stmt->bindValue(':message', $message, PDO::PARAM_STR);
        if ($insert_stmt->execute()) {
            // Regenerate CSRF token after successful POST
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

// Fetch initial chat messages
$initial_messages = [];
if ($is_stream_live) {
    $chat_query = "
        SELECT cm.id, cm.message, cm.timestamp, u.userFirstName, u.userLastName, u.userType, u.userProfilePhoto
        FROM chat_messages cm
        JOIN users u ON cm.user_id = u.userID
        ORDER BY cm.timestamp ASC
        LIMIT 50
    ";
    $chat_stmt = $db->prepare($chat_query);
    $chat_stmt->execute();
    $initial_messages = $chat_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background: none; 
            border: none; 
            box-shadow: none; 
            display: inline-block;
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
        .livestream-container {
            padding: 20px;
            color: #333;
            font-family: 'Jura', sans-serif;
        }
        .welcome-section {
            background: #f0e8f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .widget {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .widget h3 {
            color: #744769;
            margin-bottom: 10px;
            font-size: 1.5rem;
            -webkit-text-stroke: 0.3px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }
        .event-list {
            list-style: none;
            padding: 0;
        }
        .event-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .event-list li:last-child {
            border-bottom: none;
        }
        .offline-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgb(211, 40, 40);
            margin-left: 8px;
            box-shadow: 0 0 5px 2px rgba(211, 40, 40, 0.7);
        }
        @keyframes pulse {
            0% { opacity: 0.7; }
            50% { opacity: 1; }
            100% { opacity: 0.7; }
        }
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgb(40, 211, 71);
            margin-left: 8px;
            animation: pulse 1.5s infinite ease-in-out;
            box-shadow: 0 0 5px 2px rgba(40, 211, 71, 0.7);
        }
        .toggle-stream-btn {
            background-color: <?php echo $is_stream_live ? '#d9534f' : '#5cb85c'; ?>;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 5px;
            margin: 10px auto;
            display: block;
        }
        .toggle-stream-btn:hover {
            background-color: <?php echo $is_stream_live ? '#c9302c' : '#449d44'; ?>;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
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
                <a href="https://www.patreon.com/user?u=55698119" class="fa-brands fa-patreon" target="_blank"></a>
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
                            <li><a href="../forum/forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['forum_menu']; ?></a></li>
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
                            <li><a href="?lang=en">English (EN)</a></li>
                            <li><a href="?lang=bg">Български (BG)</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="content-board">
            <h1><?php echo $texts[$lang]['stream_title']; ?></h1>
            <div class="livestream-container">


                <div class="widget">
                    <div class="stream-chat-container" style="display: flex; flex-direction: row;">
                        <div class="stream-container" style="position: relative; border-top-left-radius: 8px; border-bottom-left-radius: 8px; width: 75%; flex-shrink: 0;" id="stream-container">
                            <?php if ($is_stream_live): ?>
                                <iframe 
                                    id="stream-iframe"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-top-left-radius: 8px; border-bottom-left-radius: 8px; border: 2px solid #744769; box-shadow: 0 0 10px rgba(116, 71, 105, 0.2);" 
                                    src="https://streamyard.com/embed/w3sn55y37p"
                                    
                                    frameborder="0" 
                                    allowfullscreen>
                                </iframe>
                            <?php else: ?>
                                <div id="offline-message" style="position: absolute; top: 0; left: 0; border-top-left-radius: 8px; border-bottom-left-radius: 8px; width: 100%; height: 100%; background: #333; color: #fff; display: flex; align-items: center; justify-content: center; text-align: center; border: 2px solid #744769;">
                                    <p><?php echo $texts[$lang]['offline_message']; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-section">
                            <div id="chat-container">
                                <div class="chat-header" style="flex-shrink: 0; display: flex; align-items: center; margin-bottom: 10px;">
                                    <div id="stlivechat21" style="margin: auto;">
                                        <?php if ($is_stream_live): ?>
                                        <div id="stlivechat21_online">
                                            <h3><span><?php echo $texts[$lang]['live_chat']; ?></span><span class="pulse-dot"></span></h3>
                                        </div>
                                        <?php else: ?>
                                        <div id="stlivechat21_offline">
                                            <h3><span><?php echo $texts[$lang]['chat_offline']; ?></span><span class="offline-dot"></span></h3>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div id="chat-messages">
                                    <?php if ($is_stream_live): ?>
                                        <div class="comment" id="welcome-message">
                                            <div class="comment-avatar">
                                                <img src="../images/default_profile.jpg" alt="Admin">
                                            </div>
                                            <div class="comment-content">
                                                <div class="comment-header">
                                                    <span class="comment-author"><?php echo htmlspecialchars($admin_info['userFirstName'] . ' ' . $admin_info['userLastName']); ?> <span class="admin-badge">Admin</span></span>
                                                </div>
                                                <div class="comment-text">
                                                    <?php echo $texts[$lang]['description'] . ' ' . ($isLoggedIn ? '' : $texts[$lang]['chat_instruction_logged_out']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php foreach ($initial_messages as $msg): ?>
                                            <div class="comment" data-id="<?php echo $msg['id']; ?>">
                                                <div class="comment-avatar">
                                                    <img src="<?php echo $msg['userProfilePhoto'] ?? '../images/default_profile.jpg'; ?>" alt="User">
                                                </div>
                                                <div class="comment-content">
                                                    <div class="comment-header">
                                                        <span class="comment-author"><?php echo htmlspecialchars($msg['userFirstName'] . ' ' . $msg['userLastName']) . ($msg['userType'] === 'admin' ? ' <span class="admin-badge">Admin</span>' : ''); ?></span>
                                                    </div>
                                                    <div class="comment-text">
                                                        <?php echo htmlspecialchars($msg['message']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="chat-disabled-message">
                                            <p><?php echo $texts[$lang]['chat_unavailable']; ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form id="chat-form" method="POST" action="" class="comment-form">
                                    <input type="hidden" name="csrf_token" id="csrf_token_chat" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <textarea id="chat-message" name="chat_message" placeholder="<?php echo $is_stream_live ? ($isLoggedIn ? $texts[$lang]['chat_placeholder_logged_in'] : $texts[$lang]['chat_placeholder_logged_out']) : $texts[$lang]['input_chat_unavailable']; ?>" required <?php echo $is_stream_live ? ($isLoggedIn ? '' : 'disabled class="chat-disabled"') : 'disabled class="chat-disabled"'; ?>></textarea>
                                    <button type="submit" id="chat-submit" 
                                        <?php echo $is_stream_live ? ($isLoggedIn ? '' : 'disabled class="chat-disabled"') : 'disabled class="chat-disabled"'; ?>>
                                        <?php echo $texts[$lang]['send']; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                    </div>
                    <?php if ($isAdmin): ?>
                    <form method="POST" action="" id="stream-toggle-form">
                        <input type="hidden" name="csrf_token" id="csrf_token_stream" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" name="toggle_stream" class="toggle-stream-btn" id="toggle-stream-btn">
                            <?php echo $is_stream_live ? $texts[$lang]['toggle_stream_off'] : $texts[$lang]['toggle_stream_on']; ?>
                        </button>
                    </form>
                <?php endif; ?>
                </div>

                <div class="widget">
                    <h3><?php echo $texts[$lang]['upcoming_streams']; ?></h3>
                    <ul class="event-list">
                        <?php
                        $events_sql = "
                            SELECT 
                                " . ($lang == 'bg' ? 'eventTitleBG' : 'eventTitle') . " AS eventTitle, 
                                " . ($lang == 'bg' ? 'eventDescriptionBG' : 'eventDescription') . " AS eventDescription, 
                                eventStartDate 
                            FROM EVENTS 
                            WHERE eventType = 'webinar'
                            AND eventStartDate > NOW()
                            ORDER BY eventStartDate ASC 
                            LIMIT 3";
                        try {
                            $events_stmt = $db->prepare($events_sql);
                            $events_stmt->execute();
                            $events_result = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($events_result) > 0) {
                                foreach ($events_result as $event) {
                                    echo '<li>';
                                    echo '<strong>' . htmlspecialchars($event['eventTitle'] ?? 'No Title') . '</strong><br>';
                                    echo '<small>' . date('M d, Y H:i', strtotime($event['eventStartDate'] ?? 'now')) . '</small><br>';
                                    echo '<p>' . htmlspecialchars($event['eventDescription'] ?? 'No Description') . '</p>';
                                    echo '</li>';
                                }
                            } else {
                                echo '<li>' . $texts[$lang]['no_streams'] . '</li>';
                            }
                        } catch (PDOException $e) {
                            error_log("Upcoming streams fetch error: " . $e->getMessage());
                            echo '<li>' . $texts[$lang]['no_streams'] . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <div id="overlay" class="overlay"></div>
        <div id="logoutPopup" class="popup">
            <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
            <button class="confirm" onclick="logout()"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideLogoutPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
    </main>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const contentBoard = document.querySelector('.content-board');
    contentBoard.style.height = 'auto';
    let actualHeight = contentBoard.scrollHeight + "px";
    contentBoard.style.height = "0";
    setTimeout(() => {
        contentBoard.style.height = actualHeight;
        contentBoard.classList.add("loaded");
    }, 100);

    const chatForm = document.getElementById('chat-form');
    const chatMessages = document.getElementById('chat-messages');
    const chatMessage = document.getElementById('chat-message');
    const chatSubmit = document.getElementById('chat-submit');
    const streamContainer = document.getElementById('stream-container');
    const toggleStreamBtn = document.getElementById('toggle-stream-btn');
    const welcomeMessage = document.getElementById('welcome-message');

    // Refresh CSRF token on page load
    function refreshCsrfToken() {
        fetch('get_csrf_token.php', {
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('csrf_token_chat').value = data.csrf_token;
            <?php if ($isAdmin): ?>
            document.getElementById('csrf_token_stream').value = data.csrf_token;
            <?php endif; ?>
        })
        .catch(error => console.error('Error refreshing CSRF token:', error));
    }
    refreshCsrfToken();

    // Handle form submission (only for logged-in users)
    <?php if ($isLoggedIn): ?>
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (chatMessage.value.trim() !== '' && !chatSubmit.disabled) {
            const formData = new FormData(chatForm);
            fetch('', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).then(response => {
                if (!response.ok) {
                    throw new Error('CSRF validation failed');
                }
                chatMessage.value = '';
                refreshCsrfToken(); // Refresh token after successful submission
            }).catch(error => console.error('Error submitting message:', error));
        }
    });
    <?php endif; ?>

    // SSE for real-time updates
    let lastId = <?php echo !empty($initial_messages) ? end($initial_messages)['id'] : 0; ?>;
    let isStreamLive = <?php echo $is_stream_live ? 'true' : 'false'; ?>;
    
    function connectSSE() {
        const source = new EventSource('chat_updates.php?lastId=' + lastId);

        source.addEventListener('message', function(event) {
            if (!isStreamLive) return;
            const data = JSON.parse(event.data);
            if (!document.querySelector(`.comment[data-id="${data.id}"]`)) {
                const newMessage = document.createElement('div');
                newMessage.className = 'comment';
                newMessage.dataset.id = data.id;
                newMessage.innerHTML = `
                    <div class="comment-avatar">
                        <img src="${data.photo}" alt="User">
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author">${data.author}</span>
                            ${data.adminBadge}
                        </div>
                        <div class="comment-text">
                            ${data.text}
                        </div>
                    </div>
                `;
                chatMessages.appendChild(newMessage);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                lastId = data.id;
            }
        });

        source.addEventListener('stream_state', function(event) {
            const data = JSON.parse(event.data);
            isStreamLive = data.state === 'on';

            if (isStreamLive) {
                streamContainer.innerHTML = `
                    <iframe 
                        id="stream-iframe"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-top-left-radius: 8px; border-bottom-left-radius: 8px; border: 2px solid #744769; box-shadow: 0 0 10px rgba(116, 71, 105, 0.2);" 
                        src="https://streamyard.com/embed/w3sn55y37p" 
                        frameborder="0" 
                        allowfullscreen>
                    </iframe>
                `;
                document.getElementById('stlivechat21').innerHTML = `
                    <div id="stlivechat21_online">
                        <h3><span><?php echo $texts[$lang]['live_chat']; ?></span><span class="pulse-dot"></span></h3>
                    </div>
                `;
                chatMessages.innerHTML = '';
                chatMessages.appendChild(welcomeMessage);
                chatMessage.placeholder = <?php echo $isLoggedIn ? "'{$texts[$lang]['chat_placeholder_logged_in']}'" : "'{$texts[$lang]['chat_placeholder_logged_out']}'"; ?>;
                <?php if ($isLoggedIn): ?>
                chatMessage.disabled = false;
                chatMessage.classList.remove('chat-disabled');
                chatSubmit.disabled = false;
                chatSubmit.classList.remove('chat-disabled');
                <?php endif; ?>
                if (toggleStreamBtn) {
                    toggleStreamBtn.style.backgroundColor = '#d9534f';
                    toggleStreamBtn.textContent = '<?php echo $texts[$lang]['toggle_stream_off']; ?>';
                    toggleStreamBtn.addEventListener('mouseover', () => toggleStreamBtn.style.backgroundColor = '#c9302c');
                    toggleStreamBtn.addEventListener('mouseout', () => toggleStreamBtn.style.backgroundColor = '#d9534f');
                }
            } else {
                streamContainer.innerHTML = `
                    <div id="offline-message" style="position: absolute; top: 0; left: 0; border-top-left-radius: 8px; border-bottom-left-radius: 8px; width: 100%; height: 100%; background: #333; color: #fff; display: flex; align-items: center; justify-content: center; text-align: center; border: 2px solid #744769;">
                        <p><?php echo $texts[$lang]['offline_message']; ?></p>
                    </div>
                `;
                document.getElementById('stlivechat21').innerHTML = `
                    <div id="stlivechat21_offline">
                        <h3><span><?php echo $texts[$lang]['chat_offline']; ?></span><span class="offline-dot"></span></h3>
                    </div>
                `;
                chatMessages.innerHTML = `<div class="chat-disabled-message"><p><?php echo $texts[$lang]['chat_unavailable']; ?></p></div>`;
                chatMessage.placeholder = '<?php echo $texts[$lang]['chat_unavailable']; ?>';
                chatMessage.disabled = true;
                chatMessage.classList.add('chat-disabled');
                chatSubmit.disabled = true;
                chatSubmit.classList.add('chat-disabled');
                if (toggleStreamBtn) {
                    toggleStreamBtn.style.backgroundColor = '#5cb85c';
                    toggleStreamBtn.textContent = '<?php echo $texts[$lang]['toggle_stream_on']; ?>';
                    toggleStreamBtn.addEventListener('mouseover', () => toggleStreamBtn.style.backgroundColor = '#449d44');
                    toggleStreamBtn.addEventListener('mouseout', () => toggleStreamBtn.style.backgroundColor = '#5cb85c');
                }
            }
        });

        source.onerror = function() {
            console.log('SSE connection lost, reconnecting...');
            source.close();
            setTimeout(connectSSE, 1000);
        };
    }

    connectSSE();
});

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
<script src="hamburger-menu.js"></script>
</body>
</html>