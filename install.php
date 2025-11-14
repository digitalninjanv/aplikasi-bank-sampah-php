<?php
// error_reporting(0); // Aktifkan ini untuk menyembunyikan notice/warning jika perlu

// --- PENGATURAN KONEKSI ---
// Harap sesuaikan ini dengan pengaturan server MySQL Anda (biasanya XAMPP).
$db_host = 'localhost';
$db_user = 'root'; // User default XAMPP
$db_pass = '';     // Password default XAMPP (kosong)
$db_name = 'db_banksampah'; // Nama database yang akan dibuat
$sql_file_path = 'banksampah.sql'; // Path ke file .sql Anda
// --------------------------


$output_messages = [];
$success = false;

function format_message($message, $is_error = false) {
    $color = $is_error ? 'text-red-600' : 'text-green-600';
    $icon = $is_error ? '<i class="fas fa-times-circle mr-2"></i>' : '<i class="fas fa-check-circle mr-2"></i>';
    return "<li class=\"{$color} flex items-center\">{$icon}" . htmlspecialchars($message) . "</li>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_install'])) {
    ob_start();
    try {
        $output_messages[] = format_message("Memulai proses instalasi...");

        if (!function_exists('mysqli_connect')) {
            throw new Exception("Ekstensi MySQLi belum diaktifkan di PHP.");
        }

        if (!file_exists($sql_file_path)) {
            throw new Exception("File '{$sql_file_path}' tidak ditemukan. Pastikan file tersebut ada di folder yang sama dengan install.php.");
        }

        // 1. Koneksi ke server MySQL (tanpa memilih database)
        $koneksi_server = new mysqli($db_host, $db_user, $db_pass);
        if ($koneksi_server->connect_error) {
            throw new Exception("Gagal terhubung ke server MySQL: " . $koneksi_server->connect_error);
        }
        $output_messages[] = format_message("Berhasil terhubung ke server MySQL.");

        // 2. Buat database
        $create_db_query = "CREATE DATABASE IF NOT EXISTS `" . $koneksi_server->real_escape_string($db_name) . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
        if (!$koneksi_server->query($create_db_query)) {
            throw new Exception("Gagal membuat database '{$db_name}': " . $koneksi_server->error);
        }
        $output_messages[] = format_message("Database '{$db_name}' berhasil dibuat (atau sudah ada).");
        $koneksi_server->close();

        // 3. Koneksi ke database yang baru dibuat
        $koneksi_db = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($koneksi_db->connect_error) {
            throw new Exception("Gagal terhubung ke database '{$db_name}': " . $koneksi_db->connect_error);
        }
        $output_messages[] = format_message("Berhasil terhubung ke database '{$db_name}'.");

        // 4. Baca dan eksekusi file .sql
        $sql_content = file_get_contents($sql_file_path);
        if ($sql_content === false) {
            throw new Exception("Gagal membaca file '{$sql_file_path}'.");
        }

        // Eksekusi multi-query
        if ($koneksi_db->multi_query($sql_content)) {
            $output_messages[] = format_message("Mengeksekusi file SQL...");
            do {
                // Simpan hasil set pertama (jika ada)
                if ($result = $koneksi_db->store_result()) {
                    $result->free();
                }
            } while ($koneksi_db->next_result()); // Pindah ke hasil berikutnya
            
            $output_messages[] = format_message("Semua tabel dan data berhasil diimpor.");
        } else {
            throw new Exception("Gagak mengeksekusi file SQL: " . $koneksi_db->error);
        }

        $koneksi_db->close();
        $output_messages[] = format_message("Instalasi Selesai!", false);
        $success = true;

    } catch (Exception $e) {
        $output_messages[] = format_message("TERJADI ERROR: " . $e->getMessage(), true);
        $success = false;
        // Bersihkan koneksi jika masih ada
        if (isset($koneksi_server) && $koneksi_server->ping()) $koneksi_server->close();
        if (isset($koneksi_db) && $koneksi_db->ping()) $koneksi_db->close();
    } finally {
        ob_end_clean();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Bank Sampah Digital</title>
    <!-- Memuat Tailwind CSS dan Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-2xl bg-white shadow-2xl rounded-xl overflow-hidden">
        
        <div class="px-8 py-6 bg-gradient-to-r from-sky-600 to-teal-500 text-white">
            <h1 class="text-3xl font-bold text-center flex items-center justify-center">
                <i class="fas fa-database mr-3 text-4xl"></i>
                Instalasi Bank Sampah Digital
            </h1>
            <p class="text-center text-sky-100 mt-2">Skrip ini akan mengonfigurasi database Anda secara otomatis.</p>
        </div>

        <div class="p-8">
            <?php if (empty($_POST)): ?>
                <div class="mb-6 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-lg">
                    <p class="font-semibold"><i class="fas fa-exclamation-triangle mr-2"></i> Peringatan!</p>
                    <p class="text-sm">Tindakan ini akan membuat database `<?php echo htmlspecialchars($db_name); ?>` dan semua tabelnya. Jika database/tabel sudah ada, skrip ini akan mencoba menimpanya (tergantung isi file `.sql`).</p>
                </div>
                
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h2 class="font-semibold text-lg text-gray-700 mb-2">Konfigurasi Terdeteksi:</h2>
                    <ul class="list-none space-y-1 text-sm">
                        <li><strong>Host:</strong> <?php echo htmlspecialchars($db_host); ?></li>
                        <li><strong>User:</strong> <?php echo htmlspecialchars($db_user); ?></li>
                        <li><strong>Password:</strong> <?php echo htmlspecialchars(empty($db_pass) ? '[KOSONG]' : '******'); ?></li>
                        <li><strong>Database:</strong> <?php echo htmlspecialchars($db_name); ?></li>
                        <li><strong>File SQL:</strong> <?php echo htmlspecialchars($sql_file_path); ?></li>
                    </ul>
                    <p class="text-xs text-gray-500 mt-3">Jika pengaturan ini salah, harap ubah di bagian atas file `install.php` sebelum melanjutkan.</p>
                </div>

                <form method="POST" action="install.php">
                    <button type="submit" name="start_install" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-sky-300">
                        <i class="fas fa-bolt mr-2"></i>
                        Mulai Instalasi Sekarang
                    </button>
                </form>

            <?php else: ?>
                <!-- Tampilan Hasil Instalasi -->
                <div class="p-4 bg-gray-900 text-gray-200 rounded-lg font-mono text-sm shadow-inner max-h-60 overflow-y-auto">
                    <ul class="list-none space-y-1">
                        <?php foreach ($output_messages as $message): ?>
                            <?php echo $message; // Pesan sudah di-format dengan HTML dan di-escape ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if ($success): ?>
                    <div class="mt-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg">
                        <p class="font-bold text-lg"><i class="fas fa-party-horn mr-2"></i> Instalasi Berhasil!</p>
                        <p>Aplikasi Anda sekarang siap digunakan.</p>
                    </div>
                    
                    <a href="index.php" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition duration-300 mt-6">
                        Buka Aplikasi <i class="fas fa-arrow-right ml-2"></i>
                    </a>

                    <div class="mt-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg">
                        <p class="font-bold text-lg"><i class="fas fa-shield-alt mr-2"></i> TINDAKAN KEAMANAN PENTING!</p>
                        <p>Untuk alasan keamanan, segera **HAPUS** file `install.php` ini dari server Anda.</p>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg">
                        <p class="font-bold text-lg"><i class="fas fa-bug mr-2"></i> Instalasi Gagal</p>
                        <p>Silakan periksa pesan error di atas. Perbaiki masalah (misalnya, koneksi database atau path file) dan coba lagi.</p>
                    </div>
                    <a href="install.php" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition duration-300 mt-6">
                        Coba Lagi <i class="fas fa-redo ml-2"></i>
                    </a>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>