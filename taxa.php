<?php

$active = 'taxa';
include 'header.php';
include 'db_config.php';
include 'phylum-ledger.php';
include 'kingdoms.php';

$kingdoms = kingdom_labels($db_connect);
?>

<?php
$first = true;
foreach ($kingdoms as $category => $name) { ?>
    <div id="<?php echo $category; ?>"<?php echo $first ? '' : ' style="margin-top:28px;"'; ?>>
        <?php render_phylum_ledger($db_connect, $category, $name, KINGDOM_GLOSS[$category]); ?>
    </div>
<?php
    $first = false;
} ?>

<div style="margin-top:16px;"><a class="edit-link" href="cladogram.php">View the full cladogram &rarr;</a></div>

<?php include 'footer.php'; ?>
