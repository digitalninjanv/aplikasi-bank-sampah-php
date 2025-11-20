<?php
// cek_info_warga.php (Halaman Publik)
require_once 'config/database.php'; // Untuk koneksi dan fungsi format_rupiah, format_tanggal_indonesia

$input_pencarian_display = ''; // Untuk menampilkan di form
$warga_data = null;
$riwayat_transaksi = [];
$error_message_public = '';
$info_message_public = ''; 
$no_telepon_terdaftar = ''; // Untuk menyimpan no telepon yang benar dari DB

// Proses POST request (saat form disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cek_info'])) {
    $input_pencarian_post = sanitize_input($_POST['input_pencarian']);
    if (empty($input_pencarian_post)) {
        // Jika input kosong, redirect kembali dengan pesan error
        // Kita bisa menggunakan session untuk pesan error singkat atau parameter GET
        // Untuk kesederhanaan, kita akan redirect dan biarkan GET handler menampilkan error jika q kosong
        redirect(BASE_URL . 'cek.php?error=empty_input');
    } else {
        // Redirect ke halaman yang sama dengan parameter GET
        redirect(BASE_URL . 'cek.php?q=' . urlencode($input_pencarian_post));
    }
    exit(); // Pastikan skrip berhenti setelah redirect
}

// Proses GET request (setelah redirect atau saat halaman dimuat langsung dengan parameter q)
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $input_pencarian_get = sanitize_input($_GET['q']);
    $input_pencarian_display = $input_pencarian_get; // Untuk ditampilkan di form

    // Cari warga berdasarkan nomor telepon ATAU nama lengkap
    $query_warga = "
        SELECT id_pengguna, nama_lengkap, username, saldo, alamat, tanggal_daftar, no_telepon 
        FROM pengguna 
        WHERE (no_telepon = ? OR nama_lengkap = ?) AND level = 'warga'
        ORDER BY CASE
            WHEN no_telepon = ? THEN 1
            WHEN nama_lengkap = ? THEN 2
            ELSE 3
        END
        LIMIT 1";
    
    $stmt_warga = mysqli_prepare($koneksi, $query_warga);
    mysqli_stmt_bind_param($stmt_warga, "ssss", $input_pencarian_get, $input_pencarian_get, $input_pencarian_get, $input_pencarian_get);
    mysqli_stmt_execute($stmt_warga);
    $result_warga_data = mysqli_stmt_get_result($stmt_warga);
    $warga_data = mysqli_fetch_assoc($result_warga_data);
    mysqli_stmt_close($stmt_warga);

    if ($warga_data) {
        $id_warga_ditemukan = $warga_data['id_pengguna'];
        $no_telepon_terdaftar = $warga_data['no_telepon']; 

        $query_riwayat = "
            SELECT 
                t.tanggal_transaksi, 
                t.tipe_transaksi, 
                t.total_nilai, 
                t.keterangan AS keterangan_transaksi,
                petugas.nama_lengkap AS nama_petugas
            FROM transaksi t
            LEFT JOIN pengguna petugas ON t.id_petugas_pencatat = petugas.id_pengguna 
            WHERE t.id_warga = ?
            ORDER BY t.tanggal_transaksi DESC
            LIMIT 15";
        $stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $id_warga_ditemukan);
        mysqli_stmt_execute($stmt_riwayat);
        $result_riwayat_data = mysqli_stmt_get_result($stmt_riwayat);
        while ($row = mysqli_fetch_assoc($result_riwayat_data)) {
            $riwayat_transaksi[] = $row;
        }
        mysqli_stmt_close($stmt_riwayat);
        
        if (empty($riwayat_transaksi)) {
             $info_message_public = "Informasi untuk " . htmlspecialchars($warga_data['nama_lengkap']) . " ditemukan. Belum ada riwayat transaksi untuk ditampilkan.";
        }
    } else {
        $error_message_public = "Warga dengan nama atau nomor telepon '".htmlspecialchars($input_pencarian_get)."' tidak ditemukan. Pastikan data yang Anda masukkan benar dan terdaftar.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', 'Inter', sans-serif; 
            background-color: #f0f7ff; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex-grow: 1;
        }
        .gradient-header {
            background: linear-gradient(135deg, #38b2ac 0%, #3182ce 100%); 
        }
        .btn-primary {
            background-color: #3182ce; 
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2c5282; 
        }
        .card {
            background-color: white;
            border-radius: 0.75rem; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
        }
        .table th, .table td {
            padding: 0.75rem 1rem; 
        }
        .table thead th {
            background-color: #edf2f7; 
        }
        .table tbody tr:nth-child(even) {
            background-color: #f7fafc; 
        }
        .input-icon-wrapper input {
            padding-left: 2.5rem; /* Space for icon */
        }
    </style>
</head>
<body class="antialiased">
    <div class="content-wrapper">
        <header class="gradient-header text-white py-8 sm:py-12 shadow-lg">
            <div class="container mx-auto px-4 text-center">
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>cek.php" class="inline-flex items-center space-x-3 group"> <i class="fas fa-recycle fa-3x transform group-hover:rotate-12 transition-transform duration-300"></i>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Bank Sampah Digital</h1>
                </a>
                <p class="text-lg sm:text-xl mt-2 opacity-90">Cek Saldo dan Riwayat Transaksi Anda Dengan Mudah</p>
            </div>
        </header>

        <main class="container mx-auto px-4 py-8 sm:py-10">
            <div class="max-w-xl mx-auto card p-6 sm:p-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">
                    <i class="fas fa-search-dollar mr-2 text-sky-600"></i>Temukan Informasi Akun Anda
                </h2>
                <form action="cek.php" method="POST" class="space-y-6">
                    <div>
                        <label for="input_pencarian" class="block text-sm font-medium text-gray-700 mb-1.5">Masukkan Nama Lengkap atau Nomor Telepon Terdaftar:</label>
                        <div class="relative rounded-md shadow-sm input-icon-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                            <input type="text" name="input_pencarian" id="input_pencarian" value="<?php echo htmlspecialchars($input_pencarian_display); ?>" required 
                                   class="focus:ring-sky-500 focus:border-sky-500 block w-full py-3 sm:text-sm border-gray-300 rounded-md" 
                                   placeholder="Ketik nama atau nomor telepon...">
                        </div>
                    </div>
                    <div>
                        <button type="submit" name="cek_info" class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-md shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i> Cek Informasi
                        </button>
                    </div>
                </form>
            </div>

            <?php if (!empty($error_message_public)): ?>
                <div class="max-w-xl mx-auto mt-6 card p-4 bg-red-50 border-l-4 border-red-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle text-red-500 fa-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-700"><?php echo $error_message_public; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($info_message_public)): ?>
                 <div class="max-w-xl mx-auto mt-6 card p-4 bg-sky-50 border-l-4 border-sky-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-sky-500 fa-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-sky-700"><?php echo $info_message_public; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($warga_data): ?>
            <div class="max-w-4xl mx-auto mt-8 sm:mt-10 card p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 pb-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">
                            <i class="fas fa-user-check mr-2 text-green-500"></i>Informasi Akun: <?php echo htmlspecialchars($warga_data['nama_lengkap']); ?>
                        </h2>
                        <p class="text-sm text-gray-500">Terdaftar sejak: <?php echo format_tanggal_indonesia($warga_data['tanggal_daftar']); ?></p>
                    </div>
                    <div class="mt-4 sm:mt-0 text-left sm:text-right">
                        <p class="text-lg font-medium text-gray-600">Saldo Anda:</p>
                        <p class="text-4xl font-bold text-green-600"><?php echo format_rupiah($warga_data['saldo']); ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-6 text-gray-700 text-sm">
                    <div><i class="fas fa-phone-alt w-5 mr-2 text-sky-500"></i><strong class="font-medium">No. Telepon Terdaftar:</strong> <?php echo htmlspecialchars($no_telepon_terdaftar); ?></div>
                    <div class="md:col-span-2"><i class="fas fa-map-marker-alt w-5 mr-2 text-sky-500"></i><strong class="font-medium">Alamat:</strong> <?php echo htmlspecialchars($warga_data['alamat'] ? $warga_data['alamat'] : '-'); ?></div>
                    </div>

                <h3 class="text-xl font-semibold text-gray-700 mb-4 mt-8 pt-4 border-t border-gray-200">
                   <i class="fas fa-history mr-2 text-sky-500"></i> Riwayat Transaksi Terbaru
                </h3>
                <?php if (!empty($riwayat_transaksi)): ?>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 table">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal & Waktu</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipe</th>
                                <th class="text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Nilai (Rp)</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Keterangan</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($riwayat_transaksi as $trx): ?>
                            <tr>
                                <td class="whitespace-nowrap text-sm text-gray-600"><?php echo format_tanggal_indonesia($trx['tanggal_transaksi']); ?></td>
                                <td class="whitespace-nowrap text-sm">
                                    <?php if ($trx['tipe_transaksi'] == 'setor'): ?>
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-arrow-down mr-1.5"></i>Setoran
                                        </span>
                                    <?php elseif ($trx['tipe_transaksi'] == 'tarik_saldo'): ?>
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-arrow-up mr-1.5"></i>Penarikan
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"><?php echo htmlspecialchars($trx['tipe_transaksi']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap text-sm text-gray-800 text-right font-semibold"><?php echo format_rupiah($trx['total_nilai']); ?></td>
                                <td class="text-sm text-gray-500 max-w-[150px] sm:max-w-xs truncate" title="<?php echo htmlspecialchars($trx['keterangan_transaksi']); ?>">
                                    <?php echo htmlspecialchars($trx['keterangan_transaksi'] ? $trx['keterangan_transaksi'] : '-'); ?>
                                </td>
                                <td class="whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo htmlspecialchars($trx['nama_petugas'] ? $trx['nama_petugas'] : 'Sistem'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-6">
                    <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500 italic">Belum ada riwayat transaksi untuk ditampilkan.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <footer class="text-center py-6 bg-gray-800 text-sm text-gray-400 print:hidden">
        <p>&copy; <?php echo date('Y'); ?> Bank Sampah Digital. Dikelola dengan <i class="fas fa-heart text-sky-500"></i>.</p>
        <p>Alamat Bank Sampah Anda | Kontak: email@example.com</p>
    </footer>
</body>
</html>
