<?php
// admin-image-box.php — the optional "own image" upload for the taxon and species forms.
// Most entries borrow their picture from a plate they're tagged in, so this stays folded
// away unless the entry already has an image of its own. Expects $own_image (filename or '').
?>
<details class="info-box admin-image-box" <?php echo $own_image !== '' ? 'open' : ''; ?>>
    <summary class="info-box-header">Own Image (optional)</summary>
    <div class="info-row admin-image-hint">
        Leave empty to use the plate this entry is tagged in.
    </div>
    <div class="info-row">
        <div id="drop-zone" class="drop-zone">
            <span class="drop-zone-text"><?php echo $own_image !== '' ? 'Drop a new image to replace it' : 'Drag an image here, or click to choose'; ?></span>
            <input type="file" name="image_upload" id="file-input" accept="image/*">
        </div>
    </div>
    <?php if ($own_image !== '') { ?>
        <label class="info-row admin-image-remove">
            <input type="checkbox" name="remove_image" value="1"> Remove this image (fall back to the plate)
        </label>
    <?php } ?>
</details>
