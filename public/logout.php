<?php
session_start();
session_destroy();
header("Location: http://localhost/AgendaApp/public/homepage.html");
exit();
?>
