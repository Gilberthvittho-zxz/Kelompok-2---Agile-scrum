/**
 * MOTOKU - Generator Dokumen Presentasi
 * Run: node generate-docx.js
 */
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, PageOrientation, LevelFormat,
  TabStopType, TabStopPosition, TableOfContents, HeadingLevel,
  BorderStyle, WidthType, ShadingType, PageNumber, PageBreak,
} = require('docx');
const fs = require('fs');

const A4_W = 11906;
const A4_H = 16838;
const MARGIN = 1080; // 0.75 inch
const CONTENT_W = A4_W - MARGIN * 2; // 9746

// ===== Helpers =====
const p = (text, opts = {}) => new Paragraph({
  children: Array.isArray(text)
    ? text
    : [new TextRun({ text, size: 22, ...(opts.run || {}) })],
  spacing: { after: 120, ...(opts.spacing || {}) },
  alignment: opts.alignment,
  shading: opts.shading,
});

const h1 = (text, pageBreak = true) => new Paragraph({
  heading: HeadingLevel.HEADING_1,
  children: [new TextRun({ text, bold: true, size: 36, color: "1F2937" })],
  spacing: { before: 240, after: 240 },
  pageBreakBefore: pageBreak,
});

const h2 = (text) => new Paragraph({
  heading: HeadingLevel.HEADING_2,
  children: [new TextRun({ text, bold: true, size: 28, color: "1F2937" })],
  spacing: { before: 320, after: 160 },
});

const h3 = (text) => new Paragraph({
  heading: HeadingLevel.HEADING_3,
  children: [new TextRun({ text, bold: true, size: 24, color: "374151" })],
  spacing: { before: 240, after: 120 },
});

const bullet = (text, level = 0) => new Paragraph({
  numbering: { reference: "bullets", level },
  children: Array.isArray(text) ? text : [new TextRun({ text, size: 22 })],
  spacing: { after: 60 },
});

const num = (text, level = 0) => new Paragraph({
  numbering: { reference: "numbers", level },
  children: Array.isArray(text) ? text : [new TextRun({ text, size: 22 })],
  spacing: { after: 60 },
});

const codeBlock = (code) => {
  const lines = code.split('\n');
  return lines.map((line, i) => new Paragraph({
    children: [new TextRun({ text: line || ' ', font: 'Consolas', size: 18, color: "1F2937" })],
    shading: { fill: "F3F4F6", type: ShadingType.CLEAR },
    spacing: { line: 240, after: 0, before: 0 },
    indent: { left: 144 },
    border: i === 0
      ? { top: { style: BorderStyle.SINGLE, size: 4, color: "9CA3AF" } }
      : (i === lines.length - 1
          ? { bottom: { style: BorderStyle.SINGLE, size: 4, color: "9CA3AF" } }
          : undefined),
  }));
};

const inlineCode = (text) => new TextRun({ text, font: 'Consolas', size: 20, color: "BE185D" });

const bold = (text) => new TextRun({ text, bold: true, size: 22 });
const plain = (text) => new TextRun({ text, size: 22 });

const makeTable = (rows, colWidths, opts = {}) => {
  const cw = colWidths || rows[0].map(() => Math.floor(CONTENT_W / rows[0].length));
  const sum = cw.reduce((a, b) => a + b, 0);
  // Ensure cw sums to total
  const adjustedCw = [...cw];
  if (sum !== CONTENT_W) adjustedCw[adjustedCw.length - 1] += (CONTENT_W - sum);

  const border = { style: BorderStyle.SINGLE, size: 4, color: "D1D5DB" };
  const borders = { top: border, bottom: border, left: border, right: border };

  return new Table({
    width: { size: CONTENT_W, type: WidthType.DXA },
    columnWidths: adjustedCw,
    rows: rows.map((row, rIdx) => new TableRow({
      tableHeader: rIdx === 0 && !opts.noHeader,
      children: row.map((cell, cIdx) => {
        const isHeader = rIdx === 0 && !opts.noHeader;
        return new TableCell({
          borders,
          width: { size: adjustedCw[cIdx], type: WidthType.DXA },
          shading: isHeader
            ? { fill: "1F2937", type: ShadingType.CLEAR }
            : (rIdx % 2 === 0 ? { fill: "F9FAFB", type: ShadingType.CLEAR } : undefined),
          margins: { top: 80, bottom: 80, left: 120, right: 120 },
          children: [new Paragraph({
            children: [new TextRun({
              text: cell,
              bold: isHeader,
              color: isHeader ? "FFFFFF" : "111827",
              size: 20,
            })],
            spacing: { after: 0 },
          })],
        });
      }),
    })),
  });
};

const spacer = () => new Paragraph({ children: [new TextRun(' ')], spacing: { after: 0 } });

// ===== Content =====
const content = [];

// COVER
content.push(
  new Paragraph({ children: [new TextRun(' ')], spacing: { before: 2400 } }),
  new Paragraph({
    children: [new TextRun({ text: "MOTOKU", bold: true, size: 96, color: "1F2937" })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 240 },
  }),
  new Paragraph({
    children: [new TextRun({ text: "Sistem Manajemen Inventori Sparepart Motor", size: 32, color: "4B5563" })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 480 },
  }),
  new Paragraph({
    children: [new TextRun({ text: "Dokumentasi Pengembangan Sprint 1 & Sprint 2", size: 26, italics: true, color: "6B7280" })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 1200 },
  }),
  new Paragraph({
    children: [new TextRun({ text: "Disusun untuk Presentasi Tugas Agile Software Development", size: 22, color: "374151" })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 120 },
  }),
  new Paragraph({
    children: [new TextRun({ text: "Tim Kelompok 2 — Metodologi Scrum", size: 22, color: "374151" })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 2400 },
  }),
  new Paragraph({
    children: [new TextRun({ text: `Tanggal: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`, size: 22, color: "6B7280" })],
    alignment: AlignmentType.CENTER,
  }),
);

// DAFTAR ISI
content.push(
  h1("Daftar Isi"),
  new TableOfContents("Klik kanan -> Update Field untuk refresh daftar isi", {
    hyperlink: true,
    headingStyleRange: "1-3",
  }),
);

// =================================================================
// BAB 1 — PENDAHULUAN
// =================================================================
content.push(h1("BAB 1 — Pendahuluan"));

content.push(h2("1.1 Latar Belakang"));
content.push(p("Banyak pemilik bengkel dan toko sparepart motor masih mengandalkan pencatatan manual menggunakan buku atau Microsoft Excel. Metode ini rentan terhadap human error, tidak efisien saat melayani pelanggan, dan tidak mampu memberikan gambaran bisnis secara real-time."));
content.push(p("Sistem MOTOKU hadir sebagai solusi manajemen inventori sparepart motor berbasis web yang mengintegrasikan penjualan, pembelian dari supplier, pengelolaan stok, serta laporan bisnis dalam satu platform terpadu. Pemilik usaha dapat memantau bisnis dari mana saja melalui browser tanpa instalasi software tambahan."));

content.push(h2("1.2 Nilai Bisnis"));
content.push(h3("Tangible (Manfaat Terukur)"));
content.push(bullet("Mengurangi kerugian akibat salah catat stok."));
content.push(bullet("Mempercepat proses transaksi (tidak perlu hitung manual)."));
content.push(bullet("Mengurangi kebutuhan tenaga administrasi."));
content.push(h3("Intangible (Manfaat Tidak Terukur)"));
content.push(bullet("Meningkatkan kepercayaan pelanggan karena pelayanan lebih cepat dan akurat."));
content.push(bullet("Membantu pengambilan keputusan berbasis data — produk terlaris, stok menipis, supplier paling aktif."));
content.push(bullet("Meningkatkan citra profesionalisme usaha."));

content.push(h2("1.3 Pengguna Utama"));
content.push(p("Sistem MOTOKU dirancang untuk satu pengguna utama yaitu Pemilik Usaha, yang memegang kendali penuh atas seluruh fitur — dari pengelolaan data master, transaksi harian, hingga melihat laporan dan dashboard."));

content.push(h2("1.4 Tujuan & Ruang Lingkup Dokumen"));
content.push(p("Dokumen ini berisi dokumentasi pengembangan MOTOKU yang dibuat menggunakan metodologi Scrum. Cakupan dokumen mencakup:"));
content.push(num("Penjelasan konsep Scrum dan Sprint."));
content.push(num("User Stories dan Product Backlog lengkap."));
content.push(num("Sprint Planning untuk Sprint 1 dan Sprint 2."));
content.push(num("Tahapan coding dan development secara detail."));
content.push(num("Snippet kode penting beserta penjelasannya."));
content.push(num("Sprint Review & rencana sprint berikutnya."));

// =================================================================
// BAB 2 — METODOLOGI SCRUM
// =================================================================
content.push(h1("BAB 2 — Metodologi Scrum & Agile"));

content.push(h2("2.1 Apa itu Agile?"));
content.push(p("Agile Software Development adalah pendekatan pengembangan perangkat lunak yang menekankan kolaborasi tim, respons cepat terhadap perubahan, dan delivery produk secara iteratif/bertahap. Berbeda dengan model Waterfall yang merencanakan semuanya di awal lalu mengeksekusi linear, Agile bekerja dalam siklus pendek (iterasi) yang berulang."));

content.push(h3("Empat Nilai Manifesto Agile"));
content.push(num("Individu dan interaksi lebih penting daripada proses dan tools."));
content.push(num("Software yang berfungsi lebih penting daripada dokumentasi yang lengkap."));
content.push(num("Kolaborasi dengan customer lebih penting daripada negosiasi kontrak."));
content.push(num("Respons terhadap perubahan lebih penting daripada mengikuti rencana kaku."));

content.push(h2("2.2 Apa itu Scrum?"));
content.push(p("Scrum adalah salah satu framework Agile yang paling populer. Scrum membagi pekerjaan ke dalam siklus pendek bernama Sprint (biasanya 1-4 minggu). Setiap akhir Sprint, tim menghasilkan increment produk yang dapat digunakan."));
content.push(p("Scrum terdiri dari 3 elemen utama: Roles (peran), Events (kegiatan), dan Artifacts (artefak)."));

content.push(h2("2.3 Roles (Peran dalam Scrum)"));
content.push(makeTable([
  ["Peran", "Tanggung Jawab"],
  ["Product Owner", "Memprioritaskan Product Backlog, mewakili kepentingan pengguna/stakeholder, memutuskan apa yang dibangun."],
  ["Scrum Master", "Memfasilitasi proses Scrum, menghilangkan hambatan tim, memastikan ceremonies berjalan sesuai aturan."],
  ["Development Team", "Tim cross-functional yang mengerjakan teknis: analisis, desain, coding, testing. Dalam MOTOKU: D1, D2, D3, D4."],
], [2200, 7546]));

content.push(h2("2.4 Events / Ceremonies (4 Kegiatan Inti)"));

content.push(h3("Sprint Planning"));
content.push(p("Pertemuan di awal Sprint untuk menentukan tujuan Sprint (Sprint Goal) dan memilih PBI dari Product Backlog yang akan dikerjakan. Output: Sprint Backlog."));

content.push(h3("Daily Standup (Daily Scrum)"));
content.push(p("Pertemuan singkat (15 menit) setiap hari kerja. Tiap anggota menjawab 3 pertanyaan:"));
content.push(bullet("Apa yang sudah saya kerjakan kemarin?"));
content.push(bullet("Apa yang akan saya kerjakan hari ini?"));
content.push(bullet("Adakah hambatan (impediment)?"));

content.push(h3("Sprint Review"));
content.push(p("Di akhir Sprint, tim mendemo hasil kerja kepada Product Owner dan stakeholder. Tujuannya mendapatkan feedback dan validasi apakah increment sudah sesuai harapan."));

content.push(h3("Sprint Retrospective"));
content.push(p("Pertemuan setelah Sprint Review yang fokus pada peningkatan proses. Tim mendiskusikan: What went well? What went wrong? What can we improve?"));

content.push(h2("2.5 Artifacts (3 Artefak)"));
content.push(makeTable([
  ["Artefak", "Deskripsi"],
  ["Product Backlog", "Daftar prioritas semua fitur/PBI yang ingin dibangun. Dikelola Product Owner. Bersifat dinamis — bisa ditambah/dihapus seiring waktu."],
  ["Sprint Backlog", "Subset Product Backlog yang dipilih untuk Sprint berjalan. Berisi PBI + task-task yang dipecah oleh Development Team."],
  ["Increment", "Hasil akhir Sprint — software/produk yang berfungsi dan memenuhi Definition of Done."],
], [2200, 7546]));

content.push(h2("2.6 Sprint & Timebox"));
content.push(p("Sprint adalah kotak waktu (timebox) tetap untuk mengerjakan sekumpulan PBI. Setiap Sprint memiliki:"));
content.push(bullet("Sprint Goal — tujuan tunggal yang merangkum apa yang akan dicapai."));
content.push(bullet("Sprint Backlog — daftar PBI dan task yang dipilih."));
content.push(bullet("Increment — produk yang dapat di-demo di akhir Sprint."));
content.push(p("Pada proyek MOTOKU, durasi 1 Sprint = 7-8 hari kerja, dengan kapasitas tim 120 jam (4 orang × 10 hari × 3 jam/hari)."));

content.push(h2("2.7 Story Points & Estimasi"));
content.push(p("Story Points (SP) adalah satuan estimasi relatif untuk mengukur kompleksitas PBI, bukan waktu. Skala umum: Fibonacci-like (1, 3, 5, 8, 13)."));
content.push(makeTable([
  ["SP", "Tingkat Kesulitan", "Contoh"],
  ["1", "Trivial — sangat mudah", "Ubah teks label, tambah field opsional."],
  ["3", "Easy — mudah, langsung kerjakan", "CRUD sederhana 1 tabel tanpa relasi."],
  ["5", "Medium — perlu sedikit pemikiran", "CRUD dengan validasi kompleks dan relasi."],
  ["8", "Hard — kompleks, banyak komponen", "Transaksi multi-tabel dengan logic stok otomatis."],
  ["13", "Very Hard — sangat kompleks", "Modul integrasi pihak ketiga (payment gateway, dll)."],
], [1000, 3000, 5746]));

// =================================================================
// BAB 3 — PERENCANAAN PRODUK
// =================================================================
content.push(h1("BAB 3 — Perencanaan Produk"));

content.push(h2("3.1 User Stories Lengkap (US-01 sd US-15)"));
content.push(p("User story ditulis dalam format: 'Sebagai [peran], saya ingin [kebutuhan] agar [manfaat].'"));
content.push(makeTable([
  ["ID", "Modul", "User Story"],
  ["US-01", "Auth", "Login ke sistem agar data bisnis aman dari akses pihak luar."],
  ["US-02", "Kategori", "Menambah, mengedit, dan menghapus kategori sparepart agar produk lebih terorganisir."],
  ["US-03", "Supplier", "Menyimpan data supplier beserta kontak dan alamat agar mudah dihubungi saat restock."],
  ["US-04", "Produk", "Menambah produk sparepart dengan harga jual, harga beli, stok awal, gambar, dan kategori."],
  ["US-05", "Produk", "Mengedit data produk dan melakukan soft-delete agar histori tetap terjaga."],
  ["US-06", "Stok", "Melihat sisa stok semua produk secara real-time dengan filter per kategori."],
  ["US-07", "Penjualan", "Melakukan transaksi penjualan; stok otomatis berkurang."],
  ["US-08", "Penjualan", "Memilih metode pembayaran (tunai/transfer/QRIS) di setiap transaksi."],
  ["US-09", "Penjualan", "Membatalkan (void) transaksi penjualan; stok otomatis dikembalikan."],
  ["US-10", "Pembelian", "Mencatat pembelian sparepart dari supplier; stok otomatis bertambah."],
  ["US-11", "Pembelian", "Membatalkan (void) pembelian agar stok tidak salah hitung."],
  ["US-12", "Stok", "Memodifikasi stok manual dengan alasan untuk kondisi di luar transaksi normal."],
  ["US-13", "Dashboard", "Melihat dashboard total penjualan hari ini, produk terlaris, peringatan stok menipis."],
  ["US-14", "Laporan", "Melihat laporan penjualan dan pembelian berdasarkan filter rentang tanggal."],
  ["US-15", "Laporan", "Melihat laporan sisa stok dengan filter kategori untuk evaluasi inventori."],
], [900, 1500, 7346]));

content.push(h2("3.2 Product Backlog (14 PBI)"));
content.push(makeTable([
  ["ID", "Judul PBI", "User Story", "Prio", "SP", "Sprint"],
  ["PBI-01", "Autentikasi Login/Logout", "US-01", "High", "3", "1"],
  ["PBI-02", "CRUD Kategori Produk", "US-02", "High", "3", "1"],
  ["PBI-03", "CRUD Supplier", "US-03", "High", "3", "1"],
  ["PBI-04", "CRUD Produk Sparepart", "US-04, US-05", "High", "5", "2"],
  ["PBI-05", "Lihat Sisa Stok Real-time", "US-06", "High", "3", "2"],
  ["PBI-06", "Transaksi Penjualan + Auto-deduct Stok", "US-07", "High", "8", "3"],
  ["PBI-07", "Pilih Metode Pembayaran", "US-08", "High", "3", "3"],
  ["PBI-08", "Void Transaksi Penjualan", "US-09", "Medium", "5", "3-4"],
  ["PBI-09", "Transaksi Pembelian + Auto-add Stok", "US-10", "High", "8", "4"],
  ["PBI-10", "Void Pembelian", "US-11", "Medium", "5", "4"],
  ["PBI-11", "Stock Adjustment Manual", "US-12", "Medium", "5", "4-5"],
  ["PBI-12", "Dashboard Penjualan", "US-13", "Medium", "8", "5"],
  ["PBI-13", "Laporan Penjualan & Pembelian", "US-14", "Medium", "5", "5"],
  ["PBI-14", "Laporan Stok", "US-15", "Low", "5", "5"],
], [800, 2800, 1700, 800, 600, 800]));
content.push(p([bold("Total Story Points: 69 SP")]));

content.push(h2("3.3 Definition of Done (DoD)"));
content.push(p("Sebuah PBI dianggap 'Done' jika memenuhi semua kriteria berikut:"));
content.push(bullet("Kode sudah ditulis dan di-commit ke Git."));
content.push(bullet("Validasi server-side berfungsi untuk semua input."));
content.push(bullet("Fitur dapat di-akses melalui browser tanpa error."));
content.push(bullet("Sudah di-test secara manual untuk happy path dan error case."));
content.push(bullet("Tidak ada data sensitif (password, key) di kode atau commit."));
content.push(bullet("Migration database sudah berjalan tanpa error."));

content.push(h2("3.4 Sprint 1 — Sprint Planning"));
content.push(p([bold("Sprint Goal: "), plain("Pemilik usaha dapat login ke sistem dan mengelola seluruh data master awal (kategori dan supplier) sebagai fondasi sebelum data produk dimasukkan.")]));
content.push(p([bold("PBI: "), plain("PBI-01, PBI-02, PBI-03 — Total 9 SP")]));
content.push(p([bold("Durasi: "), plain("30 April — 7 Mei")]));
content.push(makeTable([
  ["Task", "PBI", "PIC", "Est. Jam"],
  ["Setup Laravel + struktur folder + Git", "—", "D4", "4j"],
  ["Migration users + seeder admin", "PBI-01", "D1", "2j"],
  ["Form login + validasi server-side", "PBI-01", "D1", "3j"],
  ["Session + middleware auth", "PBI-01", "D1", "2j"],
  ["View halaman login (Blade)", "PBI-01", "D2", "2j"],
  ["Migration & model Category + soft delete", "PBI-02", "D3", "2j"],
  ["Controller CRUD kategori + routes", "PBI-02", "D3", "3j"],
  ["View tabel + form kategori", "PBI-02", "D2", "4j"],
  ["Migration & model Supplier + soft delete", "PBI-03", "D3", "2j"],
  ["Controller CRUD supplier + routes", "PBI-03", "D3", "3j"],
  ["View tabel + form supplier", "PBI-03", "D2", "4j"],
  ["Testing & review Sprint 1", "—", "D4", "6j"],
], [5500, 1300, 1000, 1946]));
content.push(p([bold("Total estimasi: 37 jam")]));

content.push(h2("3.5 Sprint 2 — Sprint Planning"));
content.push(p([bold("Sprint Goal: "), plain("Pemilik usaha dapat menambah dan mengelola produk sparepart secara lengkap, serta memantau sisa stok semua produk secara real-time.")]));
content.push(p([bold("PBI: "), plain("PBI-04, PBI-05 — Total 8 SP")]));
content.push(p([bold("Durasi: "), plain("8 Mei — 14 Mei")]));
content.push(makeTable([
  ["Task", "PBI", "PIC", "Est. Jam"],
  ["Migration & model Product (FK + soft delete)", "PBI-04", "D1", "3j"],
  ["Controller CRUD produk + routes", "PBI-04", "D1", "4j"],
  ["Logic upload & simpan gambar", "PBI-04", "D1", "2j"],
  ["View list produk", "PBI-04", "D2", "5j"],
  ["View form tambah/edit produk", "PBI-04", "D2", "4j"],
  ["Konfirmasi soft-delete produk", "PBI-04", "D2", "2j"],
  ["View halaman stok real-time", "PBI-05", "D2", "3j"],
  ["Filter stok by kategori", "PBI-05", "D3", "2j"],
  ["Badge/highlight stok di bawah threshold", "PBI-05", "D3", "2j"],
  ["Testing & review Sprint 2", "—", "D4", "5j"],
], [5500, 1300, 1000, 1946]));
content.push(p([bold("Total estimasi: 32 jam")]));

// =================================================================
// BAB 4 — SETUP PROJECT
// =================================================================
content.push(h1("BAB 4 — Setup Project"));

content.push(h2("4.1 Tools & Environment"));
content.push(makeTable([
  ["Tool", "Versi", "Fungsi"],
  ["Laragon", "Latest (Full)", "Local development stack — bundling Apache, MySQL, PHP."],
  ["PHP", "8.3.14", "Bahasa server-side untuk Laravel."],
  ["Composer", "2.8.9", "Package manager PHP."],
  ["MySQL", "8.4.3", "Database relasional."],
  ["Laravel", "13.9.0", "Framework PHP utama."],
  ["Bootstrap", "5.3.3 (CDN)", "Framework CSS untuk UI."],
  ["Bootstrap Icons", "1.11.3 (CDN)", "Library icon vector."],
  ["Git", "2.49.0", "Version control."],
], [2500, 2500, 4746]));

content.push(h2("4.2 Install Laravel"));
content.push(p("Step pertama adalah create project Laravel baru di direktori web Laragon."));
content.push(...codeBlock(`cd C:\\laragon\\www
composer create-project --prefer-dist laravel/laravel motoku`));
content.push(p("Setelah selesai, direktori motoku akan berisi skeleton Laravel lengkap dengan struktur folder app/, config/, database/, dll."));

content.push(h2("4.3 Konfigurasi .env"));
content.push(p("File .env adalah konfigurasi environment. Buka dan edit beberapa baris berikut:"));
content.push(...codeBlock(`APP_NAME=MOTOKU
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motoku_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync`));

content.push(h2("4.4 Buat Database"));
content.push(p("Buat database MySQL menggunakan command line atau phpMyAdmin (yang tersedia di Laragon)."));
content.push(...codeBlock(`CREATE DATABASE motoku_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;`));

content.push(h2("4.5 Struktur Folder Laravel"));
content.push(p("Folder penting di Laravel yang akan kita pakai:"));
content.push(bullet([inlineCode('app/Models/'), plain(" — file Eloquent model (Category, Supplier, Product, dll).")]));
content.push(bullet([inlineCode('app/Http/Controllers/'), plain(" — controller untuk handle request HTTP.")]));
content.push(bullet([inlineCode('database/migrations/'), plain(" — file migration untuk schema database.")]));
content.push(bullet([inlineCode('database/seeders/'), plain(" — file seeder untuk data dummy/awal.")]));
content.push(bullet([inlineCode('resources/views/'), plain(" — template Blade untuk UI.")]));
content.push(bullet([inlineCode('routes/web.php'), plain(" — definisi route URL.")]));
content.push(bullet([inlineCode('public/'), plain(" — folder root web (CSS, JS, gambar publik).")]));
content.push(bullet([inlineCode('storage/app/public/'), plain(" — file upload (perlu storage:link).")]));

// =================================================================
// BAB 5 — SPRINT 1
// =================================================================
content.push(h1("BAB 5 — Sprint 1 Development"));

content.push(h2("5.1 Sprint 1 Goal & Scope"));
content.push(p([bold("Goal: "), plain("Pemilik usaha dapat login ke sistem dan mengelola seluruh data master awal (kategori dan supplier).")]));
content.push(p([bold("Scope: "), plain("PBI-01 Auth, PBI-02 Kategori, PBI-03 Supplier")]));

// PBI-01
content.push(h2("5.2 PBI-01 — Autentikasi Login/Logout"));
content.push(p([bold("User Story: "), plain("Sebagai pemilik usaha, saya ingin login ke sistem agar data bisnis saya aman dari akses pihak luar.")]));

content.push(h3("Step 1: Migration users (default Laravel)"));
content.push(p("Laravel sudah menyediakan migration default untuk tabel users. Kita tidak perlu ubah apapun, cukup pastikan tabelnya ada:"));
content.push(...codeBlock(`Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});`));

content.push(h3("Step 2: Seeder Admin (database/seeders/DatabaseSeeder.php)"));
content.push(p("Buat akun admin default agar bisa login pertama kali:"));
content.push(...codeBlock(`use App\\Models\\User;
use Illuminate\\Support\\Facades\\Hash;

User::updateOrCreate(
    ['email' => 'admin@motoku.test'],
    [
        'name' => 'Pemilik Usaha',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]
);`));
content.push(p([bold("Penjelasan: "), plain("Hash::make() mengenkripsi password dengan algoritma bcrypt. updateOrCreate memastikan seeder bisa di-run berkali-kali tanpa error duplicate.")]));

content.push(h3("Step 3: LoginController (app/Http/Controllers/Auth/LoginController.php)"));
content.push(...codeBlock(`namespace App\\Http\\Controllers\\Auth;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Validation\\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}`));
content.push(p([bold("Penjelasan: "), plain("Auth::attempt() mencocokkan email+password di tabel users. session()->regenerate() mencegah session fixation attack. redirect()->intended() membawa user ke halaman yang tadinya dituju sebelum diminta login.")]));

content.push(h3("Step 4: View Login (resources/views/auth/login.blade.php)"));
content.push(...codeBlock(`<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="remember">
    <button type="submit">Login</button>
</form>`));
content.push(p([bold("Penjelasan: "), plain("@csrf adalah Blade directive yang generate CSRF token otomatis. Form tanpa @csrf akan ditolak Laravel dengan error 419.")]));

content.push(h3("Step 5: Routes (routes/web.php)"));
content.push(...codeBlock(`Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... route lain yang butuh login
});`));
content.push(p([bold("Penjelasan: "), plain("Middleware 'guest' = hanya boleh diakses jika belum login. Middleware 'auth' = hanya boleh diakses jika sudah login. Kalau user belum login akses /dashboard, akan otomatis redirect ke /login.")]));

content.push(h3("Step 6: Testing PBI-01"));
content.push(num("Jalankan migrate + seed: php artisan migrate --seed"));
content.push(num("Jalankan server: php artisan serve"));
content.push(num("Buka http://127.0.0.1:8000 → redirect ke /login"));
content.push(num("Login dengan admin@motoku.test / password → redirect ke dashboard"));
content.push(num("Logout → kembali ke /login"));

// PBI-02
content.push(h2("5.3 PBI-02 — CRUD Kategori"));
content.push(p([bold("User Story: "), plain("Sebagai pemilik usaha, saya ingin menambah, mengedit, dan menghapus kategori sparepart agar produk lebih terorganisir.")]));

content.push(h3("Step 1: Migration (database/migrations/...create_categories_table.php)"));
content.push(...codeBlock(`Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('description', 255)->nullable();
    $table->timestamps();
    $table->softDeletes();
});`));
content.push(p([bold("Penjelasan: "), plain("$table->softDeletes() menambah kolom deleted_at. Saat Eloquent delete(), Laravel hanya set kolom ini, tidak benar-benar hapus row → histori aman.")]));

content.push(h3("Step 2: Model (app/Models/Category.php)"));
content.push(...codeBlock(`namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Attributes\\Fillable;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

#[Fillable(['name', 'description'])]
class Category extends Model
{
    use SoftDeletes;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}`));
content.push(p([bold("Penjelasan: "), plain("#[Fillable] adalah PHP 8 attribute (Laravel 11+) yang mendefinisikan kolom mass-assignable. Trait SoftDeletes mengaktifkan fitur soft delete di model. Relasi products() menyatakan 1 kategori punya banyak produk.")]));

content.push(h3("Step 3: Controller (app/Http/Controllers/CategoryController.php)"));
content.push(...codeBlock(`public function index(Request $request)
{
    $categories = Category::query()
        ->withCount('products')
        ->orderBy('name')
        ->paginate(10);
    return view('categories.index', compact('categories'));
}

public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        'description' => ['nullable', 'string', 'max:255'],
    ]);
    Category::create($data);
    return redirect()->route('categories.index')
        ->with('success', 'Kategori berhasil ditambahkan.');
}

public function update(Request $request, Category $category)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100',
                   'unique:categories,name,'.$category->id],
        'description' => ['nullable', 'string', 'max:255'],
    ]);
    $category->update($data);
    return redirect()->route('categories.index')
        ->with('success', 'Kategori berhasil diperbarui.');
}

public function destroy(Category $category)
{
    if ($category->products()->exists()) {
        return back()->with('error',
            'Kategori tidak bisa dihapus karena masih memiliki produk.');
    }
    $category->delete();
    return redirect()->route('categories.index')
        ->with('success', 'Kategori berhasil dihapus.');
}`));
content.push(p([bold("Penjelasan: "), plain("withCount('products') menambahkan kolom products_count untuk tiap row tanpa N+1 query. Rule 'unique:categories,name,'.$category->id mengabaikan record yang sedang diedit. Guard di destroy() mencegah hapus kategori yang masih dipakai produk.")]));

content.push(h3("Step 4: Routes"));
content.push(...codeBlock(`Route::resource('categories', CategoryController::class);`));
content.push(p([bold("Penjelasan: "), plain("Satu baris ini otomatis generate 7 route: index (GET /), create (GET /create), store (POST /), show (GET /{id}), edit (GET /{id}/edit), update (PUT/PATCH /{id}), destroy (DELETE /{id}).")]));

content.push(h3("Step 5: View Index (resources/views/categories/index.blade.php)"));
content.push(...codeBlock(`<table class="table">
    <thead>
        <tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        @forelse ($categories as $cat)
            <tr>
                <td>{{ $cat->name }}</td>
                <td>{{ $cat->description }}</td>
                <td>
                    <a href="{{ route('categories.edit', $cat) }}">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $cat) }}">
                        @csrf @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3">Belum ada kategori.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $categories->links() }}`));

// PBI-03
content.push(h2("5.4 PBI-03 — CRUD Supplier"));
content.push(p([bold("User Story: "), plain("Sebagai pemilik usaha, saya ingin menyimpan data supplier beserta kontak dan alamat agar mudah dihubungi saat restock.")]));
content.push(p("Struktur PBI-03 mirip dengan PBI-02 namun dengan field lebih lengkap (nama, PIC, email, telepon, alamat)."));

content.push(h3("Migration Supplier"));
content.push(...codeBlock(`Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->string('name', 150);
    $table->string('contact_person', 100)->nullable();
    $table->string('email', 150)->nullable();
    $table->string('phone', 30)->nullable();
    $table->text('address')->nullable();
    $table->timestamps();
    $table->softDeletes();
});`));

content.push(h3("Model Supplier"));
content.push(...codeBlock(`#[Fillable(['name', 'contact_person', 'email', 'phone', 'address'])]
class Supplier extends Model
{
    use SoftDeletes;
    public function products(): HasMany {
        return $this->hasMany(Product::class);
    }
}`));

content.push(h3("Validation Rules"));
content.push(...codeBlock(`$request->validate([
    'name' => ['required', 'string', 'max:150'],
    'contact_person' => ['nullable', 'string', 'max:100'],
    'email' => ['nullable', 'email', 'max:150'],
    'phone' => ['nullable', 'string', 'max:30'],
    'address' => ['nullable', 'string', 'max:1000'],
]);`));
content.push(p([bold("Penjelasan: "), plain("Rule 'email' otomatis memvalidasi format email. Rule 'nullable' artinya field boleh kosong; jika diisi, baru rule lain (string, max, email) dievaluasi.")]));

content.push(h2("5.5 Sprint 1 Review & Hasil"));
content.push(p("Akhir Sprint 1, tim mendemo:"));
content.push(num("Halaman login berfungsi (admin@motoku.test / password)."));
content.push(num("Setelah login, redirect ke dashboard."));
content.push(num("Menu Kategori bisa tambah, edit, hapus dengan validasi unique."));
content.push(num("Menu Supplier bisa tambah, edit, hapus dengan validasi email."));
content.push(num("Logout berfungsi, session terhapus."));

// =================================================================
// BAB 6 — SPRINT 2
// =================================================================
content.push(h1("BAB 6 — Sprint 2 Development"));

content.push(h2("6.1 Sprint 2 Goal & Scope"));
content.push(p([bold("Goal: "), plain("Pemilik usaha dapat menambah dan mengelola produk sparepart secara lengkap, serta memantau sisa stok semua produk secara real-time.")]));
content.push(p([bold("Scope: "), plain("PBI-04 Produk (dengan gambar), PBI-05 Stok Real-time")]));

// PBI-04
content.push(h2("6.2 PBI-04 — CRUD Produk + Upload Gambar"));
content.push(p([bold("User Story: "), plain("US-04: Menambah produk dengan harga, stok, gambar, kategori. US-05: Edit produk dan soft-delete.")]));

content.push(h3("Step 1: Migration Products (dengan Foreign Key)"));
content.push(...codeBlock(`Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->nullable()->unique();
    $table->string('name', 150);
    $table->text('description')->nullable();
    $table->foreignId('category_id')
        ->constrained('categories')
        ->cascadeOnUpdate()
        ->restrictOnDelete();
    $table->foreignId('supplier_id')
        ->nullable()
        ->constrained('suppliers')
        ->cascadeOnUpdate()
        ->nullOnDelete();
    $table->decimal('purchase_price', 12, 2)->default(0);
    $table->decimal('selling_price', 12, 2)->default(0);
    $table->integer('stock')->default(0);
    $table->integer('min_stock')->default(5);
    $table->string('image')->nullable();
    $table->timestamps();
    $table->softDeletes();
});`));
content.push(p([bold("Penjelasan: "), plain("foreignId('category_id')->constrained() membuat FK ke categories.id. restrictOnDelete() mencegah hapus kategori yang dipakai produk. nullOnDelete() set supplier_id ke NULL jika supplier dihapus. decimal(12,2) untuk uang (max 9999999999.99).")]));

content.push(h3("Step 2: Model Product (dengan Cast & Accessor)"));
content.push(...codeBlock(`#[Fillable([
    'code', 'name', 'description',
    'category_id', 'supplier_id',
    'purchase_price', 'selling_price',
    'stock', 'min_stock', 'image',
])]
class Product extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }
        return asset('images/no-image.svg');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}`));
content.push(p([bold("Penjelasan: "), plain("getImageUrlAttribute = accessor yang dipakai sebagai $product->image_url. Otomatis fallback ke placeholder kalau gambar tidak ada. isLowStock() = helper untuk cek stok di bawah threshold.")]));

content.push(h3("Step 3: Controller dengan Upload Gambar"));
content.push(...codeBlock(`public function store(Request $request)
{
    $data = $request->validate([
        'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
        'name' => ['required', 'string', 'max:150'],
        'category_id' => ['required', 'exists:categories,id'],
        'supplier_id' => ['nullable', 'exists:suppliers,id'],
        'purchase_price' => ['required', 'numeric', 'min:0'],
        'selling_price' => ['required', 'numeric', 'min:0'],
        'stock' => ['required', 'integer', 'min:0'],
        'min_stock' => ['required', 'integer', 'min:0'],
        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('products', 'public');
    }

    Product::create($data);
    return redirect()->route('products.index')
        ->with('success', 'Produk berhasil ditambahkan.');
}`));
content.push(p([bold("Penjelasan: "), plain("Rule 'image' validasi MIME image, 'mimes' batasi format, 'max:2048' batasi 2MB. $request->file('image')->store('products', 'public') simpan file ke storage/app/public/products/ dan return relative path-nya untuk disimpan di kolom image.")]));

content.push(h3("Step 4: Buat Symlink Storage"));
content.push(p("Agar file upload dapat diakses via URL public, jalankan:"));
content.push(...codeBlock(`php artisan storage:link`));
content.push(p([bold("Penjelasan: "), plain("Command ini membuat symbolic link dari public/storage/ → storage/app/public/. Jadi file di storage/app/public/products/foto.jpg bisa diakses sebagai /storage/products/foto.jpg.")]));

content.push(h3("Step 5: Form View dengan multipart/form-data"));
content.push(...codeBlock(`<form method="POST" action="{{ route('products.store') }}"
      enctype="multipart/form-data">
    @csrf
    <input type="text" name="name" required>
    <select name="category_id" required>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
    </select>
    <input type="number" name="purchase_price" step="0.01" min="0" required>
    <input type="number" name="selling_price" step="0.01" min="0" required>
    <input type="number" name="stock" min="0" required>
    <input type="file" name="image" accept="image/*">
    <button type="submit">Simpan</button>
</form>`));
content.push(p([bold("Penjelasan: "), plain("enctype='multipart/form-data' WAJIB ada untuk upload file. Tanpa atribut ini, $request->hasFile() akan selalu return false.")]));

// PBI-05
content.push(h2("6.3 PBI-05 — Stok Real-time"));
content.push(p([bold("User Story: "), plain("Sebagai pemilik usaha, saya ingin melihat sisa stok semua produk secara real-time dengan filter per kategori.")]));

content.push(h3("Step 1: Controller dengan Filter"));
content.push(...codeBlock(`public function index(Request $request)
{
    $categoryId = $request->integer('category_id');
    $lowOnly = $request->boolean('low_only');

    $products = Product::query()
        ->with('category')
        ->when($categoryId > 0,
            fn($q) => $q->where('category_id', $categoryId))
        ->when($lowOnly,
            fn($q) => $q->whereColumn('stock', '<=', 'min_stock'))
        ->orderBy('name')
        ->paginate(20)
        ->withQueryString();

    $categories = Category::orderBy('name')->get();

    $summary = [
        'total_items' => Product::count(),
        'low_stock' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
        'out_of_stock' => Product::where('stock', '<=', 0)->count(),
    ];

    return view('stocks.index', compact('products', 'categories', 'summary'));
}`));
content.push(p([bold("Penjelasan: "), plain("->when($condition, $closure) = conditional query — kalau condition true, jalankan closure. ->with('category') = eager load untuk hindari N+1 problem. whereColumn membandingkan 2 kolom langsung di SQL. withQueryString() = pagination links mempertahankan query params filter.")]));

content.push(h3("Step 2: View Stok dengan Badge Visual"));
content.push(...codeBlock(`@foreach($products as $p)
    @php
        $isOut = $p->stock <= 0;
        $isLow = !$isOut && $p->isLowStock();
        $rowClass = $isOut ? 'table-danger' : ($isLow ? 'table-warning' : '');
    @endphp
    <tr class="{{ $rowClass }}">
        <td>{{ $p->name }}</td>
        <td>{{ $p->category?->name }}</td>
        <td class="text-center fw-bold">{{ $p->stock }}</td>
        <td>
            @if($isOut)
                <span class="badge bg-danger">Habis</span>
            @elseif($isLow)
                <span class="badge bg-warning">Stok Menipis</span>
            @else
                <span class="badge bg-success">Aman</span>
            @endif
        </td>
    </tr>
@endforeach`));
content.push(p([bold("Penjelasan: "), plain("Operator ?- (null-safe) mencegah error kalau category null. Class table-danger/warning Bootstrap memberi background merah/kuning pada row. Badge memberi sinyal visual cepat untuk kasir.")]));

content.push(h2("6.4 Sprint 2 Review & Hasil"));
content.push(p("Akhir Sprint 2, tim mendemo:"));
content.push(num("Halaman produk: tambah produk lengkap dengan upload gambar."));
content.push(num("Edit produk mempertahankan gambar lama jika tidak di-replace."));
content.push(num("Soft delete: produk hilang dari list tapi tetap ada di DB."));
content.push(num("Halaman stok real-time: filter per kategori, toggle 'hanya stok menipis', summary card."));
content.push(num("Baris stok habis berwarna merah, stok menipis berwarna kuning."));

// =================================================================
// BAB 7 — FITUR TAMBAHAN
// =================================================================
content.push(h1("BAB 7 — Fitur Tambahan Beyond Sprint"));
content.push(p("Setelah Sprint 1 & 2 selesai, beberapa fitur tambahan diimplementasikan untuk meningkatkan UX dan keamanan."));

content.push(h2("7.1 Status Aktif/Non-aktif untuk Master Data"));
content.push(p("Berbeda dari soft delete (hard removal dari listing), status non-aktif memungkinkan suatu master data sementara dijeda — tetap muncul di list tapi tidak bisa dipakai di transaksi baru."));

content.push(h3("Migration"));
content.push(...codeBlock(`Schema::table('categories', function (Blueprint $table) {
    $table->boolean('is_active')->default(true)->after('description');
});
// Sama untuk suppliers & products`));

content.push(h3("Toggle Status Method"));
content.push(...codeBlock(`public function toggleStatus(Request $request, Category $category)
{
    if (! $this->passwordOk($request)) {
        return back()->with('error', 'Password salah.');
    }
    $category->update(['is_active' => ! $category->is_active]);
    return back()->with('success', "Status kategori berubah.");
}

private function passwordOk(Request $request): bool
{
    return Hash::check(
        $request->input('confirm_password', ''),
        Auth::user()->password
    );
}`));
content.push(p([bold("Penjelasan: "), plain("Sebelum toggle, sistem cek password user dulu (mencegah orang lewat sengaja klik). Hash::check membandingkan plain password dengan hash bcrypt di database.")]));

content.push(h2("7.2 Filter Dropdown — Hanya yang Aktif"));
content.push(p("Saat input produk baru, dropdown kategori dan supplier hanya menampilkan yang aktif. Saat edit, item yang sedang dipilih tetap muncul walau non-aktif:"));
content.push(...codeBlock(`public function edit(Product $product)
{
    $categories = Category::where(function ($q) use ($product) {
        $q->where('is_active', true)
          ->orWhere('id', $product->category_id);
    })->orderBy('name')->get();

    return view('products.edit', compact('product', 'categories'));
}`));

content.push(h2("7.3 Password Confirmation Modal"));
content.push(p("Untuk aksi sensitif (toggle status & hapus), sistem menampilkan modal Bootstrap yang meminta password login user. Aksi baru dieksekusi jika password benar."));
content.push(...codeBlock(`<!-- partials/password-modal.blade.php -->
<div class="modal fade" id="passwordConfirmModal">
    <form method="POST" id="passwordConfirmForm">
        @csrf
        <input type="hidden" name="_method" id="passwordConfirmMethod" value="PATCH">
        <input type="password" name="confirm_password" required>
        <button type="submit">Konfirmasi</button>
    </form>
</div>

<script>
document.querySelectorAll('[data-confirm-action]').forEach(el => {
    el.addEventListener('click', e => {
        e.preventDefault();
        form.action = el.dataset.confirmAction;
        methodInput.value = el.dataset.confirmMethod;
        bsModal.show();
    });
});
</script>`));

content.push(h2("7.4 Sidebar Layout"));
content.push(p("Layout aplikasi pakai sidebar gelap fixed di kiri (240px) dengan brand, section nav (Menu Utama / Data Master / Inventory), dan user info di bawah. Topbar putih sticky di atas konten dengan tanggal."));
content.push(p("Di mobile (<992px), sidebar otomatis collapse jadi drawer yang bisa dibuka via tombol hamburger."));

content.push(h2("7.5 Generated SVG Images (untuk demo)"));
content.push(p("Untuk seeder produk, setiap produk di-generate SVG image unik dengan: warna gradient sesuai kategori, 2-letter abbreviation di tengah, dan nama produk di bawah. File di-store di storage/app/public/products/ dan diakses lewat symlink."));
content.push(...codeBlock(`private function buildSvg(array $p): string
{
    [$from, $to] = $this->palette[$p['category']];
    return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'>
        <defs><linearGradient id='bg' x1='0' y1='0' x2='0' y2='1'>
            <stop offset='0' stop-color='{$from}'/>
            <stop offset='1' stop-color='{$to}'/>
        </linearGradient></defs>
        <rect width='400' height='400' fill='url(#bg)'/>
        <text x='200' y='220' fill='white' font-size='140'
              font-weight='900'>{$p['abbr']}</text>
        <text x='200' y='368' fill='white'
              font-size='22'>{$p['name']}</text>
    </svg>";
}`));

// =================================================================
// BAB 8 — GIT
// =================================================================
content.push(h1("BAB 8 — Git & GitHub"));

content.push(h2("8.1 Inisialisasi Repository Lokal"));
content.push(...codeBlock(`cd C:\\laragon\\www\\motoku
git init -b main
git add .
git commit -m "Initial commit: MOTOKU Sprint 1 & 2"`));

content.push(h2("8.2 File .gitignore"));
content.push(p("Laravel sudah menyediakan .gitignore default yang men-exclude file sensitif:"));
content.push(bullet([inlineCode('.env'), plain(" — password database dan secret keys")]));
content.push(bullet([inlineCode('/vendor'), plain(" — dependencies Composer (akan di-install ulang)")]));
content.push(bullet([inlineCode('/node_modules'), plain(" — dependencies npm")]));
content.push(bullet([inlineCode('/storage/*.key'), plain(" — application key")]));
content.push(bullet([inlineCode('/public/storage'), plain(" — symlink storage")]));

content.push(h2("8.3 Push ke GitHub"));
content.push(num("Buat repository di github.com/new (jangan centang README, .gitignore, license)."));
content.push(num("Hubungkan remote:"));
content.push(...codeBlock(`git remote add origin https://github.com/USERNAME/motoku.git
git push -u origin main`));

content.push(h2("8.4 Branch Strategy"));
content.push(p("Untuk tim Agile, gunakan personal branch agar work-in-progress tidak ganggu main:"));
content.push(...codeBlock(`# Buat branch personal
git checkout -b williams

# Kerja, commit
git add .
git commit -m "tambah fitur X"

# Push branch ke GitHub
git push -u origin williams

# Saat siap merge ke main, buat Pull Request di GitHub`));

content.push(h2("8.5 Workflow Harian"));
content.push(num("git checkout williams (pastikan di branch sendiri)"));
content.push(num("Edit kode, save file."));
content.push(num("git add . dan git commit -m 'pesan jelas'"));
content.push(num("git push (lanjutkan ke branch yang sudah di-track)"));
content.push(num("Saat selesai sprint, merge ke main via Pull Request di GitHub."));

// =================================================================
// BAB 9 — DEMO & TESTING
// =================================================================
content.push(h1("BAB 9 — Demo & Testing"));

content.push(h2("9.1 Cara Menjalankan Aplikasi"));
content.push(num("Buka Laragon dan klik Start All (Apache + MySQL menyala)."));
content.push(num("Buka terminal di folder C:\\laragon\\www\\motoku."));
content.push(num("Jalankan: php artisan serve"));
content.push(num("Buka browser ke http://127.0.0.1:8000"));
content.push(num("Login dengan: admin@motoku.test / password"));

content.push(h2("9.2 Skenario Testing Sprint 1"));
content.push(makeTable([
  ["Skenario", "Langkah", "Expected"],
  ["Login berhasil", "Email + password benar", "Redirect ke /dashboard"],
  ["Login gagal", "Email salah", "Error 'Email atau password salah'"],
  ["Akses tanpa login", "Buka /dashboard langsung", "Redirect ke /login"],
  ["Tambah kategori", "Klik 'Tambah Kategori', isi nama unik", "Muncul di tabel"],
  ["Duplicate nama kategori", "Tambah nama yang sudah ada", "Error 'sudah dipakai'"],
  ["Hapus kategori berisi produk", "Klik hapus", "Error 'masih memiliki produk'"],
  ["Tambah supplier email invalid", "Email = 'abc'", "Error 'format email tidak valid'"],
], [3500, 3500, 2746]));

content.push(h2("9.3 Skenario Testing Sprint 2"));
content.push(makeTable([
  ["Skenario", "Langkah", "Expected"],
  ["Tambah produk lengkap", "Isi form + upload .jpg", "Produk muncul dengan gambar"],
  ["Upload file > 2MB", "Pilih file 3MB", "Error 'max 2MB'"],
  ["Upload .pdf", "Pilih file PDF", "Error 'must be image'"],
  ["Soft-delete produk", "Klik hapus", "Hilang dari list, deleted_at terisi"],
  ["Stok real-time filter", "Pilih kategori X", "Hanya produk kategori X"],
  ["Stok menipis highlight", "Set stok < min_stock", "Row berwarna kuning"],
  ["Stok habis highlight", "Set stok = 0", "Row berwarna merah + badge 'Habis'"],
], [3500, 3500, 2746]));

content.push(h2("9.4 Skenario Testing Fitur Tambahan"));
content.push(makeTable([
  ["Skenario", "Langkah", "Expected"],
  ["Toggle status password salah", "Klik switch, masuk password salah", "Status tidak berubah, error muncul"],
  ["Toggle status password benar", "Klik switch, masuk password benar", "Status flip, badge update"],
  ["Filter dropdown produk", "Non-aktifkan kategori X, lalu tambah produk", "Kategori X tidak muncul di dropdown"],
  ["Hapus produk konfirmasi password", "Klik hapus, masuk password", "Produk soft-deleted setelah konfirmasi"],
], [3500, 3500, 2746]));

// =================================================================
// BAB 10 — PENUTUP
// =================================================================
content.push(h1("BAB 10 — Penutup"));

content.push(h2("10.1 Kesimpulan"));
content.push(p("Sprint 1 dan Sprint 2 berhasil menghasilkan increment fungsional MOTOKU yang dapat di-demo:"));
content.push(bullet("Sistem autentikasi dengan session yang aman."));
content.push(bullet("Manajemen data master lengkap (kategori, supplier, produk)."));
content.push(bullet("Halaman monitoring stok real-time dengan visualisasi warna."));
content.push(bullet("Fitur extra: status aktif/non-aktif, password confirmation, soft delete."));

content.push(h2("10.2 Pembelajaran Scrum yang Didapat"));
content.push(bullet("Sprint Planning memaksa tim memprioritaskan PBI dengan teliti — tidak semua harus dikerjakan di Sprint 1."));
content.push(bullet("Story Point estimasi membantu memprediksi kapasitas tim per Sprint."));
content.push(bullet("Increment yang dapat di-demo di akhir Sprint memberi rasa pencapaian + feedback cepat."));
content.push(bullet("Definition of Done membuat 'selesai' jadi konsisten — bukan opini."));

content.push(h2("10.3 Rencana Sprint Berikutnya"));
content.push(p("Berdasarkan Product Backlog, Sprint 3 direncanakan fokus pada transaksi penjualan dan metode pembayaran:"));
content.push(makeTable([
  ["PBI", "Judul", "SP", "User Story"],
  ["PBI-06", "Transaksi Penjualan + Auto-deduct Stok", "8", "US-07"],
  ["PBI-07", "Pilih Metode Pembayaran", "3", "US-08"],
  ["PBI-08", "Void Transaksi Penjualan", "5", "US-09"],
], [1500, 5246, 800, 2200]));

content.push(p([bold("Total Sprint 3: 16 SP")]));

content.push(h2("10.4 Penutup"));
content.push(p("Pendekatan Scrum membantu tim memecah project besar menjadi increment yang terukur dan dapat didemo. Dengan delivery bertahap, risiko gagal di akhir project bisa diminimalisir karena setiap Sprint sudah menghasilkan working software."));
content.push(p([bold("— Selesai Dokumentasi Sprint 1 & 2 —")]));

// ===== Build Document =====
const doc = new Document({
  creator: "Williams",
  title: "MOTOKU - Dokumentasi Sprint 1 & 2",
  description: "Dokumen presentasi pengembangan MOTOKU dengan metodologi Scrum",
  styles: {
    default: {
      document: { run: { font: "Arial", size: 22 } },
    },
    paragraphStyles: [
      {
        id: "Heading1",
        name: "Heading 1",
        basedOn: "Normal",
        next: "Normal",
        quickFormat: true,
        run: { size: 36, bold: true, font: "Arial", color: "1F2937" },
        paragraph: { spacing: { before: 240, after: 240 }, outlineLevel: 0 },
      },
      {
        id: "Heading2",
        name: "Heading 2",
        basedOn: "Normal",
        next: "Normal",
        quickFormat: true,
        run: { size: 28, bold: true, font: "Arial", color: "1F2937" },
        paragraph: { spacing: { before: 240, after: 120 }, outlineLevel: 1 },
      },
      {
        id: "Heading3",
        name: "Heading 3",
        basedOn: "Normal",
        next: "Normal",
        quickFormat: true,
        run: { size: 24, bold: true, font: "Arial", color: "374151" },
        paragraph: { spacing: { before: 180, after: 100 }, outlineLevel: 2 },
      },
    ],
  },
  numbering: {
    config: [
      {
        reference: "bullets",
        levels: [
          {
            level: 0, format: LevelFormat.BULLET, text: "•", alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } },
          },
          {
            level: 1, format: LevelFormat.BULLET, text: "◦", alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 1440, hanging: 360 } } },
          },
        ],
      },
      {
        reference: "numbers",
        levels: [
          {
            level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } },
          },
        ],
      },
    ],
  },
  sections: [
    {
      properties: {
        page: {
          size: { width: A4_W, height: A4_H },
          margin: { top: MARGIN, right: MARGIN, bottom: MARGIN, left: MARGIN },
        },
      },
      headers: {
        default: new Header({
          children: [new Paragraph({
            children: [
              new TextRun({ text: "MOTOKU", bold: true, size: 18, color: "6B7280" }),
              new TextRun({ text: "\tDokumentasi Sprint 1 & 2", size: 18, color: "9CA3AF" }),
            ],
            tabStops: [{ type: TabStopType.RIGHT, position: CONTENT_W }],
            border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: "E5E7EB", space: 4 } },
          })],
        }),
      },
      footers: {
        default: new Footer({
          children: [new Paragraph({
            children: [
              new TextRun({ text: "Halaman ", size: 18, color: "9CA3AF" }),
              new TextRun({ children: [PageNumber.CURRENT], size: 18, color: "9CA3AF" }),
              new TextRun({ text: " dari ", size: 18, color: "9CA3AF" }),
              new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 18, color: "9CA3AF" }),
            ],
            alignment: AlignmentType.CENTER,
          })],
        }),
      },
      children: content,
    },
  ],
});

Packer.toBuffer(doc).then(buffer => {
  const outPath = __dirname + '/MOTOKU-Presentasi.docx';
  fs.writeFileSync(outPath, buffer);
  console.log('OK: ' + outPath);
  console.log('Size: ' + (buffer.length / 1024).toFixed(1) + ' KB');
}).catch(err => {
  console.error('FAILED:', err.message);
  process.exit(1);
});
