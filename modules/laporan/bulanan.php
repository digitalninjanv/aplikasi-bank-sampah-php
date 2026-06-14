<?php
check_user_level(['admin', 'petugas']);

$bulan_tahun_input = isset($_GET['bulan_tahun']) ? sanitize_input($_GET['bulan_tahun']) : date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $bulan_tahun_input)) { $bulan_tahun_input = date('Y-m'); }
list($tahun, $bulan) = explode('-', $bulan_tahun_input);

$query = "SELECT DATE(t.tanggal_transaksi) as tanggal, COUNT(CASE WHEN t.tipe_transaksi = 'setor' THEN t.id_transaksi END) as jumlah_setoran, SUM(CASE WHEN t.tipe_transaksi = 'setor' THEN t.total_nilai ELSE 0 END) as total_nilai_setoran, SUM(CASE WHEN t.tipe_transaksi = 'tarik_saldo' THEN t.total_nilai ELSE 0 END) as total_nilai_penarikan FROM transaksi t WHERE YEAR(t.tanggal_transaksi) = ? AND MONTH(t.tanggal_transaksi) = ? GROUP BY DATE(t.tanggal_transaksi) ORDER BY tanggal ASC";
$stmt_bulanan = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt_bulanan, "ss", $tahun, $bulan);
mysqli_stmt_execute($stmt_bulanan);
$result_bulanan = mysqli_stmt_get_result($stmt_bulanan);
$data_bulanan = []; $grand_total_setoran = 0; $grand_total_penarikan = 0;
if ($result_bulanan) { while ($row = mysqli_fetch_assoc($result_bulanan)) { $row['total_nilai_setoran'] = $row['total_nilai_setoran'] ?: 0; $row['total_nilai_penarikan'] = $row['total_nilai_penarikan'] ?: 0; $grand_total_setoran += $row['total_nilai_setoran']; $grand_total_penarikan += $row['total_nilai_penarikan']; $data_bulanan[] = $row; } mysqli_free_result($result_bulanan); }

$total_pemasukan_bulan_ini = $grand_total_setoran;
$total_pengeluaran_bulan_ini = $grand_total_penarikan;
?>
<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Laporan Bulanan</h1>
        <p>Pantau total pemasukan, pengeluaran, dan selisih bersih setiap hari</p>
    </div>

    <div class="card p-4 sm:p-5 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-col sm:flex-row gap-3 items-end">
            <input type="hidden" name="page" value="laporan/bulanan">
            <div class="flex-1 w-full">
                <label for="bulan_tahun_input" class="form-label">Pilih Bulan</label>
                <input type="month" name="bulan_tahun" id="bulan_tahun_input" value="<?php echo $bulan_tahun_input; ?>" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg"><i class="fas fa-arrow-trend-up"></i></div>
                <div><p class="text-xs uppercase tracking-wider text-emerald-600 font-semibold">Total Pemasukan</p><p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pemasukan_bulan_ini); ?></p></div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg"><i class="fas fa-arrow-trend-down"></i></div>
                <div><p class="text-xs uppercase tracking-wider text-red-600 font-semibold">Total Pengeluaran</p><p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pengeluaran_bulan_ini); ?></p></div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-scale-balanced"></i></div>
                <div><p class="text-xs uppercase tracking-wider text-blue-600 font-semibold">Selisih Bersih</p><p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pemasukan_bulan_ini - $total_pengeluaran_bulan_ini); ?></p></div>
            </div>
        </div>
    </div>

    <div class="table-wrap hidden md:block">
        <table>
            <thead><tr><th>Tanggal</th><th class="text-right">Total Setoran</th><th class="text-right">Total Penarikan</th><th class="text-right">Selisih Harian</th></tr></thead>
            <tbody>
                <?php if (!empty($data_bulanan)): ?>
                    <?php foreach($data_bulanan as $row): ?>
                    <?php $selisih = $row['total_nilai_setoran'] - $row['total_nilai_penarikan']; ?>
                    <tr>
                        <td class="whitespace-nowrap"><?php echo format_tanggal_indonesia($row['tanggal'], false); ?></td>
                        <td class="whitespace-nowrap text-right font-semibold text-emerald-600"><?php echo format_rupiah($row['total_nilai_setoran']); ?></td>
                        <td class="whitespace-nowrap text-right font-semibold text-red-600"><?php echo format_rupiah($row['total_nilai_penarikan']); ?></td>
                        <td class="whitespace-nowrap text-right font-bold text-slate-800"><?php echo format_rupiah($selisih); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-slate-50 font-semibold">
                        <td class="uppercase text-sm">Total Bulan Ini</td>
                        <td class="text-right text-emerald-700"><?php echo format_rupiah($grand_total_setoran); ?></td>
                        <td class="text-right text-red-700"><?php echo format_rupiah($grand_total_penarikan); ?></td>
                        <td class="text-right text-blue-700"><?php echo format_rupiah($grand_total_setoran - $grand_total_penarikan); ?></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="4"><div class="empty-state"><i class="fas fa-calendar-times text-slate-300"></i><p>Tidak ada data transaksi pada bulan ini.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 md:hidden"><?php if (!empty($data_bulanan)): foreach($data_bulanan as $row): $selisih = $row['total_nilai_setoran'] - $row['total_nilai_penarikan']; ?>
        <div class="mobile-card">
            <div class="flex items-center justify-between"><p class="font-semibold"><?php echo format_tanggal_indonesia($row['tanggal'], false); ?></p><span class="badge <?php echo $selisih >= 0 ? 'badge-green' : 'badge-red'; ?>"><?php echo $selisih >= 0 ? 'Surplus' : 'Defisit'; ?></span></div>
            <div class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Setoran</span><span class="font-semibold text-emerald-600"><?php echo format_rupiah($row['total_nilai_setoran']); ?></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Penarikan</span><span class="font-semibold text-red-600"><?php echo format_rupiah($row['total_nilai_penarikan']); ?></span></div>
                <div class="flex justify-between border-t border-dashed pt-1"><span class="text-slate-500">Selisih</span><span class="font-bold"><?php echo format_rupiah($selisih); ?></span></div>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div class="mobile-card text-center py-6"><i class="fas fa-calendar-times text-2xl text-slate-300 mb-2"></i><p class="text-sm text-slate-500">Tidak ada data.</p></div>
    <?php endif; ?></div>
    <?php mysqli_stmt_close($stmt_bulanan); ?>
</div>
