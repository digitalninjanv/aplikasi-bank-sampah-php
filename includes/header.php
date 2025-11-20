<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) { // Pastikan session sudah dimulai
    session_start();
}
$current_page = isset($_GET['page']) ? $_GET['page'] : '';
$user_level = isset($_SESSION['user_level']) ? $_SESSION['user_level'] : null;
$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'Tamu';

$current_hour = intval(date('H'));
if ($current_hour >= 5 && $current_hour < 12) {
    $greeting = 'Selamat pagi';
} elseif ($current_hour >= 12 && $current_hour < 15) {
    $greeting = 'Selamat siang';
} elseif ($current_hour >= 15 && $current_hour < 18) {
    $greeting = 'Selamat sore';
} else {
    $greeting = 'Selamat malam';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Sampah Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #1d4ed8;
            --surface: rgba(255, 255, 255, 0.92);
        }

        /* Custom scrollbar (opsional, tapi bisa mempercantik) */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            background: radial-gradient(circle at 20% 20%, rgba(14,165,233,0.08), transparent 25%),
                        radial-gradient(circle at 80% 0%, rgba(99,102,241,0.08), transparent 25%),
                        #f8fafc;
            color: #0f172a;
        }

        .sidebar {
            transition: transform 0.3s ease-in-out;
            backdrop-filter: blur(12px);
        }

        .sidebar-overlay { /* Untuk efek gelap di belakang sidebar mobile */
            transition: opacity 0.3s ease-in-out;
        }

        .active-nav-link {
            background: linear-gradient(120deg, #0ea5e9, #6366f1);
            color: white;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
        }

        .active-nav-link i { color: white; }

        .card-shell {
            background: var(--surface);
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .glass-header {
            backdrop-filter: blur(18px);
            background: rgba(255, 255, 255, 0.86);
        }

        .content-gradient {
            background: linear-gradient(180deg, rgba(14,165,233,0.05), transparent 40%);
        }

        .pill {
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(255, 255, 255, 0.65);
        }

        .welcome-card {
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(241, 245, 249, 0.8));
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.08);
        }

        .avatar-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.16), rgba(99, 102, 241, 0.18));
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #0ea5e9;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body class="antialiased">

<?php if (is_logged_in()): ?>
    <div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 z-20 bg-slate-900/50 opacity-0 pointer-events-none md:hidden"></div>

    <div class="flex min-h-screen">
        <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-30 w-72 bg-gradient-to-b from-sky-600 via-sky-700 to-indigo-700 text-white p-5 space-y-4 transform -translate-x-full md:translate-x-0 md:relative md:flex md:flex-col shadow-2xl rounded-r-3xl">
            <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="flex items-center space-x-3 px-2 py-3 mb-2">
                <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center shadow-inner">
                    <i class="fas fa-recycle text-2xl text-sky-100"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-sky-100/80">Bank Sampah</p>
                    <span class="text-xl font-bold leading-tight">Digital</span>
                </div>
            </a>

            <nav class="flex-grow overflow-y-auto pr-1 space-y-1">
                <?php
                if ($user_level == 'admin') {
                    include 'sidebar_admin.php';
                } elseif ($user_level == 'petugas') {
                    include 'sidebar_petugas.php';
                } elseif ($user_level == 'warga') {
                    include 'sidebar_warga.php';
                }
                ?>
            </nav>

            <div class="pt-4 border-t border-white/10 space-y-2">
                <a href="<?php echo BASE_URL; ?>index.php?page=profil" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white/10 transition duration-200 <?php echo ($current_page == 'profil') ? 'active-nav-link' : ''; ?>">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="fas fa-user-circle w-5"></i>
                    </div>
                    <div>
                        <p class="text-xs text-white/80">Akun</p>
                        <span class="font-semibold">Profil Saya</span>
                    </div>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=auth/logout" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-rose-100 hover:bg-rose-500/20 hover:text-white transition duration-200">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center">
                        <i class="fas fa-sign-out-alt w-5"></i>
                    </div>
                    <span class="font-semibold">Keluar</span>
                </a>
            </div>
        </aside>

        <div id="content-area" class="flex-1 flex flex-col overflow-hidden md:ml-0 bg-slate-50 content-gradient">
            <header class="glass-header sticky top-0 z-10 border-b border-slate-200/70 shadow-sm">
                <div class="px-4 sm:px-6 py-3 flex items-center gap-3">
                    <button id="menu-button" class="text-slate-700 md:hidden p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>

                    <div class="flex flex-col gap-1 w-full">
                        <span class="text-[11px] sm:text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase">
                            <?php echo strtoupper($greeting); ?>
                        </span>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">
                                <?php echo htmlspecialchars($user_nama); ?>
                            </span>
                            <?php if ($user_level): ?>
                                <span class="inline-flex items-center gap-2 text-xs sm:text-sm px-3 py-1.5 rounded-full border border-slate-200 bg-white shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    <span class="font-semibold text-slate-700">Login sebagai <?php echo ucfirst($user_level); ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6">
                <?php
                    $has_printable_transaksi = isset($_SESSION['last_transaksi_id']);
                    $last_transaksi_label = '';
                    if ($has_printable_transaksi) {
                        $tipe = $_SESSION['last_transaksi_tipe'] ?? '';
                        if ($tipe === 'setor') {
                            $last_transaksi_label = 'Setoran Sampah';
                        } elseif ($tipe === 'tarik_saldo') {
                            $last_transaksi_label = 'Penarikan Saldo';
                        } elseif (!empty($tipe)) {
                            $last_transaksi_label = ucfirst(str_replace('_', ' ', $tipe));
                        }
                    }
                ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg shadow" role="alert">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex">
                                <div class="py-1"><i class="fas fa-check-circle fa-lg mr-3 text-green-500"></i></div>
                                <div>
                                    <p class="font-bold">Sukses!</p>
                                    <p class="text-sm"><?php echo $_SESSION['success_message']; ?></p>
                                </div>
                            </div>
                            <?php if ($has_printable_transaksi): ?>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="<?php echo BASE_URL . 'index.php?page=transaksi/struk&id=' . urlencode($_SESSION['last_transaksi_id']); ?>"
                                       target="_blank" rel="noopener"
                                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white text-green-700 font-semibold text-sm border border-green-300 shadow-sm hover:bg-green-50">
                                        <i class="fas fa-print"></i>
                                        Cetak Struk<?php echo $last_transaksi_label ? ' ' . htmlspecialchars($last_transaksi_label) : ''; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                        unset($_SESSION['success_message']);
                        if ($has_printable_transaksi) {
                            unset($_SESSION['last_transaksi_id'], $_SESSION['last_transaksi_tipe']);
                        }
                    ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                     <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow" role="alert">
                        <div class="flex">
                            <div class="py-1"><i class="fas fa-exclamation-triangle fa-lg mr-3 text-red-500"></i></div>
                            <div>
                                <p class="font-bold">Error!</p>
                                <p class="text-sm"><?php echo $_SESSION['error_message']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
<?php else: // Jika belum login (misalnya halaman login) ?>
    <div class="min-h-screen flex flex-col bg-slate-50">
        <main class="flex-1">
<?php endif; ?>
