

<?php

$active = 'fauna';
include 'header.php';
include 'db_config.php';

// check connection before querying
if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}

$query = "SELECT * FROM taxa WHERE rank = 'phylum' AND category = 'fauna'";
$result = mysqli_query($db_connect, $query);

if (!$result) {
    die("Query Failed:" . $db_connect->error);
}

echo "<div class='fauna-head'>";
echo "<div class='fauna-title'>Fauna</div>";
echo "<div class='fauna-sub'>Phylum Index</div>";
echo "<div class='fauna-count'>" . mysqli_num_rows($result) . " phyla catalogued</div>";
echo "</div>";

echo "<div class='phylum-index'>";

while ($row = $result->fetch_assoc()) {
    echo "<a href='entry.php?id=" . $row['id'] . "' class='phylum-row'>";
    echo "<div class='phylum-spine' style='background:" . $row['colour_hex'] . ";'></div>";
    echo "<div class='phylum-body'>";
    echo "<div class='phylum-name'>" . $row['name'] . "</div>";
    echo "<div class='phylum-code'>DNY-" . str_pad($row['id'], 4, "0", STR_PAD_LEFT) . "</div>";
    echo "<div class='phylum-desc'>" . $row['description'] . "</div>";
    echo "</div>";
    echo "<span class='phylum-cue'>Access record &rarr;</span>";
    echo "</a>";
}

echo "</div>";

include 'footer.php';
