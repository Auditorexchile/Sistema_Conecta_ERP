    </div> <!-- End wrapper -->

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        <?= SYSTEM_NAME ?> v<?= SYSTEM_VERSION ?> &copy; <?= date('Y') ?> <?= SYSTEM_AUTHOR ?>
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['nombre_completo']) ?>
                        (<?= htmlspecialchars($_SESSION['nombre_perfil'] ?? 'Usuario') ?>)
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- DatePicker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    <script src="<?= ASSETS_URL ?>/js/contabilidad.js"></script>

    <script>
        // Configuración global de DataTables en español
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
        });

        // Configuración global de Select2
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('language', 'es');
        $.fn.select2.defaults.set('width', '100%');

        // Inicializar componentes comunes
        $(document).ready(function() {
            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Inicializar popovers
            $('[data-bs-toggle="popover"]').popover();

            // Auto-ocultar alertas después de 5 segundos
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>
</html>
