<?php
// modules/auth/login.php
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, #3b82f6 0%, transparent 50%), radial-gradient(circle at 80% 20%, #6366f1 0%, transparent 50%);"></div>

    <div class="relative w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <div class="hidden lg:block text-white space-y-6">
            <div class="flex items-center gap-3 text-blue-300 font-semibold uppercase tracking-[0.2em] text-xs">
                <span class="w-8 h-0.5 bg-blue-400 rounded-full"></span>Bank Sampah Digital
            </div>
            <h1 class="text-4xl font-bold font-['Poppins'] leading-tight">Kelola setoran sampah<br>lebih ringkas</h1>
            <p class="text-slate-300 leading-relaxed max-w-md">Akses dashboard ramah mobile untuk mencatat setoran, memantau saldo, dan mengelola warga kapan saja.</p>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-xs text-slate-200">Keamanan multi level</span>
                <span class="px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-xs text-slate-200">Statistik realtime</span>
                <span class="px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-xs text-slate-200">Mudah diakses</span>
            </div>
        </div>

        <div class="card p-8 sm:p-10 space-y-6">
            <div class="text-center space-y-2">
                <div class="mx-auto w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <i class="fas fa-recycle text-lg"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400 font-semibold">Masuk ke sistem</p>
                    <h2 class="text-xl font-bold font-['Poppins'] text-slate-900"><?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Bank Sampah Digital'; ?></h2>
                </div>
            </div>

        <?php
        if (isset($_GET['pesan'])) {
            $pesan = "";
            if ($_GET['pesan'] == "gagal") {
                $pesan = "Login gagal! Username atau password salah.";
            } else if ($_GET['pesan'] == "logout") {
                $pesan = "Anda telah berhasil logout.";
            } else if ($_GET['pesan'] == "belum_login") {
                $pesan = "Anda harus login untuk mengakses halaman.";
            } else if ($_GET['pesan'] == "password_salah_lama") {
                $pesan = "Password lama yang Anda masukkan salah.";
            } else if ($_GET['pesan'] == "password_updated") {
                $pesan = "Password berhasil diperbarui. Silakan login kembali.";
            }
            if ($pesan) {
                echo "<div class='flash-error' role='alert'>";
                echo "<i class='fas fa-exclamation-circle'></i><div><p class='font-semibold'>Informasi</p><p>" . htmlspecialchars($pesan) . "</p></div></div>";
            }
        }
        ?>

            <form action="<?php echo BASE_URL; ?>index.php?page=auth/proses_login" method="POST" x-data="{ loading: false }" x-on:submit="loading = true" class="space-y-5">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="username" class="form-label">Username</label>
                    <div class="relative">
                        <i class="fas fa-user text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                        <input id="username" name="username" type="text" autocomplete="username" required
                               class="form-input pl-10" placeholder="Masukkan username">
                    </div>
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm"></i>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="form-input pl-10" placeholder="Masukkan password">
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" :disabled="loading" class="btn btn-primary w-full btn-lg">
                        <span x-text="loading ? 'Memproses...' : 'Masuk ke Dashboard'"></span>
                    </button>
                    <div class="flex items-center justify-between text-sm">
                        <a href="<?php echo BASE_URL; ?>index.php?page=auth/lupa_password" class="text-blue-600 hover:text-blue-700 font-medium">Lupa password?</a>
                        <a href="<?php echo BASE_URL; ?>index.php?page=auth/register" class="text-emerald-600 hover:text-emerald-700 font-medium">Daftar sebagai warga</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
