<?php
check_user_level(['warga', 'admin', 'petugas']);

$id_warga_login = $_SESSION['user_id'];
$nama_warga_login = $_SESSION['user_nama'];
$is_admin_or_petugas = in_array($_SESSION['user_level'], ['admin', 'petugas']);

$target_id_warga = $id_warga_login;
if ($is_admin_or_petugas && isset($_GET['id_warga_filter']) && !empty($_GET['id_warga_filter'])) {
    $target_id_warga = sanitize_input($_GET['id_warga_filter']);
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
    SELECT t.id_transaksi, t.tanggal_transaksi, t.tipe_transaksi, t.total_nilai, t.keterangan AS keterangan_transaksi, petugas.nama_lengkap AS nama_petugas
    FROM transaksi t JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    WHERE t.id_warga = ? ORDER BY t.tanggal_transaksi DESC";
$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $target_id_warga);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
$riwayat_warga = [];
if ($result_riwayat) { while ($row = mysqli_fetch_assoc($result_riwayat)) { $row['detail_items'] = []; $riwayat_warga[] = $row; } mysqli_free_result($result_riwayat); }

$detail_query = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai FROM detail_setoran ds JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah WHERE ds.id_transaksi_setor = ?";
$detail_stmt = mysqli_prepare($koneksi, $detail_query);
if ($detail_stmt) {
    $detail_transaksi_id = 0; mysqli_stmt_bind_param($detail_stmt, "i", $detail_transaksi_id);
    foreach ($riwayat_warga as &$trx) {
        if ($trx['tipe_transaksi'] === 'setor') {
            $detail_transaksi_id = $trx['id_transaksi']; mysqli_stmt_execute($detail_stmt);
            $detail_result = mysqli_stmt_get_result($detail_stmt); $detail_items = [];
            if ($detail_result) { while ($item = mysqli_fetch_assoc($detail_result)) { $detail_items[] = $item; } mysqli_free_result($detail_result); }
            $trx['detail_items'] = $detail_items;
        }
    } unset($trx); mysqli_stmt_close($detail_stmt);
}

$saldo_warga_saat_ini = 0;
$query_saldo_warga = "SELECT saldo FROM pengguna WHERE id_pengguna = ?";
$stmt_saldo_warga = mysqli_prepare($koneksi, $query_saldo_warga);
mysqli_stmt_bind_param($stmt_saldo_warga, "i", $target_id_warga);
mysqli_stmt_execute($stmt_saldo_warga);
$res_saldo = mysqli_stmt_get_result($stmt_saldo_warga);
if($data_saldo = mysqli_fetch_assoc($res_saldo)){ $saldo_warga_saat_ini = $data_saldo['saldo']; }
mysqli_stmt_close($stmt_saldo_warga);
?>
<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Transaksi <?php echo $is_admin_or_petugas ? htmlspecialchars($nama_warga_login) : 'Saya'; ?></h1>
        <p>Rekap seluruh setoran dan penarikan yang tercatat</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="card p-5">
            <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Saldo Saat Ini</p>
            <p class="text-3xl font-bold font-['Poppins'] text-emerald-600"><?php echo format_rupiah($saldo_warga_saat_ini); ?></p>
        </div>
        <div class="card p-5">
            <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Total Transaksi</p>
            <p class="text-3xl font-bold font-['Poppins'] text-slate-800"><?php echo count($riwayat_warga); ?></p>
        </div>
    </div>

    <?php if ($is_admin_or_petugas): ?>
    <div class="card p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="flex flex-col sm:flex-row gap-3 items-end">
            <input type="hidden" name="page" value="laporan/riwayat_warga">
            <div class="flex-1 w-full">
                <label class="form-label">Lihat Riwayat Warga Lain</label>
                <select name="id_warga_filter" class="form-input">
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
            <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Tampilkan</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-wrap hidden md:block">
        <table>
            <thead><tr><th>Tanggal & Waktu</th><th>Tipe</th><th class="text-right">Nilai (Rp)</th><th>Pencatat</th><th>Detail/Keterangan</th></tr></thead>
            <tbody>
                <?php if (!empty($riwayat_warga)): ?>
                    <?php foreach($riwayat_warga as $trx): ?>
                    <tr>
                        <td class="whitespace-nowrap"><?php echo format_tanggal_indonesia($trx['tanggal_transaksi']); ?></td>
                        <td class="whitespace-nowrap"><?php if ($trx['tipe_transaksi'] == 'setor'): ?><span class="badge badge-green">Setor Sampah</span><?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?><span class="badge badge-amber">Tarik Saldo</span><?php else: ?><span class="badge" style="background:#f1f5f9;color:#475569;"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span><?php endif; ?></td>
                        <td class="whitespace-nowrap text-right font-semibold"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                        <td class="whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></td>
                        <td class="max-w-xs">
                            <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                                <ul class="list-disc list-inside text-xs space-y-0.5 text-slate-600"><?php foreach($trx['detail_items'] as $item): ?><li><?php echo htmlspecialchars($item['nama_sampah']); ?>: <?php echo $item['berat_kg']; ?>kg @ <?php echo format_rupiah($item['harga_saat_setor']); ?> = <?php echo format_rupiah($item['subtotal_nilai']); ?></li><?php endforeach; ?></ul>
                                <?php if(!empty($trx['keterangan_transaksi'])): ?><p class="mt-1 text-xs italic text-slate-500">Ket: <?php echo htmlspecialchars($trx['keterangan_transaksi']); ?></p><?php endif; ?>
                            <?php else: ?><span class="text-slate-500"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ?: '-'); ?></span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><div class="empty-state"><i class="fas fa-receipt text-slate-300"></i><p>Belum ada riwayat transaksi.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 md:hidden">
        <?php if (!empty($riwayat_warga)): foreach($riwayat_warga as $trx): ?>
            <div class="mobile-card">
                <div class="flex items-start justify-between gap-3">
                    <div><p class="text-xs text-slate-400"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></p><p class="font-semibold text-slate-900"><?php echo $trx['tipe_transaksi'] == 'setor' ? 'Setor Sampah' : 'Tarik Saldo'; ?></p><p class="text-xs text-slate-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></p></div>
                    <span class="badge <?php echo $trx['tipe_transaksi'] == 'setor' ? 'badge-green' : 'badge-amber'; ?> shrink-0"><?php echo $trx['tipe_transaksi'] == 'setor' ? 'Setor' : 'Tarik'; ?></span>
                </div>
                <div class="mt-2 text-sm text-slate-600">
                    <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase">Detail Setoran</p>
                        <ul class="text-xs space-y-1"><?php foreach($trx['detail_items'] as $item): ?><li class="flex justify-between gap-3"><span><?php echo htmlspecialchars($item['nama_sampah']); ?></span><span class="font-semibold"><?php echo $item['berat_kg']; ?>kg · <?php echo format_rupiah($item['subtotal_nilai']); ?></span></li><?php endforeach; ?></ul>
                    <?php else: ?><p class="text-xs"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ?: '-'); ?></p><?php endif; ?>
                </div>
                <div class="mt-3 flex items-center justify-between text-sm"><span class="text-slate-500">Nilai</span><span class="font-semibold text-slate-900"><?php echo format_rupiah($trx['total_nilai']); ?></span></div>
            </div>
        <?php endforeach; else: ?>
            <div class="mobile-card text-center py-6"><i class="fas fa-receipt text-2xl text-slate-300 mb-2"></i><p class="text-sm text-slate-500">Belum ada riwayat.</p></div>
        <?php endif; ?>
    </div>

    <?php mysqli_stmt_close($stmt_riwayat); ?>
</div>
