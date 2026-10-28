<?php
require_once __DIR__ . '/_layout.php';
$me = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $a = $_POST['action'] ?? '';
    if ($a === 'status') {
        if (in_array($_POST['status'] ?? '', ['coming_soon', 'open', 'closed'], true)) {
            save_setting('status', $_POST['status']);
            flash('Status situs diperbarui.');
        }
        redirect('admin/index.php');
    }
    if ($a === 'save') {
        $keys = ['event_name', 'tagline', 'period_text', 'venue', 'target_date', 'marquee', 'contact_wa', 'instagram', 'footer_text', 'closed_msg', 'status'];
        foreach ($keys as $k) {
            if (!isset($_POST[$k])) continue;
            $v = trim($_POST[$k]);
            if ($k === 'status' && !in_array($v, ['coming_soon', 'open', 'closed'], true)) continue;
            if ($k === 'contact_wa') $v = preg_replace('/\D/', '', $v);
            if ($k === 'instagram' && $v !== '' && !preg_match('#^https?://#i', $v)) $v = 'https://instagram.com/' . ltrim($v, '@/');
            save_setting($k, $v);
        }
        flash('Pengaturan disimpan.');
    }
    if ($a === 'password') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id=?');
        $st->execute([$me['id']]);
        if (!password_verify($_POST['old'] ?? '', (string) $st->fetchColumn())) flash('Password lama salah.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 8) flash('Password baru minimal 8 karakter.', 'err');
        elseif ($_POST['new'] !== ($_POST['new2'] ?? '')) flash('Konfirmasi password tidak sama.', 'err');
        else {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $me['id']]);
            flash('Password diganti.');
        }
    }
    if ($a === 'user_add') {
        $u = trim($_POST['username'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $u) || strlen($_POST['password'] ?? '') < 8) flash('Username 3-50 karakter & password min. 8 karakter.', 'err');
        else {
            try {
                $pdo->prepare('INSERT INTO users (username,name,password_hash) VALUES (?,?,?)')->execute([$u, trim($_POST['name'] ?? '') ?: $u, password_hash($_POST['password'], PASSWORD_DEFAULT)]);
                flash('Admin ditambahkan.');
            } catch (PDOException $e) { flash('Username sudah ada.', 'err'); }
        }
    }
    if ($a === 'user_del') {
        $uid = (int) $_POST['uid'];
        if ($uid === $me['id']) flash('Tidak bisa menghapus akun sendiri.', 'err');
        else { $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$uid]); flash('Admin dihapus.'); }
    }
    redirect('admin/settings.php');
}

admin_header('Pengaturan Situs', 'settings');
$s = settings();
$g = fn($k) => e($s[$k] ?? '');
$users = $pdo->query('SELECT id, username, name, created_at FROM users ORDER BY id')->fetchAll();
?>
<form method="post" class="panel grid2">
  <?= csrf_field() ?><input type="hidden" name="action" value="save">
  <label>Nama acara<input name="event_name" value="<?= $g('event_name') ?>"></label>
  <label>Status situs<select name="status"><?php foreach (['coming_soon' => 'Coming Soon', 'open' => 'Pendaftaran Dibuka', 'closed' => 'Pendaftaran Ditutup'] as $k => $l): ?><option value="<?= $k ?>" <?= ($s['status'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
  <label class="full">Tagline<input name="tagline" value="<?= $g('tagline') ?>"></label>
  <label>Periode (teks)<input name="period_text" value="<?= $g('period_text') ?>"></label>
  <label>Lokasi / venue<input name="venue" value="<?= $g('venue') ?>"></label>
  <label>Target countdown<input type="datetime-local" name="target_date" value="<?= $g('target_date') ?>"></label>
  <label>WhatsApp panitia (628xxx)<input name="contact_wa" value="<?= $g('contact_wa') ?>"></label>
  <label>Instagram (URL / @username)<input name="instagram" value="<?= $g('instagram') ?>"></label>
  <label>Teks footer<input name="footer_text" value="<?= $g('footer_text') ?>"></label>
  <label class="full">Teks berjalan (marquee)<input name="marquee" value="<?= $g('marquee') ?>"></label>
  <label class="full">Pesan saat pendaftaran belum dibuka<input name="closed_msg" value="<?= $g('closed_msg') ?>"></label>
  <div class="full"><button class="btn">Simpan Pengaturan</button></div>
</form>

<div class="panel"><h2>Admin CMS</h2>
<table><tr><th>Username</th><th>Nama</th><th></th></tr>
<?php foreach ($users as $u): ?>
<tr><td><?= e($u['username']) ?></td><td><?= e($u['name']) ?></td><td>
<?php if ($u['id'] != $me['id']): ?><form method="post" class="inline" onsubmit="return confirm('Hapus admin ini?')"><?= csrf_field() ?><input type="hidden" name="action" value="user_del"><input type="hidden" name="uid" value="<?= $u['id'] ?>"><button class="btn small danger">Hapus</button></form><?php else: ?><span class="muted">akun Anda</span><?php endif; ?></td></tr>
<?php endforeach; ?></table>
<form method="post" class="grid2" style="margin-top:14px"><?= csrf_field() ?><input type="hidden" name="action" value="user_add">
  <label>Username<input name="username" required></label><label>Nama<input name="name"></label>
  <label>Password (min. 8)<input type="password" name="password" required minlength="8"></label>
  <div><button class="btn small">+ Tambah Admin</button></div></form>
</div>

<form method="post" class="panel grid2"><h2 class="full">Ganti Password</h2><?= csrf_field() ?><input type="hidden" name="action" value="password">
  <label>Password lama<input type="password" name="old" required></label><span></span>
  <label>Password baru<input type="password" name="new" required minlength="8"></label>
  <label>Ulangi password baru<input type="password" name="new2" required></label>
  <div class="full"><button class="btn">Ganti Password</button></div></form>
<?php admin_footer();
