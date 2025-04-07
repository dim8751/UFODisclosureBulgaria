<?php
session_start();
require_once '../inc/db_connect.php';

header('Content-Type: application/json');

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userID = $_SESSION['user']['userID'] ?? null;
    
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    if (!$userID) {
        $response['message'] = 'User not authenticated';
        echo json_encode($response);
        exit;
    }

    try {
        switch ($action) {
            case 'add_comment':
                $topicID = $_POST['topicID'] ?? null;
                $commentContent = trim($_POST['commentContent'] ?? '');
                $parentCommentID = $_POST['parentCommentID'] ?? null;

                if (empty($commentContent)) {
                    $response['message'] = 'Comment cannot be empty';
                    break;
                }

                $stmt = $db->prepare("
                    INSERT INTO FORUM_COMMENTS (topicID, userID, parentCommentID, commentContent) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$topicID, $userID, $parentCommentID, $commentContent]);
                
                $commentID = $db->lastInsertId();
                
                // Fetch the newly created comment with user details
                $stmt = $db->prepare("
                    SELECT c.*, u.userFirstName, u.userLastName, u.userProfilePhoto, u.userType,
                        (SELECT SUM(d.donationAmount) FROM DONATIONS d WHERE d.userID = c.userID AND d.donationStatus = 'Completed') as totalDonation
                    FROM FORUM_COMMENTS c
                    JOIN USERS u ON c.userID = u.userID
                    WHERE c.commentID = ?
                ");
                $stmt->execute([$commentID]);
                $comment = $stmt->fetch(PDO::FETCH_ASSOC);

                $response['success'] = true;
                $response['comment'] = $comment;
                $response['parentCommentID'] = $parentCommentID;
                break;

            default:
                $response['message'] = 'Invalid action';
                break;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit;
?>