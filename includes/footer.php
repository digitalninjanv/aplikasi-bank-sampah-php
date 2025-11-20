<?php // includes/footer.php ?>
            </main>
<?php if (is_logged_in()): ?>
        </div>
    </div>
<?php else: // Kondisi jika pengguna tidak login (misalnya di halaman login) ?>
    </div>
<?php endif; // Akhir dari if (is_logged_in()) untuk penutup div layout utama ?>

<footer class="<?php echo is_logged_in() ? 'md:pl-72' : ''; ?> bg-slate-900 text-slate-300 text-sm print:hidden border-t border-slate-800">
    <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="flex flex-col items-center justify-center gap-2 text-center sm:flex-row sm:gap-3 sm:text-left sm:justify-center">
            <p class="text-slate-400 tracking-wide">&copy; 2025 Bank Sampah Digital</p>
            <span class="hidden text-slate-700 sm:inline">|</span>
            <p class="text-slate-300">
                Powered by
                <a href="https://github.com/digitalninjanv" target="_blank" rel="noopener" class="inline-flex items-center gap-2 font-semibold text-emerald-300 hover:text-emerald-200 transition">
                    <i class="fab fa-github text-base"></i>
                    <span>digitalninjanv</span>
                </a>
            </p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

<?php if (is_logged_in()): // Hanya sertakan script sidebar jika pengguna login ?>
<script>
    // Script untuk toggle sidebar di mobile
    const menuButton = document.getElementById('menu-button');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const contentArea = document.getElementById('content-area'); // Mungkin tidak terlalu dibutuhkan lagi untuk logic ini

    function openSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('opacity-0');
            sidebarOverlay.classList.remove('pointer-events-none');
            // Optional: Mencegah scroll pada body saat sidebar mobile terbuka
            // document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        }
    }

    function closeSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('opacity-0');
            sidebarOverlay.classList.add('pointer-events-none');
            // document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
        }
    }

    if (menuButton) {
        menuButton.addEventListener('click', (e) => {
            e.stopPropagation(); // Mencegah event bubbling yang bisa langsung menutup sidebar
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            closeSidebar();
        });
    }

    // Menutup sidebar jika menekan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });
</script>
<?php endif; ?>

</body>
</html>
