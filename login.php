<?php
session_start();

// Already logged in? Skip the form, go straight to admin.
if (!empty($_SESSION['authed'])) {
    header('Location: admin.php');
    exit;
}

// bcrypt hash of the admin password. TEMP password is 'thrysus' — CHANGE IT:
// generate a new hash for your own password with
//   docker compose exec web php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
// then paste the $2y$... string below.
$admin_hash = '$2y$10$IF9ki6RONxbfZ1NUxmRHF.fWQ/jf3r4CKjSiifxh5ARBZHldOUWQy';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // password_verify() hashes the attempt the same way and compares — never store/compare plaintext.
    if (password_verify($_POST['password'] ?? '', $admin_hash)) {
        $_SESSION['authed'] = true;               // the flag every protected page checks
        header('Location: admin.php');
        exit;
    } else {
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
        <label>Password</label>
        <input type="password" name="password" autofocus>
        <button type="submit">Enter</button>
    </form>
</div>

<?php include 'footer.php'; ?>
