<?php
check_user_level(['admin', 'petugas']);

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
$params = [];
$param_types = "";
$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

if (!empty($search)) {
    $search_term = "%" . $search . "%";
    $query_condition = " AND (nama_sampah LIKE ? OR deskripsi LIKE ?)";
    $param_types .= "ss";
    $params[] = $search_term;
    $params[] = $search_term;
}

$count_query = "SELECT COUNT(*) AS total FROM jenis_sampah WHERE (status IS NULL OR status = 'aktif') $query_condition";
$total_jenis = 0;
$count_stmt = mysqli_prepare($koneksi, $count_query);
if ($count_stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($count_stmt);
    mysqli_stmt_bind_result($count_stmt, $total_jenis);
    mysqli_stmt_fetch($count_stmt);
    mysqli_stmt_close($count_stmt);
}

$total_pages = max(1, (int)ceil($total_jenis / $per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $per_page;

$query = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg, deskripsi, satuan FROM jenis_sampah WHERE (status IS NULL OR status = 'aktif') $query_condition ORDER BY nama_sampah ASC LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($koneksi, $query);
$jenis_sampah = [];
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $jenis_sampah[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="page-header mb-0">
            <h1>Data Jenis Sampah</h1>
            <p>Perbarui harga dan satuan dengan tampilan ringan dan optimal</p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="btn btn-success shrink-0">
            <i class="fas fa-plus"></i> Tambah Jenis Sampah
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-5">
        <input type="hidden" name="page" value="jenis_sampah/data">
        <input type="hidden" name="p" value="1">
        <div class="flex gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari jenis sampah atau deskripsi..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <div class="table-wrap hidden md:block">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Sampah</th>
                    <th>Harga/Satuan</th>
                    <th>Deskripsi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($jenis_sampah)): ?>
                    <?php foreach($jenis_sampah as $index => $row): ?>
                    <tr>
                        <td class="whitespace-nowrap text-slate-500">#<?php echo $offset + $index + 1; ?></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="avatar-initials bg-emerald-100 text-emerald-700"><?php echo strtoupper(substr($row['nama_sampah'], 0, 2)); ?></span>
                                <div>
                                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($row['nama_sampah']); ?></p>
                                    <p class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($row['id_jenis_sampah']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap font-semibold text-slate-700">
                            <?php echo format_rupiah($row['harga_per_kg']); ?> <span class="text-xs text-slate-500">/ <?php echo htmlspecialchars($row['satuan']);?></span>
                        </td>
                        <td class="max-w-md truncate text-slate-500" title="<?php echo htmlspecialchars($row['deskripsi']); ?>"><?php echo htmlspecialchars($row['deskripsi'] ? $row['deskripsi'] : '-'); ?></td>
                        <td class="whitespace-nowrap text-center">
                            <div class="inline-flex gap-1">
                                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/edit&id=<?php echo $row['id_jenis_sampah']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i><span class="hidden lg:inline">Edit</span>
                                </a>
                                <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/hapus" class="inline" onsubmit="return confirm('Nonaktifkan jenis sampah ini? Data tetap tersimpan.');">
                                    <input type="hidden" name="id" value="<?php echo $row['id_jenis_sampah']; ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm text-red-600 border border-red-200 hover:bg-red-50 rounded-lg">
                                        <i class="fas fa-trash"></i><span class="hidden lg:inline">Nonaktifkan</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-recycle text-slate-300"></i>
                                <p>Tidak ada data jenis sampah ditemukan.</p>
                                <?php if(!empty($search)): ?>
                                    <br><a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="text-blue-600 hover:underline mt-2 inline-block">Tampilkan semua jenis sampah</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 md:hidden">
        <?php if (!empty($jenis_sampah)): ?>
            <?php foreach($jenis_sampah as $index => $row): ?>
                <div class="mobile-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="avatar-initials w-10 h-10 rounded-xl text-sm bg-emerald-100 text-emerald-700"><?php echo strtoupper(substr($row['nama_sampah'], 0, 2)); ?></div>
                            <div>
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['nama_sampah']); ?></p>
                                <p class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($row['id_jenis_sampah']); ?></p>
                            </div>
                        </div>
                        <span class="badge badge-blue">#<?php echo $offset + $index + 1; ?></span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm font-semibold text-slate-700 bg-slate-50 px-3 py-2 rounded-lg">
                        <i class="fas fa-tags text-emerald-500"></i>
                        <?php echo format_rupiah($row['harga_per_kg']); ?> <span class="text-xs font-normal text-slate-500">/ <?php echo htmlspecialchars($row['satuan']);?></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed border-t border-dashed border-slate-200 pt-3"><?php echo htmlspecialchars($row['deskripsi'] ? $row['deskripsi'] : 'Belum ada deskripsi.'); ?></p>
                    <div class="mt-3 flex gap-2">
                        <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/edit&id=<?php echo $row['id_jenis_sampah']; ?>" class="btn btn-sm btn-outline flex-1 justify-center">
                            <i class="fas fa-edit"></i>Edit
                        </a>
                        <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/hapus" class="flex-1" onsubmit="return confirm('Nonaktifkan jenis sampah ini? Data tetap tersimpan.');">
                            <input type="hidden" name="id" value="<?php echo $row['id_jenis_sampah']; ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm w-full justify-center text-red-600 border border-red-200 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash"></i>Nonaktifkan
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mobile-card text-center py-8">
                <i class="fas fa-recycle text-3xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 text-sm">Belum ada data jenis sampah.</p>
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="text-blue-600 font-semibold text-sm mt-2 inline-block">Tambah sekarang <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>

    <?php
        $start_item = ($total_jenis > 0) ? $offset + 1 : 0;
        $end_item = ($total_jenis > 0) ? min($total_jenis, $offset + count($jenis_sampah)) : 0;
        $base_params = ['page' => 'jenis_sampah/data'];
        if (!empty($search)) {
            $base_params['search'] = $search;
        }
        $prev_disabled = ($current_page <= 1);
        $next_disabled = ($current_page >= $total_pages || $total_jenis === 0);
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
            <?php if ($total_jenis > 0): ?>
                Menampilkan <span class="font-semibold text-slate-700"><?php echo $start_item; ?>-<?php echo $end_item; ?></span> dari <span class="font-semibold text-slate-700"><?php echo $total_jenis; ?></span> jenis sampah.
            <?php else: ?>
                Belum ada data jenis sampah untuk ditampilkan.
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
