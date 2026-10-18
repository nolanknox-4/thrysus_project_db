<?php
// sessions are needed here so footer.php (and the edit links) can tell if the visitor is the logged-in admin
require_once __DIR__ . '/session.php';
if (!isset($active)) {
    $active = '';
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thrysus Project · Dionysus</title>
        <link rel="stylesheet"  href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital@0;1&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    </head>


    <!--topbar and search stuff-->
<body>
    <div class="shell">
    <div class="topbar">
        <a class="logo" href="index.php">
            <span class="logo-main">Thrysus Project</span>
            <span class="logo-sub">Dionysus</span>
        </a>

        <div class="search-wrap">
            <form method="GET" action="search.php" autocomplete="off">
                <input class="search-input" type="text" name="q" id="search-input" placeholder="search species, phyla, biome">
            </form>
            <div id="search-suggestions" class="search-suggestions"></div>
        </div>
    </div>

    <nav class="subnav">
        <a href="index.php" class="<?php if ($active == 'main') echo 'active'; ?>"> Main Page </a>
        <a href="world.php" class="<?php if ($active == 'world') echo 'active'; ?>"> Dionysus </a>
        <a href="plates.php" class="<?php if ($active == 'plates') echo 'active'; ?>"> Plates </a>
        <a href="taxa.php" class="<?php if ($active == 'taxa') echo 'active'; ?>"> Taxa </a>
        <a href="cladogram.php" class="<?php if ($active == 'cladogram') echo 'active'; ?>"> Cladogram </a>
        <a href="timeline.php" class="<?php if ($active == 'timeline') echo 'active'; ?>"> Timeline </a>
    </nav>
