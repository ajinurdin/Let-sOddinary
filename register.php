<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['e'] ?? $_POST['e'] ?? '';
$st = db()->prepare('SELECT * FROM events WHERE slug = ? AND is_active = 1');
$st->execute([$slug]);
$ev = $st->fetch();
if (!$ev) {
    redirect('index.php#arena-lengkap');
}

$fq = db()->prepare('SELECT * FROM form_fields WHERE event_id = ? ORDER BY sort_order, id');
$fq->execute([$ev['id']]);
$fields = $fq->fetchAll();

$isOpen = registration_open($ev);
$full = $ev['quota_num'] !== null && registration_count((int) $ev['id']) >= (int) $ev['quota_num'];
$errors = [];
$old = [];

const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];

function save_upload(array $f, string $label, array &$errors): ?array
{
    if ($f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = "Upload \"$label\" gagal."; return null; }
    if ($f['size'] > UPLOAD_MAX_BYTES) { $errors[] = "File \"$label\" maksimal " . (UPLOAD_MAX_BYTES / 1048576) . " MB."; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(ALLOWED_MIME[$mime])) { $errors[] = "File \"$label\" harus JPG, PNG, atau PDF."; return null; }
    $name = bin2hex(random_bytes(16)) . '.' . ALLOWED_MIME[$mime];
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/reg/' . $name)) {
        $errors[] = "File \"$label\" tidak dapat disimpan."; return null;
    }
    return ['file' => $name, 'name' => mb_substr(basename($f['name']), 0, 120)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!empty($_POST['website'])) { exit; } // honeypot
    if (!$isOpen) { $errors[] = 'Pendaftaran untuk arena ini sedang tidak dibuka.'; }
    if ($full) { $errors[] = 'Maaf, kuota sudah penuh.'; }

    $base = [
        'school' => 'Nama Sekolah' . ($ev['slug'] === 'mlbb' ? ' (SMP/MTs)' : ' / Instansi'),
        'contact_name' => $ev['contact_label'] ?? 'Nama Penanggung Jawab',
        'phone' => $ev['phone_label'] ?? 'No. WhatsApp',
    ];
    if (!empty($ev['team_label'])) {
        $base = array_merge(['team_name' => $ev['team_label']], $base);
    }
    if (!empty($ev['email_required'])) {
        $base['email'] = 'Email';
    } else {
        $old['email'] = trim((string) ($_POST['email'] ?? ''));
    }

    foreach ($base as $k => $lbl) {
        $old[$k] = trim((string) ($_POST[$k] ?? ''));
        if ($old[$k] === '') $errors[] = "$lbl wajib diisi.";
    }
    if (!empty($old['email']) && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!empty($old['phone']) && !preg_match('/^[0-9+\-\s()]{8,20}$/', $old['phone'])) $errors[] = 'Nomor WhatsApp tidak valid.';
    foreach (['team_name' => 120, 'school' => 160, 'contact_name' => 120, 'email' => 160] as $k => $max) {
        if (!empty($old[$k]) && mb_strlen($old[$k]) > $max) $errors[] = "Isian terlalu panjang ($k).";
    }

    $data = [];
    foreach ($fields as $f) {
        if ($f['type'] === 'heading') continue;
        $key = 'f_' . $f['field_key'];
        if ($f['type'] === 'file') {
            $res = isset($_FILES[$key]) ? save_upload($_FILES[$key], $f['label'], $errors) : null;
            if ($res) $data[$f['field_key']] = $res;
            elseif ($f['required'] && !array_filter($errors, fn($m) => str_contains($m, $f['label']))) $errors[] = $f['label'] . ' wajib diunggah.';
            continue;
        }
        $val = trim((string) ($_POST[$key] ?? ''));
        $old[$key] = $val;
        if ($val === '') {
            if ($f['required']) $errors[] = $f['label'] . ' wajib diisi.';
            continue;
        }
        if ($f['type'] === 'select' && !in_array($val, lines((string) $f['options']), true)) $errors[] = $f['label'] . ' tidak valid.';
        if ($f['type'] === 'number' && !is_numeric($val)) $errors[] = $f['label'] . ' harus angka.';
        if ($f['type'] === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) $errors[] = $f['label'] . ' tidak valid.';
        if (mb_strlen($val) > 3000) $errors[] = $f['label'] . ' terlalu panjang.';
        $data[$f['field_key']] = $val;
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // kunci baris event agar kuota & kode urut aman dari balapan
            $pdo->prepare('SELECT id FROM events WHERE id = ? FOR UPDATE')->execute([$ev['id']]);
            if ($ev['quota_num'] !== null && registration_count((int) $ev['id']) >= (int) $ev['quota_num']) {
                throw new RuntimeException('Maaf, kuota baru saja penuh.');
            }
            $teamNameVal = $old['team_name'] ?? $old['school'];
            $dup = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND LOWER(team_name) = LOWER(?) AND status <> 'rejected'");
            $dup->execute([$ev['id'], $teamNameVal]);
            if ($dup->fetchColumn() > 0) throw new RuntimeException(($ev['team_label'] ?: 'Sekolah') . ' tersebut sudah terdaftar.');

            $n = (int) $pdo->query('SELECT COALESCE(MAX(id),0)+1 FROM registrations')->fetchColumn();
            $code = 'LO-' . strtoupper(substr($ev['slug'], 0, 3)) . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
            $pdo->prepare('INSERT INTO registrations (event_id,code,team_name,school,contact_name,phone,email,data,ip) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$ev['id'], $code, $teamNameVal, $old['school'], $old['contact_name'], $old['phone'], $old['email'] ?? '',
                    json_encode($data, JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? null]);
            $pdo->commit();
            $_SESSION['reg_done'] = ['code' => $code, 'slug' => $ev['slug']];
            redirect('register.php?e=' . urlencode($ev['slug']) . '&done=1');
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }
}

$done = isset($_GET['done']) && ($_SESSION['reg_done']['slug'] ?? '') === $ev['slug'] ? $_SESSION['reg_done'] : null;
$val = fn($k) => e($old[$k] ?? '');
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar <?= e($ev['title']) ?> - <?= e(setting('event_name')) ?></title>
  <link rel="icon" type="image/png" href="<?= url('assets/img/Logo-Icon.png') ?>">
  <link rel="apple-touch-icon" href="<?= url('assets/img/Logo-Icon.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="reg-page" style="--accent:<?= e($ev['color']) ?>">
<div class="reg-wrap" style="max-width:680px;margin:0 auto">
  <a class="back-link" href="<?= url('index.php') ?>">&larr; Kembali ke poster</a>
  <div class="reg-card">
    <span class="card-header-badge" style="background:<?= e($ev['color']) ?>;color:<?= e($ev['text_color']) ?>"><?= e($ev['badge']) ?></span>

<?php if ($done): unset($_SESSION['reg_done']); ?>
    <h1>Pendaftaran Berhasil!</h1>
    <p class="lead">Terima kasih. Simpan kode pendaftaran berikut dan tunjukkan ke panitia bila diminta.</p>
    <div class="reg-code"><?= e($done['code']) ?></div>
    <div class="alert alert-info">Status: <b>Menunggu verifikasi</b>. Panitia akan menghubungi lewat WhatsApp / email yang kamu isi.</div>
    <a class="btn-submit" style="display:block;text-align:center;text-decoration:none" href="<?= url('index.php') ?>">Kembali ke Beranda</a>

<?php else: ?>
    <h1>Daftar: <?= e($ev['title']) ?></h1>
    <p class="lead"><?= e($ev['description']) ?></p>

<?php if (!$isOpen): ?>
    <div class="alert alert-info"><?= e(setting('closed_msg')) ?></div>
<?php elseif ($full): ?>
    <div class="alert alert-err">Maaf, kuota arena ini sudah penuh.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-err"><ul style="padding-left:18px"><?php foreach ($errors as $m) echo '<li>' . e($m) . '</li>'; ?></ul></div>
<?php endif; ?>

<?php if ($isOpen && !$full): ?>
    <form class="reg-form" method="post" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="e" value="<?= e($ev['slug']) ?>">
      <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

      <h3>Data Tim &amp; Sekolah</h3>
      <label>Nama Sekolah <?= $ev['slug'] === 'mlbb' ? '(SMP/MTs)' : '/ Instansi' ?> <span class="req">*</span></label>
      <input name="school" maxlength="160" required value="<?= $val('school') ?>" placeholder="Nama asal sekolah...">

<?php if (!empty($ev['team_label'])): ?>
      <label><?= e($ev['team_label']) ?> <span class="req">*</span></label>
      <input name="team_name" maxlength="120" required value="<?= $val('team_name') ?>" placeholder="Nama tim / regu...">
<?php endif; ?>

      <h3 style="margin-top:22px"><?= e($ev['slug'] === 'mlbb' || $ev['slug'] === 'tari' ? 'Kontak Guru Pembimbing' : 'Kontak Penanggung Jawab') ?></h3>
      <label><?= e($ev['contact_label'] ?? 'Nama Penanggung Jawab') ?> <span class="req">*</span></label>
      <input name="contact_name" maxlength="120" required value="<?= $val('contact_name') ?>" placeholder="Nama lengkap...">

      <label><?= e($ev['phone_label'] ?? 'No. WhatsApp') ?> <span class="req">*</span></label>
      <input name="phone" type="tel" maxlength="20" required value="<?= $val('phone') ?>" placeholder="08xxxxxxxxxx">

<?php if (!empty($ev['email_required'])): ?>
      <label>Email <span class="req">*</span></label>
      <input name="email" type="email" maxlength="160" required value="<?= $val('email') ?>" placeholder="nama@email.com">
<?php endif; ?>

<?php if ($fields): ?>
<?php foreach ($fields as $f): $k = 'f_' . $f['field_key']; $req = $f['required'] ? ' <span class="req">*</span>' : ''; ?>
<?php if ($f['type'] === 'heading'): ?>
      <h3 style="margin-top:28px"><?= e($f['label']) ?></h3>
<?php else: ?>
      <label for="<?= e($k) ?>"><?= e($f['label']) . $req ?></label>
<?php if ($f['type'] === 'textarea'): ?>
      <textarea id="<?= e($k) ?>" name="<?= e($k) ?>" maxlength="3000" placeholder="<?= e($f['placeholder'] ?? '') ?>"><?= $val($k) ?></textarea>
<?php elseif ($f['type'] === 'select'): ?>
      <select id="<?= e($k) ?>" name="<?= e($k) ?>">
        <option value="">-- Pilih --</option>
<?php foreach (lines((string) $f['options']) as $o): ?>
        <option value="<?= e($o) ?>" <?= ($old[$k] ?? '') === $o ? 'selected' : '' ?>><?= e($o) ?></option>
<?php endforeach; ?>
      </select>
<?php elseif ($f['type'] === 'file'): ?>
      <input id="<?= e($k) ?>" type="file" name="<?= e($k) ?>" accept=".jpg,.jpeg,.png,.pdf">
      <div class="hint"><?= !empty($f['help']) ? e($f['help']) . ' ' : '' ?>JPG / PNG / PDF, maksimal <?= UPLOAD_MAX_BYTES / 1048576 ?> MB.</div>
<?php else: ?>
      <input id="<?= e($k) ?>" type="<?= e($f['type']) ?>" name="<?= e($k) ?>" maxlength="500" value="<?= $val($k) ?>" placeholder="<?= e($f['placeholder'] ?? '') ?>">
      <?php if (!empty($f['help'])): ?><div class="hint"><?= e($f['help']) ?></div><?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

      <button class="btn-submit" type="submit">Kirim Pendaftaran</button>
    </form>
<?php endif; ?>
<?php endif; ?>
  </div>
</div>
</body>
</html>
