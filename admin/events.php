<?php
require_once __DIR__ . '/_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle_reg') {
        db()->prepare('UPDATE events SET reg_open = 1 - reg_open WHERE id = ?')->execute([$id]);
    } elseif (($_POST['action'] ?? '') === 'toggle_active') {
        db()->prepare('UPDATE events SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    } elseif (($_POST['action'] ?? '') === 'move') {
        $dir = $_POST['dir'] === 'up' ? -1 : 1;
        $rows = db()->query('SELECT id FROM events ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN);
        $i = array_search($id, array_map('intval', $rows), true);
        if ($i !== false && isset($rows[$i + $dir])) {
            [$rows[$i], $rows[$i + $dir]] = [$rows[$i + $dir], $rows[$i]];
            $up = db()->prepare('UPDATE events SET sort_order = ? WHERE id = ?');
            foreach ($rows as $n => $rid) $up->execute([$n + 1, $rid]);
        }
    }
    redirect('admin/events.php');
}

admin_header('Arena & Form Pendaftaran', 'events');
$rows = db()->query("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) regs,
  (SELECT COUNT(*) FROM form_fields f WHERE f.event_id=e.id) nfields FROM events e ORDER BY sort_order, id")->fetchAll();
?>
<p><a class="btn" href="event_edit.php">+ Tambah Arena</a></p>
<div class="panel"><table>
<tr><th>Urut</th><th>Arena</th><th>Kategori</th><th>Tanggal</th><th>Pendaftar</th><th>Field Form</th><th>Pendaftaran</th><th>Tampil</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td>
    <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="move">
    <button name="dir" value="up" class="mini" title="Naik">&uarr;</button><button name="dir" value="down" class="mini" title="Turun">&darr;</button></form>
  </td>
  <td><span class="dot" style="background:<?= e($r['color']) ?>"></span> <b><?= e($r['title']) ?></b><br><small class="muted">/<?= e($r['slug']) ?> &middot; petak poster: <?= e($r['poster_tile'] ?: '-') ?></small></td>
  <td><?= e($r['category']) ?></td>
  <td><?= e(fmt_date($r['event_date'])) ?></td>
  <td><a href="registrations.php?event=<?= $r['id'] ?>"><?= (int) $r['regs'] ?></a> / <?= $r['quota_num'] ?? '&infin;' ?></td>
  <td><?= (int) $r['nfields'] ?></td>
  <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="toggle_reg">
    <button class="tag <?= $r['reg_open'] ? 'ok' : 'bad' ?> btn-tag"><?= $r['reg_open'] ? 'Dibuka' : 'Ditutup' ?></button></form></td>
  <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="toggle_active">
    <button class="tag <?= $r['is_active'] ? 'ok' : 'bad' ?> btn-tag"><?= $r['is_active'] ? 'Ya' : 'Sembunyi' ?></button></form></td>
  <td><a class="btn small" href="event_edit.php?id=<?= $r['id'] ?>">Edit &amp; Form</a></td>
</tr>
<?php endforeach; ?>
</table></div>
<?php admin_footer();
