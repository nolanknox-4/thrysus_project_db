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
        echo "<div class='era-card'>";
        echo "<div class='era-bar' style='background-color: " . $row['colour_hex'] . ";'></div>";
        echo "<div class='era-number'>Era " . $row['era_number'] . "</div>";
        echo "<div class='era-name'>" . $row['era_name'] . "</div>";
        echo "<div class='era-description'>" . $row['era_description'] . "</div>";
        echo "</div>";
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

    // one random pick across the merged fauna + flora pool
    $feature = $candidates[array_rand($candidates)];

    // Breadcrumb: walk up the taxa tree from crumb_start (same walker as before).
    $feature_crumbs = [];
    $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = " . (int)$feature['crumb_start'])->fetch_assoc();
    while ($walker) {
        $feature_crumbs[] = $walker['name'];
        if ($walker['parent_taxon_id']) {
            $walker = mysqli_query($db_connect, "SELECT * FROM taxa WHERE id = " . (int)$walker['parent_taxon_id'])->fetch_assoc();
        } else {
            $walker = null;
        }
    }
    $feature_crumbs = array_reverse($feature_crumbs);
    ?>
    <div class="main">
        <span class="feature-title">Featured Article</span>
        <div class="feature-container">
            <a href="entry.php?type=<?php echo $feature['link_type']; ?>&id=<?php echo $feature['id']; ?>" class="feature-link">
            <div class="feature-img"> <img src="images/<?php echo $feature['species_image']; ?>" alt="Featured specimen"></div>
            <!--each <?php echo $feature['...']; ?> below prints a column; category-text is the breadcrumb built by the walker above-->
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

            <div class="sidebar-cat">
                <div class="sidebar-cat-dot" style="background: #1D9E75;"></div>
                <span class="sidebar-cat-name"> Great Sieve-Snout </span>
                <span class="sidebar-cat-count"> 2341.10.28 </span>
            </div>


            <div class="sidebar-cat">
                <div class="sidebar-cat-dot" style="background: #888780;"></div>
                <span class="sidebar-cat-name"> Dorygnatha </span>
                <span class="sidebar-cat-count"> 2341.10.29 </span>
            </div>


            <div class="sidebar-cat">
                <div class="sidebar-cat-dot" style="background: #534AB7;"></div>
                <span class="sidebar-cat-name"> Hygrophyte </span>
                <span class="sidebar-cat-count"> 2341.10.14 </span>
            </div>

            <div class="sidebar-cat">
                <div class="sidebar-cat-dot" style="background: #BA7517;"></div>
                <span class="sidebar-cat-name"> Ossid </span>
                <span class="sidebar-cat-count"> 2341.09.17 </span>
            </div>

        </div><!-- /sidebar-block recent entries -->

        <div class="sidebar-block">
            <div class="sidebar-label"> Browse By Phylum</div>

            <?php
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
                echo "<span class='sidebar-cat-count'> - entries </span>";
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
                echo "<span class='sidebar-cat-count'> - entries </span>";
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
                echo "<span class='sidebar-cat-count'> - entries </span>";
                echo "</div>";
            }
            ?>

        </div><!-- /sidebar-block browse by phylum -->
    </div><!-- /sidebar -->
</div><!-- /body -->


<?php include 'footer.php'; ?>
