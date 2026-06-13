<?php if (!isset($active)) { $active = ''; } ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thrysus Project - Diotha b · PH-K23V</title>
        <link rel="stylesheet"  href="style.css">
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
            <input class="search-input" type="text" placeholder="search species, phyla, biome">
        </div>

        <div class="nav-right">
            <span class="pill">SOHS-VDSRP</span>   <span class="pill">ACCESS: LVL: 2</span>
        </div>
    </div>

    <nav class="subnav">
        <a href="index.php" class="<?php if ($active == 'main') echo 'active'; ?>"> Main Page </a>
        <a href="fauna.php" class="<?php if ($active == 'fauna') echo 'active'; ?>"> Fauna </a>
         <span> Flora </span> <span> Geography </span> <span> Biomes </span> <span> Geology </span> <span style="margin-left: auto">  Expeditions </span> <span> Travel Guide </span>
    </nav>
