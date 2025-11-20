<?php
  date_default_timezone_set('Asia/Jakarta');
// modules/transaksi/setor.php
check_user_level(['admin', 'petugas']);

// Ambil daftar warga untuk dropdown
$query_warga = "SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE level = 'warga' ORDER BY nama_lengkap ASC";
$result_warga = mysqli_query($koneksi, $query_warga);

// Ambil daftar jenis sampah untuk dropdown
$query_jenis_sampah = "SELECT id_jenis_sampah, nama_sampah, harga_per_kg FROM jenis_sampah ORDER BY nama_sampah ASC";
$result_jenis_sampah = mysqli_query($koneksi, $query_jenis_sampah);
$jenis_sampah_data = [];
while($row = mysqli_fetch_assoc($result_jenis_sampah)) {
    $jenis_sampah_data[] = $row;
}
// Reset pointer result set jika perlu digunakan lagi, atau fetch semua data ke array seperti di atas.
mysqli_data_seek($result_jenis_sampah, 0); 

?>
<div class="container mx-auto px-4 py-8" x-data="transaksiSetorForm()">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-gradient-to-r from-sky-600 to-emerald-500 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-widest text-white/80">Transaksi</p>
                    <h1 class="text-2xl sm:text-3xl font-bold">Input Setoran Sampah</h1>
                    <p class="mt-1 text-white/80 text-sm">Pastikan data warga dan detail sampah diisi lengkap untuk pencatatan cepat.</p>
                </div>
                <div class="flex items-center gap-3 bg-white/15 backdrop-blur-sm px-4 py-3 rounded-xl border border-white/20">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-sky-600 font-semibold">&#43;</span>
                    <div>
                        <p class="text-xs text-white/70">Status</p>
                        <p class="font-semibold">Form Siap Digunakan</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_setor" method="POST" @submit.prevent="submitForm" class="space-y-6">
            <div class="bg-white/70 backdrop-blur shadow-xl rounded-2xl border border-gray-100 p-5 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
                    <div class="space-y-2">
                        <label for="id_warga" class="block text-sm font-semibold text-gray-800">Pilih Warga <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="id_warga" id="id_warga" required x-model="formData.id_warga"
                                    class="peer block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                                <option value="" class="text-gray-400">-- Pilih Warga --</option>
                                <?php while($warga = mysqli_fetch_assoc($result_warga)): ?>
                                <option value="<?php echo $warga['id_pengguna']; ?>">
                                    <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400"><i class="fas fa-chevron-down text-xs"></i></span>
                        </div>
                        <p class="text-xs text-gray-500">Nama warga ditampilkan beserta username untuk meminimalkan salah pilih.</p>
                    </div>
                    <div class="space-y-2">
                        <label for="tanggal_transaksi" class="block text-sm font-semibold text-gray-800">Tanggal Transaksi <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi" required
                               value="<?php echo date('Y-m-d\TH:i'); ?>"
                               class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                        <p class="text-xs text-gray-500">Waktu otomatis terisi sesuai zona Jakarta dan bisa disesuaikan.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Detail setoran</p>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-800">Sampah yang disetor</h2>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-2 rounded-lg bg-sky-50 text-sky-600 px-3 py-2 text-sm font-semibold border border-sky-100 hover:bg-sky-100 transition">
                        <i class="fas fa-plus"></i>
                        Tambah Item
                    </button>
                </div>

                <div id="detail-sampah-container" class="space-y-4">
                    <template x-for="(item, index) in formData.items" :key="index">
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4 sm:p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">Item <span x-text="index + 1"></span></p>
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-600 text-sm font-semibold inline-flex items-center gap-1">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </div>
                            <div class="grid grid-cols-12 gap-3 sm:gap-4">
                                <div class="col-span-12 sm:col-span-5 space-y-1">
                                    <label class="text-xs font-medium text-gray-600">Jenis Sampah</label>
                                    <div class="relative">
                                        <select :name="'items[' + index + '][id_jenis_sampah]'" x-model="item.id_jenis_sampah" @change="updateHarga(index, $event.target.value)" required
                                                class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                                            <option value="">-- Pilih Sampah --</option>
                                            <?php foreach($jenis_sampah_data as $js): ?>
                                            <option value="<?php echo $js['id_jenis_sampah']; ?>" data-harga="<?php echo $js['harga_per_kg']; ?>">
                                                <?php echo htmlspecialchars($js['nama_sampah']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400"><i class="fas fa-chevron-down text-xs"></i></span>
                                    </div>
                                </div>
                                <div class="col-span-6 sm:col-span-2 space-y-1">
                                    <label class="text-xs font-medium text-gray-600">Berat (Kg)</label>
                                    <input type="number" :name="'items[' + index + '][berat_kg]'" x-model.number="item.berat_kg" @input="hitungSubtotal(index)" step="0.01" min="0.01" required
                                           class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                                </div>
                                <div class="col-span-6 sm:col-span-2 space-y-1">
                                    <label class="text-xs font-medium text-gray-600">Harga/Kg</label>
                                    <input type="number" :name="'items[' + index + '][harga_saat_setor]'" x-model.number="item.harga_saat_setor" readonly
                                           class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2.5 text-sm text-gray-700 shadow-sm" />
                                </div>
                                <div class="col-span-12 sm:col-span-3 space-y-1">
                                    <label class="text-xs font-medium text-gray-600">Subtotal</label>
                                    <input type="text" :value="formatRupiah(item.subtotal_nilai)" readonly
                                           class="block w-full rounded-lg border border-gray-200 bg-emerald-50 px-3 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm text-right" />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6">
                    <label for="keterangan" class="block text-sm font-semibold text-gray-800 mb-2">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan" rows="3" x-model="formData.keterangan"
                              class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                              placeholder="Contoh: Setoran rutin bulanan atau detail catatan lain"></textarea>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><i class="fas fa-wallet"></i></span>
                        <span>Total nilai otomatis terakumulasi dari setiap item.</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-sm text-gray-500">Total Setoran</span>
                        <span class="text-2xl font-extrabold text-emerald-600" x-text="formatRupiah(totalNilaiKeseluruhan)">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    Batal
                </a>
                <button type="submit" name="proses_setor"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition focus:ring-2 focus:ring-offset-2 focus:ring-emerald-300">
                    <i class="fas fa-save"></i> Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Data jenis sampah dari PHP untuk JavaScript
    const masterJenisSampah = <?php echo json_encode($jenis_sampah_data); ?>;

    function transaksiSetorForm() {
        return {
            formData: {
                id_warga: '',
                tanggal_transaksi: new Date().toISOString().slice(0, 16), // Format YYYY-MM-DDTHH:mm
                items: [],
                keterangan: ''
            },
            init() {
                this.addItem(); // Mulai dengan satu item default
            },
            addItem() {
                this.formData.items.push({
                    id_jenis_sampah: '',
                    berat_kg: 0,
                    harga_saat_setor: 0,
                    subtotal_nilai: 0
                });
            },
            removeItem(index) {
                this.formData.items.splice(index, 1);
            },
            updateHarga(itemIndex, idJenisSampah) {
                const selectedSampah = masterJenisSampah.find(js => js.id_jenis_sampah == idJenisSampah);
                if (selectedSampah) {
                    this.formData.items[itemIndex].harga_saat_setor = parseFloat(selectedSampah.harga_per_kg);
                } else {
                    this.formData.items[itemIndex].harga_saat_setor = 0;
                }
                this.hitungSubtotal(itemIndex);
            },
            hitungSubtotal(itemIndex) {
                const item = this.formData.items[itemIndex];
                item.subtotal_nilai = parseFloat(item.berat_kg) * parseFloat(item.harga_saat_setor);
            },
            get totalNilaiKeseluruhan() {
                return this.formData.items.reduce((total, item) => total + item.subtotal_nilai, 0);
            },
            formatRupiah(angka) {
                if (isNaN(angka)) return "Rp 0";
                return "Rp " + parseFloat(angka).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            },
            submitForm(event) {
                if (!this.formData.id_warga) {
                    alert('Harap pilih warga terlebih dahulu.');
                    event.preventDefault();
                    return false;
                }
                if (this.formData.items.length === 0) {
                    alert('Harap tambahkan minimal satu jenis sampah yang disetor.');
                    event.preventDefault();
                    return false;
                }
                for (let item of this.formData.items) {
                    if (!item.id_jenis_sampah || item.berat_kg <= 0) {
                        alert('Pastikan semua detail sampah terisi dengan benar (Jenis Sampah dipilih dan Berat > 0).');
                        event.preventDefault();
                        return false;
                    }
                }
                // Jika validasi lolos, form akan disubmit secara native
                event.target.submit();
            }
        }
    }
</script>
