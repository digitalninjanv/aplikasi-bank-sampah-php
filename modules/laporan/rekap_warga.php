<?php
check_user_level(['admin', 'petugas']);
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$order = $_GET['order'] ?? 'total_setor_desc';
$limit = 50;

$order_clause = match($order) {
    'total_setor_desc' => 'total_setor DESC', 'total_setor_asc' => 'total_setor ASC',
    'total_tarik_desc' => 'total_tarik DESC', 'saldo_desc' => 'p.saldo DESC',
    'nama_asc' => 'p.nama_lengkap ASC', default => 'total_setor DESC',
};

$query = "SELECT p.id_pengguna, p.nama_lengkap, p.no_telepon, p.saldo, COALESCE(s.total_setor, 0) AS total_setor, COALESCE(t.total_tarik, 0) AS total_tarik FROM pengguna p LEFT JOIN (SELECT id_warga, SUM(total_nilai) AS total_setor FROM transaksi WHERE tipe_transaksi='setor' AND DATE(tanggal_transaksi) BETWEEN ? AND ? GROUP BY id_warga) s ON p.id_pengguna = s.id_warga LEFT JOIN (SELECT id_warga, SUM(total_nilai) AS total_tarik FROM transaksi WHERE tipe_transaksi='tarik_saldo' AND DATE(tanggal_transaksi) BETWEEN ? AND ? GROUP BY id_warga) t ON p.id_pengguna = t.id_warga WHERE p.level='warga' AND (p.status IS NULL OR p.status='aktif') ORDER BY $order_clause LIMIT $limit";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ssss", $dari, $sampai, $dari, $sampai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = [];
while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
mysqli_stmt_close($stmt);
?>
<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Rekap Transaksi per Warga</h1>
        <p>Total setor & tarik per warga dalam periode tertentu</p>
    </div>

    <div class="card p-4 sm:p-5 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php">
            <input type="hidden" name="page" value="laporan/rekap_warga">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="dari" value="<?php echo htmlspecialchars($dari); ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="<?php echo htmlspecialchars($sampai); ?>" class="form-input">
                </div>
                <div>
                    <label class="form-label">Urutkan</label>
                    <select name="order" class="form-input">
                        <option value="total_setor_desc" <?php echo $order === 'total_setor_desc' ? 'selected' : ''; ?>>Total Setor Terbanyak</option>
                        <option value="total_tarik_desc" <?php echo $order === 'total_tarik_desc' ? 'selected' : ''; ?>>Total Tarik Terbanyak</option>
                        <option value="saldo_desc" <?php echo $order === 'saldo_desc' ? 'selected' : ''; ?>>Saldo Terbesar</option>
                        <option value="nama_asc" <?php echo $order === 'nama_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Warga</th><th class="text-right">Total Setor</th><th class="text-right">Total Tarik</th><th class="text-right">Saldo Akhir</th></tr></thead>
            <tbody>
                <?php $grand_setor = 0; $grand_tarik = 0; if (!empty($data)): ?>
                    <?php foreach($data as $row): $grand_setor += $row['total_setor']; $grand_tarik += $row['total_tarik']; ?>
                    <tr>
                        <td class="whitespace-nowrap font-semibold"><?php echo htmlspecialchars($row['nama_lengkap']); ?><span class="text-xs text-slate-500 block"><?php echo htmlspecialchars($row['no_telepon']); ?></span></td>
                        <td class="whitespace-nowrap text-right font-semibold text-emerald-600"><?php echo format_rupiah($row['total_setor']); ?></td>
                        <td class="whitespace-nowrap text-right font-semibold text-amber-600"><?php echo format_rupiah($row['total_tarik']); ?></td>
                        <td class="whitespace-nowrap text-right font-semibold text-slate-800"><?php echo format_rupiah($row['saldo']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4"><div class="empty-state"><i class="fas fa-users text-slate-300"></i><p>Belum ada data untuk periode ini.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($data)): ?>
            <tfoot class="bg-slate-50 font-semibold">
                <tr><td class="text-sm text-slate-700">Total Seluruh Warga</td><td class="text-right text-emerald-700"><?php echo format_rupiah($grand_setor); ?></td><td class="text-right text-amber-700"><?php echo format_rupiah($grand_tarik); ?></td><td class="text-right text-slate-700"><?php echo format_rupiah($grand_setor - $grand_tarik); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
