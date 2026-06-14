<?php
// modules/profil/proses_update_profil.php
check_user_level(['admin', 'petugas', 'warga']);
require_csrf();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $user_id = $_SESSION['user_id'];

    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $username_profil = sanitize_input($_POST['username']);
    $alamat = sanitize_input($_POST['alamat']);
    $no_telepon = sanitize_input($_POST['no_telepon']);

    if (empty($nama_lengkap) || empty($username_profil)) {
        $_SESSION['error_message'] = "Nama lengkap dan username tidak boleh kosong.";
        redirect(BASE_URL . 'index.php?page=profil');
    }

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

    $foto_name = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $_SESSION['error_message'] = "Format foto harus JPG, PNG, atau GIF.";
            redirect(BASE_URL . 'index.php?page=profil');
        }
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_message'] = "Ukuran foto maksimal 2MB.";
            redirect(BASE_URL . 'index.php?page=profil');
        }

        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . '/uploads/foto_profil/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = $user_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_name);

        $query_old = "SELECT foto FROM pengguna WHERE id_pengguna = ?";
        $stmt_old = mysqli_prepare($koneksi, $query_old);
        mysqli_stmt_bind_param($stmt_old, "i", $user_id);
        mysqli_stmt_execute($stmt_old);
        $res_old = mysqli_stmt_get_result($stmt_old);
        $old = mysqli_fetch_assoc($res_old);
        mysqli_stmt_close($stmt_old);
        if (!empty($old['foto']) && file_exists($upload_dir . $old['foto'])) {
            unlink($upload_dir . $old['foto']);
        }
    }

    if ($foto_name) {
        $query_update = "UPDATE pengguna SET nama_lengkap = ?, username = ?, alamat = ?, no_telepon = ?, foto = ? WHERE id_pengguna = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "sssssi", $nama_lengkap, $username_profil, $alamat, $no_telepon, $foto_name, $user_id);
    } else {
        $query_update = "UPDATE pengguna SET nama_lengkap = ?, username = ?, alamat = ?, no_telepon = ? WHERE id_pengguna = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssssi", $nama_lengkap, $username_profil, $alamat, $no_telepon, $user_id);
    }

    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION['success_message'] = "Profil berhasil diperbarui.";
        log_aktivitas('Update Profil', 'pengguna', $user_id, "Profil diperbarui");
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
