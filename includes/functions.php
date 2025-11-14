<?php
// includes/functions.php

/**
 * Fungsi untuk memformat angka menjadi format mata uang Rupiah.
 * * @param float|int $angka Angka yang akan diformat.
 * @return string Angka dalam format Rupiah (contoh: "Rp 1.250.000").
 */
function format_rupiah($angka) {
    if (!is_numeric($angka)) {
        return "Rp 0"; // Atau handle error sesuai kebutuhan
    }
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi untuk memformat tanggal ke format Indonesia.
 * Contoh: Y-m-d H:i:s -> d M Y, H:i
 * * @param string $tanggal_mysql Tanggal dalam format MySQL (Y-m-d H:i:s atau Y-m-d).
 * @param bool $dengan_waktu Apakah menyertakan waktu dalam output.
 * @return string Tanggal dalam format Indonesia.
 */
function format_tanggal_indonesia($tanggal_mysql, $dengan_waktu = true) {
    if (empty($tanggal_mysql) || $tanggal_mysql == '0000-00-00 00:00:00' || $tanggal_mysql == '0000-00-00') {
        return "-";
    }
    try {
        $date_obj = new DateTime($tanggal_mysql);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $tanggal = $date_obj->format('d');
        $bulan_index = (int)$date_obj->format('m');
        $tahun = $date_obj->format('Y');
        
        $format_akhir = $tanggal . ' ' . $bulan[$bulan_index] . ' ' . $tahun;
        
        if ($dengan_waktu) {
            $format_akhir .= ', ' . $date_obj->format('H:i');
        }
        return $format_akhir;
    } catch (Exception $e) {
        return $tanggal_mysql; // Kembalikan format asli jika ada error parsing
    }
}


// Anda bisa menambahkan fungsi-fungsi umum lainnya di sini, misalnya:
// function generate_kode_unik($prefix = 'TRX') { ... }
// function hitung_umur($tanggal_lahir) { ... }

?>
