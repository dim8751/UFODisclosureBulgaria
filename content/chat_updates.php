<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once '../inc/db_connect.php';

$lastId = isset($_GET['lastId']) ? (int)$_GET['lastId'] : 0;
$lastStreamState = null;

while (true) {
    // Check stream state
    $stream_query = "SELECT setting_value FROM stream_settings WHERE setting_name = 'is_stream_live'";
    $stream_stmt = $db->prepare($stream_query);
    $stream_stmt->execute();
    $stream_result = $stream_stmt->fetch(PDO::FETCH_ASSOC);
    $is_stream_live = $stream_result && $stream_result['setting_value'] == '1' ? true : false;

    // If stream state changed, send an event
    if ($lastStreamState !== null && $lastStreamState !== $is_stream_live) {
        $state_data = ['state' => $is_stream_live ? 'on' : 'off'];
        echo "event: stream_state\ndata: " . json_encode($state_data) . "\n\n";
        ob_flush();
        flush();
    }
    $lastStreamState = $is_stream_live;

    // Fetch new messages since the last ID (only if stream is live)
    if ($is_stream_live) {
        $query = "
            SELECT cm.id, cm.message, cm.timestamp, u.userFirstName, u.userLastName, u.userType, u.userProfilePhoto
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.userID
            WHERE cm.id > :lastId
            ORDER BY cm.timestamp ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':lastId', $lastId, PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($messages) {
            foreach ($messages as $message) {
                $isAdmin = $message['userType'] === 'admin';
                $adminBadge = $isAdmin ? '<span class="admin-badge">Admin</span>' : ''; // Ensure proper HTML
                $data = [
                    'id' => $message['id'],
                    'author' => htmlspecialchars($message['userFirstName'] . ' ' . $message['userLastName']),
                    'adminBadge' => $adminBadge, // Separate field for badge
                    'text' => htmlspecialchars($message['message']),
                    'timestamp' => date('M d, Y H:i', strtotime($message['timestamp'])),
                    'photo' => $message['userProfilePhoto'] ?? '../images/default_profile.jpg'
                ];
                echo "event: message\ndata: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
                $lastId = $message['id'];
            }
        }
    }

    // Send a heartbeat to keep the connection alive
    echo ": heartbeat\n\n";
    ob_flush();
    flush();

    sleep(1);
}
?>