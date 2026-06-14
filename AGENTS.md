# AGENTS.md — Aplikasi Bank Sampah PHP

## Entrypoints & Routing

- **`index.php`** is the main router. All internal pages are served via `?page=<route>` (see `$allowed_pages` array). No framework — plain PHP with `require_once`.
- **`admin.php`** is a separate, self-contained admin panel with hardcoded credentials (`admin`/`admin123`). It uses `config.php` + a non-existent `functions.php` (does not include the main app). Mostly a stub.
- **`cek.php`** is a public page — no login required. Citizens look up balance by name or phone.
- **`index.html`** is a legacy static landing page. Not used by the active app.

## DB Config

- **Primary:** `config/database.php` — defines `DB_HOST/USER/PASS/NAME`, `BASE_URL`, starts session, sets timezone `Asia/Jakarta`.
- **Legacy (admin.php only):** `config.php` — duplicate config.
- **DB name:** `db_banksampah`. Schema in `banksampah.sql`.

## Key Developer Facts

- **No tests, no CI, no build step, no linter, no typechecker.** There is nothing to run. The repo ships a vendor directory.
- **Install:** Point a PHP 8.0+ server at the root, adjust `config/database.php`, run `install.php`, then **delete `install.php`**.
- **Vendor libs are committed** (`libs/vendor/`). No `composer install` needed. The only real dependency is PhpSpreadsheet (Excel export). No `composer.json` in the repo root.
- **No CSRF tokens** on any form.
- **Export handler** (`modules/laporan/export_handler.php`) clears output buffers before writing XLSX — don't add stray `echo` before it.
- **Transactions use DB-level commit/rollback** (`mysqli_begin_transaction`) in `proses_setor.php`.
- `check_user_level(['admin','petugas'])` guards protected pages. Redirects to login if unauthorized.
- Prices are **re-fetched server-side** in deposit processing (client-side `harga_saat_setor` is cosmetic only).

## Architecture

```
config/database.php   → DB connection, helpers (redirect, is_logged_in, check_user_level, format_rupiah)
includes/             → header.php, footer.php, sidebar_{admin,petugas,warga}.php
modules/              → auth/, dashboard/, jenis_sampah/, laporan/, profil/, transaksi/, warga/
```

Three user levels: `admin`, `petugas`, `warga`. Session keys: `user_id`, `user_nama`, `user_username`, `user_level`, `login_time`.

## Known Bugs & Broken Things (still open)

- **`admin.php`** — `case 'nasabah_list'`, `'nasabah_tambah'`, `'nasabah_proses_tambah'` call undefined functions (`generateNextIdNasabah`, `getNasabahInfo`, `tambahNasabah`). Login/dashboard work but CRUD stubs will error. The legacy `admin.php` is separate from the main app.
- **`index.html`** — legacy page with its own broken query logic. Not used by the active app.
- **`config.php`** — `BASE_URL` is not defined (only used by legacy `admin.php` and `index.html`).

## Security Weaknesses (agent should never introduce more)

- **No CSRF tokens** on any form. State-changing actions (setor, tarik, tambah/edit/hapus warga, ganti password) are all unprotected.
- **No `session_regenerate_id()`** after login — session fixation possible.
- **Delete operations use GET** with `onclick="return confirm(...)"` only (`modules/warga/hapus.php`, `modules/jenis_sampah/hapus.php`).
- `install.php` must be **deleted** after setup — leaving it accessible lets anyone reinitialize the DB.
- Flash messages (`$_SESSION['success_message']` / `$_SESSION['error_message']`) are echoed **without `htmlspecialchars()`** in `includes/header.php:203,232`.
- Verbose error messages leak server paths and SQL errors to users (`config/database.php:18`, `modules/warga/proses_simpan.php`).

## Applied Fixes (Jun 2026)

### Session 1
- **`admin.php`** — removed broken `require_once 'functions.php'`. `config.php` updated to use TCP + `banksampah` user.
- **`modules/jenis_sampah/index.php`** — converted `LIKE '%$search%'` to prepared statement.
- **`includes/header.php`** — flash messages now wrapped in `htmlspecialchars()`.
- **`modules/auth/proses_login.php`** — added `session_regenerate_id(true)` after login + `require_csrf()`.
- **`install.php`** — guard blocks re-installation if `pengguna` table has data.

### Session 2 — Full Security & Feature Overhaul
- **CSRF** — `csrf_field()` added to every form (login, warga CRUD, jenis_sampah CRUD, setor, tarik, profil, pengelola, lupa_password, register). `require_csrf()` in every process file.
- **Activity logging** — `log_aktivitas()` on every create/update/delete action across all modules.
- **Soft delete** — `warga/hapus.php` and `jenis_sampah/hapus.php` converted from GET+DELETE to POST+status='nonaktif'. `pengelola/hapus_petugas.php` also converted to POST.
- **Status filter** — `warga/index.php` and `jenis_sampah/index.php` now filter by `(status IS NULL OR status = 'aktif')`. Hapus links replaced with CSRF-protected POST forms.
- **Dashboard** — Chart.js 7-month bar chart (setoran vs penarikan). Warga-level dashboard (own balance + transactions). `check_user_level` relaxed to include `warga`.
- **Photo upload** — Profile page shows photo. `proses_update_profil.php` handles JPEG/PNG/GIF upload with validation and old-file cleanup.
- **Sidebars** — `sidebar_admin.php` and `sidebar_petugas.php` updated with Kelola Petugas, Rekap Warga, Export PDF, Backup Database links.
- **Login page** — added "Lupa password?" and "Daftar sebagai warga" links.
- **Price history** — `jenis_sampah/proses_simpan.php` records old→new price changes in `harga_history` table.
- **Database migration** — `migration_2026.sql` updated to add `status` column to `jenis_sampah`.
- **`log_aktivitas()` calls fixed** — all calls corrected to match function signature (`$aksi, $tabel, $id_record, $detail`), not passing `$koneksi`.

## Style Conventions

- Tailwind CSS v3 via CDN, Font Awesome via CDN, Alpine.js v2 for interactive forms.
- Flash messages via `$_SESSION['success_message']` / `$_SESSION['error_message']` (cleared after display in header.php). Also `$_GET['pesan']` query param pattern in legacy login.
- SQL: **prefer prepared statements** (`mysqli_prepare`/`bind_param`). Some dashboard stats still use raw `mysqli_query()` — match the file's existing pattern when editing.
- `sanitize_input()` wraps `trim` + `stripslashes` + `htmlspecialchars` + `mysqli_real_escape_string` (order is unusual — htmlspecialchars runs before escape).
- All user output should be wrapped in `htmlspecialchars()`.

## Duplicated / Dead Code to Know

- **`includes/functions.php`** — defines `format_rupiah()` and `format_tanggal_indonesia()`, but these exact same functions already live in `config/database.php`. This file is never loaded.
- **`style.css`** — only used by legacy `index.html` and `admin.php`. Main app uses Tailwind CDN + inline `<style>`.
- **`config.php`** — legacy duplicate of `config/database.php`, only used by `admin.php` and `index.html`.
- **`sidebar_admin.php` and `sidebar_petugas.php` are byte-for-byte identical** — edit both if changing nav links.
