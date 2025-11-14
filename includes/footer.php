<?php // includes/footer.php ?>
            </main> <?php if (is_logged_in()): ?>
        </div> </div> <?php else: // Kondisi jika pengguna tidak login (misalnya di halaman login) ?>
    </div> <?php endif; // Akhir dari if (is_logged_in()) untuk penutup div layout utama ?>

<footer class="text-center py-4 <?php echo is_logged_in() ? 'md:pl-64' : ''; ?> bg-gray-200 text-gray-600 text-sm print:hidden">
    <p>&copy; <?php echo date('Y'); ?> Bank Sampah Digital. Dibuat dengan <i class="fas fa-heart text-red-500"></i>.</p>
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
