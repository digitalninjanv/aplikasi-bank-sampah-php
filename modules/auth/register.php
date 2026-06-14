<?php
// modules/auth/register.php
?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4 py-8">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 80% 20%, #059669 0%, transparent 50%), radial-gradient(circle at 20% 80%, #3b82f6 0%, transparent 50%);"></div>
    <div class="relative w-full max-w-md card p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="mx-auto w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20">
                <i class="fas fa-user-plus text-lg"></i>
            </div>
            <h2 class="text-xl font-bold font-['Poppins'] text-slate-900">Daftar Sebagai Warga</h2>
            <p class="text-sm text-slate-500">Isi data diri untuk mendaftar akun baru</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><p class="font-semibold">Gagal</p><p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p></div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>index.php?page=auth/proses_register" method="POST" x-data="{ loading: false }" x-on:submit="loading = true" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" type="text" required class="form-input" placeholder="Nama sesuai KTP">
            </div>
            <div>
                <label for="username" class="form-label">Username (No. Telepon)</label>
                <input id="username" name="username" type="text" required class="form-input" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password" required class="form-input" placeholder="Minimal 6 karakter">
            </div>
            <div>
                <label for="alamat" class="form-label">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" class="form-input" placeholder="Alamat lengkap"></textarea>
            </div>
            <div class="flex flex-col gap-3 pt-2">
                <button type="submit" :disabled="loading" class="btn btn-success w-full">Daftar Sekarang</button>
                <p class="text-center text-sm text-slate-500">Sudah punya akun? <a href="<?php echo BASE_URL; ?>" class="text-blue-600 hover:text-blue-700 font-medium">Masuk</a></p>
            </div>
        </form>
    </div>
</div>
