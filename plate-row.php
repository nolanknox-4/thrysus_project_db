<?php
// plate-row.php — renders one plate as an image-led card (plates.php's grid, the homepage's Latest Plates).

function render_plate_card($db_connect, $row, $spine_colour = null)
{
    echo "<a href='plates.php?id=" . (int) $row['id'] . "' class='plate-card'>";
    echo "<div class='plate-card-img'><img src='images/" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['title']) . "' loading='lazy'></div>";
    if ($spine_colour) {
        echo "<div class='plate-card-bar' style='background:" . htmlspecialchars($spine_colour) . ";'></div>";
    }
    echo "<div class='plate-card-body'>";
    // just title and era: the picture does the rest
    echo "<div class='phylum-name'>" . htmlspecialchars($row['title']) . "</div>";
    if (!empty($row['era_name'])) {
        echo "<div class='phylum-code'>" . htmlspecialchars($row['era_name']) . "</div>";
    }
    echo "</div>";
    echo "</a>";
}
