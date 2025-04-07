<?php
session_start();
require_once '../inc/db_connect.php';

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
        'title' => 'Dashboard - UFO Disclosure Bulgaria',
        'welcome' => 'Welcome, ',
        'dashboard' => 'Dashboard',
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'settings' => 'SETTINGS',
        'language' => 'LANGUAGE',
        'latest_topics' => 'Latest Forum Topics',
        'upcoming_events' => 'Upcoming Events',
        'recent_sightings_map' => 'Recent UFO Sightings Map',
        'no_topics' => 'No recent topics available.',
        'no_events' => 'No upcoming events scheduled.',
        'no_sightings' => 'No recent UFO sightings available.',
        'logout_popup' => 'Are you sure you want to log out?',
        'yes' => 'Yes',
        'cancel' => 'Cancel',
        'view_all' => 'View All',
        'join_date' => 'Joined',
        'forum_posts' => 'Forum Posts',
        'events_attended' => 'Events Attended',
        // Menu items
        'our_team' => 'Our Team',
        'latest_videos' => 'Latest Videos',
        'live_stream' => 'Live Stream',
        'merchandise' => 'Merchandise',
        'forum_menu' => 'Forum',
        'events' => 'Events',
        'report_sighting' => 'Report Sighting',
        'donors' => 'Donors List',
        'view_profile' => 'View Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations',
        'sighting_type' => 'Sighting Type',
        'ufos_aliens' => 'UFOs & Aliens',
        'paranormal_ghosts' => 'Paranormal & Ghosts',
        'cryptids_creatures' => 'Cryptids & Creatures',
        'unexplained_phenomena' => 'Unexplained Phenomena',
        'strange_disappearances' => 'Strange Disappearances',
        'other' => 'Other',
    ],
    'bg' => [
        'title' => 'Начален Панел - НЛО Разкритие България',
        'welcome' => 'Добре дошли, ',
        'dashboard' => 'Начален Панел',
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'settings' => 'НАСТРОЙКИ',
        'language' => 'ЕЗИК',
        'latest_topics' => 'Последни Теми във Форума',
        'upcoming_events' => 'Предстоящи Събития',
        'recent_sightings_map' => 'Карта на Скорошни Наблюдения на НЛО',
        'no_topics' => 'Няма скорошни теми.',
        'no_events' => 'Няма насрочени предстоящи събития.',
        'no_sightings' => 'Няма скорошни наблюдения на НЛО.',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        'yes' => 'Да',
        'cancel' => 'Отказ',
        'view_all' => 'Виж Всички',
        'join_date' => 'Присъединил се',
        'forum_posts' => 'Публикации във Форума',
        'events_attended' => 'Посетени Събития',
        // Menu items
        'our_team' => 'Нашият Екип',
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Излъчване на Живо',
        'merchandise' => 'Стоки',
        'forum_menu' => 'Форум',
        'events' => 'Събития',
        'report_sighting' => 'Докладвай Наблюдение',
        'donors' => 'Списък с Дарители',
        'view_profile' => 'Преглед на Профила',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения',
        'sighting_type' => 'Тип Наблюдение',
        'ufos_aliens' => 'НЛО & Извънземни',
        'paranormal_ghosts' => 'Паранормални Явления & Духове',
        'cryptids_creatures' => 'Криптиди & Същества',
        'unexplained_phenomena' => 'Необясними Явления',
        'strange_disappearances' => 'Странни Изчезвания',
        'other' => 'Друго',
    ]
];

// Fetch user data if logged in
$userData = null;
if ($isLoggedIn) {
    $query = "SELECT userFirstName, userLastName, created_at, 
                     (SELECT COUNT(*) FROM FORUM_TOPICS WHERE userID = :userID) as forum_posts,
                     (SELECT COUNT(*) FROM EVENT_REGISTRATIONS WHERE userID = :userID AND registrationStatus = 'confirmed') as events_attended
              FROM USERS WHERE userID = :userID";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch latest forum topics (limit to 5)
$stmt = $db->prepare("
    SELECT ft.topicID, ft.topicTitle, fct.categoryName, u.userFirstName, u.userLastName, ft.created_at
    FROM FORUM_TOPICS ft
    JOIN FORUM_CATEGORIES fc ON ft.categoryID = fc.categoryID
    JOIN FORUM_CATEGORY_TRANSLATIONS fct ON fc.categoryID = fct.categoryID AND fct.languageCode = :lang
    JOIN USERS u ON ft.userID = u.userID
    ORDER BY ft.isPinned DESC, ft.created_at DESC
    LIMIT 5
");
$stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
$stmt->execute();
$latestTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events (limit to 3)
$stmt = $db->prepare("
    SELECT eventID, " . ($lang == 'bg' ? 'eventTitleBG' : 'eventTitle') . " as eventTitle,
           eventStartDate, eventEndDate, eventType
    FROM EVENTS
    WHERE eventStartDate >= NOW()
    ORDER BY eventStartDate ASC
    LIMIT 3
");
$stmt->execute();
$upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
        .dashboard-container {
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
            -webkit-text-stroke: 1px;
        }
        .topic-list, .event-list {
            list-style: none;
            padding: 0;
        }
        .topic-list li, .event-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .topic-list li:last-child, .event-list li:last-child {
            border-bottom: none;
        }
        .topic-list a, .event-list a {
            color: #744769;
            text-decoration: none;
            font-weight: 700;
        }
        .topic-list a:hover, .event-list a:hover {
            text-decoration: underline;
        }
        .view-all {
            display: block;
            text-align: right;
            margin-top: 10px;
        }
        .view-all a {
            color: #744769;
            text-decoration: none;
            font-weight: 700;
        }
        .view-all a:hover {
            text-decoration: underline;
        }
        .user-stats {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .leaflet-popup-content-wrapper {
    max-width: 500px; 
    font-family: 'Jura', sans-serif;
}

.leaflet-popup-content {
    word-wrap: break-word; 
    overflow-wrap: break-word;
    max-height: 300px; 
    overflow-y: auto; 
}

.leaflet-popup-content p {
    margin: 5px 0;
    white-space: normal; /* Ensure text wraps */
}

/* Legend Styling */
.legend {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.9);
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: 'Jura', sans-serif;
    z-index: 1000;
}

.legend-item {
    align-items: center;
    text-align: left;
    margin: 5px 0;
    white-space: nowrap;
}

.legend-color {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-right: 8px;
    display: inline-block;
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
                            <li><a href="index.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['dashboard']; ?></a></li>
                            <li><a href="our_team.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['our_team']; ?></a></li>
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
            <div class="dashboard-container">
                <?php if ($isLoggedIn && $userData) : ?>
                    <div class="welcome-section">
                        <h2><?php echo $texts[$lang]['welcome'] . htmlspecialchars($userData['userFirstName'] . ' ' . $userData['userLastName']); ?></h2>
                        <div class="user-stats">
                            <span><?php echo $texts[$lang]['join_date'] . ': ' . date('M d, Y', strtotime($userData['created_at'])); ?></span> | 
                            <span><?php echo $texts[$lang]['forum_posts'] . ': ' . $userData['forum_posts']; ?></span> | 
                            <span><?php echo $texts[$lang]['events_attended'] . ': ' . $userData['events_attended']; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="widget">
                    <h3><?php echo $texts[$lang]['recent_sightings_map']; ?></h3>
                    <div id="sightings-map" style="height: 600px; width: 100%; border-radius: 4px; z-index: 1;"></div>
                </div>

                <div class="widget">
                    <h3><?php echo $texts[$lang]['latest_topics']; ?></h3>
                    <ul class="topic-list">
                        <?php if (empty($latestTopics)) : ?>
                            <li><?php echo $texts[$lang]['no_topics']; ?></li>
                        <?php else : ?>
                            <?php foreach ($latestTopics as $topic) : ?>
                                <li>
                                    <a href="../forum/forum.php?topic=<?php echo $topic['topicID']; ?>&lang=<?php echo $lang; ?>">
                                        <?php echo htmlspecialchars($topic['topicTitle']); ?>
                                    </a>
                                    <br>
                                    <small><?php echo $topic['categoryName'] . ' | ' . $topic['userFirstName'] . ' ' . $topic['userLastName'] . ' | ' . date('M d, Y', strtotime($topic['created_at'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="view-all">
                        <a href="../forum/forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['view_all']; ?></a>
                    </div>
                </div>

                <div class="widget">
                    <h3><?php echo $texts[$lang]['upcoming_events']; ?></h3>
                    <ul class="event-list">
                        <?php if (empty($upcomingEvents)) : ?>
                            <li><?php echo $texts[$lang]['no_events']; ?></li>
                        <?php else : ?>
                            <?php foreach ($upcomingEvents as $event) : ?>
                                <li>
                                    <a href="../events/events_calendar.php?lang=<?php echo $lang; ?>">
                                        <?php echo htmlspecialchars($event['eventTitle']); ?>
                                    </a>
                                    <br>
                                    <small><?php echo date('M d, Y H:i', strtotime($event['eventStartDate'])) . ' - ' . date('H:i', strtotime($event['eventEndDate'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="view-all">
                        <a href="../events/events_calendar.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['view_all']; ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Logout Confirmation Popup -->
        <div id="overlay" class="overlay"></div>
        <div id="logoutPopup" class="popup">
            <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
            <button class="confirm" onclick="logout()"><?php echo $texts[$lang]['yes']; ?></button><br>
            <button class="cancel" onclick="hideLogoutPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
        </div>
    </main>
    
    <script>
// Logout popup functions
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

// Content board animation
document.addEventListener("DOMContentLoaded", function() {
    const contentBoard = document.querySelector(".content-board");
    contentBoard.style.height = "auto";
    let actualHeight = contentBoard.scrollHeight + "px";
    contentBoard.style.height = "0";
    setTimeout(() => {
        contentBoard.style.height = actualHeight;
        contentBoard.classList.add("loaded");
    }, 100);
});

document.addEventListener("DOMContentLoaded", function() {
    // Define marker colors for each sighting type (same as sighting report page)
    const markerColors = {
        'ufos_aliens': '#4CAF50',          
        'paranormal_ghosts': '#0288D1',    
        'cryptids_creatures': '#8D5524',   
        'unexplained_phenomena': '#FFD700', 
        'strange_disappearances': '#D32F2F', 
        'other': '#808080'                 
    };

    // Create map centered on Bulgaria
    const map = L.map('sightings-map').setView([42.7339, 25.4858], 7);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Pass admin status to JavaScript
    const isAdmin = <?php echo json_encode($isAdmin); ?>;
    
    // Function to create a colored marker (same as sighting report page)
    function createColoredMarker(lat, lng, color) {
        return L.circleMarker([lat, lng], {
            radius: 8,
            fillColor: color,
            color: color,
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
        });
    }

    // Function to load sightings
    function loadSightings() {
        fetch(`../api/get_sightings.php?lang=<?php echo $lang; ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('sightings-map').innerHTML = `<div class="no-data">${data.error}</div>`;
                return;
            }
            if (!Array.isArray(data) || data.length === 0) {
                document.getElementById('sightings-map').innerHTML = '<div class="no-data"><?php echo $texts[$lang]['no_sightings']; ?></div>';
                return;
            }
            
            data.forEach(sighting => {
                // Use the sighting type to determine marker color
                const markerColor = markerColors[sighting.sightingType] || '#808080';
                const marker = createColoredMarker(sighting.latitude, sighting.longitude, markerColor).addTo(map);
                
                const sightingDate = new Date(sighting.sightingDate);
                const formattedDate = sightingDate.toLocaleDateString('<?php echo $lang; ?>', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Build media content for popup
                let mediaContent = '';
                if (sighting.media && Array.isArray(sighting.media) && sighting.media.length > 0) {
                    mediaContent = '<div style="margin-top: 10px;">';
                    sighting.media.forEach((mediaPath, index) => {
                        const fileExt = mediaPath.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                        const isVideo = ['mp4', 'webm', 'ogg'].includes(fileExt);

                        if (isImage) {
                            mediaContent += `
                                <a href="${mediaPath}" target="_blank" style="display: block; margin: 5px 0;">
                                    <img src="${mediaPath}" alt="Sighting Image ${index + 1}" style="max-width: 100%; height: auto; border-radius: 4px;" loading="lazy">
                                </a>`;
                        } else if (isVideo) {
                            mediaContent += `
                                <video controls style="width: auto; height: 300px; margin: auto; border-radius: 4px;">
                                    <source src="${mediaPath}" type="video/${fileExt}">
                                    Your browser does not support the video tag.
                                </video>`;
                        } else {
                            mediaContent += `
                                <a href="${mediaPath}" target="_blank" style="display: block; margin: 5px 0; color: #744769; text-decoration: none;">
                                    View Media ${index + 1}
                                </a>`;
                        }
                    });
                    mediaContent += '</div>';
                }

                // Add delete button for admins with a unique ID
                let deleteButton = '';
                if (isAdmin) {
                    deleteButton = `
                        <button id="delete-sighting-${sighting.sightingID}" style="margin-top: 10px; background-color: #744769; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            Delete
                        </button>`;
                }

                const popupContent = `
                    <strong>${sighting.title}</strong><br>
                    <em>${formattedDate}</em><br>
                    <p>${sighting.description}</p>
                    <small>Reported by: ${sighting.userFirstName} ${sighting.userLastName}</small>
                    ${mediaContent}
                    ${deleteButton}
                `;

                marker.bindPopup(popupContent);

                // Attach delete event listener when popup opens
                if (isAdmin) {
                    marker.on('popupopen', function() {
                        const deleteBtn = document.getElementById(`delete-sighting-${sighting.sightingID}`);
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', function() {
                                deleteSighting(sighting.sightingID, marker);
                            });
                        }
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error loading sightings data:', error);
            document.getElementById('sightings-map').innerHTML = '<div class="no-data">Error loading sighting data.</div>';
        });
    }

    // Function to delete a sighting
    function deleteSighting(sightingID, marker) {
        if (confirm('Are you sure you want to delete this sighting?')) {
            fetch('../api/delete_sighting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ sightingID: sightingID })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    map.removeLayer(marker);
                    alert('Sighting deleted successfully.');
                } else {
                    alert('Error deleting sighting: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error deleting sighting:', error);
                alert('Error deleting sighting.');
            });
        }
    }

    // Add legend (same as sighting report page)
    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML = '<h4>' + '<?php echo $texts[$lang]["sighting_type"]; ?>' + '</h4>';
        
        const types = {
            'ufos_aliens': '<?php echo $texts[$lang]["ufos_aliens"]; ?>',
            'paranormal_ghosts': '<?php echo $texts[$lang]["paranormal_ghosts"]; ?>',
            'cryptids_creatures': '<?php echo $texts[$lang]["cryptids_creatures"]; ?>',
            'unexplained_phenomena': '<?php echo $texts[$lang]["unexplained_phenomena"]; ?>',
            'strange_disappearances': '<?php echo $texts[$lang]["strange_disappearances"]; ?>',
            'other': '<?php echo $texts[$lang]["other"]; ?>'
        };

        for (const [type, label] of Object.entries(types)) {
            div.innerHTML += `
                <div class="legend-item">
                    <span class="legend-color" style="background-color: ${markerColors[type]}"></span>
                    ${label}
                </div>
            `;
        }
        return div;
    };
    legend.addTo(map);

    // Load sightings on page load
    loadSightings();
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
    
    // Initial setup
    updateDropdownArrows();
    
    // Update on resize
    window.addEventListener('resize', function() {
        updateDropdownArrows();
        
        // Reset menu state when switching to desktop
        if (window.innerWidth > 768) {
            navbar.classList.remove('active');
            menuOverlay.classList.remove('active');
            hamburgerMenu.classList.remove('active');
            
            // Reset all dropdowns
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