<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$pdo = db();

$event = (int) ($_GET['event'] ?? 0);
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$where = ['1=1']; $args = [];
if ($event) { $where[] = 'r.event_id=?'; $args[] = $event; }
if (isset(status_labels()[$status])) { $where[] = 'r.status=?'; $args[] = $status; }
if ($q !== '') { $where[] = '(r.team_name LIKE ? OR r.school LIKE ? OR r.contact_name LIKE ? OR r.code LIKE ? OR r.phone LIKE ?)'; array_push($args, ...array_fill(0, 5, "%$q%")); }
$st = $pdo->prepare('SELECT r.*, e.title FROM registrations r JOIN events e ON e.id=r.event_id WHERE ' . implode(' AND ', $where) . ' ORDER BY r.id');
$st->execute($args);
$rows = $st->fetchAll();

// kumpulkan semua key data dinamis (label dari form_fields)
$labels = [];
foreach ($pdo->query('SELECT field_key, label FROM form_fields ORDER BY event_id, sort_order') as $f) $labels[$f['field_key']] ??= $f['label'];
$keys = [];
foreach ($rows as $r) foreach (array_keys(json_decode((string) $r['data'], true) ?: []) as $k) $keys[$k] = true;
$keys = array_keys($keys);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="pendaftar-letris-' . date('Ymd-His') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
$safe = fn($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : (string) $v; // cegah CSV/formula injection
fputcsv($out, array_merge(['Kode', 'Arena', 'Nama', 'Sekolah', 'Penanggung Jawab', 'WhatsApp', 'Email', 'Status', 'Catatan', 'Waktu'], array_map(fn($k) => $labels[$k] ?? $k, $keys)));
foreach ($rows as $r) {
    $d = json_decode((string) $r['data'], true) ?: [];
    $line = [$r['code'], $r['title'], $r['team_name'], $r['school'], $r['contact_name'], $r['phone'], $r['email'], status_labels()[$r['status']], $r['notes'], $r['created_at']];
    foreach ($keys as $k) {
        $v = $d[$k] ?? '';
        $line[] = is_array($v) ? 'File: ' . $v['name'] : $v;
    }
    fputcsv($out, array_map($safe, $line));
}
