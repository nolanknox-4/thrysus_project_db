<?php

$active = 'cladogram';
include 'header.php';
include 'db_config.php';
include 'cladogram-render.php';
include 'kingdoms.php';

if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}
?>

<div class="fauna-head">
    <div class="fauna-title">Cladogram</div>
    <div class="fauna-sub">Full Lineage Index</div>
</div>

<?php
// each panel's tree already starts at its kingdom's own name, so the panel header
// carries the plain-English gloss (Animals / Plants / Decomposers) instead of repeating it
foreach (KINGDOM_GLOSS as $category => $gloss) {
    render_cladogram($db_connect, $category, $gloss);
}

include 'footer.php';
?>
