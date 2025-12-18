<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= ($module ?? '') === 'dashboard' ? 'active' : '' ?>"
                   href="?module=dashboard&action=index">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- MÓDULO CONTABILIDAD -->
            <?php if (hasPermission('contabilidad', 'ver')): ?>
            <li class="nav-item">
                <div class="nav-section-title">
                    <i class="fas fa-calculator"></i> CONTABILIDAD
                </div>
            </li>

            <!-- Plan de Cuentas -->
            <li class="nav-item">
                <a class="nav-link <?= ($module ?? '') === 'plan_cuentas' ? 'active' : '' ?>"
                   href="?module=contabilidad&action=plan_cuentas">
                    <i class="fas fa-list-alt"></i>
                    <span>Plan de Cuentas</span>
                </a>
            </li>

            <!-- Comprobantes Contables -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#comprobantesMenu">
                    <i class="fas fa-file-invoice"></i>
                    <span>Comprobantes</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="comprobantesMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=comprobante&tipo=ingreso">
                                <i class="fas fa-plus-circle text-success"></i> Ingreso
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=comprobante&tipo=egreso">
                                <i class="fas fa-minus-circle text-danger"></i> Egreso
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=comprobante&tipo=traspaso">
                                <i class="fas fa-exchange-alt text-info"></i> Traspaso
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=comprobante&tipo=ajuste">
                                <i class="fas fa-sliders-h text-warning"></i> Ajuste
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=comprobantes_lista">
                                <i class="fas fa-th-list"></i> Ver Todos
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Centros de Costo -->
            <li class="nav-item">
                <a class="nav-link" href="?module=contabilidad&action=centros_costo">
                    <i class="fas fa-sitemap"></i>
                    <span>Centros de Costo</span>
                </a>
            </li>

            <!-- Libros Contables -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#librosMenu">
                    <i class="fas fa-book"></i>
                    <span>Libros Contables</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="librosMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=libro_diario">
                                <i class="fas fa-book-open"></i> Libro Diario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=libro_mayor">
                                <i class="fas fa-book"></i> Libro Mayor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=balance_comprobacion">
                                <i class="fas fa-balance-scale"></i> Balance de Comprobación
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=balance_general">
                                <i class="fas fa-file-contract"></i> Balance General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=estado_resultados">
                                <i class="fas fa-chart-bar"></i> Estado de Resultados
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- IVA -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#ivaMenu">
                    <i class="fas fa-percentage"></i>
                    <span>IVA y F29</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="ivaMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=libro_compras">
                                <i class="fas fa-shopping-cart"></i> Libro de Compras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=libro_ventas">
                                <i class="fas fa-cash-register"></i> Libro de Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=libro_honorarios">
                                <i class="fas fa-file-invoice-dollar"></i> Libro de Honorarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=f29">
                                <i class="fas fa-file-alt text-primary"></i> Formulario 29 (F29)
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Declaraciones Juradas -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#djMenu">
                    <i class="fas fa-file-signature"></i>
                    <span>Declaraciones Juradas</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="djMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=dj&tipo=1879">
                                <i class="fas fa-file-invoice"></i> DJ 1879 - Honorarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=dj&tipo=1887">
                                <i class="fas fa-users"></i> DJ 1887 - Remuneraciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=dj&tipo=1948">
                                <i class="fas fa-hand-holding-usd"></i> DJ 1948 - Retenciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=dj_lista">
                                <i class="fas fa-th-list"></i> Todas las DJ
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Cierres -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#cierresMenu">
                    <i class="fas fa-lock"></i>
                    <span>Cierres</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="cierresMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=cierre_mensual">
                                <i class="fas fa-calendar-check"></i> Cierre Mensual
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=cierre_anual">
                                <i class="fas fa-calendar-times"></i> Cierre Anual
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=periodos">
                                <i class="fas fa-calendar-alt"></i> Periodos Contables
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Configuración Contable -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#configMenu">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="configMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=tipos_documentos">
                                <i class="fas fa-file"></i> Tipos de Documentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=planimetria">
                                <i class="fas fa-project-diagram"></i> Planimetría Contable
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=auxiliares">
                                <i class="fas fa-address-book"></i> Auxiliares
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?module=contabilidad&action=tipos_cambio">
                                <i class="fas fa-dollar-sign"></i> Tipos de Cambio
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <?php endif; ?>

            <!-- MÓDULO REPORTES -->
            <?php if (hasPermission('reportes', 'ver')): ?>
            <li class="nav-item">
                <div class="nav-section-title mt-3">
                    <i class="fas fa-chart-pie"></i> REPORTES
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?module=reportes&action=index">
                    <i class="fas fa-file-excel"></i>
                    <span>Generador de Reportes</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- MÓDULO ADMINISTRACIÓN -->
            <?php if (hasPermission('administracion', 'ver')): ?>
            <li class="nav-item">
                <div class="nav-section-title mt-3">
                    <i class="fas fa-users-cog"></i> ADMINISTRACIÓN
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?module=usuarios&action=index">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?module=usuarios&action=perfiles">
                    <i class="fas fa-user-shield"></i>
                    <span>Perfiles y Permisos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?module=usuarios&action=auditoria">
                    <i class="fas fa-history"></i>
                    <span>Auditoría</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?module=empresas&action=index">
                    <i class="fas fa-building"></i>
                    <span>Empresas</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
