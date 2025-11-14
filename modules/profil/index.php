<?php
// modules/profil/index.php
check_user_level(['admin', 'petugas', 'warga']); // Semua level bisa akses profilnya

$user_id = $_SESSION['user_id'];

// Ambil data pengguna saat ini untuk ditampilkan
$query_user = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon, saldo, level FROM pengguna WHERE id_pengguna = ?";
$stmt_user = mysqli_prepare($koneksi, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

if (!$user_data) {
    // Jika user_data tidak ditemukan setelah login, ini bisa jadi masalah sesi.
    $error_message_content = "Gagal memuat data pengguna. Sesi mungkin bermasalah (ID Pengguna: " . htmlspecialchars($user_id) . ").";
    $_SESSION['error_message'] = $error_message_content; // Untuk ditampilkan jika redirect gagal total
    error_log("Profil: User data not found for user_id: " . $user_id . ". Forcing logout.");

    // Hancurkan sesi jika data pengguna tidak konsisten
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    if (!headers_sent()) {
        // Headers belum terkirim, kita bisa memulai sesi baru untuk pesan dan redirect
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Mulai sesi baru untuk menyimpan pesan error spesifik untuk halaman login
        }
        // Gunakan key yang berbeda jika pesan dari header.php sudah ada, agar tidak tertimpa
        $_SESSION['error_message_for_login_redirect'] = "Sesi Anda bermasalah atau data pengguna tidak ditemukan. Silakan login kembali.";
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=sesi_user_corrupt_profil');
    } else {
        // Headers sudah terkirim. Tampilkan pesan error inline.
        // Pesan error_message dari session mungkin sudah ditampilkan oleh header.php.
        // Ini adalah fallback jika pesan tersebut belum muncul atau untuk kejelasan.
        echo "<div class='container mx-auto mt-10 p-6 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow text-center'>";
        echo "<h1 class='text-2xl font-bold mb-2'><i class='fas fa-exclamation-triangle mr-2'></i>Error Sesi</h1>";
        echo "<p>" . htmlspecialchars($error_message_content) . "</p>";
        echo "<p class='mt-2'>Silakan coba <a href='" . BASE_URL . "index.php?page=auth/login&pesan=sesi_user_corrupt_profil_manual' class='text-blue-600 hover:text-blue-800 underline font-semibold'>login kembali</a>.</p>";
        echo "</div>";
        // Karena kita sudah menampilkan pesan error dan header.php sudah output,
        // kita perlu menghentikan eksekusi agar footer.php tidak ikut termuat dan merusak tampilan.
        // Namun, karena index.php (router) akan memanggil footer.php setelah ini,
        // cara terbaik adalah membiarkan pesan error dari session yang di-set di atas ditampilkan oleh header.php,
        // dan di sini kita hanya memastikan tidak ada konten profil lebih lanjut yang ditampilkan.
        // Untuk menghentikan render sisa halaman profil dan footer dari router:
        return; // Atau exit(); jika ini adalah akhir dari file yang di-include.
                 // 'return;' akan menghentikan eksekusi file ini, dan router akan lanjut ke footer.
                 // 'exit();' akan menghentikan semua eksekusi.
                 // Dalam konteks di-include oleh router, 'return;' lebih aman jika ada cleanup di router.
                 // Namun, jika ingin menghentikan total, 'exit();' bisa digunakan.
                 // Untuk kasus ini, karena header sudah tercetak, kita mungkin ingin menghentikan total agar footer tidak tercetak.
    }
    // Jika redirect atau echo+exit terjadi di atas, kode di bawah ini tidak akan berjalan.
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Profil Saya</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-lg">
            <div class="text-center mb-6">
                <i class="fas fa-user-circle fa-5x text-sky-500"></i>
                <h2 class="text-2xl font-semibold mt-3"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></h2>
                <p class="text-gray-600">@<?php echo htmlspecialchars($user_data['username']); ?></p>
                <span class="mt-2 inline-block bg-<?php echo ($user_data['level'] == 'admin' ? 'red' : ($user_data['level'] == 'petugas' ? 'yellow' : 'green')); ?>-500 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase">
                    <?php echo htmlspecialchars($user_data['level']); ?>
                </span>
            </div>
            <hr class="my-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Detail Kontak:</h3>
                <p class="text-gray-600 mb-1"><i class="fas fa-map-marker-alt w-5 mr-2 text-sky-500"></i> <?php echo htmlspecialchars($user_data['alamat'] ? $user_data['alamat'] : 'Belum diisi'); ?></p>
                <p class="text-gray-600"><i class="fas fa-phone w-5 mr-2 text-sky-500"></i> <?php echo htmlspecialchars($user_data['no_telepon'] ? $user_data['no_telepon'] : 'Belum diisi'); ?></p>
            </div>
            <?php if ($user_data['level'] == 'warga'): ?>
            <hr class="my-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Informasi Saldo:</h3>
                <div class="bg-green-100 p-4 rounded-lg">
                    <p class="text-sm text-green-700">Saldo Anda saat ini:</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo format_rupiah($user_data['saldo']); ?></p>
                </div>
                 <a href="<?php echo BASE_URL; ?>index.php?page=laporan/riwayat_warga" class="mt-3 block text-center w-full bg-sky-500 hover:bg-sky-600 text-white font-medium py-2 px-4 rounded-lg transition duration-150">
                    <i class="fas fa-history mr-2"></i> Lihat Riwayat Transaksi
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Ubah Informasi Profil</h2>
                <form action="<?php echo BASE_URL; ?>index.php?page=profil/proses_update_profil" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="username_profil" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="username_profil" value="<?php echo htmlspecialchars($user_data['username']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="no_telepon_profil" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="tel" name="no_telepon" id="no_telepon_profil" value="<?php echo htmlspecialchars($user_data['no_telepon']); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                         <div class="md:col-span-2">
                            <label for="alamat_profil" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea name="alamat" id="alamat_profil" rows="2" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"><?php echo htmlspecialchars($user_data['alamat']); ?></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="update_profil" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Ubah Password</h2>
                <form action="<?php echo BASE_URL; ?>index.php?page=profil/proses_ganti_password" method="POST">
                    <div class="space-y-4 mb-4">
                        <div>
                            <label for="password_lama" class="block text-sm font-medium text-gray-700 mb-1">Password Lama <span class="text-red-500">*</span></label>
                            <input type="password" name="password_lama" id="password_lama" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="password_baru" id="password_baru" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="konfirmasi_password_baru" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="konfirmasi_password_baru" id="konfirmasi_password_baru" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="ganti_password" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                            <i class="fas fa-key mr-2"></i> Ganti Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
