// Data Konfigurasi Global (Bisa diatur dari CMS)
const siteConfig = {
  eventName: "LETRIS ODDINARY",
  status: "COMING SOON", // Bisa diubah di CMS jadi "OPEN REGISTRATION"
  tagline: "Are YOU Ready? Turn On Your Odd Side!",
  targetDate: "2026-11-25T08:00:00",
  announcement: "★ COMING SOON: LETRIS ODDINARY ★ SIAPKAN TIM FUTSAL, SQUAD MLBB, DAN KARYA TERBAIKMU! ★ PANTAU TERUS JADWAL OPEN REGISTRATION ★"
};

// Database Mata Lomba & Festival (Tersambung langsung dengan ID Petak di SVG)
const eventsData = {
  futsal: {
    id: "futsal",
    title: "Futsal Championship",
    category: "lomba",
    badge: "⚽ Sports Arena",
    color: "#34D8A6",
    textColor: "#1a1438",
    coneColor: "#695BD0",
    fee: "Rp 350.000 / Tim",
    prizePool: "Total Jutaan Rupiah + Piala Bergilir",
    quota: "32 Tim Pelajar",
    schedule: "Segera Diumumkan",
    cp: "0812-XXXX-XXXX (Kak Raka)",
    description: "Tunjukkan sportivitas, taktik cepat, dan atmosfer suporter paling meriah! Pertandingan menggunakan sistem gugur dengan wasit berlisensi resmi.",
    requirements: [
      "Pelajar aktif (dibuktikan dengan Kartu Pelajar / Rapor)",
      "1 Tim terdiri dari maksimal 10 pemain + 2 official",
      "Wajib menggunakan jersey bernomor punggung & deker"
    ],
    juknisUrl: "#juknis-futsal",
    regUrl: "#daftar-futsal"
  },
  tari: {
    id: "tari",
    title: "Tari Tradisional Kreasi",
    category: "lomba",
    badge: "💃 Culture & Art",
    color: "#F4819D",
    textColor: "#1a1438",
    coneColor: "#F4819C",
    fee: "Rp 200.000 / Tim",
    prizePool: "Uang Pembinaan + Plakat + Sertifikat",
    quota: "20 Tim Tari",
    schedule: "Segera Diumumkan",
    cp: "0812-XXXX-XXXX (Kak Nabila)",
    description: "Padukan keanggunan gerak tari Nusantara dengan aransemen energik di panggung utama Letris Oddinary.",
    requirements: [
      "Beregu (3 - 7 penari per tim)",
      "Durasi penampilan 5 - 7 menit",
      "Musik pengiring diserahkan dalam format MP3 H-3 acara"
    ],
    juknisUrl: "#juknis-tari",
    regUrl: "#daftar-tari"
  },
  dkvest: {
    id: "dkvest",
    title: "DKVest Art Exhibition",
    category: "festival",
    badge: "🎨 Visual Art Festival",
    color: "#695BD0",
    textColor: "#FFF5DA",
    coneColor: "#34D8A6",
    fee: "FREE ENTRY (Terbuka Umum)",
    prizePool: "Voting Karya Terfavorit & Art Merch",
    quota: "100+ Karya Pameran",
    schedule: "Selama Event Berlangsung",
    cp: "0812-XXXX-XXXX (Tim DKVest)",
    description: "Ruang eksplorasi visual terbesar tahun ini! Menampilkan pameran ilustrasi digital, fotografi, instalasi seni, pemutaran film pendek, dan live mural.",
    requirements: [
      "Katalog karya digital & fisik dari talenta muda DKV",
      "Tersedia photobooth tematik & workshop kreatif",
      "Voting terbuka untuk pengunjung memilih Best Artwork"
    ],
    juknisUrl: "#katalog-dkvest",
    regUrl: "#submit-karya"
  },
  mlbb: {
    id: "mlbb",
    title: "Mobile Legends (MLBB)",
    category: "lomba",
    badge: "🎮 E-Sports Tournament",
    color: "#F6E37A",
    textColor: "#1a1438",
    coneColor: "#FFBF3E",
    fee: "Rp 100.000 / Squad",
    prizePool: "Uang Tunai + Sertifikat + E-Certificate",
    quota: "64 Squad",
    schedule: "Kualifikasi Online & Final Offline",
    cp: "0812-XXXX-XXXX (Kak Dimas)",
    description: "Kumpulkan 5 roster terbaikmu dan kuasai Land of Dawn! Babak puncak akan disiarkan langsung dengan caster seru di venue utama.",
    requirements: [
      "5 Pemain Inti + 1 Cadangan (Opsional)",
      "Mode Custom Draft Pick 5v5 (No Cheat / Map Hack)",
      "Wajib hadir tepat waktu saat jadwal tanding"
    ],
    juknisUrl: "#juknis-mlbb",
    regUrl: "#daftar-mlbb"
  },
  market: {
    id: "market",
    title: "Market Day & Bazaar",
    category: "festival",
    badge: "🎪 Culinary & Merch",
    color: "#2F80D9",
    textColor: "#FFF5DA",
    coneColor: "#FFBF3E",
    fee: "Tenant Pelajar & Sponsor",
    prizePool: "Best Tenant Award",
    quota: "30+ Stand Kuliner & Kreatif",
    schedule: "08.00 - Selesai",
    cp: "0812-XXXX-XXXX (Panitia Bazaar)",
    description: "Surga jajanan hits, minuman segar, hingga official merchandise Letris Oddinary! Dukung kreasi wirausaha muda sambil menikmati panggung hiburan.",
    requirements: [
      "Tersedia berbagai pembayaran (Tunai & QRIS)",
      "Promo bundling khusus pengunjung Letris Oddinary",
      "Area nongkrong nyaman dekat panggung utama"
    ],
    juknisUrl: "#denah-booth",
    regUrl: "#booking-tenant"
  }
};