<?php

echo "<a href='index.php'>Return to Main Hub</a>";

include 'db_config.php';

//check if connection failed
if ($db_connect->connect_error) {
    die("Connection Failed: " . $db_connect->connect_error);
}
$sql = "SELECT * FROM phyla";
$result = $db_connect->query($sql);

if (!$result) {
    die("Query Failed:" . $db_connect->error);
}

while ($row = $result->fetch_assoc()) {
echo "<h1>" . $row['phylum_name'] . "</h1>";
echo "<p>" . $row['phylum_description'] . "</p>";
echo "<hr>";
}