<?php
$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? $_GET['page'] : '');
?>
<a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
    <i class="fas fa-tachometer-alt"></i>
    <span>Dashboard</span>
</a>
<a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="nav-link <?php echo (strpos($current_page, 'warga/') === 0) ? 'active' : ''; ?>">
    <i class="fas fa-users"></i>
    <span>Data Warga</span>
</a>
<a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="nav-link <?php echo (strpos($current_page, 'jenis_sampah/') === 0) ? 'active' : ''; ?>">
    <i class="fas fa-dumpster"></i>
    <span>Jenis Sampah</span>
</a>
<div class="pt-3 mt-3 border-t border-slate-800">
    <p class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Transaksi</p>
    <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/setor" class="nav-link <?php echo ($current_page == 'transaksi/setor') ? 'active' : ''; ?>">
        <i class="fas fa-arrow-down-wide-short"></i>
        <span>Setor Sampah</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/tarik_saldo" class="nav-link <?php echo ($current_page == 'transaksi/tarik_saldo') ? 'active' : ''; ?>">
        <i class="fas fa-money-bill-wave"></i>
        <span>Tarik Saldo</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" class="nav-link <?php echo ($current_page == 'transaksi/riwayat') ? 'active' : ''; ?>">
        <i class="fas fa-history"></i>
        <span>Riwayat Transaksi</span>
    </a>
</div>
<div class="pt-3 mt-3 border-t border-slate-800">
    <p class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Laporan</p>
    <a href="<?php echo BASE_URL; ?>index.php?page=laporan/harian" class="nav-link <?php echo ($current_page == 'laporan/harian') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-day"></i>
        <span>Laporan Harian</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=laporan/bulanan" class="nav-link <?php echo ($current_page == 'laporan/bulanan') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt"></i>
        <span>Laporan Bulanan</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=laporan/rekap_warga" class="nav-link <?php echo ($current_page == 'laporan/rekap_warga') ? 'active' : ''; ?>">
        <i class="fas fa-file-contract"></i>
        <span>Rekap Warga</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=laporan/export_pdf" class="nav-link <?php echo ($current_page == 'laporan/export_pdf') ? 'active' : ''; ?>">
        <i class="fas fa-file-pdf"></i>
        <span>Export PDF</span>
    </a>
</div>
<div class="pt-3 mt-3 border-t border-slate-800">
    <p class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Pengaturan</p>
    <a href="<?php echo BASE_URL; ?>index.php?page=pengelola/petugas" class="nav-link <?php echo (strpos($current_page, 'pengelola/') === 0) ? 'active' : ''; ?>">
        <i class="fas fa-user-tie"></i>
        <span>Kelola Petugas</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=backup/index" class="nav-link <?php echo ($current_page == 'backup/index') ? 'active' : ''; ?>">
        <i class="fas fa-database"></i>
        <span>Backup Database</span>
    </a>
    <a href="<?php echo BASE_URL; ?>index.php?page=admin/settings" class="nav-link <?php echo ($current_page == 'admin/settings') ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i>
        <span>Pengaturan</span>
    </a>
</div>
