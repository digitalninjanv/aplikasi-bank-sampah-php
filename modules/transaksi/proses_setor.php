<?php
// modules/transaksi/proses_setor.php
check_user_level(['admin', 'petugas']);

// Kondisi diubah: Cek metode POST dan keberadaan field utama seperti id_warga dan items,
// karena submit programatik via JS tidak mengirimkan nama tombol submit.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_warga']) && isset($_POST['items'])) {

    $id_warga = sanitize_input($_POST['id_warga']);
    $tanggal_transaksi_input = sanitize_input($_POST['tanggal_transaksi']);
    // Konversi format tanggal dari datetime-local (Y-m-d\TH:i) ke format MySQL (Y-m-d H:i:s)
    $tanggal_transaksi = date('Y-m-d H:i:s', strtotime($tanggal_transaksi_input));
    
    $keterangan = isset($_POST['keterangan']) ? sanitize_input($_POST['keterangan']) : null;
    $items = $_POST['items']; // items sudah dicek isset di kondisi if utama
    $id_petugas_pencatat = $_SESSION['user_id']; // Petugas yang login

    // Validasi dasar (keberadaan items sudah dicek di if utama)
    if (empty($id_warga) || empty($tanggal_transaksi) || !is_array($items) || count($items) === 0) {
        $_SESSION['error_message'] = "Data warga, tanggal, dan minimal satu item sampah harus diisi.";
        redirect(BASE_URL . 'index.php?page=transaksi/setor');
    }

    $total_nilai_keseluruhan = 0;
    $detail_items_valid = true;

    // Validasi setiap item dan hitung total nilai
    foreach ($items as $item) {
        if (empty($item['id_jenis_sampah']) || !isset($item['berat_kg']) || floatval($item['berat_kg']) <= 0) {
            $detail_items_valid = false;
            break;
        }
        // Ambil harga_per_kg dari database untuk keamanan, jangan percaya harga dari form
        $query_harga_sampah = "SELECT harga_per_kg FROM jenis_sampah WHERE id_jenis_sampah = ?";
        $stmt_harga = mysqli_prepare($koneksi, $query_harga_sampah);
        mysqli_stmt_bind_param($stmt_harga, "i", $item['id_jenis_sampah']);
        mysqli_stmt_execute($stmt_harga);
        $result_harga = mysqli_stmt_get_result($stmt_harga);
        $data_harga = mysqli_fetch_assoc($result_harga);
        mysqli_stmt_close($stmt_harga);

        if (!$data_harga) {
            $_SESSION['error_message'] = "Jenis sampah tidak valid ditemukan dalam item.";
            redirect(BASE_URL . 'index.php?page=transaksi/setor');
        }
        
        $harga_saat_setor = floatval($data_harga['harga_per_kg']);
        $berat_kg = floatval($item['berat_kg']);
        $subtotal_nilai = $berat_kg * $harga_saat_setor;
        $total_nilai_keseluruhan += $subtotal_nilai;
    }

    if (!$detail_items_valid) {
        $_SESSION['error_message'] = "Detail item sampah tidak valid. Pastikan jenis sampah dipilih dan berat lebih dari 0.";
        redirect(BASE_URL . 'index.php?page=transaksi/setor');
    }
    
    if ($total_nilai_keseluruhan <= 0 && count($items) > 0) { // Hanya error jika ada item tapi totalnya 0 atau kurang
        $_SESSION['error_message'] = "Total nilai setoran harus lebih dari 0.";
        redirect(BASE_URL . 'index.php?page=transaksi/setor');
    }

    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel transaksi
        $query_insert_transaksi = "INSERT INTO transaksi (id_warga, id_petugas_pencatat, tanggal_transaksi, tipe_transaksi, total_nilai, keterangan) 
                                   VALUES (?, ?, ?, 'setor', ?, ?)";
        $stmt_transaksi = mysqli_prepare($koneksi, $query_insert_transaksi);
        mysqli_stmt_bind_param($stmt_transaksi, "iisds", $id_warga, $id_petugas_pencatat, $tanggal_transaksi, $total_nilai_keseluruhan, $keterangan);
        
        if (!mysqli_stmt_execute($stmt_transaksi)) {
            throw new Exception("Gagal menyimpan data transaksi utama: " . mysqli_stmt_error($stmt_transaksi));
        }
        $id_transaksi_setor = mysqli_insert_id($koneksi); 
        mysqli_stmt_close($stmt_transaksi);

        // 2. Insert ke tabel detail_setoran untuk setiap item
        $query_insert_detail = "INSERT INTO detail_setoran (id_transaksi_setor, id_jenis_sampah, berat_kg, harga_saat_setor, subtotal_nilai) 
                                VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = mysqli_prepare($koneksi, $query_insert_detail);

        foreach ($items as $item_data) {
            $id_jenis_sampah_item = $item_data['id_jenis_sampah'];
            $berat_kg_item = floatval($item_data['berat_kg']);
            
            $query_harga_item = "SELECT harga_per_kg FROM jenis_sampah WHERE id_jenis_sampah = ?";
            $stmt_harga_i = mysqli_prepare($koneksi, $query_harga_item);
            mysqli_stmt_bind_param($stmt_harga_i, "i", $id_jenis_sampah_item);
            mysqli_stmt_execute($stmt_harga_i);
            $result_harga_i = mysqli_stmt_get_result($stmt_harga_i);
            $data_harga_i = mysqli_fetch_assoc($result_harga_i);
            mysqli_stmt_close($stmt_harga_i);

            $harga_saat_setor_item = floatval($data_harga_i['harga_per_kg']);
            $subtotal_nilai_item = $berat_kg_item * $harga_saat_setor_item;

            mysqli_stmt_bind_param($stmt_detail, "iidds", $id_transaksi_setor, $id_jenis_sampah_item, $berat_kg_item, $harga_saat_setor_item, $subtotal_nilai_item);
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Gagal menyimpan detail setoran: " . mysqli_stmt_error($stmt_detail));
            }
        }
        mysqli_stmt_close($stmt_detail);

        // 3. Update saldo warga
        $query_update_saldo = "UPDATE pengguna SET saldo = saldo + ? WHERE id_pengguna = ? AND level = 'warga'";
        $stmt_saldo = mysqli_prepare($koneksi, $query_update_saldo);
        mysqli_stmt_bind_param($stmt_saldo, "di", $total_nilai_keseluruhan, $id_warga);
        if (!mysqli_stmt_execute($stmt_saldo)) {
            throw new Exception("Gagal memperbarui saldo warga: " . mysqli_stmt_error($stmt_saldo));
        }
        mysqli_stmt_close($stmt_saldo);

        mysqli_commit($koneksi);
        $_SESSION['success_message'] = "Setoran sampah berhasil dicatat dengan total nilai " . format_rupiah($total_nilai_keseluruhan) . ".";
        $_SESSION['last_transaksi_id'] = $id_transaksi_setor;
        $_SESSION['last_transaksi_tipe'] = 'setor';
        redirect(BASE_URL . 'index.php?page=transaksi/setor');

    } catch (Exception $e) {
        mysqli_rollback($koneksi); 
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        error_log("Error proses setor: " . $e->getMessage() . " - Data POST: " . print_r($_POST, true));
        redirect(BASE_URL . 'index.php?page=transaksi/setor');
    }

} else {
    $_SESSION['error_message'] = "Aksi tidak valid atau metode request salah. Pastikan form diisi dengan benar.";
    error_log("Proses setor gagal masuk kondisi utama. METHOD: ".$_SERVER['REQUEST_METHOD'].". POST_DATA: ".print_r($_POST, true));
    redirect(BASE_URL . 'index.php?page=transaksi/setor');
}
?>
