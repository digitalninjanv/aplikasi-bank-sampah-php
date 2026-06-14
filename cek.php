<?php
require_once 'config/database.php';

$input_pencarian_display = '';
$warga_data = null;
$riwayat_transaksi = [];
$error_message_public = '';
$info_message_public = '';
$no_telepon_terdaftar = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cek_info'])) {
    $input_pencarian_post = sanitize_input($_POST['input_pencarian']);
    if (empty($input_pencarian_post)) {
        redirect(BASE_URL . 'cek.php?error=empty_input');
    } else {
        redirect(BASE_URL . 'cek.php?q=' . urlencode($input_pencarian_post));
    }
    exit();
}

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $input_pencarian_get = sanitize_input($_GET['q']);
    $input_pencarian_display = $input_pencarian_get;

    $query_warga = "SELECT id_pengguna, nama_lengkap, username, saldo, alamat, tanggal_daftar, no_telepon FROM pengguna WHERE (no_telepon = ? OR nama_lengkap = ?) AND level = 'warga' ORDER BY CASE WHEN no_telepon = ? THEN 1 WHEN nama_lengkap = ? THEN 2 ELSE 3 END LIMIT 1";
    $stmt_warga = mysqli_prepare($koneksi, $query_warga);
    mysqli_stmt_bind_param($stmt_warga, "ssss", $input_pencarian_get, $input_pencarian_get, $input_pencarian_get, $input_pencarian_get);
    mysqli_stmt_execute($stmt_warga);
    $result_warga_data = mysqli_stmt_get_result($stmt_warga);
    $warga_data = mysqli_fetch_assoc($result_warga_data);
    mysqli_stmt_close($stmt_warga);

    if ($warga_data) {
        $id_warga_ditemukan = $warga_data['id_pengguna'];
        $no_telepon_terdaftar = $warga_data['no_telepon'];

        $query_riwayat = "SELECT t.tanggal_transaksi, t.tipe_transaksi, t.total_nilai, t.keterangan AS keterangan_transaksi, petugas.nama_lengkap AS nama_petugas FROM transaksi t LEFT JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna WHERE t.id_warga = ? ORDER BY t.tanggal_transaksi DESC LIMIT 15";
        $stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $id_warga_ditemukan);
        mysqli_stmt_execute($stmt_riwayat);
        $result_riwayat_data = mysqli_stmt_get_result($stmt_riwayat);
        while ($row = mysqli_fetch_assoc($result_riwayat_data)) { $riwayat_transaksi[] = $row; }
        mysqli_stmt_close($stmt_riwayat);

        if (empty($riwayat_transaksi)) {
            $info_message_public = "Informasi untuk " . htmlspecialchars($warga_data['nama_lengkap']) . " ditemukan. Belum ada riwayat transaksi untuk ditampilkan.";
        }
    } else {
        $error_message_public = "Warga dengan nama atau nomor telepon '".htmlspecialchars($input_pencarian_get)."' tidak ditemukan.";
    }
} elseif (isset($_GET['error']) && $_GET['error'] == 'empty_input') {
    $error_message_public = "Kolom pencarian tidak boleh kosong. Silakan masukkan Nama atau Nomor Telepon Anda.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Info Saldo & Riwayat - Bank Sampah Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
        h1, h2, h3 { font-family: 'Poppins', 'Inter', system-ui, sans-serif; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(15,23,42,0.06); }
        .form-input { display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: white; padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #0f172a; transition: all 0.15s ease; }
        .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s ease; cursor: pointer; border: none; line-height: 1.25rem; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: #d1fae5; color: #047857; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .table-wrap { overflow-x: auto; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: white; }
        .table-wrap table { width: 100%; border-collapse: collapse; }
        .table-wrap th { padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .table-wrap td { padding: 0.75rem 1rem; font-size: 0.875rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table-wrap tr:last-child td { border-bottom: none; }
        .table-wrap tbody tr:hover { background: #f8fafc; }
    </style>
</head>
<body class="antialiased">
    <div class="flex-1">
        <header class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-10 sm:py-14 shadow-lg">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>cek.php" class="inline-flex items-center gap-3 group">
                    <i class="fas fa-recycle fa-3x text-blue-400 group-hover:rotate-12 transition-transform duration-300"></i>
                    <h1 class="text-3xl sm:text-4xl font-bold font-['Poppins']">Bank Sampah Digital</h1>
                </a>
                <p class="text-lg mt-2 text-slate-300">Cek Saldo dan Riwayat Transaksi Anda Dengan Mudah</p>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 sm:py-10">
            <div class="card p-6 sm:p-8 max-w-xl mx-auto">
                <h2 class="text-xl font-bold font-['Poppins'] text-slate-800 mb-6 text-center flex items-center justify-center gap-2">
                    <i class="fas fa-search-dollar text-blue-600"></i>Temukan Informasi Akun Anda
                </h2>
                <form action="cek.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="input_pencarian" class="form-label">Masukkan Nama Lengkap atau Nomor Telepon Terdaftar:</label>
                            <div class="relative">
                                <i class="fas fa-id-card text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                                <input type="text" name="input_pencarian" id="input_pencarian" value="<?php echo htmlspecialchars($input_pencarian_display); ?>" required class="form-input pl-10" placeholder="Ketik nama atau nomor telepon...">
                            </div>
                        </div>
                        <button type="submit" name="cek_info" class="btn btn-primary w-full"><i class="fas fa-search"></i> Cek Informasi</button>
                    </div>
                </form>
            </div>

            <?php if (!empty($error_message_public)): ?>
                <div class="max-w-xl mx-auto mt-6 card p-4 bg-red-50 border-l-4 border-red-500">
                    <div class="flex items-start gap-3"><i class="fas fa-times-circle text-red-500 mt-0.5"></i><p class="text-sm font-medium text-red-700"><?php echo $error_message_public; ?></p></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($info_message_public)): ?>
                <div class="max-w-xl mx-auto mt-6 card p-4 bg-blue-50 border-l-4 border-blue-500">
                    <div class="flex items-start gap-3"><i class="fas fa-info-circle text-blue-500 mt-0.5"></i><p class="text-sm font-medium text-blue-700"><?php echo $info_message_public; ?></p></div>
                </div>
            <?php endif; ?>

            <?php if ($warga_data): ?>
            <div class="card p-6 sm:p-8 mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 pb-4 border-b border-slate-200 gap-4">
                    <div>
                        <h2 class="text-xl font-bold font-['Poppins'] text-slate-800 flex items-center gap-2"><i class="fas fa-user-check text-emerald-500"></i><?php echo htmlspecialchars($warga_data['nama_lengkap']); ?></h2>
                        <p class="text-sm text-slate-500">Terdaftar sejak: <?php echo format_tanggal_indonesia($warga_data['tanggal_daftar']); ?></p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm text-slate-500">Saldo Anda:</p>
                        <p class="text-3xl font-bold font-['Poppins'] text-emerald-600"><?php echo format_rupiah($warga_data['saldo']); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6 text-sm">
                    <div class="flex items-center gap-2 text-slate-600"><i class="fas fa-phone text-blue-500 w-4"></i><span><strong>No. Telepon:</strong> <?php echo htmlspecialchars($no_telepon_terdaftar); ?></span></div>
                    <div class="flex items-start gap-2 text-slate-600 md:col-span-2"><i class="fas fa-map-marker-alt text-blue-500 w-4 mt-0.5"></i><span><strong>Alamat:</strong> <?php echo htmlspecialchars($warga_data['alamat'] ?: '-'); ?></span></div>
                </div>

                <h3 class="text-lg font-bold font-['Poppins'] text-slate-800 mb-4 pt-4 border-t border-slate-200 flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i>Riwayat Transaksi Terbaru
                </h3>
                <?php if (!empty($riwayat_transaksi)): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Tanggal</th><th>Tipe</th><th class="text-right">Nilai (Rp)</th><th>Keterangan</th><th class="hidden sm:table-cell">Pencatat</th></tr></thead>
                        <tbody>
                            <?php foreach ($riwayat_transaksi as $trx): ?>
                            <tr>
                                <td class="whitespace-nowrap text-sm text-slate-600"><?php echo format_tanggal_indonesia($trx['tanggal_transaksi']); ?></td>
                                <td class="whitespace-nowrap"><?php if ($trx['tipe_transaksi'] == 'setor'): ?><span class="badge badge-green"><i class="fas fa-arrow-down mr-1"></i>Setoran</span><?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?><span class="badge badge-amber"><i class="fas fa-arrow-up mr-1"></i>Penarikan</span><?php else: ?><span class="badge" style="background:#f1f5f9;color:#475569;"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span><?php endif; ?></td>
                                <td class="whitespace-nowrap text-sm text-right font-semibold"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                                <td class="text-sm text-slate-500 max-w-[150px] truncate" title="<?php echo htmlspecialchars($trx['keterangan_transaksi']); ?>"><?php echo htmlspecialchars($trx['keterangan_transaksi'] ?: '-'); ?></td>
                                <td class="whitespace-nowrap text-sm text-slate-500 hidden sm:table-cell"><?php echo htmlspecialchars($trx['nama_petugas'] ?: 'Sistem'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-6"><i class="fas fa-folder-open fa-3x text-slate-300 mb-3"></i><p class="text-slate-500">Belum ada riwayat transaksi.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <footer class="text-center py-5 bg-slate-900 text-sm text-slate-400 print:hidden">
        <p>&copy; <?php echo date('Y'); ?> <?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Bank Sampah Digital'; ?>. All rights reserved.</p>
    </footer>
</body>
</html>
