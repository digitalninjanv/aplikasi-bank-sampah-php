<?php
// includes/sidebar_warga.php
// Pastikan $current_page sudah didefinisikan di header.php
$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? $_GET['page'] : '');
?>
<ul class="space-y-2">
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'dashboard') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-home w-5"></i>
            <span>Dashboard Saya</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=laporan/riwayat_warga" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'laporan/riwayat_warga') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-history w-5"></i>
            <span>Riwayat Transaksi</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=profil" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'profil') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-user-cog w-5"></i>
            <span>Profil & Saldo</span>
        </a>
    </li>
    </ul>
