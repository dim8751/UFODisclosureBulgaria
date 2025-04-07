<?php
session_start();
require_once '../inc/db_connect.php';

header('Content-Type: application/json');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['userType'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userID = $_SESSION['user']['userID'];

// Get the sighting ID from the POST request
$input = json_decode(file_get_contents('php://input'), true);
$sightingID = isset($input['sightingID']) ? intval($input['sightingID']) : null;

if (!$sightingID) {
    echo json_encode(['success' => false, 'error' => 'Invalid sighting ID']);
    exit;
}

try {
    // Check if the sighting exists
    $stmt = $db->prepare("SELECT sightingID FROM UFO_SIGHTINGS WHERE sightingID = :sightingID");
    $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Sighting not found']);
        exit;
    }

    // Delete associated media files (optional)
    $stmt = $db->prepare("SELECT mediaPaths FROM UFO_SIGHTINGS WHERE sightingID = :sightingID");
    $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['mediaPaths'])) {
        $mediaPaths = json_decode($result['mediaPaths'], true);
        if (is_array($mediaPaths)) {
            foreach ($mediaPaths as $path) {
                if (file_exists($path)) {
                    unlink($path); // Delete the file from the server
                }
            }
        }
    }

    // Delete the sighting from the database
    $stmt = $db->prepare("DELETE FROM UFO_SIGHTINGS WHERE sightingID = :sightingID");
    $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>