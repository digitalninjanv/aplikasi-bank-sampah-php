<?php
$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? $_GET['page'] : '');
?>
<a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
    <i class="fas fa-home"></i>
    <span>Dashboard Saya</span>
</a>
<a href="<?php echo BASE_URL; ?>index.php?page=laporan/riwayat_warga" class="nav-link <?php echo ($current_page == 'laporan/riwayat_warga') ? 'active' : ''; ?>">
    <i class="fas fa-history"></i>
    <span>Riwayat Transaksi</span>
</a>
<a href="<?php echo BASE_URL; ?>index.php?page=profil" class="nav-link <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
    <i class="fas fa-user-cog"></i>
    <span>Profil & Saldo</span>
</a>
