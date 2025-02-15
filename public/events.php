<?php
include '../app/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Check if the request is to delete an event
    if (isset($_POST['delete']) && $_POST['delete'] === 'Delete Event') {
        $event_id = $_POST['eventId'];

        // Check if the user is the creator of the event
        $sql = "SELECT user_id FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        if ($event['user_id'] != $user_id) {
            echo "Error: Only the creator can delete this event.";
            exit();
        }

        // Delete the participants
        $sql = "DELETE FROM participants WHERE event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        // Delete the event
        $sql = "DELETE FROM events WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $event_id, $user_id);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "Error deleting event.";
        }
    
    // Check if the request is to save an event
    } elseif (isset($_POST['submit']) && $_POST['submit'] === 'Save Event') {
        $event_id = $_POST['eventId'];
        $event_name = $_POST['name'];
        $event_start = $_POST['start'];
        $event_end = $_POST['end'];
        $participantInput = isset($_POST['participantInput']) ? $_POST['participantInput'] : '';
        $participants = array_map('trim', explode(',', $participantInput));

        if (empty($event_id)) {
            // Insert a new event if the event ID is empty, otherwise, it should be updated
            $sql = "INSERT INTO events (user_id, title, start_time, end_time) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $event_name, $event_start, $event_end);

            if ($stmt->execute()) {
                $event_id = $stmt->insert_id;
            } else {
                echo "Error saving event.";
                exit();
            }
        } else {
            // Check if the user is the creator of the event
            $sql = "SELECT user_id FROM events WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();

            if ($event['user_id'] != $user_id) {
                echo "Error: Only the creator can update this event.";
                exit();
            }

            // Update the event
            $sql = "UPDATE events SET title = ?, start_time = ?, end_time = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $event_name, $event_start, $event_end, $event_id, $user_id);

            if (!$stmt->execute()) {
                echo "Error updating event.";
                exit();
            }

            // Delete the participants to reinsert them
            $sql = "DELETE FROM participants WHERE event_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
        }

        // Insert participants
        foreach ($participants as $participant) {
            $sql = "SELECT id FROM users WHERE email = ? OR username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $participant, $participant);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $participant_id = $user['id'];

                $sql = "INSERT INTO participants (event_id, user_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $event_id, $participant_id);
                $stmt->execute();
            }
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    
    // Save a comment
    } elseif (isset($_POST['saveComment']) && $_POST['saveComment'] === 'Save Comment') {
        $event_id = $_POST['eventId'];
        $comment = $_POST['comment'];

        // First, get the status (whether they will attend or not)
        $sql = "SELECT status FROM participants WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_status = $result->fetch_assoc()['status'];

        // Then, save the comment along with the current status
        $sql = "INSERT INTO participants (event_id, user_id, comments, status, comment_time) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE comments = ?, status = ?, comment_time = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $event_id, $user_id, $comment, $current_status, $comment, $current_status);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "Error saving comment.";
        }

    // Check if the request is to save the status
    } elseif (isset($_POST['saveStatus']) && $_POST['saveStatus'] === 'Save Status') {
        $event_id = $_POST['eventId'];
        $status = $_POST['status'];

        $sql = "UPDATE participants SET status = ? WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $event_id, $user_id);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "Error saving status.";
        }
    }
}
?>
