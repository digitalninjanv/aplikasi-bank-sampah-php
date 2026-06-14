<?php
check_user_level(['admin', 'petugas']);

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
$params_type = "";
$params_value = [];
$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($current_page - 1) * $per_page;

$base_condition = "level = 'warga' AND (status IS NULL OR status = 'aktif')";

if (!empty($search)) {
    $search_term = "%" . $search . "%";
    $query_condition = " AND (nama_lengkap LIKE ? OR username LIKE ? OR alamat LIKE ? OR no_telepon LIKE ?)";
    $params_type = "ssss";
    for ($i = 0; $i < substr_count($params_type, 's'); $i++) {
        $params_value[] = $search_term;
    }
}

$count_query = "SELECT COUNT(*) AS total FROM pengguna WHERE $base_condition $query_condition";
$total_warga = 0;
$count_stmt = mysqli_prepare($koneksi, $count_query);
if ($count_stmt) {
    if (!empty($search)) {
        mysqli_stmt_bind_param($count_stmt, $params_type, ...$params_value);
    }
    mysqli_stmt_execute($count_stmt);
    mysqli_stmt_bind_result($count_stmt, $total_warga);
    mysqli_stmt_fetch($count_stmt);
    mysqli_stmt_close($count_stmt);
}

$total_pages = max(1, (int)ceil($total_warga / $per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $per_page;

$query_string = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon, saldo, tanggal_daftar
                 FROM pengguna
                 WHERE $base_condition $query_condition
                 ORDER BY nama_lengkap ASC
                 LIMIT $per_page OFFSET $offset";

$stmt = mysqli_prepare($koneksi, $query_string);
if (!empty($search) && $stmt) {
    mysqli_stmt_bind_param($stmt, $params_type, ...$params_value);
}
$data_warga = [];
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_warga[] = $row;
        }
    }
} else {
    error_log("MySQLi prepare error in warga/index.php: " . mysqli_error($koneksi));
    $result = false;
}
?>

<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="page-header mb-0">
            <h1>Data Warga Terdaftar</h1>
            <p>Pantau dan kelola akun warga dengan tampilan yang nyaman</p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="btn btn-success shrink-0">
            <i class="fas fa-user-plus"></i> Tambah Warga Baru
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-5">
        <input type="hidden" name="page" value="warga/data">
        <input type="hidden" name="p" value="1">
        <div class="flex gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari berdasarkan nama, no. telepon, atau alamat..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <div class="table-wrap hidden md:block">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>No. Telepon</th>
                    <th>Saldo</th>
                    <th>Alamat</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_warga)): ?>
                    <?php foreach($data_warga as $index => $row): ?>
                    <tr>
                        <td class="whitespace-nowrap text-slate-500"><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="avatar-initials"><?php echo strtoupper(substr($row['nama_lengkap'], 0, 2)); ?></span>
                                <div>
                                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($row['nama_lengkap']); ?></p>
                                    <p class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($row['id_pengguna']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap"><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                        <td class="whitespace-nowrap font-semibold text-emerald-600"><?php echo format_rupiah($row['saldo']); ?></td>
                        <td class="max-w-xs truncate text-slate-500" title="<?php echo htmlspecialchars($row['alamat']); ?>"><?php echo htmlspecialchars($row['alamat'] ? $row['alamat'] : '-'); ?></td>
                        <td class="whitespace-nowrap text-center">
                            <div class="inline-flex gap-1">
                                <a href="<?php echo BASE_URL; ?>index.php?page=warga/edit&id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i><span class="hidden lg:inline">Edit</span>
                                </a>
                                <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=warga/hapus" class="inline" onsubmit="return confirm('Nonaktifkan warga ini? Data tetap tersimpan.');">
                                    <input type="hidden" name="id" value="<?php echo $row['id_pengguna']; ?>">
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
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-users-slash text-slate-300"></i>
                                <?php if(!empty($search)): ?>
                                    <p>Tidak ada data warga ditemukan dengan kata kunci "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
                                    <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="text-blue-600 hover:underline mt-2 inline-block">Tampilkan semua warga</a>
                                <?php else: ?>
                                    <p>Belum ada data warga terdaftar.</p>
                                    <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="text-blue-600 hover:underline mt-2 inline-block">Tambahkan warga baru sekarang.</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 md:hidden">
        <?php if (!empty($data_warga)): ?>
            <?php foreach($data_warga as $index => $row): ?>
                <div class="mobile-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="avatar-initials w-10 h-10 rounded-xl text-sm"><?php echo strtoupper(substr($row['nama_lengkap'], 0, 2)); ?></div>
                            <div>
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['nama_lengkap']); ?></p>
                                <p class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($row['id_pengguna']); ?></p>
                            </div>
                        </div>
                        <span class="badge badge-blue">#<?php echo $offset + $index + 1; ?></span>
                    </div>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="fas fa-phone text-blue-500 w-4"></i>
                            <span><?php echo htmlspecialchars($row['no_telepon'] ?: '-'); ?></span>
                        </div>
                        <div class="flex items-start gap-2 text-slate-600">
                            <i class="fas fa-map-marker-alt text-blue-500 w-4 mt-0.5"></i>
                            <span><?php echo htmlspecialchars($row['alamat'] ?: '-'); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-emerald-700 font-semibold bg-emerald-50 px-3 py-2 rounded-lg text-sm">
                            <i class="fas fa-wallet text-emerald-500"></i>
                            <span><?php echo format_rupiah($row['saldo']); ?></span>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="<?php echo BASE_URL; ?>index.php?page=warga/edit&id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline flex-1 justify-center">
                            <i class="fas fa-edit"></i>Edit
                        </a>
                        <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=warga/hapus" class="flex-1" onsubmit="return confirm('Nonaktifkan warga ini? Data tetap tersimpan.');">
                            <input type="hidden" name="id" value="<?php echo $row['id_pengguna']; ?>">
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
                <i class="fas fa-users-slash text-3xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 text-sm">Belum ada data warga terdaftar.</p>
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="text-blue-600 font-semibold text-sm mt-2 inline-block">Tambah sekarang <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>

    <?php
        $start_item = ($total_warga > 0) ? $offset + 1 : 0;
        $end_item = ($total_warga > 0) ? min($total_warga, $offset + count($data_warga)) : 0;
        $base_params = ['page' => 'warga/data'];
        if (!empty($search)) {
            $base_params['search'] = $search;
        }
        $prev_disabled = ($current_page <= 1);
        $next_disabled = ($current_page >= $total_pages || $total_warga === 0);
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
            <?php if ($total_warga > 0): ?>
                Menampilkan <span class="font-semibold text-slate-700"><?php echo $start_item; ?>-<?php echo $end_item; ?></span> dari <span class="font-semibold text-slate-700"><?php echo $total_warga; ?></span> warga terdaftar.
            <?php else: ?>
                Belum ada data warga untuk ditampilkan.
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

<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
?>
