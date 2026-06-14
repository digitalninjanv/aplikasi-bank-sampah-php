<?php
check_user_level(['admin']);
?>
<div class="max-w-lg mx-auto">
    <div class="page-header">
        <h1>Backup Database</h1>
        <p>Unduh cadangan database dalam format SQL</p>
    </div>

    <div class="card p-8 text-center">
        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-cloud-download-alt fa-3x text-slate-400"></i>
        </div>
        <h2 class="text-xl font-bold font-['Poppins'] text-slate-800 mb-2">Cadangkan Database</h2>
        <p class="text-sm text-slate-500 mb-6">File SQL akan diunduh otomatis. Simpan di tempat aman.</p>
        <a href="<?php echo BASE_URL; ?>index.php?page=backup/proses" class="btn btn-primary btn-lg">
            <i class="fas fa-download"></i> Download Backup (.sql)
        </a>
        <p class="text-xs text-slate-400 mt-4"><i class="fas fa-info-circle"></i> Proses mungkin memakan waktu beberapa detik.</p>
    </div>
</div>
