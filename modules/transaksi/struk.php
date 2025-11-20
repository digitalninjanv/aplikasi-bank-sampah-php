<?php
// modules/transaksi/struk.php
check_user_level(['admin', 'petugas']);

$id_transaksi = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_transaksi <= 0) {
    echo '<div class="container mx-auto px-4 py-12"><div class="bg-white border border-red-100 rounded-2xl p-6 text-center shadow"><h1 class="text-xl font-semibold text-red-600 mb-2">Transaksi tidak ditemukan</h1><p class="text-gray-600">Pastikan Anda memilih transaksi yang valid untuk dicetak struknya.</p><a href="' . BASE_URL . 'index.php?page=transaksi/riwayat" class="inline-flex mt-4 px-4 py-2 rounded-lg bg-sky-500 text-white">Kembali ke Riwayat</a></div></div>';
    return;
}

$query_transaksi = "
    SELECT
        t.id_transaksi,
        t.tanggal_transaksi,
        t.tipe_transaksi,
        t.total_nilai,
        t.keterangan,
        warga.nama_lengkap AS nama_warga,
        warga.username AS username_warga,
        petugas.nama_lengkap AS nama_petugas,
        petugas.username AS username_petugas
    FROM transaksi t
    JOIN pengguna warga ON t.id_warga = warga.id_pengguna
    JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    WHERE t.id_transaksi = ?
";

$stmt = mysqli_prepare($koneksi, $query_transaksi);
if (!$stmt) {
    echo '<div class="container mx-auto px-4 py-12"><div class="bg-white border border-red-100 rounded-2xl p-6 text-center shadow"><p class="text-gray-600">Tidak dapat menyiapkan detail transaksi untuk dicetak.</p></div></div>';
    return;
}

mysqli_stmt_bind_param($stmt, 'i', $id_transaksi);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$transaksi) {
    echo '<div class="container mx-auto px-4 py-12"><div class="bg-white border border-red-100 rounded-2xl p-6 text-center shadow"><h1 class="text-xl font-semibold text-red-600 mb-2">Transaksi tidak ditemukan</h1><p class="text-gray-600">Data transaksi yang Anda cari tidak tersedia.</p><a href="' . BASE_URL . 'index.php?page=transaksi/riwayat" class="inline-flex mt-4 px-4 py-2 rounded-lg bg-sky-500 text-white">Kembali ke Riwayat</a></div></div>';
    return;
}

$detail_items = [];
if ($transaksi['tipe_transaksi'] === 'setor') {
    $detail_query = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai
                     FROM detail_setoran ds
                     JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
                     WHERE ds.id_transaksi_setor = ?";
    $detail_stmt = mysqli_prepare($koneksi, $detail_query);
    if ($detail_stmt) {
        mysqli_stmt_bind_param($detail_stmt, 'i', $id_transaksi);
        mysqli_stmt_execute($detail_stmt);
        $detail_result = mysqli_stmt_get_result($detail_stmt);
        while ($row = mysqli_fetch_assoc($detail_result)) {
            $detail_items[] = $row;
        }
        mysqli_stmt_close($detail_stmt);
    }
}

$tipe_label = '';
if ($transaksi['tipe_transaksi'] === 'setor') {
    $tipe_label = 'Setoran Sampah';
} elseif ($transaksi['tipe_transaksi'] === 'tarik_saldo') {
    $tipe_label = 'Penarikan Saldo';
} else {
    $tipe_label = ucfirst(str_replace('_', ' ', $transaksi['tipe_transaksi']));
}
$waktu_transaksi = date('d M Y \p\u\k\u\l H:i', strtotime($transaksi['tanggal_transaksi']));
$kode_struk = 'TRX-' . str_pad($transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT);
?>

<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
        <div class="p-6 sm:p-8 bg-gradient-to-r from-sky-500/10 to-indigo-500/10 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Struk Transaksi</p>
                    <h1 class="text-2xl font-bold text-slate-900"><?php echo htmlspecialchars($tipe_label); ?></h1>
                    <p class="text-sm text-slate-500">Nomor Referensi: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($kode_struk); ?></span></p>
                </div>
                <div class="no-print flex flex-wrap gap-2">
                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold shadow hover:bg-sky-700">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6 text-sm text-slate-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs uppercase text-slate-400 tracking-[0.2em] mb-1">Data Warga</p>
                    <p class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($transaksi['nama_warga']); ?></p>
                    <p class="text-xs text-slate-500">Username: <?php echo htmlspecialchars($transaksi['username_warga']); ?></p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs uppercase text-slate-400 tracking-[0.2em] mb-1">Dicatat Oleh</p>
                    <p class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($transaksi['nama_petugas']); ?></p>
                    <p class="text-xs text-slate-500">Username: <?php echo htmlspecialchars($transaksi['username_petugas']); ?></p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-slate-100 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
                    <div>
                        <p class="text-xs uppercase text-slate-400 tracking-[0.3em]">Tanggal</p>
                        <p class="text-base font-semibold text-slate-900"><?php echo $waktu_transaksi; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase text-slate-400 tracking-[0.3em]">Total Transaksi</p>
                        <p class="text-2xl font-bold text-emerald-600"><?php echo format_rupiah($transaksi['total_nilai']); ?></p>
                    </div>
                </div>
            </div>

            <?php if ($transaksi['tipe_transaksi'] === 'setor'): ?>
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-widest">
                            <tr>
                                <th class="px-4 py-3">Jenis Sampah</th>
                                <th class="px-4 py-3 text-right">Berat (Kg)</th>
                                <th class="px-4 py-3 text-right">Harga/Kg</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($detail_items as $item): ?>
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($item['nama_sampah']); ?></td>
                                    <td class="px-4 py-3 text-right text-slate-600"><?php echo number_format($item['berat_kg'], 2); ?></td>
                                    <td class="px-4 py-3 text-right text-slate-600"><?php echo format_rupiah($item['harga_saat_setor']); ?></td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900"><?php echo format_rupiah($item['subtotal_nilai']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Transaksi ini merupakan <?php echo strtolower($tipe_label); ?> sebesar <span class="font-semibold text-slate-900"><?php echo format_rupiah($transaksi['total_nilai']); ?></span> yang tercatat pada <?php echo $waktu_transaksi; ?>.
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($transaksi['keterangan'])): ?>
                <div class="p-4 rounded-xl bg-white border border-amber-100">
                    <p class="text-xs uppercase text-amber-500 tracking-[0.3em] mb-1">Catatan</p>
                    <p class="text-sm text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($transaksi['keterangan'])); ?></p>
                </div>
            <?php endif; ?>

            <div class="text-center text-xs text-slate-400">
                <p>Terima kasih telah menjaga lingkungan bersama Bank Sampah Digital.</p>
                <p>Struk ini sah walau tanpa tanda tangan.</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background: #fff;
        }
        .no-print {
            display: none !important;
        }
        #sidebar, #content-area > header, #menu-button {
            display: none !important;
        }
        #content-area main {
            padding: 0 !important;
        }
    }
</style>
