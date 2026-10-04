<?php
// plate-row.php — renders one plate as a phylum-index-style ledger row.
// Shared by plates.php's grouped index and timeline.php's per-era plate lists.

function render_plate_ledger_row($db_connect, $row, $spine_colour = null)
{
    $count = $db_connect->execute_query("SELECT COUNT(*) AS c FROM plate_subjects WHERE plate_id = ?", [$row['id']])->fetch_assoc()['c'];
    echo "<a href='plates.php?id=" . $row['id'] . "' class='phylum-row'>";
    if ($spine_colour) {
        echo "<div class='phylum-spine' style='background:" . htmlspecialchars($spine_colour) . ";'></div>";
    }
    echo "<div class='phylum-body'>";
    echo "<div class='phylum-name'>" . htmlspecialchars($row['title']) . "</div>";
    echo "<div class='phylum-code'>DNY-P" . str_pad($row['id'], 4, "0", STR_PAD_LEFT);
    echo !empty($row['era_name']) ? ' &middot; ' . htmlspecialchars($row['era_name']) : '';
    echo "</div>";
    echo "<div class='phylum-desc'>" . (int) $count . " subjects pictured";
    echo !empty($row['location']) ? ' &middot; ' . htmlspecialchars($row['location']) : '';
    echo "</div></div>";
    echo "<span class='phylum-cue'>View plate &rarr;</span>";
    echo "</a>";
}
