<?php
/**
 * Data awal Letris Oddinary (dipakai install.php & upgrade.php).
 * Setelah terpasang, semua data dikelola lewat CMS.
 */
return [
    'settings' => [
        'event_name'   => 'LETRIS ODDINARY',
        'tagline'      => 'Are YOU Ready? Turn On Your Odd Side!',
        'status'       => 'coming_soon', // coming_soon | open | closed
        'target_date'  => '2026-12-04T07:00',
        'venue'        => 'Segera diumumkan',
        'period_text'  => '4 - 10 Desember 2026',
        'marquee'      => "LETRIS ODDINARY | ARE YOU READY? | LETRIS CUP FUTSAL | LENTERA ABADI 2.0 | MLBB RISING STARS 1.0 | DKVEST 3.0 | LET'STAR PHORIA NUSANTARA | PENTAS SENI | 4 - 10 DESEMBER 2026",
        'contact_wa'   => '6281200000000',
        'instagram'    => 'https://instagram.com/letrispamulangofficial',
        'footer_text'  => 'Letris Oddinary - Turn On Your Odd Side.',
        'closed_msg'   => 'Pendaftaran belum dibuka. Pantau terus informasi terbaru!',
        'splash'       => '1',
    ],

    // kolom: title, category, badge, color, text_color, description, fee, prize, quota_num, quota_text,
    //        schedule_text, event_date, contact, requirements, team_label, contact_label, phone_label, email_required, poster_tile
    'events' => [
        'futsal' => ['Letris Cup Futsal Putra & Putri', 'lomba', 'Letris Cup - Sports', '#34D8A6', '#1a1438',
            'Tunjukkan sportivitas, taktik cepat, dan atmosfer suporter paling meriah di Letris Cup Futsal kategori Putra dan Putri!',
            'Rp 350.000 / Tim', 'Piala + Uang Pembinaan', 32, '32 Tim', '4 - 6 Desember 2026', '2026-12-04',
            '0812-XXXX-XXXX (Panitia Futsal)',
            "Kategori Putra & Putri\nPelajar aktif (dibuktikan Kartu Pelajar)\n1 tim maksimal 10 pemain + 2 official\nWajib jersey bernomor punggung & deker",
            'Nama Tim', 'Nama Penanggung Jawab', 'No. WhatsApp', 1, 'tile1'],
        'tari' => ['Lentera Abadi 2.0', 'lomba', 'Letris Cup Tari Tradisional', '#F4819D', '#1a1438',
            'Lomba Tari Tradisional tingkat SD/SMP/SMA. Padukan keanggunan gerak tari Nusantara dengan semangat muda di panggung utama.',
            'Rp 200.000 / Tim', 'Uang Pembinaan + Plakat + Sertifikat', 20, '20 Tim Tari', 'Letris Cup Tatra SD/SMP/SMA - Edu eXPO', '2026-12-09',
            '0812-XXXX-XXXX (Panitia Tari)',
            "Terbuka untuk SD / SMP / SMA\nBeregu 3 - 7 penari\nDurasi penampilan 5 - 7 menit\nMusik pengiring (MP3) dikumpulkan H-3 acara",
            'Nama Sanggar / Tim', 'Nama Guru Pembimbing', 'No. WhatsApp Guru Pembimbing', 1, 'tile2'],
        'mlbb' => ['MLBB Rising Stars 1.0', 'lomba', 'Letris Cup E-Sport SMP', '#695BD0', '#FFF5DA',
            'Turnamen Mobile Legends: Bang Bang tingkat SMP/MTs. Kumpulkan 5 roster terbaik sekolahmu dan kuasai Land of Dawn!',
            'Rp 100.000 / Tim', 'Uang Tunai + E-Certificate', 64, '64 Tim', 'Letris Cup E-Sport SMP', '2026-12-08',
            '0812-XXXX-XXXX (Panitia MLBB)',
            "Khusus siswa SMP / MTs, 1 tim mewakili 1 sekolah\n5 roster inti + 1 cadangan (opsional)\nWajib follow Instagram @redbox.letris2 dan @letrispamulangofficial\nSurat keterangan siswa aktif / biodata rapor / kartu pelajar setiap peserta\nBerita transfer: MRS1_Nama Sekolah SMP/MTs",
            '', 'Nama Lengkap Guru Pembimbing', 'No. WhatsApp Aktif Guru Pembimbing', 0, 'tile3'],
        'dkvest' => ['DKVest 3.0 Art Exhibition & Film Festival', 'festival', 'Letris Expo 2026', '#F6E37A', '#1a1438',
            'Pameran karya seni visual dan festival film dari talenta muda DKV: ilustrasi, fotografi, instalasi seni, film pendek, dan live mural - bersama Expo Jurusan.',
            'Gratis (terbuka umum)', 'Voting Karya Terfavorit & Art Merch', 100, '100+ Karya', 'Letris Expo 2026 (DKVest & Expo Jurusan)', '2026-12-07',
            '0812-XXXX-XXXX (Tim DKVest)',
            "Karya digital & fisik terbuka untuk pelajar\nPemutaran film pendek & photobooth tematik\nVoting pengunjung untuk Best Artwork",
            'Nama Kreator / Kolektif', 'Nama Penanggung Jawab', 'No. WhatsApp', 1, 'tile4'],
        'market' => ["Let'star Phoria Nusantara", 'festival', 'Market Day', '#2F80D9', '#FFF5DA',
            'Market Day bernuansa Nusantara: jajanan hits, minuman segar, official merchandise, dan kreasi wirausaha muda.',
            'Biaya tenant mengikuti paket', 'Best Tenant Award', 30, '30+ Stand', 'Jadwal menyusul', null,
            '0812-XXXX-XXXX (Panitia Market Day)',
            "Pembayaran tunai & QRIS\nPromo khusus pengunjung Letris Oddinary\nArea santai dekat panggung utama",
            'Nama Brand / Usaha', 'Nama Penanggung Jawab', 'No. WhatsApp', 1, 'tile5'],
        'pentas' => ['Pentas Seni Letris Oddinary', 'penutup', 'Grand Closing', '#F0BB49', '#1a1438',
            'Malam puncak penutup Letris Oddinary: penampilan musik, tari, teater, dan pengumuman juara seluruh lomba!',
            'Gratis (terbuka umum)', 'Penampilan Terbaik & Pengumuman Juara', 20, 'Slot terbatas', 'Penutup rangkaian acara', '2026-12-10',
            '0812-XXXX-XXXX (Panitia Pentas Seni)',
            "Pengisi acara: musik, tari, teater, stand-up, dll\nDurasi tampil menyesuaikan rundown\nSoundcheck dijadwalkan panitia",
            'Nama Penampil / Grup', 'Nama Penanggung Jawab', 'No. WhatsApp', 1, ''],
    ],

    // kolom: field_key, label, type, options, required, placeholder, help
    'fields' => [
        'futsal' => [
            ['kategori', 'Kategori', 'select', "Putra\nPutri", 1, '', ''],
            ['jumlah_pemain', 'Jumlah pemain + official', 'number', null, 1, '', ''],
            ['kartu_pelajar', 'Scan kartu pelajar ketua tim', 'file', null, 1, '', ''],
            ['bukti_bayar', 'Bukti pembayaran', 'file', null, 1, '', ''],
        ],
        'tari' => [
            ['jenjang', 'Jenjang', 'select', "SD\nSMP\nSMA/SMK", 1, '', ''],
            ['judul_tari', 'Judul / nama tarian', 'text', null, 1, '', ''],
            ['daerah', 'Asal daerah tarian', 'text', null, 1, '', ''],
            ['jumlah_penari', 'Jumlah penari', 'number', null, 1, '', ''],
            ['bukti_bayar', 'Bukti pembayaran', 'file', null, 1, '', ''],
        ],
        'mlbb' => (function () {
            $id = 'contoh: 12345678 (1234)';
            $f = [
                ['logo_sekolah', 'Logo Sekolah', 'file', null, 1, '', 'Format JPG / PNG.'],
                ['h_kapten', 'Kontak Kapten', 'heading', null, 0, '', ''],
                ['kapten_nama', 'Nama Lengkap Kapten', 'text', null, 1, '', ''],
                ['kapten_wa', 'Nomor WhatsApp Aktif Kapten', 'tel', null, 1, '08xxxxxxxxxx', ''],
                ['kapten_ign', 'Nickname Game (IGN) Kapten', 'text', null, 1, '', ''],
                ['kapten_id', 'User ID & Server ID Kapten', 'text', null, 1, $id, ''],
            ];
            foreach ([2, 3, 4, 5] as $n) {
                $f[] = ["h_roster$n", "Data Roster $n", 'heading', null, 0, '', ''];
                $f[] = ["roster{$n}_nama", "Nama Lengkap Roster $n", 'text', null, 1, '', ''];
                $f[] = ["roster{$n}_ign", "Nickname Game (IGN) Roster $n", 'text', null, 1, '', ''];
                $f[] = ["roster{$n}_id", "User ID & Server ID Roster $n", 'text', null, 1, $id, ''];
            }
            array_push($f,
                ['h_cadangan', 'Data Cadangan (opsional)', 'heading', null, 0, '', ''],
                ['cadangan_nama', 'Nama Lengkap Cadangan', 'text', null, 0, '', ''],
                ['cadangan_ign', 'Nickname Game (IGN) Cadangan', 'text', null, 0, '', ''],
                ['cadangan_id', 'User ID & Server ID Cadangan', 'text', null, 0, $id, ''],
                ['h_admin', 'Administrasi Turnamen', 'heading', null, 0, '', ''],
                ['bukti_follow', 'Screenshot bukti follow @redbox.letris2 dan @letrispamulangofficial', 'file', null, 1, '', 'Gabungkan kedua screenshot dalam 1 gambar atau 1 PDF.'],
                ['surat_siswa', 'Surat Keterangan Siswa Aktif / Biodata Rapor / Kartu Pelajar seluruh peserta', 'file', null, 1, '', 'Gabungkan dokumen semua peserta menjadi 1 file PDF.'],
                ['bukti_bayar', 'Bukti Transfer / Pembayaran Pendaftaran', 'file', null, 1, '', 'Cantumkan berita transfer: MRS1_Nama Sekolah SMP/MTs'],
            );
            return $f;
        })(),
        'dkvest' => [
            ['judul_karya', 'Judul karya', 'text', null, 1, '', ''],
            ['kategori', 'Kategori karya', 'select', "Ilustrasi\nFotografi\nInstalasi Seni\nFilm Pendek\nLive Mural\nLainnya", 1, '', ''],
            ['deskripsi', 'Deskripsi singkat karya', 'textarea', null, 1, '', ''],
            ['file_karya', 'File / preview karya (JPG/PNG/PDF)', 'file', null, 0, '', ''],
        ],
        'market' => [
            ['produk', 'Jenis produk', 'select', "Makanan\nMinuman\nMerchandise\nKerajinan\nLainnya", 1, '', ''],
            ['listrik', 'Butuh listrik?', 'select', "Tidak\nYa", 1, '', ''],
            ['deskripsi', 'Deskripsi produk yang dijual', 'textarea', null, 1, '', ''],
        ],
        'pentas' => [
            ['jenis', 'Jenis penampilan', 'select', "Musik\nTari\nTeater\nStand-up\nLainnya", 1, '', ''],
            ['judul', 'Judul / nama penampilan', 'text', null, 1, '', ''],
            ['personil', 'Jumlah personil', 'number', null, 1, '', ''],
            ['durasi', 'Durasi (menit)', 'number', null, 1, '', ''],
        ],
    ],

    // kolom: tanggal, jam, judul, keterangan, slug arena
    'schedule' => [
        ['2026-12-04', '', 'Letris Cup Futsal Putra & Putri', 'Hari 1', 'futsal'],
        ['2026-12-05', '', 'Letris Cup Futsal Putra & Putri', 'Hari 2', 'futsal'],
        ['2026-12-06', '', 'Letris Cup Futsal Putra & Putri', 'Hari 3', 'futsal'],
        ['2026-12-07', '', 'Letris Expo 2026', 'DKVest 3.0 Art Exhibition & Film Festival dan Expo Jurusan', 'dkvest'],
        ['2026-12-08', '', 'MLBB Rising Stars 1.0', 'Letris Cup E-Sport SMP - Class Meeting Kelas X, XI, XII', 'mlbb'],
        ['2026-12-09', '', 'Lentera Abadi 2.0', 'Letris Cup Tari Tradisional SD/SMP/SMA - Edu eXPO', 'tari'],
        ['2026-12-10', '', 'Pentas Seni Letris Oddinary', 'Grand closing & pengumuman juara', 'pentas'],
    ],
];
