<?php
// modules/jenis_sampah/hapus.php
check_user_level(['admin', 'petugas']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Jenis Sampah tidak valid untuk dihapus.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}

$id_jenis_sampah = sanitize_input($_GET['id']);

// PENTING: Sebelum menghapus, pertimbangkan dampaknya.
// Jika jenis sampah ini sudah pernah digunakan dalam transaksi (tabel detail_setoran),
// menghapusnya bisa menyebabkan error foreign key jika tidak ada ON DELETE SET NULL atau ON DELETE CASCADE
// pada constraint di tabel detail_setoran yang merujuk ke jenis_sampah.
// Dalam SQL yang kita buat, ada ON DELETE CASCADE, jadi detail_setoran terkait akan ikut terhapus.
// Ini mungkin bukan perilaku yang diinginkan jika Anda ingin menyimpan riwayat transaksi lengkap
// meskipun jenis sampahnya sudah dihapus.
// Alternatif: Tambahkan kolom 'status' (aktif/tidak aktif) di tabel jenis_sampah daripada menghapus permanen.

// Untuk contoh ini, kita akan tetap menghapus.
$query_delete = "DELETE FROM jenis_sampah WHERE id_jenis_sampah = ?";
$stmt = mysqli_prepare($koneksi, $query_delete);
mysqli_stmt_bind_param($stmt, "i", $id_jenis_sampah);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "Jenis sampah berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Jenis sampah tidak ditemukan atau sudah terhapus.";
    }
} else {
    // Cek error foreign key constraint
    if(mysqli_errno($koneksi) == 1451) { // Error code untuk foreign key constraint violation
         $_SESSION['error_message'] = "Gagal menghapus jenis sampah. Jenis sampah ini masih digunakan dalam data transaksi. Nonaktifkan jika tidak ingin digunakan lagi.";
         error_log("Error delete jenis_sampah (ID: $id_jenis_sampah) - Foreign Key Constraint: " . mysqli_stmt_error($stmt));
    } else {
        $_SESSION['error_message'] = "Gagal menghapus jenis sampah: " . mysqli_stmt_error($stmt);
        error_log("Error delete jenis_sampah (ID: $id_jenis_sampah): " . mysqli_stmt_error($stmt));
    }
}
mysqli_stmt_close($stmt);

redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
?>
