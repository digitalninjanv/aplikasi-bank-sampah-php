<?php
check_user_level(['admin', 'petugas']);
?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Tambah Warga Baru</h1>
        <p>Daftarkan warga baru ke sistem Bank Sampah</p>
    </div>

    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=warga/proses_simpan" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="simpan_warga_cepat" value="1">
            <div class="space-y-5">
                <div>
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required class="form-input" placeholder="Masukkan nama lengkap warga">
                </div>
                <div>
                    <label for="no_telepon" class="form-label">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="no_telepon" id="no_telepon" required class="form-input" placeholder="Contoh: 081234567890">
                    <p class="text-xs text-slate-500 mt-1">Nomor telepon akan digunakan sebagai username dan untuk pengecekan saldo publik.</p>
                </div>
                <div>
                    <label for="alamat" class="form-label">Alamat (Opsional)</label>
                    <textarea name="alamat" id="alamat" rows="3" class="form-input" placeholder="Masukkan alamat warga"></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=warga/data" class="btn btn-outline">Batal</a>
                <button type="submit" name="simpan_warga_cepat" :disabled="loading" class="btn btn-success">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan Warga'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
