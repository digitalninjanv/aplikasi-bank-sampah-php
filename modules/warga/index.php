<?php
// modules/warga/index.php
// File ini untuk menampilkan daftar warga
check_user_level(['admin', 'petugas']); // Hanya admin dan petugas yang bisa akses

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
$params_type = "";
$params_value = [];

// Hanya mencari warga
$base_condition = "level = 'warga'";

if (!empty($search)) {
    $search_term = "%" . $search . "%";
    // Pencarian tetap bisa mencakup username meskipun tidak ditampilkan, karena username = no_telepon bersih
    $query_condition = " AND (nama_lengkap LIKE ? OR username LIKE ? OR alamat LIKE ? OR no_telepon LIKE ?)";
    $params_type = "ssss"; 
    for ($i = 0; $i < substr_count($params_type, 's'); $i++) {
        $params_value[] = $search_term;
    }
}

// Kolom yang dipilih disesuaikan, username tetap dipilih untuk logika internal jika diperlukan, tapi tidak ditampilkan
$query_string = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon, saldo, tanggal_daftar 
                 FROM pengguna 
                 WHERE $base_condition $query_condition 
                 ORDER BY nama_lengkap ASC";

$stmt = mysqli_prepare($koneksi, $query_string);

if (!empty($search) && $stmt) {
    mysqli_stmt_bind_param($stmt, $params_type, ...$params_value);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    error_log("MySQLi prepare error in warga/index.php: " . mysqli_error($koneksi));
    $result = false; 
}

?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Data Warga Terdaftar</h1>
        <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="w-full sm:w-auto bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out flex items-center justify-center text-sm">
            <i class="fas fa-user-plus mr-2"></i> Tambah Warga Baru
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-6">
        <input type="hidden" name="page" value="warga/data">
        <div class="flex">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari berdasarkan nama, no. telepon, atau alamat..." class="w-full px-4 py-2.5 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
            <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold px-4 py-2.5 rounded-r-lg transition duration-150 text-sm">
                <i class="fas fa-search"></i> <span class="hidden sm:inline">Cari</span>
            </button>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Nama Lengkap</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">No. Telepon</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Alamat</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $no++; ?></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($row['alamat']); ?>"><?php echo htmlspecialchars($row['alamat'] ? $row['alamat'] : '-'); ?></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>index.php?page=warga/edit&id=<?php echo $row['id_pengguna']; ?>" class="text-sky-600 hover:text-sky-800 mr-3 transition-colors duration-150" title="Edit Data Warga">
                                    <i class="fas fa-edit"></i> <span class="hidden sm:inline">Edit</span>
                                </a>
                                <a href="<?php echo BASE_URL; ?>index.php?page=warga/hapus&id=<?php echo $row['id_pengguna']; ?>" 
                                   class="text-red-600 hover:text-red-800 transition-colors duration-150" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus warga ini? Semua data transaksi terkait juga akan terhapus.');" title="Hapus Warga">
                                   <i class="fas fa-trash"></i> <span class="hidden sm:inline">Hapus</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500"> {/* Colspan disesuaikan menjadi 5 */}
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users-slash fa-3x text-gray-400 mb-3"></i>
                                    <?php if(!empty($search)): ?>
                                        Tidak ada data warga ditemukan dengan kata kunci "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                        <br>Coba kata kunci lain atau <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="text-sky-500 hover:underline mt-2">tampilkan semua warga</a>.
                                    <?php else: ?>
                                        Belum ada data warga terdaftar.
                                        <br><a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="text-sky-500 hover:underline mt-2">Tambahkan warga baru sekarang.</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
?>
