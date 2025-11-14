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
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Input Setoran Sampah</h1>

    <form action="<?php echo BASE_URL; ?>index.php?page=transaksi/proses_setor" method="POST" @submit.prevent="submitForm">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-4xl mx-auto">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="id_warga" class="block text-sm font-medium text-gray-700 mb-1">Pilih Warga <span class="text-red-500">*</span></label>
                    <select name="id_warga" id="id_warga" required x-model="formData.id_warga"
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        <option value="">-- Pilih Warga --</option>
                        <?php while($warga = mysqli_fetch_assoc($result_warga)): ?>
                        <option value="<?php echo $warga['id_pengguna']; ?>">
                            <?php echo htmlspecialchars($warga['nama_lengkap']) . " (" . htmlspecialchars($warga['username']) . ")"; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="tanggal_transaksi" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi" required
                           value="<?php echo date('Y-m-d\TH:i'); ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-700 mb-3">Detail Sampah Disetor</h2>
            <div id="detail-sampah-container" class="space-y-4 mb-6">
                <template x-for="(item, index) in formData.items" :key="index">
                    <div class="grid grid-cols-12 gap-3 p-3 border rounded-lg items-end bg-gray-50">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block text-xs font-medium text-gray-600">Jenis Sampah</label>
                            <select :name="'items[' + index + '][id_jenis_sampah]'" x-model="item.id_jenis_sampah" @change="updateHarga(index, $event.target.value)" required
                                    class="mt-1 block w-full px-2 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                                <option value="">-- Pilih Sampah --</option>
                                <?php foreach($jenis_sampah_data as $js): ?>
                                <option value="<?php echo $js['id_jenis_sampah']; ?>" data-harga="<?php echo $js['harga_per_kg']; ?>">
                                    <?php echo htmlspecialchars($js['nama_sampah']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Berat (Kg)</label>
                            <input type="number" :name="'items[' + index + '][berat_kg]'" x-model.number="item.berat_kg" @input="hitungSubtotal(index)" step="0.01" min="0.01" required
                                   class="mt-1 block w-full px-2 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Harga/Kg</label>
                            <input type="number" :name="'items[' + index + '][harga_saat_setor]'" x-model.number="item.harga_saat_setor" readonly
                                   class="mt-1 block w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                        <div class="col-span-10 sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                            <input type="text" :value="formatRupiah(item.subtotal_nilai)" readonly
                                   class="mt-1 block w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm sm:text-sm text-right">
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex items-end">
                            <button type="button" @click="removeItem(index)" title="Hapus item"
                                    class="mt-1 w-full text-red-500 hover:text-red-700 px-2 py-2 rounded-md border border-red-300 hover:bg-red-100 transition">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()"
                    class="mb-6 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                <i class="fas fa-plus mr-2"></i> Tambah Jenis Sampah Lain
            </button>

            <div class="mb-6">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" rows="2" x-model="formData.keterangan"
                          class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"></textarea>
            </div>

            <div class="mt-6 p-4 border-t border-gray-200">
                <div class="flex justify-end items-center">
                    <span class="text-lg font-semibold text-gray-700 mr-4">Total Nilai Setoran:</span>
                    <span class="text-2xl font-bold text-green-600" x-text="formatRupiah(totalNilaiKeseluruhan)">Rp 0</span>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" name="proses_setor"
                        class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i> Simpan Setoran
                </button>
            </div>
        </div>
    </form>
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
