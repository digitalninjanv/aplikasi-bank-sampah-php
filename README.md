# Aplikasi Bank Sampah Digital (PHP Native - Gratis & Open Source)

**Demo Screenshot:**  

`[https://raw.githubusercontent.com/digitalninjanv/hosting_image/refs/heads/main/bank%20sampah/Screenshot%202025-11-13%20060241.png]`

---

## Deskripsi Umum

**Source Code Aplikasi Bank Sampah Digital** adalah sistem informasi manajemen (SIM) berbasis **PHP Native** tanpa framework.  
Aplikasi ini bersifat **gratis**, **open source**, dan **siap digunakan**.

Tujuannya adalah membantu petugas bank sampah dalam:
- Mengelola data warga.  
- Mencatat transaksi setoran dan penarikan.  
- Menyusun laporan keuangan dan operasional.

Selain itu, aplikasi ini memiliki **halaman publik** bagi warga untuk **mengecek saldo dan riwayat transaksi secara mandiri**.

Cocok digunakan oleh:
- Komunitas dan UKM.  
- Sekolah atau instansi yang memiliki unit bank sampah.  
- Pembelajaran PHP Native yang aman dan modular.

---

## Fitur Utama

### 1. Area Admin & Petugas (Memerlukan Login)
- **Dashboard Real-Time:** Menampilkan ringkasan data seperti total warga, saldo, dan aktivitas terbaru.  
- **Manajemen Warga (CRUD):**  
  - Tambah, ubah, dan hapus data warga.  
  - Username & password warga dihasilkan otomatis untuk keamanan.  
- **Manajemen Jenis Sampah:**  
  - CRUD untuk daftar jenis sampah dan harga per kilogram.  
- **Transaksi Setor Sampah:**  
  - Form dinamis dengan perhitungan otomatis subtotal dan total nilai.  
  - Saldo warga diperbarui secara real-time.  
- **Transaksi Tarik Saldo:**  
  - Validasi saldo otomatis untuk mencegah penarikan berlebih.  
- **Pelaporan Komprehensif:**  
  - Laporan harian dan bulanan.  
  - Ekspor ke **Excel (.xlsx)** menggunakan **PhpSpreadsheet**.

### 2. Halaman Publik Warga (Tanpa Login)
- **Cek Info Saldo & Riwayat Transaksi:**  
  Warga cukup memasukkan **Nama Lengkap** atau **Nomor Telepon** terdaftar.  
- **Tampilan Riwayat Lengkap:**  
  Menampilkan daftar setoran dan penarikan saldo.  
- **Keamanan Data:**  
  Informasi sensitif seperti username internal dan password tidak ditampilkan.

---

## Keamanan & Fitur Umum

- **Desain Responsif:** Menggunakan **Tailwind CSS**, nyaman diakses dari desktop maupun smartphone.  
- **Perlindungan SQL Injection:** Semua query menggunakan `mysqli_prepare()`.  
- **Pencegahan XSS:** Sanitasi output menggunakan `htmlspecialchars()`.  
- **Password Hashing:**  
  Password disimpan menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.  
- **Struktur Modular:** Logika, tampilan, dan konfigurasi terpisah dengan baik.  
- **Instalasi Otomatis:**  
  Tersedia `install.php` untuk setup database hanya dengan satu klik.

---

## Tech Stack

| Komponen | Teknologi |
|-----------|------------|
| **Backend** | PHP 8.0+ (Native) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5 + Tailwind CSS v3 (via CDN) |
| **Library** | PhpSpreadsheet (ekspor Excel, sudah termasuk) |
| **Ikon** | Font Awesome (via CDN) |

---

## Panduan Instalasi

### 1. Prasyarat
- XAMPP / WAMP / MAMP / LAMP sudah terinstal.  
- PHP versi 8.0 atau lebih tinggi.  
- Akses ke **phpMyAdmin**.

### 2. Ekstrak Proyek
1. Unduh file `.zip` proyek.  
2. Ekstrak ke dalam folder `htdocs` (contoh: `C:\xampp\htdocs\aplikasi-bank-sampah`).  
3. Folder `libs/vendor` sudah berisi semua library, tidak perlu instalasi tambahan.

### 3. Konfigurasi Database
Edit file `config/database.php` dan ubah URL dasar agar sesuai:
```php
define('BASE_URL', 'http://localhost/aplikasi-bank-sampah/');
```

### 4. Setup Database

#### Metode A: Instalasi Otomatis (Disarankan)
1. Pastikan file `banksampah.sql` ada di folder root.  
2. Buka file `install.php` dan sesuaikan:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = '';
   $db_name = 'db_banksampah';
   ```
3. Jalankan di browser:
   ```
   http://localhost/aplikasi-bank-sampah/install.php
   ```
4. Klik **"Mulai Instalasi Sekarang"**.  
5. Setelah berhasil, **hapus file install.php** untuk keamanan.

#### Metode B: Instalasi Manual
1. Buka **phpMyAdmin** → buat database baru `db_banksampah`.  
2. Pilih database tersebut → klik tab **Import**.  
3. Pilih file `banksampah.sql` → klik **Import**.

### 5. Jalankan Aplikasi
Akses URL yang telah Anda tentukan:
```
http://localhost/aplikasi-bank-sampah/
```

---

## Akun Default

| Peran | Username | Password |
|--------|-----------|-----------|
| **Admin** | admin | admin123 |
| **Petugas** | petugas1 | petugas123 |
| **Contoh Warga 1** | No. HP: 081234567001 | (Budi Santoso) |
| **Contoh Warga 2** | No. HP: 081234567002 | (Siti Aminah) |

---

## Struktur Folder

```
aplikasi-bank-sampah/
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar_admin.php
│   └── sidebar_petugas.php
│
├── libs/
│   └── vendor/
│       ├── autoload.php
│       └── ...
│
├── modules/
│   ├── auth/
│   ├── dashboard/
│   ├── jenis_sampah/
│   ├── laporan/
│   ├── profil/
│   ├── transaksi/
│   └── warga/
│
├── index.php
├── cek_info_warga.php
├── install.php
├── banksampah.sql
└── README.md
```

---

## Kontribusi

Kontribusi sangat terbuka bagi siapa pun.  
Langkah-langkah untuk berkontribusi:

1. Fork repositori ini.  
2. Buat branch fitur baru:
   ```bash
   git checkout -b fitur/fitur-baru
   ```
3. Commit perubahan Anda:
   ```bash
   git commit -m "Menambahkan fitur baru"
   ```
4. Push ke branch:
   ```bash
   git push origin fitur/fitur-baru
   ```
5. Buat **Pull Request** atau buka **Issue** untuk pelaporan bug dan saran fitur.

---

## Lisensi

Proyek ini menggunakan **Lisensi MIT**.  
Anda bebas menggunakan, memodifikasi, dan mendistribusikan kode dengan mencantumkan atribusi kepada pembuat aslinya.
