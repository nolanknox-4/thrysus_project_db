<?php
// search-suggest.php — JSON endpoint for the live autocomplete dropdown in header.php.
// Returns the best few matches across taxa/species/plates/biomes; search.php (the full
// reload-on-submit results page) stays as the no-JS fallback and the "see all" target.

include 'db_config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}
$like = '%' . $q . '%';
$candidates = [];

// how well a name matches what was typed: exact beats starts-with beats a later word
// starting with it beats somewhere-in-the-middle. so "hy" puts Hygrophytes above Thyrsozoa.
function match_score($text, $q)
{
    $t = mb_strtolower($text ?? '');
    $q = mb_strtolower($q);
    if ($t === $q) return 0;
    if (str_starts_with($t, $q)) return 1;
    if (preg_match('/[\s\-]' . preg_quote($q, '/') . '/u', $t)) return 2;
    return 3;
}

function add_candidate(&$list, $q, $type, $label, $sub, $link, $also = '')
{
    // a species can match on its scientific name too; use whichever matches better
    $score = min(match_score($label, $q), $also !== '' ? match_score($also, $q) : 9);
    $list[] = ['type' => $type, 'label' => $label, 'sub' => $sub, 'link' => $link, 'score' => $score];
}

$taxa = $db_connect->execute_query("SELECT id, name, rank FROM taxa WHERE name LIKE ? LIMIT 20", [$like]);
while ($row = $taxa->fetch_assoc()) {
    add_candidate($candidates, $q, 'Taxon', $row['name'], $row['rank'] ? ucfirst($row['rank']) : 'Clade', 'entry.php?id=' . $row['id']);
}

$species = $db_connect->execute_query(
    "SELECT id, common_name, scientific_name FROM species WHERE common_name LIKE ? OR scientific_name LIKE ? LIMIT 20",
    [$like, $like]
);
while ($row = $species->fetch_assoc()) {
    add_candidate($candidates, $q, 'Species', $row['common_name'], $row['scientific_name'] ?? '', 'entry.php?type=species&id=' . $row['id'], $row['scientific_name'] ?? '');
}

$plates = $db_connect->execute_query("SELECT id, title, location FROM plates WHERE title LIKE ? OR location LIKE ? LIMIT 20", [$like, $like]);
while ($row = $plates->fetch_assoc()) {
    add_candidate($candidates, $q, 'Plate', $row['title'], $row['location'] ?? '', 'plates.php?id=' . $row['id'], $row['location'] ?? '');
}

// only biomes that have plates: the link jumps to that biome's section, which only exists if it has some
$biomes = $db_connect->execute_query(
    "SELECT b.id, b.name FROM biomes b WHERE b.name LIKE ? AND EXISTS (SELECT 1 FROM plates p WHERE p.biome_id = b.id) LIMIT 20",
    [$like]
);
while ($row = $biomes->fetch_assoc()) {
    add_candidate($candidates, $q, 'Biome', $row['name'], '', 'plates.php#biome-' . $row['id']);
}

// best matches first; ties go to the shorter (more specific) name, then alphabetical
usort($candidates, function ($a, $b) {
    return [$a['score'], mb_strlen($a['label']), $a['label']] <=> [$b['score'], mb_strlen($b['label']), $b['label']];
});

$results = array_map(function ($c) {
    unset($c['score']);
    return $c;
}, array_slice($candidates, 0, 8));

echo json_encode($results);
