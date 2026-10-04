<?php

$active = 'main';
include 'header.php';
include 'db_config.php'; ?>

<!--the hero-->
<div class="image-container">
    <img src="images/hero.png" alt="Image Desc">
    <div class="text-overlay">
        <p class="eyebrow">EYEBROW TEXT</p>
        <h1 class="big-tittle">Main Title</h1>
        <p class="description">descriptive paragraph here</p>
    </div>
</div>


<!--at a glance-->
<div class="quick-look">

    <div class="look-label">
        Diotha b · PH-K23V
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Surface Gravity</span>
        <span class="stat-value">1.2g</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Pressure</span>
        <span class="stat-value">~200 kPa</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Atmosphere</span>
        <span class="stat-value danger">TOXIC</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Moons</span>
        <span class="stat-value">3</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Distance From Sol</span>
        <span class="stat-value">847 ly</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Expeditions</span>
        <span class="stat-value highlight">22</span>
    </div>

    <div class="quick-look-stat">
        <span class="stat-label">Species Catalogued</span>
        <span class="stat-value highlight">18</span>
    </div>

</div>

<div class="advisory-strip">
    <div class="advisory-strip-label">ADVISORIES</div>
    <div class="advisory-item">
        <div class="adv-badge adv-danger">DANGER</div>
        <span class="advisory-item-text">No ignition sources at any altitude.</span>
    </div>
    <div class="advisory-item">
        <div class="adv-badge adv-warn">CAUTION</div>
        <span class="advisory-item-text">1.2g fatigue — 6hr surface limit.</span>
    </div>
    <div class="advisory-item">
        <div class="adv-badge adv-info">INFO</div>
        <span class="advisory-item-text">Floater migration window open.</span>
    </div>
</div>

<div class="geographic-strip"> <span class="Geo-text"> Geological Eras</span> </div>

<!--query!-->

<div class="era-strip">
    <?php
    $query = "SELECT * FROM geological_eras ORDER BY era_number";
    $result = mysqli_query($db_connect, $query);
    if (!$result) {
        die("Query failed: " . $db_connect->error);
    }
    while ($row = $result->fetch_assoc()) {
        echo "<a class='era-card' href='timeline.php#era-" . $row['id'] . "'>";
        echo "<div class='era-bar' style='background-color: " . $row['colour_hex'] . ";'></div>";
        echo "<div class='era-number'>Era " . $row['era_number'] . "</div>";
        echo "<div class='era-name'>" . $row['era_name'] . "</div>";
        echo "<div class='era-description'>" . $row['era_description'] . "</div>";
        echo "</a>";
    }
    ?>
</div>

<div class="body">

    <!--FEATURED ARTICLE: grab ONE random species from the db into $feature-->
    <!--the spans below print $feature's columns (common_name, scientific_name, etc.)-->
    <?php
    // FEATURED ARTICLE: pick one at random from BOTH fauna (species table) and flora (taxa table).
    // The two tables use different column names, so each candidate is normalised to the same keys.
    $candidates = [];

    // Fauna creatures: every species.
    $sp = mysqli_query($db_connect, "SELECT * FROM species");
    while ($row = $sp->fetch_assoc()) {
        $candidates[] = [
            'link_type'           => 'species',
            'id'                  => $row['id'],
            'common_name'         => $row['common_name'],
            'scientific_name'     => $row['scientific_name'],
            'species_description' => $row['species_description'],
            'species_image'       => $row['species_image'],
            'lead'                => $row['lead'],
            'last_edited'         => $row['last_edited'],
            'crumb_start'         => (int)$row['taxon_id'], // walk up from the parent clade
        ];
    }

    // Flora: taxa in the flora kingdom that have an illustration to show.
    $fl = mysqli_query($db_connect, "SELECT * FROM taxa WHERE category = 'flora' AND image IS NOT NULL AND image <> ''");
    while ($row = $fl->fetch_assoc()) {
        $candidates[] = [
            'link_type'           => 'taxon',
            'id'                  => $row['id'],
            'common_name'         => $row['name'],
            'scientific_name'     => ucfirst($row['rank']),
            'species_description' => $row['description'],
            'species_image'       => $row['image'],
            'lead'                => $row['lead'],
            'last_edited'         => $row['last_edited'],
            'crumb_start'         => (int)$row['parent_taxon_id'], // walk up from this taxon's parent
        ];
    }

    // Plates: illustrations are now the site's primary art, so they're featurable too.
    // They aren't part of the taxa tree, so crumb_start stays null (no breadcrumb to walk).
    $pl = mysqli_query($db_connect, "SELECT * FROM plates");
    while ($row = $pl->fetch_assoc()) {
        $candidates[] = [
            'link_type'           => 'plate',
            'id'                  => $row['id'],
            'common_name'         => $row['title'],
            'scientific_name'     => '',
            'species_description' => $row['tour_text'],
            'species_image'       => $row['image'],
            'lead'                => $row['lead'],
            'last_edited'         => $row['last_edited'],
            'crumb_start'         => null,
        ];
    }

    // one random pick across the merged fauna + flora + plates pool
    $feature = $candidates[array_rand($candidates)];

    // Breadcrumb: walk up the taxa tree from crumb_start (same walker as before).
    // Plates have no crumb_start, so this just stays empty for them.
    $feature_crumbs = [];
    if ($feature['crumb_start']) {
        $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = " . (int)$feature['crumb_start'])->fetch_assoc();
        while ($walker) {
            $feature_crumbs[] = $walker['name'];
            if ($walker['parent_taxon_id']) {
                $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = " . (int)$walker['parent_taxon_id'])->fetch_assoc();
            } else {
                $walker = null;
            }
        }
    }
    $feature_crumbs = array_reverse($feature_crumbs);

    // plates live at plates.php, not entry.php
    $feature_href = ($feature['link_type'] === 'plate')
        ? 'plates.php?id=' . $feature['id']
        : 'entry.php?type=' . $feature['link_type'] . '&id=' . $feature['id'];
    ?>
    <div class="main">
        <span class="feature-title">Featured Article</span>
        <div class="feature-container">
            <a href="<?php echo $feature_href; ?>" class="feature-link">
            <div class="feature-img"> <img src="images/<?php echo $feature['species_image']; ?>" alt="Featured specimen"></div>
            <!-- each span below prints one $feature[...] column; category-text is the breadcrumb built by the walker above -->
            <div class="feature-text"> <span class="category-text"><?php echo implode(' . ', $feature_crumbs); ?></span> <span class="common-name"><?php echo $feature['common_name']; ?> </span> <span class="sci-name"> <?php echo $feature['scientific_name']; ?> </span> <span class="featured-desc"> <?php echo $feature['species_description']; ?></span> <span class="entry-details">DNY-<?php echo str_pad($feature['id'], 4, "0", STR_PAD_LEFT); ?> . <?php echo $feature['lead']; ?> . rev 0 . <?php echo $feature['last_edited']; ?></span> </div>
            </a>
        </div>

        <div class="log">
            <div class="log-label">Changelog</div>

            <div class="log-row">
                <div class="log-badge log-added">ADDED</div>
                <span class="log-name">Homepage layout</span>
                <span class="log-date">6/3/26</span>
            </div>

            <div class="log-row">
                <div class="log-badge log-revised">REVISED</div>
                <span class="log-name">Era descriptions</span>
                <span class="log-date">6/3/26</span>
            </div>

            <div class="log-row">
                <div class="log-badge log-updated">UPDATED</div>
                <span class="log-name">Featured article</span>
                <span class="log-date">6/3/26</span>

</a>
            </div>
        </div>
    </div>
    <div class="sidebar">

        <div class="sidebar-block">
            <div class="sidebar-label">Recent Entries</div>

            <?php
            // Most recently created taxon/species/plate, merged and re-sorted in PHP —
            // same normalise-then-merge pattern as the Featured Article pool above.
            $recent = [];

            $rt = $db_connect->execute_query("SELECT id, name, colour_hex, created_at FROM taxa ORDER BY created_at DESC LIMIT 6");
            while ($row = $rt->fetch_assoc()) {
                $recent[] = [
                    'link'       => 'entry.php?id=' . $row['id'],
                    'label'      => $row['name'],
                    'colour'     => $row['colour_hex'] ?: '#9c9080',
                    'created_at' => $row['created_at'],
                ];
            }

            $rs = $db_connect->execute_query(
                "SELECT s.id, s.common_name, t.colour_hex, s.created_at FROM species s LEFT JOIN taxa t ON t.id = s.taxon_id ORDER BY s.created_at DESC LIMIT 6"
            );
            while ($row = $rs->fetch_assoc()) {
                $recent[] = [
                    'link'       => 'entry.php?type=species&id=' . $row['id'],
                    'label'      => $row['common_name'],
                    'colour'     => $row['colour_hex'] ?: '#9c9080',
                    'created_at' => $row['created_at'],
                ];
            }

            $rp = $db_connect->execute_query("SELECT id, title, created_at FROM plates ORDER BY created_at DESC LIMIT 6");
            while ($row = $rp->fetch_assoc()) {
                $recent[] = [
                    'link'       => 'plates.php?id=' . $row['id'],
                    'label'      => $row['title'],
                    'colour'     => '#9c9080',
                    'created_at' => $row['created_at'],
                ];
            }

            usort($recent, function ($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });

            foreach (array_slice($recent, 0, 4) as $r) {
                echo "<div class='sidebar-cat'>";
                echo "<div class='sidebar-cat-dot' style='background: " . htmlspecialchars($r['colour']) . ";'></div>";
                echo "<a class='sidebar-cat-name' href='" . $r['link'] . "'>" . htmlspecialchars($r['label']) . "</a>";
                echo "<span class='sidebar-cat-count'>" . date('Y.m.d', strtotime($r['created_at'])) . "</span>";
                echo "</div>";
            }
            if (empty($recent)) {
                echo "<div class='sidebar-cat'><span class='sidebar-cat-name'>Nothing catalogued yet</span></div>";
            }
            ?>

        </div><!-- /sidebar-block recent entries -->

        <div class="sidebar-block">
            <div class="sidebar-label"> Browse By Phylum</div>

            <?php
            // species often attach several levels below a phylum (genus, etc.), so this
            // walks the whole subtree rather than just direct children.
            function count_species_in_subtree($db_connect, $taxon_id)
            {
                $result = $db_connect->execute_query(
                    "WITH RECURSIVE subtree AS (
                        SELECT id FROM taxa WHERE id = ?
                        UNION ALL
                        SELECT t.id FROM taxa t JOIN subtree s ON t.parent_taxon_id = s.id
                    )
                    SELECT COUNT(*) AS c FROM species WHERE taxon_id IN (SELECT id FROM subtree)",
                    [$taxon_id]
                );
                return (int) $result->fetch_assoc()['c'];
            }

            // Fauna group
            echo "<div class='sidebar-subhead'>Fauna</div>";
            $fauna_query = "SELECT * FROM taxa WHERE rank='phylum' AND category='fauna'";
            $fauna_result = mysqli_query($db_connect, $fauna_query);
            if (!$fauna_result) {
                die("Query failed: " . $db_connect->error);
            }
            while ($row = $fauna_result->fetch_assoc()) {
                echo "<div class='sidebar-cat'>";
                echo "<div class='sidebar-cat-dot' style='background: " . $row['colour_hex'] . ";'></div>";
                echo "<a class='sidebar-cat-name' href='entry.php?id=" . $row['id'] . "'>" . $row['name'] . "</a>";
                $entry_count = count_species_in_subtree($db_connect, $row['id']);
                echo "<span class='sidebar-cat-count'>" . $entry_count . ($entry_count === 1 ? " entry" : " entries") . "</span>";
                echo "</div>";
            }

            // Flora group
            echo "<div class='sidebar-subhead'>Flora</div>";
            $flora_query = "SELECT * FROM taxa WHERE rank='phylum' AND category='flora'";
            $flora_result = mysqli_query($db_connect, $flora_query);
            if (!$flora_result) {
                die("Query failed: " . $db_connect->error);
            }
            while ($row = $flora_result->fetch_assoc()) {
                echo "<div class='sidebar-cat'>";
                echo "<div class='sidebar-cat-dot' style='background: " . $row['colour_hex'] . ";'></div>";
                echo "<a class='sidebar-cat-name' href='entry.php?id=" . $row['id'] . "'>" . $row['name'] . "</a>";
                $entry_count = count_species_in_subtree($db_connect, $row['id']);
                echo "<span class='sidebar-cat-count'>" . $entry_count . ($entry_count === 1 ? " entry" : " entries") . "</span>";
                echo "</div>";
            }

            // Tapetae group
            echo "<div class='sidebar-subhead'>Tapetae</div>";
            $tapetae_query = "SELECT * FROM taxa WHERE rank='phylum' AND category='tapetae'";
            $tapetae_result = mysqli_query($db_connect, $tapetae_query);
            if (!$tapetae_result) {
                die("Query failed: " . $db_connect->error);
            }
            while ($row = $tapetae_result->fetch_assoc()) {
                echo "<div class='sidebar-cat'>";
                echo "<div class='sidebar-cat-dot' style='background: " . $row['colour_hex'] . ";'></div>";
                echo "<a class='sidebar-cat-name' href='entry.php?id=" . $row['id'] . "'>" . $row['name'] . "</a>";
                $entry_count = count_species_in_subtree($db_connect, $row['id']);
                echo "<span class='sidebar-cat-count'>" . $entry_count . ($entry_count === 1 ? " entry" : " entries") . "</span>";
                echo "</div>";
            }
            ?>

        </div><!-- /sidebar-block browse by phylum -->
    </div><!-- /sidebar -->
</div><!-- /body -->


<?php include 'footer.php'; ?>
