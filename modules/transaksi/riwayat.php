<?php
check_user_level(['admin', 'petugas']);

$filter_warga = isset($_GET['filter_warga']) ? sanitize_input($_GET['filter_warga']) : '';
$filter_tipe = isset($_GET['filter_tipe']) ? sanitize_input($_GET['filter_tipe']) : '';
$filter_tanggal_mulai = isset($_GET['filter_tanggal_mulai']) ? sanitize_input($_GET['filter_tanggal_mulai']) : '';
$filter_tanggal_akhir = isset($_GET['filter_tanggal_akhir']) ? sanitize_input($_GET['filter_tanggal_akhir']) : '';
$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

$conditions = [];
$params_type = "";
$params_value = [];

if (!empty($filter_warga)) {
    $conditions[] = "t.id_warga = ?";
    $params_type .= "i";
    $params_value[] = $filter_warga;
}
if (!empty($filter_tipe)) {
    $conditions[] = "t.tipe_transaksi = ?";
    $params_type .= "s";
    $params_value[] = $filter_tipe;
}
if (!empty($filter_tanggal_mulai)) {
    $conditions[] = "DATE(t.tanggal_transaksi) >= ?";
    $params_type .= "s";
    $params_value[] = $filter_tanggal_mulai;
}
if (!empty($filter_tanggal_akhir)) {
    $conditions[] = "DATE(t.tanggal_transaksi) <= ?";
    $params_type .= "s";
    $params_value[] = $filter_tanggal_akhir;
}

$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

$count_query = "
    SELECT COUNT(*) AS total
    FROM transaksi t
    JOIN pengguna warga ON t.id_warga = warga.id_pengguna
    JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    $where_clause
";

$total_transaksi = 0;
$count_stmt = mysqli_prepare($koneksi, $count_query);
if ($count_stmt) {
    if (!empty($params_type) && !empty($params_value)) {
        mysqli_stmt_bind_param($count_stmt, $params_type, ...$params_value);
    }
    mysqli_stmt_execute($count_stmt);
    mysqli_stmt_bind_result($count_stmt, $total_transaksi);
    mysqli_stmt_fetch($count_stmt);
    mysqli_stmt_close($count_stmt);
}

$total_pages = max(1, (int)ceil($total_transaksi / $per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $per_page;

$query_transaksi = "
    SELECT t.id_transaksi, t.tanggal_transaksi, t.tipe_transaksi, t.total_nilai,
           t.keterangan AS keterangan_transaksi, warga.nama_lengkap AS nama_warga,
           warga.username AS username_warga, petugas.nama_lengkap AS nama_petugas
    FROM transaksi t
    JOIN pengguna warga ON t.id_warga = warga.id_pengguna
    JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
    $where_clause
    ORDER BY t.tanggal_transaksi DESC
    LIMIT $per_page OFFSET $offset
";

$stmt_transaksi = mysqli_prepare($koneksi, $query_transaksi);
if ($stmt_transaksi) {
    if (!empty($params_type) && !empty($params_value)) {
        mysqli_stmt_bind_param($stmt_transaksi, $params_type, ...$params_value);
    }
    mysqli_stmt_execute($stmt_transaksi);
    $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
} else {
    $result_transaksi = false;
}

$riwayat_transaksi = [];
if ($result_transaksi) {
    while ($row = mysqli_fetch_assoc($result_transaksi)) {
        $row['detail_items'] = [];
        $riwayat_transaksi[] = $row;
    }
    mysqli_free_result($result_transaksi);
}

$detail_query = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai
                 FROM detail_setoran ds
                 JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
                 WHERE ds.id_transaksi_setor = ?";
$detail_stmt = mysqli_prepare($koneksi, $detail_query);
if ($detail_stmt) {
    $detail_transaksi_id = 0;
    mysqli_stmt_bind_param($detail_stmt, "i", $detail_transaksi_id);
    foreach ($riwayat_transaksi as &$trx) {
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

$query_all_warga = "SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_all_warga = mysqli_query($koneksi, $query_all_warga);
?>

<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Riwayat Transaksi</h1>
        <p>Lihat seluruh setoran dan penarikan dengan tampilan yang rapi</p>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="card p-4 sm:p-5 mb-6">
        <input type="hidden" name="page" value="transaksi/riwayat">
        <input type="hidden" name="p" value="1">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
            <div>
                <label for="filter_warga" class="form-label">Warga</label>
                <select name="filter_warga" id="filter_warga" class="form-input">
                    <option value="">Semua Warga</option>
                    <?php if ($result_all_warga && mysqli_num_rows($result_all_warga) > 0): ?>
                        <?php mysqli_data_seek($result_all_warga, 0); while($w = mysqli_fetch_assoc($result_all_warga)): ?>
                        <option value="<?php echo $w['id_pengguna']; ?>" <?php echo ($filter_warga == $w['id_pengguna']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($w['nama_lengkap'] . ' (' . $w['username'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="filter_tipe" class="form-label">Tipe</label>
                <select name="filter_tipe" id="filter_tipe" class="form-input">
                    <option value="">Semua Tipe</option>
                    <option value="setor" <?php echo ($filter_tipe == 'setor') ? 'selected' : ''; ?>>Setor Sampah</option>
                    <option value="tarik_saldo" <?php echo ($filter_tipe == 'tarik_saldo') ? 'selected' : ''; ?>>Tarik Saldo</option>
                </select>
            </div>
            <div>
                <label for="filter_tanggal_mulai" class="form-label">Dari Tanggal</label>
                <input type="date" name="filter_tanggal_mulai" id="filter_tanggal_mulai" value="<?php echo htmlspecialchars($filter_tanggal_mulai); ?>" class="form-input">
            </div>
            <div>
                <label for="filter_tanggal_akhir" class="form-label">Sampai Tanggal</label>
                <input type="date" name="filter_tanggal_akhir" id="filter_tanggal_akhir" value="<?php echo htmlspecialchars($filter_tanggal_akhir); ?>" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <div class="table-wrap hidden md:block">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Warga</th>
                    <th>Tipe</th>
                    <th class="text-right">Nilai (Rp)</th>
                    <th>Pencatat</th>
                    <th>Detail</th>
                    <th class="text-center">Struk</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($riwayat_transaksi)): ?>
                    <?php foreach($riwayat_transaksi as $trx): ?>
                    <tr>
                        <td class="whitespace-nowrap text-slate-500">#<?php echo $trx['id_transaksi']; ?></td>
                        <td class="whitespace-nowrap"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                        <td class="whitespace-nowrap font-semibold"><?php echo htmlspecialchars($trx['nama_warga']); ?></td>
                        <td class="whitespace-nowrap">
                            <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                <span class="badge badge-green">Setor Sampah</span>
                            <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                <span class="badge badge-amber">Tarik Saldo</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f1f5f9;color:#475569;"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap text-right font-semibold"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                        <td class="whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></td>
                        <td class="max-w-xs">
                            <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                                <ul class="list-disc list-inside text-xs space-y-0.5 text-slate-600">
                                    <?php foreach($trx['detail_items'] as $item): ?>
                                        <li><?php echo htmlspecialchars($item['nama_sampah']); ?>: <?php echo $item['berat_kg']; ?>kg @ <?php echo format_rupiah($item['harga_saat_setor']); ?> = <?php echo format_rupiah($item['subtotal_nilai']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if(!empty($trx['keterangan_transaksi'])): ?>
                                    <p class="mt-1 text-xs italic text-slate-500">Ket: <?php echo htmlspecialchars($trx['keterangan_transaksi']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-slate-500"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?php echo BASE_URL . 'index.php?page=transaksi/struk&id=' . urlencode($trx['id_transaksi']); ?>"
                               target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline">
                                <i class="fas fa-print"></i> Cetak
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-receipt text-slate-300"></i>
                                <p>Tidak ada data transaksi ditemukan dengan filter yang diterapkan.</p>
                                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="text-blue-600 hover:underline mt-2 inline-block font-semibold">Reset filter dan tampilkan semua</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 md:hidden">
        <?php if (!empty($riwayat_transaksi)): ?>
            <?php foreach($riwayat_transaksi as $trx): ?>
                <div class="mobile-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400">ID #<?php echo $trx['id_transaksi']; ?></p>
                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($trx['nama_warga']); ?></p>
                            <p class="text-xs text-slate-500"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></p>
                        </div>
                        <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                            <span class="badge badge-green">Setor</span>
                        <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                            <span class="badge badge-amber">Tarik</span>
                        <?php else: ?>
                            <span class="badge" style="background:#f1f5f9;color:#475569;"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nilai</span>
                            <span class="font-semibold text-slate-900"><?php echo format_rupiah($trx['total_nilai']); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Pencatat</span>
                            <span class="font-medium text-slate-700"><?php echo htmlspecialchars($trx['nama_petugas']); ?></span>
                        </div>
                        <div class="pt-2 border-t border-dashed border-slate-200">
                            <?php if ($trx['tipe_transaksi'] == 'setor' && !empty($trx['detail_items'])): ?>
                                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Detail Setoran</p>
                                <ul class="text-xs space-y-1">
                                    <?php foreach($trx['detail_items'] as $item): ?>
                                        <li class="flex justify-between gap-4">
                                            <span class="text-slate-600"><?php echo htmlspecialchars($item['nama_sampah']); ?></span>
                                            <span class="font-semibold text-slate-800"><?php echo $item['berat_kg']; ?>kg · <?php echo format_rupiah($item['subtotal_nilai']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if(!empty($trx['keterangan_transaksi'])): ?>
                                    <p class="mt-2 text-xs text-slate-500 italic">Ket: <?php echo htmlspecialchars($trx['keterangan_transaksi']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <a href="<?php echo BASE_URL . 'index.php?page=transaksi/struk&id=' . urlencode($trx['id_transaksi']); ?>"
                           target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline w-full justify-center">
                            <i class="fas fa-print"></i> Cetak Struk
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mobile-card text-center py-8">
                <i class="fas fa-receipt text-3xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 text-sm">Tidak ada data transaksi ditemukan.</p>
                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="text-blue-600 font-semibold text-sm mt-2 inline-block">Reset filter <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>

    <?php
        $start_item = ($total_transaksi > 0) ? $offset + 1 : 0;
        $end_item = ($total_transaksi > 0) ? min($total_transaksi, $offset + count($riwayat_transaksi)) : 0;
        $base_params = ['page' => 'transaksi/riwayat'];
        if (!empty($filter_warga)) { $base_params['filter_warga'] = $filter_warga; }
        if (!empty($filter_tipe)) { $base_params['filter_tipe'] = $filter_tipe; }
        if (!empty($filter_tanggal_mulai)) { $base_params['filter_tanggal_mulai'] = $filter_tanggal_mulai; }
        if (!empty($filter_tanggal_akhir)) { $base_params['filter_tanggal_akhir'] = $filter_tanggal_akhir; }
        $prev_disabled = ($current_page <= 1);
        $next_disabled = ($current_page >= $total_pages || $total_transaksi === 0);
        if (!$prev_disabled) {
            $prev_params = $base_params;
            $prev_params['p'] = $current_page - 1;
        }
        if (!$next_disabled) {
            $next_params = $base_params;
            $next_params['p'] = $current_page + 1;
        }
    ?>

    <div class="pagination">
        <p class="pagination-info">
            <?php if ($total_transaksi > 0): ?>
                Menampilkan <span class="font-semibold text-slate-700"><?php echo $start_item; ?>-<?php echo $end_item; ?></span> dari <span class="font-semibold text-slate-700"><?php echo $total_transaksi; ?></span> riwayat transaksi.
            <?php else: ?>
                Tidak ada riwayat transaksi untuk ditampilkan.
            <?php endif; ?>
        </p>
        <div class="pagination-links">
            <a href="<?php echo !$prev_disabled ? BASE_URL . 'index.php?' . http_build_query($prev_params) : 'javascript:void(0);'; ?>"
               class="pagination-btn <?php echo $prev_disabled ? 'disabled' : ''; ?>">
                <i class="fas fa-arrow-left"></i> Sebelumnya
            </a>
            <span class="pagination-page">Halaman <?php echo $current_page; ?> dari <?php echo max($total_pages, 1); ?></span>
            <a href="<?php echo !$next_disabled ? BASE_URL . 'index.php?' . http_build_query($next_params) : 'javascript:void(0);'; ?>"
               class="pagination-btn <?php echo $next_disabled ? 'disabled' : ''; ?>">
                Berikutnya <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<?php if ($stmt_transaksi) { mysqli_stmt_close($stmt_transaksi); } ?>
