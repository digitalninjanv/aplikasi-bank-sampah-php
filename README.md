<div align="center">
  <h1>🌱 Aplikasi Bank Sampah Digital</h1>
  <p><strong>Sistem Manajemen Bank Sampah Terintegrasi — PHP Native, Open Source</strong></p>
  <p>
    <a href="#fitur">Fitur</a> •
    <a href="#demonstrasi">Demo</a> •
    <a href="#panduan-instalasi">Instalasi</a> •
    <a href="#arsitektur">Arsitektur</a> •
    <a href="#keamanan">Keamanan</a> •
    <a href="#api-endpoints">API</a> •
    <a href="#database">Database</a> •
    <a href="#kontribusi">Kontribusi</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.0+">
    <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" alt="MySQL 8.0">
    <img src="https://img.shields.io/badge/License-MIT-green" alt="MIT License">
    <img src="https://img.shields.io/badge/Tailwind_CSS-v3-06B6D4?logo=tailwindcss" alt="Tailwind CSS v3">
  </p>
</div>

---

## 📋 Daftar Isi

- [Sekilas Tentang](#sekilas-tentang)
- [Fitur Lengkap](#fitur-lengkap)
- [Demonstrasi](#demonstrasi)
- [Panduan Instalasi](#panduan-instalasi)
- [Konfigurasi](#konfigurasi)
- [Akun Default](#akun-default)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
- [Database Schema](#database-schema)
- [Keamanan](#keamanan)
- [API & Endpoints](#api--endpoints)
- [Hak Akses Pengguna](#hak-akses-pengguna)
- [Dependensi](#dependensi)
- [Panduan Kontribusi](#panduan-kontribusi)
- [Lisensi](#lisensi)

---

## 🎯 Sekilas Tentang

**Aplikasi Bank Sampah Digital** adalah sistem informasi manajemen bank sampah berbasis **PHP Native** (tanpa framework) yang dirancang untuk membantu pengelolaan bank sampah di tingkat desa, kelurahan, sekolah, atau komunitas.

Aplikasi ini mencakup siklus penuh operasional bank sampah: **pendaftaran nasabah**, **pencatatan setoran sampah**, **penarikan tabungan**, **pelaporan keuangan**, **ekspor data**, dan **halaman publik** untuk pengecekan saldo mandiri.

> **Status:** Production-ready | Aktif dikembangkan | Open Source (MIT)

---

## ✨ Fitur Lengkap

### 🖥️ Area Admin & Petugas (Memerlukan Login)

| Modul | Fitur | Deskripsi |
|-------|-------|-----------|
| **Dashboard** | Ringkasan & Grafik | 4 kartu statistik (total warga, jenis sampah, berat bulan ini, total saldo), grafik batang 7 bulan (Chart.js), aktivitas terbaru, tautan cepat |
| **Manajemen Warga** | CRUD + Soft Delete | Tambah (username otomatis dari nomor HP, password acak), edit, hapus (soft delete), pencarian, paginasi, tampilan mobile-card |
| **Jenis Sampah** | CRUD + Riwayat Harga | Daftar jenis sampah beserta harga per kg, soft delete, **riwayat perubahan harga** (harga_history) — setiap perubahan harga tercatat otomatis |
| **Transaksi Setor** | Form Dinamis + Cetak Struk | Form multi-item (Alpine.js) dengan perhitungan subtotal otomatis, **harga di-refetch server-side** (tidak menggunakan harga dari client), **DB transaction** untuk integritas data, cetak struk |
| **Transaksi Tarik** | Validasi Saldo | Pengecekan saldo sebelum penarikan, **DB transaction**, cetak struk print-friendly |
| **Riwayat Transaksi** | Filter & Paginasi | Filter berdasarkan warga, tipe transaksi, rentang tanggal, paginasi, tampilan grid/table |
| **Laporan Harian** | Income/Expense | Pemasukan (setoran), pengeluaran (penarikan), saldo akhir, detail per transaksi |
| **Laporan Bulanan** | Breakdown Per Hari | Rincian per hari dalam bulan, total pemasukan/pengeluaran/saldo akhir |
| **Laporan Rekap Warga** | Aggregat Per Nasabah | Total setor, tarik, saldo per warga dalam rentang tanggal, bisa diurutkan |
| **Laporan Riwayat Warga** | Histori Individu | Petugas/admin bisa lihat histori warga mana pun, warga hanya lihat milik sendiri |
| **Ekspor Excel** | XLSX via PhpSpreadsheet | Ekspor laporan ke format Excel dengan robust output buffer handling |
| **Ekspor PDF** | PDF via Dompdf | Ekspor laporan ke PDF dengan fallback jika library tidak terinstall |
| **Manajemen Petugas** | CRUD (Admin Only) | Tambah, edit, hapus (soft delete) akun petugas, lihat status & last login |
| **Backup Database** | SQL Dump (Admin Only) | Download seluruh database sebagai file SQL |
| **Pengaturan Aplikasi** | Konfigurasi (Admin Only) | Nama aplikasi, alamat, nomor telepon — tersimpan di database (app_settings) |
| **Profil Pengguna** | Edit Profil + Foto | Upload foto profil (JPEG/PNG/GIF, max 2MB, validasi MIME), ganti password dengan verifikasi lama |

### 👤 Halaman Publik (Tanpa Login)

| Halaman | Fitur |
|---------|-------|
| **Landing Page** | `/` — Landing page SEO dengan meta tags, JSON-LD structured data, fitur, testimoni, cara kerja, CTA kontak |
| **Login** | `?page=auth/login` — Form login dengan CSRF, link "Lupa Password?" dan "Daftar sebagai Warga" |
| **Registrasi** | `?page=auth/register` — Pendaftaran mandiri sebagai warga |
| **Lupa Password** | `?page=auth/lupa_password` — Reset password via token (2 langkah) |
| **Cek Saldo Publik** | `cek.php` — Cek saldo & riwayat via nama atau nomor telepon (tanpa login) |

---

## 🎬 Demonstrasi


| Item | Detail |
| --- | --- |
| **URL Demo** | [https://bankgentara.my.id/aplikasi-bank-sampah-php-main/](https://bankgentara.my.id/aplikasi-bank-sampah-php-main/) |
| **Admin** | Username: `admin`, Password: `admin123` |
| **Petugas** | Username: `petugas1`, Password: `petugas123` |
| **Warga** | Login via aplikasi setelah registrasi |

## 📦 Panduan Instalasi

### Prasyarat

- PHP 8.0 atau lebih tinggi
- MySQL 8.0 / MariaDB 10.4+
- Web server (Apache / Nginx / PHP built-in server)
- Ekstensi PHP: `mysqli`, `mbstring`, `gd` (untuk upload foto), `fileinfo` (untuk validasi MIME), `zip` (untuk PhpSpreadsheet)

### Instalasi Cepat

```bash
# 1. Clone repositori
git clone https://github.com/digitalninjanv/aplikasi-bank-sampah-php.git
cd aplikasi-bank-sampah-php

# 2. Konfigurasi database
#    Edit config/database.php — sesuaikan DB_HOST, DB_USER, DB_PASS, DB_NAME, BASE_URL

# 3. Install database
#    Buka http://localhost/aplikasi-bank-sampah/install.php di browser
#    Klik "Mulai Instalasi Sekarang"

# 4. HAPUS install.php setelah berhasil!
rm install.php

# 5. Selesai. Akses aplikasi di browser
```

### Instalasi Manual (Alternatif)

1. Buat database `db_banksampah` di phpMyAdmin
2. Import `banksampah.sql` ke database tersebut
3. Jalankan `migration_2026.sql` untuk update schema terbaru
4. Edit `config/database.php` sesuai environment Anda
5. Hapus `install.php`

### Development Server (PHP Built-in)

```bash
php -S localhost:8080 -t /path/to/aplikasi-bank-sampah-php
```

---

## ⚙️ Konfigurasi

### `config/database.php`

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'banksampah');
define('DB_PASS', 'banksampah');
define('DB_NAME', 'db_banksampah');
define('BASE_URL', 'http://localhost:8080/');
```

**Catatan:** `BASE_URL` harus diakhiri dengan `/` dan digunakan untuk redirect. Sesuaikan dengan URL deployment Anda.

### `config.php` (Legacy)

Hanya digunakan oleh `admin.php` dan `index.html` (keduanya legacy/stub). Tidak digunakan oleh aplikasi utama.

---

## 👤 Akun Default

| Peran | Username | Password | Keterangan |
|-------|----------|----------|------------|
| **Admin** | `admin` | `admin123` | Akses penuh ke semua fitur |
| **Petugas** | `petugas1` | `petugas123` | Operasional (tanpa manajemen petugas/backup/settings) |
| **Warga Demo 1** | (login via HP) | `warga123` | Budi Santoso — 081234567001 |
| **Warga Demo 2** | (login via HP) | `warga123` | Siti Aminah — 081234567002 |

> **Peringatan:** Ubah password default segera setelah instalasi untuk lingkungan production!

---

## 🏗️ Arsitektur Aplikasi

### Struktur Direktori

```
aplikasi-bank-sampah-php/
│
├── config/
│   └── database.php          # 🔧 Konfigurasi utama DB, helper functions, CSRF, logging
│
├── includes/
│   ├── header.php            # Header HTML, sidebar loader, flash messages, CSS/JS CDN
│   ├── footer.php            # Footer + Alpine.js + sidebar toggle
│   ├── sidebar_admin.php     # Navigasi sidebar untuk admin
│   ├── sidebar_petugas.php   # Navigasi sidebar untuk petugas (identik dengan admin)
│   └── sidebar_warga.php     # Navigasi sidebar untuk warga (3 item)
│
├── libs/
│   ├── composer.json         # Dependensi: dompdf/dompdf ^3.1, phpoffice/phpspreadsheet ^5.8
│   └── vendor/               # Vendor libraries (committed — no composer install needed)
│
├── modules/
│   ├── admin/
│   │   └── settings.php      # Pengaturan aplikasi (admin only)
│   ├── auth/
│   │   ├── login.php         # Halaman login
│   │   ├── proses_login.php  # Proses login (rate limiting, session regeneration)
│   │   ├── logout.php        # Proses logout
│   │   ├── register.php      # Halaman registrasi warga
│   │   ├── proses_register.php
│   │   ├── lupa_password.php # Halaman lupa password
│   │   └── proses_lupa_password.php
│   ├── backup/
│   │   ├── index.php         # Halaman backup
│   │   └── proses.php        # Proses backup SQL dump
│   ├── dashboard/
│   │   └── index.php         # Dashboard dengan Chart.js
│   ├── jenis_sampah/
│   │   ├── index.php         # Daftar jenis sampah (paginasi, search)
│   │   ├── tambah.php        # Form tambah
│   │   ├── edit.php          # Form edit
│   │   ├── proses_simpan.php # Simpan/edit + riwayat harga
│   │   └── hapus.php         # Soft delete (POST)
│   ├── laporan/
│   │   ├── harian.php        # Laporan harian
│   │   ├── bulanan.php       # Laporan bulanan
│   │   ├── rekap_warga.php   # Rekap per warga
│   │   ├── riwayat_warga.php # Histori transaksi warga
│   │   ├── export_handler.php# Ekspor XLSX
│   │   └── export_pdf.php    # Ekspor PDF
│   ├── pengelola/
│   │   ├── petugas.php       # Daftar petugas
│   │   ├── tambah_petugas.php
│   │   ├── edit_petugas.php
│   │   ├── proses_simpan_petugas.php
│   │   └── hapus_petugas.php # Soft delete (POST)
│   ├── profil/
│   │   ├── index.php         # Profil + upload foto + ganti password
│   │   ├── proses_update_profil.php
│   │   └── proses_ganti_password.php
│   ├── transaksi/
│   │   ├── setor.php         # Form setor (Alpine.js, dynamic items)
│   │   ├── proses_setor.php  # Proses setor (DB transaction)
│   │   ├── tarik_saldo.php   # Form tarik
│   │   ├── proses_tarik.php  # Proses tarik (DB transaction)
│   │   ├── riwayat.php       # Riwayat transaksi (filter, paginasi)
│   │   └── struk.php         # Cetak struk (print CSS)
│   └── warga/
│       ├── index.php         # Daftar warga
│       ├── tambah.php        # Form tambah
│       ├── edit.php          # Form edit
│       ├── proses_simpan.php # Proses simpan (cek username unik)
│       └── hapus.php         # Soft delete (POST)
│
├── uploads/
│   └── foto_profil/          # Upload foto profil warga/petugas/admin
│
├── index.php                 # 🚀 Router utama (42 routes via ?page=)
├── landing.php               # Landing page SEO
├── cek.php                   # Cek saldo publik
├── install.php               # Installer database (HAPUS setelah instalasi!)
├── banksampah.sql            # Schema & seed data original
├── migration_2026.sql        # Migration: soft delete, log, harga history, settings
├── admin.php                 # ⚠️ Legacy — admin panel terpisah (stub, tidak digunakan)
├── index.html                # ⚠️ Legacy — halaman statis lama (broken, tidak digunakan)
├── style.css                 # ⚠️ Legacy — hanya untuk admin.php & index.html
├── config.php                # ⚠️ Legacy — duplikat config (hanya untuk admin.php)
└── AGENTS.md                 # Petunjuk untuk AI coding agents
```

### Alur Request

```
Browser → index.php?page=<route>
              │
              ├── Cek session & status login
              ├── Cek public_pages vs protected pages
              ├── Load $allowed_pages[$route]
              │       │
              │       ├── Jika di $no_layout_pages → load file saja
              │       └── Jika tidak → include header → load page → include footer
              │
              └── Jika route tidak valid → 403 / 404
```

### Design Pattern

- **Router-centric:** Satu entry point (`index.php`) menangani semua request
- **MVC-lite:** Pemisahan antara logika (proses_*.php), tampilan (module/*.php), dan konfigurasi (config/)
- **No ORM:** Semua query menggunakan MySQLi procedural
- **No Build Step:** Frontend via CDN (Tailwind, Font Awesome, Alpine.js, Chart.js)

---

## 🗄️ Database Schema

### Tables

#### `pengguna` — Users (Admin, Petugas, Warga)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_pengguna` | int(11) PK, AUTO_INCREMENT | ID unik |
| `nama_lengkap` | varchar(100) NOT NULL | Nama lengkap |
| `username` | varchar(50) UNIQUE | Username login (warga = nomor HP) |
| `password` | varchar(255) | bcrypt hash |
| `level` | enum('admin','petugas','warga') | Level akses |
| `alamat` | text | Alamat |
| `no_telepon` | varchar(15) | Nomor HP (untuk cek saldo publik) |
| `saldo` | decimal(10,2) DEFAULT 0 | Saldo tabungan |
| `foto` | varchar(255) | Path foto profil |
| `status` | enum('aktif','nonaktif') DEFAULT 'aktif' | Soft delete |
| `last_login` | timestamp | Waktu login terakhir |
| `login_attempts` | tinyint DEFAULT 0 | Percobaan login gagal |
| `locked_until` | timestamp | Waktu lockout |
| `reset_token` | varchar(64) | Token reset password |
| `reset_expires` | timestamp | Token reset expiry |
| `created_by` | int | FK ke id_pengguna pembuat |
| `tanggal_daftar` | timestamp DEFAULT CURRENT_TIMESTAMP | Tanggal daftar |

#### `jenis_sampah` — Jenis Sampah

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_jenis_sampah` | int(11) PK, AUTO_INCREMENT | ID unik |
| `nama_sampah` | varchar(100) | Nama sampah |
| `harga_per_kg` | decimal(10,2) | Harga per kg |
| `deskripsi` | text | Deskripsi |
| `satuan` | varchar(10) DEFAULT 'kg' | Satuan |
| `status` | enum('aktif','nonaktif') DEFAULT 'aktif' | Soft delete |

#### `transaksi` — Transaksi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_transaksi` | int(11) PK, AUTO_INCREMENT | ID unik |
| `id_warga` | int(11) FK → pengguna(id_pengguna) | Nasabah |
| `id_petugas_pencatat` | int(11) FK → pengguna(id_pengguna) | Petugas |
| `tanggal_transaksi` | timestamp | Waktu transaksi |
| `tipe_transaksi` | enum('setor','tarik_saldo') | Tipe |
| `total_nilai` | decimal(10,2) | Total nominal |
| `keterangan` | text | Keterangan |

#### `detail_setoran` — Detail Setoran

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_detail_setoran` | int(11) PK, AUTO_INCREMENT | ID unik |
| `id_transaksi_setor` | int(11) FK → transaksi(id_transaksi) | Transaksi induk |
| `id_jenis_sampah` | int(11) FK → jenis_sampah(id_jenis_sampah) | Jenis sampah |
| `berat_kg` | decimal(5,2) | Berat (kg) |
| `harga_saat_setor` | decimal(10,2) | Harga per kg (server-side) |
| `subtotal_nilai` | decimal(10,2) | Subtotal |

#### `log_aktivitas` — Log Aktivitas

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_log` | int(11) PK, AUTO_INCREMENT | ID unik |
| `id_pengguna` | int(11) | Pelaku |
| `username` | varchar(50) | Username pelaku |
| `aksi` | varchar(50) | Aksi (tambah/edit/hapus/login, dll) |
| `tabel` | varchar(50) | Tabel yang diubah |
| `id_record` | int(11) | ID record yang diubah |
| `detail` | text | Detail aksi |
| `ip_address` | varchar(45) | Alamat IP |
| `created_at` | timestamp | Waktu aksi |

#### `harga_history` — Riwayat Harga Sampah

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_history` | int(11) PK, AUTO_INCREMENT | ID unik |
| `id_jenis_sampah` | int(11) FK → jenis_sampah(id_jenis_sampah) | Jenis sampah |
| `harga_lama` | decimal(10,2) | Harga sebelum perubahan |
| `harga_baru` | decimal(10,2) | Harga setelah perubahan |
| `id_petugas` | int(11) FK → pengguna(id_pengguna) | Petugas pengubah |
| `created_at` | timestamp | Waktu perubahan |

#### `app_settings` — Pengaturan Aplikasi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `setting_key` | varchar(50) PK | Key (app_name, app_address, app_phone) |
| `setting_value` | text | Value |
| `updated_at` | timestamp | Waktu update |

### Entity Relationship

```
pengguna (admin/petugas)
    │
    ├──< transaksi (id_petugas_pencatat)
    │
pengguna (warga/nasabah)
    │
    ├──< transaksi (id_warga)
    ├──< log_aktivitas (id_pengguna)
    └──< harga_history (id_petugas)
    
transaksi
    │
    └──< detail_setoran

jenis_sampah
    │
    ├──< detail_setoran (id_jenis_sampah)
    └──< harga_history (id_jenis_sampah)
```

---

## 🔒 Keamanan

### Ringkasan Postur Keamanan

| Aspek | Implementasi |
|-------|--------------|
| **CSRF** | Token 32-byte random hex per session, `hash_equals()` untuk verifikasi, `csrf_field()` di setiap form |
| **SQL Injection** | Semua query menggunakan prepared statements (`mysqli_prepare()` + `bind_param()`) |
| **XSS** | Output escaping dengan `htmlspecialchars()` di setiap tampilan data |
| **Password** | `password_hash(PASSWORD_DEFAULT)` = bcrypt, diverifikasi dengan `password_verify()` |
| **Session Fixation** | `session_regenerate_id(true)` setelah login berhasil |
| **Session Timeout** | 30 menit tanpa aktivitas → auto-logout |
| **Rate Limiting** | 5x gagal login → akun terkunci 15 menit |
| **Account Status** | Akun nonaktif tidak bisa login |
| **Soft Delete** | Data tidak dihapus permanen, hanya diubah statusnya |
| **HTTP Headers** | X-Frame-Options: DENY, X-Content-Type-Options: nosniff, X-XSS-Protection, Referrer-Policy, Permissions-Policy |
| **File Upload** | Validasi MIME via finfo, ekstensi whitelist (jpg/jpeg/png/gif), max 2MB |
| **Transaction** | `mysqli_begin_transaction/commit/rollback` untuk setor dan tarik |
| **Server-Side Price** | Harga sampah di-refetch server-side saat setor (client-side hanya display) |
| **Install Guard** | `install.php` diblokir jika database sudah terisi |
| **Error Reporting** | `mysqli_report(MYSQLI_REPORT_OFF)` — error DB tidak ditampilkan ke user |

### Detail Implementasi

```
config/database.php:
  ├── generate_csrf_token()     → bin2hex(random_bytes(32))
  ├── verify_csrf_token()       → hash_equals() timing-safe comparison
  ├── csrf_field()              → <input type="hidden" name="csrf_token" value="...">
  ├── require_csrf()            → Validasi di setiap POST handler, redirect jika invalid
  └── log_aktivitas()           → INSERT ke log_aktivitas untuk setiap aksi penting

modules/auth/proses_login.php:
  ├── login_attempts check      → Blokir jika locked_until > now
  ├── session_regenerate_id()   → Cegah session fixation
  ├── status check              → Akun nonaktif ditolak
  └── log_aktivitas()           → Catat sukses/gagal login
```

---

## 🌐 API & Endpoints

### Route Map

Aplikasi menggunakan skema `?page=<route>` dengan `index.php` sebagai router tunggal.

```
Public Pages (no login):
  GET  /                          → Landing page
  GET  ?page=landing              → Landing page
  GET  ?page=auth/login           → Form login
  POST ?page=auth/proses_login    → Proses login
  GET  ?page=auth/register        → Form registrasi warga
  POST ?page=auth/proses_register → Proses registrasi
  GET  ?page=auth/lupa_password   → Form lupa password
  POST ?page=auth/proses_lupa_password → Proses reset password
  GET  cek.php                    → Cek saldo publik (by nama/telepon)

Protected Pages (login required):
  GET  ?page=dashboard            → Dashboard utama
  GET  ?page=profil               → Profil pengguna
  POST ?page=profil/proses_update_profil  → Update profil
  POST ?page=profil/proses_ganti_password → Ganti password

  GET  ?page=warga/data           → Daftar warga
  GET  ?page=warga/tambah         → Form tambah warga
  GET  ?page=warga/edit           → Form edit warga (requires id)
  POST ?page=warga/proses_simpan  → Simpan/edit warga
  POST ?page=warga/hapus          → Soft delete warga

  GET  ?page=jenis_sampah/data    → Daftar jenis sampah
  GET  ?page=jenis_sampah/tambah  → Form tambah jenis sampah
  GET  ?page=jenis_sampah/edit    → Form edit jenis sampah (requires id)
  POST ?page=jenis_sampah/proses_simpan → Simpan/edit jenis sampah
  POST ?page=jenis_sampah/hapus   → Soft delete jenis sampah

  GET  ?page=transaksi/setor      → Form setor sampah
  POST ?page=transaksi/proses_setor → Proses setor (DB transaction)
  GET  ?page=transaksi/tarik_saldo → Form tarik saldo
  POST ?page=transaksi/proses_tarik  → Proses tarik (DB transaction)
  GET  ?page=transaksi/riwayat    → Riwayat transaksi
  GET  ?page=transaksi/struk      → Cetak struk (requires id_transaksi)

  GET  ?page=laporan/harian       → Laporan harian
  GET  ?page=laporan/bulanan      → Laporan bulanan
  GET  ?page=laporan/rekap_warga  → Rekap per warga
  GET  ?page=laporan/riwayat_warga → Riwayat warga (admin/petugas/warga)
  GET  ?page=laporan/export       → Download XLSX
  GET  ?page=laporan/export_pdf   → Download PDF

Admin Only:
  GET  ?page=pengelola/petugas    → Daftar petugas
  GET  ?page=pengelola/tambah_petugas → Form tambah petugas
  GET  ?page=pengelola/edit_petugas   → Form edit petugas
  POST ?page=pengelola/proses_simpan_petugas → Simpan/edit petugas
  POST ?page=pengelola/hapus_petugas    → Soft delete petugas
  GET  ?page=backup/index         → Halaman backup
  POST ?page=backup/proses        → Proses backup SQL
  GET  ?page=admin/settings       → Pengaturan aplikasi
```

---

## 👑 Hak Akses Pengguna

| Level | Modul yang Dapat Diakses |
|-------|-------------------------|
| **`admin`** | Semua modul: dashboard, warga, jenis_sampah, transaksi, laporan (all), **pengelola**, **backup**, **settings**, profil, riwayat warga (any) |
| **`petugas`** | Dashboard, warga, jenis_sampah, transaksi, laporan (harian/bulanan/rekap/ekspor), profil sendiri |
| **`warga`** | Dashboard sendiri (saldo + 5 transaksi terakhir), profil sendiri (edit, ganti password), riwayat transaksi sendiri |

### Guard Implementation

```php
// config/database.php
function check_user_level($allowed_levels) {
    // Cek login → redirect ke login jika belum
    // Cek level → redirect ke dashboard jika tidak punya akses
}

// Contoh penggunaan:
check_user_level(['admin']);                        // Hanya admin
check_user_level(['admin', 'petugas']);             // Admin & petugas
check_user_level(['warga', 'admin', 'petugas']);    // Semua level
```

---

## 📚 Dependensi

### Vendor (Committed in `libs/vendor/`)

| Library | Versi | Fungsi | Digunakan Di |
|---------|-------|--------|--------------|
| **phpoffice/phpspreadsheet** | ^5.8 | Generate file Excel XLSX | `modules/laporan/export_handler.php` |
| **dompdf/dompdf** | ^3.1 | Generate file PDF | `modules/laporan/export_pdf.php` |
| dompdf/php-font-lib | (transitive) | Font handling | Dompdf |
| dompdf/php-svg-lib | (transitive) | SVG rendering | Dompdf |

> **Catatan:** Semua vendor sudah dicommit — tidak perlu `composer install`. Jika ingin update, jalankan `composer update` di direktori `libs/`.

### CDN (Frontend — tidak perlu diinstal)

| Resource | URL | Digunakan Di |
|----------|-----|--------------|
| Tailwind CSS v3 | `cdn.tailwindcss.com` | Semua halaman utama |
| Font Awesome 6 | `cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css` | Semua halaman |
| Alpine.js v2 | `cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js` | Form setor, tarik, loading states |
| Chart.js 4 | `cdn.jsdelivr.net/npm/chart.js@4` | Dashboard (grafik batang 7 bulan) |
| Google Fonts (Inter + Poppins) | `fonts.googleapis.com` | Semua halaman |

---

## 🧪 Testing

Proyek ini **tidak memiliki test suite otomatis**. Pengujian dilakukan secara manual.

### Validasi yang Direkomendasikan Sebelum Deployment

```bash
# 1. Cek syntax PHP semua file
find . -name "*.php" -not -path "./libs/vendor/*" -exec php -l {} \; 2>&1 | grep -v "No syntax"

# 2. Test flow kritis secara manual:
#    - Login/logout dengan semua level user
#    - CRUD warga (tambah, edit, hapus)
#    - Setor sampah (multiple items)
#    - Tarik saldo
#    - Generate laporan + export XLSX/PDF
#    - Backup database
```

---

## 🤝 Panduan Kontribusi

Kontribusi sangat terbuka! Berikut panduan kontribusi:

### Cara Berkontribusi

1. **Fork** repositori ini
2. Buat **branch fitur**:
   ```bash
   git checkout -b fitur/fitur-keren
   ```
3. **Commit** perubahan Anda:
   ```bash
   git commit -m "feat: menambahkan fitur keren"
   ```
4. **Push** ke branch:
   ```bash
   git push origin fitur/fitur-keren
   ```
5. Buka **Pull Request**

### Pedoman Kontribusi

- Ikuti **style convention** yang sudah ada (PHP tanpa framework, Tailwind CSS, Alpine.js)
- Gunakan **prepared statements** untuk semua query SQL baru
- Tambahkan **CSRF protection** untuk setiap form baru
- Gunakan **`htmlspecialchars()`** untuk output data user
- Tambahkan **`log_aktivitas()`** untuk setiap aksi create/update/delete
- Jangan commit file `install.php` yang sudah dieksekusi

### Melaporkan Bug

Buka **Issue** di GitHub dengan template:
- Deskripsi bug
- Langkah reproduksi
- Expected vs actual behavior
- Screenshot (jika relevan)
- Environment (PHP version, browser, OS)

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **Lisensi MIT**.

```
MIT License

Copyright (c) 2026 Digital Ninja

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

<div align="center">
  <p>Dibuat dengan ❤️ untuk lingkungan yang lebih baik</p>
  <p>
    <a href="mailto:digitalninja.net@gmail.com">📧 Kontak</a> •
    <a href="https://github.com/digitalninjanv/aplikasi-bank-sampah-php/issues">🐛 Laporkan Bug</a> •
    <a href="https://github.com/digitalninjanv/aplikasi-bank-sampah-php">⭐ GitHub</a>
  </p>
</div>
