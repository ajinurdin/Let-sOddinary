document.addEventListener("DOMContentLoaded", () => {
  initLivingPosterSVG();
  renderArenaCards("all");
});

/**
 * Fungsi ini otomatis mencari elemen petak di dalam SVG Figma kamu
 * berdasarkan warna fill uniknya, lalu menjadikannya interaktif!
 */
function initLivingPosterSVG() {
  const svg = document.querySelector("#poster-canvas svg");
  if (!svg) return;

  // Pemetaan warna petak di SVG Figma dengan ID acara di data.js
  const tileMapping = [
    { fill: "#34D8A6", dStart: "M11.9048", eventKey: "futsal", label: "⚽ Klik: Arena Futsal" },
    { fill: "#F4819D", dStart: "M264.697", eventKey: "tari", label: "💃 Klik: Tari Tradisional" },
    { fill: "#695BD0", dStart: "M517.49", eventKey: "dkvest", label: "🎨 Klik: Festival DKVest" },
    { fill: "#F6E37A", dStart: "M770.282", eventKey: "mlbb", label: "🎮 Klik: Turnamen MLBB" },
    { fill: "#2F80D9", dStart: "M661.893", eventKey: "market", label: "🎪 Klik: Market Day" }
  ];

  const allPaths = svg.querySelectorAll("path");

  tileMapping.forEach(map => {
    allPaths.forEach(path => {
      const fillAttr = path.getAttribute("fill");
      const dAttr = path.getAttribute("d") || "";

      if (fillAttr === map.fill && dAttr.startsWith(map.dStart)) {
        path.classList.add("svg-hotspot");
        path.setAttribute("data-event", map.eventKey);
        
        // Tambahkan efek klik untuk membuka Pop-up Card Lomba
        path.addEventListener("click", () => {
          openTileModal(map.eventKey);
        });
      }
    });
  });

  // Buat ikon di atas petak (filter1, filter4, filter6, filter7, filter8, filter10) 
  // meneruskan klik ke petak di bawahnya (pointer-events: none) agar mudah diklik
  const iconFilters = ["filter1_d_85_58", "filter4_d_85_58", "filter6_d_85_58", "filter7_d_85_58", "filter8_d_85_58", "filter10_d_85_58"];
  iconFilters.forEach(fid => {
    const iconGroup = svg.querySelector(`g[filter="url(#${fid})"]`);
    if (iconGroup) iconGroup.style.pointerEvents = "none";
  });

  // Buat Pita Putih "are YOU ready?" (filter3_d_85_58) bisa diklik untuk scroll ke daftar lomba
  const readyBanner = svg.querySelector('g[filter="url(#filter3_d_85_58)"]');
  if (readyBanner) {
    readyBanner.classList.add("svg-hotspot");
    readyBanner.addEventListener("click", () => {
      document.getElementById("arena-lengkap").scrollIntoView({ behavior: "smooth" });
    });
  }

  // Buat Dadu bisa diklik untuk Kocok Dadu
  allPaths.forEach(path => {
    const dAttr = path.getAttribute("d") || "";
    if (dAttr.startsWith("M108.96 203.33")) {
      path.style.cursor = "pointer";
      path.addEventListener("click", rollPosterDice);
    }
  });
}

// Membuka Pop-up Card saat petak di poster diklik
function openTileModal(key) {
  const item = eventsData[key];
  if (!item) return;

  const badge = document.getElementById("mBadge");
  badge.textContent = item.badge;
  badge.style.background = item.color;
  badge.style.color = item.textColor;

  document.getElementById("mTitle").textContent = item.title;
  document.getElementById("mDesc").textContent = item.description;
  document.getElementById("mFee").textContent = item.fee;
  document.getElementById("mPrize").textContent = item.prizePool;
  document.getElementById("mQuota").textContent = item.quota;
  document.getElementById("mCp").textContent = item.cp;

  document.getElementById("mRules").innerHTML = item.requirements
    .map(r => `<li>${r}</li>`)
    .join("");

  const cardBox = document.getElementById("modalCardBox");
  cardBox.style.boxShadow = `16px 18px 0px ${item.color}`;

  const btnReg = document.getElementById("mBtnReg");
  btnReg.style.background = item.color;
  btnReg.style.color = item.textColor;
  btnReg.href = item.regUrl;

  document.getElementById("tileModal").classList.add("open");
}

function closeTileModal(e, force = false) {
  if (force || e.target.id === "tileModal") {
    document.getElementById("tileModal").classList.remove("open");
  }
}

// Fitur Kocok Dadu: Menyalakan petak-petak di poster secara bergantian lalu membuka 1 lomba acak
function rollPosterDice() {
  const hotspots = document.querySelectorAll(".svg-hotspot[data-event]");
  if (hotspots.length === 0) return;

  let count = 0;
  const interval = setInterval(() => {
    hotspots.forEach(h => h.classList.remove("highlight-active"));
    const randomTile = hotspots[count % hotspots.length];
    randomTile.classList.add("highlight-active");
    count++;

    if (count > 9) {
      clearInterval(interval);
      const chosenTile = hotspots[Math.floor(Math.random() * hotspots.length)];
      hotspots.forEach(h => h.classList.remove("highlight-active"));
      chosenTile.classList.add("highlight-active");

      setTimeout(() => {
        openTileModal(chosenTile.getAttribute("data-event"));
      }, 350);
    }
  }, 110);
}

// Render Kartu di bawah poster
function renderArenaCards(category) {
  const grid = document.getElementById("arenaGrid");
  const list = Object.values(eventsData).filter(
    item => category === "all" || item.category === category
  );

  grid.innerHTML = list.map(item => `
    <div class="arena-card-item" style="background:${item.color}; color:${item.textColor}">
      <div>
        <span style="display:inline-block; background:#FFF5DA; color:#1f1646; font-weight:900; font-size:0.75rem; padding:5px 12px; border-radius:20px; border:2px solid #1f1646; margin-bottom:14px;">
          ${item.badge}
        </span>
        <h3 style="font-family:'Montserrat',sans-serif; font-weight:900; font-size:1.7rem; margin-bottom:10px;">
          ${item.title}
        </h3>
        <p style="font-size:0.92rem; line-height:1.5; margin-bottom:20px;">
          ${item.description}
        </p>
      </div>
      <button onclick="openTileModal('${item.id}')" style="width:100%; background:#FFF5DA; color:#1f1646; border:3px solid #1f1646; padding:12px; border-radius:14px; font-family:'Montserrat',sans-serif; font-weight:900; cursor:pointer; box-shadow:4px 4px 0px #1f1646;">
        Lihat Detail & Daftar →
      </button>
    </div>
  `).join("");
}

function filterCards(cat, btn) {
  document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
  btn.classList.add("active");
  renderArenaCards(cat);
}