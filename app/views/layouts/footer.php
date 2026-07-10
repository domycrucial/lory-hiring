<?php if (!empty($inDashboard)): ?>
    </main> <!-- Close dashboard-main -->
</div> <!-- Close dashboard wrapper -->
<?php else: ?>
</main> <!-- Close main wrapper container -->
<?php endif; ?>

<?php 
// Determine if we should display the footer
$showFooter = true;
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
$basePath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

if (
    !empty($inDashboard) || 
    str_starts_with($requestUri, 'auth')
) {
    $showFooter = false;
}
?>

<?php if ($showFooter): ?>
<!-- ─── Application Footer ────────────────────────────────────── -->
<footer class="footer">
    <div class="container">
        <!-- Responsive footer columns grid -->
        <div class="footer-grid">
            <!-- Brand introduction column -->
            <div>
                <h3 style="color:white; margin-bottom: var(--space-4);"><i class="fa-solid fa-truck"></i> OLHS</h3>
                <p>Online Lorries Hiring System — connecting lorry owners with customers across Tanzania. Fast, secure, and reliable transport solutions.</p>
            </div>
            <!-- Quick navigation links column -->
            <div>
                <h4 style="color:white; margin-bottom: var(--space-3);"><i class="fa-solid fa-compass"></i> Quick Links</h4>
                <p><a href="<?= APP_URL ?>/"><i class="fa-solid fa-caret-right"></i> Home</a></p>
                <p><a href="<?= APP_URL ?>/lorries/search"><i class="fa-solid fa-caret-right"></i> Search Lorries</a></p>
                <p><a href="<?= APP_URL ?>/auth/register"><i class="fa-solid fa-caret-right"></i> Register</a></p>
            </div>
            <!-- Business partners column -->
            <div>
                <h4 style="color:white; margin-bottom: var(--space-3);"><i class="fa-solid fa-briefcase"></i> For Owners</h4>
                <p><a href="<?= APP_URL ?>/auth/register"><i class="fa-solid fa-caret-right"></i> List Your Lorry</a></p>
                <p><a href="<?= APP_URL ?>/auth/login"><i class="fa-solid fa-caret-right"></i> Owner Login</a></p>
            </div>
            <!-- Address and contact column -->
            <div>
                <h4 style="color:white; margin-bottom: var(--space-3);"><i class="fa-solid fa-envelope"></i> Contact</h4>
                <p><i class="fa-solid fa-phone" style="width: 20px;"></i> +255 700 000 000</p>
                <p><i class="fa-solid fa-envelope" style="width: 20px;"></i> info@olhs.co.tz</p>
                <p><i class="fa-solid fa-location-dot" style="width: 20px;"></i> Dar es Salaam, TZ</p>
            </div>
        </div>
        <!-- Bottom copyright footer text -->
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved. Made with <i class="fa-solid fa-heart" style="color: var(--danger);"></i> in Tanzania.
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- ─── Global Client-Side Scripts ──────────────────────────────── -->
<script>
    // Logout SweetAlert Confirmation
    document.addEventListener('DOMContentLoaded', () => {
        const logoutLinks = document.querySelectorAll('a[href$="/auth/logout"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                Swal.fire({
                    title: '<?= currentLang() === "sw" ? "Je, uko tayari kutoka?" : "Are you sure you want to logout?" ?>',
                    text: '<?= currentLang() === "sw" ? "Utaondolewa kwenye akaunti yako." : "You will be logged out of your session." ?>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: '<?= currentLang() === "sw" ? "Ndiyo, Toka" : "Yes, Logout" ?>',
                    cancelButtonText: '<?= currentLang() === "sw" ? "Hapana" : "Cancel" ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link.href;
                    }
                });
            });
        });
    });

    // Initialize listener for mobile navigation hamburger menu toggle
    const toggle = document.getElementById('nav-toggle');
    const links = document.getElementById('nav-links');
    const sidebar = document.querySelector('.sidebar');
    
    // Create backdrop overlay for sidebar drawer if on mobile/dashboard
    let overlay = document.querySelector('.sidebar-overlay');
    if (sidebar && !overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        sidebar.parentNode.insertBefore(overlay, sidebar.nextSibling);
    }
    
    // Bind click event to slide open navigation menu drawer/sidebar
    if (toggle) {
        toggle.addEventListener('click', () => {
            if (sidebar) {
                sidebar.classList.toggle('open');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
                const icon = toggle.querySelector('i');
                if (icon) {
                    if (sidebar.classList.contains('open')) {
                        icon.className = 'fa-solid fa-xmark';
                    } else {
                        icon.className = 'fa-solid fa-bars';
                    }
                }
            } else if (links) {
                links.classList.toggle('open');
                const icon = toggle.querySelector('i');
                if (icon) {
                    if (links.classList.contains('open')) {
                        icon.className = 'fa-solid fa-xmark';
                    } else {
                        icon.className = 'fa-solid fa-bars';
                    }
                }
            }
        });
    }
    
    // Close sidebar when clicking on overlay backdrop
    if (overlay && sidebar) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            if (toggle) {
                const icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-bars';
                }
            }
        });
    }
    
    // Automatically fade out and dismiss flash notification messages after 5 seconds
    const flash = document.getElementById('flash-alert');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0'; // Reduce visibility
            flash.style.transition = 'opacity .5s'; // Smooth fade animation
            setTimeout(() => flash.remove(), 500); // Purge elements from DOM
        }, 5000);
    }

    // Dynamic Dark/Light Theme Switching Click Handler
    const themeBtns = [document.getElementById('theme-toggle'), document.getElementById('sidebar-theme-toggle')];
    
    const updateThemeIcons = (theme) => {
        themeBtns.forEach(btn => {
            if (!btn) return;
            const icon = btn.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'fa-solid fa-sun';
                    icon.style.color = '#fbbf24'; // Sun yellow
                } else {
                    icon.className = 'fa-solid fa-moon';
                    icon.style.color = btn.id === 'sidebar-theme-toggle' ? 'var(--gray-500)' : 'var(--gray-600)';
                }
            }
        });
    };

    // Initialize icon state
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeIcons(currentTheme);

    themeBtns.forEach(btn => {
        if (!btn) return;
        btn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons(newTheme);
        });
    });

    // Premium dashboard counter animation for stat values
    document.addEventListener('DOMContentLoaded', () => {
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach(el => {
            const text = el.innerText.trim();
            // Extract numeric part (e.g., "120,000" or "5")
            const matches = text.match(/[\d,.]+/);
            if (matches) {
                const rawNum = parseFloat(matches[0].replace(/,/g, ''));
                if (!isNaN(rawNum) && rawNum > 0) {
                    const isCurrency = text.includes('TZS') || text.toLowerCase().includes('tz');
                    const suffix = text.replace(matches[0], '');
                    let start = 0;
                    const duration = 1000; // 1 second duration
                    const startTime = performance.now();
                    
                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        // Ease out cubic function
                        const easeProgress = 1 - Math.pow(1 - progress, 3);
                        const currentNum = Math.floor(easeProgress * rawNum);
                        
                        if (isCurrency) {
                            el.innerText = currentNum.toLocaleString() + suffix;
                        } else {
                            el.innerText = currentNum + suffix;
                        }
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            el.innerText = text; // Ensure exact final text
                        }
                    }
                    requestAnimationFrame(updateCounter);
                }
            }
        });
    });
</script>

<!-- Inject controller-specific JavaScript files -->
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
    <script src="<?= APP_URL ?>/public/js/<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
