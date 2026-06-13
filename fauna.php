

<?php

$active = 'fauna';
include 'header.php';
include 'db_config.php';

// check connection before querying
if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}

$query = "SELECT * FROM taxa WHERE rank = 'phylum'";
$result = mysqli_query($db_connect, $query);

if (!$result) {
    die("Query Failed:" . $db_connect->error);
}

while ($row = $result->fetch_assoc()) {
echo "<div class='phylum-card'>";
echo "<div class='phylum-dot' style='background:" . $row['colour_hex'] . ";'></div>";
echo "<a href='entry.php?id=" . $row['id'] .  "'>"  .  $row['name'] . "</a>";
echo "<p>" . $row['description'] . "</p>"; 
echo "</div>";
}

include 'footer.php';
