<?php
// 404.php — shown by Apache for any address that doesn't exist (see apache-security.conf)
http_response_code(404);
$active = '';
include 'header.php';
?>

<div class="entry-main-text">
    <div class="section-heading">Not found</div>
    <p class="body-text">There's nothing at this address. It may have been renamed or removed.</p>
    <a class="entry-child-link" href="index.php">Main page</a><br>
    <a class="entry-child-link" href="plates.php">Plates</a><br>
    <a class="entry-child-link" href="taxa.php">Taxa</a>
</div>

<?php include 'footer.php'; ?>
