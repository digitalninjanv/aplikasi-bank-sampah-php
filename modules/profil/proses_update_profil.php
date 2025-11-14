<?php
// modules/profil/proses_update_profil.php
// Pastikan config/database.php sudah di-include oleh index.php (karena file ini akan masuk no_layout_pages)
// atau jika dipanggil langsung, uncomment baris di bawah dan sesuaikan path:
// require_once '../../config/database.php'; // Sesuaikan path jika perlu

// Langsung panggil check_user_level karena ini adalah file proses
check_user_level(['admin', 'petugas', 'warga']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $user_id = $_SESSION['user_id']; // Ambil user_id dari session

    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $username_profil = sanitize_input($_POST['username']); // Username dari form profil
    $alamat = sanitize_input($_POST['alamat']);
    $no_telepon = sanitize_input($_POST['no_telepon']);

    if (empty($nama_lengkap) || empty($username_profil)) {
        $_SESSION['error_message'] = "Nama lengkap dan username tidak boleh kosong.";
        redirect(BASE_URL . 'index.php?page=profil');
    }

    // Cek apakah username baru sudah digunakan oleh pengguna lain
    $query_cek_username = "SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?";
    $stmt_cek = mysqli_prepare($koneksi, $query_cek_username);
    mysqli_stmt_bind_param($stmt_cek, "si", $username_profil, $user_id);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $_SESSION['error_message'] = "Username '{$username_profil}' sudah digunakan oleh pengguna lain.";
        mysqli_stmt_close($stmt_cek);
        redirect(BASE_URL . 'index.php?page=profil');
    }
    mysqli_stmt_close($stmt_cek);

    // Query update profil
    $query_update_profil = "UPDATE pengguna SET nama_lengkap = ?, username = ?, alamat = ?, no_telepon = ? WHERE id_pengguna = ?";
    $stmt_update = mysqli_prepare($koneksi, $query_update_profil);
    mysqli_stmt_bind_param($stmt_update, "ssssi", $nama_lengkap, $username_profil, $alamat, $no_telepon, $user_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION['success_message'] = "Profil berhasil diperbarui.";
        // Update session jika username atau nama berubah
        if (isset($_SESSION['user_username']) && $_SESSION['user_username'] !== $username_profil) {
            $_SESSION['user_username'] = $username_profil;
        }
        if (isset($_SESSION['user_nama']) && $_SESSION['user_nama'] !== $nama_lengkap) {
            $_SESSION['user_nama'] = $nama_lengkap;
        }
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui profil: " . mysqli_stmt_error($stmt_update);
        error_log("Error update profil (User ID: $user_id): " . mysqli_stmt_error($stmt_update));
    }
    mysqli_stmt_close($stmt_update);
    redirect(BASE_URL . 'index.php?page=profil');

} else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    redirect(BASE_URL . 'index.php?page=profil');
}
?>
