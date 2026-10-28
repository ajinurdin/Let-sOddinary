(function () {
  const EVENTS = window.LO_EVENTS || {};

  // slot petak di poster SVG -> ciri path (fill + awal atribut d)
  const TILES = {
    tile1: { fill: '#34D8A6', d: 'M11.9048' },   // kiri bawah (mint)
    tile2: { fill: '#F4819D', d: 'M264.697' },   // pink
    tile3: { fill: '#695BD0', d: 'M517.49' },    // tengah (ungu, berisi gambar)
    tile4: { fill: '#F6E37A', d: 'M770.282' },   // kuning kanan
    tile5: { fill: '#2F80D9', d: 'M661.893' }    // biru atas
  };
  const SVGNS = 'http://www.w3.org/2000/svg';
  const $ = (s, r = document) => r.querySelector(s);
  const tooltip = $('#svgTooltip');
  const hotspots = [];

  function slugForTile(tile) {
    return Object.keys(EVENTS).find(k => EVENTS[k].tile === tile);
  }

  function makeOverlay(svg, shape, onClick, label, targetPath) {
    shape.setAttribute('fill', 'transparent');
    shape.classList.add('svg-overlay');
    shape.setAttribute('tabindex', '0');
    shape.setAttribute('role', 'button');
    if (label) shape.setAttribute('aria-label', label);
    const on = () => { targetPath && targetPath.classList.add('hot'); shape.classList.add('is-hot'); if (label) { tooltip.textContent = label; tooltip.classList.add('active'); } };
    const off = () => { targetPath && targetPath.classList.remove('hot'); shape.classList.remove('is-hot'); tooltip.classList.remove('active'); };
    shape.addEventListener('mouseenter', on);
    shape.addEventListener('mouseleave', off);
    shape.addEventListener('focus', on);
    shape.addEventListener('blur', off);
    shape.addEventListener('mousemove', e => { tooltip.style.left = (e.clientX + 16) + 'px'; tooltip.style.top = (e.clientY + 16) + 'px'; });
    shape.addEventListener('click', onClick);
    shape.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); } });
    svg.appendChild(shape);
    return shape;
  }

  function initPoster() {
    const svg = $('#poster-canvas svg');
    if (!svg) return;
    const paths = [...svg.querySelectorAll('path')];

    Object.entries(TILES).forEach(([tile, t]) => {
      const slug = slugForTile(tile);
      if (!slug) return;
      const src = paths.find(p => p.getAttribute('fill') === t.fill && (p.getAttribute('d') || '').startsWith(t.d));
      if (!src) return;
      src.classList.add('svg-tile-fill');
      const ov = document.createElementNS(SVGNS, 'path');
      ov.setAttribute('d', src.getAttribute('d'));
      ov.dataset.event = slug;
      makeOverlay(svg, ov, () => openModal(slug), 'Klik: ' + EVENTS[slug].title, src);
      hotspots.push(ov);
    });

    // banner "are YOU ready?" -> scroll ke daftar arena
    const banner = document.createElementNS(SVGNS, 'path');
    banner.setAttribute('d', 'M476.923 1220L449.073 1119.27L962.82 909.5L1044 1026.83Z');
    makeOverlay(svg, banner, () => $('#arena-lengkap').scrollIntoView({ behavior: 'smooth' }), 'Lihat semua arena');

    // dadu: kelompokkan seluruh bagian dadu (bayangan, 3 bidang kubus, dan 7 titik lingkaran) agar berputar menyatu
    const dicePath = paths.find(p => (p.getAttribute('d') || '').startsWith('M189.68 441.324'));
    if (dicePath) {
      const g = document.createElementNS(SVGNS, 'g');
      g.classList.add('poster-dice-group');
      dicePath.parentNode.insertBefore(g, dicePath);
      let el = dicePath;
      // 1 bayangan + 3 bidang kubus + 7 titik (total 11 elemen)
      for (let i = 0; i < 11 && el; i++) {
        const next = el.nextElementSibling;
        g.appendChild(el);
        el = next;
      }
      window.__dice = g;
    }
    const dice = document.createElementNS(SVGNS, 'rect');
    ['x:105', 'y:150', 'width:195', 'height:220'].forEach(s => { const [k, v] = s.split(':'); dice.setAttribute(k, v); });
    makeOverlay(svg, dice, rollPosterDice, 'Kocok dadu!');
  }

  window.rollPosterDice = function () {
    if (window.__dice) {
      window.__dice.classList.remove('rolling-now');
      void window.__dice.getBoundingClientRect();
      window.__dice.classList.add('rolling-now');
    }
  };

  /* ---------- Modal ---------- */
  const modal = $('#tileModal');
  let lastFocus = null;

  function openModal(slug) {
    const ev = EVENTS[slug];
    if (!ev) return;
    const badge = $('#mBadge');
    badge.textContent = ev.badge;
    badge.style.background = ev.color;
    badge.style.color = ev.textColor;
    $('#mTitle').textContent = ev.title;
    $('#mDesc').textContent = ev.description;
    $('#mFee').textContent = ev.fee || '-';
    $('#mPrize').textContent = ev.prize || '-';
    $('#mQuota').textContent = ev.quota + (ev.remaining !== null && ev.state === 'open' ? ` (sisa ${ev.remaining})` : '');
    $('#mSchedule').textContent = ev.schedule || '-';
    $('#mCp').textContent = ev.contact || '-';
    const ul = $('#mRules');
    ul.innerHTML = '';
    ev.requirements.forEach(r => { const li = document.createElement('li'); li.textContent = r; ul.appendChild(li); });
    $('.board-game-card', modal).style.boxShadow = `16px 18px 0 ${ev.color}`;

    const reg = $('#mBtnReg');
    const labels = { open: 'Daftar Sekarang', soon: 'Segera Dibuka', closed: 'Pendaftaran Ditutup', full: 'Kuota Penuh' };
    reg.textContent = labels[ev.state];
    reg.style.background = ev.color;
    reg.style.color = ev.textColor;
    if (ev.state === 'open') { reg.href = ev.regUrl; reg.classList.remove('disabled'); reg.removeAttribute('aria-disabled'); }
    else { reg.removeAttribute('href'); reg.classList.add('disabled'); reg.setAttribute('aria-disabled', 'true'); }

    const jk = $('#mBtnJuknis');
    jk.style.display = ev.juknis ? '' : 'none';
    if (ev.juknis) jk.href = ev.juknis;

    lastFocus = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    $('#mClose').focus();
  }

  function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  $('#mClose').addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });

  /* ---------- Kartu arena ---------- */
  document.querySelectorAll('[data-open]').forEach(b => b.addEventListener('click', () => openModal(b.dataset.open)));
  document.querySelectorAll('.filter-btn').forEach(btn => btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.arena-card-item').forEach(c => {
      c.style.display = (btn.dataset.cat === 'all' || c.dataset.cat === btn.dataset.cat) ? '' : 'none';
    });
  }));

  /* ---------- Countdown ---------- */
  const cd = $('#countdown');
  if (cd && cd.dataset.target) {
    const target = new Date(cd.dataset.target).getTime();
    const tick = () => {
      const diff = target - Date.now();
      if (isNaN(target)) { cd.textContent = ''; return; }
      if (diff <= 0) { cd.textContent = 'Acara sedang berlangsung / telah dimulai!'; return; }
      const d = Math.floor(diff / 864e5), h = Math.floor(diff % 864e5 / 36e5), m = Math.floor(diff % 36e5 / 6e4), s = Math.floor(diff % 6e4 / 1e3);
      cd.innerHTML = [[d, 'Hari'], [h, 'Jam'], [m, 'Menit'], [s, 'Detik']].map(([v, l]) => `<div><b>${String(v).padStart(2, '0')}</b><small>${l}</small></div>`).join('');
    };
    tick(); setInterval(tick, 1000);
  }

  /* ---------- Intro Splash Overlay ---------- */
  const splashOverlay = $('#introSplashOverlay');
  const skipBtn = $('#introSkipBtn');
  const replayBtn = $('#replayIntroBtn');

  function triggerIntroAnimation() {
    const ov = $('#introSplashOverlay');
    if (!ov) return;

    // Reset overlay visibility
    ov.classList.remove('is-hidden');

    // Clone & replace wrapper logo agar seluruh CSS animation di elemen-elemen SVG me-restart dari 0
    const widget = ov.querySelector('#introLogoWidget');
    if (widget) {
      const clone = widget.cloneNode(true);
      widget.parentNode.replaceChild(clone, widget);
    }

    const dismiss = () => {
      ov.classList.add('is-hidden');
      sessionStorage.setItem('lo_intro_seen', '1');
    };

    const newSkipBtn = ov.querySelector('#introSkipBtn');
    if (newSkipBtn) newSkipBtn.onclick = dismiss;

    ov.onclick = e => {
      if (e.target === ov || e.target.closest('#introLogoWidget') || e.target.closest('#introSkipBtn')) {
        dismiss();
      }
    };

    clearTimeout(window.__splashTimer);
    window.__splashTimer = setTimeout(dismiss, 3200);
  }

  if (splashOverlay) {
    const hasSeen = sessionStorage.getItem('lo_intro_seen');
    if (hasSeen === '1') {
      splashOverlay.classList.add('is-hidden');
    } else {
      triggerIntroAnimation();
    }
  }

  if (replayBtn) {
    replayBtn.addEventListener('click', e => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
      triggerIntroAnimation();
    });
  }

  setTimeout(() => { const t = $('#hintToast'); if (t) t.style.display = 'none'; }, 9000);
  initPoster();
})();
