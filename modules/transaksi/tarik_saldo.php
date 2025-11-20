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
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-widest text-white/80">Transaksi</p>
                    <h1 class="text-2xl sm:text-3xl font-bold">Input Penarikan Saldo</h1>
                    <p class="mt-1 text-white/80 text-sm">Pastikan jumlah tidak melebihi saldo dan catat keterangan singkat.</p>
                </div>
                <div class="bg-white/15 backdrop-blur-sm px-4 py-3 rounded-xl border border-white/20 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-orange-600 font-semibold"><i class="fas fa-wallet"></i></span>
                    <div>
                        <p class="text-xs text-white/70">Mode</p>
                        <p class="font-semibold">Penarikan saldo</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_tarik" method="POST" @submit.prevent="validateAndSubmit" class="space-y-6">
            <div class="bg-white/70 backdrop-blur shadow-xl rounded-2xl border border-gray-100 p-5 sm:p-8">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="id_warga" class="block text-sm font-semibold text-gray-800">Pilih Warga <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="id_warga" id="id_warga" required x-model="selectedWargaId" @change="updateSaldoWarga($event.target.options[$event.target.selectedIndex].dataset.saldo)"
                                    class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                <option value="" class="text-gray-400">-- Pilih Warga --</option>
                                <?php foreach($warga_data_options as $warga): ?>
                                <option value="<?php echo $warga['id_pengguna']; ?>" data-saldo="<?php echo $warga['saldo']; ?>">
                                    <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400"><i class="fas fa-chevron-down text-xs"></i></span>
                        </div>
                        <p class="text-xs text-gray-500">Urutan nama sesuai alfabet untuk mempercepat pencarian.</p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl bg-orange-50 border border-orange-100 p-3" x-show="selectedWargaId && currentSaldoWarga !== null">
                        <div class="flex items-center gap-2 text-orange-700 text-sm font-semibold">
                            <i class="fas fa-info-circle"></i>
                            Saldo saat ini
                        </div>
                        <p class="text-2xl font-bold text-orange-600" x-text="formatRupiah(currentSaldoWarga)"></p>
                        <p class="text-xs text-orange-700/80">Penarikan otomatis dicegah jika melebihi saldo.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="jumlah_penarikan" class="block text-sm font-semibold text-gray-800">Jumlah Penarikan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_penarikan" id="jumlah_penarikan" required step="100" min="1000" x-model.number="jumlahPenarikan"
                                   class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                                   placeholder="Minimal Rp1.000" />
                            <p class="text-xs text-gray-500">Gunakan kelipatan seribu untuk memudahkan pencairan.</p>
                        </div>
                        <div class="space-y-2">
                            <label for="tanggal_transaksi_tarik" class="block text-sm font-semibold text-gray-800">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi_tarik" required
                                   value="<?php echo date('Y-m-d\TH:i'); ?>"
                                   class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200" />
                            <p class="text-xs text-gray-500">Tanggal dan waktu dapat disesuaikan jika perlu koreksi.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="keterangan_tarik" class="block text-sm font-semibold text-gray-800">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan_tarik" rows="3" class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200" placeholder="Contoh: Penarikan untuk kebutuhan darurat"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-50 text-orange-600"><i class="fas fa-shield-alt"></i></span>
                        <span>Validasi otomatis mencegah saldo minus.</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                            Batal
                        </a>
                        <button type="submit" name="proses_tarik_saldo"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 hover:bg-orange-600 transition focus:ring-2 focus:ring-offset-2 focus:ring-orange-300">
                            <i class="fas fa-money-bill-wave"></i> Proses Penarikan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
