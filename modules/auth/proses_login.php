<?php
// modules/auth/proses_login.php
// Pastikan config/database.php sudah di-require oleh index.php utama yang memanggil file ini.
// Tidak perlu require_once '../../config/database.php'; jika routing melalui index.php sudah benar.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_csrf();
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

    $query = "SELECT id_pengguna, nama_lengkap, username, password, level, status, login_attempts, locked_until FROM pengguna WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Cek status akun
            if (isset($user['status']) && $user['status'] === 'nonaktif') {
                mysqli_stmt_close($stmt);
                $_SESSION['error_message'] = "Akun Anda telah dinonaktifkan. Hubungi administrator.";
                redirect(BASE_URL . 'index.php?page=auth/login');
            }

            // Rate limit: cek locked_until
            if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
                mysqli_stmt_close($stmt);
                $_SESSION['error_message'] = "Akun terkunci karena terlalu banyak percobaan. Coba lagi nanti.";
                redirect(BASE_URL . 'index.php?page=auth/login');
            }

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Login berhasil — reset rate limit + update last_login
                mysqli_query($koneksi, "UPDATE pengguna SET login_attempts=0, locked_until=NULL, last_login=NOW() WHERE id_pengguna=" . (int)$user['id_pengguna']);

                if (session_status() == PHP_SESSION_NONE) {
                    session_start(); 
                }

                session_regenerate_id(true); // Cegah session fixation

                $_SESSION['user_id'] = $user['id_pengguna'];
                $_SESSION['user_nama'] = $user['nama_lengkap'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_level'] = $user['level'];
                $_SESSION['login_time'] = time();

                log_aktivitas('login', 'pengguna', $user['id_pengguna'], "Login berhasil");

                // Redirect ke dashboard sesuai level
                redirect(BASE_URL . 'index.php?page=dashboard&pesan=login_sukses');
            } else {
                // Rate limit: increment attempts
                $attempts = (int)$user['login_attempts'] + 1;
                if ($attempts >= 5) {
                    mysqli_query($koneksi, "UPDATE pengguna SET login_attempts=$attempts, locked_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id_pengguna=" . (int)$user['id_pengguna']);
                    log_aktivitas('login_locked', 'pengguna', $user['id_pengguna'], "Akun dikunci karena 5x gagal login");
                } else {
                    mysqli_query($koneksi, "UPDATE pengguna SET login_attempts=$attempts WHERE id_pengguna=" . (int)$user['id_pengguna']);
                }
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
