<?php
// modules/transaksi/riwayat.php
check_user_level(['admin', 'petugas']);

$filter_warga = isset($_GET['filter_warga']) ? sanitize_input($_GET['filter_warga']) : '';
$filter_tipe = isset($_GET['filter_tipe']) ? sanitize_input($_GET['filter_tipe']) : '';
$filter_tanggal_mulai = isset($_GET['filter_tanggal_mulai']) ? sanitize_input($_GET['filter_tanggal_mulai']) : '';
$filter_tanggal_akhir = isset($_GET['filter_tanggal_akhir']) ? sanitize_input($_GET['filter_tanggal_akhir']) : '';

$conditions = [];
$params_type = "";
$params_value = [];

if (!empty($filter_warga)) {
    $conditions[] = "t.id_warga = ?";
    $params_type .= "i";
    $params_value[] = $filter_warga;
}
if (!empty($filter_tipe)) {
    $conditions[] = "t.tipe_transaksi = ?";
    $params_type .= "s";
    $params_value[] = $filter_tipe;
}
if (!empty($filter_tanggal_mulai)) {
    $conditions[] = "DATE(t.tanggal_transaksi) >= ?";
    $params_type .= "s";
    $params_value[] = $filter_tanggal_mulai;
}
if (!empty($filter_tanggal_akhir)) {
    $conditions[] = "DATE(t.tanggal_transaksi) <= ?";
    $params_type .= "s";
    $params_value[] = $filter_tanggal_akhir;
}

$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

$query_transaksi = "
    SELECT 
        t.id_transaksi, 
        t.tanggal_transaksi, 
        t.tipe_transaksi, 
        t.total_nilai, 
        t.keterangan AS keterangan_transaksi,
        warga.nama_lengkap AS nama_warga, 
        warga.username AS username_warga,
        petugas.nama_lengkap AS nama_petugas
    FROM transaksi t
    JOIN pengguna warga ON t.id_warga = warga.id_pengguna
    JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    $where_clause
    ORDER BY t.tanggal_transaksi DESC
";

$stmt_transaksi = mysqli_prepare($koneksi, $query_transaksi);
if (!empty($params_type) && !empty($params_value)) {
    mysqli_stmt_bind_param($stmt_transaksi, $params_type, ...$params_value);
}
mysqli_stmt_execute($stmt_transaksi);
$result_transaksi = mysqli_stmt_get_result($stmt_transaksi);

// Ambil daftar warga untuk filter
$query_all_warga = "SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_all_warga = mysqli_query($koneksi, $query_all_warga);
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Riwayat Semua Transaksi</h1>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-6 bg-white p-4 rounded-lg shadow">
        <input type="hidden" name="page" value="transaksi/riwayat">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter_warga" class="block text-sm font-medium text-gray-700">Warga:</label>
                <select name="filter_warga" id="filter_warga" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                    <option value="">Semua Warga</option>
                    <?php while($w = mysqli_fetch_assoc($result_all_warga)): ?>
                    <option value="<?php echo $w['id_pengguna']; ?>" <?php echo ($filter_warga == $w['id_pengguna']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($w['nama_lengkap'] . ' (' . $w['username'] . ')'); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="filter_tipe" class="block text-sm font-medium text-gray-700">Tipe Transaksi:</label>
                <select name="filter_tipe" id="filter_tipe" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="setor" <?php echo ($filter_tipe == 'setor') ? 'selected' : ''; ?>>Setor Sampah</option>
                    <option value="tarik_saldo" <?php echo ($filter_tipe == 'tarik_saldo') ? 'selected' : ''; ?>>Tarik Saldo</option>
                </select>
            </div>
            <div>
                <label for="filter_tanggal_mulai" class="block text-sm font-medium text-gray-700">Dari Tanggal:</label>
                <input type="date" name="filter_tanggal_mulai" id="filter_tanggal_mulai" value="<?php echo htmlspecialchars($filter_tanggal_mulai); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
            </div>
            <div>
                <label for="filter_tanggal_akhir" class="block text-sm font-medium text-gray-700">Sampai Tanggal:</label>
                <input type="date" name="filter_tanggal_akhir" id="filter_tanggal_akhir" value="<?php echo htmlspecialchars($filter_tanggal_akhir); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
            </div>
            <div class="lg:col-start-4">
                <button type="submit" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Trans.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai (Rp)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail/Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0): ?>
                        <?php while($trx = mysqli_fetch_assoc($result_transaksi)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#<?php echo $trx['id_transaksi']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($trx['nama_warga']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Setor Sampah</span>
                                <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Tarik Saldo</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-sm">
                                <?php 
                                if ($trx['tipe_transaksi'] == 'setor') {
                                    // Ambil detail item setoran
                                    $query_detail_items = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai 
                                                           FROM detail_setoran ds 
                                                           JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah 
                                                           WHERE ds.id_transaksi_setor = ?";
                                    $stmt_items = mysqli_prepare($koneksi, $query_detail_items);
                                    mysqli_stmt_bind_param($stmt_items, "i", $trx['id_transaksi']);
                                    mysqli_stmt_execute($stmt_items);
                                    $result_items = mysqli_stmt_get_result($stmt_items);
                                    if(mysqli_num_rows($result_items) > 0){
                                        echo "<ul class='list-disc list-inside text-xs'>";
                                        while($item = mysqli_fetch_assoc($result_items)){
                                            echo "<li>" . htmlspecialchars($item['nama_sampah']) . ": " . $item['berat_kg'] . "kg @ " . format_rupiah($item['harga_saat_setor']) . " = " . format_rupiah($item['subtotal_nilai']) . "</li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "Detail item tidak ditemukan.";
                                    }
                                    mysqli_stmt_close($stmt_items);
                                    if(!empty($trx['keterangan_transaksi'])) echo "<p class='mt-1 text-xs italic'>Ket: " . htmlspecialchars($trx['keterangan_transaksi']) . "</p>";

                                } else { // Untuk tarik_saldo atau tipe lain
                                    echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-');
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                Tidak ada data transaksi ditemukan dengan filter yang diterapkan.
                                <br><a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="text-sky-500 hover:underline">Reset filter dan tampilkan semua</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php mysqli_stmt_close($stmt_transaksi); ?>
