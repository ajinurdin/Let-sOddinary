<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $rid = (int) ($_POST['rid'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($act === 'status' && isset(status_labels()[$_POST['status'] ?? ''])) {
        $pdo->prepare('UPDATE registrations SET status=?, notes=COALESCE(?, notes) WHERE id=?')
            ->execute([$_POST['status'], isset($_POST['notes']) ? trim($_POST['notes']) : null, $rid]);
        flash('Status diperbarui.');
    } elseif ($act === 'delete') {
        $row = $pdo->prepare('SELECT data FROM registrations WHERE id=?');
        $row->execute([$rid]);
        foreach (json_decode((string) $row->fetchColumn(), true) ?: [] as $v) {
            if (is_array($v) && isset($v['file'])) @unlink(__DIR__ . '/../uploads/reg/' . basename($v['file']));
        }
        $pdo->prepare('DELETE FROM registrations WHERE id=?')->execute([$rid]);
        flash('Pendaftar dihapus.');
        redirect('admin/registrations.php');
    }
    redirect('admin/registrations.php?' . ($_POST['back'] ?? ''));
}

/* ---- detail ---- */
if (isset($_GET['view'])) {
    $st = $pdo->prepare('SELECT r.*, e.title, e.id eid FROM registrations r JOIN events e ON e.id=r.event_id WHERE r.id=?');
    $st->execute([(int) $_GET['view']]);
    $r = $st->fetch();
    if (!$r) redirect('admin/registrations.php');
    $labels = [];
    $lq = $pdo->prepare('SELECT field_key, label FROM form_fields WHERE event_id=?');
    $lq->execute([$r['eid']]);
    foreach ($lq as $x) $labels[$x['field_key']] = $x['label'];
    $data = json_decode((string) $r['data'], true) ?: [];
    admin_header('Pendaftar ' . $r['code'], 'registrations');
    ?>
<p><a href="registrations.php">&larr; Daftar pendaftar</a></p>
<div class="panel">
  <h2><?= e($r['team_name']) ?> <span class="tag <?= e($r['status']) ?>"><?= e(status_labels()[$r['status']]) ?></span></h2>
  <table class="kv">
    <tr><th>Kode</th><td><?= e($r['code']) ?></td></tr>
    <tr><th>Arena</th><td><?= e($r['title']) ?></td></tr>
    <tr><th>Sekolah / Instansi</th><td><?= e($r['school']) ?></td></tr>
    <tr><th>Penanggung jawab</th><td><?= e($r['contact_name']) ?></td></tr>
    <tr><th>WhatsApp</th><td><a href="https://wa.me/<?= e(preg_replace('/^0/', '62', preg_replace('/\D/', '', $r['phone']))) ?>" target="_blank" rel="noopener"><?= e($r['phone']) ?></a></td></tr>
    <tr><th>Email</th><td><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></td></tr>
    <tr><th>Mendaftar</th><td><?= e($r['created_at']) ?> (IP <?= e($r['ip']) ?>)</td></tr>
<?php foreach ($data as $k => $v): ?>
    <tr><th><?= e($labels[$k] ?? $k) ?></th><td>
<?php if (is_array($v)): ?><a href="file.php?f=<?= urlencode($v['file']) ?>" target="_blank"><?= e($v['name']) ?></a>
<?php else: echo nl2br(e($v)); endif; ?></td></tr>
<?php endforeach; ?>
  </table>
  <form method="post" class="grid2" style="margin-top:16px">
    <?= csrf_field() ?><input type="hidden" name="rid" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="back" value="view=<?= $r['id'] ?>">
    <label>Status<select name="status"><?php foreach (status_labels() as $k => $l): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
    <label class="full">Catatan internal<textarea name="notes" rows="3"><?= e($r['notes']) ?></textarea></label>
    <div class="full"><button class="btn">Simpan</button></div>
  </form>
  <form method="post" onsubmit="return confirm('Hapus pendaftar ini?')" style="margin-top:10px">
    <?= csrf_field() ?><input type="hidden" name="rid" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn danger small">Hapus</button>
  </form>
</div>
<?php admin_footer(); exit; }

/* ---- daftar + filter ---- */
$event = (int) ($_GET['event'] ?? 0);
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$where = ['1=1']; $args = [];
if ($event) { $where[] = 'r.event_id=?'; $args[] = $event; }
if (isset(status_labels()[$status])) { $where[] = 'r.status=?'; $args[] = $status; }
if ($q !== '') { $where[] = '(r.team_name LIKE ? OR r.school LIKE ? OR r.contact_name LIKE ? OR r.code LIKE ? OR r.phone LIKE ?)'; array_push($args, ...array_fill(0, 5, "%$q%")); }
$st = $pdo->prepare('SELECT r.*, e.title FROM registrations r JOIN events e ON e.id=r.event_id WHERE ' . implode(' AND ', $where) . ' ORDER BY r.id DESC LIMIT 500');
$st->execute($args);
$rows = $st->fetchAll();
$events = $pdo->query('SELECT id,title FROM events ORDER BY sort_order')->fetchAll();
$qs = http_build_query(array_filter(['event' => $event, 'status' => $status, 'q' => $q]));

admin_header('Pendaftar', 'registrations');
?>
<form method="get" class="panel inline filters">
  <select name="event"><option value="">Semua arena</option><?php foreach ($events as $e): ?><option value="<?= $e['id'] ?>" <?= $event == $e['id'] ? 'selected' : '' ?>><?= e($e['title']) ?></option><?php endforeach; ?></select>
  <select name="status"><option value="">Semua status</option><?php foreach (status_labels() as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
  <input name="q" placeholder="Cari nama / sekolah / kode / HP" value="<?= e($q) ?>">
  <button class="btn">Filter</button>
  <a class="btn" href="export.php?<?= e($qs) ?>">Ekspor CSV</a>
</form>
<div class="panel"><table>
<tr><th>Kode</th><th>Arena</th><th>Nama</th><th>Sekolah</th><th>WhatsApp</th><th>Status</th><th>Waktu</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><a href="?view=<?= $r['id'] ?>"><?= e($r['code']) ?></a></td><td><?= e($r['title']) ?></td><td><?= e($r['team_name']) ?></td>
  <td><?= e($r['school']) ?></td><td><?= e($r['phone']) ?></td>
  <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="rid" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="back" value="<?= e($qs) ?>">
    <select name="status" onchange="this.form.submit()" class="st-<?= e($r['status']) ?>"><?php foreach (status_labels() as $k => $l): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></form></td>
  <td><?= e($r['created_at']) ?></td>
</tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="muted">Tidak ada data.</td></tr>'; ?>
</table></div>
<?php admin_footer();
