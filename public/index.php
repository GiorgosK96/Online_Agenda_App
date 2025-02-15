<?php
session_start();
include '../app/config/config.php';
include 'events.php';
include 'fetch_events.php';
?>

<html>
<head>
    <title>Welcome to your Calendar</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<h2><center><strong>Welcome to your Calendar, <?php echo htmlspecialchars($username); ?></strong></center></h2>
  <div class="container">
    <div id="calendar"></div>
      <br>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
  <br>
</body>
</html>

<script>
var selectedDate;

$(document).ready(function() {
    var events = <?php echo json_encode($events); ?>; // Αυτή η μεταβλητή περιέχει τα events που έχουν ληφθεί από τη βάση δεδομένων
    var currentUserId = <?php echo json_encode($user_id); ?>; // Αυτή η μεταβλητή περιέχει το user_id του τρέχοντος χρήστη
    console.log(events);

    $('#calendar').fullCalendar({
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            selectedDate = start; 
            // Όταν ο χρήστης επιλέγει να δημιουργήσει ένα event ποια στοιχεία φαίνονται στο modal μας.
            $('#eventForm')[0].reset(); 
            $('#eventId').val(''); 
            $('#participantInput').val(''); 
            $('#participantsList').empty(); 
            $('#participantsStatusList tbody').empty();
            $('#commentsList').empty(); 
            $('#eventTitle').prop('readonly', false);
            $('#startTime').prop('readonly', false);
            $('#endTime').prop('readonly', false);
            $('#participantInput').prop('readonly', false);
            $('#status').prop('disabled', true);
            $('#saveEventButton').show();
            $('#deleteEventButton').show();
            $('#comment').prop('readonly', true);
            $('#saveCommentButton').hide(); 
            $('#saveStatusButton').hide();
            $('#creatorUsername').text('');
            $('#myModal').modal('show');
        },
        header: {
            left: 'month, agendaWeek, agendaDay, list',
            center: 'title',
            right: 'prev, today, next'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        editable: true,
        eventLimit: true,
        // Μετατροπή των δεδομένων των γεγονότων από τον αρχικό πίνακα events σε μορφή που μπορεί να καταλάβει και να εμφανίσει το FullCalendar.
        events: events.map(function(event) {
            return {
                id: event.id,
                title: event.title,
                start: event.start_time,
                end: event.end_time,
                color: event.creator == currentUserId ? 'CadetBlue' : 'ForestGreen',
                textColor: 'black',
                participants: event.participants,
                creator: event.creator,
                creator_username: event.creator_username,
                comments: event.comments,
                status: event.status
            };
        }),
        eventClick: function(event) {
            console.log('Event clicked:', event);
            // Αυτές οι γραμμές γεμίζουν τα πεδία της φόρμας με τα δεδομένα του επιλεγμένου γεγονότος
            $('#eventId').val(event.id);
            $('#eventTitle').val(event.title);
            $('#startTime').val(new Date(event.start).toISOString().slice(0, 16));
            $('#endTime').val(new Date(event.end).toISOString().slice(0, 16));
            $('#participantInput').val(''); 
            $('#participantsList').empty();
            $('#participantsStatusList tbody').empty();
            $('#commentsList').empty();
            $('#creatorUsername').text(event.creator_username); 

            event.participants.forEach(function(participant) {
                $('#participantsList').append('<p>' + participant + '</p>');
                $('#participantsStatusList tbody').append('<tr><td>' + participant + '</td><td>' + (event.status[participant] || 'Pending') + '</td></tr>');
            });
            // Ελέγχουμε αν ο χρήστης είναι ο δημιουργός του event ή όχι
            if (event.creator != currentUserId) {
                $('#eventTitle').prop('readonly', true);
                $('#startTime').prop('readonly', true);
                $('#endTime').prop('readonly', true);
                $('#participantInput').prop('readonly', true);
                $('#saveEventButton').hide();
                $('#deleteEventButton').hide();
                $('#comment').prop('readonly', false);
                $('#status').prop('disabled', false); 
                $('#saveCommentButton').show();
                $('#saveStatusButton').show(); 
            } else {
                $('#eventTitle').prop('readonly', false);
                $('#startTime').prop('readonly', false);
                $('#endTime').prop('readonly', false);
                $('#participantInput').prop('readonly', false);
                $('#status').prop('disabled', true); 
                $('#saveEventButton').show();
                $('#deleteEventButton').show();
                $('#comment').prop('readonly', true); 
                $('#saveCommentButton').hide();
                $('#saveStatusButton').hide(); 
            }
            // Γεμίζει τη λίστα των σχολίων
            var commentsHtml = '';
            if (event.comments) {
                event.comments.forEach(function(comment) {
                    commentsHtml += '<p><strong>' + comment.username + ':</strong> ' + comment.comment + ' <em>(' + comment.comment_time + ')</em></p>';
                });
            }
            $('#commentsList').html(commentsHtml);

            $('#status').val(event.status[currentUserId] || 'pending'); 

            $('#myModal').modal('show');
        }
    });


    $('#eventForm').on('submit', function(e) {
        var startTime = new Date($('#startTime').val());
        var endTime = new Date($('#endTime').val());

        if (endTime <= startTime) {
            alert('End time must be after start time.');
            e.preventDefault();
        }
    });
});

function deleteEvent() {
    var eventId = $('#eventId').val();
    if (eventId) {
        $('#deleteEventId').val(eventId);
        $('#deleteForm').submit();
    }
}



</script>

<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create or Edit Appointment</h4>
            </div>
            <form id="eventForm" method="post">
                <div class="modal-body">
                    <input type="hidden" id="eventId" name="eventId">
                    <div class="form-group">
                        <label for="eventTitle">Appointment Title:</label>
                        <input type="text" class="form-control" id="eventTitle" placeholder="Enter event title" required name="name">
                    </div>
                    <div class="form-group">
                        <label for="startTime">Start Time:</label>
                        <input type="datetime-local" name="start" id="startTime" required class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label for="endTime">End Time:</label>
                        <input type="datetime-local" id="endTime" required name="end" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label for="participantInput">Add Participants (Emails/Usernames, comma-separated):</label>
                        <input type="text" class="form-control" id="participantInput" name="participantInput" placeholder="Enter emails or usernames">
                    </div>
                    <div class="form-group">
                        <label for="creatorUsername">Creator:</label>
                        <p id="creatorUsername" class="well"></p>
                    </div>
                    <div class="form-group">
                        <label for="participantsList">Participants:</label>
                        <div id="participantsList" class="well"></div>
                    </div>
                    <div class="form-group">
                        <label for="status">Will you attend?</label>
                        <select class="form-control" id="status" name="status">
                            <option value="pending">Pending</option>
                            <option value="attending">Attending</option>
                            <option value="not_attending">Not Attending</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Add a Comment:</label>
                        <textarea class="form-control" id="comment" name="comment" placeholder="Enter your comment"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="commentsList">Comments:</label>
                        <div id="commentsList" class="well"></div>
                    </div>
                    <div class="form-group">
                        <label for="participantsStatusList">Participants Status:</label>
                        <table class="table table-bordered" id="participantsStatusList">
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" id="saveEventButton" value="Save Event" name="submit">
                    <input type="submit" class="btn btn-primary" id="saveCommentButton" value="Save Comment" name="saveComment">
                    <input type="submit" class="btn btn-primary" id="saveStatusButton" value="Save Status" name="saveStatus">
                    <button type="button" class="btn btn-danger" id="deleteEventButton" onclick="deleteEvent()">Delete</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
            <form id="deleteForm" method="post" style="display:none;">
                <input type="hidden" id="deleteEventId" name="eventId">
                <input type="hidden" name="delete" value="Delete Event">
            </form>
        </div>
    </div>
</div>


<script>

function deleteEvent() {
    var eventId = $('#eventId').val();
    if (eventId) {
        $('#deleteEventId').val(eventId);
        $('#deleteForm').submit();
    }
}
</script>