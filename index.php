<?php

$active = 'main';
include 'header.php';
include 'db_config.php';
include 'plate-row.php';
include 'site-text.php'; ?>

<!--the hero: newest plate's illustration, falling back to the static image if there are no plates yet-->
<?php
$hero = $db_connect->execute_query("SELECT title, image FROM plates WHERE image IS NOT NULL AND image <> '' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$hero_src = $hero ? $hero['image'] : 'Hero.png';
$hero_alt = $hero ? $hero['title'] : 'Dionysus';
?>
<div class="image-container">
    <img src="images/<?php echo htmlspecialchars($hero_src); ?>" alt="<?php echo htmlspecialchars($hero_alt); ?>">
    <div class="text-overlay">
        <?php // hero text is editable in place when logged in (site-text.php)
        editable_line($db_connect, 'home.eyebrow', 'SOHS · Xenobiological Field Index', 'p', 'eyebrow');
        editable_line($db_connect, 'home.title', 'Dionysus', 'h1', 'big-tittle');
        editable_line($db_connect, 'home.description', 'A survey of life on a toxic, high-pressure world 847 light-years from Sol: its creatures, its flora, and the ecosystems they share.', 'p', 'description'); ?>
        <div class="hero-links">
            <a href="plates.php">Explore the plates &rarr;</a>
            <a href="world.php">The planet &rarr;</a>
        </div>
    </div>
</div>

<div class="body">

    <?php
    // FEATURED ARTICLE: one random pick from species, flora taxa and plates.
    // The tables use different column names, so each candidate is normalised to the same keys.
    // Species/taxa without their own image borrow one from a plate they appear in (same rule as entry.php);
    // anything with no image at all is left out of the pool.
    $candidates = [];

    $sp = $db_connect->execute_query(
        "SELECT s.*, COALESCE(NULLIF(s.species_image, ''),
            (SELECT p.image FROM plates p JOIN plate_subjects ps ON ps.plate_id = p.id
             WHERE ps.subject_type = 'species' AND ps.subject_id = s.id ORDER BY p.id LIMIT 1)) AS shown_image
         FROM species s"
    );
    while ($row = $sp->fetch_assoc()) {
        if (empty($row['shown_image'])) {
            continue;
        }
        $candidates[] = [
            'href'        => 'entry.php?type=species&id=' . $row['id'],
            'name'        => $row['common_name'],
            'sub'         => $row['scientific_name'],
            'desc'        => $row['species_description'],
            'image'       => $row['shown_image'],
            'lead'        => $row['lead'],
            'last_edited' => $row['last_edited'],
            'crumb_start' => (int) $row['taxon_id'], // walk up from the parent clade
        ];
    }

    $fl = $db_connect->execute_query(
        "SELECT t.*, COALESCE(NULLIF(t.image, ''),
            (SELECT p.image FROM plates p JOIN plate_subjects ps ON ps.plate_id = p.id
             WHERE ps.subject_type = 'taxon' AND ps.subject_id = t.id ORDER BY p.id LIMIT 1)) AS shown_image
         FROM taxa t WHERE t.category = 'flora'"
    );
    while ($row = $fl->fetch_assoc()) {
        if (empty($row['shown_image'])) {
            continue;
        }
        $candidates[] = [
            'href'        => 'entry.php?id=' . $row['id'],
            'name'        => $row['name'],
            'sub'         => ucfirst($row['rank'] ?? ''),
            'desc'        => $row['description'],
            'image'       => $row['shown_image'],
            'lead'        => $row['lead'],
            'last_edited' => $row['last_edited'],
            'crumb_start' => (int) $row['parent_taxon_id'],
        ];
    }

    $pl = $db_connect->execute_query("SELECT * FROM plates WHERE image <> ''");
    while ($row = $pl->fetch_assoc()) {
        $candidates[] = [
            'href'        => 'plates.php?id=' . $row['id'],
            'name'        => $row['title'],
            'sub'         => 'Plate',
            'desc'        => $row['tour_text'],
            'image'       => $row['image'],
            'lead'        => $row['lead'],
            'last_edited' => $row['last_edited'],
            'crumb_start' => null, // plates aren't in the taxa tree
        ];
    }

    $feature = $candidates ? $candidates[array_rand($candidates)] : null;

    // Breadcrumb: walk up the taxa tree from crumb_start.
    $feature_crumbs = [];
    if ($feature && $feature['crumb_start']) {
        $walker = $db_connect->execute_query("SELECT * FROM taxa WHERE id = ?", [$feature['crumb_start']])->fetch_assoc();
        while ($walker) {
            $feature_crumbs[] = $walker['name'];
            $walker = $walker['parent_taxon_id']
                ? $db_connect->execute_query("SELECT * FROM taxa WHERE id = ?", [$walker['parent_taxon_id']])->fetch_assoc()
                : null;
        }
    }
    $feature_crumbs = array_reverse($feature_crumbs);

    $latest_plates = $db_connect->execute_query(
        "SELECT p.*, e.era_name FROM plates p LEFT JOIN geological_eras e ON e.id = p.era_id ORDER BY p.created_at DESC LIMIT 3"
    )->fetch_all(MYSQLI_ASSOC);
    ?>
    <div class="main">
        <span class="feature-title">Featured Article</span>
        <div class="feature-container">
            <?php if ($feature) { ?>
            <a href="<?php echo $feature['href']; ?>" class="feature-link">
                <div class="feature-img"><img src="images/<?php echo htmlspecialchars($feature['image']); ?>" alt="<?php echo htmlspecialchars($feature['name']); ?>"></div>
                <div class="feature-text">
                    <span class="category-text"><?php echo htmlspecialchars(implode(' . ', $feature_crumbs)); ?></span>
                    <span class="common-name"><?php echo htmlspecialchars($feature['name']); ?></span>
                    <span class="sci-name"><?php echo htmlspecialchars($feature['sub'] ?? ''); ?></span>
                    <span class="featured-desc"><?php echo $feature['desc']; ?></span>
                </div>
            </a>
            <?php } else { ?>
                <p class="featured-desc">Nothing illustrated yet.</p>
            <?php } ?>
        </div>

        <?php if (!empty($latest_plates)) { ?>
        <span class="feature-title">Latest Plates <a class="feature-title-link" href="plates.php">All plates &rarr;</a></span>
        <div class="feature-container">
            <div class="plate-grid plate-grid-compact">
                <?php foreach ($latest_plates as $row) {
                    render_plate_card($db_connect, $row);
                } ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <div class="sidebar">

        <div class="sidebar-block">
            <div class="sidebar-label">Browse By Biome</div>
            <?php
            // links land on the matching biome section of the plate index
            $biomes = $db_connect->execute_query(
                "SELECT b.id, b.name, b.colour_hex, COUNT(p.id) AS plate_count
                 FROM biomes b LEFT JOIN plates p ON p.biome_id = b.id
                 GROUP BY b.id, b.name, b.colour_hex
                 HAVING plate_count > 0
                 ORDER BY b.name"
            );
            while ($row = $biomes->fetch_assoc()) {
                $n = (int) $row['plate_count'];
                echo "<div class='sidebar-cat'>";
                echo "<div class='sidebar-cat-dot' style='background: " . htmlspecialchars($row['colour_hex'] ?: '#9c9080') . ";'></div>";
                echo "<a class='sidebar-cat-name' href='plates.php#biome-" . (int) $row['id'] . "'>" . htmlspecialchars($row['name']) . "</a>";
                echo "<span class='sidebar-cat-count'>" . $n . ($n === 1 ? " plate" : " plates") . "</span>";
                echo "</div>";
            }
            if ($biomes->num_rows === 0) {
                echo "<div class='sidebar-cat'><span class='sidebar-cat-name'>No plates yet</span></div>";
            }
            ?>
        </div>

    </div><!-- /sidebar -->
</div><!-- /body -->


<?php include 'footer.php'; ?>
