<?php
require_once __DIR__ . '/../includes/bootstrap.php';

function admin_header(string $title, string $active = ''): void
{
    $u = require_admin();
    $nav = [
        'index' => ['index.php', 'Dashboard'],
        'registrations' => ['registrations.php', 'Pendaftar'],
        'events' => ['events.php', 'Arena & Form'],
        'schedule' => ['schedule.php', 'Rundown'],
        'settings' => ['settings.php', 'Pengaturan'],
    ];
    $f = flash();
    ?><!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - CMS Letris Oddinary</title>
<link rel="icon" type="image/png" href="<?= url('assets/img/Logo-Icon.png') ?>">
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>"></head>
<body>
<aside class="side">
  <div class="logo">LETRIS<br>ODDINARY<small>CMS</small></div>
  <nav>
<?php foreach ($nav as $k => [$href, $label]): ?>
    <a href="<?= url('admin/' . $href) ?>" class="<?= $k === $active ? 'on' : '' ?>"><?= e($label) ?></a>
<?php endforeach; ?>
    <a href="<?= url('index.php') ?>" target="_blank">Lihat Situs &nearr;</a>
  </nav>
  <div class="me">Masuk: <b><?= e($u['name']) ?></b><br><a href="<?= url('admin/logout.php') ?>">Keluar</a></div>
</aside>
<main>
  <h1><?= e($title) ?></h1>
<?php if ($f): ?><div class="flash <?= e($f['t']) ?>"><?= e($f['m']) ?></div><?php endif; ?>
<?php
}

function admin_footer(): void
{
    echo "\n</main></body></html>";
}

function post_only(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
    csrf_check();
}
