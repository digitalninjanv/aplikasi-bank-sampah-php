<?php
// modules/auth/lupa_password.php
?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, #3b82f6 0%, transparent 50%), radial-gradient(circle at 80% 20%, #6366f1 0%, transparent 50%);"></div>
    <div class="relative w-full max-w-md card p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="mx-auto w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center shadow-lg shadow-amber-600/20">
                <i class="fas fa-key text-lg"></i>
            </div>
            <h2 class="text-xl font-bold font-['Poppins'] text-slate-900">Lupa Password</h2>
            <p class="text-sm text-slate-500">Masukkan username (no. telepon) untuk mereset password</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><p class="font-semibold">Gagal</p><p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p></div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="flash-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <div><p class="font-semibold">Berhasil</p><p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p></div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>index.php?page=auth/proses_lupa_password" method="POST" x-data="{ loading: false }" x-on:submit="loading = true" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="username" class="form-label">Username (No. Telepon)</label>
                <div class="relative">
                    <i class="fas fa-user text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                    <input id="username" name="username" type="text" required class="form-input pl-10" placeholder="Masukkan username">
                </div>
            </div>
            <div class="flex flex-col gap-3 pt-2">
                <button type="submit" :disabled="loading" class="btn btn-primary w-full">Reset Password</button>
                <p class="text-center text-sm text-slate-500"><a href="<?php echo BASE_URL; ?>" class="text-blue-600 hover:text-blue-700 font-medium">Kembali ke Login</a></p>
            </div>
        </form>
    </div>
</div>
