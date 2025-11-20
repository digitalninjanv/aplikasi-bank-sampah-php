<?php
// config/database.php
date_default_timezone_set('Asia/Jakarta');

  
  
  // Pengaturan Database (Sesuaikan dengan detail database Anda di Serv00)
define('DB_HOST', 'localhost'); // Biasanya 'localhost' atau alamat server DB dari Serv00
define('DB_USER', 'root'); // Username database Anda
define('DB_PASS', ''); // Password database Anda
define('DB_NAME', 'db_banksampah'); // Nama database Anda
  

// Membuat Koneksi
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

define('BASE_URL', 'http://localhost/gem/'); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function sanitize_input($data) {
    global $koneksi; 
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if (isset($koneksi) && $koneksi) { 
        $data = mysqli_real_escape_string($koneksi, $data);
    }
    return $data;
}

// Fungsi redirect yang lebih aman
function redirect($url) {
    if (!headers_sent($file, $line)) {
        header("Location: " . $url);
        exit();
    } else {
        // Catat error untuk developer
        error_log("Redirect_FAIL: Attempted to redirect to '{$url}' after headers were already sent from {$file}:{$line}. Displaying manual link.");
        
        // Tampilkan pesan dan link manual untuk pengguna
        echo "<div style='margin: 20px; padding: 20px; border: 2px solid #ffc107; background-color: #fff3e0; color: #856404; font-family: sans-serif; text-align: center; border-radius: 8px;'>";
        echo "<h3 style='color: #d68910; margin-top:0;'>Peringatan Sistem</h3>";
        echo "<p>Pengalihan otomatis tidak dapat dilakukan karena halaman sudah mulai ditampilkan (output dimulai dari file <strong>{$file}</strong> pada baris <strong>{$line}</strong>).</p>";
        echo "<p style='margin-top: 15px;'>Silakan klik tautan berikut untuk melanjutkan:</p>";
        echo "<a href='{$url}' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Lanjutkan ke Tujuan</a>";
        echo "</div>";
        // Penting untuk menghentikan eksekusi skrip lebih lanjut untuk mencegah output ganda atau error lain.
        exit();
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function check_user_level($allowed_levels) {
    if (!is_logged_in()) {
        // Pesan untuk redirect ini akan ditangani oleh fungsi redirect() yang sudah aman
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=belum_login_cl_v3');
        // exit() sudah ada di dalam redirect()
    }

    if (!isset($_SESSION['user_level'])) {
        $user_id_info = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'TIDAK DIKETAHUI';
        error_log("KRITIS_SESSION: Pengguna (ID: ".$user_id_info.") login tetapi user_level tidak ada di session.");
        
        if (session_status() == PHP_SESSION_ACTIVE) { 
            session_unset(); 
            session_destroy();
        }

        // Mulai sesi baru HANYA jika headers belum terkirim, untuk menyimpan pesan error spesifik
        // Fungsi redirect() akan menangani jika headers sudah terkirim.
        if (!headers_sent()) {
            if (session_status() == PHP_SESSION_NONE) { 
                session_start(); 
            }
            $_SESSION['error_message_for_login_redirect'] = "Sesi Anda bermasalah (level pengguna tidak terdefinisi). Silakan login kembali.";
        }
        redirect(BASE_URL . 'index.php?page=auth/login&pesan=sesi_level_error_cl_v3');
    }
    
    if (!in_array($_SESSION['user_level'], (array)$allowed_levels)) {
        // Set pesan error. Jika redirect gagal, pesan ini mungkin ditampilkan oleh header.php
        // atau pesan dari fungsi redirect() akan muncul.
        $_SESSION['error_message'] = "Anda tidak memiliki hak akses ke halaman ini.";
        redirect(BASE_URL . 'index.php?page=dashboard&pesan=akses_ditolak_level_cl_v3');
    }
}

function format_rupiah($angka) {
    if (!is_numeric($angka)) {
        return "Rp 0";
    }
    return "Rp " . number_format($angka, 0, ',', '.');
}

function format_tanggal_indonesia($tanggal_mysql, $dengan_waktu = true) {
    if (empty($tanggal_mysql) || $tanggal_mysql == '0000-00-00 00:00:00' || $tanggal_mysql == '0000-00-00') {
        return "-";
    }
    try {
        $date_obj = new DateTime($tanggal_mysql);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $tanggal = $date_obj->format('d');
        $bulan_index = (int)$date_obj->format('m');
        $tahun = $date_obj->format('Y');
        
        $format_akhir = $tanggal . ' ' . $bulan[$bulan_index] . ' ' . $tahun;
        
        if ($dengan_waktu) {
            $format_akhir .= ', ' . $date_obj->format('H:i');
        }
        return $format_akhir;
    } catch (Exception $e) {
        return $tanggal_mysql;
    }
}

?>

