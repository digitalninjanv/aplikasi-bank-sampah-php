<?php
// modules/transaksi/proses_tarik.php
check_user_level(['admin', 'petugas']);

// Kondisi diubah: Cek metode POST dan keberadaan field utama seperti id_warga dan jumlah_penarikan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_warga']) && isset($_POST['jumlah_penarikan'])) {

    $id_warga = sanitize_input($_POST['id_warga']);
    $jumlah_penarikan_input = $_POST['jumlah_penarikan'];
    $jumlah_penarikan = filter_var($jumlah_penarikan_input, FILTER_VALIDATE_FLOAT);
    
    $tanggal_transaksi_input = sanitize_input($_POST['tanggal_transaksi']);
    $tanggal_transaksi = date('Y-m-d H:i:s', strtotime($tanggal_transaksi_input));
    $keterangan = isset($_POST['keterangan']) ? sanitize_input($_POST['keterangan']) : 'Penarikan Saldo';
    $id_petugas_pencatat = $_SESSION['user_id'];

    // Validasi dasar
    if (empty($id_warga) || $jumlah_penarikan === false || $jumlah_penarikan <= 0 || empty($tanggal_transaksi)) {
        $_SESSION['error_message'] = "Data warga, jumlah penarikan (harus angka positif > 0), dan tanggal harus diisi dengan benar.";
        redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');
    }

    // Ambil saldo warga saat ini untuk validasi
    $query_saldo = "SELECT saldo FROM pengguna WHERE id_pengguna = ? AND level = 'warga'";
    $stmt_cek_saldo = mysqli_prepare($koneksi, $query_saldo);
    mysqli_stmt_bind_param($stmt_cek_saldo, "i", $id_warga);
    mysqli_stmt_execute($stmt_cek_saldo);
    $result_saldo = mysqli_stmt_get_result($stmt_cek_saldo);
    $data_warga = mysqli_fetch_assoc($result_saldo);
    mysqli_stmt_close($stmt_cek_saldo);

    if (!$data_warga) {
        $_SESSION['error_message'] = "Warga tidak ditemukan.";
        redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');
    }

    $saldo_saat_ini = floatval($data_warga['saldo']);
    if ($jumlah_penarikan > $saldo_saat_ini) {
        $_SESSION['error_message'] = "Jumlah penarikan (" . format_rupiah($jumlah_penarikan) . ") melebihi saldo warga saat ini (" . format_rupiah($saldo_saat_ini) . ").";
        redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');
    }

    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel transaksi
        $query_insert_transaksi = "INSERT INTO transaksi (id_warga, id_petugas_pencatat, tanggal_transaksi, tipe_transaksi, total_nilai, keterangan) 
                                   VALUES (?, ?, ?, 'tarik_saldo', ?, ?)";
        $stmt_transaksi = mysqli_prepare($koneksi, $query_insert_transaksi);
        mysqli_stmt_bind_param($stmt_transaksi, "iisds", $id_warga, $id_petugas_pencatat, $tanggal_transaksi, $jumlah_penarikan, $keterangan);
        
        if (!mysqli_stmt_execute($stmt_transaksi)) {
            throw new Exception("Gagal menyimpan data transaksi penarikan: " . mysqli_stmt_error($stmt_transaksi));
        }
        mysqli_stmt_close($stmt_transaksi);

        // 2. Update saldo warga (kurangi saldo)
        $query_update_saldo = "UPDATE pengguna SET saldo = saldo - ? WHERE id_pengguna = ? AND level = 'warga'";
        $stmt_saldo = mysqli_prepare($koneksi, $query_update_saldo);
        mysqli_stmt_bind_param($stmt_saldo, "di", $jumlah_penarikan, $id_warga);
        if (!mysqli_stmt_execute($stmt_saldo)) {
            throw new Exception("Gagal memperbarui saldo warga setelah penarikan: " . mysqli_stmt_error($stmt_saldo));
        }
        mysqli_stmt_close($stmt_saldo);

        mysqli_commit($koneksi);
        $_SESSION['success_message'] = "Penarikan saldo sebesar " . format_rupiah($jumlah_penarikan) . " berhasil dicatat.";
        redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');

    } catch (Exception $e) {
        mysqli_rollback($koneksi); 
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        error_log("Error proses tarik saldo: " . $e->getMessage() . " - Data POST: " . print_r($_POST, true));
        redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');
    }

} else {
    $_SESSION['error_message'] = "Aksi tidak valid atau metode request salah. Pastikan form diisi dengan benar.";
    error_log("Proses tarik saldo gagal masuk kondisi utama. METHOD: ".$_SERVER['REQUEST_METHOD'].". POST_DATA: ".print_r($_POST, true));
    redirect(BASE_URL . 'index.php?page=transaksi/tarik_saldo');
}
?>
