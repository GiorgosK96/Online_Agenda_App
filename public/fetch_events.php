<?php
include '../app/config/config.php';

$user_id = $_SESSION['user_id']; // Retrieve the user ID

// Select all events where the current user is either the creator or a participant.
$sql_events = "
    SELECT events.id, events.title, events.start_time, events.end_time, events.user_id as creator, creator.username as creator_username
    FROM events
    LEFT JOIN users as creator ON events.user_id = creator.id
    WHERE events.user_id = ? OR events.id IN (SELECT event_id FROM participants WHERE user_id = ?)
";

$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("ii", $user_id, $user_id);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
$events = [];
$events_map = [];

while ($row = $result_events->fetch_assoc()) {
    $event_id = $row['id'];
    $events_map[$event_id] = [
        'id' => $event_id,
        'title' => htmlspecialchars($row['title']),
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'participants' => [],
        'comments' => [],
        'creator' => $row['creator'],
        'creator_username' => htmlspecialchars($row['creator_username']),
        'status' => []
    ];
}

// Select participants and their comments for all events related to the current user.
$sql_participants = "
    SELECT participants.event_id, users.username as participant_username, participants.comments, participants.status, participants.comment_time
    FROM participants
    LEFT JOIN users ON participants.user_id = users.id
    WHERE participants.event_id IN (SELECT id FROM events WHERE user_id = ? OR id IN (SELECT event_id FROM participants WHERE user_id = ?))
";

$stmt_participants = $conn->prepare($sql_participants);
$stmt_participants->bind_param("ii", $user_id, $user_id);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();

while ($row = $result_participants->fetch_assoc()) {
    $event_id = $row['event_id'];
    $participant = htmlspecialchars($row['participant_username']);
    $comment = htmlspecialchars($row['comments']);
    $comment_time = $row['comment_time'];
    $status = $row['status'];
    
    if ($participant && !in_array($participant, $events_map[$event_id]['participants'])) { // Check for duplicate participants
        $events_map[$event_id]['participants'][] = $participant;
    }
    
    if ($comment) {
        $events_map[$event_id]['comments'][] = [
            'username' => $participant,
            'comment' => $comment,
            'comment_time' => $comment_time
        ];
    }

    if ($status) {
        $translated_status = ($status == 'attending') ? 'Yes' : (($status == 'not_attending') ? 'No' : 'Pending');
        $events_map[$event_id]['status'][$participant] = $translated_status;
    }
}

$events = array_values($events_map);

// Retrieve the username and email of the current user from the users table
$sql_user = "SELECT username, email FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email']);
?>
