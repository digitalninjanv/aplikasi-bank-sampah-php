<?php
// modules/laporan/harian.php (Hanya untuk tampilan)
check_user_level(['admin', 'petugas']);

$tanggal_laporan = isset($_GET['tanggal']) ? sanitize_input($_GET['tanggal']) : date('Y-m-d');

// Query untuk mengambil data setoran harian
$query_setoran_harian = "
    SELECT 
        t.id_transaksi, 
        p_warga.nama_lengkap AS nama_warga, 
        p_petugas.nama_lengkap AS nama_petugas, 
        t.tanggal_transaksi, 
        t.total_nilai,
        GROUP_CONCAT(DISTINCT CONCAT(js.nama_sampah, ' (', ds.berat_kg, ' ', js.satuan, ')') SEPARATOR '; ') AS detail_item_setoran,
        t.keterangan AS keterangan_transaksi
    FROM transaksi t
    JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna
    JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna
    LEFT JOIN detail_setoran ds ON t.id_transaksi = ds.id_transaksi_setor
    LEFT JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
    WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'setor'
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal_transaksi DESC
";
$stmt_setoran = mysqli_prepare($koneksi, $query_setoran_harian);
mysqli_stmt_bind_param($stmt_setoran, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_setoran);
$result_setoran = mysqli_stmt_get_result($stmt_setoran);

// Query untuk mengambil data penarikan harian
$query_penarikan_harian = "
    SELECT 
        t.id_transaksi, 
        p_warga.nama_lengkap AS nama_warga, 
        p_petugas.nama_lengkap AS nama_petugas, 
        t.tanggal_transaksi, 
        t.total_nilai,
        t.keterangan AS keterangan_transaksi
    FROM transaksi t
    JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna
    JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna
    WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'tarik_saldo'
    ORDER BY t.tanggal_transaksi DESC
";
$stmt_penarikan = mysqli_prepare($koneksi, $query_penarikan_harian);
mysqli_stmt_bind_param($stmt_penarikan, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_penarikan);
$result_penarikan = mysqli_stmt_get_result($stmt_penarikan);

// Hitung total pemasukan
$total_pemasukan_hari_ini = 0;
$query_total_pemasukan = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'setor'";
$stmt_total_pemasukan = mysqli_prepare($koneksi, $query_total_pemasukan);
mysqli_stmt_bind_param($stmt_total_pemasukan, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_total_pemasukan);
$result_total_pemasukan = mysqli_stmt_get_result($stmt_total_pemasukan);
if($data_total_pemasukan = mysqli_fetch_assoc($result_total_pemasukan)) {
    $total_pemasukan_hari_ini = $data_total_pemasukan['total'] ?: 0;
}
mysqli_stmt_close($stmt_total_pemasukan);

// Hitung total pengeluaran
$total_pengeluaran_hari_ini = 0;
$query_total_pengeluaran = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'tarik_saldo'";
$stmt_total_pengeluaran = mysqli_prepare($koneksi, $query_total_pengeluaran);
mysqli_stmt_bind_param($stmt_total_pengeluaran, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_total_pengeluaran);
$result_total_pengeluaran = mysqli_stmt_get_result($stmt_total_pengeluaran);
if($data_total_pengeluaran = mysqli_fetch_assoc($result_total_pengeluaran)) {
    $total_pengeluaran_hari_ini = $data_total_pengeluaran['total'] ?: 0;
}
mysqli_stmt_close($stmt_total_pengeluaran);

?>
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Harian</h1>
        <div class="flex flex-col sm:flex-row items-center gap-2">
            <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex items-center space-x-2">
                <input type="hidden" name="page" value="laporan/harian">
                <label for="tanggal_laporan_input" class="text-sm font-medium text-gray-700 whitespace-nowrap">Pilih Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal_laporan_input" value="<?php echo $tanggal_laporan; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-3 rounded-lg shadow-md text-sm">
                    <i class="fas fa-search"></i> <span class="hidden sm:inline">Tampilkan</span>
                </button>
            </form>
            <!-- Tombol Ekspor diubah untuk mengarah ke export_handler.php -->
            <a href="<?php echo BASE_URL; ?>index.php?page=laporan/export&report_type=harian&tanggal=<?php echo $tanggal_laporan; ?>"
               class="bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2 px-3 rounded-lg shadow-md text-sm flex items-center w-full sm:w-auto justify-center">
                <i class="fas fa-file-excel mr-2"></i> Ekspor ke Excel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-green-100 p-6 rounded-xl shadow-lg text-green-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider">Total Pemasukan</p>
                    <p class="text-3xl font-bold"><?php echo format_rupiah($total_pemasukan_hari_ini); ?></p>
                </div>
                <i class="fas fa-arrow-down fa-2x opacity-50"></i>
            </div>
        </div>
        <div class="bg-red-100 p-6 rounded-xl shadow-lg text-red-800">
             <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider">Total Pengeluaran</p>
                    <p class="text-2xl font-bold"><?php echo format_rupiah($total_pengeluaran_hari_ini); ?></p>
                </div>
                <i class="fas fa-arrow-up fa-2x opacity-50"></i>
            </div>
        </div>
        <div class="bg-blue-100 p-6 rounded-xl shadow-lg text-blue-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider">Selisih</p>
                    <p class="text-2xl font-bold"><?php echo format_rupiah($total_pemasukan_hari_ini - $total_pengeluaran_hari_ini); ?></p>
                </div>
                 <i class="fas fa-balance-scale fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Setoran -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-8">
        <h2 class="text-xl font-semibold text-gray-700 p-4 bg-gray-50 border-b">
            <i class="fas fa-arrow-down-wide-short mr-2 text-green-500"></i>Detail Setoran Tanggal <?php echo format_tanggal_indonesia($tanggal_laporan, false); ?>
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Warga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Petugas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Detail Item</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_setoran && mysqli_num_rows($result_setoran) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result_setoran)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-md">
                                <?php 
                                $items = explode('; ', $row['detail_item_setoran']);
                                if (!empty($row['detail_item_setoran'])) {
                                    echo "<ul class='list-disc list-inside text-xs'>";
                                    foreach ($items as $item) {
                                        echo "<li>" . htmlspecialchars($item) . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "-";
                                }
                                if(!empty($row['keterangan_transaksi'])) echo "<p class='mt-1 text-xs italic'>Ket: " . htmlspecialchars($row['keterangan_transaksi']) . "</p>";
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-right font-medium"><?php echo format_rupiah($row['total_nilai']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada data setoran pada tanggal ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($stmt_setoran) mysqli_stmt_close($stmt_setoran); ?>

    <!-- Tabel Penarikan -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <h2 class="text-xl font-semibold text-gray-700 p-4 bg-gray-50 border-b">
            <i class="fas fa-arrow-up-short-wide mr-2 text-red-500"></i>Detail Penarikan Saldo Tanggal <?php echo format_tanggal_indonesia($tanggal_laporan, false); ?>
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                 <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Warga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Petugas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Jumlah Ditarik</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_penarikan && mysqli_num_rows($result_penarikan) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result_penarikan)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-md"><?php echo htmlspecialchars($row['keterangan_transaksi'] ? $row['keterangan_transaksi'] : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-right font-medium"><?php echo format_rupiah($row['total_nilai']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada data penarikan pada tanggal ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($stmt_penarikan) mysqli_stmt_close($stmt_penarikan); ?>
</div>
