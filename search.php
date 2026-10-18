<?php
$active = '';
//results
include 'header.php';
include 'db_config.php';

$q = trim($_GET['q'] ?? ''); //text user typed

// a species/taxon with no image of its own borrows one from a plate it's tagged in (same rule as entry.php)
$plate_image_sql = "(SELECT p.image FROM plates p JOIN plate_subjects ps ON ps.plate_id = p.id
                     WHERE ps.subject_type = ? AND ps.subject_id = %s ORDER BY p.id LIMIT 1)";

// descriptions are rich text (HTML from the editor): strip the tags and trim to a one-line teaser
function teaser($html)
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html ?? '')));
    return mb_strlen($text) > 180 ? mb_substr($text, 0, 180) . '…' : $text;
}

function search_row($href, $image, $name, $desc)
{
    echo "<a class='search-result' href='" . $href . "'>";
    if (!empty($image)) {
        echo "<img class='search-result-img' src='images/" . htmlspecialchars($image) . "' alt=''>";
    }
    echo "<span class='search-result-text'>";
    echo "<span class='search-result-name'>" . htmlspecialchars($name) . "</span>";
    echo "<span class='search-result-desc'>" . htmlspecialchars(teaser($desc)) . "</span>";
    echo "</span>";
    echo "</a>";
}
?>

<div class="search-page">
    <h2>Search Results for "<?php echo htmlspecialchars($q); ?>"</h2>
<?php
if ($q !== '') {
    $like = "%" . $q . "%";
    $found = false;

    $result = $db_connect->execute_query(
        "SELECT t.id, t.name, t.description, COALESCE(NULLIF(t.image, ''), " . sprintf($plate_image_sql, 't.id') . ") AS shown_image
         FROM taxa t WHERE t.name LIKE ? ORDER BY t.name",
        ['taxon', $like]
    );
    while ($row = $result->fetch_assoc()) {
        search_row('entry.php?id=' . (int) $row['id'], $row['shown_image'], $row['name'], $row['description']);
        $found = true;
    }

    $species_result = $db_connect->execute_query(
        "SELECT s.id, s.common_name, s.species_description, COALESCE(NULLIF(s.species_image, ''), " . sprintf($plate_image_sql, 's.id') . ") AS shown_image
         FROM species s WHERE s.common_name LIKE ? OR s.scientific_name LIKE ? ORDER BY s.common_name",
        ['species', $like, $like]
    );
    while ($row = $species_result->fetch_assoc()) {
        search_row('entry.php?type=species&id=' . (int) $row['id'], $row['shown_image'], $row['common_name'], $row['species_description']);
        $found = true;
    }

    $plate_result = $db_connect->execute_query(
        "SELECT id, title, tour_text, image FROM plates WHERE title LIKE ? OR location LIKE ? ORDER BY title",
        [$like, $like]
    );
    while ($row = $plate_result->fetch_assoc()) {
        search_row('plates.php?id=' . (int) $row['id'], $row['image'], $row['title'], $row['tour_text']);
        $found = true;
    }

    if (!$found) {
        echo "<p>No matches found.</p>";
    }
}
?>

</div>

<?php include 'footer.php'; ?>
