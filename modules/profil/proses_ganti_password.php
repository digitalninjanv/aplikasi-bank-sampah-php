<?php
// modules/profil/proses_ganti_password.php
// Pastikan config/database.php sudah di-include oleh index.php
check_user_level(['admin', 'petugas', 'warga']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $user_id = $_SESSION['user_id'];

    $password_lama = $_POST['password_lama']; // Tidak perlu sanitize untuk diverifikasi
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password_baru = $_POST['konfirmasi_password_baru'];

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password_baru)) {
        $_SESSION['error_message'] = "Semua field password harus diisi.";
        redirect(BASE_URL . 'index.php?page=profil');
    }

    if (strlen($password_baru) < 6) { // Contoh validasi panjang password
        $_SESSION['error_message'] = "Password baru minimal harus 6 karakter.";
        redirect(BASE_URL . 'index.php?page=profil');
    }

    if ($password_baru !== $konfirmasi_password_baru) {
        $_SESSION['error_message'] = "Password baru dan konfirmasi password tidak cocok.";
        redirect(BASE_URL . 'index.php?page=profil');
    }

    // Ambil password hash saat ini dari database
    $query_pass = "SELECT password FROM pengguna WHERE id_pengguna = ?";
    $stmt_pass = mysqli_prepare($koneksi, $query_pass);
    mysqli_stmt_bind_param($stmt_pass, "i", $user_id);
    mysqli_stmt_execute($stmt_pass);
    $result_pass = mysqli_stmt_get_result($stmt_pass);
    $current_user_data = mysqli_fetch_assoc($result_pass);
    mysqli_stmt_close($stmt_pass);

    if ($current_user_data && password_verify($password_lama, $current_user_data['password'])) {
        // Password lama cocok, hash password baru
        $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update_pass = "UPDATE pengguna SET password = ? WHERE id_pengguna = ?";
        $stmt_update_pass = mysqli_prepare($koneksi, $query_update_pass);
        mysqli_stmt_bind_param($stmt_update_pass, "si", $hashed_password_baru, $user_id);

        if (mysqli_stmt_execute($stmt_update_pass)) {
            $_SESSION['success_message'] = "Password berhasil diganti. Silakan login kembali untuk keamanan.";
            // Hancurkan sesi dan paksa login ulang
            session_unset();
            session_destroy();
            redirect(BASE_URL . 'index.php?page=auth/login&pesan=password_updated');
        } else {
            $_SESSION['error_message'] = "Gagal mengganti password: " . mysqli_stmt_error($stmt_update_pass);
            error_log("Error ganti password (User ID: $user_id): " . mysqli_stmt_error($stmt_update_pass));
            redirect(BASE_URL . 'index.php?page=profil');
        }
        mysqli_stmt_close($stmt_update_pass);
    } else {
        $_SESSION['error_message'] = "Password lama yang Anda masukkan salah.";
        redirect(BASE_URL . 'index.php?page=profil');
    }
} else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    redirect(BASE_URL . 'index.php?page=profil');
}
?>
