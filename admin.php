<?php
session_start();
// GATE: no valid session -> bounce to the login page before anything else runs.
if (empty($_SESSION['authed'])) {
    header('Location: login.php');
    exit;
}

$active = '';
include 'db_config.php';

// empty string -> null, for nullable columns (FKs, dates, enums)
function nn($v)
{
    return $v === '' ? null : $v;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'taxon';
    if ($type !== 'species') {
        $type = 'taxon';
    }

    if ($type === 'species') {
        if (trim($_POST['common_name'] ?? '') === '') {
            header("Location: admin.php?type=species&status=noname");
            exit;
        }
        $image_name = $_POST['species_image'];
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
            // 0. the upload must have actually succeeded before we touch the temp file.
            //    (e.g. a file bigger than upload_max_filesize fails here with an empty tmp_name.)
            if ($_FILES['image_upload']['error'] !== UPLOAD_ERR_OK) {
                header("Location: admin.php?type=species&status=badimage");
                exit;
            }
            // 1. is it really an image, and not too big? getimagesize returns false if not.
            $info = getimagesize($_FILES['image_upload']['tmp_name']);
            if ($info === false || $_FILES['image_upload']['size'] > 5 * 1024 * 1024) {
                header("Location: admin.php?type=species&status=badimage");
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
                header("Location: admin.php?type=species&status=badimage");
                exit;
            }
            $ext = $ext_map[$info[2]];

            // 3. generate my own safe, unique filename.
            $safe = uniqid('img_', true) . '.' . $ext;

            // 4. move_uploaded_file verifies it was a genuine HTTP upload before moving it.
            if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], __DIR__ . '/images/' . $safe)) {
                header("Location: admin.php?type=species&status=badimage");
                exit;
            }

            // 5. this is the name we store in the DB.
            $image_name = $safe;
        }

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
            nn($_POST['last_edited']),
        ];

        if ($_POST['id'] !== '') {
            $values[] = $_POST['id'];
            $db_connect->execute_query("UPDATE species SET common_name=?, scientific_name=?, species_description=?, species_image=?, taxon_id=?, ancestor_id=?, status=?, body_length=?, mass=?, thermy=?, encounter_risk=?, body_plan=?, appearance=?, behaviour=?, habitat_and_distribution=?, evolutionary_history=?, first_described=?, lead=?, last_edited=? WHERE id=?", $values);
        } else {
            $db_connect->execute_query("INSERT INTO species (common_name, scientific_name, species_description, species_image, taxon_id, ancestor_id, status, body_length, mass, thermy, encounter_risk, body_plan, appearance, behaviour, habitat_and_distribution, evolutionary_history, first_described, lead, last_edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
        }

        header("Location: admin.php?type=species&status=saved");
        exit;
    } else {
        if (trim($_POST['name'] ?? '') === '') {
            header("Location: admin.php?status=noname");
            exit;
        }

        $image_name = $_POST['image'];
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
            // 0. the upload must have actually succeeded before we touch the temp file.
            //    (e.g. a file bigger than upload_max_filesize fails here with an empty tmp_name.)
            if ($_FILES['image_upload']['error'] !== UPLOAD_ERR_OK) {
                header("Location: admin.php?status=badimage");
                exit;
            }
            // 1. is it really an image, and not too big? getimagesize returns false if not.
            $info = getimagesize($_FILES['image_upload']['tmp_name']);
            if ($info === false || $_FILES['image_upload']['size'] > 5 * 1024 * 1024) {
                header("Location: admin.php?status=badimage");
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
                header("Location: admin.php?status=badimage");
                exit;
            }
            $ext = $ext_map[$info[2]];

            // 3. generate our own safe, unique filename.
            $safe = uniqid('img_', true) . '.' . $ext;

            // 4. move_uploaded_file verifies it was a genuine HTTP upload before moving it.
            if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], __DIR__ . '/images/' . $safe)) {
                header("Location: admin.php?status=badimage");
                exit;
            }

            // 5. this is the name we store in the DB.
            $image_name = $safe;
        }

        $values = [
            $_POST['name'],
            nn($_POST['rank']),
            nn($_POST['parent_taxon_id']),
            nn($_POST['description']),
            nn($_POST['colour_hex']),
            nn($_POST['body_plan']),
            nn($_POST['appearance']),
            nn($_POST['behaviour']),
            nn($_POST['habitat_and_distribution']),
            nn($_POST['evolutionary_history']),
            nn($_POST['status']),
            nn($_POST['category']),
            nn($image_name),
            nn($_POST['first_described']),
            nn($_POST['lead']),
            nn($_POST['last_edited']),
        ];

        if ($_POST['id'] !== '') {
            $values[] = $_POST['id'];
            $db_connect->execute_query("UPDATE taxa SET name=?, rank=?, parent_taxon_id=?, description=?, colour_hex=?, body_plan=?, appearance=?, behaviour=?, habitat_and_distribution=?, evolutionary_history=?, status=?, category=?, image=?, first_described=?, lead=?, last_edited=? WHERE id=?", $values);
        } else {
            $db_connect->execute_query("INSERT INTO taxa (name, rank, parent_taxon_id, description, colour_hex, body_plan, appearance, behaviour, habitat_and_distribution, evolutionary_history, status, category, image, first_described, lead, last_edited) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
        }
        header("Location: admin.php?status=saved");
        exit;
    }
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$type = $_GET['type'] ?? 'taxon';
if ($type !== 'species') {
    $type = 'taxon';
}

$editing = null;
if ($id > 0) {
    $table = ($type === 'species') ? 'species' : 'taxa';
    $result = $db_connect->execute_query("SELECT * FROM $table WHERE id = ?", [$id]);
    $editing = $result->fetch_assoc();
}

$all_taxa = $db_connect->execute_query("SELECT id, name FROM taxa ORDER BY name");
$all_species = $db_connect->execute_query("SELECT id, common_name FROM species ORDER BY common_name");

include 'header.php';
?>

<h2 class="admin-title"><?php echo ($editing ? 'Edit ' : 'Add ') . ucfirst($type); ?></h2>

<?php
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'saved') {
        echo "<p class='admin-msg'>Saved!</p>";
    } elseif ($_GET['status'] === 'noname') {
        echo "<p class='admin-msg'>Name is required.</p>";
    } elseif ($_GET['status'] === 'badimage') {
        echo "<p class='admin-msg'>That file wasn't a valid image (JPG, PNG, GIF or WebP, max 5 MB).</p>";
    }
}
?>

<link href="js/quill.snow.css" rel="stylesheet">
<script src="js/quill.js"></script>

<div class="admin-switch">
    <select onchange="location.href='admin.php?type=' + this.value">
        <option value="taxon" <?php if ($type === 'taxon') echo 'selected'; ?>>Taxon</option>
        <option value="species" <?php if ($type === 'species') echo 'selected'; ?>>Species</option>
    </select>
    <a href="logout.php" class="admin-logout">Log out</a>
</div>

<form class="admin-form" method="POST" action="admin.php" enctype="multipart/form-data">

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
                            <select name="taxon_id" class="compose-side">
                                <option value="">— none —</option>
                                <?php while ($t = $all_taxa->fetch_assoc()) { ?>
                                    <option value="<?php echo $t['id']; ?>" <?php if (($editing['taxon_id'] ?? '') == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Ancestor</span>
                            <select name="ancestor_id" class="compose-side">
                                <option value="">— none —</option>
                                <?php while ($s = $all_species->fetch_assoc()) { ?>
                                    <option value="<?php echo $s['id']; ?>" <?php if (($editing['ancestor_id'] ?? '') == $s['id']) echo 'selected'; ?>><?php echo htmlspecialchars($s['common_name']); ?></option>
                                <?php } ?>
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
                            <span class="info-key">Image file</span>
                            <input type="text" name="species_image" id="img-field" class="compose-side" value="<?php echo htmlspecialchars($editing['species_image'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <div id="drop-zone" class="drop-zone">
                                <span class="drop-zone-text">Drag an image here, or click to choose</span>
                                <input type="file" name="image_upload" id="file-input" accept="image/*">
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-key">First described</span>
                            <input type="text" name="first_described" class="compose-side" value="<?php echo htmlspecialchars($editing['first_described'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Author</span>
                            <input type="text" name="lead" class="compose-side" value="<?php echo htmlspecialchars($editing['lead'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Last edited</span>
                            <input type="text" name="last_edited" class="compose-side" value="<?php echo htmlspecialchars($editing['last_edited'] ?? ''); ?>">
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
                            <select name="parent_taxon_id" class="compose-side">
                                <option value="">— none —</option>
                                <?php while ($t = $all_taxa->fetch_assoc()) { ?>
                                    <option value="<?php echo $t['id']; ?>" <?php if (($editing['parent_taxon_id'] ?? '') == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Category</span>
                            <select name="category" class="compose-side">
                                <option value="fauna" <?php if (($editing['category'] ?? 'fauna') === 'fauna') echo 'selected'; ?>>Fauna</option>
                                <option value="flora" <?php if (($editing['category'] ?? '') === 'flora') echo 'selected'; ?>>Flora</option>
                                <option value="tapetae" <?php if (($editing['category'] ?? '') === 'tapetae') echo 'selected'; ?>>Tapetae</option>
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
                            <input type="color" name="colour_hex" class="compose-side" value="<?php echo htmlspecialchars($editing['colour_hex'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Image file</span>
                            <input type="text" name="image" id="img-field" class="compose-side" value="<?php echo htmlspecialchars($editing['image'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <div id="drop-zone" class="drop-zone">
                                <span class="drop-zone-text">Drag an image here, or click to choose</span>
                                <input type="file" name="image_upload" id="file-input" accept="image/*">
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-key">First described</span>
                            <input type="text" name="first_described" class="compose-side" value="<?php echo htmlspecialchars($editing['first_described'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Author</span>
                            <input type="text" name="lead" class="compose-side" value="<?php echo htmlspecialchars($editing['lead'] ?? ''); ?>">
                        </div>
                        <div class="info-row">
                            <span class="info-key">Last edited</span>
                            <input type="text" name="last_edited" class="compose-side" value="<?php echo htmlspecialchars($editing['last_edited'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>

    <button type="submit">Save</button>

</form>

<script>
    // --- shared on BOTH forms (taxon + species) ---
    const pvImg = document.getElementById('pv-img');
    const fImg = document.getElementById('img-field');

    // swap the faceplate image live when the filename text field changes
    if (fImg && pvImg) {
        fImg.addEventListener('input', function() {
            if (fImg.value) {
                pvImg.src = 'images/' + fImg.value;
                pvImg.style.display = '';
            } else {
                pvImg.style.display = 'none';
            }
        });
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