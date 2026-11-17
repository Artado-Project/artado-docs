        </main>
    </div>

    <footer class="site-footer" role="contentinfo">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="assets/img/logo.svg" alt="Artado" class="logo-img"> <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>
                    </div>
                    <p class="footer-desc">
                        <?php echo t('footer_desc', 'Artado ekosistemi için resmi geliştirici dokümantasyonu.'); ?>
                    </p>
                </div>
                <div class="footer-column">
                    <h4><?php echo t('resources', 'Kaynaklar'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="docs.php?slug=getting-started"><?php echo t('getting_started', 'Başlangıç Rehberi'); ?></a></li>
                        <li><a href="docs.php?slug=what-is-artado"><?php echo t('what_is_artado', 'Artado Nedir?'); ?></a></li>
                        <li><a href="docs.php?slug=hig"><?php echo t('hig', 'Arayüz Kılavuzu (HIG)'); ?></a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4><?php echo t('navigation', 'Gezinme'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><?php echo t('home', 'Ana Sayfa'); ?></a></li>
                        <li><a href="search.php"><?php echo t('search', 'Arama'); ?></a></li>
                        <li><a href="login.php"><?php echo t('admin_login', 'Yönetici Girişi'); ?></a></li>
                    </ul>
                    <div class="social-icons" aria-label="Social links">
                        <a href="https://github.com/Artado-Project" target="_blank" rel="noopener noreferrer" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>. <?php echo t('all_rights_reserved', 'Tüm hakları saklıdır.'); ?></div>
        </div>
    </footer>

    <script>
        // Universal Theme Toggle Logic
        function applyTheme(theme) {
            document.body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            const isDark = theme === 'dark';
            document.querySelectorAll('#theme-toggle').forEach(btn => {
                const icon = btn.querySelector('i');
                const span = btn.querySelector('span');
                if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
                if (span) span.innerText = isDark ? 'Light Mode' : 'Dark Mode';
            });
        }

        const savedTheme = localStorage.getItem('theme') || 'dark';
        applyTheme(savedTheme);

        document.querySelectorAll('#theme-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentTheme = document.body.getAttribute('data-theme') || 'dark';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme);
            });
        });
        
        // Top Nav Mobile Menu Toggle
        function toggleTopNavMenu() {
            const navLinks = document.getElementById('topNavLinks');
            const toggleBtn = document.querySelector('.top-nav-mobile-toggle');
            if (navLinks) {
                const isOpen = navLinks.classList.toggle('active');
                if (toggleBtn) {
                    toggleBtn.innerHTML = isOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
                }
            }
        }

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-sidebar-overlay');
            const body = document.body;
            
            if (sidebar && overlay) {
                const isActive = sidebar.classList.contains('active');
                
                if (isActive) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                } else {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                }
            }
        }
        
        // Close mobile menu when clicking overlay
        const overlay = document.querySelector('.mobile-sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                toggleMobileMenu();
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-sidebar-overlay');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const navLinks = document.getElementById('topNavLinks');
            const topToggle = document.querySelector('.top-nav-mobile-toggle');
            
            if (sidebar && overlay && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && toggle && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }

            if (navLinks && navLinks.classList.contains('active')) {
                if (!navLinks.contains(event.target) && topToggle && !topToggle.contains(event.target)) {
                    navLinks.classList.remove('active');
                    if (topToggle) topToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        });
        
        // Mobile TOC Toggle
        function toggleMobileTOC() {
            const toc = document.querySelector('.toc-sidebar');
            const overlay = document.querySelector('.mobile-toc-overlay');
            const body = document.body;
            
            if (toc && overlay) {
                const isActive = toc.classList.contains('active');
                if (isActive) {
                    toc.classList.remove('active');
                    overlay.classList.remove('active');
                    body.classList.remove('toc-open');
                } else {
                    toc.classList.add('active');
                    overlay.classList.add('active');
                    body.classList.add('toc-open');
                }
            }
        }

        const tocOverlay = document.querySelector('.mobile-toc-overlay');
        if (tocOverlay) {
            tocOverlay.addEventListener('click', function() {
                toggleMobileTOC();
            });
        }

        // Close the TOC after selecting a section or tapping outside it.
        document.addEventListener('click', function(event) {
            const toc = document.querySelector('.toc-sidebar');
            const tocTrigger = event.target.closest('.mobile-toc-btn, .mobile-toc-inline-btn');

            if (toc && toc.classList.contains('active') && !tocTrigger) {
                if (!toc.contains(event.target) || event.target.closest('.toc-sidebar a')) {
                    toggleMobileTOC();
                }
            }
        });

        // Close mobile menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMobileMenu();
                }
                const toc = document.querySelector('.toc-sidebar');
                if (toc && toc.classList.contains('active')) {
                    toggleMobileTOC();
                }
                const navLinks = document.getElementById('topNavLinks');
                if (navLinks && navLinks.classList.contains('active')) {
                    toggleTopNavMenu();
                }
            }
        });

        // Toast Notification System
        function showToast(message, type = 'success') {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas fa-check"></i></div>
                <div class="toast-message">${message}</div>
            `;
            container.appendChild(toast);

            setTimeout(() => toast.classList.add('active'), 10);

            setTimeout(() => {
                toast.classList.remove('active');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        // Copy to Clipboard
        function copyToClipboard(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            showToast('<?php echo t('link_copied', 'Link kopyalandı!'); ?>');
        }

        // Global Click Listener for Copyable Elements
        document.addEventListener('click', function(e) {
            const headerAnchor = e.target.closest('.header-anchor');
            if (headerAnchor) {
                e.preventDefault();
                const url = window.location.origin + window.location.pathname + window.location.search + headerAnchor.getAttribute('href');
                copyToClipboard(url);
                
                const id = headerAnchor.getAttribute('href').substring(1);
                const target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    window.history.pushState(null, null, '#' + id);
                }
                return;
            }

            const tocTitle = e.target.closest('.toc-title');
            if (tocTitle) {
                copyToClipboard(window.location.href);
            }
        });

        // Google Translate Integration
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'tr', 
                autoDisplay: false
            }, 'google_translate_element');
            
            updateLangButtons();
        }

        function setLang(langCode) {
            const combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = langCode;
                combo.dispatchEvent(new Event('change'));
            }
            
            if (langCode === 'tr') {
                const domains = [window.location.hostname, "." + window.location.hostname, ""];
                const cookies = ["googtrans"];
                cookies.forEach(c => {
                    domains.forEach(d => {
                        document.cookie = c + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + d;
                        document.cookie = c + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    });
                });
                window.location.reload(); 
            }
            
            setTimeout(updateLangButtons, 500);
        }

        function updateLangButtons() {
            const combo = document.querySelector('.goog-te-combo');
            const currentLang = combo ? combo.value : 'tr';
            document.querySelectorAll('.lang-btn').forEach(btn => {
                const btnLang = btn.getAttribute('data-lang') || btn.innerText.trim().toLowerCase();
                if (btnLang.toLowerCase() === currentLang.toLowerCase()) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <div id="google_translate_element" style="display:none !important;"></div>
</body>
</html>
