<?php
// modules/warga/hapus.php
check_user_level(['admin', 'petugas']); // Hanya admin dan petugas

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Warga tidak valid untuk dihapus.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}

$id_warga = sanitize_input($_GET['id']);

// Untuk keamanan tambahan, bisa dicek dulu apakah warga ini memiliki transaksi yang belum diselesaikan,
// atau apakah ada saldo yang belum ditarik. Namun, saat ini kita akan langsung hapus.
// Foreign key constraint ON DELETE CASCADE pada tabel 'transaksi' akan menghapus transaksi terkait.

$query_delete = "DELETE FROM pengguna WHERE id_pengguna = ? AND level = 'warga'";
$stmt = mysqli_prepare($koneksi, $query_delete);
mysqli_stmt_bind_param($stmt, "i", $id_warga);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "Data warga berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Data warga tidak ditemukan atau sudah terhapus.";
    }
} else {
    $_SESSION['error_message'] = "Gagal menghapus data warga: " . mysqli_stmt_error($stmt);
    error_log("Error delete warga (ID: $id_warga): " . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

redirect(BASE_URL . 'index.php?page=warga/data');
?>
