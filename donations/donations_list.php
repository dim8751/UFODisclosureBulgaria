<?php
require_once('../inc/db_connect.php');

// Start session
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

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
        'title' => 'Donors List - UFO Disclosure Bulgaria',
        'header' => 'OUR GENEROUS DONORS',
        'gratitude_note' => 'Your support means so much to us, and we are deeply grateful for your generosity!<br><br>
        Your donations are the foundation of everything we do. They provide the vital energy that helps us move forward, seek the truth, and share it with the world.
        As a team dedicated to uncovering hidden information, we rely on your support to cover essential expenses—from maintaining the platform to organizing impactful community events.
        Even with the tireless efforts of our volunteers, these costs cannot be met without your help.<br><br>
        Every dollar you contribute directly strengthens the effort to disclose UFO-related information and raise awareness. Your generosity truly makes a difference!
        As a token of appreciation, all non-anonymous donations will be proudly listed on this page (unless you prefer to remain anonymous).<br><br>
        Thank you for being part of this journey—your belief in our mission is the driving force behind success and progress!',
        'badge_bronze' => 'Bronze $10+',
        'badge_silver' => 'Silver $25+',
        'badge_gold' => 'Gold $50+',
        'no_donations' => 'No public donations have been made yet.',
        'table_profile' => 'Profile',
        'table_name' => 'Name',
        'table_email' => 'Email',
        'table_total' => 'Total Donated',
        'table_last' => 'Last Donation',
        'logout_popup' => 'Are you sure you want to log out?',
        'yes' => 'Yes',
        'cancel' => 'Cancel',
        'error_msg' => 'Database error occurred. Please try again later.',
        // Navigation
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'settings' => 'SETTINGS',
        'dashboard' => 'Dashboard',
        'our_team' => 'Our Team',
        'latest_videos' => 'Latest Videos',
        'live_stream' => 'Live Stream',
        'merchandise' => 'Merchandise',
        'forum' => 'Forum',
        'events' => 'Events',
        'report_sighting' => 'Report Sighting',
        'donors' => 'Donors List',
        'view_profile' => 'View Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations',
    ],
    'bg' => [
        'title' => 'Списък с Дарители - НЛО Разкритие България',
        'header' => 'НАШИТЕ ЩЕДРИ ДАРИТЕЛИ',
        'gratitude_note' => 'Вашата подкрепа означава изключително много за нас, и сме дълбоко благодарни за вашата щедрост!<br><br>
        Даренията ви са в основата на всичко, което правим. Те осигуряват жизнената енергия, която ни помага да продължим напред, да търсим истината и да я споделяме със света.
        Като екип, отдаден на разкриването на скритата информация, разчитаме на вашата подкрепа, за да покрием основни разходи – от поддръжката на платформата до организирането на значими обществени събития.
        Дори с неуморните усилия на нашите доброволци, тези разходи не могат да бъдат покрити без вашата помощ.<br><br>
        Всеки долар, който дарите, директно подпомага усилията за разкриване на информация за НЛО и повишаване на осведомеността. Вашата щедрост има реално значение!
        В знак на признателност, всички дарения, направени без анонимност, ще бъдат с гордост отбелязани на тази страница (освен ако предпочитате да останете анонимни).<br><br>
        Благодарим ви, че сте част от това пътуване – вашата вяра в мисията ни е движещата сила зад успеха и напредъка!',
        'badge_bronze' => 'Бронз $10+',
        'badge_silver' => 'Сребро $25+',
        'badge_gold' => 'Злато $50+',
        'no_donations' => 'Все още няма публични дарения.',
        'table_profile' => 'Профил',
        'table_name' => 'Име',
        'table_email' => 'Имейл',
        'table_total' => 'Общо Дарено',
        'table_last' => 'Последно Дарение',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        'yes' => 'Да',
        'cancel' => 'Отказ',
        'error_msg' => 'Възникна грешка в базата данни. Моля, опитайте отново по-късно.',
        // Navigation
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'settings' => 'НАСТРОЙКИ',
        'dashboard' => 'Начален Панел',
        'our_team' => 'Нашият Екип',
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Предаване на Живо',
        'merchandise' => 'Стоки',
        'forum' => 'Форум',
        'events' => 'Събития',
        'report_sighting' => 'Докладвай Наблюдение',
        'donors' => 'Списък с Дарители',
        'view_profile' => 'Преглед на Профила',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения',
    ]
];

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['userID']);

// Fetch only non-anonymous donations grouped by user with SUM of amounts
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
            AND d.isAnonymous = 0
        GROUP BY 
            u.userID
        ORDER BY 
            totalDonation DESC, lastDonationDate DESC
    ");
    $stmt->execute();
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = $texts[$lang]['error_msg'];
    error_log("Database Error: " . $e->getMessage());
}

// Function to determine badge color based on donation amount
function getBadgeDetails($amount) {
    $amount = $amount / 100;
    if ($amount >= 50) {
        return [
            'color' => '#FFD700', // Gold
            'border' => '#B8860B', // DarkGoldenRod
            'title' => 'Gold Donor' // This will be translated dynamically
        ];
    } elseif ($amount >= 25) {
        return [
            'color' => '#C0C0C0', // Silver
            'border' => '#808080', // Gray
            'title' => 'Silver Donor'
        ];
    } elseif ($amount >= 10) {
        return [
            'color' => '#CD7F32', // Bronze
            'border' => '#8B4513', // SaddleBrown
            'title' => 'Bronze Donor'
        ];
    } else {
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    
    <!-- JavaScript for handling custom popup -->
    <script type="text/javascript">
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
    </script>
    
    <title><?php echo $texts[$lang]['title']; ?></title>
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
        .donors-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .donors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
        }

        .donors-table th, .donors-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #444;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }
        .donors-table th {
            background-color: #744769;
            color: white;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            -webkit-text-stroke: 0.5px;
        }

        .donors-table tr {
            transition: transform 0.2s;
        }

        .donors-table tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .profile-photo-container {
            position: relative;
            width: 60px;
            height: 60px;
        }
        .profile-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid black;
        }
        .donor-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border-radius: 50%;
        }
        .badge-legend {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 20px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }
        .badge-item {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .badge-icon {
            margin-right: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .no-donations {
            text-align: center;
            padding: 20px;
            background-color: rgba(216, 71, 105, 0.1);
            border-radius: 5px;
            margin-top: 20px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            font-style: italic;
        }
        .gratitude-note {
            color: black;
            font-weight: 0.2rem;
            text-align: left;
            font-size: 1.1rem;
            z-index: 1;
            margin-bottom: 50px;
            border: 2px solid #744769;
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
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
                            <li><a href="../dashboard/index.php"><?php echo $texts[$lang]['dashboard']; ?></a></li>
                            <li><a href="../dashboard/our_team.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['our_team']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['content']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="../content/ytvideos.php"><?php echo $texts[$lang]['latest_videos']; ?></a></li>
                            <li><a href="../content/live_stream.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['live_stream']; ?></a></li>
                            <li><a href="../content/merch.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['merchandise']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['community']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="../forum/forum.php"><?php echo $texts[$lang]['forum']; ?></a></li>
                            <li><a href="../events/events_calendar.php"><?php echo $texts[$lang]['events']; ?></a></li>
                            <li><a href="../content/sightings_form.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['report_sighting']; ?></a></li>    
                            <li><a href="donations_list.php"><?php echo $texts[$lang]['donors']; ?></a></li>        
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['profile']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="../user_info/view_profile.php"><?php echo $texts[$lang]['view_profile']; ?></a></li>
                            <?php endif; ?>                       
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="#" onclick="showLogoutPopup()"><?php echo $texts[$lang]['logout']; ?></a></li>
                            <?php else : ?>
                                <li><a href="../login_register/login.php"><?php echo $texts[$lang]['login']; ?></a></li>
                            <?php endif; ?>
                            <?php if ($isLoggedIn) : ?>
                                <li><a href="../user_info/change_password.php"><?php echo $texts[$lang]['change_password']; ?></a></li>
                            <?php else : ?>
                                <li><a href="../login_register/register.php"><?php echo $texts[$lang]['registration']; ?></a></li>
                            <?php endif; ?>
                            <li><a href="donations.php"><?php echo $texts[$lang]['donations']; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $lang === 'bg' ? 'ЕЗИК' : 'LANGUAGE'; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="?lang=en">English (EN)</a></li>
                            <li><a href="?lang=bg">Български (BG)</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="content-board">
            <div class="donors-container">
                <h1><?php echo $texts[$lang]['header']; ?></h1>
                <h2 class="gratitude-note"><?php echo $texts[$lang]['gratitude_note']; ?></h2>
                <!-- Badge Legend -->
                <div class="badge-legend">
                    <div class="badge-item">
                        <div class="badge-icon" style="background-color: #CD7F32; border: 2px solid #8B4513;">
                            <i class="fas fa-star" style="color: #000;"></i>
                        </div>
                        <span><?php echo $texts[$lang]['badge_bronze']; ?></span>
                    </div>
                    <div class="badge-item">
                        <div class="badge-icon" style="background-color: #C0C0C0; border: 2px solid #808080;">
                            <i class="fas fa-star" style="color: #000;"></i>
                        </div>
                        <span><?php echo $texts[$lang]['badge_silver']; ?></span>
                    </div>
                    <div class="badge-item">
                        <div class="badge-icon" style="background-color: #FFD700; border: 2px solid #B8860B;">
                            <i class="fas fa-star" style="color: #000;"></i>
                        </div>
                        <span><?php echo $texts[$lang]['badge_gold']; ?></span>
                    </div>
                </div>
                
                <?php if (isset($error_msg) && !empty($error_msg)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>
                
                <?php if (empty($donations)): ?>
                    <div class="no-donations"><?php echo $texts[$lang]['no_donations']; ?></div>
                <?php else: ?>
                    <table class="donors-table">
                        <thead>
                            <tr>
                                <th><?php echo $texts[$lang]['table_profile']; ?></th>
                                <th><?php echo $texts[$lang]['table_name']; ?></th>
                                <th><?php echo $texts[$lang]['table_email']; ?></th>
                                <th><?php echo $texts[$lang]['table_total']; ?></th>
                                <th><?php echo $texts[$lang]['table_last']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <?php 
                                    $badgeDetails = getBadgeDetails($donation['totalDonation']);
                                    $badgeTitle = $badgeDetails ? ($lang === 'bg' ? str_replace(['Bronze', 'Silver', 'Gold'], ['Бронзов', 'Сребърен', 'Златен'], $badgeDetails['title']) : $badgeDetails['title']) : '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="profile-photo-container">
                                            <?php if (!empty($donation['userProfilePhoto'])): ?>
                                                <img src="<?php echo htmlspecialchars('../uploads/' . $donation['userProfilePhoto']); ?>" alt="Profile" class="profile-photo">
                                            <?php else: ?>
                                                <img src="../images/default_profile.png" alt="Default Profile" class="profile-photo">
                                            <?php endif; ?>                                    
                                            <?php if ($badgeDetails !== null): ?>
                                                <div class="donor-badge" 
                                                     title="<?php echo $badgeTitle; ?>"
                                                     style="background-color: <?php echo $badgeDetails['color']; ?>; 
                                                            border: 2px solid <?php echo $badgeDetails['border']; ?>;">
                                                    <i class="fas fa-star" style="color: #000;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($donation['userFirstName'] . ' ' . $donation['userLastName']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['userEmailAddress']); ?></td>
                                    <td>$<?php echo number_format($donation['totalDonation'] / 100, 2); ?></td>
                                    <td><?php echo date($lang === 'bg' ? 'j M Y' : 'M j, Y', strtotime($donation['lastDonationDate'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Custom Logout Confirmation Popup -->
    <div id="overlay" class="overlay"></div>
    <div id="logoutPopup" class="popup">
        <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
        <button class="confirm" onclick="logout()"><?php echo $texts[$lang]['yes']; ?></button><br>
        <button class="cancel" onclick="hideLogoutPopup()"><?php echo $texts[$lang]['cancel']; ?></button>
    </div>
    
    <script>
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