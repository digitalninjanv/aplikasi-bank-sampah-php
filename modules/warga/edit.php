<?php
// modules/warga/edit.php
check_user_level(['admin', 'petugas']); 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Warga tidak valid.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}

$id_warga = sanitize_input($_GET['id']);

// Ambil data warga dari database, termasuk username untuk referensi jika diperlukan
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

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Data Warga</h1>
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-lg mx-auto">
        <form action="<?php echo BASE_URL; ?>index.php?page=warga/proses_simpan" method="POST">
            <input type="hidden" name="id_pengguna" value="<?php echo htmlspecialchars($warga['id_pengguna']); ?>">
            
            <div class="space-y-6">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($warga['nama_lengkap']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                <div>
                    <label for="no_telepon" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="no_telepon" id="no_telepon" value="<?php echo htmlspecialchars($warga['no_telepon']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                     <p class="mt-1 text-xs text-gray-500">Mengubah nomor telepon juga akan mengubah username warga (menjadi nomor telepon baru tanpa spasi/simbol).</p>
                </div>
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat (Opsional)</label>
                    <textarea name="alamat" id="alamat" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"><?php echo htmlspecialchars($warga['alamat']); ?></textarea>
                </div>
                </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" name="update_warga" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i> Update Data Warga
                </button>
            </div>
        </form>
    </div>
</div>

