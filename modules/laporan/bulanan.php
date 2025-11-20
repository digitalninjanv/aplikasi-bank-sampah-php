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

$data_bulanan = [];
$grand_total_setoran = 0;
$grand_total_penarikan = 0;
if ($result_bulanan) {
    while ($row = mysqli_fetch_assoc($result_bulanan)) {
        $row['total_nilai_setoran'] = $row['total_nilai_setoran'] ?: 0;
        $row['total_nilai_penarikan'] = $row['total_nilai_penarikan'] ?: 0;
        $grand_total_setoran += $row['total_nilai_setoran'];
        $grand_total_penarikan += $row['total_nilai_penarikan'];
        $data_bulanan[] = $row;
    }
    mysqli_free_result($result_bulanan);
}

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
    <div class="bg-gradient-to-br from-indigo-500/10 via-sky-500/10 to-cyan-500/10 border border-indigo-100 rounded-2xl p-5 sm:p-6 shadow-xl mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">Laporan Bulanan</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Rekap Keuangan <?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?></h1>
                <p class="text-sm text-gray-600 mt-1">Pantau total pemasukan, pengeluaran, dan selisih bersih setiap hari dalam satu bulan.</p>
            </div>
            <span class="inline-flex items-center gap-2 text-xs bg-white px-3 py-1 rounded-full border border-gray-200 text-gray-600"><i class="far fa-calendar"></i><?php echo count($data_bulanan); ?> hari tercatat</span>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-col md:flex-row gap-3 md:items-end">
            <input type="hidden" name="page" value="laporan/bulanan">
            <div class="flex-1">
                <label for="bulan_tahun_input" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pilih Bulan</label>
                <input type="month" name="bulan_tahun" id="bulan_tahun_input" value="<?php echo $bulan_tahun_input; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 text-sm">
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-sky-500 text-white font-semibold shadow-lg hover:shadow-xl transition">
                <i class="fas fa-sync-alt mr-2"></i> Tampilkan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-emerald-500 font-semibold">Total Pemasukan</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pemasukan_bulan_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                    <i class="fas fa-arrow-trend-down"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pengeluaran_bulan_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fas fa-scale-balanced"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-sky-500 font-semibold">Selisih Bersih</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pemasukan_bulan_ini - $total_pengeluaran_bulan_ini); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="hidden md:block bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
            <h2 class="text-lg font-semibold text-gray-700 p-4 bg-gray-50 border-b flex items-center gap-2">
                <i class="fas fa-table text-sky-500"></i>
                Rincian Transaksi Bulan <?php echo format_tanggal_indonesia($bulan_tahun_input."-01", false); ?>
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total Setoran</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total Penarikan</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Selisih Harian</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (!empty($data_bulanan)): ?>
                            <?php foreach($data_bulanan as $row): ?>
                            <?php $selisih_harian = $row['total_nilai_setoran'] - $row['total_nilai_penarikan']; ?>
                            <tr class="hover:bg-sky-50/50">
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-700"><?php echo format_tanggal_indonesia($row['tanggal'], false); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-emerald-600 text-right font-semibold"><?php echo format_rupiah($row['total_nilai_setoran']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-rose-600 text-right font-semibold"><?php echo format_rupiah($row['total_nilai_penarikan']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-800 text-right font-bold"><?php echo format_rupiah($selisih_harian); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 uppercase">Total Bulan Ini</td>
                                <td class="px-4 sm:px-6 py-3 text-sm text-emerald-700 text-right"><?php echo format_rupiah($grand_total_setoran); ?></td>
                                <td class="px-4 sm:px-6 py-3 text-sm text-rose-700 text-right"><?php echo format_rupiah($grand_total_penarikan); ?></td>
                                <td class="px-4 sm:px-6 py-3 text-sm text-sky-700 text-right"><?php echo format_rupiah($grand_total_setoran - $grand_total_penarikan); ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-10 text-sm text-gray-500">Tidak ada data transaksi pada bulan ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-4 md:hidden">
            <?php if (!empty($data_bulanan)): ?>
                <?php foreach($data_bulanan as $row): ?>
                    <?php $selisih_harian = $row['total_nilai_setoran'] - $row['total_nilai_penarikan']; ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-400">Tanggal</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo format_tanggal_indonesia($row['tanggal'], false); ?></p>
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full <?php echo $selisih_harian >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?>">
                                <?php echo $selisih_harian >= 0 ? 'Surplus' : 'Defisit'; ?>
                            </span>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Setoran</span>
                                <span class="font-semibold text-emerald-600"><?php echo format_rupiah($row['total_nilai_setoran']); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Penarikan</span>
                                <span class="font-semibold text-rose-600"><?php echo format_rupiah($row['total_nilai_penarikan']); ?></span>
                            </div>
                            <div class="flex items-center justify-between border-t border-dashed pt-2">
                                <span class="text-gray-500">Selisih</span>
                                <span class="font-bold text-gray-900"><?php echo format_rupiah($selisih_harian); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-md">
                    <i class="fas fa-calendar-times fa-2x text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">Tidak ada data transaksi pada bulan ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php mysqli_stmt_close($stmt_bulanan); ?>
</div>
