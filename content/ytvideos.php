<?php
session_start();
require_once 'youtube-functions.php';

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
        'title' => 'Latest Videos - UFO Disclosure Bulgaria',
        'new_videos' => 'LATEST VIDEOS',
        'newest' => 'Newest First',
        'oldest' => 'Oldest First',
        'load_more' => 'Load More Videos',
        'no_videos' => 'No videos found.',
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
        'language' => 'LANGUAGE',
        'logout_popup' => 'Are you sure you want to log out?',
        // Home menu
        'dashboard' => 'Dashboard',
        'our_team' => 'Our Team',
        // Content menu
        'latest_videos' => 'Latest Videos',
        'live_stream' => 'Live Stream',
        'merchandise' => 'Merchandise',
        // Community menu
        'forum' => 'Forum',
        'events' => 'Events',
        'report_sighting' => 'Report Sighting',
        'donors' => 'Donors List',
        // Profile menu
        'view_profile' => 'View Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations'
    ],
    'bg' => [
        'title' => 'Нови Видеоклипове - НЛО Разкритие България',
        'new_videos'=> 'НОВИ ВИДЕОКЛИПОВЕ',
        'newest' => 'Първо Най-Новите',
        'oldest' => 'Първо Най-Старите',
        'load_more' => 'Зареди Още Видеа',
        'no_videos' => 'Няма Намерени Видеа.',
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'language' => 'ЕЗИК',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        // Home menu
        'dashboard' => 'Начален Панел',
        'our_team' => 'Нашият Екип',
        // Content menu
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Излъчване на Живо',
        'merchandise' => 'Стоки',
        // Community menu
        'forum' => 'Форум',
        'events' => 'Събития',
        'report_sighting' => 'Докладвай Наблюдение',
        'donors' => 'Списък с Дарители',
        // Profile menu
        'view_profile' => 'Преглед на Профила',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения'
    ]
];

$channelId = 'UCdOO6baqmC14_VsrRm8d5rA';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$perPage = 15;

// Handle AJAX requests for loading more videos
if (isset($_GET['action']) && $_GET['action'] === 'loadMore') {
    header('Content-Type: application/json');
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $sortOrder = $_GET['sort'] ?? 'newest';
    
    $result = getVideosPage($channelId, $page, $perPage, $sortOrder);
    echo json_encode($result);
    exit;
}

// Get initial videos
$initialVideos = getVideosPage($channelId, 1, $perPage, $sortOrder);
$videos = $initialVideos['videos'];
$totalVideos = $initialVideos['totalVideos'];

// Calculate if there are more videos
$hasMore = count($videos) < $totalVideos;

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);

if (isset($_POST['Login'])) {
    header('Location: ../login_register/login.php');
    exit();
}

if (isset($_POST['Register'])) {
    header('Location: ../login_register/register.php');
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <!-- JavaScript for handling custom popup -->
    <script type="text/javascript">
        // Function to show the custom logout popup
        function showLogoutPopup() {
            // Show overlay
            document.getElementById('overlay').style.display = 'block';  
            // Show the popup
            document.getElementById('logoutPopup').style.display = 'block';  
        }

        // Function to hide the logout popup
        function hideLogoutPopup() {
            // Hide popup
            document.getElementById('logoutPopup').style.display = 'none';  
            // Hide overlay
            document.getElementById('overlay').style.display = 'none';  
        }

        // Function to log out the user by redirecting to logout.php
        function logout() {
            window.location.href = '../login_register/logout.php';  // Redirect to logout page
        }
    </script>
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
                            <li><a href="ytvideos.php"><?php echo $texts[$lang]['latest_videos']; ?></a></li>
                            <li><a href="live_stream.php"><?php echo $texts[$lang]['live_stream']; ?></a></li>  
                            <li><a href="merch.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['merchandise']; ?></a></li> 
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#"><?php echo $texts[$lang]['community']; ?> ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="../forum/forum.php"><?php echo $texts[$lang]['forum']; ?></a></li>
                            <li><a href="../events/events_calendar.php"><?php echo $texts[$lang]['events']; ?></a></li>
                            <li><a href="sightings_form.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['report_sighting']; ?></a></li>
                            <li><a href="../donations/donations_list.php"><?php echo $texts[$lang]['donors']; ?></a></li>        
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
                            <li><a href="../donations/donations.php"><?php echo $texts[$lang]['donations']; ?></a></li>
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
        
        <!-- Custom Logout Confirmation Popup -->
        <div id="overlay" class="overlay"></div>
        <div id="logoutPopup" class="popup">
            <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
            <button class="confirm" onclick="logout()"><?php echo $lang === 'bg' ? 'Да' : 'Yes'; ?></button><br>
            <button class="cancel" onclick="hideLogoutPopup()"><?php echo $lang === 'bg' ? 'Отказ' : 'Cancel'; ?></button>
        </div>
        
        <div class="content-board">
            <h1><?php echo $texts[$lang]['new_videos']; ?></h1>
            <form class="sort-form">
                <div class="video-controls">
                    <select name="sort" id="sort">
                        <option value="newest" <?php echo $sortOrder === 'newest' ? 'selected' : ''; ?>><?php echo $texts[$lang]['newest']; ?></option>
                        <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>><?php echo $texts[$lang]['oldest']; ?></option>
                    </select>
                </div>
            </form>
            <div class="video-grid">
                <?php if (empty($videos)): ?>
                    <div class="error-message"><?php echo $texts[$lang]['no_videos']; ?></div>
                <?php else: ?>
                    <?php foreach ($videos as $video): ?>
                        <div class="video-card">
                            <a href="https://www.youtube.com/watch?v=<?php echo htmlspecialchars($video['id']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p class="video-date"><?php echo date('F j, Y', $video['publishedAt']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="load-more-container">
                <button id="loadMoreBtn" class="load-more-btn" <?php echo ($totalVideos <= count($videos)) ? 'style="display: none;"' : ''; ?>>
                    <?php echo $texts[$lang]['load_more']; ?>
                </button>
            </div>

            <!-- Hidden inputs for JavaScript -->
            <input type="hidden" id="currentPage" value="1">
            <input type="hidden" id="currentSort" value="<?php echo htmlspecialchars($sortOrder); ?>">
            <input type="hidden" id="totalVideos" value="<?php echo htmlspecialchars($totalVideos); ?>">
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

// All functionality in a single DOMContentLoaded listener
document.addEventListener("DOMContentLoaded", function() {
    // Content board animation
    const contentBoard = document.querySelector(".content-board");
    contentBoard.style.height = "auto";
    let actualHeight = contentBoard.scrollHeight + "px";
    contentBoard.style.height = "0";
    setTimeout(() => {
        contentBoard.style.height = actualHeight;
        contentBoard.classList.add("loaded");
    }, 100);

    // Create hamburger menu element if it doesn't exist
    if (!document.querySelector('.hamburger-menu')) {
        const hamburgerMenu = document.createElement('div');
        hamburgerMenu.className = 'hamburger-menu';
        for (let i = 0; i < 3; i++) {
            const bar = document.createElement('div');
            bar.className = 'bar';
            hamburgerMenu.appendChild(bar);
        }
        document.querySelector('.ribbon').prepend(hamburgerMenu);
    }
    
    // Create overlay element if it doesn't exist
    if (!document.querySelector('.menu-overlay')) {
        const menuOverlay = document.createElement('div');
        menuOverlay.className = 'menu-overlay';
        document.body.appendChild(menuOverlay);
    }
    
    // Get navbar elements
    const navbar = document.querySelector('.navbar');
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const menuOverlay = document.querySelector('.menu-overlay');
    
    // Toggle menu function
    function toggleMenu() {
        navbar.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        hamburgerMenu.classList.toggle('active');
    }
    
    // Event listeners for hamburger menu
    hamburgerMenu.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);
    
    // Function to update dropdown arrows based on screen size
    function updateDropdownArrows() {
        const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
        if (window.innerWidth <= 768) {
            dropdownItems.forEach(item => {
                const mainLink = item.querySelector('a');
                if (mainLink && !mainLink.querySelector('.dropdown-arrow')) {
                    const originalText = mainLink.textContent.replace(' ▾', '');
                    mainLink.innerHTML = originalText + '<span class="dropdown-arrow"><i class="fa fa-chevron-right"></i></span>';
                }
            });
        } else {
            dropdownItems.forEach(item => {
                const mainLink = item.querySelector('a');
                if (mainLink && mainLink.querySelector('.dropdown-arrow')) {
                    const textContent = mainLink.childNodes[0].nodeValue.trim();
                    mainLink.innerHTML = textContent + ' ▾';
                }
            });
        }
    }
    
    // Handle dropdown menu clicks for mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (e.target.closest('.nav-item.dropdown > a')) {
                const link = e.target.closest('.nav-item.dropdown > a');
                const dropdownItem = link.parentNode;
                e.preventDefault();
                const wasActive = dropdownItem.classList.contains('active');
                document.querySelectorAll('.nav-item.dropdown.active').forEach(item => {
                    if (item !== dropdownItem) {
                        item.classList.remove('active');
                        const menu = item.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.maxHeight = '0px';
                            setTimeout(() => {
                                if (!item.classList.contains('active')) {
                                    menu.style.display = 'none';
                                }
                            }, 300);
                        }
                        const arrow = item.querySelector('.dropdown-arrow');
                        if (arrow) {
                            arrow.classList.remove('rotate');
                        }
                    }
                });
                dropdownItem.classList.toggle('active');
                const arrow = link.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.classList.toggle('rotate');
                }
                const dropdownMenu = dropdownItem.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    if (wasActive) {
                        dropdownMenu.style.maxHeight = '0px';
                        setTimeout(() => {
                            if (!dropdownItem.classList.contains('active')) {
                                dropdownMenu.style.display = 'none';
                            }
                        }, 300);
                    } else {
                        dropdownMenu.style.display = 'block';
                        void dropdownMenu.offsetHeight;
                        dropdownMenu.style.maxHeight = dropdownMenu.scrollHeight + 'px';
                    }
                }
            }
        }
    });
    
    // Initial setup for dropdown arrows
    updateDropdownArrows();
    
    // Update on resize
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

    // Sort change handler
    document.getElementById('sort').addEventListener('change', function() {
        document.querySelector('.video-grid').innerHTML = '';
        document.getElementById('currentPage').value = '1';
        let loadMoreBtn = document.getElementById('loadMoreBtn');
        if (!loadMoreBtn) {
            const container = document.createElement('div');
            container.className = 'load-more-container';
            container.innerHTML = '<button id="loadMoreBtn" class="load-more-btn"><?php echo $texts[$lang]['load_more']; ?></button>';
            document.querySelector('.video-grid').after(container);
            loadMoreBtn = container.querySelector('button');
            loadMoreBtn.addEventListener('click', loadMoreVideos);
        }
        loadMoreBtn.style.display = 'block';
        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;
        document.getElementById('currentSort').value = this.value;
        fetch(`ytvideos.php?action=loadMore&sort=${this.value}&page=1&lang=<?php echo $lang; ?>`)
            .then(response => response.json())
            .then(data => {
                const videoGrid = document.querySelector('.video-grid');
                videoGrid.innerHTML = '';
                data.videos.forEach(video => {
                    const videoCard = createVideoCard(video);
                    videoGrid.appendChild(videoCard);
                });
                document.getElementById('totalVideos').value = data.totalVideos;
                if (!data.hasMore) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'block';
                    loadMoreBtn.textContent = '<?php echo $texts[$lang]['load_more']; ?>';
                    loadMoreBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading videos:', error);
                loadMoreBtn.textContent = '<?php echo $texts[$lang]['load_more']; ?>';
                loadMoreBtn.disabled = false;
            });
    });

    // Load more videos functionality
    let loading = false;
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreVideos);
    }

    function loadMoreVideos() {
        if (loading) return;
        const currentPage = parseInt(document.getElementById('currentPage').value);
        const currentSort = document.getElementById('currentSort').value;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        loading = true;
        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;
        fetch(`ytvideos.php?action=loadMore&page=${currentPage + 1}&sort=${currentSort}&lang=<?php echo $lang; ?>`)
            .then(response => response.json())
            .then(data => {
                const videoGrid = document.querySelector('.video-grid');
                data.videos.forEach(video => {
                    const videoCard = createVideoCard(video);
                    videoGrid.appendChild(videoCard);
                });
                document.getElementById('currentPage').value = currentPage + 1;
                const totalLoaded = document.querySelectorAll('.video-card').length;
                const totalVideos = parseInt(document.getElementById('totalVideos').value);
                if (totalLoaded >= totalVideos) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'block';
                    loadMoreBtn.textContent = '<?php echo $texts[$lang]['load_more']; ?>';
                    loadMoreBtn.disabled = false;
                }
                loading = false;
            })
            .catch(error => {
                console.error('Error loading more videos:', error);
                loading = false;
                loadMoreBtn.textContent = '<?php echo $texts[$lang]['load_more']; ?>';
                loadMoreBtn.disabled = false;
            });
    }

    function createVideoCard(video) {
        const div = document.createElement('div');
        div.className = 'video-card';
        const date = new Date(video.publishedAt * 1000);
        const formattedDate = date.toLocaleDateString('<?php echo $lang === 'bg' ? 'bg-BG' : 'en-US'; ?>', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        div.innerHTML = `
            <a href="https://www.youtube.com/watch?v=${video.id}" target="_blank">
                <img src="${video.thumbnail}" alt="${video.title}">
                <h3>${video.title}</h3>
                <p class="video-date">${formattedDate}</p>
            </a>
        `;
        return div;
    }
});
</script>
    <!-- Custom CSS for the popup -->
    <style>
        body, .navbar, .video-card h3, .video-date, .load-more-btn, .popup, .sort-form select, .error-message {
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .load-more-btn {
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

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
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;    
        }

        .popup button.cancel:hover {
            background-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .video-controls {
            margin: 20px 0;
            text-align: right;
            padding: 10px;
        }

        .sort-form {
            display: inline-block;
            margin-left: 85%;
            margin-top: -5%;
        }

        .sort-form select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #744769;
            background-color: white;
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            max-height: 750px;
        }

        .error-message {
            text-align: center;
            padding: 20px;
            color: #744769;
            grid-column: 1 / -1;
        }

        .video-card {
            background: #f5f5f5;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .video-card a {
            text-decoration: none;
            color: inherit;
        }

        .video-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .video-card h3 {
            padding: 10px;
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .video-date {
            padding: 0 10px 10px;
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .load-more-container {
            text-align: center;
            padding: 20px;
        }

        .load-more-btn {
            background-color: #744769;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .load-more-btn:hover {
            background-color: #442538;
        }

        .load-more-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        #sort li :hover {
            background-color: #442538;
        }
    </style>
</body>
</html>