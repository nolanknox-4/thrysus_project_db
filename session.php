<?php
// session.php — the one place sessions start, so every page gets the same hardened cookie.
// include this instead of calling session_start() directly.

if (session_status() === PHP_SESSION_NONE) {
    // HTTPS? (directly, or behind a proxy/host that terminates TLS and says so)
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,          // until the browser closes
        'path'     => '/',
        'secure'   => $https,     // only sent over HTTPS once the site has it (plain http still works locally)
        'httponly' => true,       // page scripts can't read the login cookie
        'samesite' => 'Lax',      // other sites can't send it along with forged form posts
    ]);
    ini_set('session.use_strict_mode', '1'); // refuse session IDs the server didn't issue
    session_start();
}

// CSRF token: a random value only our own forms know. Every admin POST must send it back,
// so a form on some other website can't make a logged-in admin save or delete things.
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function csrf_field()
{
    echo "<input type='hidden' name='csrf' value='" . $_SESSION['csrf'] . "'>";
}

function csrf_ok()
{
    return hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '');
}
