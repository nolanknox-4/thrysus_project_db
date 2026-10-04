<?php
// phylum-ledger.php — shared "Phylum Index" ledger, included by taxa.php (one call per kingdom).

function render_phylum_ledger($db_connect, $category, $title)
{
    $result = $db_connect->execute_query("SELECT * FROM taxa WHERE rank = 'phylum' AND category = ?", [$category]);

    echo "<div class='fauna-head'>";
    echo "<div class='fauna-title'>" . htmlspecialchars($title) . "</div>";
    echo "<div class='fauna-sub'>Phylum Index</div>";
    echo "<div class='fauna-count'>" . $result->num_rows . " phyla catalogued</div>";
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
}
