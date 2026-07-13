<?php
$active = '';
//results
include 'header.php';
include 'db_config.php';

$q = $_GET['q'] ?? ''; //text user typed
//if nothing
?>

<div class="search-page" style="padding:20px;">
    <h2>Search Results for "<?php echo htmlspecialchars($q); ?>"</h2>
<?php
if ($q !== '') {
    $like = "%" . $q . "%";
    $found = false;

    $result = $db_connect->execute_query("SELECT id, name, description from taxa WHERE name LIKE ?", [$like]);
    while ($row = $result->fetch_assoc()) {
        echo "<a class='search-result' href='entry.php?id=" . $row['id'] . "'>";
        echo "<span class='search-result-text'>";
        echo "<span class='search-result-name'>" . htmlspecialchars($row['name']) . "</span>";
        echo "<span class='search-result-desc'>" . htmlspecialchars($row['description']) . "</span>";
        echo "</span>";
        echo "</a>";
        $found = true;
    }

    $species_result = $db_connect->execute_query("SELECT id, common_name, species_description, species_image FROM species WHERE common_name LIKE ? OR scientific_name LIKE ?", [$like, $like]);
    while ($row = $species_result->fetch_assoc()) {
        echo "<a class='search-result' href='entry.php?type=species&id=" . $row['id'] . "'>";
        echo "<img class='search-result-img' src='images/" . $row['species_image'] . "' alt=''>";
        echo "<span class='search-result-text'>";
        echo "<span class='search-result-name'>" . htmlspecialchars($row['common_name']) . "</span>";
        echo "<span class='search-result-desc'>" . htmlspecialchars($row['species_description']) . "</span>";
        echo "</span>";
        echo "</a>";
        $found = true;
    }

    if (!$found) {
        echo "<p>No matches found.</p>";
    }
}
?>

</div>

<?php include 'footer.php'; ?>