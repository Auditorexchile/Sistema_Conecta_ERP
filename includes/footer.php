    </div> <!-- End wrapper -->

    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="text-muted">
                        <?= SYSTEM_NAME ?> v<?= SYSTEM_VERSION ?> &copy; <?= date('Y') ?> <?= SYSTEM_AUTHOR ?>
                    </span>
                </div>
                <div class="footer-right">
                    <span class="text-muted">
                        <span class="icon">&#128100;</span> <?= htmlspecialchars($_SESSION['nombre_completo']) ?>
                        (<?= htmlspecialchars($_SESSION['nombre_perfil'] ?? 'Usuario') ?>)
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Vanilla - SIN DEPENDENCIAS EXTERNAS -->
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    <script src="<?= ASSETS_URL ?>/js/components.js"></script>
    <script src="<?= ASSETS_URL ?>/js/contabilidad.js"></script>
</body>
</html>
