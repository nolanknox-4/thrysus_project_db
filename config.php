<?php

//db.config stuff
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'MINE'; // my password
$db_name = 'thrysus_project_db';

$db_connect = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db_connect->connect_error); {
    die('Connection Failed:' . $db_connect->connect_error);
}
?>