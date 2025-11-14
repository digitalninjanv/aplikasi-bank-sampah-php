<?php
// modules/jenis_sampah/edit.php
check_user_level(['admin', 'petugas']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Jenis Sampah tidak valid.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}

$id_jenis_sampah = sanitize_input($_GET['id']);

// Ambil data jenis sampah dari database
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

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Jenis Sampah</h1>
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-lg mx-auto">
        <form action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/proses_simpan" method="POST">
            <input type="hidden" name="id_jenis_sampah" value="<?php echo htmlspecialchars($jenis_sampah['id_jenis_sampah']); ?>">
            
            <div class="space-y-6">
                <div>
                    <label for="nama_sampah" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Sampah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sampah" id="nama_sampah" value="<?php echo htmlspecialchars($jenis_sampah['nama_sampah']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                <div>
                    <label for="harga_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" value="<?php echo htmlspecialchars($jenis_sampah['harga_per_kg']); ?>" required step="50" min="0" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                 <div>
                    <label for="satuan" class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="satuan" id="satuan" required value="<?php echo htmlspecialchars($jenis_sampah['satuan']); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm" placeholder="Contoh: kg, buah, liter">
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"><?php echo htmlspecialchars($jenis_sampah['deskripsi']); ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" name="update_jenis_sampah" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i> Update Jenis Sampah
                </button>
            </div>
        </form>
    </div>
</div>
