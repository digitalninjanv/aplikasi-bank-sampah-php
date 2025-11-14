<?php
// modules/jenis_sampah/tambah.php
check_user_level(['admin', 'petugas']);
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Jenis Sampah Baru</h1>
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-lg mx-auto">
        <form action="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/proses_simpan" method="POST">
            <div class="space-y-6">
                <div>
                    <label for="nama_sampah" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Sampah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sampah" id="nama_sampah" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                <div>
                    <label for="harga_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" required step="50" min="0" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
                </div>
                <div>
                    <label for="satuan" class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="satuan" id="satuan" required value="kg" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm" placeholder="Contoh: kg, buah, liter">
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>index.php?page=jenis_sampah/data" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" name="simpan_jenis_sampah" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i> Simpan Jenis Sampah
                </button>
            </div>
        </form>
    </div>
</div>
