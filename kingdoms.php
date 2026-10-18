<?php
// kingdoms.php — display labels for the three kingdom groups.
// The database files every taxon under a fixed category (fauna / flora / tapetae); what visitors
// see is the NAME of that group's kingdom taxon, read live from the taxa table. So renaming a
// kingdom in admin renames it everywhere, with nothing to change in the code.

// plain-English gloss shown next to each kingdom's scientific name
const KINGDOM_GLOSS = [
    'fauna'   => 'Animals',
    'flora'   => 'Plants',
    'tapetae' => 'Decomposers',
];

function kingdom_labels($db_connect)
{
    // fallbacks, in case a group has no top-level kingdom taxon yet
    $labels = ['fauna' => 'Fauna', 'flora' => 'Flora', 'tapetae' => 'Tapetae'];
    $result = $db_connect->execute_query(
        "SELECT category, name FROM taxa WHERE rank = 'kingdom' AND parent_taxon_id IS NULL ORDER BY id"
    );
    while ($row = $result->fetch_assoc()) {
        if (isset($labels[$row['category']])) {
            $labels[$row['category']] = $row['name'];
        }
    }
    return $labels;
}
