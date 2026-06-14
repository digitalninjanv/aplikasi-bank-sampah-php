<?php // includes/footer.php ?>
            </main>
<?php if (is_logged_in()): ?>
        </div>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>

<footer class="bg-white border-t border-slate-200 text-slate-500 text-sm print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>&copy; <?php echo date('Y'); ?> <?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Bank Sampah Digital'; ?>. All rights reserved.</p>
        <p class="text-xs text-slate-400">v1.0.0</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

<?php if (is_logged_in()): ?>
<script>
(function() {
    const menuButton = document.getElementById('menu-button');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
        document.body.classList.add('overflow-hidden', 'md:overflow-auto');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
    }

    if (menuButton) {
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });

    var currentPath = window.location.pathname + window.location.search;
    document.querySelectorAll('.nav-link').forEach(function(link) {
        if (link.getAttribute('href') && currentPath.indexOf(link.getAttribute('href')) !== -1) {
            link.classList.add('active');
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>
