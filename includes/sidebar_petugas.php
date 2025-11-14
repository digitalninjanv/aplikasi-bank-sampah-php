<?php
// includes/sidebar_petugas.php
// Pastikan $current_page sudah didefinisikan di header.php
$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? $_GET['page'] : '');
?>
<ul class="space-y-2">
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'dashboard') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo (strpos($current_page, 'warga/') === 0) ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-users w-5"></i>
            <span>Data Warga</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo (strpos($current_page, 'jenis_sampah/') === 0) ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-dumpster w-5"></i>
            <span>Jenis Sampah</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/setor" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'transaksi/setor') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-arrow-down-wide-short w-5"></i>
            <span>Setor Sampah</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/tarik_saldo" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'transaksi/tarik_saldo') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-money-bill-wave w-5"></i>
            <span>Tarik Saldo</span>
        </a>
    </li>
    <li>
        <a href="<?php echo BASE_URL; ?>index.php?page=transaksi/riwayat" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200 <?php echo ($current_page == 'transaksi/riwayat') ? 'active-nav-link' : ''; ?>">
            <i class="fas fa-history w-5"></i>
            <span>Riwayat Transaksi</span>
        </a>
    </li>
    <li x-data="{ open: <?php echo (strpos($current_page, 'laporan/') === 0) ? 'true' : 'false'; ?> }">
        <button @click="open = !open" class="w-full flex items-center justify-between space-x-3 px-4 py-3 rounded-lg hover:bg-sky-600 transition duration-200">
            <div class="flex items-center space-x-3">
                <i class="fas fa-chart-line w-5"></i>
                <span>Laporan</span>
            </div>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <ul x-show="open" x-transition class="ml-4 mt-1 space-y-1">
            <li>
                <a href="<?php echo BASE_URL; ?>index.php?page=laporan/harian" 
                   class="block px-4 py-2 rounded-md hover:bg-sky-700 <?php echo ($current_page == 'laporan/harian') ? 'active-nav-link' : ''; ?>">Laporan Harian</a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>index.php?page=laporan/bulanan" 
                   class="block px-4 py-2 rounded-md hover:bg-sky-700 <?php echo ($current_page == 'laporan/bulanan') ? 'active-nav-link' : ''; ?>">Laporan Bulanan</a>
            </li>
        </ul>
    </li>
    </ul>
