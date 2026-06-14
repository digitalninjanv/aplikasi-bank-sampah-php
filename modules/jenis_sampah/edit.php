<?php
check_user_level(['admin', 'petugas']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Jenis Sampah tidak valid.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}

$id_jenis_sampah = sanitize_input($_GET['id']);
$query = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg, deskripsi, satuan FROM jenis_sampah WHERE id_jenis_sampah = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_jenis_sampah);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$jenis_sampah = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$jenis_sampah) {
    $_SESSION['error_message'] = "Data jenis sampah tidak ditemukan.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}
?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Edit Jenis Sampah</h1>
        <p>Perbarui data jenis sampah yang sudah ada</p>
    </div>

    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/proses_simpan" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_jenis_sampah" value="<?php echo htmlspecialchars($jenis_sampah['id_jenis_sampah']); ?>">
            <div class="space-y-5">
                <div>
                    <label for="nama_sampah" class="form-label">Nama Jenis Sampah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sampah" id="nama_sampah" value="<?php echo htmlspecialchars($jenis_sampah['nama_sampah']); ?>" required class="form-input">
                </div>
                <div>
                    <label for="harga_per_kg" class="form-label">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" value="<?php echo htmlspecialchars($jenis_sampah['harga_per_kg']); ?>" required step="50" min="0" class="form-input">
                </div>
                <div>
                    <label for="satuan" class="form-label">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="satuan" id="satuan" required value="<?php echo htmlspecialchars($jenis_sampah['satuan']); ?>" class="form-input" placeholder="Contoh: kg, buah, liter">
                </div>
                <div>
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-input"><?php echo htmlspecialchars($jenis_sampah['deskripsi']); ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="btn btn-outline">Batal</a>
                <button type="submit" name="update_jenis_sampah" :disabled="loading" class="btn btn-primary">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Update Jenis Sampah'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
