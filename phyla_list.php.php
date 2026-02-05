<?php

echo "<a href='index.php'>Return to Main Hub</a>";

include 'db_config.php';

//check if connection failed
if ($db_connect->connect_error)

$result = $db_connect->query('SELECT * FROM phyla');
while ($row = $result->fetch_assoc()) {

echo "<h1>"; $row['phylum_name']; "</h1>";
echo "<p>" . $row ['phylum_description'] . "</p>";
echo "<hr>" . $row [""] . "";
}