<?php
check_user_level(['admin', 'petugas']);

$tanggal_laporan = isset($_GET['tanggal']) ? sanitize_input($_GET['tanggal']) : date('Y-m-d');

$query_setoran_harian = "
    SELECT t.id_transaksi, p_warga.nama_lengkap AS nama_warga, p_petugas.nama_lengkap AS nama_petugas,
           t.tanggal_transaksi, t.total_nilai,
           GROUP_CONCAT(DISTINCT CONCAT(js.nama_sampah, ' (', ds.berat_kg, ' ', js.satuan, ')') SEPARATOR '; ') AS detail_item_setoran,
           t.keterangan AS keterangan_transaksi
    FROM transaksi t
    JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna
    JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna
    LEFT JOIN detail_setoran ds ON t.id_transaksi = ds.id_transaksi_setor
    LEFT JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
    WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'setor'
    GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi DESC";
$stmt_setoran = mysqli_prepare($koneksi, $query_setoran_harian);
mysqli_stmt_bind_param($stmt_setoran, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_setoran);
$result_setoran = mysqli_stmt_get_result($stmt_setoran);
$setoran_harian = [];
if ($result_setoran) { while ($row = mysqli_fetch_assoc($result_setoran)) { $setoran_harian[] = $row; } mysqli_free_result($result_setoran); }

$query_penarikan_harian = "
    SELECT t.id_transaksi, p_warga.nama_lengkap AS nama_warga, p_petugas.nama_lengkap AS nama_petugas,
           t.tanggal_transaksi, t.total_nilai, t.keterangan AS keterangan_transaksi
    FROM transaksi t
    JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna
    JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna
    WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'tarik_saldo'
    ORDER BY t.tanggal_transaksi DESC";
$stmt_penarikan = mysqli_prepare($koneksi, $query_penarikan_harian);
mysqli_stmt_bind_param($stmt_penarikan, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_penarikan);
$result_penarikan = mysqli_stmt_get_result($stmt_penarikan);
$penarikan_harian = [];
if ($result_penarikan) { while ($row = mysqli_fetch_assoc($result_penarikan)) { $penarikan_harian[] = $row; } mysqli_free_result($result_penarikan); }

$total_pemasukan_hari_ini = 0;
$query_total = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'setor'";
$stmt_t = mysqli_prepare($koneksi, $query_total);
mysqli_stmt_bind_param($stmt_t, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_t);
$d = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_t));
$total_pemasukan_hari_ini = $d['total'] ?: 0;
mysqli_stmt_close($stmt_t);

$total_pengeluaran_hari_ini = 0;
$query_t2 = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'tarik_saldo'";
$stmt_t2 = mysqli_prepare($koneksi, $query_t2);
mysqli_stmt_bind_param($stmt_t2, "s", $tanggal_laporan);
mysqli_stmt_execute($stmt_t2);
$d2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_t2));
$total_pengeluaran_hari_ini = $d2['total'] ?: 0;
mysqli_stmt_close($stmt_t2);
?>
<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Laporan Harian</h1>
        <p>Ringkasan pemasukan, pengeluaran, dan detail transaksi pada tanggal terpilih</p>
    </div>

    <div class="card p-4 sm:p-5 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-col sm:flex-row gap-3 items-end">
            <input type="hidden" name="page" value="laporan/harian">
            <div class="flex-1 w-full">
                <label for="tanggal_laporan_input" class="form-label">Pilih Tanggal</label>
                <input type="date" name="tanggal" id="tanggal_laporan_input" value="<?php echo $tanggal_laporan; ?>" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg"><i class="fas fa-arrow-down"></i></div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-emerald-600 font-semibold">Total Pemasukan</p>
                    <p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pemasukan_hari_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg"><i class="fas fa-arrow-up"></i></div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-red-600 font-semibold">Total Pengeluaran</p>
                    <p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pengeluaran_hari_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-scale-balanced"></i></div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-blue-600 font-semibold">Selisih</p>
                    <p class="text-xl font-bold font-['Poppins'] text-slate-800"><?php echo format_rupiah($total_pemasukan_hari_ini - $total_pengeluaran_hari_ini); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div>
            <h2 class="text-lg font-semibold font-['Poppins'] text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-arrow-down-wide-short text-emerald-500"></i> Detail Setoran
                <span class="badge badge-green text-xs"><?php echo count($setoran_harian); ?> transaksi</span>
            </h2>
            <div class="table-wrap hidden md:block">
                <table>
                    <thead><tr><th>Waktu</th><th>Warga</th><th>Petugas</th><th>Detail Item</th><th class="text-right">Total Nilai</th></tr></thead>
                    <tbody>
                        <?php if (!empty($setoran_harian)): ?>
                            <?php foreach($setoran_harian as $row): ?>
                            <tr>
                                <td class="whitespace-nowrap"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td class="whitespace-nowrap font-semibold"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                                <td class="whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                                <td class="max-w-xs">
                                    <?php $items = !empty($row['detail_item_setoran']) ? explode('; ', $row['detail_item_setoran']) : []; if (!empty($items)) { echo "<ul class='list-disc list-inside text-xs space-y-0.5'>"; foreach ($items as $item) { echo "<li>" . htmlspecialchars($item) . "</li>"; } echo "</ul>"; } else { echo "-"; } if(!empty($row['keterangan_transaksi'])) echo "<p class='mt-1 text-xs italic text-slate-500'>Ket: " . htmlspecialchars($row['keterangan_transaksi']) . "</p>"; ?>
                                </td>
                                <td class="whitespace-nowrap text-right font-semibold"><?php echo format_rupiah($row['total_nilai']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5"><div class="empty-state"><i class="fas fa-receipt text-slate-300"></i><p>Tidak ada data setoran pada tanggal ini.</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="grid gap-3 md:hidden"><?php if (!empty($setoran_harian)): foreach($setoran_harian as $row): ?>
                <div class="mobile-card">
                    <div class="flex items-center justify-between"><div><p class="text-xs text-slate-400"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></p><p class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['nama_warga']); ?></p></div><span class="badge badge-green">Setor</span></div>
                    <p class="mt-2 text-xs text-slate-600"><?php $items = !empty($row['detail_item_setoran']) ? explode('; ', $row['detail_item_setoran']) : []; echo !empty($items) ? implode('<br>', array_map('htmlspecialchars', $items)) : '-'; ?></p>
                    <div class="mt-2 flex items-center justify-between text-sm"><span class="text-slate-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></span><span class="font-semibold"><?php echo format_rupiah($row['total_nilai']); ?></span></div>
                </div>
            <?php endforeach; else: ?>
                <div class="mobile-card text-center py-6"><i class="fas fa-receipt text-2xl text-slate-300 mb-2"></i><p class="text-sm text-slate-500">Tidak ada setoran.</p></div>
            <?php endif; ?></div>
        </div>

        <div>
            <h2 class="text-lg font-semibold font-['Poppins'] text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-arrow-up-short-wide text-red-500"></i> Detail Penarikan Saldo
                <span class="badge badge-red text-xs"><?php echo count($penarikan_harian); ?> transaksi</span>
            </h2>
            <div class="table-wrap hidden md:block">
                <table>
                    <thead><tr><th>Waktu</th><th>Warga</th><th>Petugas</th><th>Keterangan</th><th class="text-right">Jumlah</th></tr></thead>
                    <tbody>
                        <?php if (!empty($penarikan_harian)): ?>
                            <?php foreach($penarikan_harian as $row): ?>
                            <tr>
                                <td class="whitespace-nowrap"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td class="whitespace-nowrap font-semibold"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                                <td class="whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                                <td class="text-slate-600"><?php echo htmlspecialchars($row['keterangan_transaksi'] ?: '-'); ?></td>
                                <td class="whitespace-nowrap text-right font-semibold text-red-600"><?php echo format_rupiah($row['total_nilai']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5"><div class="empty-state"><i class="fas fa-hand-holding-dollar text-slate-300"></i><p>Tidak ada data penarikan pada tanggal ini.</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="grid gap-3 md:hidden"><?php if (!empty($penarikan_harian)): foreach($penarikan_harian as $row): ?>
                <div class="mobile-card">
                    <div class="flex items-center justify-between"><div><p class="text-xs text-slate-400"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></p><p class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['nama_warga']); ?></p></div><span class="badge badge-red">Tarik</span></div>
                    <p class="mt-2 text-xs text-slate-600"><?php echo htmlspecialchars($row['keterangan_transaksi'] ?: '-'); ?></p>
                    <div class="mt-2 flex items-center justify-between text-sm"><span class="text-slate-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></span><span class="font-semibold text-red-600"><?php echo format_rupiah($row['total_nilai']); ?></span></div>
                </div>
            <?php endforeach; else: ?>
                <div class="mobile-card text-center py-6"><i class="fas fa-hand-holding-dollar text-2xl text-slate-300 mb-2"></i><p class="text-sm text-slate-500">Tidak ada penarikan.</p></div>
            <?php endif; ?></div>
        </div>
    </div>
    <?php if($stmt_setoran) mysqli_stmt_close($stmt_setoran); if($stmt_penarikan) mysqli_stmt_close($stmt_penarikan); ?>
</div>
