<?php
session_start();
require_once '../inc/db_connect.php';

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../login_register/login.php?lang=' . urlencode($lang) . '&from=report_sighting');
    exit;
}

$userID = $_SESSION['user']['userID'];

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

$texts = [
    'en' => [
        'title' => 'Report Sighting - UFO Disclosure Bulgaria',
        'form_title' => 'REPORT SIGHTING',
        'date_time' => 'Date and Time of Sighting',
        'click_map' => 'Click on the map to set location',
        'location' => 'Location',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'sighting_title' => 'Sighting Title',
        'sighting_description' => 'Description',
        'describe_details' => 'Describe what you saw in detail',
        'sighting_type' => 'Sighting Type',
        'ufos_aliens' => 'UFOs & Aliens',
        'paranormal_ghosts' => 'Paranormal & Ghosts',
        'cryptids_creatures' => 'Cryptids & Creatures',
        'unexplained_phenomena' => 'Unexplained Phenomena',
        'strange_disappearances' => 'Strange Disappearances',
        'other' => 'Other',
        'upload_media' => 'Upload Photos/Videos (optional)',
        'choose_file' => 'Choose File',
        'submit_button' => 'SUBMIT REPORT',
        'success_message' => 'Your sighting has been submitted',
        'error_message' => 'Please fill out the report',
        'back_to_map' => 'Back to Map',
        'nav_home' => 'HOME',
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
        'donors' => 'Donors List',
        'report_sighting' => 'Report Sighting',
        'view_profile' => 'View Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'change_password' => 'Change Password',
        'registration' => 'Registration',
        'donations' => 'Donations'
    ],
    'bg' => [
        'title' => 'Докладвай Наблюдение - НЛО Разкритие България',
        'form_title' => 'ДОКЛАДВАЙ НАБЛЮДЕНИЕ',
        'date_time' => 'Дата и Час на Наблюдението',
        'click_map' => 'Кликнете върху картата, за да зададете местоположение',
        'location' => 'Местоположение',
        'latitude' => 'Географска Ширина',
        'longitude' => 'Географска Дължина',
        'sighting_title' => 'Заглавие на Наблюдението',
        'sighting_description' => 'Описание',
        'describe_details' => 'Опишете подробно какво видяхте',
        'sighting_type' => 'Тип Наблюдение',
        'ufos_aliens' => 'НЛО & Извънземни',
        'paranormal_ghosts' => 'Паранормални Явления & Духове',
        'cryptids_creatures' => 'Криптиди & Същества',
        'unexplained_phenomena' => 'Необясними Явления',
        'strange_disappearances' => 'Странни Изчезвания',
        'other' => 'Друго',
        'upload_media' => 'Качете Снимки/Видеоклипове (по желание)',
        'choose_file' => 'Изберете Файл',
        'submit_button' => 'ИЗПРАТИ ДОКЛАД',
        'success_message' => 'Вашето наблюдение беше изпратено',
        'error_message' => 'Моля, попълнете доклада',
        'back_to_map' => 'Обратно към Картата',
        'nav_home' => 'НАЧАЛО',
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
        'donors' => 'Списък с Дарители',
        'report_sighting' => 'Докладвай Наблюдение',
        'view_profile' => 'Преглед на Профила',
        'logout' => 'Изход',
        'login' => 'Вход',
        'change_password' => 'Смяна на Парола',
        'registration' => 'Регистрация',
        'donations' => 'Дарения'
    ]
];

$isLoggedIn = isset($_SESSION['user']['userID']);
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = $texts[$lang]['error_message'] . ' - Invalid CSRF token';
    } else {
        // Assign variables based on language
        $sightingDate = !empty($_POST['sightingDate']) ? $_POST['sightingDate'] : null;
        $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        $sightingTitle = $lang === 'en' 
            ? (!empty($_POST['sightingTitle']) ? trim($_POST['sightingTitle']) : null)
            : (!empty($_POST['sightingTitleBG']) ? trim($_POST['sightingTitleBG']) : null);
        $sightingTitleBG = !empty($_POST['sightingTitleBG']) ? trim($_POST['sightingTitleBG']) : $sightingTitle;
        $sightingDescription = $lang === 'en' 
            ? (!empty($_POST['sightingDescription']) ? trim($_POST['sightingDescription']) : null)
            : (!empty($_POST['sightingDescriptionBG']) ? trim($_POST['sightingDescriptionBG']) : null);
        $sightingDescriptionBG = !empty($_POST['sightingDescriptionBG']) ? trim($_POST['sightingDescriptionBG']) : $sightingDescription;
        $sightingType = !empty($_POST['sightingType']) ? $_POST['sightingType'] : null;

        // Validate all required fields
        if (!$sightingDate || !$latitude || !$longitude || !$sightingTitle || !$sightingDescription || !$sightingType) {
            $error_message = $texts[$lang]['error_message'];
        } else {
            try {
                // Handle file uploads
                $mediaPaths = [];
                if (!empty($_FILES['sightingMedia']['name'][0])) {
                    $upload_dir = '../uploads/sightings/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    foreach ($_FILES['sightingMedia']['name'] as $key => $name) {
                        if ($_FILES['sightingMedia']['error'][$key] === 0) {
                            $tmp_name = $_FILES['sightingMedia']['tmp_name'][$key];
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $filename = uniqid('sighting_') . '.' . $ext;
                            $target = $upload_dir . $filename;
                            if (move_uploaded_file($tmp_name, $target)) {
                                $mediaPaths[] = $target;
                            }
                        }
                    }
                }

                // Insert into database
                $stmt = $db->prepare("
                    INSERT INTO UFO_SIGHTINGS (
                        userID, sightingDate, latitude, longitude, 
                        sightingTitle, sightingDescription, sightingTitleBG, sightingDescriptionBG,
                        sightingType, mediaPaths
                    ) VALUES (
                        :userID, :sightingDate, :latitude, :longitude, 
                        :sightingTitle, :sightingDescription, :sightingTitleBG, :sightingDescriptionBG,
                        :sightingType, :mediaPaths
                    )
                ");
                
                $mediaPathsStr = !empty($mediaPaths) ? json_encode($mediaPaths) : null;
                
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $stmt->bindParam(':sightingDate', $sightingDate);
                $stmt->bindParam(':latitude', $latitude);
                $stmt->bindParam(':longitude', $longitude);
                $stmt->bindParam(':sightingTitle', $sightingTitle);
                $stmt->bindParam(':sightingDescription', $sightingDescription);
                $stmt->bindParam(':sightingTitleBG', $sightingTitleBG);
                $stmt->bindParam(':sightingDescriptionBG', $sightingDescriptionBG);
                $stmt->bindParam(':sightingType', $sightingType);
                $stmt->bindParam(':mediaPaths', $mediaPathsStr);
                
                $stmt->execute();
                $success_message = $texts[$lang]['success_message'];
                // Regenerate CSRF token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (PDOException $e) {
                $error_message = $texts[$lang]['error_message'] . ' - DB Error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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

    <style>
        /* All styles remain unchanged */
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

        body {
            background-image: url('../images/space_img.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            margin: 0;
            min-height: 100vh;
            overflow: hidden;
            font-family: 'Jura', sans-serif;
        }
        .ribbon {
            margin-top: 1%;
            position: relative;
            top: 0%;
            left: 50%;
            width: 90%;
            transform: translateX(-50%); 
            background-color: #744769; 
            color: white;
            padding: 1.5%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 10;
            text-align: center;
        }
        .logo_img {
            position: absolute;
            left: 1%;
            top: 3%;
            height: 80%;
            max-width: 100%
        }
        .fa-container {
            position: fixed; 
            top: 40%;        
            right: 10%;
            display: flex;   
            flex-direction: row; 
            gap: 45%;     
        }

        .fa {
            padding: 10%;       
            width: auto;         
            height: auto;        
            text-align: center;
            text-decoration: none;
        }

        .fa-brands {
            text-decoration: none;
        }

        .fa-patreon,
        .fa-facebook,
        .fa-youtube,
        .fa-instagram {
            background: #744769;
            color: white;
            border-radius: 5%;
            font-size: 1.3rem;
        }

        .fa:hover {
            background-color: #442538;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .navbar {
            background-color: #744769;
            padding: 1% 0;
            font-weight: bold;
        }

        .nav-list {
            list-style-type: none;
            display: flex;
            justify-content: center;
        }

        .nav-item {
            position: relative;
            width: 10%;
            margin: 1%;
        }

        .nav-item a {
            text-decoration: none;
            color: white;
            display: block;
            font-size: 90%;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            -webkit-text-stroke: 0.5px;
        }

        .nav-item a:hover {
            background-color: #5a3651;
        }

        .dropdown-menu {
            list-style: none;
            display: none;
            position: absolute;
            background-color: #5a3651;
            top: 100%; 
            width: 140%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .dropdown-menu li {
            border-bottom: 1px solid #744769;
        }

        .dropdown-menu li a {
            padding: 5%;
            color: white;
        }

        .dropdown-menu li a:hover {
            background-color: #442538;
        }

        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }

        .content-board {
            max-width: 90%;
            margin: auto;
            overflow: auto;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: 'height 1s ease-out, opacity 1s ease-out';
            max-height: 770px;   
        }

        h1 {
            color: #744769;
            margin-bottom: 20px;
            padding-top: 20px;
            font-size: 2.2rem;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            -webkit-text-stroke: 1.5px #744769;
            text-align: center;
            padding-bottom: 5px;
            border-bottom: 2px solid #744769;
        }

        h3 {
            color: #744769;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 20px;
            -webkit-text-stroke: 0.3px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 30px;
            margin-bottom: 10px;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .form-section:hover {
            transform: translateY(-3px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #744769;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="datetime-local"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Jura', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            color: black;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #744769;
            box-shadow: 0 0 8px rgba(116, 71, 105, 0.2);
            transform: translateY(-2px);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        #location-map {
            height: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            z-index: 1;
        }

        .coordinates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .file-input-container {
            position: relative;
            display: inline-block;
            margin-top: 10px;
        }

        .file-input-button {
            background-color: #744769;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .file-input-button:hover {
            background-color: #5a3651;
            transform: translateY(-2px);
        }

        .file-input {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-list {
            margin-top: 10px;
            color: #744769;
            font-family: 'Jura', sans-serif;
        }

        .submit-button {
            background-color: #744769;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
        }

        .submit-button:hover {
            background-color: #5a3651;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(90, 54, 81, 0.3);
        }

        .error-message {
            color: #d23100;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            text-align: center;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 1.5rem;
            font-style: italic;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .success-message {
            color: #2e7d32; 
            background-color: #c8e6c9; 
            border: 1px solid #388e3c;
            padding: 10px;
            text-align: center;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 1.5rem;
            font-style: italic;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
        }

        .back-link {
            display: block;
            text-align: center;
            color: #744769;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #5a3651;
            transform: translateY(-2px);
        }

.hamburger-menu {
    display: none;
    cursor: pointer;
    position: absolute;
    top: 25px;
    left: 20px;
    z-index: 200;
}

.hamburger-menu .bar {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 5px 0;
    transition: 0.4s;
}

.hamburger-menu.active .bar:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.hamburger-menu.active .bar:nth-child(2) {
    opacity: 0;
}

.hamburger-menu.active .bar:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

.menu-overlay.active {
    display: block;
}

.dropdown-arrow {
    margin-left: 8px;
    display: inline-block;
    transition: transform 0.3s ease;
}

.dropdown-arrow.rotate {
    transform: rotate(90deg);
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

@media screen and (max-width: 768px) {

.dropdown-arrow {
    margin-left: 8px;
    display: inline-block;
    transition: transform 0.3s ease;
}

.dropdown-arrow.rotate {
    transform: rotate(90deg);
}

.hamburger-menu {
    display: block;
}

.ribbon {
    margin-top: 0;
    width: 100%;
    padding: 10px;
    position: relative;
    height: auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
}

.logo_img {
    position: relative;
    left: 0;
    top: 0;
    height: 60px;
    display: block;
    margin: 0;
    margin-left: 50px;
}

.fa-container {
    position: relative;
    top: auto;
    right: auto;
    flex-direction: row;
    gap: 10px;
    margin: 0;
}

.fa-patreon,
.fa-facebook,
.fa-youtube,
.fa-instagram {
    font-size: 0.9rem;
    padding: 6px;
}

.navbar {
    padding: 0;
    display: none;
    width: 100%;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 100;
}

.navbar.active {
    display: block; 
}

.nav-list {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
    padding: 0;
}

.nav-item {
    width: 100%;
    margin: 0;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-item a {
    padding: 15px 20px;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-menu {
    position: static;
    width: 100%;
    box-shadow: none;
    display: none;
    background-color: rgba(0, 0, 0, 0.1);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.dropdown-menu li {
    border-bottom: 1px solid #744769;
}

.dropdown-menu li:last-child {
    border-bottom: none;
}

.dropdown-menu li a {
    padding: 12px 20px 12px 35px;
    font-size: 14px;
}

.nav-item.dropdown:hover .dropdown-menu {
    display: none;
}

.nav-item.dropdown.active .dropdown-menu {
    display: block;
}

.content-board {
    min-width: 100%;
    overflow: auto;
    max-height: auto;
    padding-bottom: 100px;
}

.form-grid {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: auto auto; 
        gap: 20px;
    }

    .form-section:first-child {
        order: 1; 
    }

    .form-section:nth-child(2) {
        order: 2; 
    }

    #location-map {
        height: 300px;
    }

h1 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    padding-top: 15px;
}

.legend {
    right: 1px;
    bottom: 10px; 
    transform: scale(0.5); 
    transform-origin: bottom right; 
    width: auto; 
    margin-left: 0; 
}

.error-message,
.success-message {
    font-size: 1rem;
    padding: 8px;
    margin: -15px 0 10px 0;
}
}

@media screen and (min-width: 769px) and (max-width: 1132px) {
    body {
        transform: scale(1, 1.15);
        transform-origin: top center;
        width: 100%;
        margin: 0 auto;
    }
    .nav-item a {
        font-size: 65%; 
    }
    .nav-item.dropdown {
        font-size: 90%; 
    }
    .fa-brands {
        font-size: 0.8rem; 
        padding: 4px; 
    }
    .fa-container {
        gap: 15px;
        right: 2%; 
        top: 35%; 
    }
    .content-board {
        min-width: 98%;
    }
    .ribbon {
        width: 98%; 
    }
    .nav-list {
        flex-direction: row; 
    }
}

@media screen and (min-width: 1133px) and (max-width: 1320px) {
    body {
        transform: scale(0.9, 1);
        transform-origin: top center;
        width: 100%;
        margin: 0 auto;
    }

.nav-item a {
    font-size: 75%; 
}
.nav-item.dropdown {
    font-size: 90%; 
}
.fa-brands {
    font-size: 1rem; 
    padding: 5px; 
}
.fa-container {
    gap: 15px; 
    right: 2%;
    top: 35%; 
}

.content-board {
    width: 90%;
}
.ribbon {
    width: 90%;
}
.nav-list {
    flex-direction: row;
}
}

@media (hover: none) {
.nav-item.dropdown .dropdown-menu {
    display: none;
}

.nav-item.dropdown:active .dropdown-menu {
    display: block;
}
}
    </style>
</head>
<body>
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
    <h1><?php echo $texts[$lang]['form_title']; ?></h1>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-grid">
            <div class="form-section">
                <h3 style="text-align: center;"><?php echo $texts[$lang]['location']; ?></h3>
                <h4 style="color: #888; text-align: center; font-style: italic; margin-bottom: 5px;"><?php echo $texts[$lang]['click_map']; ?></h4>
                <div id="location-map"></div>
                <div class="coordinates-grid">
                    <div class="form-group">
                        <label for="latitude"><?php echo $texts[$lang]['latitude']; ?></label>
                        <input type="text" id="latitude" name="latitude" readonly>
                    </div>
                    <div class="form-group">
                        <label for="longitude"><?php echo $texts[$lang]['longitude']; ?></label>
                        <input type="text" id="longitude" name="longitude" readonly>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="sightingDate"><?php echo $texts[$lang]['date_time']; ?></label>
                    <input type="datetime-local" id="sightingDate" name="sightingDate">
                </div>

                <div class="form-group">
                    <label for="<?php echo $lang === 'en' ? 'sightingTitle' : 'sightingTitleBG'; ?>">
                        <?php echo $texts[$lang]['sighting_title']; ?>
                    </label>
                    <input type="text" 
                           id="<?php echo $lang === 'en' ? 'sightingTitle' : 'sightingTitleBG'; ?>" 
                           name="<?php echo $lang === 'en' ? 'sightingTitle' : 'sightingTitleBG'; ?>" 
                           maxlength="80">
                </div>

                <div class="form-group">
                    <label for="<?php echo $lang === 'en' ? 'sightingDescription' : 'sightingDescriptionBG'; ?>">
                        <?php echo $texts[$lang]['sighting_description']; ?>
                    </label>
                    <textarea
                        style="resize: none;"
                        id="<?php echo $lang === 'en' ? 'sightingDescription' : 'sightingDescriptionBG'; ?>" 
                        name="<?php echo $lang === 'en' ? 'sightingDescription' : 'sightingDescriptionBG'; ?>" 
                        placeholder="<?php echo $texts[$lang]['describe_details']; ?>"></textarea>
                </div>

                <label for="sightingType"><?php echo $texts[$lang]['sighting_type']; ?></label>
                <div class="form-group" style="width: 50%;">                        
                    <select id="sightingType" name="sightingType">
                        <option value="" selected disabled>-- Select --</option>
                        <option value="ufos_aliens"><?php echo $texts[$lang]['ufos_aliens']; ?></option>
                        <option value="paranormal_ghosts"><?php echo $texts[$lang]['paranormal_ghosts']; ?></option>
                        <option value="cryptids_creatures"><?php echo $texts[$lang]['cryptids_creatures']; ?></option>
                        <option value="unexplained_phenomena"><?php echo $texts[$lang]['unexplained_phenomena']; ?></option>
                        <option value="strange_disappearances"><?php echo $texts[$lang]['strange_disappearances']; ?></option>
                        <option value="other"><?php echo $texts[$lang]['other']; ?></option>
                    </select>
                </div>
                <div class="form-section" style="align-items: center;">
                    <h3><?php echo $texts[$lang]['upload_media']; ?></h3>
                    <div class="file-input-container">
                        <span class="file-input-button"><?php echo $texts[$lang]['choose_file']; ?></span>
                        <input type="file" id="sightingMedia" name="sightingMedia[]" multiple class="file-input" accept="image/*,video/*">
                    </div>
                    <div id="file-list" class="file-list"></div>
                </div>
                <button type="submit" class="submit-button"><?php echo $texts[$lang]['submit_button']; ?></button>
            </div>        
        </div>     
    </form>
</div>
        
<!-- Custom Logout Confirmation Popup -->
<div id="overlay" class="overlay"></div>
<div id="logoutPopup" class="popup">
    <h2><?php echo $lang === 'en' ? 'Are you sure you want to log out?' : 'Сигурни ли сте, че искате да излезете?'; ?></h2>
    <button class="confirm" onclick="logout()"><?php echo $lang === 'en' ? 'Yes' : 'Да'; ?></button><br>
    <button class="cancel" onclick="hideLogoutPopup()"><?php echo $lang === 'en' ? 'Cancel' : 'Отказ'; ?></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Define marker colors for each sighting type
        const markerColors = {
            'ufos_aliens': '#4CAF50',          
            'paranormal_ghosts': '#0288D1',    
            'cryptids_creatures': '#8D5524',   
            'unexplained_phenomena': '#FFD700', 
            'strange_disappearances': '#D32F2F', 
            'other': '#808080'                 
        };

        // Map initialization
        const map = L.map('location-map').setView([42.7339, 25.4858], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker;
        const sightingTypeSelect = document.getElementById('sightingType');

        // Function to create a colored marker
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

        // Map click handler
        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            const selectedType = sightingTypeSelect.value;
            const markerColor = selectedType ? markerColors[selectedType] : '#808080';
            
            if (marker) map.removeLayer(marker);
            marker = createColoredMarker(lat, lng, markerColor).addTo(map);
        });

        // Update marker color when sighting type changes
        sightingTypeSelect.addEventListener('change', function() {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            if (lat && lng && marker) {
                map.removeLayer(marker);
                const markerColor = markerColors[this.value] || '#808080';
                marker = createColoredMarker(lat, lng, markerColor).addTo(map);
            }
        });

        // Add legend
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

        // Rest of your existing code (file input handling, datetime, etc.) remains unchanged
        // File input handling
        document.getElementById('sightingMedia').addEventListener('change', function(e) {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            for (let file of this.files) {
                const item = document.createElement('div');
                item.textContent = file.name;
                fileList.appendChild(item);
            }
        });

        // Set default datetime
        const now = new Date();
        const dateTimeValue = now.toISOString().slice(0,16);
        document.getElementById('sightingDate').value = dateTimeValue;

        // Content board animation
        const contentBoard = document.querySelector('.content-board');
        contentBoard.style.height = 'auto';
        let actualHeight = contentBoard.scrollHeight + 'px';
        contentBoard.style.height = '0';
        setTimeout(() => {
            contentBoard.style.height = actualHeight;
            contentBoard.style.transition = 'height 1s ease-out, opacity 1s ease-out';
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