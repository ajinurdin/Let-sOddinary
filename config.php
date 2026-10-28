<?php
// Konfigurasi database (default XAMPP). Ubah jika perlu.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'letris_oddinary';
const DB_USER = 'root';
const DB_PASS = '';

// URL folder situs (otomatis), mis. "/LetsOddinary". Kosong jika di root domain.
(function () {
    $root = str_replace('\\', '/', (string) realpath(__DIR__));
    $doc  = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    define('BASE_URL', ($doc !== '' && str_starts_with($root, $doc)) ? substr($root, strlen($doc)) : '');
})();

const UPLOAD_MAX_BYTES = 3 * 1024 * 1024; // 3 MB
const TIMEZONE = 'Asia/Jakarta';
date_default_timezone_set(TIMEZONE);
