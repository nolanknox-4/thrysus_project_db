
<div class="footer">
    <div class="footer-id"> SOHS-VDSRP · THYRSIS PROJECT · DNY-001 · REV 23 · 2341.11.02</div>
    <?php if (!empty($_SESSION['authed'])) { ?>
        <a href="admin.php" class="footer-admin">Admin</a>
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

    function closeBox() {
        box.classList.remove('is-open');
        box.innerHTML = '';
        activeIndex = -1;
    }

    function esc(s) {
        const div = document.createElement('div');
        div.textContent = s == null ? '' : s;
        return div.innerHTML;
    }

    function renderResults(results) {
        if (results.length === 0) {
            box.innerHTML = '<div class="search-suggestion-empty">No matches</div>';
        } else {
            box.innerHTML = results.map(function (r) {
                return '<a class="search-suggestion" href="' + esc(r.link) + '">' +
                    '<span class="search-suggestion-label">' + esc(r.label) + '</span>' +
                    '<span class="search-suggestion-sub">' + esc(r.type) + '</span>' +
                    '</a>';
            }).join('');
        }
        box.classList.add('is-open');
        activeIndex = -1;
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) {
            closeBox();
            return;
        }
        timer = setTimeout(function () {
            fetch('search-suggest.php?q=' + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(renderResults)
                .catch(closeBox);
        }, 200);
    });

    input.addEventListener('keydown', function (e) {
        const items = box.querySelectorAll('.search-suggestion');
        if (e.key === 'Escape') {
            closeBox();
        } else if (e.key === 'ArrowDown' && items.length) {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % items.length;
            items.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIndex); });
        } else if (e.key === 'ArrowUp' && items.length) {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + items.length) % items.length;
            items.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIndex); });
        } else if (e.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
            e.preventDefault();
            window.location.href = items[activeIndex].getAttribute('href');
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target !== input && !box.contains(e.target)) {
            closeBox();
        }
    });
})();
</script>

</body>
</html>
