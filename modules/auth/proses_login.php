<?php
// modules/auth/proses_login.php
// Pastikan config/database.php sudah di-require oleh index.php utama yang memanggil file ini.
// Tidak perlu require_once '../../config/database.php'; jika routing melalui index.php sudah benar.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan $koneksi dan fungsi-fungsi dari config/database.php tersedia.
    // Jika tidak, berarti ada masalah dengan bagaimana file ini dipanggil.
    if (!isset($koneksi) || !function_exists('sanitize_input') || !function_exists('redirect')) {
        // Ini seharusnya tidak terjadi jika routing melalui index.php benar
        // dan config/database.php termuat dengan baik.
        error_log("Peringatan Kritis: proses_login.php dipanggil tanpa konteks aplikasi yang benar (koneksi atau fungsi dasar tidak ada).");
        die("Terjadi kesalahan sistem. Silakan coba lagi nanti atau hubungi administrator. (Error Code: PLP_CTXT)");
    }

    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // Password tidak disanitasi dengan htmlspecialchars karena akan diverifikasi

    if (empty($username) || empty($password)) {
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=kolom_kosong');
        // exit() sudah ada di dalam fungsi redirect()
    }

    $query = "SELECT id_pengguna, nama_lengkap, username, password, level FROM pengguna WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Login berhasil, pastikan sesi dimulai sebelum menulis ke dalamnya
                if (session_status() == PHP_SESSION_NONE) {
                    // Ini sebagai fallback, idealnya sesi sudah dimulai di config/database.php atau index.php
                    session_start(); 
                }

                $_SESSION['user_id'] = $user['id_pengguna'];
                $_SESSION['user_nama'] = $user['nama_lengkap'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_level'] = $user['level'];
                $_SESSION['login_time'] = time(); // Tambahkan waktu login untuk manajemen sesi (opsional)

                // Redirect ke dashboard sesuai level
                redirect(BASE_URL . 'index.php?page=dashboard&pesan=login_sukses');
            } else {
                // Password salah
                redirect(BASE_URL . 'index.php?page=auth/login&pesan=gagal');
            }
        } else {
            // Username tidak ditemukan
            redirect(BASE_URL . 'index.php?page=auth/login&pesan=gagal');
        }
        mysqli_stmt_close($stmt);
    } else {
        // Error pada statement SQL
        error_log("MySQLi prepare error on login: " . mysqli_error($koneksi));
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=db_error');
    }
    // Koneksi tidak perlu ditutup di sini jika akan digunakan oleh halaman lain setelah redirect.
    // mysqli_close($koneksi); 
} else {
    // Jika bukan metode POST, redirect ke halaman login
    $_SESSION['error_message'] = "Metode akses tidak valid."; // Pesan opsional
    redirect(BASE_URL . 'index.php?page=auth/login&pesan=metode_salah');
}
?>
