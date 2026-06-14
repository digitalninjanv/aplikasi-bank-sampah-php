<?php
// modules/jenis_sampah/proses_simpan.php
check_user_level(['admin', 'petugas']);
require_csrf();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_sampah = sanitize_input($_POST['nama_sampah']);
    $harga_per_kg = filter_var($_POST['harga_per_kg'], FILTER_VALIDATE_FLOAT); // Validasi sebagai float
    $deskripsi = sanitize_input($_POST['deskripsi']);
    $satuan = sanitize_input($_POST['satuan']);

    // Proses Tambah Jenis Sampah Baru
    if (isset($_POST['simpan_jenis_sampah'])) {
        if (empty($nama_sampah) || $harga_per_kg === false || $harga_per_kg < 0 || empty($satuan)) {
            $_SESSION['error_message'] = "Nama sampah, harga (harus angka positif), dan satuan tidak boleh kosong.";
            redirect(BASE_URL . 'index.php?page=jenis_sampah/tambah');
        }

        // Cek apakah nama sampah sudah ada (opsional, bisa jadi ada sampah sama dengan harga beda)
        // Untuk contoh ini, kita izinkan nama sampah yang sama. Jika ingin unik, tambahkan pengecekan.

        $query_insert = "INSERT INTO jenis_sampah (nama_sampah, harga_per_kg, deskripsi, satuan) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($koneksi, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "sdss", $nama_sampah, $harga_per_kg, $deskripsi, $satuan);

        if (mysqli_stmt_execute($stmt_insert)) {
            $_SESSION['success_message'] = "Jenis sampah baru berhasil ditambahkan.";
            log_aktivitas('Tambah Jenis Sampah', 'jenis_sampah', null, "Jenis baru: $nama_sampah");
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan jenis sampah: " . mysqli_stmt_error($stmt_insert);
            error_log("Error insert jenis_sampah: " . mysqli_stmt_error($stmt_insert));
        }
        mysqli_stmt_close($stmt_insert);
        redirect(BASE_URL . 'index.php?page=jenis_sampah/data');

    // Proses Update Data Jenis Sampah
    } elseif (isset($_POST['update_jenis_sampah'])) {
        if (!isset($_POST['id_jenis_sampah']) || empty($_POST['id_jenis_sampah'])) {
            $_SESSION['error_message'] = "ID Jenis Sampah tidak valid untuk update.";
            redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
        }
        $id_jenis_sampah = sanitize_input($_POST['id_jenis_sampah']);

        if (empty($nama_sampah) || $harga_per_kg === false || $harga_per_kg < 0 || empty($satuan)) {
            $_SESSION['error_message'] = "Nama sampah, harga (harus angka positif), dan satuan tidak boleh kosong.";
            redirect(BASE_URL . 'index.php?page=jenis_sampah/edit&id=' . $id_jenis_sampah);
        }

        // Catat perubahan harga
        $query_harga_lama = "SELECT harga_per_kg FROM jenis_sampah WHERE id_jenis_sampah = ?";
        $stmt_harga = mysqli_prepare($koneksi, $query_harga_lama);
        mysqli_stmt_bind_param($stmt_harga, "i", $id_jenis_sampah);
        mysqli_stmt_execute($stmt_harga);
        $res_harga = mysqli_stmt_get_result($stmt_harga);
        $harga_lama_row = mysqli_fetch_assoc($res_harga);
        $harga_lama = $harga_lama_row ? (float)$harga_lama_row['harga_per_kg'] : 0;
        mysqli_stmt_close($stmt_harga);

        $query_update = "UPDATE jenis_sampah SET nama_sampah = ?, harga_per_kg = ?, deskripsi = ?, satuan = ? WHERE id_jenis_sampah = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "sdssi", $nama_sampah, $harga_per_kg, $deskripsi, $satuan, $id_jenis_sampah);

        if (mysqli_stmt_execute($stmt_update)) {
            if ($harga_lama != $harga_per_kg) {
                $q_history = "INSERT INTO harga_history (id_jenis_sampah, harga_lama, harga_baru, id_petugas) VALUES (?, ?, ?, ?)";
                $s_history = mysqli_prepare($koneksi, $q_history);
                mysqli_stmt_bind_param($s_history, "iddi", $id_jenis_sampah, $harga_lama, $harga_per_kg, $_SESSION['user_id']);
                mysqli_stmt_execute($s_history);
                mysqli_stmt_close($s_history);
            }
            $_SESSION['success_message'] = "Data jenis sampah berhasil diperbarui.";
            log_aktivitas('Update Jenis Sampah', 'jenis_sampah', $id_jenis_sampah, "Jenis diperbarui");
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui data jenis sampah: " . mysqli_stmt_error($stmt_update);
            error_log("Error update jenis_sampah (ID: $id_jenis_sampah): " . mysqli_stmt_error($stmt_update));
        }
        mysqli_stmt_close($stmt_update);
        redirect(BASE_URL . 'index.php?page=jenis_sampah/data');

    } else {
        $_SESSION['error_message'] = "Aksi tidak valid.";
        redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
    }

} else {
    $_SESSION['error_message'] = "Metode request tidak diizinkan.";
    redirect(BASE_URL . 'index.php?page=jenis_sampah/data');
}
?>
