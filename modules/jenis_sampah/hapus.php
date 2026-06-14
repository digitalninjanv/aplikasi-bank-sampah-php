<?php
// modules/jenis_sampah/hapus.php
check_user_level(['admin', 'petugas']);
require_csrf();

if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error_message'] = "ID Jenis Sampah tidak valid.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}

$id_jenis_sampah = (int)$_POST['id'];

$query_update = "UPDATE jenis_sampah SET status = 'nonaktif' WHERE id_jenis_sampah = ? AND status = 'aktif'";
$stmt = mysqli_prepare($koneksi, $query_update);
mysqli_stmt_bind_param($stmt, "i", $id_jenis_sampah);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "Jenis sampah berhasil dinonaktifkan.";
        log_aktivitas('Nonaktifkan Jenis Sampah', 'jenis_sampah', $id_jenis_sampah, "Jenis dinonaktifkan");
    } else {
        $_SESSION['error_message'] = "Jenis sampah tidak ditemukan atau sudah dinonaktifkan.";
    }
} else {
    $_SESSION['error_message'] = "Gagal menonaktifkan jenis sampah: " . mysqli_stmt_error($stmt);
    error_log("Error nonaktifkan jenis_sampah (ID: $id_jenis_sampah): " . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
?>
