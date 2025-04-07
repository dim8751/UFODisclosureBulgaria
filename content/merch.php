<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);

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
        'title' => 'Merchandise - UFO Disclosure Bulgaria',
        'nav_home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'language' => 'LANGUAGE',
        'dashboard' => 'Dashboard',
        'our_mission' => 'Our Mission',
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
        'coming_soon' => 'Coming Soon!',
        'coming_soon_subtitle' => 'We\'re working on our merchandise collection. Check back later for UFO Disclosure Bulgaria branded items.'
    ],
    'bg' => [
        'title' => 'Стоки - НЛО Разкритие България',
        'nav_home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'language' => 'ЕЗИК',
        'dashboard' => 'Начален Панел',
        'our_mission' => 'Нашата Мисия',
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
        'coming_soon' => 'Скоро предстои!',
        'coming_soon_subtitle' => 'Работим върху нашата колекция от стоки. Проверете отново по-късно за брандирани артикули на UFO Disclosure Bulgaria.'
    ]
];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

    <!-- Custom CSS for the popup and coming soon message -->
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
        .coming-soon-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 770px;
            padding: 40px;
            font-family: 'Jura', sans-serif;
        }
        .coming-soon-message {
            font-size: 3rem;
            font-weight: 700;
            color: #744769;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
            margin-bottom: 20px;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .coming-soon-subtitle {
            font-size: 1.2rem;
            color: #5a3651;
            max-width: 600px;
            text-align: center;
            margin-top: 20px;

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
                    <a href="#"><?php echo $texts[$lang]['nav_home']; ?> ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="../dashboard/index.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['dashboard']; ?></a></li>
                        <li><a href="../dashboard/our_team.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['our_team']; ?></a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#"><?php echo $texts[$lang]['content']; ?> ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="ytvideos.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['latest_videos']; ?></a></li>
                        <li><a href="live_stream.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['live_stream']; ?></a></li>
                        <li><a href="merch.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['merchandise']; ?></a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#"><?php echo $texts[$lang]['community']; ?> ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="../forum/forum.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['forum_menu']; ?></a></li>
                        <li><a href="../events/events_calendar.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['events']; ?></a></li>
                        <li><a href="sightings_form.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['report_sighting']; ?></a></li>
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
        <div class="coming-soon-container">
            <div class="coming-soon-message"><?php echo $texts[$lang]['coming_soon']; ?></div>
            <div class="coming-soon-subtitle"><?php echo $texts[$lang]['coming_soon_subtitle']; ?></div>
        </div>
    </div>
    </main>

    <!-- Custom Logout Confirmation Popup -->
    <div id="overlay" class="overlay"></div>
    <div id="logoutPopup" class="popup">
        <h2><?php echo $lang === 'en' ? 'Are you sure you want to log out?' : 'Сигурни ли сте, че искате да излезете?'; ?></h2>
        <button class="confirm" onclick="logout()"><?php echo $lang === 'en' ? 'Yes' : 'Да'; ?></button><br>
        <button class="cancel" onclick="hideLogoutPopup()"><?php echo $lang === 'en' ? 'Cancel' : 'Отказ'; ?></button>
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