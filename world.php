<?php
// world.php — the planet dossier: everything about Dionysus itself, as opposed to the life on it.
// All the text here is editable in place when logged in (see site-text.php). The strings below
// are only the starting text: once a block is edited, the saved version lives in the database.

$active = 'world';
include 'header.php';
include 'db_config.php';
include 'site-text.php';

// prose sections: slug => [heading, default text, what to write (shown to you while empty)]
// an unwritten section is hidden from visitors
$sections = [
    'world.overview' => ['Overview',
        'Dionysus is an Earth-like terrestrial planet orbiting Phoebe, a K-type orange dwarf. It has slightly less ocean coverage than Earth, beneath a dense, hazy atmosphere that is breathable in composition but toxic to humans in practice.',
        'What kind of world Dionysus is, in a paragraph.'],
    'world.system' => ['The Phoebe System',
        'Dionysus is the second planet from Phoebe. Its one large moon orbits at roughly 1.3 times the Earth–Moon distance, accompanied by two small, irregular moonlets. Tides are dominated by Phoebe itself, with the moon providing a secondary influence.',
        'Orbital period, day length, axial tilt, seasons.'],
    'world.atmosphere' => ['Atmosphere',
        "Surface pressure is around 200 kPa, about twice Earth's, comparable to standing ten metres underwater. The atmosphere is toxic to humans through elevated carbon dioxide and, at this pressure, an oxygen partial pressure more than double Earth's: prolonged exposure damages the lungs, and any flame burns violently, hence the ban on ignition sources. Methane at 0.1% can only persist if something keeps producing it, a strong sign of biological activity. The dense air scatters Phoebe's light into a hazy orange sky, so even full daylight has the dim cast of twilight.",
        'Temperature range, weather, and how the dense air shapes flight and floaters.'],
    'world.biochemistry' => ['Biochemistry', '',
        'What life here is built from: solvent, energy source, and how it differs from Earth life.'],
];

// the "Name: value" lists, one row per line (edited as plain text, same as the sections)
$lists = [
    // bodies of the Phoebe system, star first then outward, as named on the system illustration.
    // the Dionysus row is highlighted
    'world.system-table' => "Phoebe: Star · K3V · HD24398\nHephaestus: Planet\nDionysus: Planet · 3 moons\nCastor: Planet\nPollux: Planet\nAsteroid belt: Belt\nHyperion: Planet\nChione: Planet",
    // composition by volume, from the Dionysus reference sheet
    'world.atmosphere-table' => "Nitrogen: 74.25%\nOxygen: 24%\nCarbon dioxide: 1%\nArgon: 0.65%\nMethane: 0.1%",
    'world.data' => "Star: Phoebe (K3V)\nOrbit: 2nd planet\nDesignation: PH-K23V\nGravity: 1.2g\nPressure: ~200 kPa\nAtmosphere: Toxic\nMoons: 3\nFrom Sol: 847 ly",
];

// one editable "Name: value" list, rendered as info rows
function render_rows($db_connect, $key, $default, $intro = null, $highlight = null)
{
    $raw = site_text($db_connect, $key, $default);
    echo "<div class='editable'" . edit_attrs($key, $raw, 'One per line, as  Name: value') . ">";
    if ($intro) {
        echo "<div class='info-row'><span class='info-key'>" . $intro[0] . "</span><span class='info-value'>" . $intro[1] . "</span></div>";
    }
    foreach (text_rows($db_connect, $key, $default) as [$name, $value]) {
        $home = ($highlight !== null && $name === $highlight) ? ' is-home' : '';
        echo "<div class='info-row" . $home . "'><span class='info-key'>" . htmlspecialchars($name) . "</span><span class='info-value'>" . htmlspecialchars($value) . "</span></div>";
    }
    echo "</div>";
}
?>

<div class="entry-header">
    <div class="entry-breadcrumb">Phoebe System &rsaquo; PH-K23V</div>
    <h1 class="entry-title">Dionysus</h1>
</div>

<!--system illustration as the page's hero; the frame crops the white bands baked into the image-->
<div class="system-figure">
    <img src="images/phoebe-system.jpg" alt="The Phoebe K3V star system: Phoebe, Hephaestus, Dionysus, Castor and Pollux, the asteroid belt, Hyperion and Chione">
</div>
<div class="faceplate-caption faceplate-caption-slim">
    <div class="plate-credit-inline">The Phoebe System</div>
</div>


<div class="entry-body">
    <div class="entry-main-text">

        <?php foreach ($sections as $slug => [$heading, $default, $hint]) {
            // the System and Atmosphere sections always show (they have tables); others hide while empty
            $has_table = in_array($slug, ['world.system', 'world.atmosphere'], true);
            if (trim(site_text($db_connect, $slug, $default)) === '' && !$has_table && !is_editor()) {
                continue;
            }
        ?>
            <div class="section-heading"><?php echo $heading; ?></div>
            <?php editable_text($db_connect, $slug, $default, 'body-text', $hint); ?>

            <?php if ($slug === 'world.system') { ?>
                <!-- the illustration above shows the system; this table is the written record of it -->
                <div class="phylum-index system-table">
                    <?php render_rows($db_connect, 'world.system-table', $lists['world.system-table'], null, 'Dionysus'); ?>
                </div>
            <?php } ?>

            <?php if ($slug === 'world.atmosphere') { ?>
                <div class="phylum-index system-table">
                    <?php render_rows($db_connect, 'world.atmosphere-table', $lists['world.atmosphere-table'], ['Composition', 'by volume']); ?>
                </div>
            <?php } ?>
        <?php } ?>

        <?php
        // biome map: drop a file named biome-map (.jpg, .png or .webp) into images/ and it appears.
        // until then visitors see nothing, and you (logged in) see an empty frame as a reminder
        $map_found = glob(__DIR__ . '/images/biome-map.{jpg,jpeg,png,webp}', GLOB_BRACE);
        $map_img = $map_found ? basename($map_found[0]) : null;
        if ($map_img || is_editor()) { ?>
            <div class="section-heading">Biome Map</div>
            <?php if ($map_img) { ?>
                <div class="faceplate">
                    <div class="faceplate-inner">
                        <img class="faceplate-img" src="images/<?php echo htmlspecialchars($map_img); ?>" alt="Map of Dionysus's biomes">
                    </div>
                    <div class="faceplate-caption faceplate-caption-slim">
                        <div class="plate-credit-inline">Biomes of Dionysus</div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="map-slot">Biome map goes here. Drop it into images/ named biome-map (.jpg, .png or .webp).</div>
            <?php } ?>
        <?php } ?>
    </div>

    <div class="entry-sidebar">
        <div class="info-box">
            <div class="info-box-header">Planetary Data</div>
            <?php
            // planet close-up, infobox-style. the source is the full reference sheet (text panel on the left),
            // so .info-box-img crops a square around the planet on the right
            $planet_img = 'dionysus.webp';
            if (file_exists(__DIR__ . '/images/' . $planet_img)) { ?>
                <div class="info-box-img"><img src="images/<?php echo $planet_img; ?>" alt="Dionysus from orbit, with its moon and two moonlets"></div>
            <?php } ?>
            <?php render_rows($db_connect, 'world.data', $lists['world.data']); ?>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>
