<?php
require_once __DIR__ . '/session.php';
// logout is a plain link (GET), so check the token in the URL: otherwise any other site
// could log you out just by embedding this page
if (!hash_equals($_SESSION['csrf'], $_GET['t'] ?? '')) {
    header('Location: admin.php');
    exit;
}
$_SESSION = [];        // clear the data
session_destroy();     // end the session
header('Location: login.php');
exit;
