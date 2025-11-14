<?php
// modules/laporan/bulanan.php
check_user_level(['admin', 'petugas']);

$bulan_tahun_input = isset($_GET['bulan_tahun']) ? sanitize_input($_GET['bulan_tahun']) : date('Y-m');
// Pastikan format YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $bulan_tahun_input)) {
    $bulan_tahun_input = date('Y-m'); // Default ke bulan ini jika format salah
}
list($tahun, $bulan) = explode('-', $bulan_tahun_input);

// Query untuk mengambil data setoran bulanan
$query_setoran_bulanan = "
    SELECT 
        DATE(t.tanggal_transaksi) as tanggal,
        COUNT(CASE WHEN t.tipe_transaksi = 'setor' THEN t.id_transaksi END) as jumlah_setoran,
        SUM(CASE WHEN t.tipe_transaksi = 'setor' THEN t.total_nilai ELSE 0 END) as total_nilai_setoran,
        SUM(CASE WHEN t.tipe_transaksi = 'tarik_saldo' THEN t.total_nilai ELSE 0 END) as total_nilai_penarikan
    FROM transaksi t
    WHERE YEAR(t.tanggal_transaksi) = ? AND MONTH(t.tanggal_transaksi) = ?
    GROUP BY DATE(t.tanggal_transaksi)
    ORDER BY tanggal ASC
";
$stmt_bulanan = mysqli_prepare($koneksi, $query_setoran_bulanan);
mysqli_stmt_bind_param($stmt_bulanan, "ss", $tahun, $bulan);
mysqli_stmt_execute($stmt_bulanan);
$result_bulanan = mysqli_stmt_get_result($stmt_bulanan);

// Hitung total pemasukan dan pengeluaran untuk bulan yang dipilih
$total_pemasukan_bulan_ini = 0;
$total_pengeluaran_bulan_ini = 0;

$query_summary_bulan = "
    SELECT 
        SUM(CASE WHEN tipe_transaksi = 'setor' THEN total_nilai ELSE 0 END) as total_setor_bulan,
        SUM(CASE WHEN tipe_transaksi = 'tarik_saldo' THEN total_nilai ELSE 0 END) as total_tarik_bulan
    FROM transaksi
    WHERE YEAR(tanggal_transaksi) = ? AND MONTH(tanggal_transaksi) = ?
";
$stmt_summary = mysqli_prepare($koneksi, $query_summary_bulan);
mysqli_stmt_bind_param($stmt_summary, "ss", $tahun, $bulan);
mysqli_stmt_execute($stmt_summary);
$result_summary = mysqli_stmt_get_result($stmt_summary);
if($data_summary = mysqli_fetch_assoc($result_summary)){
    $total_pemasukan_bulan_ini = $data_summary['total_setor_bulan'] ?: 0;
    $total_pengeluaran_bulan_ini = $data_summary['total_tarik_bulan'] ?: 0;
}
mysqli_stmt_close($stmt_summary);

?>
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Bulanan</h1>
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex items-center space-x-2">
            <input type="hidden" name="page" value="laporan/bulanan">
            <label for="bulan_tahun_input" class="text-sm font-medium text-gray-700">Pilih Bulan:</label>
            <input type="month" name="bulan_tahun" id="bulan_tahun_input" value="<?php echo $bulan_tahun_input; ?>" 
                   class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
            <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-3 rounded-lg shadow-md text-sm">
                <i class="fas fa-search"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-green-100 p-6 rounded-xl shadow-lg text-green-800">
            <p class="text-sm font-medium uppercase tracking-wider">Total Pemasukan (<?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?>)</p>
            <p class="text-3xl font-bold"><?php echo format_rupiah($total_pemasukan_bulan_ini); ?></p>
        </div>
        <div class="bg-red-100 p-6 rounded-xl shadow-lg text-red-800">
            <p class="text-sm font-medium uppercase tracking-wider">Total Pengeluaran (<?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?>)</p>
            <p class="text-2xl font-bold"><?php echo format_rupiah($total_pengeluaran_bulan_ini); ?></p>
        </div>
        <div class="bg-blue-100 p-6 rounded-xl shadow-lg text-blue-800">
            <p class="text-sm font-medium uppercase tracking-wider">Selisih (<?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?>)</p>
            <p class="text-2xl font-bold"><?php echo format_rupiah($total_pemasukan_bulan_ini - $total_pengeluaran_bulan_ini); ?></p>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <h2 class="text-xl font-semibold text-gray-700 p-4 bg-gray-50 border-b">
            <i class="fas fa-calendar-alt mr-2 text-sky-500"></i>Rincian Transaksi Bulan <?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?>
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Total Setoran (Rp)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Total Penarikan (Rp)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Selisih Harian (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_bulanan && mysqli_num_rows($result_bulanan) > 0): ?>
                        <?php 
                        $grand_total_setoran = 0;
                        $grand_total_penarikan = 0;
                        ?>
                        <?php while($row = mysqli_fetch_assoc($result_bulanan)): ?>
                        <?php
                        $selisih_harian = $row['total_nilai_setoran'] - $row['total_nilai_penarikan'];
                        $grand_total_setoran += $row['total_nilai_setoran'];
                        $grand_total_penarikan += $row['total_nilai_penarikan'];
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo format_tanggal_indonesia($row['tanggal'], false); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right"><?php echo format_rupiah($row['total_nilai_setoran']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right"><?php echo format_rupiah($row['total_nilai_penarikan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-right font-medium"><?php echo format_rupiah($selisih_harian); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-3 text-left text-sm text-gray-700 uppercase">Total Bulan Ini</td>
                            <td class="px-6 py-3 text-right text-sm text-green-700"><?php echo format_rupiah($grand_total_setoran); ?></td>
                            <td class="px-6 py-3 text-right text-sm text-red-700"><?php echo format_rupiah($grand_total_penarikan); ?></td>
                            <td class="px-6 py-3 text-right text-sm text-blue-700"><?php echo format_rupiah($grand_total_setoran - $grand_total_penarikan); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-gray-500">Tidak ada data transaksi pada bulan ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php mysqli_stmt_close($stmt_bulanan); ?>
</div>
