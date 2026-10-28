<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $a = $_POST['action'] ?? '';
    $sid = (int) ($_POST['sid'] ?? 0);
    if ($a === 'save') {
        $date = $_POST['day_date'] ?? '';
        $title = trim($_POST['title'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $title === '') {
            flash('Tanggal dan judul wajib diisi.', 'err');
        } else {
            $eid = (int) ($_POST['event_id'] ?? 0) ?: null;
            $v = [$date, trim($_POST['time_label'] ?? ''), $title, trim($_POST['description'] ?? ''), $eid];
            if ($sid) $pdo->prepare('UPDATE schedule SET day_date=?,time_label=?,title=?,description=?,event_id=? WHERE id=?')->execute([...$v, $sid]);
            else $pdo->prepare('INSERT INTO schedule (day_date,time_label,title,description,event_id) VALUES (?,?,?,?,?)')->execute($v);
            flash('Rundown disimpan.');
        }
    } elseif ($a === 'delete') {
        $pdo->prepare('DELETE FROM schedule WHERE id=?')->execute([$sid]);
        flash('Item dihapus.');
    }
    redirect('admin/schedule.php');
}

admin_header('Rundown Acara', 'schedule');
$rows = $pdo->query('SELECT * FROM schedule ORDER BY day_date, time_label, id')->fetchAll();
$events = $pdo->query('SELECT id,title FROM events ORDER BY sort_order')->fetchAll();
$opts = function ($sel) use ($events) {
    $h = '<option value="">- umum -</option>';
    foreach ($events as $e) $h .= '<option value="' . $e['id'] . '"' . ($sel == $e['id'] ? ' selected' : '') . '>' . e($e['title']) . '</option>';
    return $h;
};
?>
<div class="panel"><div class="fieldlist">
<?php foreach ($rows as $r): ?>
  <form method="post" class="fieldrow"><?= csrf_field() ?><input type="hidden" name="sid" value="<?= $r['id'] ?>">
    <label>Tanggal<input type="date" name="day_date" value="<?= e($r['day_date']) ?>" required></label>
    <label>Jam<input name="time_label" value="<?= e($r['time_label']) ?>"></label>
    <label>Judul<input name="title" value="<?= e($r['title']) ?>" required></label>
    <label>Keterangan<input name="description" value="<?= e($r['description']) ?>"></label>
    <label>Arena<select name="event_id"><?= $opts($r['event_id']) ?></select></label>
    <div class="nowrap"><button class="btn small" name="action" value="save">Simpan</button>
    <button class="btn small danger" name="action" value="delete" onclick="return confirm('Hapus item ini?')">Hapus</button></div>
  </form>
<?php endforeach; ?>
  <form method="post" class="fieldrow newrow"><?= csrf_field() ?><input type="hidden" name="action" value="save">
    <label>Tanggal<input type="date" name="day_date" min="2026-12-01" required></label>
    <label>Jam<input name="time_label" placeholder="08.00"></label>
    <label>Judul<input name="title" placeholder="Judul kegiatan" required></label>
    <label>Keterangan<input name="description"></label>
    <label>Arena<select name="event_id"><?= $opts(0) ?></select></label>
    <div><button class="btn small">+ Tambah</button></div>
  </form>
</div></div>
<?php admin_footer();
