<?php
// modules/transaksi/tarik_saldo.php
check_user_level(['admin', 'petugas']);

// Ambil daftar warga untuk dropdown
$query_warga = "SELECT id_pengguna, nama_lengkap, username, saldo FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_warga = mysqli_query($koneksi, $query_warga);
$warga_data_options = [];
if ($result_warga) {
    while($w = mysqli_fetch_assoc($result_warga)){
        $warga_data_options[] = $w;
    }
}
?>

<div class="container mx-auto px-4 py-8" x-data="tarikSaldoForm()">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Input Penarikan Saldo Warga</h1>

    <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_tarik" method="POST" @submit.prevent="validateAndSubmit">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-lg mx-auto">
            <div class="space-y-6">
                <div>
                    <label for="id_warga" class="block text-sm font-medium text-gray-700 mb-1">Pilih Warga <span class="text-red-500">*</span></label>
                    <select name="id_warga" id="id_warga" required x-model="selectedWargaId" @change="updateSaldoWarga($event.target.options[$event.target.selectedIndex].dataset.saldo)"
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        <option value="">-- Pilih Warga --</option>
                        <?php foreach($warga_data_options as $warga): ?>
                        <option value="<?php echo $warga['id_pengguna']; ?>" data-saldo="<?php echo $warga['saldo']; ?>">
                            <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div x-show="selectedWargaId && currentSaldoWarga !== null">
                    <p class="text-sm text-gray-600">Saldo saat ini: <strong class="text-green-600" x-text="formatRupiah(currentSaldoWarga)"></strong></p>
                </div>

                <div>
                    <label for="jumlah_penarikan" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Penarikan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_penarikan" id="jumlah_penarikan" required step="100" min="1000" x-model.number="jumlahPenarikan"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="tanggal_transaksi_tarik" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi_tarik" required
                           value="<?php echo date('Y-m-d\TH:i'); ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>

                <div>
                    <label for="keterangan_tarik" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan_tarik" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" name="proses_tarik_saldo"
                        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <i class="fas fa-money-bill-wave mr-2"></i> Proses Penarikan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function tarikSaldoForm() {
        return {
            selectedWargaId: '',
            currentSaldoWarga: null,
            jumlahPenarikan: 0,
            wargaList: <?php echo json_encode($warga_data_options); ?>, // Data warga untuk JS

            updateSaldoWarga(saldo) {
                this.currentSaldoWarga = parseFloat(saldo) || 0;
            },
            formatRupiah(angka) {
                if (isNaN(angka) || angka === null) return "Rp 0";
                return "Rp " + parseFloat(angka).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            },
            validateAndSubmit(event) {
                if (!this.selectedWargaId) {
                    alert('Harap pilih warga terlebih dahulu.');
                    event.preventDefault();
                    return false;
                }
                if (this.jumlahPenarikan <= 0) {
                    alert('Jumlah penarikan harus lebih dari 0.');
                    event.preventDefault();
                    return false;
                }
                if (this.currentSaldoWarga === null || this.jumlahPenarikan > this.currentSaldoWarga) {
                    alert('Jumlah penarikan tidak boleh melebihi saldo warga saat ini.');
                    event.preventDefault();
                    return false;
                }
                // Jika validasi lolos, submit form secara native
                event.target.submit();
            }
        }
    }
</script>
