<?php
require __DIR__ . '/includes/require-login.php';

$file = basename($_GET['file'] ?? ''); // basename() strips any ../ path traversal attempt
$path = __DIR__ . '/../includes/uploads/admissions/' . $file;

if ($file === '' || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $file . '"');
readfile($path);
exit;
