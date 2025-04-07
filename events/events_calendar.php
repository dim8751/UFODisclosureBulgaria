<?php
session_start();
session_regenerate_id(true);
require_once('../inc/db_connect.php');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");

// Check if language is set via GET parameter, otherwise use session or default to 'en'
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
        'home' => 'HOME',
        'content' => 'CONTENT',
        'community' => 'COMMUNITY',
        'profile' => 'PROFILE',
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
        'title' => 'Events - UFO Disclosure Bulgaria',
        'previous' => 'Previous',
        'next' => 'Next',
        'create_event' => 'Create New Event',
        'event_title' => 'Event Title',
        'event_description' => 'Event Description',
        'event_location' => 'Location',
        'event_start_date' => 'Start Date',
        'event_end_date' => 'End Date',
        'event_type' => 'Event Type',
        'meeting' => 'Meeting',
        'webinar' => 'Webinar',
        'conference' => 'Conference',
        'observation' => 'Observation',
        'other' => 'Other',
        'max_attendees' => 'Max Attendees (optional)',
        'create_event_btn' => 'Create Event',
        'cancel_btn' => 'Cancel',
        'register_event' => 'Register for Event',
        'attendees' => 'Current Attendees',
        'register_btn' => 'Register',
        'unregister_btn' => 'Unregister',
        'delete_event' => 'Event',
        'delete_btn' => 'Delete Event',
        'logout_popup' => 'Are you sure you want to log out?',
        'yes' => 'Yes',
        'january' => 'January',
        'february' => 'February',
        'march' => 'March',
        'april' => 'April',
        'may' => 'May',
        'june' => 'June',
        'july' => 'July',
        'august' => 'August',
        'september' => 'September',
        'october' => 'October',
        'november' => 'November',
        'december' => 'December',
        'login_to_register_event' => 'Please <a href="../login_register/login.php">login</a> to register for event.',
    ],
    'bg' => [
        'home' => 'НАЧАЛО',
        'content' => 'СЪДЪРЖАНИЕ',
        'community' => 'ОБЩНОСТ',
        'profile' => 'ПРОФИЛ',
        'dashboard' => 'Начален Панел',
        'our_team' => 'Нашият Екип',
        'latest_videos' => 'Нови Видеоклипове',
        'live_stream' => 'Излъчване на Живо',
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
        'title' => 'Събития - НЛО Разкритие България',
        'previous' => 'Предишен',
        'next' => 'Следващ',
        'create_event' => 'Създай Ново Събитие',
        'event_title' => 'Заглавие на Събитието',
        'event_description' => 'Описание на Събитието',
        'event_location' => 'Местоположение',
        'event_start_date' => 'Начална Дата',
        'event_end_date' => 'Крайна Дата',
        'event_type' => 'Тип Събитие',
        'meeting' => 'Среща',
        'webinar' => 'Уебинар',
        'conference' => 'Конференция',
        'observation' => 'Наблюдение',
        'other' => 'Друго',
        'max_attendees' => 'Максимален Брой Участници (по избор)',
        'create_event_btn' => 'Създай Събитие',
        'cancel_btn' => 'Отказ',
        'register_event' => 'Регистрация за Събитие',
        'attendees' => 'Текущи Участници',
        'register_btn' => 'Регистрирай се',
        'unregister_btn' => 'Отписване', 
        'delete_event' => 'Събитие',
        'delete_btn' => 'Изтрий Събитие',
        'logout_popup' => 'Сигурни ли сте, че искате да излезете?',
        'yes' => 'Да',
        'january' => 'Януари',
        'february' => 'Февруари',
        'march' => 'Март',
        'april' => 'Април',
        'may' => 'Май',
        'june' => 'Юни',
        'july' => 'Юли',
        'august' => 'Август',
        'september' => 'Септември',
        'october' => 'Октомври',
        'november' => 'Ноември',
        'december' => 'Декември',
        'login_to_register_event' => 'Моля, <a href="../login_register/login.php">влезте</a>, за да се регистрирате за събитие.',
    ]
];

// Helper function to sanitize output
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);
$userType = $isLoggedIn ? $_SESSION['user']['userType'] : '';
$userID = $isLoggedIn ? $_SESSION['user']['userID'] : null;

// Default profile image path
$profilePhotoPath = '../images/default_profile.jpg';

// If user is logged in, get their profile photo
if ($isLoggedIn) {
    try {
        $query = 'SELECT userProfilePhoto FROM users WHERE userID = :userID';
        $statement = $db->prepare($query);
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['userProfilePhoto']) && $user['userProfilePhoto'] !== '../images/default_profile.jpg') {
            $profilePhotoPath = sanitizeOutput($user['userProfilePhoto']);
        }
    } catch (PDOException $e) {
        error_log("Profile photo error: " . $e->getMessage());
    }
}

$error_msg = '';
$success_msg = '';

// Validate event data
function validateEventData($data) {
    $error_msg = [];
    
    if (empty($data['title'])) {
        $error_msg[] = "Event title is required";
    }
    
    if (!strtotime($data['startDate']) || !strtotime($data['endDate'])) {
        $error_msg[] = "Invalid date format";
    }
    
    if (strtotime($data['endDate']) < strtotime($data['startDate'])) {
        $error_msg[] = "End date must be after start date";
    }
    
    if (!empty($data['maxAttendees']) && (!is_numeric($data['maxAttendees']) || $data['maxAttendees'] < 1)) {
        $error_msg[] = "Maximum attendees must be a positive number";
    }
    
    return $error_msg;
}

// Handle event creation, registration, and unregistration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $response = ['success' => false];
        
        switch ($_POST['action']) {
            case 'create_event':
                if ($userType === 'admin') {
                    try {
                        $error_msg = validateEventData($_POST);
                        if (!empty($error_msg)) {
                            $response['error'] = implode(", ", $error_msg);
                            break;
                        }
            
                        $stmt = $db->prepare("
                            INSERT INTO EVENTS (
                                userID, eventTitle, eventTitleBG, eventDescription, eventDescriptionBG, 
                                eventLocation, eventLocationBG, eventStartDate, eventEndDate, 
                                eventType, maxAttendees, isPublic
                            ) VALUES (
                                :userID, :title, :titleBG, :description, :descriptionBG, 
                                :location, :locationBG, :startDate, :endDate, :type, 
                                :maxAttendees, :isPublic
                            )
                        ");
                        
                        $stmt->execute([
                            'userID' => $userID,
                            'title' => trim($_POST['title']),
                            'titleBG' => trim($_POST['titleBG']),
                            'description' => trim($_POST['description']),
                            'descriptionBG' => trim($_POST['descriptionBG']),
                            'location' => trim($_POST['location']),
                            'locationBG' => trim($_POST['locationBG']),
                            'startDate' => $_POST['startDate'],
                            'endDate' => $_POST['endDate'],
                            'type' => $_POST['type'],
                            'maxAttendees' => empty($_POST['maxAttendees']) ? null : (int)$_POST['maxAttendees'],
                            'isPublic' => 1
                        ]);
                        
                        $response['success'] = true;
                    } catch (PDOException $e) {
                        error_log("Event creation error: " . $e->getMessage());
                        $response['error'] = "Failed to create event";
                    }
                } else {
                    $response['error'] = "Unauthorized";
                }
                break;

            case 'register_event':
                if ($isLoggedIn) {
                    try {
                        $db->beginTransaction();
                        
                        $checkStmt = $db->prepare("
                            SELECT COUNT(*) FROM EVENT_REGISTRATIONS 
                            WHERE eventID = :eventID AND userID = :userID
                        ");
                        $checkStmt->execute([
                            'eventID' => $_POST['eventID'],
                            'userID' => $userID
                        ]);
                        
                        if ($checkStmt->fetchColumn() > 0) {
                            throw new Exception('Already registered');
                        }

                        $eventStmt = $db->prepare("
                            SELECT e.maxAttendees, COUNT(er.registrationID) as currentAttendees
                            FROM EVENTS e
                            LEFT JOIN EVENT_REGISTRATIONS er ON e.eventID = er.eventID
                            WHERE e.eventID = :eventID
                            GROUP BY e.eventID, e.maxAttendees
                            FOR UPDATE
                        ");
                        $eventStmt->execute(['eventID' => $_POST['eventID']]);
                        $eventInfo = $eventStmt->fetch(PDO::FETCH_ASSOC);

                        if ($eventInfo['maxAttendees'] !== null && 
                            $eventInfo['currentAttendees'] >= $eventInfo['maxAttendees']) {
                            throw new Exception('Event is full');
                        }

                        $regStmt = $db->prepare("
                            INSERT INTO EVENT_REGISTRATIONS (eventID, userID, registrationStatus)
                            VALUES (:eventID, :userID, 'confirmed')
                        ");
                        $regStmt->execute([
                            'eventID' => $_POST['eventID'],
                            'userID' => $userID
                        ]);
                        
                        $db->commit();
                        $response['success'] = true;
                    } catch (Exception $e) {
                        $db->rollBack();
                        $response['error'] = $e->getMessage();
                    }
                } else {
                    $response['error'] = "Please login to register";
                }
                break;

            case 'unregister_event':
                if ($isLoggedIn) {
                    try {
                        $stmt = $db->prepare("
                            DELETE FROM EVENT_REGISTRATIONS 
                            WHERE eventID = :eventID AND userID = :userID
                        ");
                        $stmt->execute([
                            'eventID' => $_POST['eventID'],
                            'userID' => $userID
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $response['success'] = true;
                        } else {
                            $response['error'] = "Not registered for this event";
                        }
                    } catch (PDOException $e) {
                        error_log("Event unregistration error: " . $e->getMessage());
                        $response['error'] = "Failed to unregister from event";
                    }
                } else {
                    $response['error'] = "Please login to unregister";
                }
                break;
                
            case 'delete_event':
                if ($userType === 'admin') {
                    try {
                        $stmt = $db->prepare("DELETE FROM EVENT_REGISTRATIONS WHERE eventID = :eventID");
                        $stmt->execute(['eventID' => $_POST['eventID']]);
            
                        $stmt = $db->prepare("DELETE FROM EVENTS WHERE eventID = :eventID AND userID = :userID");
                        $stmt->execute([
                            'eventID' => $_POST['eventID'],
                            'userID' => $userID
                        ]);
            
                        if ($stmt->rowCount() > 0) {
                            $response['success'] = true;
                        } else {
                            $response['error'] = "Event not found or unauthorized";
                        }
                    } catch (PDOException $e) {
                        error_log("Event deletion error: " . $e->getMessage());
                        $response['error'] = "Failed to delete event";
                    }
                } else {
                    $response['error'] = "Unauthorized";
                }
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Get events for the current month
function getMonthEvents($db, $month, $year, $userID = null) {
    try {
        $startDate = date('Y-m-d H:i:s', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t 23:59:59', strtotime("$year-$month-01"));
        
        $query = "
            SELECT 
                e.eventID,
                e.eventTitle,
                e.eventTitleBG,
                e.eventDescription,
                e.eventDescriptionBG,
                e.eventLocation,
                e.eventLocationBG,
                DATE(e.eventStartDate) as eventDate,
                TIME(e.eventStartDate) as startTime,
                TIME(e.eventEndDate) as endTime,
                e.eventType,
                e.maxAttendees,
                COUNT(er.registrationID) as currentAttendees,
                " . ($userID ? "EXISTS (
                    SELECT 1 
                    FROM EVENT_REGISTRATIONS er2 
                    WHERE er2.eventID = e.eventID 
                    AND er2.userID = :userID
                )" : "0") . " as isRegistered
            FROM EVENTS e
            LEFT JOIN EVENT_REGISTRATIONS er ON e.eventID = er.eventID
            WHERE e.eventStartDate BETWEEN :startDate AND :endDate
            AND e.isPublic = 1
            GROUP BY e.eventID, e.eventTitle, e.eventTitleBG, e.eventDescription, 
                     e.eventDescriptionBG, e.eventLocation, e.eventLocationBG, 
                     e.eventStartDate, e.eventEndDate, e.eventType, e.maxAttendees
            ORDER BY e.eventStartDate
        ";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        if ($userID) {
            $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dayKey = date('j', strtotime($row['eventDate']));
            if (!isset($events[$dayKey])) {
                $events[$dayKey] = [];
            }
            $events[$dayKey][] = $row;
        }
        return $events;
    } catch (PDOException $e) {
        error_log("Event fetch error: " . $e->getMessage());
        return [];
    }
}

// Get current month and year
$month = isset($_GET['month']) ? max(1, min(12, intval($_GET['month']))) : intval(date('m'));
$year = isset($_GET['year']) ? max(2000, min(2100, intval($_GET['year']))) : intval(date('Y'));

// Calculate calendar variables
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$firstDayOfWeek = date('w', $firstDayOfMonth);

// Get events
try {
    $events = getMonthEvents($db, $month, $year, $userID);
} catch (PDOException $e) {
    error_log("Calendar error: " . $e->getMessage());
    $events = [];
}

// Navigation links
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<title><?php echo $texts[$lang]['title']; ?></title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jura:wght@400;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        .calendar {
            width: 90%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: auto;
            max-height: 770px;
            opacity: 0;
	        transition: height 1s ease-out, opacity 1s ease-out;
            overflow: auto;
            margin: auto;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            background-color: #f0f0f0;
        }

        .calendar.loaded {
            opacity: 1;
        }
                
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f0f0f0;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #ddd;
        }
        .calendar-day-header {
            background-color: #442538;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .calendar-day {
            background-color: white;
            min-height: 100px;
            padding: 10px;
            cursor: pointer;
        }
        .calendar-day.empty {
            background-color: #f9f9f9;
        }
        
        .calendar-day:hover {
            background-color: #f5f5f5;
        }
        
        a {
            text-decoration: none;
            font-weight: bold;
            color: #442538;
        }
        
        .event {
            margin: 2px 0;
            padding: 5px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
        }
        .event.meeting { background-color: #ffebee; }
        .event.webinar { background-color: #e3f2fd; }
        .event.conference { background-color: #e8f5e9; }
        .event.observation { background-color: #ede7f6; }
        .event.other { background-color: #fbe9e7; }
        
        .registered-label {
            background-color: #4CAF50;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 5px;
            display: inline-block;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 10;
        }
        
        .modal-content {
            background-color: white;
            margin: 8% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 5px;
            text-align: center;
            overflow: auto;
            max-height: 80%;
        }
        
        .modal input, .modal textarea, .modal select {
            width: 100%;
            margin-bottom: 8px;
            width: 85%;
            padding: 15px 10px;
            margin: 0 auto 20px auto;
            display: block;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Jura', sans-serif;
            font-weight: 700;
            word-wrap: break-word;
            overflow-wrap: break-word;
            transition: all 0.3s ease;
        }
        
        .modal input, .modal textarea, .modal select:focus {
            outline: none;
            border: 2px solid #744769;
            box-shadow: 0 0 10px rgba(116, 71, 105, 0.2);
            transform: translateY(-2px);
        }

        .modal button {
            margin: 5px;
            padding: 8px 15px;
            cursor: pointer;
        }

        .event-full {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .event-controls {
            display: none;
            position: absolute;
            right: 5px;
            top: 5px;
        }

        .event:hover .event-controls {
            display: block;
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
                        <li><a href="events_calendar.php"><?php echo $texts[$lang]['events']; ?></a></li>
                        <li><a href="../content/sightings_form.php?lang=<?php echo $lang; ?>"><?php echo $texts[$lang]['report_sighting']; ?></a></li>
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
                    <a href="#"><?php echo $lang === 'bg' ? 'ЕЗИК' : 'LANGUAGE'; ?> ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="?lang=en">English (EN)</a></li>
                        <li><a href="?lang=bg">Български (BG)</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <div class="calendar">
    <?php if (!$isLoggedIn) : ?>
    <div class="login-message">
        <?php echo $texts[$lang]['login_to_register_event']; ?>
    </div>
    <?php endif; ?>
        <div class="calendar-header">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&lang=<?= $lang ?>">&lt; <?php echo $texts[$lang]['previous']; ?></a>
            <h2><?php
                $monthNames = [
                    1 => 'january',
                    2 => 'february',
                    3 => 'march',
                    4 => 'april',
                    5 => 'may',
                    6 => 'june',
                    7 => 'july',
                    8 => 'august',
                    9 => 'september',
                    10 => 'october',
                    11 => 'november',
                    12 => 'december'
                ];
                $monthKey = $monthNames[$month];
                echo $texts[$lang][$monthKey] . ' ' . $year;
            ?></h2>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&lang=<?= $lang ?>"><?php echo $texts[$lang]['next']; ?> &gt;</a>
        </div>
        
        <div class="calendar-grid">
            <?php
            $days = $lang === 'bg' ? ['Нед', 'Пон', 'Вто', 'Сря', 'Чет', 'Пет', 'Съб'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($days as $day) {
                echo "<div class='calendar-day-header'>" . sanitizeOutput($day) . "</div>";
            }
            
            for ($i = 0; $i < $firstDayOfWeek; $i++) {
                echo "<div class='calendar-day empty'></div>";
            }
            
            for ($day = 1; $day <= $numberDays; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                echo "<div class='calendar-day' data-date='" . sanitizeOutput($dateStr) . "'>";
                echo "<div class='day-number'>" . $day . "</div>";
                
                if (isset($events[$day])) {
                    foreach ($events[$day] as $event) {
                        $eventClass = 'event ' . sanitizeOutput($event['eventType']);
                        if ($event['maxAttendees'] !== null && $event['currentAttendees'] >= $event['maxAttendees']) {
                            $eventClass .= ' event-full';
                        }
                        
                        echo "<div class='" . $eventClass . "' data-event='" . 
                             sanitizeOutput(json_encode($event)) . "'>";
                        if ($event['isRegistered']) {
                            echo "<span class='registered-label'>" . 
                                 ($lang === 'bg' ? 'Регистриран' : 'Registered') . 
                                 "</span> ";
                        }
                        echo sanitizeOutput($lang === 'bg' ? $event['eventTitleBG'] : $event['eventTitle']);
                        echo "<br>" . sanitizeOutput($event['startTime']) . " - " . sanitizeOutput($event['endTime']);
                        echo "<br>" . ($lang === 'bg' ? 'Местоположение: ' : 'Location: ') . 
                             sanitizeOutput($lang === 'bg' ? $event['eventLocationBG'] : $event['eventLocation']);
                        if ($event['maxAttendees']) {
                            echo "<br>" . ($lang === 'bg' ? 'Участници: ' : 'Attendees: ') . 
                                 $event['currentAttendees'] . "/" . $event['maxAttendees'];
                        }
                        echo "</div>";
                    }
                }
                
                echo "</div>";
            }
            
            $lastDayOfWeek = ($firstDayOfWeek + $numberDays - 1) % 7;
            $remainingDays = 6 - $lastDayOfWeek;
            for ($i = 0; $i < $remainingDays; $i++) {
                echo "<div class='calendar-day empty'></div>";
            }
            ?>
        </div>
    </div>

<!-- Event Creation Modal -->
<div id="createEventModal" class="modal">
    <div class="modal-content">
        <h1><?php echo $texts[$lang]['create_event']; ?></h1>
        <div id="createEventMessage" class="message-container"></div>
        <form id="createEventForm">
            <input type="hidden" name="action" value="create_event">
            <input type="text" id="eventTitle" name="title" placeholder="<?php echo $texts[$lang]['event_title']; ?> (EN)">
            <textarea id="eventDescription" name="description" placeholder="<?php echo $texts[$lang]['event_description']; ?> (EN)"></textarea>
            <input type="text" id="eventLocation" name="location" placeholder="<?php echo $texts[$lang]['event_location']; ?> (EN)">
            <input type="text" id="eventTitleBG" name="titleBG" placeholder="<?php echo $texts[$lang]['event_title']; ?> (BG)">
            <textarea id="eventDescriptionBG" name="descriptionBG" placeholder="<?php echo $texts[$lang]['event_description']; ?> (BG)"></textarea>
            <input type="text" id="eventLocationBG" name="locationBG" placeholder="<?php echo $texts[$lang]['event_location']; ?> (BG)">
            <input type="datetime-local" id="eventStartDate" name="startDate">
            <input type="datetime-local" id="eventEndDate" name="endDate">
            <select id="eventType" name="type">
                <option value="meeting"><?php echo $texts[$lang]['meeting']; ?></option>
                <option value="webinar"><?php echo $texts[$lang]['webinar']; ?></option>
                <option value="conference"><?php echo $texts[$lang]['conference']; ?></option>
                <option value="observation"><?php echo $texts[$lang]['observation']; ?></option>
                <option value="other"><?php echo $texts[$lang]['other']; ?></option>
            </select>
            <input type="number" id="maxAttendees" name="maxAttendees" placeholder="<?php echo $texts[$lang]['max_attendees']; ?>" min="1">
            <button type="submit" class="event-btn"><?php echo $texts[$lang]['create_event_btn']; ?></button>
            <button type="button" class="cancel-btn" onclick="closeCreateEventModal()"><?php echo $texts[$lang]['cancel_btn']; ?></button>
        </form>
    </div>
</div>

<!-- Event Registration Modal -->
<div id="registerEventModal" class="modal">
    <div class="modal-content">
        <h1><?php echo $texts[$lang]['register_event']; ?></h1>
        <div id="registerEventMessage" class="message-container"></div>
        <p id="registrationEventTitle" style="font-size: 1.2rem; -webkit-text-stroke: 0.5px;"></p>
        <p id="registrationEventDescription" style="color: black; font-weight: 0.2rem; text-align: left; margin: auto; margin-bottom: 10px; z-index: 1; width: auto; max-width: fit-content; border: 2px solid #744769; background-color: white; border-radius: 8px; padding: 15px; font-family: 'Jura', sans-serif; font-weight: 700;"></p>
        <p id="registrationEventDetails"></p>
        <p id="registrationEventLocation"></p>
        <p id="registrationEventAttendeeCount"></p>
        <button type="button" class="register-btn" id="confirmRegistrationBtn"><?php echo $texts[$lang]['register_btn']; ?></button>
        <button type="button" class="register-btn" id="loginToRegisterBtn" style="display: none;" onclick="window.location.href='../login_register/login.php'"><?php echo $lang === 'bg' ? 'ВЛЕЗТЕ ЗА РЕГИСТРАЦИЯ' : 'LOG IN TO REGISTER'; ?></button>
        <button type="button" class="cancel-btn" id="cancelRegistrationBtn" onclick="closeRegisterEventModal()"><?php echo $texts[$lang]['cancel_btn']; ?></button>
    </div>
</div>

<!-- Event Deletion Modal -->
<div id="deleteEventModal" class="modal">
    <div class="modal-content">
        <h1><?php echo $texts[$lang]['delete_event']; ?></h1>
        <p id="deleteEventTitle" style="font-size: 1.2rem; -webkit-text-stroke: 0.5px;"></p>
        <p id="deleteEventDescription" style="color: black; font-weight: 0.2rem; text-align: left; margin: auto; margin-bottom: 10px; z-index: 1; width: auto; max-width: fit-content; border: 2px solid #744769; background-color: white; border-radius: 8px; padding: 15px; font-family: 'Jura', sans-serif; font-weight: 700;"></p>
        <div id="deleteEventMessage" class="message-container"></div>
        <button type="button" id="confirmDeleteBtn" class="delete-btn"><?php echo $texts[$lang]['delete_btn']; ?></button>
        <button type="button" class="cancel-btn" onclick="closeDeleteEventModal()"><?php echo $texts[$lang]['cancel_btn']; ?></button>
    </div>
</div>

<script>
    const currentLang = '<?php echo $lang; ?>';

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('confirmRegistrationBtn').addEventListener('click', handleRegistrationToggle);
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmEventDeletion);

        document.querySelector('.calendar-grid').addEventListener('click', function(e) {
            const dayElement = e.target.closest('.calendar-day');
            if (dayElement && !dayElement.classList.contains('empty') && !e.target.closest('.event')) {
                const date = dayElement.dataset.date;
                if ('<?php echo $userType; ?>' === 'admin') {
                    showCreateEventModal(date);
                }
            }
        });

        document.querySelector('.calendar-grid').addEventListener('click', function(e) {
    const eventElement = e.target.closest('.event');
    if (eventElement) {
        e.stopPropagation();
        const eventData = JSON.parse(eventElement.dataset.event);
        
        if ('<?php echo $userType; ?>' === 'admin') {
            showDeleteEventModal(eventData);
        } else {
            // Show event details for all users, logged in or not
            showRegisterEventModal(eventData);
        }
    }
});

        document.getElementById('createEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            const startDate = new Date(formData.get('startDate'));
            const endDate = new Date(formData.get('endDate'));
            
            if (endDate < startDate) {
                showModalMessage('createEvent', '<?php echo $lang === "bg" ? "Крайната дата трябва да е след началната дата" : "End date must be after start date"; ?>', true);
                return;
            }

            fetch('events_calendar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModalMessage('createEvent', '<?php echo $lang === "bg" ? "Събитието е създадено успешно!" : "Event created successfully!"; ?>', false);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showModalMessage('createEvent', data.error || '<?php echo $lang === "bg" ? "Неуспешно създаване на събитие" : "Failed to create event"; ?>', true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModalMessage('createEvent', '<?php echo $lang === "bg" ? "Възникна грешка при създаването на събитието" : "An error occurred while creating the event"; ?>', true);
            });
        });
    });

    function showModalMessage(modalId, message, isError = false) {
        const messageContainer = document.getElementById(modalId + 'Message');
        messageContainer.textContent = message;
        messageContainer.className = 'message-container ' + (isError ? 'error-message' : 'success-message');
        messageContainer.style.display = 'block';
    }

    function clearModalMessage(modalId) {
        const messageContainer = document.getElementById(modalId + 'Message');
        messageContainer.style.display = 'none';
        messageContainer.textContent = '';
        messageContainer.className = 'message-container';
    }

    function handleRegistrationToggle() {
        if (!window.selectedEventId) {
            showModalMessage('registerEvent', '<?php echo $lang === "bg" ? "Няма избрано събитие" : "No event selected"; ?>', true);
            return;
        }

        const registerBtn = document.getElementById('confirmRegistrationBtn');
        const action = registerBtn.textContent === '<?php echo $texts[$lang]['register_btn']; ?>' ? 'register_event' : 'unregister_event';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('eventID', window.selectedEventId);

        fetch('events_calendar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMsg = action === 'register_event' 
                    ? '<?php echo $lang === "bg" ? "Успешно регистриран за събитието!" : "Successfully registered for event!"; ?>'
                    : '<?php echo $lang === "bg" ? "Успешно отписан от събитието!" : "Successfully unregistered from event!"; ?>';
                showModalMessage('registerEvent', successMsg, false);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                const errorMsg = action === 'register_event'
                    ? '<?php echo $lang === "bg" ? "Неуспешна регистрация за събитието" : "Failed to register for event"; ?>'
                    : '<?php echo $lang === "bg" ? "Неуспешно отписване от събитието" : "Failed to unregister from event"; ?>';
                showModalMessage('registerEvent', data.error || errorMsg, true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModalMessage('registerEvent', '<?php echo $lang === "bg" ? "Възникна грешка" : "An error occurred"; ?>', true);
        });
    }

    function confirmEventDeletion() {
        if (!window.selectedEventId) {
            showModalMessage('deleteEvent', '<?php echo $lang === "bg" ? "Няма избрано събитие за изтриване" : "No event selected for deletion"; ?>', true);
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('eventID', window.selectedEventId);

        fetch('events_calendar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showModalMessage('deleteEvent', '<?php echo $lang === "bg" ? "Събитието е изтрито успешно!" : "Event successfully deleted!"; ?>', false);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showModalMessage('deleteEvent', data.error || '<?php echo $lang === "bg" ? "Неуспешно изтриване на събитието" : "Failed to delete event"; ?>', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModalMessage('deleteEvent', '<?php echo $lang === "bg" ? "Възникна грешка при изтриването на събитието" : "An error occurred while deleting the event"; ?>', true);
        });
    }

    function showCreateEventModal(date) {
        clearModalMessage('createEvent');
        const startDateTime = date + 'T09:00';
        const endDateTime = date + 'T17:00';
        
        document.getElementById('eventStartDate').value = startDateTime;
        document.getElementById('eventEndDate').value = endDateTime;
        
        document.getElementById('eventTitle').value = '';
        document.getElementById('eventDescription').value = '';
        document.getElementById('eventLocation').value = '';
        document.getElementById('eventType').value = 'meeting';
        document.getElementById('maxAttendees').value = '';
        
        document.getElementById('createEventModal').style.display = 'block';
    }

    function showRegisterEventModal(event) {
    clearModalMessage('registerEvent');
    document.getElementById('registrationEventTitle').textContent = 
        currentLang === 'bg' ? event.eventTitleBG : event.eventTitle;
    document.getElementById('registrationEventDetails').textContent = 
        `${event.eventDate} ${event.startTime} - ${event.endTime}`;
    document.getElementById('registrationEventLocation').textContent = 
        (currentLang === 'bg' ? 'Местоположение: ' : 'Location: ') +
        (currentLang === 'bg' ? event.eventLocationBG : event.eventLocation);
    document.getElementById('registrationEventDescription').textContent = 
        (currentLang === 'bg' ? 'Описание: ' : 'Description: ') +
        (currentLang === 'bg' ? event.eventDescriptionBG : event.eventDescription);
    document.getElementById('registrationEventAttendeeCount').textContent = 
        `<?php echo $texts[$lang]['attendees']; ?>: ${event.currentAttendees}` + 
        (event.maxAttendees ? ` / ${event.maxAttendees}` : '');
    
    const registerBtn = document.getElementById('confirmRegistrationBtn');
    const loginBtn = document.getElementById('loginToRegisterBtn');
    const cancelBtn = document.getElementById('cancelRegistrationBtn');
    const isLoggedIn = '<?php echo $isLoggedIn ? '1' : '0'; ?>' === '1';

    if (isLoggedIn) {
        // For logged-in users: show Register/Unregister and Cancel buttons
        if (event.isRegistered) {
            registerBtn.textContent = '<?php echo $texts[$lang]['unregister_btn']; ?>';
        } else {
            registerBtn.textContent = '<?php echo $texts[$lang]['register_btn']; ?>';
        }
        registerBtn.style.display = 'inline-block'; // Show Register/Unregister button
        loginBtn.style.display = 'none';            // Hide Login button
        cancelBtn.style.display = 'inline-block';   // Show Cancel button
        registerBtn.disabled = false;               // Enable Register/Unregister button
    } else {
        // For non-logged-in users: show Login and Cancel buttons
        registerBtn.style.display = 'none';         // Hide Register/Unregister button
        loginBtn.style.display = 'inline-block';    // Show Login button
        cancelBtn.style.display = 'inline-block';   // Show Cancel button
    }
    
    document.getElementById('registerEventModal').style.display = 'block';
    window.selectedEventId = event.eventID;
}

    function showDeleteEventModal(event) {
    clearModalMessage('deleteEvent');
    document.getElementById('deleteEventTitle').textContent = 
        (currentLang === 'bg' ? 'Заглавие: ' : 'Title: ') + 
        (currentLang === 'bg' ? event.eventTitleBG : event.eventTitle);
    document.getElementById('deleteEventDescription').textContent = 
        (currentLang === 'bg' ? 'Описание: ' : 'Description: ') +
        (currentLang === 'bg' ? event.eventDescriptionBG : event.eventDescription);
    
    const modalContent = document.querySelector('#deleteEventModal .modal-content');
    let locationPara = document.getElementById('deleteEventLocation');
    let attendeesPara = document.getElementById('deleteEventAttendees');
    
    // Create elements if they don't exist
    if (!locationPara) {
        locationPara = document.createElement('p');
        locationPara.id = 'deleteEventLocation';
        modalContent.insertBefore(locationPara, modalContent.querySelector('p:last-of-type'));
    }
    if (!attendeesPara) {
        attendeesPara = document.createElement('p');
        attendeesPara.id = 'deleteEventAttendees';
        modalContent.insertBefore(attendeesPara, modalContent.querySelector('p:last-of-type'));
    }
    
    locationPara.textContent = 
        (currentLang === 'bg' ? 'Местоположение: ' : 'Location: ') +
        (currentLang === 'bg' ? event.eventLocationBG : event.eventLocation);
    attendeesPara.textContent = 
        `<?php echo $texts[$lang]['attendees']; ?>: ${event.currentAttendees}` + 
        (event.maxAttendees ? ` / ${event.maxAttendees}` : '');
    
    document.getElementById('deleteEventModal').style.display = 'block';
    window.selectedEventId = event.eventID;
    }

    function closeCreateEventModal() {
        document.getElementById('createEventModal').style.display = 'none';
        document.getElementById('createEventForm').reset();
        document.getElementById('eventTitleBG').value = '';
        document.getElementById('eventDescriptionBG').value = '';
        document.getElementById('eventLocationBG').value = '';
    }

    function closeRegisterEventModal() {
        document.getElementById('registerEventModal').style.display = 'none';
        window.selectedEventId = null;
    }

    function closeDeleteEventModal() {
        document.getElementById('deleteEventModal').style.display = 'none';
        window.selectedEventId = null;
    }

    function closeAllModals() {
        document.getElementById('createEventModal').style.display = 'none';
        document.getElementById('registerEventModal').style.display = 'none';
        document.getElementById('deleteEventModal').style.display = 'none';
        window.selectedEventId = null;
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeAllModals();
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        const calendar = document.querySelector(".calendar");
        calendar.style.height = "auto";
        let actualHeight = calendar.scrollHeight + "px";
        calendar.style.height = "0";
        setTimeout(() => {
            calendar.style.height = actualHeight;
            calendar.classList.add("loaded");
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

<!-- Logout Confirmation Popup -->
<div id="overlay" class="overlay"></div>
<div id="logoutPopup" class="popup">
    <h2><?php echo $texts[$lang]['logout_popup']; ?></h2>
    <button class="confirm" onclick="logout()"><?php echo $texts[$lang]['yes']; ?></button><br>
    <button class="cancel" onclick="hideLogoutPopup()"><?php echo $texts[$lang]['cancel_btn']; ?></button>
</div>
</body>
</html>