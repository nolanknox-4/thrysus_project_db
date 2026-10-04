<?php

$active = 'cladogram';
include 'header.php';
include 'db_config.php';
include 'cladogram-render.php';

if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}
?>

<div class="fauna-head">
    <div class="fauna-title">Cladogram</div>
    <div class="fauna-sub">Full Lineage Index</div>
</div>

<?php
render_cladogram($db_connect, 'fauna', 'Fauna');
render_cladogram($db_connect, 'flora', 'Flora');
render_cladogram($db_connect, 'tapetae', 'Tapetae');

include 'footer.php';
?>
