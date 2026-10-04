<?php
// search-suggest.php — JSON endpoint for the live autocomplete dropdown in header.php.
// Returns a handful of matches across taxa/species/plates/biomes; search.php (the full
// reload-on-submit results page) stays untouched as the no-JS fallback.

include 'db_config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}
$like = '%' . $q . '%';
$results = [];

$taxa = $db_connect->execute_query("SELECT id, name, rank FROM taxa WHERE name LIKE ? LIMIT 5", [$like]);
while ($row = $taxa->fetch_assoc()) {
    $results[] = [
        'type'  => 'Taxon',
        'label' => $row['name'],
        'sub'   => $row['rank'] ? ucfirst($row['rank']) : 'Clade',
        'link'  => 'entry.php?id=' . $row['id'],
    ];
}

$species = $db_connect->execute_query(
    "SELECT id, common_name, scientific_name FROM species WHERE common_name LIKE ? OR scientific_name LIKE ? LIMIT 5",
    [$like, $like]
);
while ($row = $species->fetch_assoc()) {
    $results[] = [
        'type'  => 'Species',
        'label' => $row['common_name'],
        'sub'   => $row['scientific_name'],
        'link'  => 'entry.php?type=species&id=' . $row['id'],
    ];
}

$plates = $db_connect->execute_query("SELECT id, title FROM plates WHERE title LIKE ? LIMIT 5", [$like]);
while ($row = $plates->fetch_assoc()) {
    $results[] = [
        'type'  => 'Plate',
        'label' => $row['title'],
        'sub'   => 'Illustration',
        'link'  => 'plates.php?id=' . $row['id'],
    ];
}

$biomes = $db_connect->execute_query("SELECT id, name FROM biomes WHERE name LIKE ? LIMIT 5", [$like]);
while ($row = $biomes->fetch_assoc()) {
    $results[] = [
        'type'  => 'Biome',
        'label' => $row['name'],
        'sub'   => 'Habitat',
        'link'  => 'plates.php#biome-' . $row['id'],
    ];
}

echo json_encode(array_slice($results, 0, 8));
