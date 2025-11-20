<?php
// modules/jenis_sampah/index.php
check_user_level(['admin', 'petugas']); // Hanya admin dan petugas

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$query_condition = "";
if (!empty($search)) {
    $query_condition = " WHERE nama_sampah LIKE '%$search%' OR deskripsi LIKE '%$search%'";
}

$per_page = 10;
$current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

$count_query = "SELECT COUNT(*) AS total FROM jenis_sampah $query_condition";
$count_result = mysqli_query($koneksi, $count_query);
$total_jenis = 0;
if ($count_result) {
    $row_count = mysqli_fetch_assoc($count_result);
    $total_jenis = (int)$row_count['total'];
}

$total_pages = max(1, (int)ceil($total_jenis / $per_page));
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * $per_page;

$query = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg, deskripsi, satuan FROM jenis_sampah $query_condition ORDER BY nama_sampah ASC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$jenis_sampah = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jenis_sampah[] = $row;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-gradient-to-br from-emerald-500/10 via-sky-500/10 to-indigo-500/10 border border-emerald-100 rounded-2xl p-5 sm:p-6 shadow-xl mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-emerald-600 font-semibold">Inventori Sampah</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Data Jenis Sampah</h1>
            <p class="text-sm text-gray-600 mt-1">Perbarui harga dan satuan dengan tampilan ringan dan optimal di mobile.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-sky-500 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition w-full sm:w-auto">
            <i class="fas fa-plus"></i> Tambah Jenis Sampah
        </a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-6">
        <input type="hidden" name="page" value="jenis_sampah/data">
        <input type="hidden" name="p" value="1">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search text-emerald-500 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari jenis sampah atau deskripsi..." class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm bg-white">
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-md">
                Cari
            </button>
        </div>
    </form>

    <div class="space-y-4">
        <div class="hidden md:block bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Sampah</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Harga/Satuan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deskripsi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($jenis_sampah)): ?>
                            <?php foreach($jenis_sampah as $index => $row): ?>
                            <tr class="hover:bg-emerald-50/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">#<?php echo $offset + $index + 1; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                        <?php echo strtoupper(substr($row['nama_sampah'], 0, 2)); ?>
                                    </span>
                                    <div class="leading-tight">
                                        <p><?php echo htmlspecialchars($row['nama_sampah']); ?></p>
                                        <p class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($row['id_jenis_sampah']); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                                    <?php echo format_rupiah($row['harga_per_kg']); ?> <span class="text-xs text-gray-500">/ <?php echo htmlspecialchars($row['satuan']);?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-md truncate" title="<?php echo htmlspecialchars($row['deskripsi']); ?>"><?php echo htmlspecialchars($row['deskripsi'] ? $row['deskripsi'] : '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/edit&id=<?php echo $row['id_jenis_sampah']; ?>" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                        <i class="fas fa-edit"></i><span class="hidden lg:inline">Edit</span>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/hapus&id=<?php echo $row['id_jenis_sampah']; ?>"
                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus jenis sampah ini? Ini mungkin mempengaruhi data transaksi yang ada.');">
                                       <i class="fas fa-trash"></i><span class="hidden lg:inline">Hapus</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-recycle fa-3x text-gray-400"></i>
                                        Tidak ada data jenis sampah ditemukan.
                                        <?php if(!empty($search)): ?>
                                            <br>Coba kata kunci lain atau <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="text-emerald-600 hover:underline">tampilkan semua jenis sampah</a>.
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-4 md:hidden">
            <?php if (!empty($jenis_sampah)): ?>
                <?php foreach($jenis_sampah as $index => $row): ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">
                                    <?php echo strtoupper(substr($row['nama_sampah'], 0, 2)); ?>
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-gray-900 leading-tight"><?php echo htmlspecialchars($row['nama_sampah']); ?></p>
                                    <p class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($row['id_jenis_sampah']); ?></p>
                                </div>
                            </div>
                            <span class="text-xs px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full font-semibold">#<?php echo $offset + $index + 1; ?></span>
                        </div>

                        <div class="mt-3 text-sm text-gray-700 font-semibold flex items-center gap-2">
                            <i class="fas fa-tags text-emerald-500"></i>
                            <?php echo format_rupiah($row['harga_per_kg']); ?> <span class="text-xs text-gray-500">/ <?php echo htmlspecialchars($row['satuan']);?></span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed border-t border-dashed border-gray-200 pt-3"><?php echo htmlspecialchars($row['deskripsi'] ? $row['deskripsi'] : 'Belum ada deskripsi.'); ?></p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/edit&id=<?php echo $row['id_jenis_sampah']; ?>" class="flex-1 min-w-[48%] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-sm transition">
                                <i class="fas fa-edit"></i>Edit
                            </a>
                            <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/hapus&id=<?php echo $row['id_jenis_sampah']; ?>"
                               class="flex-1 min-w-[48%] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold text-sm transition"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus jenis sampah ini? Ini mungkin mempengaruhi data transaksi yang ada.');">
                               <i class="fas fa-trash"></i>Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-md">
                    <i class="fas fa-recycle fa-2x text-gray-400 mb-2"></i>
                    <p class="text-gray-600 text-sm">Belum ada data jenis sampah.</p>
                    <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="mt-3 inline-flex items-center gap-2 text-emerald-600 font-semibold">Tambah sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
            <?php endif; ?>
        </div>
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

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-sm text-gray-600 text-center sm:text-left">
            <?php if ($total_jenis > 0): ?>
                Menampilkan <span class="font-semibold"><?php echo $start_item; ?>-<?php echo $end_item; ?></span> dari <span class="font-semibold"><?php echo $total_jenis; ?></span> jenis sampah.
            <?php else: ?>
                Belum ada data jenis sampah untuk ditampilkan.
            <?php endif; ?>
        </p>
        <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
            <a href="<?php echo !$prev_disabled ? BASE_URL . 'index.php?' . http_build_query($prev_params) : 'javascript:void(0);'; ?>"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold w-full sm:w-auto <?php echo $prev_disabled ? 'text-gray-400 border-gray-200 cursor-not-allowed bg-gray-50' : 'text-emerald-600 border-emerald-200 bg-emerald-50 hover:bg-emerald-100'; ?>">
                <i class="fas fa-arrow-left"></i> Sebelumnya
            </a>
            <span class="text-sm text-gray-500 text-center">Halaman <?php echo $current_page; ?> dari <?php echo max($total_pages, 1); ?></span>
            <a href="<?php echo !$next_disabled ? BASE_URL . 'index.php?' . http_build_query($next_params) : 'javascript:void(0);'; ?>"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold w-full sm:w-auto <?php echo $next_disabled ? 'text-gray-400 border-gray-200 cursor-not-allowed bg-gray-50' : 'text-emerald-600 border-emerald-200 bg-emerald-50 hover:bg-emerald-100'; ?>">
                Berikutnya <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
