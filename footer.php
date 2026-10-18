
<div class="footer">
    <div class="footer-id">Thrysus Project · Dionysus</div>
    <?php if (!empty($_SESSION['authed'])) { ?>
        <a href="admin.php" class="footer-admin">Admin</a>
    <?php } else { ?>
        <a href="login.php" class="footer-admin">Log in</a>
    <?php } ?>
</div>

</div><!-- /shell -->

<script>
// cladogram collapse: delegated so it works for every panel on the page, no matter how deep
document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('clado-caret')) return;
    e.preventDefault(); // caret sits inside the entry link — don't navigate
    e.target.closest('.clado-node').classList.toggle('is-collapsed');
});

// live search: debounced fetch to search-suggest.php, rendered as a dropdown under the box.
// Enter/no-JS still falls through to the normal search.php form submit.
(function () {
    const input = document.getElementById('search-input');
    const box = document.getElementById('search-suggestions');
    if (!input || !box) return;

    let timer = null;
    let activeIndex = -1;
    let latest = 0; // id of the newest request; replies to older ones are ignored

    function closeBox() {
        box.classList.remove('is-open');
        activeIndex = -1;
    }

    function setActive(i) {
        const items = box.querySelectorAll('.search-suggestion');
        activeIndex = i;
        items.forEach(function (el, n) { el.classList.toggle('is-active', n === i); });
        if (items[i]) items[i].scrollIntoView({ block: 'nearest' });
    }

    function esc(s) {
        const div = document.createElement('div');
        div.textContent = s == null ? '' : s;
        return div.innerHTML;
    }

    function renderResults(results, q) {
        // each row: name on the left, its rank / scientific name / location in grey, and a type tag
        let html = results.map(function (r) {
            return '<a class="search-suggestion" href="' + esc(r.link) + '">' +
                '<span class="search-suggestion-text">' +
                    '<span class="search-suggestion-label">' + esc(r.label) + '</span>' +
                    (r.sub ? '<span class="search-suggestion-detail">' + esc(r.sub) + '</span>' : '') +
                '</span>' +
                '<span class="search-suggestion-sub">' + esc(r.type) + '</span>' +
                '</a>';
        }).join('');
        if (results.length === 0) {
            html = '<div class="search-suggestion-empty">No matches for “' + esc(q) + '”</div>';
        }
        // always offer the full results page as the last row
        html += '<a class="search-suggestion search-suggestion-all" href="search.php?q=' + encodeURIComponent(q) + '">' +
            '<span class="search-suggestion-label">See all results for “' + esc(q) + '”</span></a>';
        box.innerHTML = html;
        box.classList.add('is-open');
        activeIndex = -1;
        box.querySelectorAll('.search-suggestion').forEach(function (el, i) {
            el.addEventListener('mousemove', function () { setActive(i); });
        });
    }

    function fetchResults() {
        const q = input.value.trim();
        if (q.length < 2) {
            closeBox();
            return;
        }
        const id = ++latest;
        fetch('search-suggest.php?q=' + encodeURIComponent(q))
            .then(function (res) { return res.json(); })
            .then(function (results) {
                if (id === latest) renderResults(results, q); // drop replies that arrive out of order
            })
            .catch(closeBox);
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(fetchResults, 150);
    });

    // coming back to the box with text still in it brings the list back
    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2) fetchResults();
    });

    input.addEventListener('keydown', function (e) {
        const items = box.querySelectorAll('.search-suggestion');
        if (e.key === 'Escape') {
            closeBox();
            input.blur();
        } else if (e.key === 'ArrowDown' && items.length) {
            e.preventDefault();
            if (!box.classList.contains('is-open')) { fetchResults(); return; }
            setActive((activeIndex + 1) % items.length);
        } else if (e.key === 'ArrowUp' && items.length) {
            e.preventDefault();
            setActive((activeIndex - 1 + items.length) % items.length);
        } else if (e.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
            e.preventDefault();
            window.location.href = items[activeIndex].getAttribute('href');
        }
        // Enter with nothing highlighted falls through to the normal search.php submit
    });

    document.addEventListener('click', function (e) {
        if (e.target !== input && !box.contains(e.target)) {
            closeBox();
        }
    });
})();
</script>

<?php if (!empty($_SESSION['authed'])) { ?>
<script>
// in-place editing (logged in only): every block marked data-edit-key (see site-text.php) gets a ✎
// button. click it, change the text, Save posts to save-text.php, and the page reloads with the change.
(function () {
    const csrf = <?php echo json_encode($_SESSION['csrf']); ?>;

    document.querySelectorAll('[data-edit-key]').forEach(function (block) {
        const pen = document.createElement('button');
        pen.type = 'button';
        pen.className = 'edit-pen';
        pen.title = 'Edit this text';
        pen.textContent = '✎';
        block.classList.add('is-editable');
        block.appendChild(pen);

        pen.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (block.classList.contains('is-editing')) return;
            block.classList.add('is-editing');

            const original = block.innerHTML;
            const multiline = block.dataset.editMultiline === '1';
            const field = document.createElement(multiline ? 'textarea' : 'input');
            field.className = 'edit-field';
            field.value = block.dataset.editRaw || '';
            if (multiline) field.rows = Math.max(4, field.value.split('\n').length + 2);

            const bar = document.createElement('div');
            bar.className = 'edit-bar';
            bar.innerHTML = '<button type="button" class="edit-save">Save</button>' +
                            '<button type="button" class="edit-cancel">Cancel</button>' +
                            '<span class="edit-hint"></span>';
            bar.querySelector('.edit-hint').textContent = block.dataset.editHint || '';

            block.innerHTML = '';
            block.appendChild(field);
            block.appendChild(bar);
            field.focus();

            const cancel = function () {
                block.innerHTML = original;
                block.classList.remove('is-editing');
                // the restored ✎ is a fresh copy without its click handler, so swap in the live one
                const stale = block.querySelector('.edit-pen');
                if (stale) stale.replaceWith(pen);
            };

            const save = function () {
                const saveBtn = bar.querySelector('.edit-save');
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving…';
                const body = new FormData();
                body.append('key', block.dataset.editKey);
                body.append('value', field.value);
                body.append('csrf', csrf);
                fetch('save-text.php', { method: 'POST', body: body })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.ok) {
                            location.reload();
                        } else {
                            alert(res.error || 'Could not save.');
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Save';
                        }
                    })
                    .catch(function () {
                        alert('Could not save: check your connection and try again.');
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save';
                    });
            };

            bar.querySelector('.edit-save').addEventListener('click', save);
            bar.querySelector('.edit-cancel').addEventListener('click', cancel);
            field.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape') cancel();
                // Ctrl/Cmd+Enter saves; plain Enter in a one-line field saves too
                if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey || !multiline)) { ev.preventDefault(); save(); }
            });
        });
    });
})();
</script>
<?php } ?>

</body>
</html>
