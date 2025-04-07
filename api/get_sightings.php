<?php
header('Content-Type: application/json');
require_once '../inc/db_connect.php';

// Language selection (default to English)
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bg']) ? $_GET['lang'] : 'en';

$response = [];

try {
    // Get approved sightings (all if no ID is provided, or filtered by ID)
    $titleCol = $lang === 'bg' ? 'sightingTitleBG' : 'sightingTitle';
    $descCol = $lang === 'bg' ? 'sightingDescriptionBG' : 'sightingDescription';
    
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        // Fetch a specific sighting by ID
        $sightingID = intval($_GET['id']);
        $stmt = $db->prepare("
            SELECT 
                sightingID, 
                latitude, 
                longitude, 
                $titleCol as title,
                $descCol as description,
                sightingDate,
                sightingType,
                mediaPaths,
                u.userFirstName,
                u.userLastName
            FROM UFO_SIGHTINGS s
            JOIN USERS u ON s.userID = u.userID
            WHERE sightingID = :sightingID
        ");
        $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
        $stmt->execute();
        $response = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Decode mediaPaths if it exists
        if ($response && !empty($response['mediaPaths'])) {
            $mediaPaths = json_decode($response['mediaPaths'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $response['media'] = $mediaPaths;
            } else {
                $response['media_error'] = 'Invalid media data format';
            }
            unset($response['mediaPaths']); // Remove raw mediaPaths from response
        } else {
            $response['media_message'] = 'No media found for this sighting';
        }
    } else {
        // Fetch all recent sightings with mediaPaths
        $stmt = $db->prepare("
            SELECT 
                sightingID, 
                latitude, 
                longitude, 
                $titleCol as title,
                $descCol as description,
                sightingDate,
                sightingType,
                mediaPaths,
                u.userFirstName,
                u.userLastName
            FROM UFO_SIGHTINGS s
            JOIN USERS u ON s.userID = u.userID
            ORDER BY sightingDate DESC
        ");
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process mediaPaths for each sighting
        foreach ($response as &$sighting) {
            if (!empty($sighting['mediaPaths'])) {
                $mediaPaths = json_decode($sighting['mediaPaths'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $sighting['media'] = $mediaPaths;
                } else {
                    $sighting['media_error'] = 'Invalid media data format';
                }
                unset($sighting['mediaPaths']); // Remove raw mediaPaths from response
            }
        }
    }
} catch (PDOException $e) {
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

echo json_encode($response);
?>