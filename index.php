<?php
require_once __DIR__ . '/includes/bootstrap.php';

$events   = events_all();
$status   = setting('status', 'coming_soon');
$schedule = db()->query('SELECT s.*, e.slug, e.color FROM schedule s LEFT JOIN events e ON e.id = s.event_id ORDER BY s.day_date, s.time_label, s.id')->fetchAll();

$jsEvents = [];
foreach ($events as $ev) {
    $count = registration_count((int) $ev['id']);
    $full  = $ev['quota_num'] !== null && $count >= (int) $ev['quota_num'];
    if ($ev['reg_open'] == 0 || $status === 'closed') $state = 'closed';
    elseif ($status !== 'open') $state = 'soon';
    elseif ($full) $state = 'full';
    else $state = 'open';

    $jsEvents[$ev['slug']] = [
        'slug' => $ev['slug'], 'title' => $ev['title'], 'category' => $ev['category'],
        'badge' => $ev['badge'], 'color' => $ev['color'], 'textColor' => $ev['text_color'],
        'description' => $ev['description'], 'fee' => $ev['fee'], 'prize' => $ev['prize'],
        'quota' => $ev['quota_text'] ?: ($ev['quota_num'] ? $ev['quota_num'] . ' peserta' : '-'),
        'schedule' => trim(($ev['event_date'] ? fmt_date($ev['event_date']) . ' - ' : '') . $ev['schedule_text'], ' -'),
        'contact' => $ev['contact'], 'requirements' => lines($ev['requirements']),
        'juknis' => $ev['juknis_url'], 'tile' => $ev['poster_tile'], 'state' => $state,
        'regUrl' => url('register.php?e=' . $ev['slug']),
        'remaining' => $ev['quota_num'] !== null ? max(0, (int) $ev['quota_num'] - $count) : null,
    ];
}
$eventName = setting('event_name', 'LETRIS ODDINARY');
$statusText = ['coming_soon' => 'COMING SOON', 'open' => 'PENDAFTARAN DIBUKA', 'closed' => 'PENDAFTARAN DITUTUP'][$status] ?? 'COMING SOON';
$catLabels = ['lomba' => 'Lomba & Turnamen', 'festival' => 'Festival & Bazaar', 'penutup' => 'Pentas Seni'];
$marquee = setting('marquee');
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($eventName) ?> - <?= e($statusText) ?></title>
  <meta name="description" content="<?= e(setting('tagline')) ?> Futsal, Tari Tradisional, MLBB, DKVest, Market Day & Pentas Seni - <?= e(setting('period_text')) ?>.">
  <link rel="icon" type="image/png" href="<?= url('assets/img/Logo-Icon.png') ?>">
  <link rel="apple-touch-icon" href="<?= url('assets/img/Logo-Icon.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,700;0,800;0,900;1,900&family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<?php if (setting('splash', '1') === '1'): ?>
<?php include __DIR__ . '/includes/intro_splash.php'; ?>
<?php endif; ?>

  <div class="floating-hud">
    <button type="button" class="hud-pill brand-pill" id="replayIntroBtn" title="Tampilkan Animasi Intro Logo">
      <img src="<?= url('assets/img/Logo-Icon.png') ?>" alt="Logo" class="hud-logo">
      <span><?= e($eventName) ?></span>
    </button>
    <button class="hud-pill action-pill" type="button" onclick="rollPosterDice()">Kocok Dadu!</button>
    <a href="#arena-lengkap" class="hud-pill link-pill">Semua Arena</a>
    <a href="#rundown" class="hud-pill link-pill">Rundown</a>
  </div>

  <div class="interactive-toast" id="hintToast">
    Poster ini interaktif! Klik petak Futsal, Tari, MLBB, DKVest, Market Day, atau dadunya.
  </div>

  <div class="svg-tooltip" id="svgTooltip"></div>

  <section class="poster-stage">
    <div class="poster-container" id="poster-canvas">
<?php include __DIR__ . '/includes/poster.svg.php'; ?>
    </div>
  </section>

  <section class="status-strip">
    <span class="status-pill status-<?= e($status) ?>"><?= e($statusText) ?></span>
    <span><?= e(setting('period_text')) ?> &middot; <?= e(setting('venue')) ?></span>
    <div class="countdown" id="countdown" data-target="<?= e(setting('target_date')) ?>"></div>
  </section>

<?php if ($marquee !== ''): $m = e($marquee) . ' | '; ?>
  <div class="slanted-banner marquee"><div class="marquee-track"><span><?= str_repeat($m, 3) ?></span><span><?= str_repeat($m, 3) ?></span></div></div>
<?php endif; ?>

  <section id="arena-lengkap" class="continuation-section">
    <div class="content-wrapper">
      <div class="section-heading-box">
        <h2 class="mont-giant-title">KARTU ARENA ODDINARY</h2>
        <p class="subtitle-text">Pilih arena favoritmu untuk melihat detail dan mendaftar.</p>
      </div>
      <div class="filter-bar">
        <button class="filter-btn active" data-cat="all">Semua Arena (<?= count($events) ?>)</button>
        <button class="filter-btn" data-cat="lomba">Lomba &amp; Turnamen</button>
        <button class="filter-btn" data-cat="festival">Festival &amp; Bazaar</button>
        <button class="filter-btn" data-cat="penutup">Pentas Seni</button>
      </div>
      <div class="arena-grid" id="arenaGrid">
<?php foreach ($events as $ev): $j = $jsEvents[$ev['slug']]; ?>
        <article class="arena-card-item" data-cat="<?= e($ev['category']) ?>" style="background:<?= e($ev['color']) ?>;color:<?= e($ev['text_color']) ?>">
          <div>
            <span class="arena-badge"><?= e($ev['badge']) ?></span>
            <h3><?= e($ev['title']) ?></h3>
            <p><?= e($ev['description']) ?></p>
            <p class="arena-meta"><?= e($j['schedule']) ?></p>
          </div>
          <button type="button" class="arena-btn" data-open="<?= e($ev['slug']) ?>">Lihat Detail &amp; Daftar &rarr;</button>
        </article>
<?php endforeach; ?>
      </div>
    </div>
  </section>

<?php if ($schedule): ?>
  <section id="rundown" class="continuation-section rundown">
    <div class="content-wrapper">
      <h2 class="mont-giant-title">RUNDOWN DESEMBER</h2>
      <p class="subtitle-text">Jadwal dapat berubah. Pantau terus informasi terbaru.</p>
      <ol class="timeline">
<?php foreach ($schedule as $s): ?>
        <li style="--c:<?= e($s['color'] ?: '#C0CF2F') ?>">
          <div class="tl-date"><?= e(fmt_date($s['day_date'])) ?><?= $s['time_label'] ? '<small>' . e($s['time_label']) . '</small>' : '' ?></div>
          <div class="tl-body"><strong><?= e($s['title']) ?></strong><?= $s['description'] ? '<span>' . e($s['description']) . '</span>' : '' ?></div>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>
<?php endif; ?>

  <footer class="site-footer">
    <div class="footer-inner">
      <div class="foot-col foot-main">
        <p class="foot-brand"><?= e($eventName) ?></p>
        <p class="foot-tagline"><?= e(setting('tagline')) ?></p>
        <p class="foot-powered">Powered by <strong>SMK Letris Pamulang</strong></p>
      </div>

      <div class="foot-col foot-addr">
        <h4>Alamat &amp; Lokasi Acara</h4>
        <p><strong>SMK Letris Pamulang</strong></p>
        <p>Jl. Raya Siliwangi No. 55, Kel. Pondok Benda, Kec. Pamulang, Kota Tangerang Selatan, Banten 15416</p>
      </div>

      <div class="foot-col foot-links">
        <h4>Kontak &amp; Informasi</h4>
        <p>
<?php if (setting('contact_wa')): ?>
          <a href="https://wa.me/<?= e(setting('contact_wa')) ?>" target="_blank" rel="noopener" class="foot-btn">WhatsApp Panitia</a>
<?php endif; ?>
<?php if (setting('instagram')): ?>
          <a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" class="foot-btn">Instagram @letrispamulangofficial</a>
<?php endif; ?>
        </p>
        <p class="foot-copy">&copy; <?= date('Y') ?> Letris Oddinary. <?= e(setting('footer_text')) ?></p>
      </div>
    </div>
  </footer>

  <div class="card-modal-backdrop" id="tileModal" aria-hidden="true">
    <div class="board-game-card" role="dialog" aria-modal="true" aria-labelledby="mTitle">
      <button class="btn-close-card" type="button" id="mClose" aria-label="Tutup">&times;</button>
      <div class="card-header-badge" id="mBadge"></div>
      <h2 class="card-title" id="mTitle"></h2>
      <p class="card-desc" id="mDesc"></p>
      <div class="card-stats-grid">
        <div class="stat-item"><span class="stat-label">Biaya</span><span class="stat-val" id="mFee"></span></div>
        <div class="stat-item"><span class="stat-label">Hadiah / Benefit</span><span class="stat-val" id="mPrize"></span></div>
        <div class="stat-item"><span class="stat-label">Kuota</span><span class="stat-val" id="mQuota"></span></div>
        <div class="stat-item"><span class="stat-label">Jadwal</span><span class="stat-val" id="mSchedule"></span></div>
        <div class="stat-item stat-wide"><span class="stat-label">Contact Person</span><span class="stat-val" id="mCp"></span></div>
      </div>
      <div class="card-rules-box"><h4>Ketentuan &amp; Info Penting</h4><ul id="mRules"></ul></div>
      <div class="card-footer-btns">
        <a href="#" id="mBtnReg" class="btn-game-primary"></a>
        <a href="#" id="mBtnJuknis" class="btn-game-secondary" target="_blank" rel="noopener">Unduh Juknis</a>
      </div>
    </div>
  </div>

  <script>window.LO_EVENTS = <?= json_encode($jsEvents, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
  <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
