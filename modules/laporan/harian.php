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
$setoran_harian = [];
if ($result_setoran) {
    while ($row = mysqli_fetch_assoc($result_setoran)) {
        $setoran_harian[] = $row;
    }
    mysqli_free_result($result_setoran);
}

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
$penarikan_harian = [];
if ($result_penarikan) {
    while ($row = mysqli_fetch_assoc($result_penarikan)) {
        $penarikan_harian[] = $row;
    }
    mysqli_free_result($result_penarikan);
}

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
    <div class="bg-gradient-to-br from-emerald-500/10 via-sky-500/10 to-indigo-500/10 border border-emerald-100 rounded-2xl p-5 sm:p-6 shadow-xl mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-500 font-semibold">Laporan Harian</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Aktivitas <?php echo format_tanggal_indonesia($tanggal_laporan, false); ?></h1>
                <p class="text-sm text-gray-600 mt-1">Ringkasan pemasukan, pengeluaran, dan detail transaksi pada tanggal terpilih.</p>
            </div>
            <span class="inline-flex items-center gap-2 text-xs bg-white px-3 py-1 rounded-full border border-gray-200 text-gray-600"><i class="far fa-clock"></i><?php echo count($setoran_harian) + count($penarikan_harian); ?> transaksi</span>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-1 flex-col sm:flex-row sm:items-center gap-3 w-full">
                <input type="hidden" name="page" value="laporan/harian">
                <div class="flex-1 w-full">
                    <label for="tanggal_laporan_input" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pilih Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal_laporan_input" value="<?php echo $tanggal_laporan; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50 text-sm">
                </div>
                <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-sky-500 text-white font-semibold shadow-lg hover:shadow-xl transition w-full sm:w-auto">
                    <i class="fas fa-search mr-2"></i> Tampilkan
                </button>
            </form>
            <a href="<?php echo BASE_URL; ?>index.php?page=laporan/export&report_type=harian&tanggal=<?php echo $tanggal_laporan; ?>"
               class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white font-semibold shadow-lg hover:shadow-xl transition">
                <i class="fas fa-file-excel mr-2"></i> Ekspor Excel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-emerald-500 font-semibold">Total Pemasukan</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pemasukan_hari_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pengeluaran_hari_ini); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fas fa-scale-balanced"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-sky-500 font-semibold">Selisih</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo format_rupiah($total_pemasukan_hari_ini - $total_pengeluaran_hari_ini); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-arrow-down-wide-short text-emerald-500"></i> Detail Setoran</h2>
            <div class="hidden md:block bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Warga</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Petugas</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Detail Item</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php if (!empty($setoran_harian)): ?>
                                <?php foreach($setoran_harian as $row): ?>
                                <tr class="hover:bg-emerald-50/50">
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-700"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">
                                        <?php
                                        $items = !empty($row['detail_item_setoran']) ? explode('; ', $row['detail_item_setoran']) : [];
                                        if (!empty($items)) {
                                            echo "<ul class='list-disc list-inside text-xs space-y-0.5'>";
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
                                    <td class="px-4 sm:px-6 py-4 text-sm text-right font-semibold text-gray-900"><?php echo format_rupiah($row['total_nilai']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-sm text-gray-500">Tidak ada data setoran pada tanggal ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid gap-4 md:hidden">
                <?php if (!empty($setoran_harian)): ?>
                    <?php foreach($setoran_harian as $row): ?>
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-gray-400">Waktu</p>
                                    <p class="text-lg font-semibold text-gray-900"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_warga']); ?></p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">Setor</span>
                            </div>
                            <div class="mt-3 text-xs text-gray-600 space-y-1">
                                <p class="font-semibold text-gray-500 uppercase tracking-wide">Detail Item</p>
                                <?php
                                $items = !empty($row['detail_item_setoran']) ? explode('; ', $row['detail_item_setoran']) : [];
                                if (!empty($items)) {
                                    echo "<ul class='space-y-1'>";
                                    foreach ($items as $item) {
                                        echo "<li>" . htmlspecialchars($item) . "</li>";
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "-";
                                }
                                if(!empty($row['keterangan_transaksi'])) echo "<p class=\"mt-1 italic\">Ket: " . htmlspecialchars($row['keterangan_transaksi']) . "</p>";
                                ?>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-gray-500">Petugas</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($row['nama_petugas']); ?></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm font-semibold">
                                <span class="text-gray-500">Total</span>
                                <span class="text-gray-900"><?php echo format_rupiah($row['total_nilai']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-md">
                        <i class="fas fa-receipt fa-2x text-gray-300 mb-3"></i>
                        <p class="text-sm text-gray-500">Tidak ada data setoran pada tanggal ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-arrow-up-short-wide text-rose-500"></i> Detail Penarikan Saldo</h2>
            <div class="hidden md:block bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Warga</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Petugas</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Keterangan</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Jumlah Ditarik</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php if (!empty($penarikan_harian)): ?>
                                <?php foreach($penarikan_harian as $row): ?>
                                <tr class="hover:bg-rose-50/50">
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-700"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_warga']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['keterangan_transaksi'] ? $row['keterangan_transaksi'] : '-'); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-right font-semibold text-gray-900"><?php echo format_rupiah($row['total_nilai']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-sm text-gray-500">Tidak ada data penarikan pada tanggal ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid gap-4 md:hidden">
                <?php if (!empty($penarikan_harian)): ?>
                    <?php foreach($penarikan_harian as $row): ?>
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-gray-400">Waktu</p>
                                    <p class="text-lg font-semibold text-gray-900"><?php echo date('H:i', strtotime($row['tanggal_transaksi'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_warga']); ?></p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-rose-50 text-rose-600">Tarik</span>
                            </div>
                            <p class="mt-3 text-xs text-gray-600"><?php echo htmlspecialchars($row['keterangan_transaksi'] ? $row['keterangan_transaksi'] : '-'); ?></p>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-gray-500">Petugas</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($row['nama_petugas']); ?></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm font-semibold">
                                <span class="text-gray-500">Total</span>
                                <span class="text-gray-900"><?php echo format_rupiah($row['total_nilai']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-md">
                        <i class="fas fa-hand-holding-dollar fa-2x text-gray-300 mb-3"></i>
                        <p class="text-sm text-gray-500">Tidak ada data penarikan pada tanggal ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($stmt_setoran) mysqli_stmt_close($stmt_setoran); ?>
    <?php if($stmt_penarikan) mysqli_stmt_close($stmt_penarikan); ?>
</div>
