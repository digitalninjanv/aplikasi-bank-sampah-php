<?php
check_user_level(['admin', 'petugas']);

$query_warga = "SELECT id_pengguna, nama_lengkap, username, saldo FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_warga = mysqli_query($koneksi, $query_warga);
$warga_data_options = [];
if ($result_warga) {
    while($w = mysqli_fetch_assoc($result_warga)){
        $warga_data_options[] = $w;
    }
}
?>
<div class="max-w-3xl mx-auto" x-data="tarikSaldoForm()">
    <div class="page-header">
        <h1>Input Penarikan Saldo</h1>
        <p>Pastikan jumlah tidak melebihi saldo dan catat keterangan singkat</p>
    </div>

    <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_tarik" method="POST" @submit.prevent="validateAndSubmit" class="space-y-6">
        <?php echo csrf_field(); ?>
        <div class="card p-5 sm:p-8">
            <div class="space-y-4">
                <div>
                    <label for="id_warga" class="form-label">Pilih Warga <span class="text-red-500">*</span></label>
                    <select name="id_warga" id="id_warga" required x-model="selectedWargaId" @change="updateSaldoWarga($event.target.options[$event.target.selectedIndex].dataset.saldo)" class="form-input">
                        <option value="">-- Pilih Warga --</option>
                        <?php foreach($warga_data_options as $warga): ?>
                        <option value="<?php echo $warga['id_pengguna']; ?>" data-saldo="<?php echo $warga['saldo']; ?>">
                            <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Urutan sesuai alfabet untuk mempercepat pencarian.</p>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3" x-show="selectedWargaId && currentSaldoWarga !== null">
                    <div class="flex items-center gap-2 text-amber-700 text-sm font-semibold">
                        <i class="fas fa-info-circle"></i> Saldo saat ini
                    </div>
                    <p class="text-2xl font-bold font-['Poppins'] text-amber-600" x-text="formatRupiah(currentSaldoWarga)"></p>
                    <p class="text-xs text-amber-700/80">Penarikan otomatis dicegah jika melebihi saldo.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah_penarikan" class="form-label">Jumlah Penarikan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_penarikan" id="jumlah_penarikan" required step="100" min="1000" x-model.number="jumlahPenarikan"
                               class="form-input" placeholder="Minimal Rp1.000" />
                        <p class="text-xs text-slate-500 mt-1">Gunakan kelipatan seribu.</p>
                    </div>
                    <div>
                        <label for="tanggal_transaksi_tarik" class="form-label">Tanggal Transaksi <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi_tarik" required
                               value="<?php echo date('Y-m-d\TH:i'); ?>" class="form-input" />
                        <p class="text-xs text-slate-500 mt-1">Dapat disesuaikan jika perlu koreksi.</p>
                    </div>
                </div>

                <div>
                    <label for="keterangan_tarik" class="form-label">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan_tarik" rows="2" class="form-input" placeholder="Contoh: Penarikan untuk kebutuhan darurat"></textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-slate-200 pt-4">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="fas fa-shield-alt text-amber-500"></i>
                    <span>Validasi otomatis mencegah saldo minus.</span>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="btn btn-outline">Batal</a>
                    <button type="submit" name="proses_tarik_saldo" :disabled="loading" class="btn btn-warning btn-lg">
                        <i class="fas fa-money-bill-wave"></i> <span x-text="loading ? 'Memproses...' : 'Proses Penarikan'"></span>
                    </button>
                </div>
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
            wargaList: <?php echo json_encode($warga_data_options); ?>,
            updateSaldoWarga(saldo) { this.currentSaldoWarga = parseFloat(saldo) || 0; },
            formatRupiah(angka) { return (isNaN(angka) || angka === null) ? "Rp 0" : "Rp " + parseFloat(angka).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); },
            validateAndSubmit(event) {
                if (!this.selectedWargaId) { alert('Harap pilih warga terlebih dahulu.'); event.preventDefault(); return false; }
                if (this.jumlahPenarikan <= 0) { alert('Jumlah penarikan harus lebih dari 0.'); event.preventDefault(); return false; }
                if (this.currentSaldoWarga === null || this.jumlahPenarikan > this.currentSaldoWarga) { alert('Jumlah penarikan tidak boleh melebihi saldo.'); event.preventDefault(); return false; }
                this.loading = true; event.target.submit();
            },
            loading: false
        }
    }
</script>
