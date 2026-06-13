<?php

$active = 'fauna';
include 'header.php';
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
<?php if ($type == 'species') { ?>
<div class="faceplate">
    <div class="faceplate-inner">
        <img class="faceplate-img" src="images/<?php echo $taxon['species_image']; ?>" alt="<?php echo $title; ?>">
    </div>
    <div class="faceplate-caption">
<div>
    <div class="faceplate-caption-name"><?php echo $title ?></div>
    <div class="faceplate-caption-sci"><?php echo $taxon['scientific_name'];?></div>
</div>
<div class ="faceplate-caption-meta">
    Illus. SOHS Field Survey<br>
    Fig. <?php echo "DNY-" . str_pad($id, 4, "0", STR_PAD_LEFT); ?>
</div>
</div>
</div>
<?php  } ?>

    <div class="entry-body">

        <div class="entry-main-text">

<?php if ($type != 'species') { ?>
  <?php
   $kids = "SELECT * FROM taxa WHERE parent_taxon_id = $id";
$kids_result = mysqli_query($db_connect, $kids);

if (mysqli_num_rows($kids_result) > 0) {
    echo "<div class='section-heading'>Contains</div>";
while ($row = $kids_result->fetch_assoc()) {
echo "<a href='entry.php?id=" . $row['id'] . "'>" . $row['name'] . "</a><br>";
    }
}

   $species = "SELECT * FROM species WHERE taxon_id = $id";
$species_result = mysqli_query($db_connect, $species);

if (mysqli_num_rows($species_result) > 0) {
    echo "<div class='section-heading'>Species</div>";
while ($row = $species_result->fetch_assoc()) {
echo "<a href='entry.php?type=species&id=" . $row['id'] . "'>" . $row['common_name'] . "</a><br>";
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
                echo "<div class='section-heading'>$label</div>";
                echo "<p class='body-text'>" . $taxon[$column] . "</p>";
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
                    "Thermy"         => ucfirst($taxon['thermy']),
                    "Encounter risk" => ucfirst($taxon['encounter_risk']),
                ];
                foreach ($field as $key => $value) {
                    echo "<div class='info-row'><span class='info-key'>$key</span><span class='info-value'>$value</span></div>";
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


        </div>
    </div>

<?php include 'footer.php'; ?>