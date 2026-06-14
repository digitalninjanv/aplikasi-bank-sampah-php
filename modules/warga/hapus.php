<?php
// modules/warga/hapus.php
check_user_level(['admin', 'petugas']);
require_csrf();

if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error_message'] = "ID Warga tidak valid.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}

$id_warga = (int)$_POST['id'];

$query_update = "UPDATE pengguna SET status = 'nonaktif' WHERE id_pengguna = ? AND level = 'warga'";
$stmt = mysqli_prepare($koneksi, $query_update);
mysqli_stmt_bind_param($stmt, "i", $id_warga);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "Data warga berhasil dinonaktifkan.";
        log_aktivitas('Nonaktifkan Warga', 'pengguna', $id_warga, "Warga dinonaktifkan");
    } else {
        $_SESSION['error_message'] = "Data warga tidak ditemukan atau sudah dinonaktifkan.";
    }
} else {
    $_SESSION['error_message'] = "Gagal menonaktifkan data warga: " . mysqli_stmt_error($stmt);
    error_log("Error nonaktifkan warga (ID: $id_warga): " . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

redirect(BASE_URL . 'index.php?page=warga/data');
?>
