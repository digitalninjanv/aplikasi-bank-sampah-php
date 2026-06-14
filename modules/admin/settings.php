<?php
check_user_level(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    require_csrf();
    $keys = ['app_name', 'app_address', 'app_phone'];
    foreach ($keys as $key) {
        $val = sanitize_input($_POST[$key] ?? '');
        $stmt = mysqli_prepare($koneksi, "INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        mysqli_stmt_bind_param($stmt, "ss", $key, $val);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    log_aktivitas('Update Settings', 'app_settings', null, 'Pengaturan aplikasi diperbarui');
    $_SESSION['success_message'] = "Pengaturan aplikasi berhasil disimpan.";
    redirect(BASE_URL . 'index.php?page=admin/settings');
}

$settings = [];
$q = mysqli_query($koneksi, "SELECT setting_key, setting_value FROM app_settings");
while ($r = mysqli_fetch_assoc($q)) {
    $settings[$r['setting_key']] = $r['setting_value'];
}
?>
<div class="max-w-2xl mx-auto">
    <div class="card p-6 sm:p-8">
        <div class="page-header mb-6">
            <h1>Pengaturan Aplikasi</h1>
            <p>Ubah nama, alamat, dan kontak bank sampah yang tampil di sistem</p>
        </div>

        <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=admin/settings" x-data="{ loading: false }" x-on:submit="loading = true">
            <?php echo csrf_field(); ?>
            <div class="space-y-5">
                <div>
                    <label for="app_name" class="form-label">Nama Aplikasi <span class="text-red-500">*</span></label>
                    <input type="text" name="app_name" id="app_name" value="<?php echo htmlspecialchars($settings['app_name'] ?? 'Bank Sampah Digital'); ?>" required class="form-input">
                </div>
                <div>
                    <label for="app_address" class="form-label">Alamat</label>
                    <textarea name="app_address" id="app_address" rows="2" class="form-input"><?php echo htmlspecialchars($settings['app_address'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label for="app_phone" class="form-label">No. Telepon</label>
                    <input type="text" name="app_phone" id="app_phone" value="<?php echo htmlspecialchars($settings['app_phone'] ?? ''); ?>" class="form-input">
                </div>
                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" name="save_settings" :disabled="loading" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span x-text="loading ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
                    </button>
                    <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="btn btn-outline">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
if (isset($stmt)) mysqli_stmt_close($stmt);
?>
