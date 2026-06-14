<?php
// modules/auth/proses_register.php
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['register_warga'])) {
    redirect(BASE_URL . 'index.php?page=auth/register');
}

$nama = sanitize_input($_POST['nama_lengkap'] ?? '');
$telepon = sanitize_input($_POST['no_telepon'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$alamat = sanitize_input($_POST['alamat'] ?? '');

if (empty($nama) || empty($telepon) || empty($password)) {
    $_SESSION['error_message'] = "Nama, telepon, dan password wajib diisi.";
    redirect(BASE_URL . 'index.php?page=auth/register');
}

if ($password !== $password_confirm) {
    $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
    redirect(BASE_URL . 'index.php?page=auth/register');
}

if (strlen($password) < 6) {
    $_SESSION['error_message'] = "Password minimal 6 karakter.";
    redirect(BASE_URL . 'index.php?page=auth/register');
}

$username = preg_replace('/[^0-9]/', '', $telepon);
if (empty($username)) {
    $_SESSION['error_message'] = "Nomor telepon tidak valid.";
    redirect(BASE_URL . 'index.php?page=auth/register');
}

$cek = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE username = ? OR no_telepon = ?");
mysqli_stmt_bind_param($cek, "ss", $username, $telepon);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);
if (mysqli_stmt_num_rows($cek) > 0) {
    $_SESSION['error_message'] = "Nomor telepon sudah terdaftar. Gunakan nomor lain atau <a href='" . BASE_URL . "index.php?page=auth/lupa_password' class='underline'>lupa password</a>.";
    mysqli_stmt_close($cek);
    redirect(BASE_URL . 'index.php?page=auth/register');
}
mysqli_stmt_close($cek);

$hashed = password_hash($password, PASSWORD_DEFAULT);
$saldo = 0;
$stmt = mysqli_prepare($koneksi, "INSERT INTO pengguna (nama_lengkap, username, password, level, alamat, no_telepon, saldo, status) VALUES (?, ?, ?, 'warga', ?, ?, ?, 'aktif')");
mysqli_stmt_bind_param($stmt, "sssssd", $nama, $username, $hashed, $alamat, $telepon, $saldo);

if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($koneksi);
    log_aktivitas('register_warga', 'pengguna', $new_id, "Registrasi mandiri: $nama");
    $_SESSION['register_result'] = [
        'success' => true,
        'nama' => $nama,
        'username' => $username,
        'password' => $password,
    ];
    $_SESSION['success_message'] = "Pendaftaran berhasil! Silakan login.";
} else {
    $_SESSION['error_message'] = "Gagal mendaftar. Silakan coba lagi.";
}
mysqli_stmt_close($stmt);
redirect(BASE_URL . 'index.php?page=auth/register');
