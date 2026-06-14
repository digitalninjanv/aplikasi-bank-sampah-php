<?php
if (session_status() == PHP_SESSION_NONE) {
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
    <title><?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Bank Sampah Digital'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --secondary: #6366f1;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --surface: #ffffff;
            --background: #f1f5f9;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #f8fafc; color: #0f172a; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', 'Inter', system-ui, sans-serif; }
        .sidebar { transition: transform 0.3s ease-in-out; }
        .sidebar-overlay { transition: opacity 0.3s ease-in-out; }
        .nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; transition: all 0.15s ease; color: rgba(255,255,255,0.7); }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: rgba(37,99,235,0.25); color: white; border-left: 3px solid #3b82f6; padding-left: calc(1rem - 3px); }
        .nav-link i { width: 1.25rem; text-align: center; font-size: 0.875rem; }
        .nav-sub-link { display: block; padding: 0.5rem 1rem 0.5rem 2.75rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 400; transition: all 0.15s ease; color: rgba(255,255,255,0.6); }
        .nav-sub-link:hover { background: rgba(255,255,255,0.08); color: white; }
        .nav-sub-link.active { color: #93c5fd; font-weight: 500; border-left: 2px solid #3b82f6; padding-left: calc(2.75rem - 2px); }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(15,23,42,0.06); }
        .stat-card { border-radius: 0.75rem; padding: 1.25rem; position: relative; overflow: hidden; }
        .stat-card .icon-bg { position: absolute; right: -0.5rem; bottom: -0.5rem; opacity: 0.12; font-size: 4rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; transition: all 0.15s ease; cursor: pointer; border: none; line-height: 1.25rem; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover:not(:disabled) { background: #1d4ed8; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover:not(:disabled) { background: #047857; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover:not(:disabled) { background: #b91c1c; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover:not(:disabled) { background: #b45309; }
        .btn-outline { background: transparent; color: #475569; border: 1px solid #cbd5e1; }
        .btn-outline:hover:not(:disabled) { background: #f1f5f9; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
        .btn-lg { padding: 0.75rem 1.5rem; font-size: 1rem; }
        .form-input { display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: white; padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #0f172a; transition: all 0.15s ease; }
        .form-input:focus { outline: none; border-color: #3b82f6; ring: 2px solid #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .form-input::placeholder { color: #94a3b8; }
        .form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.375rem; }
        .table-wrap { overflow-x: auto; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: white; }
        .table-wrap table { width: 100%; border-collapse: collapse; }
        .table-wrap th { padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .table-wrap td { padding: 0.75rem 1rem; font-size: 0.875rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table-wrap tr:last-child td { border-bottom: none; }
        .table-wrap tbody tr:hover { background: #f8fafc; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .page-header p { font-size: 0.875rem; color: #64748b; margin-top: 0.25rem; }
        .flash-success { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; }
        .flash-success i { color: #16a34a; font-size: 1.25rem; margin-top: 0.125rem; }
        .flash-success p { color: #166534; font-size: 0.875rem; }
        .flash-error { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; }
        .flash-error i { color: #dc2626; font-size: 1.25rem; margin-top: 0.125rem; }
        .flash-error p { color: #991b1b; font-size: 0.875rem; }
        .pagination { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; margin-top: 1.5rem; }
        .pagination-info { font-size: 0.8125rem; color: #64748b; }
        .pagination-links { display: flex; align-items: center; gap: 0.5rem; }
        .pagination-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; border: 1px solid #e2e8f0; background: white; color: #475569; transition: all 0.15s ease; }
        .pagination-btn:hover:not(.disabled) { background: #f1f5f9; border-color: #cbd5e1; }
        .pagination-btn.disabled { opacity: 0.4; cursor: not-allowed; }
        .pagination-page { font-size: 0.8125rem; color: #64748b; padding: 0 0.25rem; }
        .mobile-card { background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem; box-shadow: 0 1px 2px rgba(15,23,42,0.04); }
        .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .empty-state p { font-size: 0.875rem; }
        .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #d1fae5; color: #047857; }
        .badge-red { background: #fecaca; color: #b91c1c; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .avatar-initials { width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; background: #dbeafe; color: #2563eb; }
        @media (max-width: 767px) {
            .page-header h1 { font-size: 1.25rem; }
            .stat-card { padding: 1rem; }
            .stat-card .stat-value { font-size: 1.5rem; }
        }
    </style>
</head>
<body class="antialiased">

<?php if (is_logged_in()): ?>
    <div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 z-20 bg-slate-900/50 opacity-0 pointer-events-none md:hidden"></div>

    <div class="flex min-h-screen">
        <aside id="sidebar" role="navigation" aria-label="Navigasi utama" class="sidebar fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-white flex flex-col transform -translate-x-full md:translate-x-0 md:relative shadow-xl">
            <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <i class="fas fa-recycle text-white text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-semibold">Bank Sampah</p>
                    <span class="text-lg font-bold font-['Poppins'] leading-tight">Digital</span>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
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

            <div class="px-3 py-3 border-t border-slate-800 space-y-1">
                <a href="<?php echo BASE_URL; ?>index.php?page=profil" class="nav-link <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Profil Saya</span>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?page=auth/logout" class="nav-link text-red-300 hover:text-red-200 hover:bg-red-500/10">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <div id="content-area" class="flex-1 flex flex-col min-h-screen">
            <header class="sticky top-0 z-10 bg-white border-b border-slate-200 shadow-sm">
                <div class="px-4 sm:px-6 py-3 flex items-center gap-3">
                    <button id="menu-button" aria-label="Buka menu navigasi" class="text-slate-600 md:hidden p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-[11px] font-semibold tracking-[0.15em] text-slate-400 uppercase truncate"><?php echo strtoupper($greeting); ?></span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xl sm:text-2xl font-bold font-['Poppins'] text-slate-900 truncate"><?php echo htmlspecialchars($user_nama); ?></span>
                            <?php if ($user_level): ?>
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-medium border border-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <?php echo ucfirst($user_level); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
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
                    <div class="flash-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <div class="flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <p class="font-semibold text-emerald-800">Berhasil</p>
                                <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                            </div>
                            <?php if ($has_printable_transaksi): ?>
                                <a href="<?php echo BASE_URL . 'index.php?page=transaksi/struk&id=' . urlencode($_SESSION['last_transaksi_id']); ?>"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline shrink-0">
                                    <i class="fas fa-print"></i>
                                    Cetak Struk<?php echo $last_transaksi_label ? ' ' . htmlspecialchars($last_transaksi_label) : ''; ?>
                                </a>
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
                    <div class="flash-error" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <p class="font-semibold text-red-800">Gagal</p>
                            <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
<?php else: ?>
    <div class="min-h-screen flex flex-col bg-slate-50">
        <main class="flex-1">
<?php endif; ?>
