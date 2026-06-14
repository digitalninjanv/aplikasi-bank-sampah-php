<?php
check_user_level(['admin', 'petugas']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Warga tidak valid.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}

$id_warga = sanitize_input($_GET['id']);
$query = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon FROM pengguna WHERE id_pengguna = ? AND level = 'warga'";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_warga);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$warga = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$warga) {
    $_SESSION['error_message'] = "Data warga tidak ditemukan.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}
?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Edit Data Warga</h1>
        <p>Perbarui data warga yang sudah terdaftar</p>
    </div>

    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=warga/proses_simpan" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_pengguna" value="<?php echo htmlspecialchars($warga['id_pengguna']); ?>">
            <div class="space-y-5">
                <div>
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($warga['nama_lengkap']); ?>" required class="form-input">
                </div>
                <div>
                    <label for="no_telepon" class="form-label">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="no_telepon" id="no_telepon" value="<?php echo htmlspecialchars($warga['no_telepon']); ?>" required class="form-input">
                    <p class="text-xs text-slate-500 mt-1">Mengubah nomor telepon juga akan mengubah username warga.</p>
                </div>
                <div>
                    <label for="alamat" class="form-label">Alamat (Opsional)</label>
                    <textarea name="alamat" id="alamat" rows="3" class="form-input"><?php echo htmlspecialchars($warga['alamat']); ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="btn btn-outline">Batal</a>
                <button type="submit" name="update_warga" :disabled="loading" class="btn btn-primary">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Update Data Warga'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
