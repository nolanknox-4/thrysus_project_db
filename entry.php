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

// Highlight the nav section from the record's category so flora entries stop lighting up the Fauna tab.
if ($type == 'species') {
    $parent_tid = (int)($taxon['taxon_id'] ?? 0);
    $cat_row = mysqli_query($db_connect, "SELECT category FROM taxa WHERE id = $parent_tid")->fetch_assoc();
    $active = $cat_row['category'] ?? 'fauna';
} else {
    $active = $taxon['category'] ?? 'fauna';
}

include 'header.php';
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
        $walker = $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = $pid")->fetch_assoc();
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
        $links[] = "<a href='entry.php?id=" . $c['id'] . "'>" . $c['name'] . "</a>";
    }

    echo implode(' › ', $links);
    ?>
    </div>

    <div class="entry-tags">
        <div class="entry-tag <?php echo $taxon['status']; ?>"><?php echo ucfirst($taxon['status']); ?></div>
        <div class="entry-tag class"><?php echo $ranklabel; ?></div>
    </div>


</div>
<?php if (!empty($faceplate_image)) { ?>
<div class="faceplate">
    <div class="faceplate-inner">
        <img class="faceplate-img" src="images/<?php echo htmlspecialchars($faceplate_image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
    </div>
    <div class="faceplate-caption">
<div>
    <div class="faceplate-caption-name"><?php echo $title ?></div>
    <div class="faceplate-caption-sci"><?php echo ($type == 'species') ? $taxon['scientific_name'] : $ranklabel; ?></div>
</div>
<div class ="faceplate-caption-meta">
    Illus. SOHS Field Survey<br>
    Fig. <?php echo "DNY-" . str_pad($id, 4, "0", STR_PAD_LEFT); ?>
</div>
</div>
</div>
<?php if ($plate_match) { ?>
<div class="plate-credit">
    <?php echo ($own_image === '') ? 'Pictured in' : 'Also pictured in'; ?>
    <a href="plates.php?id=<?php echo $plate_match['id']; ?>"><?php echo htmlspecialchars($plate_match['title']); ?></a>
</div>
<?php } ?>
<?php } ?>

    <div class="entry-body">

        <div class="entry-main-text">

<?php if ($type != 'species') { ?>
  <?php
   $kids = "SELECT * FROM taxa WHERE parent_taxon_id = $id";
$kids_result = mysqli_query($db_connect, $kids);

if (mysqli_num_rows($kids_result) > 0) {
    echo "<div class='section-heading'>Contains</div>";
while ($row = $kids_result->fetch_assoc()) {
echo "<a class='entry-child-link' href='entry.php?id=" . $row['id'] . "'>" . $row['name'] . "</a><br>";
    }
}

   $species = "SELECT * FROM species WHERE taxon_id = $id";
$species_result = mysqli_query($db_connect, $species);

if (mysqli_num_rows($species_result) > 0) {
    echo "<div class='section-heading'>Species</div>";
while ($row = $species_result->fetch_assoc()) {
echo "<a class='entry-child-link' href='entry.php?type=species&id=" . $row['id'] . "'>" . $row['common_name'] . "</a><br>";
}
}
?>
<?php } ?>

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

            foreach ($sections as $column => $label) {
                if (!empty($taxon[$column])) {
                    echo "<div class='section-heading'>$label</div>";
                    echo "<p class='body-text'>" . $taxon[$column] . "</p>";
                }
            }
            ?>

        </div>

        <div class="entry-sidebar">
            <div class="info-box">
                <div class="info-box-header">Classification</div>
                <?php
                foreach ($crumbs as $c) {
                    $rank = $c['rank'] ? ucfirst($c['rank']) : 'Clade';
                    echo "<div class='info-row'><span class='info-key'>$rank</span><span class='info-value'>" . $c['name'] . "</span></div>";
                }
                ?>
            </div>

<?php if ($type == 'species') { ?>
            <div class="info-box">
                <div class="info-box-header">Field Data</div>
                <?php
                $field = [
                    "Body length"    => $taxon['body_length'],
                    "Mass"           => $taxon['mass'],
                     "Thermy" => ucfirst($taxon['thermy'] ?? ''),
                    "Encounter risk" => ucfirst($taxon['encounter_risk'] ?? ''),
                ];
                foreach ($field as $key => $value) {
                    if (!empty($value)) {
                        echo "<div class='info-row'><span class='info-key'>$key</span><span class='info-value'>$value</span></div>";
                    }
                }
                ?>
            </div>
<?php } ?>

            <div class="info-box">
                <div class="info-box-header">Article Info</div>
                <?php
                $rows = [
                     "Entry" => "DNY-" . str_pad($id, 4 , "0", STR_PAD_LEFT),
                    "First Described" => $taxon['first_described'],
                    "Lead" => $taxon['lead'],
                    "Last Edited" => $taxon['last_edited'],
                ];
                foreach ($rows as $key => $value) {
                    echo "<div class='info-row'><span class='info-key'>$key</span><span class='info-value'>$value</span></div>";
                }
                ?>

            </div>

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

                <a class="edit-link" href="admin.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>">
                    Edit this entry &rarr;
                </a>

        </div>
    </div>

<?php include 'footer.php'; ?>