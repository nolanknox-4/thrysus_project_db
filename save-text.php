<?php
// save-text.php — receives an in-place edit (see site-text.php) and stores it. Logged-in only, CSRF-checked.

require_once __DIR__ . '/session.php';
include 'db_config.php';
header('Content-Type: application/json');

function fail($code, $msg)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'POST only');
if (empty($_SESSION['authed'])) fail(403, 'Not logged in');
if (!csrf_ok()) fail(400, 'This page is out of date. Reload it and try again.');

$key = $_POST['key'] ?? '';
$value = str_replace("\r", '', $_POST['value'] ?? '');
if (mb_strlen($value) > 20000) fail(400, 'That text is too long.');

if (preg_match('/^era:(\d+)$/', $key, $m)) {
    $db_connect->execute_query("UPDATE geological_eras SET era_description = ? WHERE id = ?", [$value, (int) $m[1]]);
} elseif (preg_match('/^biome:(\d+)$/', $key, $m)) {
    $db_connect->execute_query("UPDATE biomes SET description = ? WHERE id = ?", [$value, (int) $m[1]]);
} elseif (preg_match('/^[a-z0-9_.-]{1,100}$/', $key)) {
    $db_connect->execute_query(
        "INSERT INTO site_text (slug, body) VALUES (?, ?) ON DUPLICATE KEY UPDATE body = VALUES(body)",
        [$key, $value]
    );
} else {
    fail(400, 'Unknown field');
}

echo json_encode(['ok' => true]);
