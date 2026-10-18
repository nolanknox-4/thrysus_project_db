<?php

$active = 'timeline';
include 'header.php';
include 'db_config.php';
include 'site-text.php';

if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}

$eras = $db_connect->execute_query("SELECT * FROM geological_eras ORDER BY era_number")->fetch_all(MYSQLI_ASSOC);
?>

<div class="fauna-head">
    <div class="fauna-title">Timeline</div>
    <div class="fauna-sub">Geological Eras</div>
    <div class="fauna-count"><?php echo count($eras); ?> eras catalogued</div>
</div>

<?php foreach ($eras as $era) {
    $era_plates = $db_connect->execute_query(
        "SELECT p.*, e.era_name FROM plates p JOIN geological_eras e ON e.id = p.era_id WHERE p.era_id = ? ORDER BY p.id DESC",
        [$era['id']]
    )->fetch_all(MYSQLI_ASSOC);
?>
    <!-- each era reads as a chapter: coloured heading, its description, then its plates as plain › links -->
    <div id="era-<?php echo $era['id']; ?>" class="plate-biome-group">
        <div class="section-heading">
            <span class="era-dot" style="background: <?php echo htmlspecialchars($era['colour_hex'] ?? '#9c9080'); ?>;"></span>
            Era <?php echo (int) $era['era_number']; ?> &middot; <?php echo htmlspecialchars($era['era_name']); ?>
        </div>
        <?php // saves straight into geological_eras.era_description when edited in place
        $era_text = $era['era_description'] ?? '';
        echo "<div class='editable'" . edit_attrs('era:' . (int) $era['id'], $era_text, 'Blank line = new paragraph') . ">";
        echo trim($era_text) !== '' ? text_to_html($era_text) : (is_editor() ? "<p class='body-text placeholder'>To be written</p>" : '');
        echo "</div>"; ?>

        <?php if (!empty($era_plates)) { ?>
            <?php foreach ($era_plates as $row) { ?>
                <a class="entry-child-link" href="plates.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a><br>
            <?php } ?>
        <?php } else { ?>
            <span class="entry-child-link entry-child-unlinked">No plates set in this era yet</span>
        <?php } ?>
    </div>
<?php } ?>

<?php include 'footer.php'; ?>
