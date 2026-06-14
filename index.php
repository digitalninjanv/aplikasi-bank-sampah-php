<?php
// index.php (Router Utama)

// Memuat file konfigurasi utama yang berisi koneksi database,
// fungsi-fungsi dasar (seperti redirect), dan memulai session.
require_once 'config/database.php'; 

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Daftar semua halaman/rute yang valid dalam aplikasi dan file PHP yang sesuai.
$allowed_pages = [
    // Otentikasi
    'auth/login' => 'modules/auth/login.php',
    'auth/proses_login' => 'modules/auth/proses_login.php',
    'auth/logout' => 'modules/auth/logout.php',

    // Dashboard
    'dashboard' => 'modules/dashboard/index.php',

    // Profil Pengguna
    'profil' => 'modules/profil/index.php',
    'profil/proses_update_profil' => 'modules/profil/proses_update_profil.php',
    'profil/proses_ganti_password' => 'modules/profil/proses_ganti_password.php',
    
    // Manajemen Warga (oleh Admin & Petugas)
    'warga/data' => 'modules/warga/index.php',
    'warga/tambah' => 'modules/warga/tambah.php',
    'warga/edit' => 'modules/warga/edit.php',
    'warga/proses_simpan' => 'modules/warga/proses_simpan.php',
    'warga/hapus' => 'modules/warga/hapus.php',

    // Manajemen Jenis Sampah (oleh Admin & Petugas)
    'jenis_sampah/data' => 'modules/jenis_sampah/index.php',
    'jenis_sampah/tambah' => 'modules/jenis_sampah/tambah.php',
    'jenis_sampah/edit' => 'modules/jenis_sampah/edit.php',
    'jenis_sampah/proses_simpan' => 'modules/jenis_sampah/proses_simpan.php',
    'jenis_sampah/hapus' => 'modules/jenis_sampah/hapus.php',

    // Transaksi (oleh Admin & Petugas)
    'transaksi/setor' => 'modules/transaksi/setor.php',
    'transaksi/proses_setor' => 'modules/transaksi/proses_setor.php',
    'transaksi/tarik_saldo' => 'modules/transaksi/tarik_saldo.php',
    'transaksi/proses_tarik' => 'modules/transaksi/proses_tarik.php',
    'transaksi/riwayat' => 'modules/transaksi/riwayat.php',
    'transaksi/struk' => 'modules/transaksi/struk.php',

    // Autentikasi & Registrasi
    'auth/register' => 'modules/auth/register.php',
    'auth/proses_register' => 'modules/auth/proses_register.php',
    'auth/lupa_password' => 'modules/auth/lupa_password.php',
    'auth/proses_lupa_password' => 'modules/auth/proses_lupa_password.php',

    // Laporan
    'laporan/harian' => 'modules/laporan/harian.php',
    'laporan/bulanan' => 'modules/laporan/bulanan.php',
    'laporan/riwayat_warga' => 'modules/laporan/riwayat_warga.php',
    'laporan/rekap_warga' => 'modules/laporan/rekap_warga.php',
    'laporan/export' => 'modules/laporan/export_handler.php',
    'laporan/export_pdf' => 'modules/laporan/export_pdf.php',

    // Manajemen Petugas (Admin only)
    'pengelola/petugas' => 'modules/pengelola/petugas.php',
    'pengelola/tambah_petugas' => 'modules/pengelola/tambah_petugas.php',
    'pengelola/edit_petugas' => 'modules/pengelola/edit_petugas.php',
    'pengelola/proses_simpan_petugas' => 'modules/pengelola/proses_simpan_petugas.php',
    'pengelola/hapus_petugas' => 'modules/pengelola/hapus_petugas.php',

    // Backup Database (Admin only)
    'backup/index' => 'modules/backup/index.php',
    'backup/proses' => 'modules/backup/proses.php',

    // Pengaturan Aplikasi (Admin only)
    'admin/settings' => 'modules/admin/settings.php',

    // Landing Page
    'landing' => 'landing.php',
];

// Mendapatkan halaman yang diminta dari URL.
// Jika tidak ada parameter, tampilkan landing page untuk pengunjung, login untuk yang sudah login.
$page = isset($_GET['page']) ? $_GET['page'] : (is_logged_in() ? 'auth/login' : 'landing');

// Pengecekan status login
// Jika sudah login dan mencoba akses halaman login/landing, arahkan ke dashboard.
if (is_logged_in() && ($page === 'auth/login' || $page === 'landing')) {
    redirect(BASE_URL . 'index.php?page=dashboard');
}

// Halaman publik yang bisa diakses tanpa login
$public_pages = ['auth/login', 'auth/proses_login', 'auth/proses_register', 'auth/register', 'landing'];

// Jika belum login dan mencoba akses halaman non-publik, paksa ke landing page.
if (!is_logged_in() && !in_array($page, $public_pages)) {
    redirect(BASE_URL . 'index.php?page=landing');
}

// Memuat file halaman yang sesuai berdasarkan rute
if (array_key_exists($page, $allowed_pages)) {
    $page_file = $allowed_pages[$page];
    if (file_exists($page_file)) {
        
        // Daftar halaman yang TIDAK memerlukan layout header dan footer.
        // Ini adalah file-file proses yang hanya berisi logika PHP, redirect, atau menghasilkan file (seperti ekspor).
        $no_layout_pages = [
            'landing',
            'auth/proses_login', 'auth/logout',
            'auth/proses_register',
            'auth/proses_lupa_password',
            'warga/proses_simpan', 'warga/hapus',
            'jenis_sampah/proses_simpan', 'jenis_sampah/hapus',
            'transaksi/proses_setor', 'transaksi/proses_tarik',
            'profil/proses_update_profil', 'profil/proses_ganti_password',
            'pengelola/proses_simpan_petugas', 'pengelola/hapus_petugas',
            'laporan/export', 'laporan/export_pdf',
            'backup/proses',
        ];

        // Jika halaman saat ini ada di daftar $no_layout_pages, muat file-nya saja.
        if (in_array($page, $no_layout_pages)) {
            require_once $page_file; 
        } else {
            // Jika tidak, muat layout lengkap: header, konten halaman, dan footer.
            require_once 'includes/header.php'; 
            require_once $page_file;            
            require_once 'includes/footer.php'; 
        }
    } else {
        // Handle Error 404
        http_response_code(404);
        require_once 'includes/header.php';
        ?>
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center max-w-md">
                <div class="text-8xl font-black text-slate-200 mb-4">404</div>
                <h1 class="text-3xl font-bold text-slate-800 mb-3">Halaman Tidak Ditemukan</h1>
                <p class="text-slate-500 mb-8">Maaf, file untuk halaman yang Anda minta tidak tersedia di server.</p>
                <a href="<?php echo BASE_URL; ?>index.php?page=dashboard"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 text-white font-semibold shadow-lg hover:bg-sky-700 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
        <?php
        require_once 'includes/footer.php';
        exit();
    }
} else {
    // Handle Error 403
    http_response_code(403);
    require_once 'includes/header.php';
    ?>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center max-w-md">
            <div class="text-8xl font-black text-slate-200 mb-4">403</div>
            <h1 class="text-3xl font-bold text-slate-800 mb-3">Akses Ditolak</h1>
            <p class="text-slate-500 mb-8">Maaf, halaman yang Anda minta tidak valid atau Anda tidak memiliki izin untuk mengaksesnya.</p>
            <a href="<?php echo BASE_URL; ?>index.php?page=dashboard"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 text-white font-semibold shadow-lg hover:bg-sky-700 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit();
}
?>
