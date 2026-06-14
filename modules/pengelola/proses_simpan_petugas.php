<?php
check_user_level(['admin']);
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $_SESSION['error_message'] = "Metode tidak valid."; redirect(BASE_URL . 'index.php?page=pengelola/petugas'); }

if (isset($_POST['simpan_petugas'])) {
    $nama = sanitize_input($_POST['nama_lengkap']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $no_telepon = sanitize_input($_POST['no_telepon'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');

    if (empty($nama) || empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Nama, username, dan password wajib diisi.";
        redirect(BASE_URL . 'index.php?page=pengelola/tambah_petugas');
    }

    $cek = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);
    if (mysqli_stmt_num_rows($cek) > 0) {
        $_SESSION['error_message'] = "Username sudah digunakan.";
        mysqli_stmt_close($cek);
        redirect(BASE_URL . 'index.php?page=pengelola/tambah_petugas');
    }
    mysqli_stmt_close($cek);

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO pengguna (nama_lengkap, username, password, level, no_telepon, alamat, status) VALUES (?, ?, ?, 'petugas', ?, ?, 'aktif')");
    mysqli_stmt_bind_param($stmt, "sssss", $nama, $username, $hashed, $no_telepon, $alamat);
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($koneksi);
        log_aktivitas('tambah_petugas', 'pengguna', $new_id, "Petugas: $nama ($username)");
        $_SESSION['success_message'] = "Petugas $nama berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambah petugas.";
    }
    mysqli_stmt_close($stmt);
    redirect(BASE_URL . 'index.php?page=pengelola/petugas');

} elseif (isset($_POST['update_petugas'])) {
    $id = sanitize_input($_POST['id_pengguna']);
    $nama = sanitize_input($_POST['nama_lengkap']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'] ?? '';
    $no_telepon = sanitize_input($_POST['no_telepon'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $status = $_POST['status'] ?? 'aktif';

    $cek = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?");
    mysqli_stmt_bind_param($cek, "si", $username, $id);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);
    if (mysqli_stmt_num_rows($cek) > 0) {
        $_SESSION['error_message'] = "Username sudah digunakan petugas lain.";
        mysqli_stmt_close($cek);
        redirect(BASE_URL . 'index.php?page=pengelola/edit_petugas&id=' . $id);
    }
    mysqli_stmt_close($cek);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET nama_lengkap=?, username=?, password=?, no_telepon=?, alamat=?, status=? WHERE id_pengguna=? AND level='petugas'");
        mysqli_stmt_bind_param($stmt, "ssssssi", $nama, $username, $hashed, $no_telepon, $alamat, $status, $id);
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET nama_lengkap=?, username=?, no_telepon=?, alamat=?, status=? WHERE id_pengguna=? AND level='petugas'");
        mysqli_stmt_bind_param($stmt, "sssssi", $nama, $username, $no_telepon, $alamat, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        log_aktivitas('update_petugas', 'pengguna', $id, "Petugas: $nama");
        $_SESSION['success_message'] = "Data petugas berhasil diperbarui.";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data.";
    }
    mysqli_stmt_close($stmt);
    redirect(BASE_URL . 'index.php?page=pengelola/petugas');

} else {
    redirect(BASE_URL . 'index.php?page=pengelola/petugas');
}
