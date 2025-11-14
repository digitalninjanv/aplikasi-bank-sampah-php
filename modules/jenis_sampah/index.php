<?php
// modules/jenis_sampah/index.php
check_user_level(['admin', 'petugas']); // Hanya admin dan petugas

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
if (!empty($search)) {
    $query_condition = " WHERE nama_sampah LIKE '%$search%' OR deskripsi LIKE '%$search%'";
}

$query = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg, deskripsi, satuan FROM jenis_sampah $query_condition ORDER BY nama_sampah ASC";
$result = mysqli_query($koneksi, $query);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Data Jenis Sampah</h1>
        <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
            <i class="fas fa-plus mr-2"></i> Tambah Jenis Sampah
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-6">
        <input type="hidden" name="page" value="jenis_sampah/data">
        <div class="flex">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari jenis sampah..." class="w-full px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold px-4 py-2 rounded-r-lg transition duration-150">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Sampah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga/Satuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['nama_sampah']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo format_rupiah($row['harga_per_kg']); ?> / <?php echo htmlspecialchars($row['satuan']);?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate" title="<?php echo htmlspecialchars($row['deskripsi']); ?>"><?php echo htmlspecialchars($row['deskripsi'] ? $row['deskripsi'] : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/edit&id=<?php echo $row['id_jenis_sampah']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/hapus&id=<?php echo $row['id_jenis_sampah']; ?>" 
                                   class="text-red-600 hover:text-red-900" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus jenis sampah ini? Ini mungkin mempengaruhi data transaksi yang ada.');"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                Tidak ada data jenis sampah ditemukan.
                                <?php if(!empty($search)): ?>
                                    <br>Coba kata kunci lain atau <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="text-sky-500 hover:underline">tampilkan semua jenis sampah</a>.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
