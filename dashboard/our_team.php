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
        'title' => 'Our Team - UFO Disclosure Bulgaria',
        'nav_home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'language' => 'LANGUAGE',
        'dashboard' => 'Dashboard',
        'our-team' => 'Our Team',
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
        'our_team' => 'OUR TEAM',
        'founder' => 'Founder & Lead Investigator',
        'paranormal_expert' => 'Paranormal Expert & Researcher',
        'psychic_specialist' => 'Psychic Channel and Remote Viewing Specialist',
        'mission_title' => 'Our Mission',
        'what_we_do' => 'What We Do',
        'join_us' => 'Join Our Community',
        'field_investigations' => 'Field Investigations',
        'documentary_production' => 'Documentary Production',
        'community_building' => 'Community Building',
        'join_forum' => 'Join Our Forum',
        'attend_events' => 'Attend Our Events',
        'support_work' => 'Support Our Work',
        'ivan_name' => 'Ivan Ivanov',
        'lyubomir_name' => 'Lyubomir Dimitrov',
        'beverli_name' => 'Beverli Rhodes'
    ],
    'bg' => [
        'title' => 'Нашият Екип - НЛО Разкритие България',
        'nav_home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'language' => 'ЕЗИК',
        'dashboard' => 'Начален Панел',
        'our-team' => 'Нашият Екип',
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
        'our_team' => 'НАШИЯТ ЕКИП',
        'founder' => 'Основател & Главен Изследовател',
        'paranormal_expert' => 'Експерт по Паранормални Явления & Изследовател',
        'psychic_specialist' => 'Ясновидски Проводник & Специалист по Дистанционно Виждане',
        'mission_title' => 'Нашата Мисия',
        'what_we_do' => 'Какво Правим',
        'join_us' => 'Присъединете се към Нашата Общност',
        'field_investigations' => 'Теренни Изследвания',
        'documentary_production' => 'Продукция на Документални Филми',
        'community_building' => 'Изграждане на Общност',
        'join_forum' => 'Присъединете се към Форума',
        'attend_events' => 'Посетете Нашите Събития',
        'support_work' => 'Подкрепете Нашата Работа',
        'ivan_name' => 'Иван Иванов',
        'lyubomir_name' => 'Любомир Димитров',
        'beverli_name' => 'Бевърли Роудс'
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

    <!-- Custom CSS for the popup -->
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
                    <a href="#"><?php echo $texts[$lang]['nav_home']; ?> ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['dashboard']; ?></a></li>
                        <li><a href="our_team.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['our-team']; ?></a></li>
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
    <h1><?php echo $texts[$lang]['our_team']; ?></h1>
        <div class="about-container">         
            <div class="founder-section">
                <div class="founder-image">
                    <img src="../images/ivan.jpg" alt="<?php echo $texts[$lang]['ivan_name']; ?> - Founder of UFO Disclosure Bulgaria" class="founder-photo">
                </div>
                <div class="founder-bio">
                    <h2><?php echo $texts[$lang]['ivan_name']; ?></h2>
                    <h3><?php echo $texts[$lang]['founder']; ?></h3>
                    <p><?php echo $lang === 'en' ? 
                        'Ivan Ivanov became a UFO and Paranormal field investigator by 1988. He was inspired after watching Erich von Daniken movie Memory from The Future.' : 
                        'Иван Иванов става изследовател в областта на НЛО и паранормалното през 1988 г. Той е вдъхновен след като гледа филма на Ерих фон Деникен "Спомени от бъдещето".'; ?></p>
                    <p><?php echo $lang === 'en' ? 
                        'Ivan Ivanov is a founder of Facebook page UFO Disclosure Bulgaria and YouTube channel UFO Disclosure Bulgaria.' : 
                        'Иван Иванов е основател на Фейсбук страницата UFO Disclosure Bulgaria и YouTube канала UFO Disclosure Bulgaria.'; ?></p>
                    <p><?php echo $lang === 'en' ? 
                        'Ivan Ivanov is author and Director of dozen movies and many interviews with world famous researchers and scientists of UFO Phenomena and The Paranormal activities.' : 
                        'Иван Иванов е автор и режисьор на десетки филми и множество интервюта със световноизвестни изследователи и учени в областта на НЛО феномена и паранормалните явления.'; ?></p>
                    <p><?php echo $lang === 'en' ? 
                        'Mr. Clifford Stone was his mentor and one of his best friends on the UFO field.' : 
                        'Г-н Клифорд Стоун беше негов ментор и един от най-добрите му приятели в областта на НЛО.'; ?></p>
                </div>
            </div>
            <div class="founder-section">
                <div class="founder-image">
                    <img src="../images/lyubomir.jpg" alt="<?php echo $texts[$lang]['lyubomir_name']; ?>" class="founder-photo">
                </div>
                <div class="founder-bio">
                    <h2><?php echo $texts[$lang]['lyubomir_name']; ?></h2>
                    <h3><?php echo $texts[$lang]['paranormal_expert']; ?></h3>
                    <p><?php echo $lang === 'en' ? 
                        'From a young age, Lyubomir Dimitrov encountered strange and unexplained phenomena that ignited a deep interest in the paranormal and extraterrestrial.' : 
                        'Още от ранна възраст Любомир Димитров се сблъсква със странни и необясними явления, които разпалват дълбок интерес към паранормалното и извънземните.'; ?></p>
                    <p><?php echo $lang === 'en' ? 
                        'Determined to explore these mysteries, he teamed up with UFO Disclosure Bulgaria, bringing his technical talents to the mission.' : 
                        'Решен да изследва тези мистерии, той се присъединява към НЛО Разкритие България, внасяйки своите технически умения в мисията.'; ?></p>
                    <p><?php echo $lang === 'en' ? 
                        'Lyubomir built the group’s website from the ground up and lends his skills to translating UFO Disclosure Bulgaria’s YouTube videos, helping share Bulgaria’s UFO research with the world.' : 
                        'Любомир изгражда уебсайта на групата от нулата и използва уменията си за превод на видеоклиповете на UFO Disclosure Bulgaria в YouTube, като по този начин помага за споделянето на българските изследвания на НЛО със света.'; ?></p>
                </div>
            </div>
            <div class="founder-section">
                <div class="founder-image">
                    <img src="../images/Beverli.jpg" alt="<?php echo $texts[$lang]['beverli_name']; ?>" class="founder-photo">
                </div>
                <div class="founder-bio">
                    <h2><?php echo $texts[$lang]['beverli_name']; ?></h2>
                    <h3><?php echo $texts[$lang]['psychic_specialist']; ?></h3>
                    <p><?php echo $lang === 'en' ? 
                        'Beverli Rhodes is an acclaimed psychic channel, remote viewing specialist, and spiritual teacher with over 30 years of experience in metaphysical exploration.' : 
                        'Бевърли Роудс е признат ясновидски проводник, специалист по дистанционно виждане и духовен учител с над 30 години опит в метафизичните изследвания.'; ?></p>
                <p><?php echo $lang === 'en' ? 
                        'As the founder of "Awakening Academy", she offers transformative courses and private sessions, guiding individuals to unlock their intuitive abilities and connect with higher consciousness.' : 
                        'Като основател на "Академия Пробуждане", тя предлага трансформиращи курсове и частни сесии, насочвайки хората към отключване на техните интуитивни способности и свързване с по-високо съзнание.'; ?></p>
        <p><?php echo $lang === 'en' ? 
                        'Beverli brings her expertise to UFO Disclosure Bulgaria, enhancing the team’s efforts with her psychic insights and deep understanding of extraterrestrial communication.' : 
                        'Бевърли внася своя опит в НЛО Разкритие България, обогатявайки усилията на екипа с нейните ясновидски прозрения и дълбоко разбиране на извънземните комуникации.'; ?></p>
                </div>
            </div>
            <div class="mission-section">
                <h2><?php echo $texts[$lang]['mission_title']; ?></h2>
                <p><?php echo $lang === 'en' ? 
                    'At UFO Disclosure Bulgaria, we are dedicated to investigating and documenting unexplained aerial phenomena across Bulgaria and beyond. Our mission is to provide credible information, scientific analysis, and thought-provoking content that encourages open-minded inquiry into the UFO phenomenon.' : 
                    'В НЛО Разкритие България сме посветени на разследването и документирането на необясними въздушни феномени в България и извън нея. Нашата мисия е да предоставяме достоверна информация, научен анализ и съдържание, което провокира размисъл и насърчава откритото изследване на феномена НЛО.'; ?></p>
                <p><?php echo $lang === 'en' ? 
                    'We believe in approaching this field with scientific rigor while remaining respectful of witness testimony and the diverse perspectives within the UFO research community.' : 
                    'Вярваме в подхода към тази област с научна строгост, като същевременно оставаме уважителни към свидетелските показания и разнообразните гледни точки в общността на изследователите на НЛО.'; ?></p>
            </div>
            <div class="what-we-do">
                <h2><?php echo $texts[$lang]['what_we_do']; ?></h2>
                <div class="activities">
                    <div class="activity-card">
                        <i class="fas fa-search"></i>
                        <h3><?php echo $texts[$lang]['field_investigations']; ?></h3>
                        <p><?php echo $lang === 'en' ? 
                            'We conduct field investigations of reported UFO sightings and paranormal phenomena both in Bulgaria and around the world, gathering evidence and interviewing witnesses.' : 
                            'Провеждаме теренни разследвания на докладвани наблюдения на НЛО и паранормални явления както в България, така и по света, събирайки доказателства и интервюирайки свидетели.'; ?></p>
                    </div>
                    <div class="activity-card">
                        <i class="fas fa-video"></i>
                        <h3><?php echo $texts[$lang]['documentary_production']; ?></h3>
                        <p><?php echo $lang === 'en' ? 
                            'We create documentaries and interview series featuring researchers, scientists, and witnesses from around the world.' : 
                            'Създаваме документални филми и поредици с интервюта с изследователи, учени и свидетели от цял свят'; ?></p>
                    </div>
                    <div class="activity-card">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $texts[$lang]['community_building']; ?></h3>
                        <p><?php echo $lang === 'en' ? 
                            'We foster a community of like-minded individuals through our forum, social media channels, and regular events.' : 
                            'Насърчаваме общност от съмишленици чрез нашия форум, канали в социалните мрежи и редовни събития.'; ?></p>
                    </div>
                </div>
            </div>
            <div class="join-us">
                <h2><?php echo $texts[$lang]['join_us']; ?></h2>
                <p><?php echo $lang === 'en' ? 
                    'Whether you’re a seasoned researcher, have had your own unexplained experience, or are simply curious about the unknown, we welcome you to join our growing community.' : 
                    'Независимо дали сте опитен изследовател, имали сте собствено необяснимо преживяване или просто сте любопитни за неизвестното, каним ви да се присъедините към нашата растяща общност.'; ?></p>
                <div class="join-buttons">
                    <a href="../forum/forum.php?lang=<?php echo $lang; ?>" class="join-button"><?php echo $texts[$lang]['join_forum']; ?></a>
                    <a href="../events/events_calendar.php?lang=<?php echo $lang; ?>" class="join-button"><?php echo $texts[$lang]['attend_events']; ?></a>
                    <a href="../donations/donations.php?lang=<?php echo $lang; ?>" class="join-button"><?php echo $texts[$lang]['support_work']; ?></a>
                </div>
            </div>
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