<?php
// modules/auth/proses_lupa_password.php
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . 'index.php?page=auth/lupa_password');

// Step 1: Minta reset (generate token)
if (isset($_POST['minta_reset'])) {
    $telepon = sanitize_input($_POST['no_telepon'] ?? '');
    if (empty($telepon)) {
        $_SESSION['error_message'] = "Nomor telepon wajib diisi.";
        redirect(BASE_URL . 'index.php?page=auth/lupa_password');
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id_pengguna, nama_lengkap FROM pengguna WHERE no_telepon = ?");
    mysqli_stmt_bind_param($stmt, "s", $telepon);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $upd = mysqli_prepare($koneksi, "UPDATE pengguna SET reset_token = ?, reset_expires = ? WHERE id_pengguna = ?");
        mysqli_stmt_bind_param($upd, "ssi", $token, $expires, $user['id_pengguna']);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);

        $reset_url = BASE_URL . 'index.php?page=auth/lupa_password&token=' . urlencode($token);
        log_aktivitas('minta_reset_password', 'pengguna', $user['id_pengguna'], "Token reset generated");
        $_SESSION['success_message'] = "Link reset password telah dibuat. Dalam production, link akan dikirim via SMS. Untuk demo: <a href='" . htmlspecialchars($reset_url) . "' class='underline font-bold'>Klik di sini untuk reset</a>.";
    } else {
        $_SESSION['error_message'] = "Nomor telepon tidak ditemukan.";
    }
    redirect(BASE_URL . 'index.php?page=auth/lupa_password');
}

// Step 2: Reset password with token
if (isset($_POST['reset_password'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($token) || empty($password)) {
        $_SESSION['error_message'] = "Token dan password wajib diisi.";
        redirect(BASE_URL . 'index.php?page=auth/lupa_password');
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
        redirect(BASE_URL . 'index.php?page=auth/lupa_password&token=' . urlencode($token));
    }

    if (strlen($password) < 6) {
        $_SESSION['error_message'] = "Password minimal 6 karakter.";
        redirect(BASE_URL . 'index.php?page=auth/lupa_password&token=' . urlencode($token));
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE reset_token = ? AND reset_expires > NOW()");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $_SESSION['error_message'] = "Token tidak valid atau sudah kadaluarsa.";
        redirect(BASE_URL . 'index.php?page=auth/lupa_password');
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $upd = mysqli_prepare($koneksi, "UPDATE pengguna SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id_pengguna = ?");
    mysqli_stmt_bind_param($upd, "si", $hashed, $user['id_pengguna']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    log_aktivitas('reset_password', 'pengguna', $user['id_pengguna'], "Password reset via lupa password");
    $_SESSION['success_message'] = "Password berhasil direset. Silakan login dengan password baru.";
    redirect(BASE_URL . 'index.php?page=auth/login');
}

redirect(BASE_URL . 'index.php?page=auth/lupa_password');
