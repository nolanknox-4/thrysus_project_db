<?php

$active = 'plates';
include 'db_config.php';
include 'site-text.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// a link to a plate that doesn't exist answers 404 (has to be set before header.php prints anything)
if ($id > 0 && $db_connect->execute_query("SELECT 1 FROM plates WHERE id = ?", [$id])->num_rows === 0) {
    http_response_code(404);
}

include 'header.php';

if ($id > 0) {

    // ---- single plate: the narrative "tour" view ----

    $plate = $db_connect->execute_query(
        "SELECT p.*, e.era_name, e.colour_hex AS era_colour, b.name AS biome_name
         FROM plates p
         LEFT JOIN geological_eras e ON e.id = p.era_id
         LEFT JOIN biomes b ON b.id = p.biome_id
         WHERE p.id = ?",
        [$id]
    )->fetch_assoc();

    if (!$plate) {
        echo "<div class='entry-main-text'><p>Plate not found.</p></div>";
        include 'footer.php';
        exit;
    }

    // resolve each subject row to a name/link/blurb from its own table
    // ('unlinked' subjects have no row to resolve — they're just a typed label, no link)
    $subjects = $db_connect->execute_query("SELECT subject_type, subject_id, label, caption_note, x_pct, y_pct FROM plate_subjects WHERE plate_id = ?", [$id]);
    $roster = [];
    $marker_num = 0;
    while ($row = $subjects->fetch_assoc()) {
        $has_coords = $row['x_pct'] !== null && $row['y_pct'] !== null;
        $marker = $has_coords ? ++$marker_num : null;

        if ($row['subject_type'] === 'species') {
            $s = $db_connect->execute_query("SELECT id, common_name, scientific_name, species_description FROM species WHERE id = ?", [$row['subject_id']])->fetch_assoc();
            if ($s) {
                $roster[] = [
                    'link' => 'entry.php?type=species&id=' . $s['id'],
                    'name' => $s['common_name'],
                    'sub'  => $s['scientific_name'],
                    'desc' => $row['caption_note'] ?: $s['species_description'],
                    'x' => $row['x_pct'], 'y' => $row['y_pct'], 'marker' => $marker,
                ];
            }
        } elseif ($row['subject_type'] === 'taxon') {
            $t = $db_connect->execute_query("SELECT id, name, rank, description FROM taxa WHERE id = ?", [$row['subject_id']])->fetch_assoc();
            if ($t) {
                $roster[] = [
                    'link' => 'entry.php?id=' . $t['id'],
                    'name' => $t['name'],
                    'sub'  => $t['rank'] ? ucfirst($t['rank']) : 'Clade',
                    'desc' => $row['caption_note'] ?: $t['description'],
                    'x' => $row['x_pct'], 'y' => $row['y_pct'], 'marker' => $marker,
                ];
            }
        } else {
            $roster[] = [
                'link' => null,
                'name' => $row['label'],
                'sub'  => '',
                'desc' => $row['caption_note'] ?: '',
                'x' => $row['x_pct'], 'y' => $row['y_pct'], 'marker' => $marker,
            ];
        }
    }
    ?>

    <div class="entry-header">
        <div class="entry-breadcrumb"><a href="plates.php">Plates</a> &rsaquo; <?php echo htmlspecialchars($plate['title']); ?></div>
        <h1 class="entry-title"><?php echo htmlspecialchars($plate['title']); ?></h1>
        <div class="entry-tags">
            <?php if (!empty($plate['biome_name'])) { ?>
                <a class="entry-tag" href="plates.php#biome-<?php echo (int) $plate['biome_id']; ?>"><?php echo htmlspecialchars($plate['biome_name']); ?></a>
            <?php } ?>
            <?php if (!empty($plate['era_name'])) { ?>
                <div class="entry-tag"><?php echo htmlspecialchars($plate['era_name']); ?></div>
            <?php } ?>
            <?php if (!empty($plate['location'])) { ?>
                <div class="entry-tag class"><?php echo htmlspecialchars($plate['location']); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="faceplate">
        <div class="faceplate-inner">
            <div class="hotspot-stage">
                <img class="faceplate-img" src="images/<?php echo htmlspecialchars($plate['image']); ?>" alt="<?php echo htmlspecialchars($plate['title']); ?>">
                <?php foreach ($roster as $r) { if ($r['marker'] === null) continue; ?>
                    <?php if ($r['link']) { ?>
                        <a class="hotspot-marker hotspot-marker-numbered" style="left:<?php echo htmlspecialchars($r['x']); ?>%;top:<?php echo htmlspecialchars($r['y']); ?>%;" href="<?php echo $r['link']; ?>" title="<?php echo htmlspecialchars($r['name']); ?>"><?php echo $r['marker']; ?></a>
                    <?php } else { ?>
                        <span class="hotspot-marker hotspot-marker-numbered" style="left:<?php echo htmlspecialchars($r['x']); ?>%;top:<?php echo htmlspecialchars($r['y']); ?>%;" title="<?php echo htmlspecialchars($r['name']); ?>"><?php echo $r['marker']; ?></span>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="faceplate-caption faceplate-caption-slim">
            <div class="plate-credit-inline"><?php echo count($roster); ?> subject<?php echo count($roster) === 1 ? '' : 's'; ?> pictured</div>
            <?php if (!empty($plate['location'])) { ?>
                <div class="faceplate-caption-meta"><?php echo htmlspecialchars($plate['location']); ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="entry-body">
        <div class="entry-main-text">
            <div class="entry-lede">
                <p><?php echo $plate['tour_text']; ?></p>
            </div>

            <?php
            // plate info as one quiet closing line, same as entry pages
            $meta = ["DNY-P" . str_pad($id, 4, "0", STR_PAD_LEFT)];
            if (!empty($plate['first_described'])) $meta[] = 'First described ' . htmlspecialchars($plate['first_described']);
            if (!empty($plate['lead']))            $meta[] = 'Lead ' . htmlspecialchars($plate['lead']);
            if (!empty($plate['last_edited']))     $meta[] = 'Edited ' . htmlspecialchars($plate['last_edited']);
            ?>
            <div class="entry-meta">
                <?php echo implode(' · ', $meta); ?>
                <?php if (!empty($_SESSION['authed'])) { ?>
                    <a class="edit-link" href="admin.php?type=plate&id=<?php echo $id; ?>">Edit &rarr;</a>
                <?php } ?>
            </div>
        </div>

        <div class="entry-sidebar">
            <div class="info-box">
                <div class="info-box-header">Pictured In This Plate</div>
                <?php foreach ($roster as $r) {
                    $prefix = $r['marker'] !== null ? $r['marker'] . '. ' : '';
                ?>
                    <?php if ($r['link']) { ?>
                        <a class="entry-child-link" href="<?php echo $r['link']; ?>"><?php echo $prefix . htmlspecialchars($r['name']); ?></a><br>
                    <?php } else { ?>
                        <span class="entry-child-link entry-child-unlinked"><?php echo $prefix . htmlspecialchars($r['name']); ?></span><br>
                    <?php } ?>
                <?php } ?>
                <?php if (empty($roster)) { ?>
                    <div class="info-row"><span class="info-key">—</span><span class="info-value">No subjects tagged yet</span></div>
                <?php } ?>
            </div>
        </div>
    </div>

<?php
} else {

    // ---- plate index: grouped by biome (Dixon-chapter style), folded in from biomes.php ----

    $biomes = $db_connect->execute_query("SELECT * FROM biomes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    $total = $db_connect->execute_query("SELECT COUNT(*) AS c FROM plates")->fetch_assoc()['c'];
    include 'plate-row.php';
    ?>

    <div class="fauna-head">
        <div class="fauna-title">Plates</div>
        <div class="fauna-sub">Ecosystem Illustrations, by Biome</div>
        <div class="fauna-count"><?php echo (int) $total; ?> plates catalogued</div>
    </div>

    <?php
    $any_shown = false;
    foreach ($biomes as $biome) {
        $biome_plates = $db_connect->execute_query(
            "SELECT p.*, e.era_name FROM plates p LEFT JOIN geological_eras e ON e.id = p.era_id WHERE p.biome_id = ? ORDER BY p.id DESC",
            [$biome['id']]
        )->fetch_all(MYSQLI_ASSOC);
        if (empty($biome_plates)) {
            continue;
        }
        $any_shown = true;
        ?>
        <div id="biome-<?php echo $biome['id']; ?>" class="plate-biome-group">
            <div class="section-heading"><?php echo htmlspecialchars($biome['name']); ?></div>
            <?php // saves straight into biomes.description when edited in place
            $biome_text = $biome['description'] ?? '';
            echo "<div class='editable'" . edit_attrs('biome:' . (int) $biome['id'], $biome_text, 'Blank line = new paragraph') . ">";
            echo trim($biome_text) !== '' ? text_to_html($biome_text) : (is_editor() ? "<p class='body-text placeholder'>To be written</p>" : '');
            echo "</div>"; ?>
            <div class="plate-grid">
                <?php foreach ($biome_plates as $row) {
                    render_plate_card($db_connect, $row, $biome['colour_hex'] ?? null);
                } ?>
            </div>
        </div>
    <?php } ?>

    <?php
    $unsorted = $db_connect->execute_query(
        "SELECT p.*, e.era_name FROM plates p LEFT JOIN geological_eras e ON e.id = p.era_id WHERE p.biome_id IS NULL ORDER BY p.id DESC"
    )->fetch_all(MYSQLI_ASSOC);
    if (!empty($unsorted)) {
        $any_shown = true;
        ?>
        <div class="plate-biome-group">
            <div class="section-heading">Unsorted</div>
            <div class="plate-grid">
                <?php foreach ($unsorted as $row) {
                    render_plate_card($db_connect, $row);
                } ?>
            </div>
        </div>
    <?php } ?>

    <?php if (!$any_shown) { ?>
        <div class="phylum-index">
            <div class="phylum-row" style="cursor:default;">
                <div class="phylum-body">
                    <div class="phylum-desc">No plates catalogued yet. Add one from the admin panel.</div>
                </div>
            </div>
        </div>
    <?php } ?>

<?php
}

include 'footer.php';
?>
