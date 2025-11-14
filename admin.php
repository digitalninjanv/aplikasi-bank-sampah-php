<?php
// admin.php
session_start();

require_once 'config.php';
require_once 'functions.php';

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

if (!isset($_SESSION['admin_logged_in']) && $page !== 'login' && $page !== 'proses_login') {
    $page = 'login';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bank Sampah</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #333; margin:0; }
        .admin-nav { background-color: #2E8B57; /* Warna hijau tema */ padding: 15px 0; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .admin-nav a { color: white; margin: 0 15px; text-decoration: none; font-weight: 500; font-size:0.95em; padding: 8px 12px; border-radius: 5px; transition: background-color 0.3s ease;}
        .admin-nav a:hover, .admin-nav a.active { background-color: #388E3C; /* Warna hover/aktif */ }
        
        .admin-container { width: 95%; max-width: 1100px; margin: 30px auto; padding: 25px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .admin-title { font-size:1.8em; color: #4CAF50; margin-bottom: 25px; border-bottom: 3px solid #4CAF50; padding-bottom:12px; font-weight:600;}
        
        .form-admin .form-group { margin-bottom: 18px; }
        .form-admin label { display:block; margin-bottom:6px; font-weight:600; font-size:0.9em; color:#333;}
        .form-admin input[type="text"],
        .form-admin input[type="password"],
        .form-admin input[type="number"],
        .form-admin input[type="date"],
        .form-admin input[type="email"],
        .form-admin textarea,
        .form-admin select {
            width:100%; padding:12px; font-size:0.95em; border:1px solid #ccc; border-radius:6px; box-sizing: border-box; transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-admin input:focus, .form-admin textarea:focus, .form-admin select:focus {
            border-color:#4CAF50; box-shadow: 0 0 0 2px rgba(76,175,80,0.2); outline:none;
        }
        .form-admin textarea { min-height: 80px; }

        .btn { display:inline-block; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:600; font-size:0.95em; cursor:pointer; transition: all 0.3s ease; border:none; }
        .btn-primary { background-color:#4CAF50; color:white; }
        .btn-primary:hover { background-color:#45a049; transform: translateY(-1px); }
        .btn-secondary { background-color:#007bff; color:white; }
        .btn-secondary:hover { background-color:#0069d9; transform: translateY(-1px); }
        .btn-danger { background-color:#dc3545; color:white; }
        .btn-danger:hover { background-color:#c82333; transform: translateY(-1px); }
        .btn-warning { background-color:#ffc107; color:#212529; }
        .btn-warning:hover { background-color:#e0a800; transform: translateY(-1px); }
        .btn-sm { padding: 6px 12px; font-size: 0.85em; }


        .admin-table { width:100%; border-collapse:collapse; margin-top:25px; font-size:0.9em; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius:8px; overflow:hidden;}
        .admin-table th, .admin-table td { border:1px solid #e0e0e0; padding:12px 15px; text-align:left; vertical-align:middle;}
        .admin-table th { background-color:#f8f9fa; color:#333; font-weight:600; text-transform: uppercase; letter-spacing: 0.5px;}
        .admin-table tbody tr:nth-child(even) { background-color:#fdfdfd; }
        .admin-table tbody tr:hover { background-color:#f1f1f1; }
        .admin-table .actions a { margin-right: 8px; }

        .message { padding:15px; margin-bottom:20px; border-radius:6px; font-size:0.95em; border:1px solid transparent; }
        .message.success { background-color:#d4edda; color:#155724; border-color:#c3e6cb;}
        .message.error { background-color:#f8d7da; color:#721c24; border-color:#f5c6cb;}
        .message.info { background-color:#d1ecf1; color:#0c5460; border-color:#bee5eb;}

        /* Responsive table */
        .table-responsive-wrapper { overflow-x: auto; }

        /* Footer */
        .main-footer { text-align:center; padding:20px; background-color:#e9ecef; color:#6c757d; font-size:0.9em; margin-top:30px; border-top:1px solid #dee2e6;}

    </style>
</head>
<body>

<?php if (isset($_SESSION['admin_logged_in'])): ?>
    <nav class="admin-nav">
        <a href="admin.php?page=dashboard" class="<?php echo ($page === 'dashboard' || $page === '') ? 'active' : ''; ?>">Dashboard</a>
        <a href="admin.php?page=nasabah_list" class="<?php echo (strpos($page, 'nasabah') === 0) ? 'active' : ''; ?>">Kelola Nasabah</a>
        <a href="admin.php?page=setor_sampah" class="<?php echo ($page === 'setor_sampah') ? 'active' : ''; ?>">Input Setoran</a>
        <a href="admin.php?page=tarik_saldo" class="<?php echo ($page === 'tarik_saldo') ? 'active' : ''; ?>">Input Penarikan</a>
        <a href="admin.php?page=jenis_sampah" class="<?php echo ($page === 'jenis_sampah') ? 'active' : ''; ?>">Jenis Sampah</a>
        <a href="admin.php?page=laporan" class="<?php echo ($page === 'laporan') ? 'active' : ''; ?>">Laporan</a>
        <a href="admin.php?page=logout">Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a>
    </nav>
<?php endif; ?>

<div class="admin-container">
<?php
// Tampilkan pesan flash jika ada
if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    echo '<div class="message ' . htmlspecialchars($flash['type']) . '">' . htmlspecialchars($flash['text']) . '</div>';
    unset($_SESSION['flash_message']); // Hapus setelah ditampilkan
}

switch ($page) {
    case 'login':
        if (isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=dashboard'); exit; }
        ?>
        <h2 class="admin-title">Login Admin Bank Sampah</h2>
        <form class="form-admin" method="POST" action="admin.php?page=proses_login" style="max-width:400px; margin:auto;">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>
        <?php
        break;

    case 'proses_login':
        // ... (kode proses_login tetap sama) ...
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Login berhasil! Selamat datang.'];
                header('Location: admin.php?page=dashboard');
                exit;
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Username atau password salah!'];
                header('Location: admin.php?page=login');
                exit;
            }
        } else {
            header('Location: admin.php?page=login');
            exit;
        }
        break;

    case 'dashboard':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        ?>
        <h2 class="admin-title">Dashboard Admin</h2>
        <p>Selamat datang di panel admin Bank Sampah, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>!</p>
        <p>Pilih menu di atas untuk mengelola data.</p>
        <?php
        break;

    case 'logout':
        // ... (kode logout tetap sama) ...
        session_unset(); 
        session_destroy(); 
        // Set pesan setelah logout, sebelum redirect
        session_start(); // Mulai session lagi untuk flash message
        $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Anda telah berhasil logout.'];
        header('Location: admin.php?page=login'); 
        exit;
        break;

    // --- Manajemen Nasabah ---
    case 'nasabah_list':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        $daftarNasabah = getAllNasabah($conn);
        ?>
        <h2 class="admin-title">Daftar Nasabah</h2>
        <p><a href="admin.php?page=nasabah_tambah" class="btn btn-primary">+ Tambah Nasabah Baru</a></p>
        
        <?php if (empty($daftarNasabah)): ?>
            <p class="message info">Belum ada data nasabah.</p>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Nasabah</th>
                            <th>Nama Lengkap</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Tgl Daftar</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarNasabah as $nasabah): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nasabah['id_nasabah']); ?></td>
                            <td><?php echo htmlspecialchars($nasabah['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($nasabah['no_telepon'] ?: '-'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($nasabah['alamat'] ?: '-')); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($nasabah['tgl_daftar']))); ?></td>
                            <td><?php echo htmlspecialchars($nasabah['email'] ?: '-'); ?></td>
                            <td class="actions">
                                <a href="admin.php?page=nasabah_edit&id=<?php echo htmlspecialchars($nasabah['id_nasabah']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="admin.php?page=nasabah_hapus&id=<?php echo htmlspecialchars($nasabah['id_nasabah']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus nasabah ini?');">Hapus</a>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php
        break;

    case 'nasabah_tambah':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        // Ambil ID Nasabah berikutnya (jika menggunakan generator)
        $nextIdNasabah = generateNextIdNasabah($conn); // Fungsi dari functions.php
        ?>
        <h2 class="admin-title">Tambah Nasabah Baru</h2>
        <form class="form-admin" method="POST" action="admin.php?page=nasabah_proses_tambah">
            <div class="form-group">
                <label for="id_nasabah">ID Nasabah:</label>
                <input type="text" id="id_nasabah" name="id_nasabah" value="<?php echo htmlspecialchars($nextIdNasabah); ?>" required>
                <small>ID Nasabah disarankan unik. Anda bisa menggunakan format yang sudah ada atau generate baru.</small>
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label for="no_telepon">Nomor Telepon (Opsional):</label>
                <input type="text" id="no_telepon" name="no_telepon">
            </div>
            <div class="form-group">
                <label for="alamat">Alamat (Opsional):</label>
                <textarea id="alamat" name="alamat"></textarea>
            </div>
             <div class="form-group">
                <label for="email">Email (Opsional):</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="tgl_daftar">Tanggal Daftar:</label>
                <input type="date" id="tgl_daftar" name="tgl_daftar" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="catatan">Catatan (Opsional):</label>
                <textarea id="catatan" name="catatan"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Nasabah</button>
            <a href="admin.php?page=nasabah_list" class="btn" style="background-color:#6c757d; color:white; margin-left:10px;">Batal</a>
        </form>
        <?php
        break;

    case 'nasabah_proses_tambah':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi dasar (bisa ditambahkan validasi yang lebih ketat)
            if (empty($_POST['id_nasabah']) || empty($_POST['nama_lengkap']) || empty($_POST['tgl_daftar'])) {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ID Nasabah, Nama Lengkap, dan Tanggal Daftar wajib diisi.'];
                header('Location: admin.php?page=nasabah_tambah'); // Kembali ke form tambah
                exit;
            }

            // Cek apakah ID Nasabah sudah ada (contoh validasi keunikan)
            $cekNasabah = getNasabahInfo($conn, $_POST['id_nasabah']); // getNasabahInfo dari functions.php sebelumnya
            if ($cekNasabah) {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ID Nasabah ' . htmlspecialchars($_POST['id_nasabah']) . ' sudah terdaftar. Gunakan ID lain.'];
                 header('Location: admin.php?page=nasabah_tambah');
                 exit;
            }

            $data_nasabah = [
                'id_nasabah' => trim($_POST['id_nasabah']),
                'nama_lengkap' => trim($_POST['nama_lengkap']),
                'no_telepon' => isset($_POST['no_telepon']) ? trim($_POST['no_telepon']) : null,
                'alamat' => isset($_POST['alamat']) ? trim($_POST['alamat']) : null,
                'email' => isset($_POST['email']) ? trim($_POST['email']) : null,
                'tgl_daftar' => $_POST['tgl_daftar'],
                'catatan' => isset($_POST['catatan']) ? trim($_POST['catatan']) : null,
            ];

            if (tambahNasabah($conn, $data_nasabah)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Nasabah baru berhasil ditambahkan!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Gagal menambahkan nasabah baru. Periksa log server untuk detail.'];
            }
            header('Location: admin.php?page=nasabah_list'); // Redirect ke daftar nasabah
            exit;
        } else {
            // Jika bukan POST, redirect
            header('Location: admin.php?page=nasabah_tambah');
            exit;
        }
        break;
    
    // Placeholder untuk Edit & Hapus Nasabah (akan ditambahkan kemudian)
    case 'nasabah_edit':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        echo "<h2 class='admin-title'>Edit Data Nasabah</h2><p>Fitur edit nasabah akan dikembangkan di sini.</p>";
        // Ambil ID dari GET, ambil data nasabah, tampilkan di form
        break;
    case 'nasabah_hapus':
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        echo "<h2 class='admin-title'>Hapus Data Nasabah</h2><p>Logika hapus nasabah akan diproses di sini setelah konfirmasi.</p>";
        // Ambil ID dari GET, proses hapus, redirect dengan pesan
        break;


    // --- Placeholder untuk Fitur Lainnya ---
    case 'setor_sampah': // (Isi dengan form input setoran)
    case 'tarik_saldo':  // (Isi dengan form input penarikan)
    case 'jenis_sampah': // (CRUD untuk jenis sampah dan harga)
    case 'laporan':      // (Tampilan berbagai laporan)
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: admin.php?page=login'); exit; }
        echo "<h2 class='admin-title'>" . ucfirst(str_replace('_', ' ', $page)) . "</h2><p>Fitur ini sedang dalam pengembangan.</p>";
        break;
    
    default:
        echo "<h2 class='admin-title'>Halaman Tidak Ditemukan</h2>";
        echo "<p>Maaf, halaman yang Anda cari tidak tersedia.</p>";
        echo "<p><a href='admin.php?page=dashboard' class='btn btn-primary'>Kembali ke Dashboard</a></p>";
        break;
}
?>
</div>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> Admin Panel Bank Sampah Kampung Kita. Modern & Responsif.</p>
</footer>

</body>
</html>