<?php
// config/database.php
date_default_timezone_set('Asia/Jakarta');

  
  
  // Pengaturan Database (Sesuaikan dengan detail database Anda di Serv00)
define('DB_HOST', '127.0.0.1'); // Force TCP (Unix socket has restricted permissions)
define('DB_USER', 'banksampah'); // Username database Anda
define('DB_PASS', 'banksampah'); // Password database Anda
define('DB_NAME', 'db_banksampah'); // Nama database Anda
  

// Nonaktifkan laporan error MySQL ke output
mysqli_report(MYSQLI_REPORT_OFF);

// Membuat Koneksi
$koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Maaf, terjadi gangguan koneksi database. Silakan coba lagi nanti.");
}

define('BASE_URL', 'https://resume-looks-chamber-nest.trycloudflare.com/'); 

// Keamanan sesi
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session timeout: 30 menit idle
$session_timeout = 30 * 60;
if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > $session_timeout) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                !empty($params["secure"]), $params["httponly"]
            );
        }
        session_destroy();
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['error_message'] = "Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Silakan login kembali.";
        header("Location: " . BASE_URL . "index.php?page=auth/login&pesan=timeout");
        exit();
    }
    $_SESSION['login_time'] = time(); // Perbarui waktu aktivitas
}

// Load pengaturan aplikasi dari database
$app_settings = [];
$settings_query = @mysqli_query($koneksi, "SELECT setting_key, setting_value FROM app_settings");
if ($settings_query) {
    while ($s = mysqli_fetch_assoc($settings_query)) {
        $app_settings[$s['setting_key']] = $s['setting_value'];
    }
}
define('APP_NAME', $app_settings['app_name'] ?? 'Bank Sampah Digital');
define('APP_ADDRESS', $app_settings['app_address'] ?? '');
define('APP_PHONE', $app_settings['app_phone'] ?? '');

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

// === CSRF Protection ===
function generate_csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['_csrf_token']) || empty($token)) return false;
    return hash_equals($_SESSION['_csrf_token'], $token);
}

function csrf_field() {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

function csrf_meta() {
    return '<meta name="csrf-token" content="' . htmlspecialchars(generate_csrf_token()) . '">';
}

function require_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            $_SESSION['error_message'] = 'Token keamanan tidak valid. Silakan coba lagi.';
            $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=dashboard';
            redirect($referer);
        }
    }
}

// === Log Aktivitas ===
function log_aktivitas($aksi, $tabel = null, $id_record = null, $detail = null) {
    global $koneksi;
    $id_pengguna = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['user_username'] ?? 'anonim';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = mysqli_prepare($koneksi, "INSERT INTO log_aktivitas (id_pengguna, username, aksi, tabel, id_record, detail, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssiss", $id_pengguna, $username, $aksi, $tabel, $id_record, $detail, $ip);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
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

