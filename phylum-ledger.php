<?php
// phylum-ledger.php — shared "Phylum Index" ledger, included by taxa.php (one call per kingdom).

function render_phylum_ledger($db_connect, $category, $title, $gloss = '')
{
    $result = $db_connect->execute_query("SELECT * FROM taxa WHERE rank = 'phylum' AND category = ? ORDER BY name", [$category]);

    // kingdom name, then one quiet line: plain-English gloss + phylum count
    $n = $result->num_rows;
    echo "<div class='fauna-head'>";
    echo "<div class='fauna-title'>" . htmlspecialchars($title) . "</div>";
    echo "<div class='fauna-sub'>" . ($gloss !== '' ? htmlspecialchars($gloss) . " · " : "") . $n . ($n === 1 ? " phylum" : " phyla") . "</div>";
    echo "</div>";

    echo "<div class='phylum-index'>";
    while ($row = $result->fetch_assoc()) {
        echo "<a href='entry.php?id=" . $row['id'] . "' class='phylum-row'>";
        echo "<div class='phylum-spine' style='background:" . $row['colour_hex'] . ";'></div>";
        echo "<div class='phylum-body'>";
        echo "<div class='phylum-name'>" . $row['name'] . "</div>";
        echo "<div class='phylum-desc'>" . $row['description'] . "</div>";
        echo "</div>";
        echo "</a>";
    }
    echo "</div>";
}
