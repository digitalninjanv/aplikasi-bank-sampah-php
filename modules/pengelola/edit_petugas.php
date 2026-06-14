<?php
check_user_level(['admin']);
if (!isset($_GET['id']) || empty($_GET['id'])) { $_SESSION['error_message'] = "ID tidak valid."; redirect(BASE_URL . 'index.php?page=pengelola/petugas'); }
$id = sanitize_input($_GET['id']);
$query = "SELECT id_pengguna, nama_lengkap, username, no_telepon, alamat, status FROM pengguna WHERE id_pengguna = ? AND level = 'petugas'";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$petugas = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$petugas) { $_SESSION['error_message'] = "Petugas tidak ditemukan."; redirect(BASE_URL . 'index.php?page=pengelola/petugas'); }
?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Edit Petugas</h1>
        <p>Perbarui data petugas yang sudah terdaftar</p>
    </div>
    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=pengelola/proses_simpan_petugas" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_pengguna" value="<?php echo htmlspecialchars($petugas['id_pengguna']); ?>">
            <div class="space-y-5">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($petugas['nama_lengkap']); ?>" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($petugas['username']); ?>" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Password Baru <span class="text-slate-400">(kosongkan jika tidak diubah)</span></label>
                    <input type="text" name="password" class="form-input">
                </div>
                <div>
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($petugas['no_telepon'] ?: ''); ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" rows="3" class="form-input"><?php echo htmlspecialchars($petugas['alamat'] ?: ''); ?></textarea>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="aktif" <?php echo ($petugas['status'] === 'aktif' || $petugas['status'] === NULL) ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo $petugas['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=pengelola/petugas" class="btn btn-outline">Batal</a>
                <button type="submit" name="update_petugas" :disabled="loading" class="btn btn-primary">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Update'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
