<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$f = basename($_GET['f'] ?? '');
$path = __DIR__ . '/../uploads/reg/' . $f;
if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|pdf)$/', $f) || !is_file($path)) { http_response_code(404); exit('Tidak ditemukan'); }
$types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
header('Content-Type: ' . $types[pathinfo($f, PATHINFO_EXTENSION)]);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . $f . '"');
readfile($path);
