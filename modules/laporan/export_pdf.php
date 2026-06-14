<?php
// modules/laporan/export_pdf.php - Export laporan PDF (simple HTML-to-PDF)
check_user_level(['admin', 'petugas']);
$report_type = $_GET['report_type'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('Y-m');

// Gunakan Dompdf jika tersedia di vendor
$dompdf_available = file_exists(__DIR__ . '/../../libs/vendor/autoload.php');
if ($dompdf_available) {
    try {
        require_once __DIR__ . '/../../libs/vendor/autoload.php';
        // Check if Dompdf is installed
        $dompdf_available = class_exists('Dompdf\Dompdf');
    } catch (Throwable $e) {
        $dompdf_available = false;
    }
}

if ($dompdf_available) {
    ob_start();
    require_once __DIR__ . '/../../config/database.php';
    // Regenerate content for PDF (simple table-based)
    ?>
    <html><head><meta charset="utf-8"><style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #999; padding: 5px; text-align: left; }
        th { background: #eee; font-weight: bold; }
        h1 { font-size: 16px; text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
    </style></head><body>
    <?php if ($report_type === 'harian'): ?>
        <h1>Laporan Harian - <?php echo format_tanggal_indonesia($tanggal, false); ?></h1>
        <?php
        $query_setoran = "SELECT p.nama_lengkap AS warga, t.total_nilai, t.tanggal_transaksi, GROUP_CONCAT(CONCAT(ds.berat_kg,'kg ',js.nama_sampah) SEPARATOR ', ') AS detail
            FROM transaksi t JOIN pengguna p ON t.id_warga = p.id_pengguna
            LEFT JOIN detail_setoran ds ON t.id_transaksi = ds.id_transaksi_setor
            LEFT JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah
            WHERE t.tipe_transaksi='setor' AND DATE(t.tanggal_transaksi)=?
            GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi ASC";
        $stmt = mysqli_prepare($koneksi, $query_setoran);
        mysqli_stmt_bind_param($stmt, "s", $tanggal);
        mysqli_stmt_execute($stmt);
        $setoran = mysqli_stmt_get_result($stmt);

        $query_tarik = "SELECT p.nama_lengkap AS warga, t.total_nilai, t.tanggal_transaksi, t.keterangan
            FROM transaksi t JOIN pengguna p ON t.id_warga = p.id_pengguna
            WHERE t.tipe_transaksi='tarik_saldo' AND DATE(t.tanggal_transaksi)=?
            ORDER BY t.tanggal_transaksi ASC";
        $stmt2 = mysqli_prepare($koneksi, $query_tarik);
        mysqli_stmt_bind_param($stmt2, "s", $tanggal);
        mysqli_stmt_execute($stmt2);
        $tarik = mysqli_stmt_get_result($stmt2);
        ?>
        <h2>Setoran</h2>
        <table><thead><tr><th>Warga</th><th>Detail</th><th>Nilai</th></tr></thead>
        <tbody><?php $ts=0; while($r=mysqli_fetch_assoc($setoran)): $ts+=$r['total_nilai']; ?>
            <tr><td><?php echo htmlspecialchars($r['warga']); ?></td><td><?php echo htmlspecialchars($r['detail']); ?></td><td class="text-right text-green"><?php echo format_rupiah($r['total_nilai']); ?></td></tr>
        <?php endwhile; ?></tbody>
        <tfoot><tr><th colspan="2">Total Setoran</th><th class="text-right text-green"><?php echo format_rupiah($ts); ?></th></tr></tfoot>
        </table>
        <h2>Penarikan</h2>
        <table><thead><tr><th>Warga</th><th>Keterangan</th><th>Nilai</th></tr></thead>
        <tbody><?php $tt=0; while($r=mysqli_fetch_assoc($tarik)): $tt+=$r['total_nilai']; ?>
            <tr><td><?php echo htmlspecialchars($r['warga']); ?></td><td><?php echo htmlspecialchars($r['keterangan'] ?: '-'); ?></td><td class="text-right text-red"><?php echo format_rupiah($r['total_nilai']); ?></td></tr>
        <?php endwhile; ?></tbody>
        <tfoot><tr><th colspan="2">Total Penarikan</th><th class="text-right text-red"><?php echo format_rupiah($tt); ?></th></tr></tfoot>
        </table>
        <p>Selisih: <?php echo format_rupiah($ts - $tt); ?></p>
    <?php elseif ($report_type === 'bulanan'): ?>
        <h1>Laporan Bulanan - <?php echo date('F Y', strtotime($bulan . '-01')); ?></h1>
        <?php
        $q = "SELECT DATE(t.tanggal_transaksi) AS tgl,
            SUM(CASE WHEN t.tipe_transaksi='setor' THEN t.total_nilai ELSE 0 END) AS pemasukan,
            SUM(CASE WHEN t.tipe_transaksi='tarik_saldo' THEN t.total_nilai ELSE 0 END) AS pengeluaran
            FROM transaksi t WHERE DATE_FORMAT(t.tanggal_transaksi,'%Y-%m')=?
            GROUP BY DATE(t.tanggal_transaksi) ORDER BY tgl ASC";
        $stmt = mysqli_prepare($koneksi, $q);
        mysqli_stmt_bind_param($stmt, "s", $bulan);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_stmt_get_result($stmt);
        ?>
        <table><thead><tr><th>Tanggal</th><th>Pemasukan</th><th>Pengeluaran</th><th>Selisih</th></tr></thead>
        <tbody><?php $gp=0;$gm=0; while($r=mysqli_fetch_assoc($rows)): $gp+=$r['pemasukan'];$gm+=$r['pengeluaran']; ?>
            <tr><td><?php echo format_tanggal_indonesia($r['tgl'], false); ?></td>
                <td class="text-right text-green"><?php echo format_rupiah($r['pemasukan']); ?></td>
                <td class="text-right text-red"><?php echo format_rupiah($r['pengeluaran']); ?></td>
                <td class="text-right"><?php echo format_rupiah($r['pemasukan'] - $r['pengeluaran']); ?></td></tr>
        <?php endwhile; ?></tbody>
        <tfoot><tr><th>Grand Total</th><th class="text-right text-green"><?php echo format_rupiah($gp); ?></th>
            <th class="text-right text-red"><?php echo format_rupiah($gm); ?></th>
            <th class="text-right"><?php echo format_rupiah($gp - $gm); ?></th></tr></tfoot>
        </table>
    <?php endif; ?>
    </body></html>
    <?php
    $html = ob_get_clean();
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("laporan-$report_type-$tanggal.pdf", ['Attachment' => true]);
    exit;
} else {
    // Fallback: install Dompdf
    $_SESSION['info_message'] = "Fitur PDF membutuhkan Dompdf. Jalankan: composer require dompdf/dompdf di folder libs/";
    redirect(BASE_URL . 'index.php?page=laporan/' . $report_type . ($report_type === 'harian' ? "?tanggal=$tanggal" : "?bulan=$bulan"));
}
