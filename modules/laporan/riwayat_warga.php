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
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Transaksi <?php echo $is_admin_or_petugas && isset($_GET['id_warga_filter']) ? htmlspecialchars($nama_warga_login) : "Saya"; ?></h1>
        <?php if (!$is_admin_or_petugas): // Hanya tampilkan saldo untuk warga yang melihat riwayatnya sendiri ?>
        <div class="text-right">
            <p class="text-md text-gray-600">Saldo Anda Saat Ini:</p>
            <p class="text-2xl font-bold text-green-600"><?php echo format_rupiah($saldo_warga_saat_ini); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($is_admin_or_petugas): ?>
    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="mb-6 bg-white p-4 rounded-lg shadow">
        <input type="hidden" name="page" value="laporan/riwayat_warga">
        <div class="flex items-end space-x-3">
            <div>
                <label for="id_warga_filter_select" class="block text-sm font-medium text-gray-700">Lihat Riwayat Warga Lain:</label>
                <select name="id_warga_filter" id="id_warga_filter_select" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
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
            <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md">
                Tampilkan
            </button>
        </div>
    </form>
    <?php endif; ?>


    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Tanggal & Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Tipe Transaksi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Nilai (Rp)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Dicatat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Detail/Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_riwayat && mysqli_num_rows($result_riwayat) > 0): ?>
                        <?php while($trx = mysqli_fetch_assoc($result_riwayat)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo format_tanggal_indonesia($trx['tanggal_transaksi']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Setor Sampah</span>
                                <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Tarik Saldo</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 text-right font-medium"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trx['nama_petugas']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-sm">
                                <?php 
                                if ($trx['tipe_transaksi'] == 'setor') {
                                    $query_detail_items_warga = "SELECT js.nama_sampah, ds.berat_kg, ds.harga_saat_setor, ds.subtotal_nilai 
                                                           FROM detail_setoran ds 
                                                           JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah 
                                                           WHERE ds.id_transaksi_setor = ?";
                                    $stmt_items_warga = mysqli_prepare($koneksi, $query_detail_items_warga);
                                    mysqli_stmt_bind_param($stmt_items_warga, "i", $trx['id_transaksi']);
                                    mysqli_stmt_execute($stmt_items_warga);
                                    $result_items_warga = mysqli_stmt_get_result($stmt_items_warga);
                                    if(mysqli_num_rows($result_items_warga) > 0){
                                        echo "<ul class='list-disc list-inside text-xs'>";
                                        while($item_warga = mysqli_fetch_assoc($result_items_warga)){
                                            echo "<li>" . htmlspecialchars($item_warga['nama_sampah']) . ": " . $item_warga['berat_kg'] . "kg @ " . format_rupiah($item_warga['harga_saat_setor']) . " = " . format_rupiah($item_warga['subtotal_nilai']) . "</li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "Detail item tidak ditemukan.";
                                    }
                                    mysqli_stmt_close($stmt_items_warga);
                                     if(!empty($trx['keterangan_transaksi'])) echo "<p class='mt-1 text-xs italic'>Ket: " . htmlspecialchars($trx['keterangan_transaksi']) . "</p>";
                                } else {
                                    echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-');
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                Belum ada riwayat transaksi untuk ditampilkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php mysqli_stmt_close($stmt_riwayat); ?>
</div>
