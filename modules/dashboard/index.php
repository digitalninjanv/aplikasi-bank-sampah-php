<?php
check_user_level(['admin', 'petugas', 'warga']);

$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['user_level'];
$user_nama = $_SESSION['user_nama'];

$chart_labels = [];
$chart_setoran = [];
$chart_penarikan = [];
for ($i = 6; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $chart_labels[] = date('M Y', strtotime("-$i months"));
    $awal = $bulan . '-01 00:00:00';
    $akhir = date('Y-m-t 23:59:59', strtotime($awal));

    $q_setor = "SELECT COALESCE(SUM(total_nilai),0) AS total FROM transaksi WHERE tipe_transaksi='setor' AND tanggal_transaksi BETWEEN ? AND ?";
    $s_setor = mysqli_prepare($koneksi, $q_setor);
    mysqli_stmt_bind_param($s_setor, "ss", $awal, $akhir);
    mysqli_stmt_execute($s_setor);
    $r_setor = mysqli_stmt_get_result($s_setor);
    $chart_setoran[] = (float)mysqli_fetch_assoc($r_setor)['total'];
    mysqli_stmt_close($s_setor);

    $q_tarik = "SELECT COALESCE(SUM(total_nilai),0) AS total FROM transaksi WHERE tipe_transaksi='tarik_saldo' AND tanggal_transaksi BETWEEN ? AND ?";
    $s_tarik = mysqli_prepare($koneksi, $q_tarik);
    mysqli_stmt_bind_param($s_tarik, "ss", $awal, $akhir);
    mysqli_stmt_execute($s_tarik);
    $r_tarik = mysqli_stmt_get_result($s_tarik);
    $chart_penarikan[] = (float)mysqli_fetch_assoc($r_tarik)['total'];
    mysqli_stmt_close($s_tarik);
}

$jumlah_warga = 0;
$jumlah_jenis_sampah = 0;
$total_berat_setoran_bulan_ini = 0;
$total_saldo_bank_sampah = 0;
$aktivitas_terbaru = [];

if ($user_level == 'admin' || $user_level == 'petugas') {
    $query_warga = "SELECT COUNT(*) AS total FROM pengguna WHERE level = 'warga' AND (status IS NULL OR status = 'aktif')";
    $result_warga = mysqli_query($koneksi, $query_warga);
    if($result_warga) $jumlah_warga = mysqli_fetch_assoc($result_warga)['total'];

    $query_jenis = "SELECT COUNT(*) AS total FROM jenis_sampah WHERE (status IS NULL OR status = 'aktif')";
    $result_jenis = mysqli_query($koneksi, $query_jenis);
    if($result_jenis) $jumlah_jenis_sampah = mysqli_fetch_assoc($result_jenis)['total'];

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

    $query_saldo_total = "SELECT SUM(saldo) AS total_saldo FROM pengguna WHERE level = 'warga' AND (status IS NULL OR status = 'aktif')";
    $result_saldo_total = mysqli_query($koneksi, $query_saldo_total);
    if($result_saldo_total) $total_saldo_bank_sampah = mysqli_fetch_assoc($result_saldo_total)['total_saldo'] ?: 0;

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
} elseif ($user_level == 'warga') {
    $query_saldo = "SELECT saldo FROM pengguna WHERE id_pengguna = ?";
    $stmt_saldo = mysqli_prepare($koneksi, $query_saldo);
    mysqli_stmt_bind_param($stmt_saldo, "i", $user_id);
    mysqli_stmt_execute($stmt_saldo);
    $result_saldo = mysqli_stmt_get_result($stmt_saldo);
    $data_warga = mysqli_fetch_assoc($result_saldo);
    $total_saldo_bank_sampah = $data_warga['saldo'] ?? 0;
    mysqli_stmt_close($stmt_saldo);

    $query_aktivitas = "
        SELECT t.id_transaksi, t.tanggal_transaksi, t.tipe_transaksi, t.total_nilai, 
               petugas.nama_lengkap as nama_petugas
        FROM transaksi t
        JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna
        WHERE t.id_warga = ?
        ORDER BY t.tanggal_transaksi DESC
        LIMIT 5
    ";
    $stmt_akt = mysqli_prepare($koneksi, $query_aktivitas);
    mysqli_stmt_bind_param($stmt_akt, "i", $user_id);
    mysqli_stmt_execute($stmt_akt);
    $result_aktivitas = mysqli_stmt_get_result($stmt_akt);
    if($result_aktivitas){
        while($row = mysqli_fetch_assoc($result_aktivitas)){
            $aktivitas_terbaru[] = $row;
        }
    }
    mysqli_stmt_close($stmt_akt);
}
?>

<div class="max-w-7xl mx-auto">
    <div class="page-header">
        <h1>Dashboard <?php echo ($user_level == 'warga') ? 'Saya' : 'Utama'; ?></h1>
        <p>Ringkasan aktivitas Bank Sampah Digital</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php if ($user_level == 'admin' || $user_level == 'petugas'): ?>
        <div class="stat-card bg-blue-600 text-white">
            <div class="relative z-10">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Warga Aktif</p>
                <p class="text-3xl font-bold font-['Poppins'] mt-1"><?php echo $jumlah_warga; ?></p>
            </div>
            <i class="fas fa-users icon-bg"></i>
        </div>
        <div class="stat-card bg-amber-600 text-white">
            <div class="relative z-10">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Jenis Sampah</p>
                <p class="text-3xl font-bold font-['Poppins'] mt-1"><?php echo $jumlah_jenis_sampah; ?></p>
            </div>
            <i class="fas fa-dumpster icon-bg"></i>
        </div>
        <div class="stat-card bg-violet-600 text-white">
            <div class="relative z-10">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Setoran Bulan Ini</p>
                <p class="text-2xl font-bold font-['Poppins'] mt-1"><?php echo number_format($total_berat_setoran_bulan_ini, 2, ',', '.'); ?> Kg</p>
            </div>
            <i class="fas fa-weight-hanging icon-bg"></i>
        </div>
        <?php endif; ?>
        <div class="stat-card bg-emerald-600 text-white">
            <div class="relative z-10">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80"><?php echo ($user_level == 'warga') ? 'Saldo Saya' : 'Total Saldo Bank'; ?></p>
                <p class="text-2xl font-bold font-['Poppins'] mt-1"><?php echo format_rupiah($total_saldo_bank_sampah); ?></p>
            </div>
            <i class="fas fa-wallet icon-bg"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 card p-5">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-blue-600"></i>
                Perbandingan Transaksi (7 Bulan)
            </h2>
            <canvas id="chartTransaksi" height="100"></canvas>
        </div>

        <?php if ($user_level == 'admin' || $user_level == 'petugas'): ?>
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-blue-600"></i>
                Pintasan Cepat
            </h2>
            <div class="space-y-2">
                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/setor" class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition font-medium text-sm">
                    <i class="fas fa-plus-circle text-emerald-600"></i>
                    <span>Input Setoran Sampah</span>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/tarik_saldo" class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition font-medium text-sm">
                    <i class="fas fa-money-bill-wave text-amber-600"></i>
                    <span>Input Tarik Saldo</span>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/tambah" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium text-sm">
                    <i class="fas fa-user-plus text-blue-600"></i>
                    <span>Tambah Warga Baru</span>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/tambah" class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition font-medium text-sm">
                    <i class="fas fa-tag text-amber-600"></i>
                    <span>Tambah Jenis Sampah</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                <i class="fas fa-stream text-blue-600"></i>
                <?php echo ($user_level == 'warga') ? 'Transaksi Terbaru Saya' : 'Aktivitas Transaksi Terbaru'; ?>
            </h2>
            <?php if (!empty($aktivitas_terbaru)): ?>
                <a href="<?php echo BASE_URL; ?>index.php?page=<?php echo ($user_level == 'warga') ? 'laporan/riwayat_warga' : 'transaksi/riwayat'; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php if (!empty($aktivitas_terbaru)): ?>
            <div class="space-y-2">
                <?php foreach($aktivitas_terbaru as $aktivitas): ?>
                    <div class="flex items-start gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition">
                        <div class="shrink-0 mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center text-sm <?php echo $aktivitas['tipe_transaksi'] == 'setor' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'; ?>">
                            <i class="fas <?php echo $aktivitas['tipe_transaksi'] == 'setor' ? 'fa-arrow-down' : 'fa-arrow-up'; ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800">
                                <?php if ($user_level == 'warga'): ?>
                                    <?php echo ($aktivitas['tipe_transaksi'] == 'setor' ? 'Setoran' : 'Penarikan'); ?>
                                    sebesar <span class="font-semibold"><?php echo format_rupiah($aktivitas['total_nilai']); ?></span>
                                    oleh <?php echo htmlspecialchars($aktivitas['nama_petugas']); ?>
                                <?php else: ?>
                                    <?php echo ($aktivitas['tipe_transaksi'] == 'setor' ? 'Setoran baru dari ' : 'Penarikan oleh '); ?>
                                    <span class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama_warga']); ?></span>
                                    sebesar <span class="font-semibold"><?php echo format_rupiah($aktivitas['total_nilai']); ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5"><?php echo format_tanggal_indonesia($aktivitas['tanggal_transaksi']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open text-slate-300"></i>
                <p class="text-slate-500">Belum ada aktivitas transaksi terbaru.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartTransaksi'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Setoran',
                data: <?php echo json_encode($chart_setoran); ?>,
                backgroundColor: 'rgba(5, 150, 105, 0.7)',
                borderColor: 'rgb(5, 150, 105)',
                borderWidth: 1,
                borderRadius: 4
            }, {
                label: 'Penarikan',
                data: <?php echo json_encode($chart_penarikan); ?>,
                backgroundColor: 'rgba(217, 119, 6, 0.7)',
                borderColor: 'rgb(217, 119, 6)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } }
            },
            plugins: {
                tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID'); } } }
            }
        }
    });
});
</script>
