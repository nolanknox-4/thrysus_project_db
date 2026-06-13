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
    <span class="advisory-item-text">No ignition sources at any altitude.</span></div>
    <div class="advisory-item">
           <div class="adv-badge adv-warn">CAUTION</div>
    <span class="advisory-item-text">1.2g fatigue — 6hr surface limit.</span></div>
    <div class="advisory-item">
           <div class="adv-badge adv-info">INFO</div>
    <span class="advisory-item-text">Floater migration window open.</span></div>
    </div>

    <div class= "geographic-strip"> <span class= "Geo-text"> Geological Eras</span> <span class= "strato-text">Full Stratograhpic Records →</span> </div>

<!--query!-->
  
        <div class="era-strip">
            <?php
             $query = "SELECT * FROM geological_eras ORDER BY era_number";
             $result = mysqli_query($db_connect, $query);
             if (!$result) { die("Query failed: " . $db_connect->error); }
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
 <!--this is for the featured area-->
    <div class="main">
        <span class="feature-title">Featured Article</span>
        <div class="feature-container">
            <div class="feature-img"> <img src= "images/AEROPSIDS-3.jpg" alt="Featured specimen"></div>
            <div class="feature-text"> <span class= "category-text">Fauna . Myctida . Aeropsida</span> <span class= "common-name">Great Sieve-Snout</span> <span class = "sci-name">Tentacluonasus aerops</span> <span class="featured-desc"> A streamlined eight-limbed predator of the of the Paleogian Era. This predator was an early member of its clade, and thought to be the progenitor of most ancestral clades today.</span> <span class= "entry-details">entry code  . lorem ipsum   . author . rev 0  .  date</span> </div>
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

</div>
        </div>
    </div>
    <div class="sidebar">

<div class="sidebar-block">
    <div class="sidebar-label">Recent Entries</div>

     <div class="sidebar-cat"> 
<div class= "sidebar-cat-dot" style="background: #1D9E75;"></div>
 <span class="sidebar-cat-name"> Great Sieve-Snout  </span>
<span class= "sidebar-cat-count"> 2341.10.28 </span>
</div>


<div class="sidebar-cat"> 
<div class= "sidebar-cat-dot" style="background: #888780;"></div>
 <span class="sidebar-cat-name"> Dorygnatha  </span>
<span class= "sidebar-cat-count"> 2341.10.29 </span>
</div>


<div class="sidebar-cat"> 
<div class= "sidebar-cat-dot" style="background: #534AB7;"></div>
 <span class="sidebar-cat-name"> Hygrophyte  </span>
<span class= "sidebar-cat-count"> 2341.10.14 </span>
</div>

<div class="sidebar-cat">
<div class= "sidebar-cat-dot" style="background: #BA7517;"></div>
 <span class="sidebar-cat-name"> Ossid  </span>
<span class= "sidebar-cat-count">  2341.09.17 </span>
</div>

</div><!-- /sidebar-block recent entries -->

<div class="sidebar-block">
    <div class="sidebar-label"> Browse By Phylum</div>
    <!--... phylum rows ...-->

   <div class="sidebar-cat"> 
<div class= "sidebar-cat-dot" style="background: #1D9E75;"></div>
 <span class="sidebar-cat-name"> Malakokelia  </span>
<span class= "sidebar-cat-count"> - entries </span>
</div>


<div class="sidebar-cat"> 
<div class= "sidebar-cat-dot" style="background: #888780;"></div>
 <span class="sidebar-cat-name"> Myctida  </span>
<span class= "sidebar-cat-count"> - entries </span>
</div>


<div class="sidebar-cat">
<div class= "sidebar-cat-dot" style="background: #534AB7;"></div>
 <span class="sidebar-cat-name"> Rotapodia  </span>
<span class= "sidebar-cat-count"> - entries </span>
</div>

</div><!-- /sidebar-block browse by phylum -->
</div><!-- /sidebar -->
</div><!-- /body -->


<?php include 'footer.php'; ?>
