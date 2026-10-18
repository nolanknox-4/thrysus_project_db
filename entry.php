<?php

include 'db_config.php';
$type = $_GET['type'] ?? 'taxon';
$id = (int)($_GET ['id'] ?? 0);

if ($type == 'species') {
    $query = "SELECT * FROM species WHERE id = $id";
} else {
    $query = "SELECT * FROM taxa WHERE id = $id";
}

$result = mysqli_query($db_connect, $query);
$taxon = $result->fetch_assoc();

// every kingdom lives under the one Taxa tab now
$active = 'taxa';

// a link to an entry that doesn't exist answers 404 (has to be set before header.php prints anything)
if (!$taxon) {
    http_response_code(404);
}

include 'header.php';

if (!$taxon) {
    echo "<div class='entry-main-text'><p class='body-text'>Entry not found. <a class='edit-link' href='taxa.php'>Browse the taxa &rarr;</a></p></div>";
    include 'footer.php';
    exit;
}

// archive code: T for taxa, S for species (P is plates, W the world figures), so ids never collide
$entry_code = 'DNY-' . ($type == 'species' ? 'S' : 'T') . str_pad($id, 4, "0", STR_PAD_LEFT);

$title = ($type == 'species') ? $taxon['common_name'] : $taxon['name'];
$lede = ( $type == 'species') ? $taxon['species_description'] : $taxon['description'];

if ($type == 'species') {
    $ranklabel = 'Species';
} else {
    $ranklabel = $taxon['rank']? ucfirst($taxon['rank']) : 'Clade';
}

$crumbs = [];
if ($type == 'species') {
    $tid = (int)$taxon['taxon_id'];
    $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = $tid")->fetch_assoc();
} else {
    $walker = $taxon;
}

while ($walker) {
    $crumbs[] = $walker;

    if ($walker['parent_taxon_id']) {
        $pid = (int)$walker['parent_taxon_id'];
        $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = $pid")->fetch_assoc();
    } else {
        $walker = null;
    }
}

$crumbs = array_reverse($crumbs);

// Plate fallback: if this subject has no image of its own, borrow one from a plate it appears in.
// Even when it has its own image, surface the plate as a cross-link ("also pictured in").
$plate_subject_type = ($type == 'species') ? 'species' : 'taxon';
$plate_match = $db_connect->execute_query(
    "SELECT p.* FROM plates p JOIN plate_subjects ps ON ps.plate_id = p.id WHERE ps.subject_type = ? AND ps.subject_id = ? ORDER BY p.id LIMIT 1",
    [$plate_subject_type, $id]
)->fetch_assoc();

$own_image = ($type == 'species') ? ($taxon['species_image'] ?? '') : ($taxon['image'] ?? '');
$faceplate_image = $own_image !== '' ? $own_image : ($plate_match['image'] ?? '');

// other subjects sharing that same plate, for the "pictured together" sidebar box
$castmates = [];
if ($plate_match) {
    $cm_result = $db_connect->execute_query(
        "SELECT subject_type, subject_id, label FROM plate_subjects WHERE plate_id = ? AND NOT (subject_type = ? AND subject_id = ?)",
        [$plate_match['id'], $plate_subject_type, $id]
    );
    while ($cm = $cm_result->fetch_assoc()) {
        if ($cm['subject_type'] === 'species') {
            $cs = $db_connect->execute_query("SELECT id, common_name FROM species WHERE id = ?", [$cm['subject_id']])->fetch_assoc();
            if ($cs) {
                $castmates[] = ['link' => 'entry.php?type=species&id=' . $cs['id'], 'name' => $cs['common_name']];
            }
        } elseif ($cm['subject_type'] === 'taxon') {
            $ct = $db_connect->execute_query("SELECT id, name FROM taxa WHERE id = ?", [$cm['subject_id']])->fetch_assoc();
            if ($ct) {
                $castmates[] = ['link' => 'entry.php?id=' . $ct['id'], 'name' => $ct['name']];
            }
        } else {
            $castmates[] = ['link' => null, 'name' => $cm['label']];
        }
    }
}

?>


    <div class="entry-header">
    <div class= "entry-breadcrumb"> 
    <?php
    $links = [];
    foreach ($crumbs as $c) {
        $links[] = "<a href='entry.php?id=" . (int) $c['id'] . "'>" . htmlspecialchars($c['name']) . "</a>";
    }

    echo implode(' › ', $links);
    ?>
    </div>

    <!-- the page's actual name: previously only shown in the faceplate caption, so imageless entries had no title -->
    <h1 class="entry-title"><?php echo htmlspecialchars($title); ?></h1>
    <?php if ($type == 'species' && !empty($taxon['scientific_name'])) { ?>
        <div class="entry-sci"><?php echo htmlspecialchars($taxon['scientific_name']); ?></div>
    <?php } ?>

    <div class="entry-tags">
        <?php if (!empty($taxon['status'])) { ?>
            <div class="entry-tag <?php echo htmlspecialchars($taxon['status']); ?>"><?php echo htmlspecialchars(ucfirst($taxon['status'])); ?></div>
        <?php } ?>
        <div class="entry-tag class"><?php echo htmlspecialchars($ranklabel); ?></div>
    </div>


</div>
<?php if (!empty($faceplate_image)) { ?>
<div class="faceplate">
    <div class="faceplate-inner">
        <img class="faceplate-img" src="images/<?php echo htmlspecialchars($faceplate_image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
    </div>
    <?php if ($plate_match) { ?>
    <!-- caption only when it says something useful: which plate this picture comes from -->
    <div class="faceplate-caption faceplate-caption-slim">
        <div class="plate-credit-inline">
            <?php echo ($own_image === '') ? 'Pictured in' : 'Also pictured in'; ?>
            <a href="plates.php?id=<?php echo (int) $plate_match['id']; ?>"><?php echo htmlspecialchars($plate_match['title']); ?></a>
        </div>
    </div>
    <?php } ?>
</div>
<?php } ?>

<?php
// sidebar boxes: only the filled-in field data, and who shares this entry's plate.
// (classification lives in the breadcrumb at the top.) with neither, the article takes the full width
$field = ($type == 'species') ? array_filter([
    "Body length"    => $taxon['body_length'] ?? '',
    "Mass"           => $taxon['mass'] ?? '',
    "Thermy"         => ucfirst($taxon['thermy'] ?? ''),
    "Encounter risk" => ucfirst($taxon['encounter_risk'] ?? ''),
]) : [];
$has_sidebar = !empty($field) || !empty($castmates);
?>

    <div class="entry-body<?php echo $has_sidebar ? '' : ' entry-body-full'; ?>">

        <div class="entry-main-text">

            <div class="entry-lede">
                <p><?php echo $lede; ?></p>
            </div>

            <?php
            $sections = [
                'body_plan' => 'Body plan',
                'appearance' => 'Appearance',
                'behaviour' => 'Behaviour',
                'habitat_and_distribution' => 'Habitat & distribution',
                'evolutionary_history' => 'Evolutionary history',
            ];

            $written = 0;
            foreach ($sections as $column => $label) {
                if (!empty($taxon[$column])) {
                    $written++;
                    echo "<div class='section-heading'>$label</div>";
                    echo "<p class='body-text'>" . $taxon[$column] . "</p>";
                }
            }
            // only the short description so far: say so, rather than leave a blank-looking page.
            // a taxon that's mostly an index of its members (a phylum, say) isn't a stub, so skip those
            $has_children = $type != 'species' && (
                $db_connect->execute_query("SELECT 1 FROM taxa WHERE parent_taxon_id = ? LIMIT 1", [$id])->num_rows
                || $db_connect->execute_query("SELECT 1 FROM species WHERE taxon_id = ? LIMIT 1", [$id])->num_rows
            );
            if ($written === 0 && !$has_children) {
                echo "<p class='entry-stub'>This entry is a stub. Its full write-up hasn't been added yet.</p>";
            }
            ?>

<?php if ($type != 'species') {
    // child navigation sits after the article, so the page reads as an article first and an index second
    $children = [
        'Contains' => $db_connect->execute_query("SELECT id, name, rank FROM taxa WHERE parent_taxon_id = ? ORDER BY name", [$id]),
        'Species'  => $db_connect->execute_query("SELECT id, common_name, scientific_name FROM species WHERE taxon_id = ? ORDER BY common_name", [$id]),
    ];
    foreach ($children as $heading => $child_result) {
        if ($child_result->num_rows === 0) {
            continue;
        }
        echo "<div class='section-heading'>" . $heading . "</div>";
        while ($row = $child_result->fetch_assoc()) {
            if ($heading === 'Species') {
                $href = 'entry.php?type=species&id=' . (int) $row['id'];
                $name = $row['common_name'];
            } else {
                $href = 'entry.php?id=' . (int) $row['id'];
                $name = $row['name'];
            }
            echo "<a class='entry-child-link' href='" . $href . "'>" . htmlspecialchars($name) . "</a><br>";
        }
    }
} ?>

            <?php
            // article info, demoted from a sidebar box to one quiet line at the end of the article
            $meta = [$entry_code];
            if (!empty($taxon['first_described'])) $meta[] = 'First described ' . htmlspecialchars($taxon['first_described']);
            if (!empty($taxon['lead']))            $meta[] = 'Lead ' . htmlspecialchars($taxon['lead']);
            if (!empty($taxon['last_edited']))     $meta[] = 'Edited ' . htmlspecialchars($taxon['last_edited']);
            ?>
            <div class="entry-meta">
                <?php echo implode(' · ', $meta); ?>
                <?php if (!empty($_SESSION['authed'])) { ?>
                    <a class="edit-link" href="admin.php?type=<?php echo $type == 'species' ? 'species' : 'taxon'; ?>&id=<?php echo $id; ?>">Edit &rarr;</a>
                <?php } ?>
            </div>

        </div>

        <?php if ($has_sidebar) { ?>
        <div class="entry-sidebar">
            <?php if (!empty($field)) { ?>
            <div class="info-box">
                <div class="info-box-header">Field Data</div>
                <?php foreach ($field as $key => $value) {
                    echo "<div class='info-row'><span class='info-key'>$key</span><span class='info-value'>" . htmlspecialchars($value) . "</span></div>";
                } ?>
            </div>
            <?php } ?>

            <?php if (!empty($castmates)) { ?>
            <div class="info-box">
                <div class="info-box-header">Pictured Together</div>
                <?php foreach ($castmates as $cm) { ?>
                    <?php if ($cm['link']) { ?>
                        <a class="entry-child-link" href="<?php echo $cm['link']; ?>"><?php echo htmlspecialchars($cm['name']); ?></a><br>
                    <?php } else { ?>
                        <span class="entry-child-link entry-child-unlinked"><?php echo htmlspecialchars($cm['name']); ?></span><br>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php } ?>

        </div>
        <?php } ?>
    </div>

<?php include 'footer.php'; ?>