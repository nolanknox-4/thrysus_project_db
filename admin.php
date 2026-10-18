<?php
require_once __DIR__ . '/session.php';
// GATE: no valid session -> bounce to the login page before anything else runs.
if (empty($_SESSION['authed'])) {
    header('Location: login.php');
    exit;
}
// every save/delete must carry this session's CSRF token (see session.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
    http_response_code(400);
    exit('This form expired or came from somewhere else. Go back, reload the page and try again.');
}

$active = '';
include 'db_config.php';
include 'kingdoms.php';

// empty string -> null, for nullable columns (FKs, dates, enums)
function nn($v)
{
    return $v === '' ? null : $v;
}

// Shared by all three compose types. Validates & moves an uploaded file, returning the
// filename to store (or the existing filename if nothing new was uploaded). On any
// failure it redirects back to the form and exits, exactly like each type did inline before.
function handle_image_upload($existing_name, $redirect_type_qs)
{
    $image_name = $existing_name;
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
        // 0. the upload must have actually succeeded before we touch the temp file.
        //    (e.g. a file bigger than upload_max_filesize fails here with an empty tmp_name.)
        if ($_FILES['image_upload']['error'] !== UPLOAD_ERR_OK) {
            header("Location: admin.php?{$redirect_type_qs}status=badimage");
            exit;
        }
        // 1. is it really an image, and not too big? getimagesize returns false if not.
        $info = getimagesize($_FILES['image_upload']['tmp_name']);
        if ($info === false || $_FILES['image_upload']['size'] > 5 * 1024 * 1024) {
            header("Location: admin.php?{$redirect_type_qs}status=badimage");
            exit;
        }

        // 2. pick the extension from the DETECTED type, never from the user's filename.
        $ext_map = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];
        if (!isset($ext_map[$info[2]])) {
            header("Location: admin.php?{$redirect_type_qs}status=badimage");
            exit;
        }
        $ext = $ext_map[$info[2]];

        // 3. generate our own safe, unique filename.
        $safe = uniqid('img_', true) . '.' . $ext;

        // 4. move_uploaded_file verifies it was a genuine HTTP upload before moving it.
        if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], __DIR__ . '/images/' . $safe)) {
            header("Location: admin.php?{$redirect_type_qs}status=badimage");
            exit;
        }

        // 5. this is the name we store in the DB.
        $image_name = $safe;
    }
    return $image_name;
}

// the stored image name, unless the "remove image" box was ticked
function kept_image($field)
{
    return !empty($_POST['remove_image']) ? '' : ($_POST[$field] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'taxon';
    if (!in_array($type, ['species', 'plate'], true)) {
        $type = 'taxon';
    }

    // every save stamps today's date (feeds the homepage's Recently Revised list),
    // and remembers the author so the next new entry starts with it filled in
    $today = date('Y-m-d');
    $_SESSION['last_lead'] = $_POST['lead'] ?? '';

    // ---- delete (the Delete button submits this same form with action=delete) ----
    if (($_POST['action'] ?? '') === 'delete' && $_POST['id'] !== '') {
        $del_id = (int) $_POST['id'];
        if ($type === 'taxon') {
            // never orphan part of the tree: a taxon must be emptied before it can go
            $kids = $db_connect->execute_query("SELECT COUNT(*) AS c FROM taxa WHERE parent_taxon_id = ?", [$del_id])->fetch_assoc()['c']
                  + $db_connect->execute_query("SELECT COUNT(*) AS c FROM species WHERE taxon_id = ?", [$del_id])->fetch_assoc()['c'];
            if ($kids > 0) {
                header("Location: admin.php?type=taxon&id={$del_id}&status=haschildren");
                exit;
            }
            $db_connect->execute_query("DELETE FROM plate_subjects WHERE subject_type = 'taxon' AND subject_id = ?", [$del_id]);
            $db_connect->execute_query("DELETE FROM taxa WHERE id = ?", [$del_id]);
        } elseif ($type === 'species') {
            // descendants just lose their ancestor link rather than blocking the delete
            $db_connect->execute_query("UPDATE species SET ancestor_id = NULL WHERE ancestor_id = ?", [$del_id]);
            $db_connect->execute_query("DELETE FROM plate_subjects WHERE subject_type = 'species' AND subject_id = ?", [$del_id]);
            $db_connect->execute_query("DELETE FROM species WHERE id = ?", [$del_id]);
        } else {
            $db_connect->execute_query("DELETE FROM plate_subjects WHERE plate_id = ?", [$del_id]);
            $db_connect->execute_query("DELETE FROM plates WHERE id = ?", [$del_id]);
        }
        header("Location: admin.php?status=deleted");
        exit;
    }

    if ($type === 'species') {
        if (trim($_POST['common_name'] ?? '') === '') {
            header("Location: admin.php?type=species&status=noname");
            exit;
        }
        $image_name = handle_image_upload(kept_image('species_image'), 'type=species&');

        $values = [
            $_POST['common_name'],
            nn($_POST['scientific_name']),
            nn($_POST['species_description']),
            nn($image_name),
            nn($_POST['taxon_id']),
            nn($_POST['ancestor_id']),
            nn($_POST['status']),
            nn($_POST['body_length']),
            nn($_POST['mass']),
            nn($_POST['thermy']),
            nn($_POST['encounter_risk']),
            nn($_POST['body_plan']),
            nn($_POST['appearance']),
            nn($_POST['behaviour']),
            nn($_POST['habitat_and_distribution']),
            nn($_POST['evolutionary_history']),
            nn($_POST['first_described']),
            nn($_POST['lead']),
            $today,
        ];

        if ($_POST['id'] !== '') {
            $saved_id = (int) $_POST['id'];
            $values[] = $saved_id;
            $db_connect->execute_query("UPDATE species SET common_name=?, scientific_name=?, species_description=?, species_image=?, taxon_id=?, ancestor_id=?, status=?, body_length=?, mass=?, thermy=?, encounter_risk=?, body_plan=?, appearance=?, behaviour=?, habitat_and_distribution=?, evolutionary_history=?, first_described=?, lead=?, last_edited=? WHERE id=?", $values);
        } else {
            $db_connect->execute_query("INSERT INTO species (common_name, scientific_name, species_description, species_image, taxon_id, ancestor_id, status, body_length, mass, thermy, encounter_risk, body_plan, appearance, behaviour, habitat_and_distribution, evolutionary_history, first_described, lead, last_edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
            $saved_id = $db_connect->insert_id;
        }

        // stay on what was just saved, rather than dropping back to a blank form
        header("Location: admin.php?type=species&id={$saved_id}&status=saved");
        exit;
    } elseif ($type === 'plate') {
        if (trim($_POST['title'] ?? '') === '') {
            header("Location: admin.php?type=plate&status=noname");
            exit;
        }
        $image_name = handle_image_upload($_POST['image'] ?? '', 'type=plate&' . ($_POST['id'] !== '' ? 'id=' . (int) $_POST['id'] . '&' : ''));
        if ($image_name === '') {
            // plates have nothing to fall back on visually without an image
            header("Location: admin.php?type=plate&status=badimage");
            exit;
        }

        $values = [
            $_POST['title'],
            nn($_POST['location']),
            nn($_POST['era_id']),
            nn($_POST['biome_id']),
            $image_name,
            nn($_POST['tour_text']),
            nn($_POST['first_described']),
            nn($_POST['lead']),
            $today,
        ];

        if ($_POST['id'] !== '') {
            $plate_id = (int) $_POST['id'];
            $values[] = $plate_id;
            $db_connect->execute_query("UPDATE plates SET title=?, location=?, era_id=?, biome_id=?, image=?, tour_text=?, first_described=?, lead=?, last_edited=? WHERE id=?", $values);
        } else {
            $db_connect->execute_query("INSERT INTO plates (title, location, era_id, biome_id, image, tour_text, first_described, lead, last_edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
            $plate_id = $db_connect->insert_id;
        }

        // resync the subject roster: wipe and reinsert from whichever boxes are checked
        $db_connect->execute_query("DELETE FROM plate_subjects WHERE plate_id = ?", [$plate_id]);
        foreach (($_POST['subject_taxa'] ?? []) as $tid) {
            $tid = (int) $tid;
            $hx = nn($_POST['hotspot_x']['taxon'][$tid] ?? '');
            $hy = nn($_POST['hotspot_y']['taxon'][$tid] ?? '');
            $db_connect->execute_query("INSERT INTO plate_subjects (plate_id, subject_type, subject_id, x_pct, y_pct) VALUES (?, 'taxon', ?, ?, ?)", [$plate_id, $tid, $hx, $hy]);
        }
        foreach (($_POST['subject_species'] ?? []) as $sid) {
            $sid = (int) $sid;
            $hx = nn($_POST['hotspot_x']['species'][$sid] ?? '');
            $hy = nn($_POST['hotspot_y']['species'][$sid] ?? '');
            $db_connect->execute_query("INSERT INTO plate_subjects (plate_id, subject_type, subject_id, x_pct, y_pct) VALUES (?, 'species', ?, ?, ?)", [$plate_id, $sid, $hx, $hy]);
        }
        // background subjects: one name per line, no species/taxon row, no entry page
        foreach (explode("\n", $_POST['background_subjects'] ?? '') as $bg_name) {
            $bg_name = trim($bg_name);
            if ($bg_name !== '') {
                $db_connect->execute_query("INSERT INTO plate_subjects (plate_id, subject_type, label) VALUES (?, 'unlinked', ?)", [$plate_id, $bg_name]);
            }
        }

        header("Location: admin.php?type=plate&id={$plate_id}&status=saved");
        exit;
    } else {
        if (trim($_POST['name'] ?? '') === '') {
            header("Location: admin.php?type=taxon&status=noname");
            exit;
        }

        $image_name = handle_image_upload(kept_image('image'), 'type=taxon&');

        // kingdom (fauna/flora/tapetae) always matches the parent, so it's only chosen by hand
        // for a top-level taxon; everything below inherits it
        $category = $_POST['category'] ?? 'fauna';
        if (($_POST['parent_taxon_id'] ?? '') !== '') {
            $parent = $db_connect->execute_query("SELECT category FROM taxa WHERE id = ?", [(int) $_POST['parent_taxon_id']])->fetch_assoc();
            if ($parent && $parent['category']) {
                $category = $parent['category'];
            }
        }

        // hex colour: accept with or without '#', store as #RRGGBB; anything malformed is left empty
        $hex = strtoupper(ltrim(trim($_POST['colour_hex'] ?? ''), '#'));
        $colour = preg_match('/^[0-9A-F]{6}$/', $hex) ? '#' . $hex : null;

        $values = [
            $_POST['name'],
            nn($_POST['rank']),
            nn($_POST['parent_taxon_id']),
            nn($_POST['description']),
            $colour,
            nn($_POST['body_plan']),
            nn($_POST['appearance']),
            nn($_POST['behaviour']),
            nn($_POST['habitat_and_distribution']),
            nn($_POST['evolutionary_history']),
            nn($_POST['status']),
            $category,
            nn($image_name),
            nn($_POST['first_described']),
            nn($_POST['lead']),
            $today,
        ];

        if ($_POST['id'] !== '') {
            $saved_id = (int) $_POST['id'];
            $values[] = $saved_id;
            $db_connect->execute_query("UPDATE taxa SET name=?, rank=?, parent_taxon_id=?, description=?, colour_hex=?, body_plan=?, appearance=?, behaviour=?, habitat_and_distribution=?, evolutionary_history=?, status=?, category=?, image=?, first_described=?, lead=?, last_edited=? WHERE id=?", $values);
        } else {
            $db_connect->execute_query("INSERT INTO taxa (name, rank, parent_taxon_id, description, colour_hex, body_plan, appearance, behaviour, habitat_and_distribution, evolutionary_history, status, category, image, first_described, lead, last_edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
            $saved_id = $db_connect->insert_id;
        }
        header("Location: admin.php?type=taxon&id={$saved_id}&status=saved");
        exit;
    }
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// plain admin.php (no type, no id) is the entry list; anything else is a form
$show_list = !isset($_GET['type']) && $id === 0;

$type = $_GET['type'] ?? 'taxon';
if (!in_array($type, ['species', 'plate'], true)) {
    $type = 'taxon';
}

$tables = ['species' => 'species', 'plate' => 'plates', 'taxon' => 'taxa'];

// public page for each type, for the "View entry" links
$view_urls = [
    'taxon'   => 'entry.php?id=',
    'species' => 'entry.php?type=species&id=',
    'plate'   => 'plates.php?id=',
];

$editing = null;
$attached_species = [];
$attached_taxa = [];
$attached_background = [];
$hotspot_coords = ['taxon' => [], 'species' => []];
if ($id > 0) {
    $result = $db_connect->execute_query("SELECT * FROM {$tables[$type]} WHERE id = ?", [$id]);
    $editing = $result->fetch_assoc();

    if ($type === 'plate') {
        $sub_result = $db_connect->execute_query("SELECT subject_type, subject_id, label, x_pct, y_pct FROM plate_subjects WHERE plate_id = ?", [$id]);
        while ($r = $sub_result->fetch_assoc()) {
            if ($r['subject_type'] === 'species') {
                $attached_species[] = (int) $r['subject_id'];
                $hotspot_coords['species'][(int) $r['subject_id']] = ['x' => $r['x_pct'], 'y' => $r['y_pct']];
            } elseif ($r['subject_type'] === 'taxon') {
                $attached_taxa[] = (int) $r['subject_id'];
                $hotspot_coords['taxon'][(int) $r['subject_id']] = ['x' => $r['x_pct'], 'y' => $r['y_pct']];
            } else {
                $attached_background[] = $r['label'];
            }
        }
    }
}

// every taxon with its place in the tree ("Thyrsozoa › Phylum X"), so pickers can tell
// same-named or similar taxa apart. built once in PHP from a single query.
$taxa_by_id = [];
foreach ($db_connect->execute_query("SELECT id, name, rank, parent_taxon_id FROM taxa")->fetch_all(MYSQLI_ASSOC) as $t) {
    $taxa_by_id[(int) $t['id']] = $t;
}
function taxon_path($taxa_by_id, $id)
{
    $names = [];
    $seen = [];
    while ($id && isset($taxa_by_id[$id]) && !isset($seen[$id])) {
        $seen[$id] = true; // guard against a bad parent loop
        array_unshift($names, $taxa_by_id[$id]['name']);
        $id = (int) $taxa_by_id[$id]['parent_taxon_id'];
    }
    return $names;
}
$all_taxa = [];
foreach ($taxa_by_id as $tid => $t) {
    $path = taxon_path($taxa_by_id, $tid);
    array_pop($path); // the taxon itself is the label; the path is what's above it
    $all_taxa[] = [
        'id'   => $tid,
        'name' => $t['name'],
        'sub'  => ucfirst($t['rank'] ?: 'clade') . ($path ? ' · ' . implode(' › ', $path) : ''),
        'path' => taxon_path($taxa_by_id, $tid),
    ];
}
// tree order: siblings sit together under their parent
usort($all_taxa, function ($a, $b) { return strcasecmp(implode('/', $a['path']), implode('/', $b['path'])); });

$all_species = [];
foreach ($db_connect->execute_query("SELECT id, common_name, scientific_name, taxon_id FROM species ORDER BY common_name")->fetch_all(MYSQLI_ASSOC) as $s) {
    $all_species[] = [
        'id'   => (int) $s['id'],
        'name' => $s['common_name'],
        'sub'  => trim(($s['scientific_name'] ?? '') . ($s['taxon_id'] ? ' · ' . implode(' › ', taxon_path($taxa_by_id, (int) $s['taxon_id'])) : ''), ' ·'),
    ];
}

// a taxon can't be its own parent, or sit under one of its own descendants
$blocked_parents = [];
if ($type === 'taxon' && $id > 0) {
    // walk down from this taxon, collecting itself and every descendant
    $stack = [$id];
    while ($stack) {
        $cur = array_pop($stack);
        $blocked_parents[$cur] = true;
        foreach ($taxa_by_id as $tid => $t) {
            if ((int) $t['parent_taxon_id'] === $cur && !isset($blocked_parents[$tid])) {
                $stack[] = $tid;
            }
        }
    }
}

// <option>s for a searchable picker; data-sub is the grey second line (rank/path or scientific name)
function picker_options($items, $selected, $blocked = [])
{
    echo "<option value=''>— none —</option>";
    foreach ($items as $it) {
        if (isset($blocked[$it['id']])) {
            continue;
        }
        $sel = ((string) $selected === (string) $it['id']) ? ' selected' : '';
        echo "<option value='" . $it['id'] . "' data-sub='" . htmlspecialchars($it['sub'], ENT_QUOTES) . "'" . $sel . ">" . htmlspecialchars($it['name']) . "</option>";
    }
}
$all_eras = $db_connect->execute_query("SELECT id, era_name FROM geological_eras ORDER BY era_number");
$all_biomes = $db_connect->execute_query("SELECT id, name FROM biomes ORDER BY name");

// author for a brand-new entry: whoever was used last
$default_lead = $editing['lead'] ?? ($_SESSION['last_lead'] ?? '');

include 'header.php';
?>

<div class="admin-switch">
    <a href="admin.php" class="<?php if ($show_list) echo 'active'; ?>">All entries</a>
    <a href="admin.php?type=taxon" class="<?php if (!$show_list && !$editing && $type === 'taxon') echo 'active'; ?>">+ Taxon</a>
    <a href="admin.php?type=species" class="<?php if (!$show_list && !$editing && $type === 'species') echo 'active'; ?>">+ Species</a>
    <a href="admin.php?type=plate" class="<?php if (!$show_list && !$editing && $type === 'plate') echo 'active'; ?>">+ Plate</a>
    <a href="logout.php?t=<?php echo $_SESSION['csrf']; ?>" class="admin-logout">Log out</a>
</div>

<?php if (!getenv('ADMIN_PASSWORD_HASH')) { ?>
    <p class="admin-msg">Your password hash is coming from login.php (which is in git). Fine locally, but set ADMIN_PASSWORD_HASH in .env (see .env.example) before the site goes live.</p>
<?php } ?>

<?php
$status = $_GET['status'] ?? '';
if ($status === 'saved' && $editing) {
    // what's still blank, so it's obvious what to write next
    $section_cols = ($type === 'plate')
        ? ['tour_text']
        : ['body_plan', 'appearance', 'behaviour', 'habitat_and_distribution', 'evolutionary_history'];
    $empty = count(array_filter($section_cols, function ($c) use ($editing) {
        return trim(strip_tags($editing[$c] ?? '')) === '';
    }));
    echo "<p class='admin-msg'>Saved. <a href='" . $view_urls[$type] . (int) $editing['id'] . "'>View entry &rarr;</a>";
    if ($type !== 'plate' && $empty > 0) {
        echo " <span class='admin-msg-note'>" . $empty . " of " . count($section_cols) . " sections still empty.</span>";
    }
    echo "</p>";
} elseif ($status === 'deleted') {
    echo "<p class='admin-msg'>Deleted.</p>";
} elseif ($status === 'haschildren') {
    echo "<p class='admin-msg'>Can't delete this taxon: it still contains other taxa or species. Move or delete those first.</p>";
} elseif ($status === 'noname') {
    echo "<p class='admin-msg'>Name is required.</p>";
} elseif ($status === 'badimage') {
    echo "<p class='admin-msg'>That file wasn't a valid image (JPG, PNG, GIF or WebP, max 5 MB).</p>";
}
?>

<?php if ($show_list) {
    // ---- entry list: everything editable, newest edits first ----
    $entries = $db_connect->execute_query(
        "(SELECT 'taxon' AS type, id, name AS label, COALESCE(rank, 'clade') AS sub, last_edited FROM taxa)
         UNION ALL
         (SELECT 'species', id, common_name, COALESCE(scientific_name, ''), last_edited FROM species)
         UNION ALL
         (SELECT 'plate', id, title, 'plate', last_edited FROM plates)
         ORDER BY last_edited IS NULL, last_edited DESC, label"
    );
    ?>
    <h2 class="admin-title">All entries</h2>
    <input type="text" class="admin-filter admin-list-filter" data-filter-target="#admin-list" placeholder="Filter by name, rank or type…">
    <div class="admin-list" id="admin-list">
        <?php while ($e = $entries->fetch_assoc()) { ?>
            <div class="admin-list-row">
                <span class="admin-list-type admin-list-type-<?php echo $e['type']; ?>"><?php echo $e['type']; ?></span>
                <a class="admin-list-name" href="admin.php?type=<?php echo $e['type']; ?>&id=<?php echo (int) $e['id']; ?>"><?php echo htmlspecialchars($e['label']); ?></a>
                <span class="admin-list-sub"><?php echo htmlspecialchars($e['sub']); ?></span>
                <span class="admin-list-date"><?php echo htmlspecialchars($e['last_edited'] ?? '—'); ?></span>
                <a class="admin-list-view" href="<?php echo $view_urls[$e['type']] . (int) $e['id']; ?>">view</a>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>

<h2 class="admin-title"><?php echo ($editing ? 'Edit ' : 'Add ') . ucfirst($type); ?></h2>

<link href="js/quill.snow.css" rel="stylesheet">
<script src="js/quill.js"></script>

<form class="admin-form" method="POST" action="admin.php" enctype="multipart/form-data">

    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">

    <?php if ($type === 'species') { ?>

        <!-- Compose block: the top of the entry, edited in place. The inputs ARE
             the faceplate title/sci and the lede, styled to look like the entry. -->
        <div class="entry-compose">
            <div class="faceplate">
                <div class="faceplate-inner">
                    <img id="pv-img" class="faceplate-img" src="<?php echo !empty($editing['species_image']) ? 'images/' . htmlspecialchars($editing['species_image']) : ''; ?>" alt="faceplate" <?php echo empty($editing['species_image']) ? ' style="display:none"' : ''; ?>>
                </div>
                <div class="faceplate-caption">
                    <div>
                        <input type="text" name="common_name" class="compose-name" placeholder="Common name" value="<?php echo htmlspecialchars($editing['common_name'] ?? ''); ?>">
                        <input type="text" name="scientific_name" class="compose-sci" placeholder="Scientific name" value="<?php echo htmlspecialchars($editing['scientific_name'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="entry-body">
                <div class="entry-main-text">
                    <div class="entry-lede">
                        <textarea name="species_description" id="desc-input" style="display:none"><?php echo htmlspecialchars($editing['species_description'] ?? ''); ?></textarea>
                        <div id="desc-editor"></div>
                    </div>

                    <div class="section-heading">Body plan</div>
                    <textarea name="body_plan" class="compose-section" placeholder="Type the body plan…"><?php echo htmlspecialchars($editing['body_plan'] ?? ''); ?></textarea>

                    <div class="section-heading">Appearance</div>
                    <textarea name="appearance" class="compose-section" placeholder="Type the appearance…"><?php echo htmlspecialchars($editing['appearance'] ?? ''); ?></textarea>

                    <div class="section-heading">Behaviour</div>
                    <textarea name="behaviour" class="compose-section" placeholder="Type the behaviour…"><?php echo htmlspecialchars($editing['behaviour'] ?? ''); ?></textarea>

                    <div class="section-heading">Habitat &amp; distribution</div>
                    <textarea name="habitat_and_distribution" class="compose-section" placeholder="Type the habitat and distribution…"><?php echo htmlspecialchars($editing['habitat_and_distribution'] ?? ''); ?></textarea>

                    <div class="section-heading">Evolutionary history</div>
                    <textarea name="evolutionary_history" class="compose-section" placeholder="Type the evolutionary history…"><?php echo htmlspecialchars($editing['evolutionary_history'] ?? ''); ?></textarea>
                </div>

                <div class="entry-sidebar">
                    <div class="info-box">
                        <div class="info-box-header">Classification</div>
                        <div class="info-row">
                            <span class="info-key">Parent taxon</span>
                            <select name="taxon_id" id="taxon_id" class="compose-side" data-picker>
                                <?php picker_options($all_taxa, $editing['taxon_id'] ?? ''); ?>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Ancestor</span>
                            <select name="ancestor_id" id="ancestor_id" class="compose-side" data-picker>
                                <?php picker_options($all_species, $editing['ancestor_id'] ?? '', isset($editing['id']) ? [(int) $editing['id'] => true] : []); ?>
                            </select>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-header">Field Data</div>
                        <div class="info-row">
                            <span class="info-key">Body length</span>
                            <input type="text" name="body_length" class="compose-side" value="<?php echo htmlspecialchars($editing['body_length'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Mass</span>
                            <input type="text" name="mass" class="compose-side" value="<?php echo htmlspecialchars($editing['mass'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Thermy</span>
                            <select name="thermy" class="compose-side">
                                <option value="">—</option>
                                <option value="ectothermy" <?php if (($editing['thermy'] ?? '') === 'ectothermy') echo 'selected'; ?>>Ectothermy</option>
                                <option value="endothermy" <?php if (($editing['thermy'] ?? '') === 'endothermy') echo 'selected'; ?>>Endothermy</option>
                                <option value="mesothermy" <?php if (($editing['thermy'] ?? '') === 'mesothermy') echo 'selected'; ?>>Mesothermy</option>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Encounter risk</span>
                            <select name="encounter_risk" class="compose-side">
                                <option value="">—</option>
                                <option value="low" <?php if (($editing['encounter_risk'] ?? '') === 'low') echo 'selected'; ?>>Low</option>
                                <option value="moderate" <?php if (($editing['encounter_risk'] ?? '') === 'moderate') echo 'selected'; ?>>Moderate</option>
                                <option value="high" <?php if (($editing['encounter_risk'] ?? '') === 'high') echo 'selected'; ?>>High</option>
                                <option value="severe" <?php if (($editing['encounter_risk'] ?? '') === 'severe') echo 'selected'; ?>>Severe</option>
                            </select>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-header">Article Info</div>
                        <div class="info-row">
                            <span class="info-key">Status</span>
                            <select name="status" class="compose-side">
                                <option value="extant" <?php if (($editing['status'] ?? 'extant') === 'extant') echo 'selected'; ?>>Extant</option>
                                <option value="extinct" <?php if (($editing['status'] ?? '') === 'extinct') echo 'selected'; ?>>Extinct</option>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">First described</span>
                            <input type="date" name="first_described" class="compose-side" value="<?php echo htmlspecialchars($editing['first_described'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Author</span>
                            <input type="text" name="lead" class="compose-side" value="<?php echo htmlspecialchars($default_lead); ?>">
                        </div>
                    </div>

                    <?php $own_image = $editing['species_image'] ?? ''; include 'admin-image-box.php'; ?>
                    <input type="hidden" name="species_image" value="<?php echo htmlspecialchars($own_image); ?>">
                </div>
            </div>
        </div>

    <?php } elseif ($type === 'plate') { ?>

        <!-- Compose block: a plate (shared ecosystem illustration), edited in place -->
        <div class="entry-compose">
            <div class="faceplate">
                <div class="faceplate-inner">
                    <div class="hotspot-stage" id="hotspot-stage">
                        <img id="pv-img" class="faceplate-img" src="<?php echo !empty($editing['image']) ? 'images/' . htmlspecialchars($editing['image']) : ''; ?>" alt="faceplate" <?php echo empty($editing['image']) ? ' style="display:none"' : ''; ?>>
                    </div>
                </div>
                <div class="faceplate-caption">
                    <div>
                        <input type="text" name="title" class="compose-name" placeholder="Plate title" value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>">
                        <input type="text" name="location" class="compose-sci" placeholder="Location" value="<?php echo htmlspecialchars($editing['location'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="entry-body">
                <div class="entry-main-text">
                    <div class="entry-lede">
                        <textarea name="tour_text" id="desc-input" style="display:none"><?php echo htmlspecialchars($editing['tour_text'] ?? ''); ?></textarea>
                        <div id="desc-editor"></div>
                    </div>
                </div>

                <div class="entry-sidebar">
                    <div class="info-box">
                        <div class="info-box-header">Plate Info</div>
                        <div class="info-row">
                            <span class="info-key">Era</span>
                            <select name="era_id" class="compose-side">
                                <option value="">— none —</option>
                                <?php while ($e = $all_eras->fetch_assoc()) { ?>
                                    <option value="<?php echo $e['id']; ?>" <?php if (($editing['era_id'] ?? '') == $e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['era_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Biome</span>
                            <select name="biome_id" class="compose-side">
                                <option value="">— none —</option>
                                <?php while ($b = $all_biomes->fetch_assoc()) { ?>
                                    <option value="<?php echo $b['id']; ?>" <?php if (($editing['biome_id'] ?? '') == $b['id']) echo 'selected'; ?>><?php echo htmlspecialchars($b['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- a plate IS its image, so the upload is always shown (and required) -->
                        <input type="hidden" name="image" value="<?php echo htmlspecialchars($editing['image'] ?? ''); ?>">
                        <div class="info-row">
                            <div id="drop-zone" class="drop-zone">
                                <span class="drop-zone-text"><?php echo !empty($editing['image']) ? 'Drop a new image to replace this one' : 'Drag the plate image here, or click to choose'; ?></span>
                                <input type="file" name="image_upload" id="file-input" accept="image/*">
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-key">First described</span>
                            <input type="date" name="first_described" class="compose-side" value="<?php echo htmlspecialchars($editing['first_described'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Author</span>
                            <input type="text" name="lead" class="compose-side" value="<?php echo htmlspecialchars($default_lead); ?>">
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-header">Subjects Pictured</div>
                        <div class="info-row">
                            <input type="text" class="subject-search" id="subject-search" placeholder="Search to add a species or taxon…" autocomplete="off">
                        </div>
                        <!-- every subject is in the form, but only ticked ones (and search matches) are shown -->
                        <div class="plate-subject-list" id="plate-subject-list">
                            <?php
                            $subject_groups = [
                                ['label' => 'Species', 'type' => 'species', 'items' => $all_species, 'attached' => $attached_species, 'field' => 'subject_species[]'],
                                ['label' => 'Taxa',    'type' => 'taxon',   'items' => $all_taxa,    'attached' => $attached_taxa,    'field' => 'subject_taxa[]'],
                            ];
                            foreach ($subject_groups as $g) { ?>
                                <div class="plate-subject-group"><?php echo $g['label']; ?></div>
                                <?php foreach ($g['items'] as $it) {
                                    $sid = (int) $it['id'];
                                    $hx = $hotspot_coords[$g['type']][$sid]['x'] ?? '';
                                    $hy = $hotspot_coords[$g['type']][$sid]['y'] ?? '';
                                ?>
                                    <label class="plate-subject-row">
                                        <input type="checkbox" name="<?php echo $g['field']; ?>" value="<?php echo $sid; ?>" <?php echo in_array($sid, $g['attached'], true) ? 'checked' : ''; ?>>
                                        <span class="plate-subject-text">
                                            <span class="plate-subject-name"><?php echo htmlspecialchars($it['name']); ?></span>
                                            <span class="plate-subject-sub"><?php echo htmlspecialchars($it['sub']); ?></span>
                                        </span>
                                        <button type="button" class="hotspot-place-btn<?php echo ($hx !== '' && $hy !== '') ? ' is-placed' : ''; ?>" data-subject-type="<?php echo $g['type']; ?>" data-subject-id="<?php echo $sid; ?>" data-checkbox-name="<?php echo $g['field']; ?>" title="Place on the image">&#128205;</button>
                                        <input type="hidden" name="hotspot_x[<?php echo $g['type']; ?>][<?php echo $sid; ?>]" id="hx-<?php echo $g['type']; ?>-<?php echo $sid; ?>" value="<?php echo htmlspecialchars($hx); ?>">
                                        <input type="hidden" name="hotspot_y[<?php echo $g['type']; ?>][<?php echo $sid; ?>]" id="hy-<?php echo $g['type']; ?>-<?php echo $sid; ?>" value="<?php echo htmlspecialchars($hy); ?>">
                                    </label>
                                <?php } ?>
                            <?php } ?>
                            <div class="plate-subject-empty" id="plate-subject-empty">Nothing tagged yet. Search above to add subjects.</div>
                        </div>
                        <div class="hotspot-hint">&#128205; places a subject on the image: click it, then click the spot.</div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-header">Background Subjects</div>
                        <div class="info-row">
                            <textarea name="background_subjects" class="compose-section" placeholder="One name per line — no page, no entry, just a label in this plate's roster"><?php echo htmlspecialchars(implode("\n", $attached_background)); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>

        <!-- Compose block: the full taxon entry, edited in place -->
        <div class="entry-compose">
            <div class="faceplate">
                <div class="faceplate-inner">
                    <img id="pv-img" class="faceplate-img" src="<?php echo !empty($editing['image']) ? 'images/' . htmlspecialchars($editing['image']) : ''; ?>" alt="faceplate" <?php echo empty($editing['image']) ? ' style="display:none"' : ''; ?>>
                </div>
                <div class="faceplate-caption">
                    <div>
                        <input type="text" name="name" class="compose-name" placeholder="Name" value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="entry-body">
                <div class="entry-main-text">
                    <div class="entry-lede">
                        <textarea name="description" id="desc-input" style="display:none"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                        <div id="desc-editor"></div>
                    </div>

                    <div class="section-heading">Body plan</div>
                    <textarea name="body_plan" class="compose-section" placeholder="Type the body plan…"><?php echo htmlspecialchars($editing['body_plan'] ?? ''); ?></textarea>

                    <div class="section-heading">Appearance</div>
                    <textarea name="appearance" class="compose-section" placeholder="Type the appearance…"><?php echo htmlspecialchars($editing['appearance'] ?? ''); ?></textarea>

                    <div class="section-heading">Behaviour</div>
                    <textarea name="behaviour" class="compose-section" placeholder="Type the behaviour…"><?php echo htmlspecialchars($editing['behaviour'] ?? ''); ?></textarea>

                    <div class="section-heading">Habitat &amp; distribution</div>
                    <textarea name="habitat_and_distribution" class="compose-section" placeholder="Type the habitat and distribution…"><?php echo htmlspecialchars($editing['habitat_and_distribution'] ?? ''); ?></textarea>

                    <div class="section-heading">Evolutionary history</div>
                    <textarea name="evolutionary_history" class="compose-section" placeholder="Type the evolutionary history…"><?php echo htmlspecialchars($editing['evolutionary_history'] ?? ''); ?></textarea>
                </div>

                <div class="entry-sidebar">
                    <div class="info-box">
                        <div class="info-box-header">Classification</div>
                        <div class="info-row">
                            <span class="info-key">Rank</span>
                            <select name="rank" class="compose-side">
                                <option value="">—</option>
                                <option value="kingdom" <?php if (($editing['rank'] ?? '') === 'kingdom') echo 'selected'; ?>>Kingdom</option>
                                <option value="phylum" <?php if (($editing['rank'] ?? '') === 'phylum') echo 'selected'; ?>>Phylum</option>
                                <option value="class" <?php if (($editing['rank'] ?? '') === 'class') echo 'selected'; ?>>Class</option>
                                <option value="order" <?php if (($editing['rank'] ?? '') === 'order') echo 'selected'; ?>>Order</option>
                                <option value="family" <?php if (($editing['rank'] ?? '') === 'family') echo 'selected'; ?>>Family</option>
                                <option value="genus" <?php if (($editing['rank'] ?? '') === 'genus') echo 'selected'; ?>>Genus</option>
                                <option value="clade" <?php if (($editing['rank'] ?? '') === 'clade') echo 'selected'; ?>>Clade</option>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Parent taxon</span>
                            <select name="parent_taxon_id" id="parent_taxon_id" class="compose-side" data-picker>
                                <?php picker_options($all_taxa, $editing['parent_taxon_id'] ?? '', $blocked_parents); ?>
                            </select>
                        </div>
                        <!-- only a top-level taxon picks its kingdom; children inherit it on save (JS hides this when a parent is set) -->
                        <div class="info-row" id="category-row">
                            <span class="info-key">Kingdom group</span>
                            <select name="category" class="compose-side">
                                <?php foreach (kingdom_labels($db_connect) as $cat => $kname) { ?>
                                    <option value="<?php echo $cat; ?>" <?php if (($editing['category'] ?? 'fauna') === $cat) echo 'selected'; ?>><?php echo htmlspecialchars($kname) . ' (' . KINGDOM_GLOSS[$cat] . ')'; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-header">Article Info</div>
                        <div class="info-row">
                            <span class="info-key">Status</span>
                            <select name="status" class="compose-side">
                                <option value="extant" <?php if (($editing['status'] ?? 'extant') === 'extant') echo 'selected'; ?>>Extant</option>
                                <option value="extinct" <?php if (($editing['status'] ?? '') === 'extinct') echo 'selected'; ?>>Extinct</option>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Colour</span>
                            <!-- typed hex instead of the browser's colour wheel: exact values, easy to copy between taxa -->
                            <span class="hex-field">
                                <span class="hex-swatch" id="hex-swatch" style="background: <?php echo htmlspecialchars($editing['colour_hex'] ?? 'transparent'); ?>;"></span>
                                <input type="text" name="colour_hex" id="hex-input" class="compose-side" maxlength="7" placeholder="#5C6672"
                                       pattern="#?[0-9A-Fa-f]{6}" title="Six hex digits, e.g. #5C6672"
                                       value="<?php echo htmlspecialchars($editing['colour_hex'] ?? ''); ?>">
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">First described</span>
                            <input type="date" name="first_described" class="compose-side" value="<?php echo htmlspecialchars($editing['first_described'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Author</span>
                            <input type="text" name="lead" class="compose-side" value="<?php echo htmlspecialchars($default_lead); ?>">
                        </div>
                    </div>

                    <?php $own_image = $editing['image'] ?? ''; include 'admin-image-box.php'; ?>
                    <input type="hidden" name="image" value="<?php echo htmlspecialchars($own_image); ?>">
                </div>
            </div>
        </div>

    <?php } ?>

    <div class="admin-actions">
        <button type="submit">Save</button>
        <?php if ($editing) { ?>
            <a class="admin-view-link" href="<?php echo $view_urls[$type] . (int) $editing['id']; ?>">View entry &rarr;</a>
            <!-- same form, different action: the handler checks action=delete before anything else -->
            <button type="submit" name="action" value="delete" class="admin-delete"
                    formnovalidate onclick="return confirm('Delete this <?php echo $type; ?> permanently? This can\'t be undone.');">Delete</button>
        <?php } ?>
    </div>

</form>

<?php } // end form view ?>

<script>
    // --- shared across all three compose forms: live-filter a <select> or a checkbox list ---
    document.querySelectorAll('.admin-filter').forEach(function (input) {
        const target = document.querySelector(input.dataset.filterTarget);
        if (!target) return;
        input.addEventListener('input', function () {
            const q = input.value.trim().toLowerCase();
            if (target.tagName === 'SELECT') {
                Array.from(target.options).forEach(function (opt) {
                    opt.hidden = q !== '' && !opt.textContent.toLowerCase().includes(q);
                });
            } else {
                target.querySelectorAll('label, .admin-list-row').forEach(function (row) {
                    row.style.display = (q === '' || row.textContent.toLowerCase().includes(q)) ? '' : 'none';
                });
            }
        });
    });

    // --- searchable pickers: each <select data-picker> becomes one type-to-search box.
    //     the real <select> stays in the form (hidden) and is what actually gets submitted ---
    document.querySelectorAll('select[data-picker]').forEach(function (select) {
        const wrap = document.createElement('div');
        wrap.className = 'picker';
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'picker-input compose-side';
        input.placeholder = 'Search…';
        input.autocomplete = 'off';
        const list = document.createElement('div');
        list.className = 'picker-list';

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        wrap.appendChild(input);
        wrap.appendChild(list);
        select.style.display = 'none';

        const options = Array.from(select.options);
        const showSelected = function () {
            const opt = select.options[select.selectedIndex];
            input.value = (opt && opt.value !== '') ? opt.textContent : '';
        };

        const render = function (q) {
            list.innerHTML = '';
            q = q.trim().toLowerCase();
            let shown = 0;
            options.forEach(function (opt) {
                const sub = opt.dataset.sub || '';
                const hay = (opt.textContent + ' ' + sub).toLowerCase();
                if (q !== '' && !hay.includes(q)) return;
                if (shown >= 60) return; // keep the list short; typing narrows it
                shown++;
                const item = document.createElement('div');
                item.className = 'picker-item' + (opt.value === select.value ? ' is-current' : '');
                item.innerHTML = '<span class="picker-name"></span><span class="picker-sub"></span>';
                item.querySelector('.picker-name').textContent = opt.textContent;
                item.querySelector('.picker-sub').textContent = sub;
                // mousedown (not click) so it fires before the input's blur closes the list
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change')); // keeps the kingdom-group toggle in sync
                    showSelected();
                    wrap.classList.remove('is-open');
                });
                list.appendChild(item);
            });
            if (shown === 0) {
                list.innerHTML = '<div class="picker-none">No matches</div>';
            }
        };

        input.addEventListener('focus', function () { input.select(); render(''); wrap.classList.add('is-open'); });
        input.addEventListener('input', function () { render(input.value); wrap.classList.add('is-open'); });
        input.addEventListener('blur', function () { wrap.classList.remove('is-open'); showSelected(); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                // Enter picks the top match instead of submitting the whole form
                e.preventDefault();
                const first = list.querySelector('.picker-item');
                if (first) first.dispatchEvent(new MouseEvent('mousedown'));
                input.blur();
            } else if (e.key === 'Escape') {
                input.blur();
            }
        });

        showSelected();
    });

    // --- plate subjects: show only what's ticked, plus search matches to add ---
    const subjectSearch = document.getElementById('subject-search');
    const subjectList = document.getElementById('plate-subject-list');
    if (subjectSearch && subjectList) {
        const syncSubjects = function () {
            const q = subjectSearch.value.trim().toLowerCase();
            let anyShown = false;
            subjectList.querySelectorAll('.plate-subject-row').forEach(function (row) {
                const ticked = row.querySelector('input[type=checkbox]').checked;
                const match = q !== '' && row.textContent.toLowerCase().includes(q);
                const show = ticked || match;
                row.style.display = show ? '' : 'none';
                row.classList.toggle('is-ticked', ticked);
                anyShown = anyShown || show;
            });
            // a group heading only shows when something under it does
            subjectList.querySelectorAll('.plate-subject-group').forEach(function (head) {
                let el = head.nextElementSibling, visible = false;
                while (el && el.classList.contains('plate-subject-row')) {
                    if (el.style.display !== 'none') visible = true;
                    el = el.nextElementSibling;
                }
                head.style.display = visible ? '' : 'none';
            });
            document.getElementById('plate-subject-empty').style.display = anyShown ? 'none' : '';
        };
        subjectSearch.addEventListener('input', syncSubjects);
        subjectSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault(); // don't submit the plate mid-search
        });
        // ticking something from the search results: clear the search so the list settles back
        subjectList.addEventListener('change', function () {
            subjectSearch.value = '';
            syncSubjects();
        });
        syncSubjects();
    }

    // --- hex colour field: swatch follows what's typed ---
    const hexInput = document.getElementById('hex-input');
    const hexSwatch = document.getElementById('hex-swatch');
    if (hexInput && hexSwatch) {
        hexInput.addEventListener('input', function () {
            let v = hexInput.value.trim();
            if (v !== '' && v[0] !== '#') v = '#' + v;
            hexSwatch.style.background = /^#[0-9A-Fa-f]{6}$/.test(v) ? v : 'transparent';
        });
    }

    // --- taxon form: the kingdom group only matters for a top-level taxon ---
    const parentSelect = document.getElementById('parent_taxon_id');
    const categoryRow = document.getElementById('category-row');
    if (parentSelect && categoryRow) {
        const syncCategory = function () {
            categoryRow.style.display = parentSelect.value === '' ? '' : 'none';
        };
        parentSelect.addEventListener('change', syncCategory);
        syncCategory();
    }

    // --- shared on all three forms: the faceplate preview image ---
    const pvImg = document.getElementById('pv-img');

    // --- plate hotspots: click a subject's pin button, then click its spot on the image ---
    const hotspotStage = document.getElementById('hotspot-stage');
    if (hotspotStage) {
        let placing = null; // { type, id } while armed, else null

        function drawHotspotMarkers() {
            hotspotStage.querySelectorAll('.hotspot-marker').forEach(function (m) { m.remove(); });
            document.querySelectorAll('.hotspot-place-btn').forEach(function (btn) {
                const type = btn.dataset.subjectType;
                const id = btn.dataset.subjectId;
                const x = document.getElementById('hx-' + type + '-' + id).value;
                const y = document.getElementById('hy-' + type + '-' + id).value;
                if (x === '' || y === '') return;
                const marker = document.createElement('div');
                marker.className = 'hotspot-marker';
                marker.style.left = x + '%';
                marker.style.top = y + '%';
                marker.title = btn.closest('.plate-subject-row').querySelector('.plate-subject-name').textContent.trim();
                hotspotStage.appendChild(marker);
            });
            // pin buttons go solid once their subject has a spot
            document.querySelectorAll('.hotspot-place-btn').forEach(function (btn) {
                const placed = document.getElementById('hx-' + btn.dataset.subjectType + '-' + btn.dataset.subjectId).value !== '';
                btn.classList.toggle('is-placed', placed);
            });
        }

        document.querySelectorAll('.hotspot-place-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                placing = { type: btn.dataset.subjectType, id: btn.dataset.subjectId, checkboxName: btn.dataset.checkboxName };
                hotspotStage.classList.add('is-placing');
            });
        });

        hotspotStage.addEventListener('click', function (e) {
            if (!placing || !pvImg || pvImg.style.display === 'none') return;
            const rect = pvImg.getBoundingClientRect();
            const xPct = ((e.clientX - rect.left) / rect.width * 100).toFixed(2);
            const yPct = ((e.clientY - rect.top) / rect.height * 100).toFixed(2);
            document.getElementById('hx-' + placing.type + '-' + placing.id).value = xPct;
            document.getElementById('hy-' + placing.type + '-' + placing.id).value = yPct;

            // placing a hotspot implies this subject is pictured — check its box too
            document.querySelectorAll('input[name="' + placing.checkboxName + '"]').forEach(function (cb) {
                if (cb.value === placing.id) cb.checked = true;
            });

            drawHotspotMarkers();
            hotspotStage.classList.remove('is-placing');
            placing = null;
        });

        drawHotspotMarkers(); // show any coordinates already saved, when editing a plate
    }

    // --- drag and drop image upload ---
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');

    // show the chosen file on the faceplate before it uploads
    function preview(file) {
        if (file && pvImg) {
            pvImg.src = URL.createObjectURL(file);
            pvImg.style.display = '';
        }
    }

    if (dropZone && fileInput) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault(); // REQUIRED — lets the drop happen
            dropZone.classList.add('dragover'); // highlight the zone
        });

        dropZone.addEventListener('dragleave', function() {
            dropZone.classList.remove('dragover'); // un-highlight when it leaves
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files; // put the dropped file INTO the real input
            preview(e.dataTransfer.files[0]); // show it
        });

        fileInput.addEventListener('change', function() {
            preview(fileInput.files[0]); // also show it when picked via the dialog
        });
    }

    // --- Quill rich-text editor: only on the Species form, where #desc-editor exists ---
    if (document.getElementById('desc-editor')) {
        const quill = new Quill('#desc-editor', {
            theme: 'snow'
        });
        const descInput = document.getElementById('desc-input');
        quill.root.innerHTML = descInput.value;

        // copy the editor's HTML into the hidden textarea before submit
        document.querySelector('.admin-form').addEventListener('submit', function() {
            descInput.value = quill.root.innerHTML;
        });
    }
</script>



<?php include 'footer.php'; ?>