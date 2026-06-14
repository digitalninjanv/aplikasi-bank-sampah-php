<?php
check_user_level(['admin']);
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
$params = [];
$param_types = "";
$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

if (!empty($search)) {
    $search_term = "%" . $search . "%";
    $query_condition = " AND (nama_lengkap LIKE ? OR username LIKE ?)";
    $param_types = "ss";
    $params = [$search_term, $search_term];
}

$count_query = "SELECT COUNT(*) AS total FROM pengguna WHERE level = 'petugas' $query_condition";
$total = 0;
$stmt = mysqli_prepare($koneksi, $count_query);
if ($stmt) {
    if (!empty($params)) mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int)ceil($total / $per_page));
if ($current_page > $total_pages) $current_page = $total_pages;
$offset = ($current_page - 1) * $per_page;

$query = "SELECT id_pengguna, nama_lengkap, username, alamat, no_telepon, tanggal_daftar, status, last_login FROM pengguna WHERE level = 'petugas' $query_condition ORDER BY nama_lengkap ASC LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($koneksi, $query);
if ($stmt) {
    if (!empty($params)) mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="page-header mb-0">
            <h1>Data Petugas</h1>
            <p>Kelola akun petugas bank sampah</p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php?page=pengelola/tambah_petugas" class="btn btn-primary shrink-0">
            <i class="fas fa-user-plus"></i> Tambah Petugas
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-5">
        <input type="hidden" name="page" value="pengelola/petugas">
        <input type="hidden" name="p" value="1">
        <div class="flex gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari petugas..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>No. Telepon</th>
                    <th class="text-center">Status</th>
                    <th>Terakhir Login</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data)): ?>
                    <?php foreach($data as $row): ?>
                    <tr>
                        <td class="whitespace-nowrap font-semibold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td class="whitespace-nowrap text-slate-600"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="whitespace-nowrap text-slate-600"><?php echo htmlspecialchars($row['no_telepon'] ?: '-'); ?></td>
                        <td class="whitespace-nowrap text-center">
                            <?php if ($row['status'] === 'aktif' || $row['status'] === NULL): ?>
                                <span class="badge badge-green">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-red">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap text-slate-500 text-sm"><?php echo $row['last_login'] ? format_tanggal_indonesia($row['last_login']) : 'Belum pernah'; ?></td>
                        <td class="whitespace-nowrap text-center">
                            <div class="inline-flex gap-1">
                                <a href="<?php echo BASE_URL; ?>index.php?page=pengelola/edit_petugas&id=<?php echo $row['id_pengguna']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($row['id_pengguna'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=pengelola/hapus_petugas" class="inline" onsubmit="return confirm('Nonaktifkan petugas ini?')">
                                    <input type="hidden" name="id" value="<?php echo $row['id_pengguna']; ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm text-red-600 border border-red-200 hover:bg-red-50 rounded-lg">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-user-tie text-slate-300"></i>
                                <p>Belum ada data petugas.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
        $start_item = ($total > 0) ? $offset + 1 : 0;
        $end_item = ($total > 0) ? min($total, $offset + count($data)) : 0;
        $base_params = ['page' => 'pengelola/petugas'];
        if (!empty($search)) { $base_params['search'] = $search; }
        $prev_disabled = ($current_page <= 1);
        $next_disabled = ($current_page >= $total_pages || $total === 0);
        if (!$prev_disabled) { $prev_params = $base_params; $prev_params['p'] = $current_page - 1; }
        if (!$next_disabled) { $next_params = $base_params; $next_params['p'] = $current_page + 1; }
    ?>

    <div class="pagination">
        <p class="pagination-info">
            <?php if ($total > 0): ?>
                Menampilkan <span class="font-semibold text-slate-700"><?php echo $start_item; ?>-<?php echo $end_item; ?></span> dari <span class="font-semibold text-slate-700"><?php echo $total; ?></span> petugas.
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
<?php if (isset($stmt) && $stmt) mysqli_stmt_close($stmt); ?>
