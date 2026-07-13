<?php
session_start();
$_SESSION = [];        // clear the data
session_destroy();     // end the session
header('Location: login.php');
exit;
