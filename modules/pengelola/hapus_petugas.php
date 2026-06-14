<?php
check_user_level(['admin']);
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    redirect(BASE_URL . 'index.php?page=pengelola/petugas');
}

$id = (int)$_POST['id'];
if ($id == $_SESSION['user_id']) { $_SESSION['error_message'] = "Tidak bisa menonaktifkan diri sendiri."; redirect(BASE_URL . 'index.php?page=pengelola/petugas'); }

$stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET status='nonaktif' WHERE id_pengguna=? AND level='petugas'");
mysqli_stmt_bind_param($stmt, "i", $id);
if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($koneksi) > 0) {
    log_aktivitas('nonaktifkan_petugas', 'pengguna', $id, "Petugas dinonaktifkan");
    $_SESSION['success_message'] = "Petugas berhasil dinonaktifkan.";
} else {
    $_SESSION['error_message'] = "Gagal menonaktifkan petugas.";
}
mysqli_stmt_close($stmt);
redirect(BASE_URL . 'index.php?page=pengelola/petugas');
?>
