<?php
// modules/auth/login.php
// Tidak memerlukan pengecekan login di sini karena ini adalah halaman login
// config/database.php sudah di-include oleh index.php utama
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-400 to-indigo-600 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-xl shadow-2xl">
        <div>
            <div class="flex justify-center">
                 <i class="fas fa-recycle fa-3x text-sky-600"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Login ke Bank Sampah
            </h2>
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

        <form class="mt-8 space-y-6" action="<?php echo BASE_URL; ?>index.php?page=auth/proses_login" method="POST">
            <input type="hidden" name="remember" value="true">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-sky-500 focus:border-sky-500 focus:z-10 sm:text-sm"
                           placeholder="Username">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-sky-500 focus:border-sky-500 focus:z-10 sm:text-sm"
                           placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock h-5 w-5 text-sky-500 group-hover:text-sky-400"></i>
                    </span>
                    Login
                </button>
            </div>
        </form>
        <p class="mt-2 text-center text-sm text-gray-600">
            Belum punya akun? Hubungi admin.
        </p>
    </div>
</div>
