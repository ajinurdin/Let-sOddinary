<?php
/**
 * Installer Letris Oddinary: membuat database, tabel, data awal & akun admin pertama.
 * Setelah terpasang, halaman ini terkunci otomatis (dan sebaiknya dihapus saat produksi).
 */
require_once __DIR__ . '/config.php';
session_start();

function schema_sql(): array
{
    return [
        "CREATE TABLE IF NOT EXISTS settings (
            k VARCHAR(60) PRIMARY KEY,
            v TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(40) NOT NULL UNIQUE,
            title VARCHAR(120) NOT NULL,
            category ENUM('lomba','festival','penutup') NOT NULL DEFAULT 'lomba',
            badge VARCHAR(80) NOT NULL DEFAULT '',
            color VARCHAR(9) NOT NULL DEFAULT '#34D8A6',
            text_color VARCHAR(9) NOT NULL DEFAULT '#1a1438',
            description TEXT NOT NULL,
            fee VARCHAR(120) NOT NULL DEFAULT '',
            prize VARCHAR(160) NOT NULL DEFAULT '',
            quota_num INT NULL,
            quota_text VARCHAR(120) NOT NULL DEFAULT '',
            schedule_text VARCHAR(160) NOT NULL DEFAULT '',
            event_date DATE NULL,
            contact VARCHAR(160) NOT NULL DEFAULT '',
            requirements TEXT NOT NULL,
            juknis_url VARCHAR(255) NOT NULL DEFAULT '',
            team_label VARCHAR(60) NOT NULL DEFAULT 'Nama Tim',
            contact_label VARCHAR(80) NOT NULL DEFAULT 'Nama Penanggung Jawab',
            phone_label VARCHAR(80) NOT NULL DEFAULT 'No. WhatsApp',
            email_required TINYINT(1) NOT NULL DEFAULT 1,
            poster_tile VARCHAR(10) NOT NULL DEFAULT '',
            reg_open TINYINT(1) NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS form_fields (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            field_key VARCHAR(40) NOT NULL,
            label VARCHAR(120) NOT NULL,
            type ENUM('text','textarea','number','email','tel','select','file','heading') NOT NULL DEFAULT 'text',
            options TEXT NULL,
            required TINYINT(1) NOT NULL DEFAULT 1,
            placeholder VARCHAR(160) NOT NULL DEFAULT '',
            help VARCHAR(255) NOT NULL DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0,
            UNIQUE KEY uq_event_key (event_id, field_key),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            code VARCHAR(30) NOT NULL UNIQUE,
            team_name VARCHAR(120) NOT NULL,
            school VARCHAR(160) NOT NULL,
            contact_name VARCHAR(120) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(160) NOT NULL,
            data LONGTEXT NULL,
            status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
            notes TEXT NULL,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_event (event_id),
            KEY idx_status (status),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            day_date DATE NOT NULL,
            time_label VARCHAR(40) NOT NULL DEFAULT '',
            title VARCHAR(160) NOT NULL,
            description VARCHAR(255) NOT NULL DEFAULT '',
            event_id INT NULL,
            KEY idx_date (day_date),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
}

function seed(PDO $pdo): void
{
    $data = require __DIR__ . '/includes/seed_data.php';

    $st = $pdo->prepare('INSERT IGNORE INTO settings (k, v) VALUES (?, ?)');
    foreach ($data['settings'] as $k => $v) $st->execute([$k, $v]);

    $ins = $pdo->prepare('INSERT IGNORE INTO events
        (slug,title,category,badge,color,text_color,description,fee,prize,quota_num,quota_text,schedule_text,event_date,contact,requirements,team_label,contact_label,phone_label,email_required,poster_tile,sort_order)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $i = 0;
    foreach ($data['events'] as $slug => $e) $ins->execute([$slug, ...$e, ++$i]);

    $idOf = $pdo->prepare('SELECT id FROM events WHERE slug = ?');
    $fi = $pdo->prepare('INSERT IGNORE INTO form_fields (event_id,field_key,label,type,options,required,placeholder,help,sort_order) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach ($data['fields'] as $slug => $list) {
        $idOf->execute([$slug]);
        $eid = (int) $idOf->fetchColumn();
        foreach ($list as $n => $f) $fi->execute([$eid, ...$f, $n + 1]);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM schedule')->fetchColumn() === 0) {
        $si = $pdo->prepare('INSERT INTO schedule (day_date,time_label,title,description,event_id) VALUES (?,?,?,?,?)');
        foreach ($data['schedule'] as $r) {
            $idOf->execute([$r[4]]);
            $si->execute([$r[0], $r[1], $r[2], $r[3], $idOf->fetchColumn() ?: null]);
        }
    }
}
$msg = '';
$installed = false;
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db = $pdo->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote(DB_NAME))->fetchColumn();
    if ($db) {
        $pdo->exec('USE `' . DB_NAME . '`');
        $has = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
        $installed = $has && (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }
} catch (PDOException $e) {
    exit('Tidak bisa terhubung ke MySQL: ' . htmlspecialchars($e->getMessage()));
}

if (!$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $n = trim($_POST['name'] ?? '');
    $p = $_POST['password'] ?? '';
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $u)) {
        $msg = 'Username 3-50 karakter (huruf, angka, _ . -).';
    } elseif (strlen($p) < 8) {
        $msg = 'Password minimal 8 karakter.';
    } else {
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        $pdo->exec('USE `' . DB_NAME . '`');
        foreach (schema_sql() as $sql) $pdo->exec($sql);
        seed($pdo);
        $pdo->prepare('INSERT INTO users (username,name,password_hash) VALUES (?,?,?)')
            ->execute([$u, $n ?: $u, password_hash($p, PASSWORD_DEFAULT)]);
        $installed = true;
        $msg = 'ok';
    }
}
?><!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install - Letris Oddinary</title>
<style>
body{font-family:system-ui,sans-serif;background:#6154C2;color:#1f1646;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px}
.box{background:#FFF5DA;border:6px solid #1f1646;border-radius:24px;padding:30px;max-width:420px;width:100%;box-shadow:10px 12px 0 #C0CF2F}
h1{margin:0 0 6px}label{display:block;font-weight:700;margin:14px 0 4px}
input{width:100%;padding:10px;border:3px solid #1f1646;border-radius:12px;font-size:1rem}
button,.btn{margin-top:18px;background:#C0CF2F;border:3px solid #1f1646;border-radius:14px;padding:12px 18px;font-weight:800;cursor:pointer;display:inline-block;color:#1f1646;text-decoration:none}
.err{background:#F4819D;padding:8px 12px;border-radius:10px;margin-top:10px}
</style></head><body><div class="box">
<h1>Install Letris Oddinary</h1>
<?php if ($msg === 'ok'): ?>
  <p>Instalasi berhasil. Database <b><?= DB_NAME ?></b> dibuat beserta data awal.</p>
  <a class="btn" href="admin/login.php">Masuk ke CMS</a>
  <p style="font-size:.85rem">Demi keamanan, hapus file <code>install.php</code> sebelum situs dipublikasikan.</p>
<?php elseif ($installed): ?>
  <p>Sudah terpasang. Installer terkunci.</p>
  <a class="btn" href="admin/login.php">Ke CMS</a>
<?php else: ?>
  <p>Buat akun admin pertama. Database <b><?= DB_NAME ?></b> akan dibuat otomatis.</p>
  <?php if ($msg): ?><div class="err"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="post">
    <label>Nama</label><input name="name" required>
    <label>Username</label><input name="username" required>
    <label>Password (min. 8 karakter)</label><input type="password" name="password" required minlength="8">
    <button>Install Sekarang</button>
  </form>
<?php endif; ?>
</div></body></html>
