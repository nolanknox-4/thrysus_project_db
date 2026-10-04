<?php

$active = 'timeline';
include 'header.php';
include 'db_config.php';
include 'plate-row.php';

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
    <div id="era-<?php echo $era['id']; ?>" class="plate-biome-group">
        <div class="section-heading">
            Era <?php echo (int) $era['era_number']; ?> &middot; <?php echo htmlspecialchars($era['era_name']); ?>
        </div>
        <p class="body-text"><?php echo htmlspecialchars($era['era_description'] ?? ''); ?></p>

        <?php if (!empty($era_plates)) { ?>
            <div class="phylum-index">
                <?php foreach ($era_plates as $row) {
                    render_plate_ledger_row($db_connect, $row, $era['colour_hex'] ?? null);
                } ?>
            </div>
        <?php } else { ?>
            <div class="phylum-index">
                <div class="phylum-row" style="cursor:default;">
                    <div class="phylum-body">
                        <div class="phylum-desc">No plates set in this era yet.</div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php include 'footer.php'; ?>
