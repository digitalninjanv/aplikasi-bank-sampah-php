<?php
// modules/auth/login.php
// Tidak memerlukan pengecekan login di sini karena ini adalah halaman login
// config/database.php sudah di-include oleh index.php utama
?>

<div class="min-h-screen relative flex items-center justify-center bg-slate-950 overflow-hidden px-4 sm:px-6">
    <div class="absolute inset-0 bg-gradient-to-br from-sky-500/40 via-indigo-500/20 to-slate-900 opacity-90"></div>
    <div class="absolute -left-16 -top-24 w-56 h-56 bg-white/10 rounded-full blur-3xl"></div>
    <div class="absolute -right-12 bottom-0 w-60 h-60 bg-sky-400/20 rounded-full blur-3xl"></div>

    <div class="relative max-w-2xl w-full grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
        <div class="hidden lg:block text-white space-y-4">
            <div class="flex items-center gap-3 text-sky-100/90 font-semibold uppercase tracking-[0.2em] text-xs">
                <span class="w-9 h-0.5 bg-sky-200"></span>Bank Sampah Digital
            </div>
            <h1 class="text-3xl font-bold leading-tight">Kelola setoran sampah lebih ringkas</h1>
            <p class="text-sky-50/90 leading-relaxed">Akses dashboard ramah mobile untuk mencatat setoran, memantau saldo, dan mengelola warga kapan saja.</p>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-3 py-2 rounded-full bg-white/10 border border-white/10 backdrop-blur">Keamanan multi level</span>
                <span class="px-3 py-2 rounded-full bg-white/10 border border-white/10 backdrop-blur">Statistik realtime</span>
                <span class="px-3 py-2 rounded-full bg-white/10 border border-white/10 backdrop-blur">Mudah diakses</span>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 sm:p-10 space-y-8 border border-slate-100/70">
            <div class="space-y-3 text-center">
                <div class="mx-auto h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/30">
                    <i class="fas fa-recycle fa-lg"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Masuk ke sistem</p>
                    <h2 class="text-2xl font-bold text-slate-900">Bank Sampah Digital</h2>
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
                echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>";
                echo "<strong class='font-bold'>Informasi:</strong>";
                echo "<span class='block sm:inline'> " . htmlspecialchars($pesan) . "</span>";
                echo "</div>";
            }
        }
        ?>

            <form class="mt-2 space-y-6" action="<?php echo BASE_URL; ?>index.php?page=auth/proses_login" method="POST">
                <input type="hidden" name="remember" value="true">
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                        <div class="relative">
                            <i class="fas fa-user text-slate-400 absolute left-4 top-3.5"></i>
                            <input id="username" name="username" type="text" autocomplete="username" required
                                   class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-11 py-3 text-slate-900 shadow-inner focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                                   placeholder="Masukkan username">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock text-slate-400 absolute left-4 top-3.5"></i>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-11 py-3 text-slate-900 shadow-inner focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                                   placeholder="Masukkan password">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-sky-500 to-indigo-600 hover:shadow-lg hover:shadow-sky-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition duration-150 ease-in-out">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-arrow-right-to-bracket h-5 w-5 text-white/80 group-hover:translate-x-0.5 transition"></i>
                        </span>
                        Masuk ke Dashboard
                    </button>
                    <p class="text-center text-sm text-slate-500">Belum punya akun? Hubungi admin untuk membuat akses.</p>
                </div>
            </form>
        </div>
    </div>
</div>
