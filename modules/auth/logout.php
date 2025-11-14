<?php
// modules/auth/logout.php
// Pastikan config/database.php sudah di-require oleh index.php utama
// atau require_once '../../config/database.php';

// Hancurkan semua variabel session
$_SESSION = array();

// Jika ingin menghancurkan session sepenuhnya, hapus juga cookie session.
// Catatan: Ini akan menghancurkan session, dan bukan hanya data session!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Akhirnya, hancurkan session.
session_destroy();

// Redirect ke halaman login dengan pesan logout berhasil
redirect(BASE_URL . 'index.php?page=auth/login&pesan=logout');
exit();
?>
