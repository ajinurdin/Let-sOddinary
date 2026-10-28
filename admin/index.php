<?php
require_once __DIR__ . '/_layout.php';
admin_header('Dashboard', 'index');

$pdo = db();
$totals = $pdo->query("SELECT COUNT(*) total, SUM(status='pending') pending, SUM(status='verified') verified, SUM(status='rejected') rejected FROM registrations")->fetch();
$per = $pdo->query("SELECT e.id, e.title, e.color, e.quota_num, e.reg_open,
    SUM(r.status<>'rejected') active, SUM(r.status='pending') pending
    FROM events e LEFT JOIN registrations r ON r.event_id = e.id GROUP BY e.id ORDER BY e.sort_order")->fetchAll();
$recent = $pdo->query("SELECT r.*, e.title FROM registrations r JOIN events e ON e.id=r.event_id ORDER BY r.id DESC LIMIT 8")->fetchAll();
$labels = ['coming_soon' => 'Coming Soon', 'open' => 'Pendaftaran Dibuka', 'closed' => 'Pendaftaran Ditutup'];
?>
<div class="cards">
  <div class="stat"><b><?= (int) $totals['total'] ?></b>Total Pendaftar</div>
  <div class="stat warn"><b><?= (int) $totals['pending'] ?></b>Menunggu Verifikasi</div>
  <div class="stat ok"><b><?= (int) $totals['verified'] ?></b>Terverifikasi</div>
  <div class="stat bad"><b><?= (int) $totals['rejected'] ?></b>Ditolak</div>
</div>

<div class="panel">
  <h2>Status Situs: <?= e($labels[setting('status')] ?? setting('status')) ?></h2>
  <form method="post" action="settings.php" class="inline">
    <?= csrf_field() ?><input type="hidden" name="action" value="status">
    <select name="status">
<?php foreach ($labels as $k => $l): ?><option value="<?= $k ?>" <?= setting('status') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
    <button class="btn">Ubah Status</button>
  </form>
  <p class="muted">Pendaftaran hanya bisa dilakukan jika status "Pendaftaran Dibuka" dan arena bersangkutan dibuka.</p>
</div>

<div class="panel"><h2>Pendaftar per Arena</h2>
<table><tr><th>Arena</th><th>Aktif / Kuota</th><th>Menunggu</th><th>Form</th></tr>
<?php foreach ($per as $p): ?>
<tr><td><span class="dot" style="background:<?= e($p['color']) ?>"></span> <?= e($p['title']) ?></td>
<td><?= (int) $p['active'] ?> / <?= $p['quota_num'] ?? '&infin;' ?></td><td><?= (int) $p['pending'] ?></td>
<td><?= $p['reg_open'] ? '<span class="tag ok">Dibuka</span>' : '<span class="tag bad">Ditutup</span>' ?></td></tr>
<?php endforeach; ?></table></div>

<div class="panel"><h2>Pendaftar Terbaru</h2>
<table><tr><th>Kode</th><th>Arena</th><th>Nama</th><th>Sekolah</th><th>Status</th><th>Waktu</th></tr>
<?php foreach ($recent as $r): ?>
<tr><td><a href="registrations.php?view=<?= $r['id'] ?>"><?= e($r['code']) ?></a></td><td><?= e($r['title']) ?></td><td><?= e($r['team_name']) ?></td><td><?= e($r['school']) ?></td>
<td><span class="tag <?= e($r['status']) ?>"><?= e(status_labels()[$r['status']]) ?></span></td><td><?= e($r['created_at']) ?></td></tr>
<?php endforeach; if (!$recent) echo '<tr><td colspan="6" class="muted">Belum ada pendaftar.</td></tr>'; ?></table></div>
<?php admin_footer();
