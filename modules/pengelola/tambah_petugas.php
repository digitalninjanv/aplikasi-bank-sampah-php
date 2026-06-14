<?php check_user_level(['admin']); ?>
<div class="max-w-2xl mx-auto">
    <div class="page-header">
        <h1>Tambah Petugas Baru</h1>
        <p>Daftarkan petugas baru ke sistem bank sampah</p>
    </div>
    <div class="card p-6 sm:p-8">
        <form action="<?php echo BASE_URL; ?>index.php?page=pengelola/proses_simpan_petugas" method="POST" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <div class="space-y-5">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" required class="form-input" placeholder="Nama lengkap petugas">
                </div>
                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required class="form-input" placeholder="Username untuk login">
                </div>
                <div>
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input type="text" name="password" value="petugas123" required class="form-input">
                    <p class="text-xs text-slate-500 mt-1">Default: <code>petugas123</code>. Bisa diubah.</p>
                </div>
                <div>
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telepon" class="form-input" placeholder="Nomor telepon petugas">
                </div>
                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" rows="3" class="form-input" placeholder="Alamat petugas"></textarea>
                </div>
            </div>
            <div class="mt-8 flex gap-3 justify-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=pengelola/petugas" class="btn btn-outline">Batal</a>
                <button type="submit" name="simpan_petugas" :disabled="loading" class="btn btn-primary">
                    <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
