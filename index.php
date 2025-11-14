<?php
// index.php (Router Utama)

// Memuat file konfigurasi utama yang berisi koneksi database,
// fungsi-fungsi dasar (seperti redirect), dan memulai session.
require_once 'config/database.php'; 

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

    // Laporan
    'laporan/harian' => 'modules/laporan/harian.php',
    'laporan/bulanan' => 'modules/laporan/bulanan.php',
    'laporan/riwayat_warga' => 'modules/laporan/riwayat_warga.php',
    'laporan/export' => 'modules/laporan/export_handler.php', // Rute untuk handler ekspor Excel
];

// Mendapatkan halaman yang diminta dari URL. Default ke halaman login.
$page = isset($_GET['page']) ? $_GET['page'] : 'auth/login'; 

// Pengecekan status login
// Jika sudah login dan mencoba akses halaman login, arahkan ke dashboard.
if (is_logged_in() && $page === 'auth/login') {
    redirect(BASE_URL . 'index.php?page=dashboard');
}

// Jika belum login dan mencoba akses halaman selain halaman login/proses login, paksa ke halaman login.
// Ini adalah pengaman utama untuk halaman-halaman yang memerlukan otentikasi.
if (!is_logged_in() && $page !== 'auth/login' && $page !== 'auth/proses_login') {
    redirect(BASE_URL . 'index.php?page=auth/login&pesan=harus_login');
}

// Memuat file halaman yang sesuai berdasarkan rute
if (array_key_exists($page, $allowed_pages)) {
    $page_file = $allowed_pages[$page];
    if (file_exists($page_file)) {
        
        // Daftar halaman yang TIDAK memerlukan layout header dan footer.
        // Ini adalah file-file proses yang hanya berisi logika PHP, redirect, atau menghasilkan file (seperti ekspor).
        $no_layout_pages = [
            'auth/proses_login', 
            'auth/logout', 
            'warga/proses_simpan', 'warga/hapus', 
            'jenis_sampah/proses_simpan', 'jenis_sampah/hapus', 
            'transaksi/proses_setor', 'transaksi/proses_tarik',
            'profil/proses_update_profil', 'profil/proses_ganti_password',
            'laporan/export' // Halaman handler ekspor Excel tidak memerlukan layout
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
        // Handle Error 404: File untuk rute yang ada tidak ditemukan di server.
        http_response_code(404);
        require_once 'includes/header.php';
        echo "<div class='container mx-auto mt-10 p-6 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg text-center'>";
        echo "<h1 class='text-2xl font-bold'>Error 404 - File Not Found</h1>";
        echo "<p>File untuk halaman yang Anda minta ('".htmlspecialchars($page)."') tidak dapat ditemukan di server.</p>";
        echo "<p class='mt-2'>Path yang dicari: ".htmlspecialchars($page_file)."</p>";
        echo "<a href='".BASE_URL."index.php?page=dashboard' class='text-blue-600 hover:underline mt-4 inline-block'>Kembali ke Dashboard</a>";
        echo "</div>";
        require_once 'includes/footer.php';
        exit();
    }
} else {
    // Handle Error 403: Rute yang diminta tidak terdaftar di $allowed_pages.
    http_response_code(403);
    require_once 'includes/header.php';
    echo "<div class='container mx-auto mt-10 p-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded-lg text-center'>";
    echo "<h1 class='text-2xl font-bold'>Error 403 - Forbidden</h1>";
    echo "<p>Akses ditolak atau halaman ('".htmlspecialchars($page)."') tidak valid.</p>";
    echo "<a href='".BASE_URL."index.php?page=dashboard' class='text-blue-600 hover:underline mt-4 inline-block'>Kembali ke Dashboard</a>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit();
}
?>
