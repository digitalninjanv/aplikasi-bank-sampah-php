<?php
// modules/laporan/export_handler.php
// Versi ini dirancang untuk menjadi sangat robust terhadap error output.

// LANGKAH 0: Bersihkan semua output buffer yang MUNGKIN sudah dimulai oleh index.php atau config.php
// Ini adalah langkah kunci untuk membersihkan output liar.
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Mulai buffer baru khusus untuk file ini.
ob_start();

// LANGKAH 1: Muat dependensi yang diperlukan
// Kita muat ulang config/database.php untuk memastikan semua fungsi dan koneksi tersedia.
// require_once akan mencegah pemuatan ganda jika sudah dimuat oleh index.php.
require_once __DIR__ . '/../../config/database.php';

// Path ke autoloader PhpSpreadsheet. Pastikan path ini 100% benar.
$phpSpreadsheetAutoloadPath = __DIR__ . '/../../libs/vendor/autoload.php';

// LANGKAH 2: Validasi Prasyarat (Login & Library)
try {
    // Cek login dan hak akses. Jika gagal, fungsi redirect akan dipanggil.
    // Fungsi redirect yang sudah aman akan menangani jika header sudah terkirim (meskipun seharusnya belum).
    check_user_level(['admin', 'petugas']);

    if (!file_exists($phpSpreadsheetAutoloadPath)) {
        throw new Exception("File autoloader PhpSpreadsheet tidak ditemukan. Periksa path instalasi: " . $phpSpreadsheetAutoloadPath);
    }
    require_once $phpSpreadsheetAutoloadPath;

    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new Exception("Gagal memuat class PhpSpreadsheet. Library mungkin korup atau path autoloader salah.");
    }
} catch (Exception $e) {
    // Jika ada error pada tahap validasi, bersihkan buffer dan tampilkan error.
    ob_end_clean();
    error_log("Export Prerequisite Error: " . $e->getMessage());
    die("Export Gagal: " . htmlspecialchars($e->getMessage()));
}

// Jika semua validasi lolos, lanjutkan ke pembuatan file Excel.

// LANGKAH 3: Proses pembuatan file Excel di dalam try-finally
$old_error_reporting = error_reporting();
$old_display_errors = ini_get('display_errors');

try {
    // Matikan error reporting sementara untuk mencegah notice/warning merusak output biner.
    error_reporting(0);
    ini_set('display_errors', '0');

    // Impor class yang dibutuhkan
    // Ini lebih aman dilakukan setelah require_once berhasil.
    $Spreadsheet = \PhpOffice\PhpSpreadsheet\Spreadsheet::class;
    $Xlsx = \PhpOffice\PhpSpreadsheet\Writer\Xlsx::class;
    $Alignment = \PhpOffice\PhpSpreadsheet\Style\Alignment::class;
    $Border = \PhpOffice\PhpSpreadsheet\Style\Border::class;
    $Fill = \PhpOffice\PhpSpreadsheet\Style\Fill::class;
    $DataType = \PhpOffice\PhpSpreadsheet\Cell\DataType::class;

    $spreadsheet = new $Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Ambil parameter laporan
    $report_type = isset($_GET['report_type']) ? sanitize_input($_GET['report_type']) : '';
    
    $filename = 'Laporan_Default.xlsx';

    // Router untuk menentukan jenis laporan yang akan dibuat
    switch ($report_type) {
        case 'harian':
            $tanggal_export = isset($_GET['tanggal']) ? sanitize_input($_GET['tanggal']) : date('Y-m-d');
            $sheet->setTitle('Laporan Harian');
            $spreadsheet->getProperties()->setCreator("Bank Sampah Digital")->setTitle("Laporan Harian - " . $tanggal_export);
            buildLaporanHarianSheet($sheet, $koneksi, $tanggal_export, $Alignment, $Border, $Fill, $DataType); // Pass class-name
            $filename = "Laporan_Harian_" . str_replace('-', '', $tanggal_export) . ".xlsx";
            break;
        
        default:
            throw new Exception("Jenis laporan tidak valid untuk diekspor.");
    }

    // Bersihkan buffer sekali lagi SEBELUM mengirim header apa pun. Ini adalah jaring pengaman terakhir.
    ob_end_clean();

    // Set header HTTP untuk download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Tulis file ke output stream
    $writer = new $Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    // Jika terjadi exception, log error dan hentikan
    error_log("Ekspor XLSX Exception: " . $e->getMessage());
    // Coba bersihkan buffer lagi jika ada output dari error
    if (ob_get_length() > 0) ob_end_clean();
    // Kirim pesan error yang aman
    if (!headers_sent()) {
        header("HTTP/1.1 500 Internal Server Error");
        header('Content-Type: text/plain; charset=utf-8');
    }
    die("Terjadi kesalahan internal saat membuat file Excel. Silakan cek log server. Pesan: " . htmlspecialchars($e->getMessage()));
} finally {
    // Pastikan error reporting selalu dikembalikan ke pengaturan awal
    error_reporting($old_error_reporting);
    ini_set('display_errors', $old_display_errors);
}

// Hentikan skrip setelah selesai.
exit();

// --- FUNGSI-FUNGSI PEMBANTU UNTUK MEMBUAT SHEET ---
// Fungsi ini harus didefinisikan SEBELUM dipanggil, atau diletakkan di luar blok eksekusi utama.
function buildLaporanHarianSheet($sheet, $koneksi, $tanggal_export, $Alignment, $Border, $Fill, $DataType) {
    // Styling
    $headerStyle = ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']], 'alignment' => ['horizontal' => $Alignment::HORIZONTAL_CENTER, 'vertical' => $Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => $Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]];
    $titleStyle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => $Alignment::HORIZONTAL_CENTER]];
    $subTitleStyle = ['font' => ['bold' => true, 'size' => 11]];
    $allBorders = ['borders' => ['allBorders' => ['borderStyle' => $Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]];
    
    $currentRow = 1;
    // Judul
    $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "LAPORAN HARIAN BANK SAMPAH");
    $sheet->getStyle("A{$currentRow}")->applyFromArray($titleStyle);
    $currentRow++;
    $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "Tanggal: " . format_tanggal_indonesia($tanggal_export, false));
    $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal($Alignment::HORIZONTAL_CENTER);
    $currentRow += 2;

    // Data Setoran
    $query_setoran = "SELECT t.id_transaksi, p_warga.nama_lengkap AS nama_warga, p_petugas.nama_lengkap AS nama_petugas, t.tanggal_transaksi, t.total_nilai, GROUP_CONCAT(DISTINCT CONCAT(js.nama_sampah, ': ', ds.berat_kg, ' ', js.satuan, ' @', ds.harga_saat_setor, ' = ', ds.subtotal_nilai) SEPARATOR '\n') AS rincian_item, t.keterangan AS keterangan_transaksi FROM transaksi t JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna LEFT JOIN detail_setoran ds ON t.id_transaksi = ds.id_transaksi_setor LEFT JOIN jenis_sampah js ON ds.id_jenis_sampah = js.id_jenis_sampah WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'setor' GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi ASC";
    $stmt_setoran = mysqli_prepare($koneksi, $query_setoran); mysqli_stmt_bind_param($stmt_setoran, "s", $tanggal_export); mysqli_stmt_execute($stmt_setoran); $result_setoran = mysqli_stmt_get_result($stmt_setoran);
    $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "--- DETAIL SETORAN ---")->getStyle("A{$currentRow}")->applyFromArray($subTitleStyle);
    $currentRow+=2;
    $headerSetoran = ['ID Transaksi', 'Waktu', 'Nama Warga', 'Nama Petugas', 'Rincian Item Sampah', 'Keterangan Tambahan', 'Total Nilai Setoran (Rp)'];
    $sheet->fromArray($headerSetoran, NULL, "A{$currentRow}")->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($headerStyle);
    $currentRow++; $startRowData = $currentRow;
    if ($result_setoran) { while ($row = mysqli_fetch_assoc($result_setoran)) {
        $sheet->fromArray([$row['id_transaksi'], date('H:i:s', strtotime($row['tanggal_transaksi'])), $row['nama_warga'], $row['nama_petugas'], $row['rincian_item'], $row['keterangan_transaksi']], NULL, "A{$currentRow}");
        $sheet->setCellValueExplicit("G{$currentRow}", $row['total_nilai'], $DataType::TYPE_NUMERIC);
        $sheet->getStyle("E{$currentRow}")->getAlignment()->setWrapText(true); $sheet->getStyle("G{$currentRow}")->getNumberFormat()->setFormatCode('#,##0'); $currentRow++;
    } }
    if ($currentRow > $startRowData) { $sheet->getStyle("A{$startRowData}:G" . ($currentRow - 1))->applyFromArray($allBorders); }
    mysqli_stmt_close($stmt_setoran); $currentRow += 2;

    // Data Penarikan
    $query_penarikan = "SELECT t.id_transaksi, p_warga.nama_lengkap AS nama_warga, p_petugas.nama_lengkap AS nama_petugas, t.tanggal_transaksi, t.total_nilai, t.keterangan AS keterangan_transaksi FROM transaksi t JOIN pengguna p_warga ON t.id_warga = p_warga.id_pengguna JOIN pengguna p_petugas ON t.id_petugas_pencatat = p_petugas.id_pengguna WHERE DATE(t.tanggal_transaksi) = ? AND t.tipe_transaksi = 'tarik_saldo' ORDER BY t.tanggal_transaksi ASC";
    $stmt_penarikan = mysqli_prepare($koneksi, $query_penarikan); mysqli_stmt_bind_param($stmt_penarikan, "s", $tanggal_export); mysqli_stmt_execute($stmt_penarikan); $result_penarikan = mysqli_stmt_get_result($stmt_penarikan);
    $sheet->mergeCells("A{$currentRow}:F{$currentRow}")->setCellValue("A{$currentRow}", "--- DETAIL PENARIKAN SALDO ---")->getStyle("A{$currentRow}")->applyFromArray($subTitleStyle);
    $currentRow+=2;
    $headerPenarikan = ['ID Transaksi', 'Waktu', 'Nama Warga', 'Nama Petugas', 'Keterangan Penarikan', 'Jumlah Ditarik (Rp)'];
    $sheet->fromArray($headerPenarikan, NULL, "A{$currentRow}")->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray($headerStyle);
    $currentRow++; $startRowData = $currentRow;
    if ($result_penarikan) { while ($row = mysqli_fetch_assoc($result_penarikan)) {
        $sheet->fromArray([$row['id_transaksi'], date('H:i:s', strtotime($row['tanggal_transaksi'])), $row['nama_warga'], $row['nama_petugas'], $row['keterangan_transaksi']], NULL, "A{$currentRow}");
        $sheet->setCellValueExplicit("F{$currentRow}", $row['total_nilai'], $DataType::TYPE_NUMERIC)->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0'); $currentRow++;
    } }
    if ($currentRow > $startRowData) { $sheet->getStyle("A{$startRowData}:F" . ($currentRow - 1))->applyFromArray($allBorders); }
    mysqli_stmt_close($stmt_penarikan); $currentRow += 2;

    // Summary
    $total_pemasukan = 0; $total_pengeluaran = 0;
    $q_sum_setor = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'setor'";
    $s_sum_setor = mysqli_prepare($koneksi, $q_sum_setor); mysqli_stmt_bind_param($s_sum_setor, "s", $tanggal_export); mysqli_stmt_execute($s_sum_setor); $r_sum_setor = mysqli_stmt_get_result($s_sum_setor); if($d = mysqli_fetch_assoc($r_sum_setor)) $total_pemasukan = $d['total'] ?: 0; mysqli_stmt_close($s_sum_setor);
    $q_sum_tarik = "SELECT SUM(total_nilai) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND tipe_transaksi = 'tarik_saldo'";
    $s_sum_tarik = mysqli_prepare($koneksi, $q_sum_tarik); mysqli_stmt_bind_param($s_sum_tarik, "s", $tanggal_export); mysqli_stmt_execute($s_sum_tarik); $r_sum_tarik = mysqli_stmt_get_result($s_sum_tarik); if($d = mysqli_fetch_assoc($r_sum_tarik)) $total_pengeluaran = $d['total'] ?: 0; mysqli_stmt_close($s_sum_tarik);
    $sheet->mergeCells("A{$currentRow}:B{$currentRow}")->setCellValue("A{$currentRow}", "--- RINGKASAN HARIAN ---")->getStyle("A{$currentRow}")->applyFromArray($subTitleStyle);
    $currentRow += 2;
    $headerSummary = ["Deskripsi", "Jumlah (Rp)"];
    $sheet->fromArray($headerSummary, NULL, "A{$currentRow}")->getStyle("A{$currentRow}:B{$currentRow}")->applyFromArray($headerStyle);
    $currentRow++; $startRowData = $currentRow;
    $dataSummary = [["Total Pemasukan", $total_pemasukan], ["Total Pengeluaran", $total_pengeluaran], ["Selisih", $total_pemasukan - $total_pengeluaran]];
    $sheet->fromArray($dataSummary, NULL, "A{$startRowData}");
    $sheet->getStyle("B{$startRowData}:B" . ($startRowData+2))->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle("A{$startRowData}:B" . ($startRowData+2))->applyFromArray($allBorders);

    // Auto size columns
    foreach (range('A', 'G') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
}
?>
