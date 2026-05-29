</main>
<?php if (empty($minimalChrome)): ?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?></span>
        <span class="site-footer__sep">·</span>
        <span class="text-muted">Reservas inteligentes</span>
    </div>
</footer>
<?php endif; ?>
<script src="<?= htmlspecialchars(base_url('assets/js/carousel.js')) ?>" defer></script>
<script>
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');
    navToggle?.addEventListener('click', function () {
        var open = navLinks?.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.querySelectorAll('[data-dismiss-alert]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.alert')?.remove();
        });
    });
</script>
</body>
</html>
