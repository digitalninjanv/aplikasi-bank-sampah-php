<?php
// modules/warga/proses_simpan.php
check_user_level(['admin', 'petugas']); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Proses Tambah Warga Baru (Mode Cepat)
    if (isset($_POST['simpan_warga_cepat'])) {
        $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
        $no_telepon = sanitize_input($_POST['no_telepon']);
        $alamat = isset($_POST['alamat']) ? sanitize_input($_POST['alamat']) : ''; 

        $level = 'warga'; 

        if (empty($nama_lengkap) || empty($no_telepon)) {
            $_SESSION['error_message'] = "Nama lengkap dan nomor telepon tidak boleh kosong.";
            redirect(BASE_URL . 'index.php?page=warga/tambah');
        }

        // Gunakan nomor telepon (setelah dibersihkan) sebagai username
        $username = preg_replace('/[^0-9]/', '', $no_telepon); 
        if (empty($username)) {
            $_SESSION['error_message'] = "Format nomor telepon tidak valid untuk dijadikan username (harus mengandung angka).";
            redirect(BASE_URL . 'index.php?page=warga/tambah');
        }
        
        // Generate random password (tidak akan digunakan warga untuk login via form standar)
        $password_plain = bin2hex(random_bytes(8)); // Contoh: 16 karakter hex
        $hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);
        $saldo_awal = 0.00;

        // Cek keunikan username (dari no_telepon)
        $query_cek_username = "SELECT id_pengguna FROM pengguna WHERE username = ?";
        $stmt_cek_username = mysqli_prepare($koneksi, $query_cek_username);
        mysqli_stmt_bind_param($stmt_cek_username, "s", $username);
        mysqli_stmt_execute($stmt_cek_username);
        mysqli_stmt_store_result($stmt_cek_username);

        if (mysqli_stmt_num_rows($stmt_cek_username) > 0) {
            $_SESSION['error_message'] = "Nomor telepon '{$no_telepon}' (yang menjadi username '{$username}') sudah terdaftar. Gunakan nomor lain.";
            mysqli_stmt_close($stmt_cek_username);
            redirect(BASE_URL . 'index.php?page=warga/tambah');
        }
        mysqli_stmt_close($stmt_cek_username);

        // Cek keunikan nomor telepon asli (jika berbeda dengan username setelah dibersihkan, meskipun seharusnya sama)
        $query_cek_no_telp = "SELECT id_pengguna FROM pengguna WHERE no_telepon = ?";
        $stmt_cek_telp = mysqli_prepare($koneksi, $query_cek_no_telp);
        mysqli_stmt_bind_param($stmt_cek_telp, "s", $no_telepon);
        mysqli_stmt_execute($stmt_cek_telp);
        mysqli_stmt_store_result($stmt_cek_telp);
        if (mysqli_stmt_num_rows($stmt_cek_telp) > 0) {
            $_SESSION['error_message'] = "Nomor telepon '{$no_telepon}' sudah terdaftar untuk warga lain.";
            mysqli_stmt_close($stmt_cek_telp);
            redirect(BASE_URL . 'index.php?page=warga/tambah');
        }
        mysqli_stmt_close($stmt_cek_telp);

        $query_insert = "INSERT INTO pengguna (nama_lengkap, username, password, level, alamat, no_telepon, saldo) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($koneksi, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "ssssssd", $nama_lengkap, $username, $hashed_password, $level, $alamat, $no_telepon, $saldo_awal);

        if (mysqli_stmt_execute($stmt_insert)) {
            $_SESSION['success_message'] = "Data warga baru berhasil ditambahkan. Username terdaftar: {$username}.";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan data warga: " . mysqli_stmt_error($stmt_insert);
            error_log("Error insert warga: " . mysqli_stmt_error($stmt_insert));
        }
        mysqli_stmt_close($stmt_insert);
        redirect(BASE_URL . 'index.php?page=warga/data');

    // Proses Update Data Warga
    } elseif (isset($_POST['update_warga'])) {
        if (!isset($_POST['id_pengguna']) || empty($_POST['id_pengguna'])) {
            $_SESSION['error_message'] = "ID Warga tidak valid untuk update.";
            redirect(BASE_URL . 'index.php?page=warga/data');
        }
        $id_pengguna = sanitize_input($_POST['id_pengguna']);
        $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
        $no_telepon_baru = sanitize_input($_POST['no_telepon']);
        $alamat_baru = isset($_POST['alamat']) ? sanitize_input($_POST['alamat']) : '';

        if (empty($nama_lengkap) || empty($no_telepon_baru)) {
            $_SESSION['error_message'] = "Nama lengkap dan nomor telepon tidak boleh kosong.";
            redirect(BASE_URL . 'index.php?page=warga/edit&id=' . $id_pengguna);
        }

        // Username baru akan berasal dari no_telepon_baru
        $username_baru = preg_replace('/[^0-9]/', '', $no_telepon_baru);
        if (empty($username_baru)) {
            $_SESSION['error_message'] = "Format nomor telepon baru tidak valid untuk dijadikan username.";
            redirect(BASE_URL . 'index.php?page=warga/edit&id=' . $id_pengguna);
        }

        // Cek apakah username baru (dari no_telepon baru) sudah digunakan oleh PENGGUNA LAIN
        $query_cek_username_update = "SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?";
        $stmt_cek_username_upd = mysqli_prepare($koneksi, $query_cek_username_update);
        mysqli_stmt_bind_param($stmt_cek_username_upd, "si", $username_baru, $id_pengguna);
        mysqli_stmt_execute($stmt_cek_username_upd);
        mysqli_stmt_store_result($stmt_cek_username_upd);

        if (mysqli_stmt_num_rows($stmt_cek_username_upd) > 0) {
            $_SESSION['error_message'] = "Nomor telepon baru '{$no_telepon_baru}' (yang menjadi username '{$username_baru}') sudah digunakan oleh pengguna lain.";
            mysqli_stmt_close($stmt_cek_username_upd);
            redirect(BASE_URL . 'index.php?page=warga/edit&id=' . $id_pengguna);
        }
        mysqli_stmt_close($stmt_cek_username_upd);

        // Cek apakah no_telepon baru sudah digunakan oleh PENGGUNA LAIN
        $query_cek_no_telp_update = "SELECT id_pengguna FROM pengguna WHERE no_telepon = ? AND id_pengguna != ?";
        $stmt_cek_telp_update = mysqli_prepare($koneksi, $query_cek_no_telp_update);
        mysqli_stmt_bind_param($stmt_cek_telp_update, "si", $no_telepon_baru, $id_pengguna);
        mysqli_stmt_execute($stmt_cek_telp_update);
        mysqli_stmt_store_result($stmt_cek_telp_update);

        if (mysqli_stmt_num_rows($stmt_cek_telp_update) > 0) {
            $_SESSION['error_message'] = "Nomor telepon baru '{$no_telepon_baru}' sudah terdaftar untuk warga lain.";
            mysqli_stmt_close($stmt_cek_telp_update);
            redirect(BASE_URL . 'index.php?page=warga/edit&id=' . $id_pengguna);
        }
        mysqli_stmt_close($stmt_cek_telp_update);


        // Update data: nama_lengkap, username (dari no_telepon), no_telepon, alamat.
        // Password warga tidak diubah dari form ini.
        $query_update = "UPDATE pengguna SET nama_lengkap = ?, username = ?, no_telepon = ?, alamat = ? WHERE id_pengguna = ? AND level = 'warga'";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssssi", $nama_lengkap, $username_baru, $no_telepon_baru, $alamat_baru, $id_pengguna);

        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['success_message'] = "Data warga berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui data warga: " . mysqli_stmt_error($stmt_update);
            error_log("Error update warga (ID: $id_pengguna): " . mysqli_stmt_error($stmt_update));
        }
        mysqli_stmt_close($stmt_update);
        redirect(BASE_URL . 'index.php?page=warga/data');

    } else {
        $_SESSION['error_message'] = "Aksi tidak valid.";
        redirect(BASE_URL . 'index.php?page=warga/data');
    }

} else {
    $_SESSION['error_message'] = "Metode request tidak diizinkan.";
    redirect(BASE_URL . 'index.php?page=warga/data');
}
?>
