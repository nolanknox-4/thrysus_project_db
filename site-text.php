<?php
// site-text.php — editable page copy.
// Text that isn't an entry (Dionysus page sections, homepage hero, planetary data lists) lives in
// the site_text table, keyed by a slug like "world.overview". Each spot on a page passes in its
// built-in default, which shows until the first edit. When logged in, footer.php's script puts a
// ✎ button on every [data-edit-key] block; saving posts to save-text.php and reloads the page.
//
// Era and biome descriptions use the same editor but save straight into their own tables:
// keys "era:<id>" and "biome:<id>".

function site_text($db_connect, $slug, $default = '')
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = $db_connect->execute_query("SELECT slug, body FROM site_text")->fetch_all(MYSQLI_ASSOC);
            foreach ($rows as $r) {
                $cache[$r['slug']] = $r['body'];
            }
        } catch (mysqli_sql_exception $e) {
            // table not created yet (older database): everything just shows its default
        }
    }
    return array_key_exists($slug, $cache) ? $cache[$slug] : $default;
}

function is_editor()
{
    return !empty($_SESSION['authed']);
}

// plain text -> HTML: blank lines split paragraphs, single line breaks stay as <br>
function text_to_html($text, $p_class = 'body-text')
{
    $html = '';
    foreach (preg_split('/\n\s*\n/', trim(str_replace("\r", '', $text))) as $para) {
        if (trim($para) === '') continue;
        $html .= "<p class='" . $p_class . "'>" . nl2br(htmlspecialchars(trim($para))) . "</p>";
    }
    return $html;
}

// the attributes that make a block editable (only output when logged in)
function edit_attrs($key, $raw, $hint = '', $multiline = true)
{
    if (!is_editor()) return '';
    return " data-edit-key='" . htmlspecialchars($key, ENT_QUOTES) . "'"
         . " data-edit-raw='" . htmlspecialchars($raw ?? '', ENT_QUOTES) . "'"
         . " data-edit-hint='" . htmlspecialchars($hint, ENT_QUOTES) . "'"
         . ($multiline ? " data-edit-multiline='1'" : "");
}

// a block of paragraphs. $placeholder is what you (logged in) see while it's still empty
function editable_text($db_connect, $key, $default = '', $p_class = 'body-text', $placeholder = '')
{
    $text = site_text($db_connect, $key, $default);
    echo "<div class='editable'" . edit_attrs($key, $text, 'Blank line = new paragraph') . ">";
    if (trim($text) !== '') {
        echo text_to_html($text, $p_class);
    } elseif (is_editor()) {
        echo "<p class='body-text placeholder'>To be written" . ($placeholder !== '' ? ': ' . htmlspecialchars($placeholder) : '') . "</p>";
    }
    echo "</div>";
}

// a single line inside a given tag, e.g. the homepage title
function editable_line($db_connect, $key, $default, $tag, $class)
{
    $text = site_text($db_connect, $key, $default);
    echo "<" . $tag . " class='" . $class . " editable'" . edit_attrs($key, $text, '', false) . ">" . htmlspecialchars($text) . "</" . $tag . ">";
}

// a "Key: value" list (one pair per line), e.g. planetary data. returns [[key, value], ...]
function text_rows($db_connect, $key, $default_lines)
{
    $rows = [];
    foreach (explode("\n", str_replace("\r", '', site_text($db_connect, $key, $default_lines))) as $line) {
        if (trim($line) === '') continue;
        $parts = explode(':', $line, 2);
        $rows[] = [trim($parts[0]), trim($parts[1] ?? '')];
    }
    return $rows;
}
