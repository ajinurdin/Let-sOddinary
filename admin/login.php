<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (admin_user()) redirect('admin/index.php');

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // pembatas percobaan login sederhana (per sesi)
    $_SESSION['tries'] = $_SESSION['tries'] ?? 0;
    if (($_SESSION['lock_until'] ?? 0) > time()) {
        $err = 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([trim($_POST['username'] ?? '')]);
        $u = $st->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => (int) $u['id'], 'name' => $u['name'], 'username' => $u['username']];
            $_SESSION['tries'] = 0;
            redirect('admin/index.php');
        }
        if (++$_SESSION['tries'] >= 5) { $_SESSION['lock_until'] = time() + 300; $_SESSION['tries'] = 0; }
        $err = 'Username atau password salah.';
    }
}
?><!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login CMS - Letris Oddinary</title>
<link rel="icon" type="image/png" href="<?= url('assets/img/Logo-Icon.png') ?>">
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>"></head>
<body class="login-body">
<form class="login-box" method="post">
  <h1>LETRIS ODDINARY</h1><p>Masuk ke CMS</p>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required autofocus></label>
  <label>Password<input type="password" name="password" required></label>
  <button class="btn">Masuk</button>
</form>
</body></html>
