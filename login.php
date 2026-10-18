<?php
require_once __DIR__ . '/session.php';

// Already logged in? Skip the form, go straight to admin.
if (!empty($_SESSION['authed'])) {
    header('Location: admin.php');
    exit;
}

// bcrypt hash of the admin password, read from the ADMIN_PASSWORD_HASH environment variable
// (set in .env — see .env.example). Make one with:
//   docker compose exec web php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
// With no variable set it falls back to the hash below. That's fine on your own machine, but this
// file is in git, so the live site should get its own hash through .env instead.
$admin_hash = getenv('ADMIN_PASSWORD_HASH') ?: '$2y$10$IF9ki6RONxbfZ1NUxmRHF.fWQ/jf3r4CKjSiifxh5ARBZHldOUWQy';

// brute-force brake: 5 wrong passwords from one IP locks that IP out for 15 minutes.
// kept in a small file outside the web folder, since an attacker can just throw away cookies.
$attempts_file = sys_get_temp_dir() . '/thrysus_login_attempts.json';
$attempts = json_decode(@file_get_contents($attempts_file) ?: '{}', true) ?: [];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$window = 15 * 60;
$attempts = array_filter($attempts, function ($a) use ($window) { return $a['last'] > time() - $window; });
$locked = ($attempts[$ip]['count'] ?? 0) >= 5;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $error = 'Too many attempts. Try again in 15 minutes.';
    } elseif (!csrf_ok()) {
        $error = 'The form expired. Please try again.';
    // password_verify() hashes the attempt the same way and compares — never store/compare plaintext.
    } elseif (password_verify($_POST['password'] ?? '', $admin_hash)) {
        unset($attempts[$ip]);
        file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
        session_regenerate_id(true);              // fresh session ID at login, so a planted one is useless
        $_SESSION['authed'] = true;               // the flag every protected page checks
        header('Location: admin.php');
        exit;
    } else {
        $attempts[$ip] = ['count' => ($attempts[$ip]['count'] ?? 0) + 1, 'last' => time()];
        file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
        $error = 'Incorrect password.';
    }
}

$active = '';
include 'header.php';
?>

<div class="admin-shell">
    <h2>SOHS Archive · Admin Access</h2>
    <?php if ($error !== '') { echo "<p class='admin-msg'>" . $error . "</p>"; } ?>
    <form class="admin-form" method="POST" action="login.php">
        <?php csrf_field(); ?>
        <label>Password</label>
        <input type="password" name="password" autofocus>
        <button type="submit">Enter</button>
    </form>
</div>

<?php include 'footer.php'; ?>
