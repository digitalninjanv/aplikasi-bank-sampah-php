<?php
check_user_level(['admin', 'petugas']);
?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Tambah Jenis Sampah Baru</h1>
        <p>Tambahkan kategori sampah baru ke dalam sistem</p>
    </div>

    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/proses_simpan" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <div class="space-y-5">
                <div>
                    <label for="nama_sampah" class="form-label">Nama Jenis Sampah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sampah" id="nama_sampah" required class="form-input" placeholder="Contoh: Botol Plastik">
                </div>
                <div>
                    <label for="harga_per_kg" class="form-label">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" required step="50" min="0" class="form-input" placeholder="Contoh: 5000">
                </div>
                <div>
                    <label for="satuan" class="form-label">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="satuan" id="satuan" required value="kg" class="form-input" placeholder="Contoh: kg, buah, liter">
                </div>
                <div>
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-input" placeholder="Deskripsi singkat tentang jenis sampah"></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="btn btn-outline">Batal</a>
                <button type="submit" name="simpan_jenis_sampah" :disabled="loading" class="btn btn-success">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan Jenis Sampah'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
