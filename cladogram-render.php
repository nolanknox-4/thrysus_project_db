<?php
// cladogram-render.php — shared clade-centric tree renderer, included by cladogram.php
// (the standalone page) and pulled straight from the DB, so admin edits show up on next
// load with no extra wiring. See memory: cladogram-design.

function render_cladogram($db_connect, $category, $heading = 'Cladogram')
{
    $all_taxa = $db_connect->execute_query("SELECT * FROM taxa WHERE category = ?", [$category])->fetch_all(MYSQLI_ASSOC);
    $all_species = $db_connect->execute_query(
        "SELECT s.* FROM species s JOIN taxa t ON s.taxon_id = t.id WHERE t.category = ?",
        [$category]
    )->fetch_all(MYSQLI_ASSOC);

    // group taxa by parent, species by their taxon — the two lookup tables render_clado_node walks
    $children = [];
    foreach ($all_taxa as $t) {
        $pid = $t['parent_taxon_id'] ?? 'root';
        $children[$pid][] = $t;
    }
    $spp = [];
    foreach ($all_species as $s) {
        $spp[$s['taxon_id']][] = $s;
    }

    $roots = $children['root'] ?? [];

    echo "<div class='clado-panel'>";
    echo "<div class='clado-panel-header'>" . htmlspecialchars($heading) . "</div>";
    if (empty($roots)) {
        echo "<div class='clado-empty'>No lineages catalogued yet.</div>";
    } else {
        echo "<ul class='clado-tree'>";
        foreach ($roots as $root) {
            render_clado_node($root, $children, $spp, '#c9bfa8');
        }
        echo "</ul>";
    }
    echo "<div class='clado-key'>&dagger; extinct</div>";
    echo "</div>";
}

// a taxon with descendants renders as a clade row (collapsible); species are always tips.
function render_clado_node($taxon, $children, $spp, $inherited_colour)
{
    $id = $taxon['id'];
    $colour = $taxon['colour_hex'] ?: $inherited_colour;
    $kid_taxa = $children[$id] ?? [];
    $kid_species = $spp[$id] ?? [];
    $has_descendants = !empty($kid_taxa) || !empty($kid_species);

    echo "<li class='clado-node" . ($has_descendants ? "" : " clado-tip") . "'>";
    echo "<a class='clado-row' href='entry.php?id=" . $id . "'>";
    if ($has_descendants) {
        echo "<span class='clado-caret'>&rsaquo;</span>";
    }
    echo "<span class='clado-dot' style='background:" . htmlspecialchars($colour) . ";'></span>";
    echo "<span class='clado-name'>" . htmlspecialchars($taxon['name']) . "</span>";
    if ($taxon['status'] === 'extinct') {
        echo " <span class='clado-dagger'>&dagger;</span>";
    }
    if (!empty($kid_species)) {
        echo " <span class='clado-count'>" . count($kid_species) . " spp</span>";
    }
    echo "</a>";

    if ($has_descendants) {
        echo "<ul class='clado-children'>";
        foreach ($kid_taxa as $kt) {
            render_clado_node($kt, $children, $spp, $colour);
        }
        foreach ($kid_species as $ks) {
            echo "<li class='clado-node clado-tip'>";
            echo "<a class='clado-row' href='entry.php?type=species&id=" . $ks['id'] . "'>";
            echo "<span class='clado-dot' style='background:" . htmlspecialchars($colour) . ";'></span>";
            echo "<span class='clado-name'>" . htmlspecialchars($ks['common_name']);
            if (!empty($ks['scientific_name'])) {
                echo " <span class='clado-sci'>" . htmlspecialchars($ks['scientific_name']) . "</span>";
            }
            echo "</span>";
            if ($ks['status'] === 'extinct') {
                echo " <span class='clado-dagger'>&dagger;</span>";
            }
            echo "</a>";
            echo "</li>";
        }
        echo "</ul>";
    }
    echo "</li>";
}
