<?php

require_once __DIR__ . "/../bootstrap.php";

$uid = $_GET['uid'] ?? '';
if (!is_string($uid) || !preg_match('/\A[a-f0-9]{64}\z/', $uid)) {
    http_response_code(400);
    exit;
}

$directory = getenv('COVER_UPLOAD_DIR') ?: '/var/lib/el-lugar/portadas';
foreach (['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'] as $extension => $mime) {
    $path = $directory . '/' . $uid . '.' . $extension;
    if (!is_file($path)) {
        continue;
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

$db = new LibreriaDB();
$cover = $db->fetch(
    "SELECT mime_type, imagen FROM libros_portadas WHERE uid = ?",
    [$uid]
);

if (!$cover) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $cover['mime_type']);
header('Content-Length: ' . strlen($cover['imagen']));
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo $cover['imagen'];
