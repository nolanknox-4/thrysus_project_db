<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // needed here so footer.php can tell if the visitor is the logged-in admin
}
if (!isset($active)) {
    $active = '';
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thrysus Project - Diotha b · PH-K23V</title>
        <link rel="stylesheet"  href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital@0;1&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    </head>


    <!--topbar and search stuff-->
<body>
    <div class="shell">
    <div class="topbar">
        <div class="logo">
            <span class="logo-main"> SOHS . Thrysus Project </span>   <span class="logo-sub"> XENOBIOLOGICAL FIELD INDEX . DIOTHA b · PH-K23V </span>
        </div>

        <div class="search-wrap">
            <form method="GET" action="search.php" autocomplete="off">
                <input class="search-input" type="text" name="q" id="search-input" placeholder="search species, phyla, biome">
            </form>
            <div id="search-suggestions" class="search-suggestions"></div>
        </div>


        <div class="nav-right">
            <span class="pill">SOHS-VDSRP</span>   <span class="pill">ACCESS: LVL: 2</span>
        </div>
    </div>

    <nav class="subnav">
        <a href="index.php" class="<?php if ($active == 'main') echo 'active'; ?>"> Main Page </a>
        <a href="taxa.php" class="<?php if (in_array($active, ['taxa', 'fauna', 'flora', 'tapetae'], true)) echo 'active'; ?>"> Taxa </a>
        <a href="cladogram.php" class="<?php if ($active == 'cladogram') echo 'active'; ?>"> Cladogram </a> <span> Xenoanthropology </span> <a href="plates.php" class="<?php if ($active == 'plates') echo 'active'; ?>" style="margin-left: auto;"> Plates </a> <a href="timeline.php" class="<?php if ($active == 'timeline') echo 'active'; ?>"> Timeline </a>
    </nav>
