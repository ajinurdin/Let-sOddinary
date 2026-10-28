<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$ev = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $st->execute([$id]);
    $ev = $st->fetch();
    if (!$ev) redirect('admin/events.php');
}

function color_ok(string $c): string { return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? $c : '#34D8A6'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post_only();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $_POST['slug'] ?? ''), '-'));
        $title = trim($_POST['title'] ?? '');
        if ($slug === '' || $title === '') { flash('Slug dan judul wajib diisi.', 'err'); redirect('admin/event_edit.php' . ($id ? "?id=$id" : '')); }
        $tile = $_POST['poster_tile'] ?? '';
        if (!in_array($tile, ['', 'tile1', 'tile2', 'tile3', 'tile4', 'tile5'], true)) $tile = '';
        $cat = in_array($_POST['category'] ?? '', ['lomba', 'festival', 'penutup'], true) ? $_POST['category'] : 'lomba';
        $quota = trim($_POST['quota_num'] ?? '');

        // upload juknis (PDF)
        $juknis = trim($_POST['juknis_url'] ?? '');
        if (!empty($_FILES['juknis_file']['name']) && $_FILES['juknis_file']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['juknis_file'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            if ($mime === 'application/pdf' && $f['size'] <= 10 * 1048576) {
                $name = $slug . '-' . bin2hex(random_bytes(4)) . '.pdf';
                move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/juknis/' . $name);
                $juknis = url('uploads/juknis/' . $name);
            } else flash('Juknis harus PDF maks. 10 MB (field lain tetap disimpan).', 'err');
        }
        if ($juknis !== '' && !preg_match('#^(https?://|/)#i', $juknis)) $juknis = '';

        $vals = [$slug, $title, $cat, trim($_POST['badge'] ?? ''), color_ok($_POST['color'] ?? ''), color_ok($_POST['text_color'] ?? ''),
            trim($_POST['description'] ?? ''), trim($_POST['fee'] ?? ''), trim($_POST['prize'] ?? ''),
            $quota === '' ? null : max(0, (int) $quota), trim($_POST['quota_text'] ?? ''), trim($_POST['schedule_text'] ?? ''),
            ($_POST['event_date'] ?? '') ?: null, trim($_POST['contact'] ?? ''), trim($_POST['requirements'] ?? ''),
            $juknis, trim($_POST['team_label'] ?? '') ?: 'Nama Tim', $tile, isset($_POST['reg_open']) ? 1 : 0, isset($_POST['is_active']) ? 1 : 0];
        try {
            if ($tile !== '') $pdo->prepare('UPDATE events SET poster_tile = "" WHERE poster_tile = ? AND id <> ?')->execute([$tile, $id]);
            if ($id) {
                $pdo->prepare('UPDATE events SET slug=?,title=?,category=?,badge=?,color=?,text_color=?,description=?,fee=?,prize=?,quota_num=?,quota_text=?,schedule_text=?,event_date=?,contact=?,requirements=?,juknis_url=?,team_label=?,poster_tile=?,reg_open=?,is_active=? WHERE id=?')
                    ->execute([...$vals, $id]);
            } else {
                $pdo->prepare('INSERT INTO events (slug,title,category,badge,color,text_color,description,fee,prize,quota_num,quota_text,schedule_text,event_date,contact,requirements,juknis_url,team_label,poster_tile,reg_open,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute([...$vals, (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM events')->fetchColumn()]);
                $id = (int) $pdo->lastInsertId();
            }
            if (!flash_pending()) flash('Arena disimpan.');
        } catch (PDOException $ex) {
            flash($ex->getCode() === '23000' ? 'Slug sudah dipakai arena lain.' : 'Gagal menyimpan.', 'err');
        }
        redirect('admin/event_edit.php' . ($id ? "?id=$id" : ''));
    }

    if (!$id) redirect('admin/events.php');

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
        flash('Arena (beserta pendaftar & form-nya) dihapus.');
        redirect('admin/events.php');
    }

    if ($action === 'field_save') {
        $fid = (int) ($_POST['fid'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $key = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $_POST['field_key'] ?? $label), '_'));
        $key = substr($key, 0, 40);
        $type = in_array($_POST['type'] ?? '', ['text', 'textarea', 'number', 'email', 'tel', 'select', 'file'], true) ? $_POST['type'] : 'text';
        if ($label === '') { flash('Label field wajib diisi.', 'err'); redirect("admin/event_edit.php?id=$id#fields"); }
        if ($key === '') $key = 'f' . substr(md5($label), 0, 6);
        $opts = $type === 'select' ? trim($_POST['options'] ?? '') : null;
        $req = isset($_POST['required']) ? 1 : 0;
        $ord = (int) ($_POST['sort_order'] ?? 0);
        try {
            if ($fid) {
                $pdo->prepare('UPDATE form_fields SET label=?, type=?, options=?, required=?, sort_order=? WHERE id=? AND event_id=?')
                    ->execute([$label, $type, $opts, $req, $ord, $fid, $id]);
            } else {
                $pdo->prepare('INSERT INTO form_fields (event_id,field_key,label,type,options,required,sort_order) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$id, $key, $label, $type, $opts, $req, $ord ?: 99]);
            }
            flash('Field form disimpan.');
        } catch (PDOException $ex) { flash('Key field sudah dipakai di arena ini.', 'err'); }
        redirect("admin/event_edit.php?id=$id#fields");
    }

    if ($action === 'field_delete') {
        $pdo->prepare('DELETE FROM form_fields WHERE id=? AND event_id=?')->execute([(int) $_POST['fid'], $id]);
        flash('Field dihapus. Data lama pendaftar tetap tersimpan.');
        redirect("admin/event_edit.php?id=$id#fields");
    }
}

function flash_pending(): bool { return isset($_SESSION['flash']); }

$d = $ev ?: ['slug' => '', 'title' => '', 'category' => 'lomba', 'badge' => '', 'color' => '#34D8A6', 'text_color' => '#1a1438', 'description' => '',
    'fee' => '', 'prize' => '', 'quota_num' => null, 'quota_text' => '', 'schedule_text' => '', 'event_date' => '', 'contact' => '',
    'requirements' => '', 'juknis_url' => '', 'team_label' => 'Nama Tim', 'poster_tile' => '', 'reg_open' => 1, 'is_active' => 1];
$fields = [];
if ($id) {
    $q = $pdo->prepare('SELECT * FROM form_fields WHERE event_id = ? ORDER BY sort_order, id');
    $q->execute([$id]);
    $fields = $q->fetchAll();
}
$tiles = ['' => '(tanpa petak di poster)', 'tile1' => 'Petak 1 - kiri (hijau mint)', 'tile2' => 'Petak 2 - pink', 'tile3' => 'Petak 3 - tengah (gambar ungu)', 'tile4' => 'Petak 4 - kanan (kuning)', 'tile5' => 'Petak 5 - atas (biru)'];

admin_header($id ? 'Edit Arena: ' . $d['title'] : 'Tambah Arena', 'events');
?>
<p><a href="events.php">&larr; Semua arena</a></p>
<form method="post" enctype="multipart/form-data" class="panel grid2">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="save">
  <label>Judul<input name="title" required value="<?= e($d['title']) ?>"></label>
  <label>Slug (URL pendaftaran)<input name="slug" required value="<?= e($d['slug']) ?>" pattern="[a-zA-Z0-9\-]+"></label>
  <label>Kategori<select name="category"><?php foreach (['lomba' => 'Lomba', 'festival' => 'Festival', 'penutup' => 'Pentas Seni / Penutup'] as $k => $l): ?><option value="<?= $k ?>" <?= $d['category'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
  <label>Badge<input name="badge" value="<?= e($d['badge']) ?>"></label>
  <label>Petak di poster<select name="poster_tile"><?php foreach ($tiles as $k => $l): ?><option value="<?= $k ?>" <?= $d['poster_tile'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
  <label>Label nama peserta di form<input name="team_label" value="<?= e($d['team_label']) ?>"></label>
  <label>Warna kartu<input type="color" name="color" value="<?= e($d['color']) ?>"></label>
  <label>Warna teks kartu<input type="color" name="text_color" value="<?= e($d['text_color']) ?>"></label>
  <label class="full">Deskripsi<textarea name="description" rows="3"><?= e($d['description']) ?></textarea></label>
  <label>Biaya<input name="fee" value="<?= e($d['fee']) ?>"></label>
  <label>Hadiah / benefit<input name="prize" value="<?= e($d['prize']) ?>"></label>
  <label>Kuota (angka, kosong = tanpa batas)<input type="number" min="0" name="quota_num" value="<?= e($d['quota_num']) ?>"></label>
  <label>Teks kuota (tampil di kartu)<input name="quota_text" value="<?= e($d['quota_text']) ?>"></label>
  <label>Tanggal acara<input type="date" name="event_date" value="<?= e($d['event_date']) ?>"></label>
  <label>Keterangan jadwal<input name="schedule_text" value="<?= e($d['schedule_text']) ?>"></label>
  <label class="full">Contact person<input name="contact" value="<?= e($d['contact']) ?>"></label>
  <label class="full">Ketentuan (satu per baris)<textarea name="requirements" rows="4"><?= e($d['requirements']) ?></textarea></label>
  <label>Juknis - URL<input name="juknis_url" value="<?= e($d['juknis_url']) ?>" placeholder="https://... atau kosong"></label>
  <label>atau unggah Juknis (PDF, maks 10 MB)<input type="file" name="juknis_file" accept="application/pdf"></label>
  <label class="chk"><input type="checkbox" name="reg_open" <?= $d['reg_open'] ? 'checked' : '' ?>> Pendaftaran arena ini dibuka</label>
  <label class="chk"><input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?>> Tampilkan di situs</label>
  <div class="full"><button class="btn">Simpan Arena</button></div>
</form>

<?php if ($id): ?>
<div class="panel" id="fields">
  <h2>Form Pendaftaran</h2>
  <p class="muted">Field standar (<?= e($d['team_label']) ?>, sekolah, penanggung jawab, WhatsApp, email) selalu ada. Tambahkan field khusus arena ini di bawah.</p>
  <?php $types = ['text', 'textarea', 'number', 'email', 'tel', 'select', 'file']; ?>
  <div class="fieldlist">
<?php foreach ($fields as $f): ?>
    <form method="post" class="fieldrow"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="fid" value="<?= $f['id'] ?>">
      <label>Urut<input class="w60" type="number" name="sort_order" value="<?= $f['sort_order'] ?>"></label>
      <label>Label<input name="label" value="<?= e($f['label']) ?>" required></label>
      <label>Tipe<select name="type"><?php foreach ($types as $t): ?><option <?= $f['type'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
      <label>Opsi (tipe select, satu per baris)<textarea name="options" rows="2"><?= e($f['options']) ?></textarea></label>
      <label class="chk"><input type="checkbox" name="required" <?= $f['required'] ? 'checked' : '' ?>> Wajib</label>
      <div class="nowrap"><code><?= e($f['field_key']) ?></code><br>
        <button class="btn small" name="action" value="field_save">Simpan</button>
        <button class="btn small danger" name="action" value="field_delete" onclick="return confirm('Hapus field ini?')">Hapus</button></div>
    </form>
<?php endforeach; ?>
    <form method="post" class="fieldrow newrow"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="field_save">
      <label>Urut<input class="w60" type="number" name="sort_order" value="<?= count($fields) + 1 ?>"></label>
      <label>Label baru<input name="label" placeholder="mis. Nama Pelatih" required></label>
      <label>Tipe<select name="type"><?php foreach ($types as $t): ?><option><?= $t ?></option><?php endforeach; ?></select></label>
      <label>Opsi (tipe select)<textarea name="options" rows="2"></textarea></label>
      <label class="chk"><input type="checkbox" name="required" checked> Wajib</label>
      <div><button class="btn small">+ Tambah Field</button></div>
    </form>
  </div>
</div>

<form method="post" class="panel" onsubmit="return confirm('Hapus arena ini beserta SEMUA pendaftarnya? Tidak bisa dibatalkan.')">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="delete">
  <button class="btn danger">Hapus Arena</button>
</form>
<?php endif;
admin_footer();
