<?php
  date_default_timezone_set('Asia/Jakarta');
check_user_level(['admin', 'petugas']);

$query_warga = "SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_warga = mysqli_query($koneksi, $query_warga);

$query_jenis_sampah = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg FROM jenis_sampah ORDER BY nama_sampah ASC";
$result_jenis_sampah = mysqli_query($koneksi, $query_jenis_sampah);
$jenis_sampah_data = [];
while($row = mysqli_fetch_assoc($result_jenis_sampah)) {
    $jenis_sampah_data[] = $row;
}
mysqli_data_seek($result_jenis_sampah, 0);
?>
<div class="max-w-5xl mx-auto" x-data="transaksiSetorForm()">
    <div class="page-header">
        <h1>Input Setoran Sampah</h1>
        <p>Pastikan data warga dan detail sampah diisi lengkap untuk pencatatan cepat</p>
    </div>

    <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_setor" method="POST" @submit.prevent="submitForm" class="space-y-6">
        <?php echo csrf_field(); ?>
        <div class="card p-5 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="id_warga" class="form-label">Pilih Warga <span class="text-red-500">*</span></label>
                    <select name="id_warga" id="id_warga" required x-model="formData.id_warga" class="form-input">
                        <option value="">-- Pilih Warga --</option>
                        <?php while($warga = mysqli_fetch_assoc($result_warga)): ?>
                        <option value="<?php echo $warga['id_pengguna']; ?>">
                            <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Nama warga ditampilkan beserta username.</p>
                </div>
                <div>
                    <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi" required
                           value="<?php echo date('Y-m-d\TH:i'); ?>" class="form-input" />
                    <p class="text-xs text-slate-500 mt-1">Waktu otomatis zona Jakarta, bisa disesuaikan.</p>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Detail setoran</p>
                    <h2 class="text-lg font-bold font-['Poppins'] text-slate-800">Sampah yang disetor</h2>
                </div>
                <button type="button" @click="addItem()" class="btn btn-sm btn-outline">
                    <i class="fas fa-plus"></i> Tambah Item
                </button>
            </div>

            <div id="detail-sampah-container" class="space-y-3">
                <template x-for="(item, index) in formData.items" :key="index">
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-800">Item <span x-text="index + 1"></span></p>
                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-600 text-sm font-semibold inline-flex items-center gap-1">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </div>
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-5">
                                <label class="form-label">Jenis Sampah</label>
                                <select :name="'items[' + index + '][id_jenis_sampah]'" x-model="item.id_jenis_sampah" @change="updateHarga(index, $event.target.value)" required class="form-input">
                                    <option value="">-- Pilih Sampah --</option>
                                    <?php foreach($jenis_sampah_data as $js): ?>
                                    <option value="<?php echo $js['id_jenis_sampah']; ?>" data-harga="<?php echo $js['harga_per_kg']; ?>">
                                        <?php echo htmlspecialchars($js['nama_sampah']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-6 sm:col-span-2">
                                <label class="form-label">Berat (Kg)</label>
                                <input type="number" :name="'items[' + index + '][berat_kg]'" x-model.number="item.berat_kg" @input="hitungSubtotal(index)" step="0.01" min="0.01" required class="form-input" />
                            </div>
                            <div class="col-span-6 sm:col-span-2">
                                <label class="form-label">Harga/Kg</label>
                                <input type="number" :name="'items[' + index + '][harga_saat_setor]'" x-model.number="item.harga_saat_setor" readonly class="form-input bg-slate-50" />
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" :value="formatRupiah(item.subtotal_nilai)" readonly class="form-input bg-emerald-50 text-emerald-700 font-semibold text-right" />
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-5">
                <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" rows="2" x-model="formData.keterangan" class="form-input" placeholder="Contoh: Setoran rutin bulanan"></textarea>
            </div>

            <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-slate-200 pt-4">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="fas fa-wallet text-emerald-500"></i>
                    <span>Total nilai otomatis terakumulasi dari setiap item.</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-sm text-slate-500">Total Setoran</span>
                    <span class="text-2xl font-bold font-['Poppins'] text-emerald-600" x-text="formatRupiah(totalNilaiKeseluruhan)">Rp 0</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="btn btn-outline">Batal</a>
            <button type="submit" name="proses_setor" :disabled="loading" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan Setoran'"></span>
            </button>
        </div>
    </form>
</div>

<script>
    const masterJenisSampah = <?php echo json_encode($jenis_sampah_data); ?>;
    function transaksiSetorForm() {
        return {
            formData: { id_warga: '', tanggal_transaksi: new Date().toISOString().slice(0, 16), items: [], keterangan: '' },
            init() { this.addItem(); },
            addItem() { this.formData.items.push({ id_jenis_sampah: '', berat_kg: 0, harga_saat_setor: 0, subtotal_nilai: 0 }); },
            removeItem(index) { this.formData.items.splice(index, 1); },
            updateHarga(itemIndex, idJenisSampah) {
                const s = masterJenisSampah.find(js => js.id_jenis_sampah == idJenisSampah);
                this.formData.items[itemIndex].harga_saat_setor = s ? parseFloat(s.harga_per_kg) : 0;
                this.hitungSubtotal(itemIndex);
            },
            hitungSubtotal(itemIndex) {
                const item = this.formData.items[itemIndex];
                item.subtotal_nilai = parseFloat(item.berat_kg) * parseFloat(item.harga_saat_setor);
            },
            get totalNilaiKeseluruhan() { return this.formData.items.reduce((t, i) => t + i.subtotal_nilai, 0); },
            formatRupiah(angka) { return isNaN(angka) ? "Rp 0" : "Rp " + parseFloat(angka).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); },
            submitForm(event) {
                if (!this.formData.id_warga) { alert('Harap pilih warga terlebih dahulu.'); event.preventDefault(); return false; }
                if (this.formData.items.length === 0) { alert('Harap tambahkan minimal satu item.'); event.preventDefault(); return false; }
                for (let item of this.formData.items) { if (!item.id_jenis_sampah || item.berat_kg <= 0) { alert('Pastikan semua detail sampah terisi.'); event.preventDefault(); return false; } }
                this.loading = true; event.target.submit();
            },
            loading: false
        }
    }
</script>
