<?php
// modules/laporan/riwayat_warga.php
check_user_level(['warga', 'admin', 'petugas']); // Warga bisa akses, admin/petugas juga bisa (jika ada filter by warga)

$id_warga_login = $_SESSION['user_id'];
$nama_warga_login = $_SESSION['user_nama'];
$is_admin_or_petugas = in_array($_SESSION['user_level'], ['admin', 'petugas']);

// Jika admin/petugas mengakses dan ada parameter id_warga, gunakan itu. Jika tidak, warga melihat riwayatnya sendiri.
$target_id_warga = $id_warga_login;
if ($is_admin_or_petugas && isset($_GET['id_warga_filter']) && !empty($_GET['id_warga_filter'])) {
    $target_id_warga = sanitize_input($_GET['id_warga_filter']);
    // Ambil nama warga yang difilter untuk judul
    $query_nama_filter = "SELECT nama_lengkap FROM pengguna WHERE id_pengguna = ?";
    $stmt_nama = mysqli_prepare($koneksi, $query_nama_filter);
    mysqli_stmt_bind_param($stmt_nama, "i", $target_id_warga);
    mysqli_stmt_execute($stmt_nama);
    $res_nama = mysqli_stmt_get_result($stmt_nama);
    if($data_nama = mysqli_fetch_assoc($res_nama)){
        $nama_warga_login = $data_nama['nama_lengkap'] . " (Dilihat oleh ".$_SESSION['user_level'].")";
    }
    mysqli_stmt_close($stmt_nama);
}

$query_riwayat = "
    SELECT
        t.id_transaksi,
        t.tanggal_transaksi,
        t.tipe_transaksi,
        t.total_nilai,
        t.keterangan AS keterangan_transaksi,
        petugas.nama_lengkap AS nama_petugas
    FROM transaksi t
    JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    WHERE t.id_warga = ?
    ORDER BY t.tanggal_transaksi DESC
";
$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $target_id_warga);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);

$riwayat_warga = [];
if ($result_riwayat) {
    while ($row = mysqli_fetch_assoc($result_riwayat)) {
        $row['detail_items'] = [];
        $riwayat_warga[] = $row;
    }
    mysqli_free_result($result_riwayat);
}

$detail_query = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai
                 FROM detail_setoran ds
                 JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
                 WHERE ds.id_transaksi_setor = ?";
$detail_stmt = mysqli_prepare($koneksi, $detail_query);
if ($detail_stmt) {
    $detail_transaksi_id = 0;
    mysqli_stmt_bind_param($detail_stmt, "i", $detail_transaksi_id);
    foreach ($riwayat_warga as &$trx) {
        if ($trx['tipe_transaksi'] === 'setor') {
            $detail_transaksi_id = $trx['id_transaksi'];
            mysqli_stmt_execute($detail_stmt);
            $detail_result = mysqli_stmt_get_result($detail_stmt);
            $detail_items = [];
            if ($detail_result) {
                while ($item = mysqli_fetch_assoc($detail_result)) {
                    $detail_items[] = $item;
                }
                mysqli_free_result($detail_result);
            }
            $trx['detail_items'] = $detail_items;
        }
    }
    unset($trx);
    mysqli_stmt_close($detail_stmt);
}

// Ambil saldo warga saat ini
$saldo_warga_saat_ini = 0;
$query_saldo_warga = "SELECT saldo FROM pengguna WHERE id_pengguna = ?";
$stmt_saldo_warga = mysqli_prepare($koneksi, $query_saldo_warga);
mysqli_stmt_bind_param($stmt_saldo_warga, "i", $target_id_warga);
mysqli_stmt_execute($stmt_saldo_warga);
$res_saldo = mysqli_stmt_get_result($stmt_saldo_warga);
if($data_saldo = mysqli_fetch_assoc($res_saldo)){
    $saldo_warga_saat_ini = $data_saldo['saldo'];
}
mysqli_stmt_close($stmt_saldo_warga);
?>
<div class="container mx-auto px-4 py-8">
    <div class="bg-gradient-to-br from-sky-500/10 via-emerald-500/10 to-indigo-500/10 border border-sky-100 rounded-2xl p-5 sm:p-6 shadow-xl mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-sky-500 font-semibold">Riwayat Warga</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Transaksi <?php echo $is_admin_or_petugas ? htmlspecialchars($nama_warga_login) : 'Saya'; ?></h1>
                <p class="text-sm text-gray-600 mt-1">Rekap seluruh setoran dan penarikan yang dicatat oleh petugas.</p>
            </div>
            <span class="inline-flex items-center gap-2 text-xs bg-white px-3 py-1 rounded-full border border-gray-200 text-gray-600"><i class="fas fa-database"></i><?php echo count($riwayat_warga); ?> transaksi</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <p class="text-xs uppercase tracking-widest text-gray-500 font-semibold mb-1">Saldo Saat Ini</p>
            <p class="text-3xl font-bold text-emerald-600"><?php echo format_rupiah($saldo_warga_saat_ini); ?></p>
            <p class="text-xs text-gray-500 mt-1">Terakhir diperbarui otomatis dari transaksi terbaru.</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-md">
            <p class="text-xs uppercase tracking-widest text-gray-500 font-semibold mb-1">Total Aktivitas</p>
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?php echo count($riwayat_warga); ?></p>
                    <p class="text-xs text-gray-500">Transaksi tercatat</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($is_admin_or_petugas): ?>
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg p-4 sm:p-6 mb-8">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-col md:flex-row gap-3 md:items-end">
            <input type="hidden" name="page" value="laporan/riwayat_warga">
            <div class="flex-1">
                <label for="id_warga_filter_select" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Lihat Riwayat Warga Lain</label>
                <select name="id_warga_filter" id="id_warga_filter_select" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50 text-sm">
                    <option value="<?php echo $_SESSION['user_id']; ?>">Riwayat Saya Sendiri (<?php echo $_SESSION['user_nama']; ?>)</option>
                    <?php
                    $q_warga_list = "SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE level='warga' ORDER BY nama_lengkap ASC";
                    $r_warga_list = mysqli_query($koneksi, $q_warga_list);
                    while($w_list = mysqli_fetch_assoc($r_warga_list)) {
                        $selected = ($target_id_warga == $w_list['id_pengguna']) ? 'selected' : '';
                        echo "<option value='{$w_list['id_pengguna']}' $selected>" . htmlspecialchars($w_list['nama_lengkap']) . " ({$w_list['username']})</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-emerald-500 text-white font-semibold shadow-lg hover:shadow-xl transition">
                <i class="fas fa-sync mr-2"></i> Tampilkan
            </button>
        </form>
    </div>
    <?php endif; ?>

    <div class="space-y-6">
        <div class="hidden md:block bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal & Waktu</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe Transaksi</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Nilai (Rp)</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dicatat Oleh</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Detail/Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (!empty($riwayat_warga)): ?>
                            <?php foreach($riwayat_warga as $trx): ?>
                            <tr class="hover:bg-sky-50/50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo format_tanggal_indonesia($trx['tanggal_transaksi']); ?></td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">Setor Sampah</span>
                                    <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-amber-50 text-amber-600">Tarik Saldo</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-700"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">
                                    <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                                        <ul class="list-disc list-inside text-xs space-y-0.5">
                                            <?php foreach($trx['detail_items'] as $item): ?>
                                                <li><?php echo htmlspecialchars($item['nama_sampah']); ?>: <?php echo $item['berat_kg']; ?>kg @ <?php echo format_rupiah($item['harga_saat_setor']); ?> = <?php echo format_rupiah($item['subtotal_nilai']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if(!empty($trx['keterangan_transaksi'])): ?>
                                            <p class="mt-1 text-xs italic">Ket: <?php echo htmlspecialchars($trx['keterangan_transaksi']); ?></p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada riwayat transaksi untuk ditampilkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-4 md:hidden">
            <?php if (!empty($riwayat_warga)): ?>
                <?php foreach($riwayat_warga as $trx): ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-400"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $trx['tipe_transaksi'] == 'setor' ? 'Setor Sampah' : ($trx['tipe_transaksi'] == 'tarik_saldo' ? 'Tarik Saldo' : htmlspecialchars($trx['tipe_transaksi'])); ?></p>
                                <p class="text-xs text-gray-500">Petugas: <?php echo htmlspecialchars($trx['nama_petugas']); ?></p>
                            </div>
                            <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">Setor</span>
                            <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-600">Tarik</span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 space-y-2 text-sm text-gray-600">
                            <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Detail Setoran</p>
                                <ul class="text-xs space-y-1">
                                    <?php foreach($trx['detail_items'] as $item): ?>
                                        <li class="flex justify-between gap-3">
                                            <span><?php echo htmlspecialchars($item['nama_sampah']); ?></span>
                                            <span class="font-semibold"><?php echo $item['berat_kg']; ?>kg · <?php echo format_rupiah($item['subtotal_nilai']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if(!empty($trx['keterangan_transaksi'])): ?>
                                    <p class="text-xs text-gray-500 italic">Ket: <?php echo htmlspecialchars($trx['keterangan_transaksi']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-gray-500">Nilai</span>
                            <span class="text-base font-semibold text-gray-900"><?php echo format_rupiah($trx['total_nilai']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-md">
                    <i class="fas fa-receipt fa-2x text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">Belum ada riwayat transaksi untuk ditampilkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php mysqli_stmt_close($stmt_riwayat); ?>
</div>
