<?php

$active = 'taxa';
include 'header.php';
include 'db_config.php';
include 'phylum-ledger.php';

if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}
?>

<div id="fauna"><?php render_phylum_ledger($db_connect, 'fauna', 'Fauna'); ?></div>
<div id="flora" style="margin-top:28px;"><?php render_phylum_ledger($db_connect, 'flora', 'Flora'); ?></div>
<div id="tapetae" style="margin-top:28px;"><?php render_phylum_ledger($db_connect, 'tapetae', 'Tapetae'); ?></div>

<div style="margin-top:16px;"><a class="edit-link" href="cladogram.php">View the full cladogram &rarr;</a></div>

<?php include 'footer.php'; ?>
