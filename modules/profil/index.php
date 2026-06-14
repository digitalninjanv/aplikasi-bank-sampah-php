<?php
check_user_level(['admin', 'petugas', 'warga']);

$user_id = $_SESSION['user_id'];

$query_user = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon, saldo, level, foto FROM pengguna WHERE id_pengguna = ?";
$stmt_user = mysqli_prepare($koneksi, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

if (!$user_data) {
    $error_message_content = "Gagal memuat data pengguna. Sesi mungkin bermasalah.";
    $_SESSION['error_message'] = $error_message_content;
    error_log("Profil: User data not found for user_id: " . $user_id);

    if (session_status() == PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    if (!headers_sent()) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error_message_for_login_redirect'] = "Sesi Anda bermasalah. Silakan login kembali.";
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=sesi_user_corrupt_profil');
    } else {
        echo "<div class='container mx-auto mt-10 p-6 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow text-center'>";
        echo "<h1 class='text-2xl font-bold mb-2'><i class='fas fa-exclamation-triangle mr-2'></i>Error Sesi</h1>";
        echo "<p>" . htmlspecialchars($error_message_content) . "</p>";
        echo "<p class='mt-2'>Silakan coba <a href='" . BASE_URL . "index.php?page=auth/login&pesan=sesi_user_corrupt_profil_manual' class='text-blue-600 hover:text-blue-800 underline font-semibold'>login kembali</a>.</p>";
        echo "</div>";
        return;
    }
}
?>

<div class="max-w-6xl mx-auto">
    <div class="page-header">
        <h1>Profil Saya</h1>
        <p>Kelola informasi akun dan password Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card p-6">
            <div class="text-center mb-5">
                <?php $foto_profil = $user_data['foto'] ?? ''; ?>
                <?php if ($foto_profil && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'uploads/foto_profil/' . $foto_profil)): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/foto_profil/<?php echo htmlspecialchars($foto_profil); ?>" class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-slate-200 shadow" alt="Foto profil">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-full mx-auto bg-blue-100 text-blue-600 flex items-center justify-center text-4xl font-bold shadow-inner">
                        <?php echo strtoupper(substr($user_data['nama_lengkap'], 0, 2)); ?>
                    </div>
                <?php endif; ?>
                <h2 class="text-xl font-bold font-['Poppins'] mt-3 text-slate-900"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></h2>
                <p class="text-sm text-slate-500">@<?php echo htmlspecialchars($user_data['username']); ?></p>
                <span class="mt-2 inline-block badge <?php echo $user_data['level'] == 'admin' ? 'badge-red' : ($user_data['level'] == 'petugas' ? 'badge-amber' : 'badge-green'); ?>">
                    <?php echo htmlspecialchars($user_data['level']); ?>
                </span>
            </div>
            <hr class="border-slate-200 mb-4">
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-blue-500 mt-0.5 w-4"></i>
                    <span class="text-slate-600"><?php echo htmlspecialchars($user_data['alamat'] ? $user_data['alamat'] : 'Belum diisi'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-phone text-blue-500 w-4"></i>
                    <span class="text-slate-600"><?php echo htmlspecialchars($user_data['no_telepon'] ? $user_data['no_telepon'] : 'Belum diisi'); ?></span>
                </div>
            </div>
            <?php if ($user_data['level'] == 'warga'): ?>
                <hr class="border-slate-200 my-4">
                <div>
                    <h3 class="font-semibold text-slate-700 mb-2 text-sm">Informasi Saldo</h3>
                    <div class="bg-emerald-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-emerald-700">Saldo Anda saat ini:</p>
                        <p class="text-2xl font-bold font-['Poppins'] text-emerald-600"><?php echo format_rupiah($user_data['saldo']); ?></p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>index.php?page=laporan/riwayat_warga" class="btn btn-primary w-full mt-3 justify-center">
                        <i class="fas fa-history"></i> Lihat Riwayat Transaksi
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold font-['Poppins'] text-slate-800 mb-5 pb-3 border-b border-slate-200">Ubah Informasi Profil</h2>
                <form action="<?php echo BASE_URL; ?>index.php?page=profil/proses_update_profil" method="POST" enctype="multipart/form-data" x-data="{ loading: false }" x-on:submit="loading = true">
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required class="form-input">
                        </div>
                        <div>
                            <label for="username_profil" class="form-label">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="username_profil" value="<?php echo htmlspecialchars($user_data['username']); ?>" required class="form-input">
                        </div>
                        <div>
                            <label for="no_telepon_profil" class="form-label">No. Telepon</label>
                            <input type="tel" name="no_telepon" id="no_telepon_profil" value="<?php echo htmlspecialchars($user_data['no_telepon']); ?>" class="form-input">
                        </div>
                        <div>
                            <label for="foto" class="form-label">Foto Profil</label>
                            <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg,image/gif" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-slate-400 mt-1">Maks 2MB. Format: JPG, PNG, GIF.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="alamat_profil" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat_profil" rows="2" class="form-input"><?php echo htmlspecialchars($user_data['alamat']); ?></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="update_profil" :disabled="loading" class="btn btn-primary">
                            <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan Profil'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-semibold font-['Poppins'] text-slate-800 mb-5 pb-3 border-b border-slate-200">Ubah Password</h2>
                <form action="<?php echo BASE_URL; ?>index.php?page=profil/proses_ganti_password" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4 mb-4">
                        <div>
                            <label for="password_lama" class="form-label">Password Lama <span class="text-red-500">*</span></label>
                            <input type="password" name="password_lama" id="password_lama" required class="form-input">
                        </div>
                        <div>
                            <label for="password_baru" class="form-label">Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="password_baru" id="password_baru" required class="form-input">
                        </div>
                        <div>
                            <label for="konfirmasi_password_baru" class="form-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="konfirmasi_password_baru" id="konfirmasi_password_baru" required class="form-input">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="ganti_password" :disabled="loading" class="btn btn-warning">
                            <i class="fas fa-key"></i> <span x-text="loading ? 'Menyimpan...' : 'Ganti Password'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
