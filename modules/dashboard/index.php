<?php
// modules/dashboard/index.php
check_user_level(['admin', 'petugas']); // Hanya admin dan petugas yang akses dashboard ini

$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['user_level'];
$user_nama = $_SESSION['user_nama'];

// Data default
$jumlah_warga = 0;
$jumlah_jenis_sampah = 0;
$total_berat_setoran_bulan_ini = 0;
$total_saldo_bank_sampah = 0; 
$aktivitas_terbaru = [];

if ($user_level == 'admin' || $user_level == 'petugas') {
    // Ambil data untuk Admin/Petugas
    // Jumlah Warga
    $query_warga = "SELECT COUNT(*) AS total FROM pengguna WHERE level = 'warga'";
    $result_warga = mysqli_query($koneksi, $query_warga);
    if($result_warga) $jumlah_warga = mysqli_fetch_assoc($result_warga)['total'];

    // Jumlah Jenis Sampah
    $query_jenis = "SELECT COUNT(*) AS total FROM jenis_sampah";
    $result_jenis = mysqli_query($koneksi, $query_jenis);
    if($result_jenis) $jumlah_jenis_sampah = mysqli_fetch_assoc($result_jenis)['total'];

    // Total Berat Setoran Bulan Ini
    $bulan_ini_awal = date('Y-m-01 00:00:00');
    $bulan_ini_akhir = date('Y-m-t 23:59:59');
    $query_berat = "SELECT SUM(ds.berat_kg) AS total_berat 
                    FROM detail_setoran ds
                    JOIN transaksi t ON ds.id_transaksi_setor = t.id_transaksi
                    WHERE t.tanggal_transaksi BETWEEN ? AND ?";
    $stmt_berat = mysqli_prepare($koneksi, $query_berat);
    mysqli_stmt_bind_param($stmt_berat, "ss", $bulan_ini_awal, $bulan_ini_akhir);
    mysqli_stmt_execute($stmt_berat);
    $result_berat = mysqli_stmt_get_result($stmt_berat);
    if($result_berat) {
        $data_berat = mysqli_fetch_assoc($result_berat);
        $total_berat_setoran_bulan_ini = $data_berat['total_berat'] ? $data_berat['total_berat'] : 0;
    }
    mysqli_stmt_close($stmt_berat);
    
    // Total Saldo Bank Sampah (akumulasi saldo semua warga)
    $query_saldo_total = "SELECT SUM(saldo) AS total_saldo FROM pengguna WHERE level = 'warga'";
    $result_saldo_total = mysqli_query($koneksi, $query_saldo_total);
    if($result_saldo_total) $total_saldo_bank_sampah = mysqli_fetch_assoc($result_saldo_total)['total_saldo'] ?: 0;

    // Aktivitas Terbaru (5 transaksi terakhir)
    $query_aktivitas = "
        SELECT t.id_transaksi, t.tanggal_transaksi, t.tipe_transaksi, t.total_nilai, 
               warga.nama_lengkap as nama_warga, petugas.nama_lengkap as nama_petugas
        FROM transaksi t
        JOIN pengguna warga ON t.id_warga = warga.id_pengguna
        JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
        ORDER BY t.tanggal_transaksi DESC
        LIMIT 5
    ";
    $result_aktivitas = mysqli_query($koneksi, $query_aktivitas);
    if($result_aktivitas){
        while($row = mysqli_fetch_assoc($result_aktivitas)){
            $aktivitas_terbaru[] = $row;
        }
    }
}

// Karena warga tidak lagi login ke dashboard ini, bagian elseif ($user_level == 'warga') bisa dihapus.
// Kode di bawah ini khusus untuk admin dan petugas.

?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">Dashboard Utama</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-gradient-to-br from-sky-500 to-sky-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider opacity-80">Jumlah Warga</p>
                    <p class="text-4xl font-extrabold"><?php echo $jumlah_warga; ?></p>
                </div>
                <i class="fas fa-users fa-3x opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider opacity-80">Jenis Sampah</p>
                    <p class="text-4xl font-extrabold"><?php echo $jumlah_jenis_sampah; ?></p>
                </div>
                <i class="fas fa-dumpster fa-3x opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider opacity-80">Setoran Bulan Ini</p>
                    <p class="text-3xl font-extrabold"><?php echo number_format($total_berat_setoran_bulan_ini, 2, ',', '.'); ?> Kg</p>
                </div>
                <i class="fas fa-weight-hanging fa-3x opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider opacity-80">Total Saldo Bank</p>
                    <p class="text-3xl font-extrabold"><?php echo format_rupiah($total_saldo_bank_sampah); ?></p>
                </div>
                <i class="fas fa-wallet fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-xl">
            <h2 class="text-xl font-semibold text-gray-700 mb-5 flex items-center">
                <i class="fas fa-stream mr-3 text-sky-500"></i>Aktivitas Transaksi Terbaru
            </h2>
            <?php if (!empty($aktivitas_terbaru)): ?>
                <div class="space-y-4">
                    <?php foreach($aktivitas_terbaru as $aktivitas): ?>
                        <div class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                            <div class="flex-shrink-0 mt-1">
                                <?php if ($aktivitas['tipe_transaksi'] == 'setor'): ?>
                                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-arrow-up"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    <?php echo ($aktivitas['tipe_transaksi'] == 'setor' ? 'Setoran baru dari ' : 'Penarikan oleh '); ?>
                                    <span class="font-semibold text-sky-600"><?php echo htmlspecialchars($aktivitas['nama_warga']); ?></span>
                                    sebesar <span class="font-semibold"><?php echo format_rupiah($aktivitas['total_nilai']); ?></span>.
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo format_tanggal_indonesia($aktivitas['tanggal_transaksi']); ?> - Dicatat oleh <?php echo htmlspecialchars($aktivitas['nama_petugas']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                 <div class="mt-6 text-right">
                    <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="text-sm font-medium text-sky-600 hover:text-sky-800 hover:underline transition">
                        Lihat Semua Riwayat <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Belum ada aktivitas transaksi terbaru.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-xl">
            <h2 class="text-xl font-semibold text-gray-700 mb-5 flex items-center">
                <i class="fas fa-bolt mr-3 text-sky-500"></i>Pintasan Cepat
            </h2>
            <div class="space-y-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/setor" class="flex items-center w-full text-left px-4 py-3 rounded-lg bg-green-500 text-white hover:bg-green-600 focus:bg-green-600 transition duration-200 shadow hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle fa-lg mr-3"></i> 
                    <div>
                        <span class="font-semibold">Input Setoran Sampah</span>
                        <p class="text-xs opacity-80">Catat setoran baru dari warga.</p>
                    </div>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/tarik_saldo" class="flex items-center w-full text-left px-4 py-3 rounded-lg bg-orange-500 text-white hover:bg-orange-600 focus:bg-orange-600 transition duration-200 shadow hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-money-bill-wave fa-lg mr-3"></i> 
                     <div>
                        <span class="font-semibold">Input Tarik Saldo</span>
                        <p class="text-xs opacity-80">Proses penarikan saldo warga.</p>
                    </div>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="flex items-center w-full text-left px-4 py-3 rounded-lg bg-sky-500 text-white hover:bg-sky-600 focus:bg-sky-600 transition duration-200 shadow hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-user-plus fa-lg mr-3"></i> 
                    <div>
                        <span class="font-semibold">Tambah Warga Baru</span>
                        <p class="text-xs opacity-80">Daftarkan warga baru ke sistem.</p>
                    </div>
                </a>
                 <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="flex items-center w-full text-left px-4 py-3 rounded-lg bg-amber-500 text-white hover:bg-amber-600 focus:bg-amber-600 transition duration-200 shadow hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-tag fa-lg mr-3"></i> 
                    <div>
                        <span class="font-semibold">Tambah Jenis Sampah</span>
                        <p class="text-xs opacity-80">Kelola daftar jenis sampah.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
