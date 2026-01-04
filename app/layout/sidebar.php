<?php
/**
 * Conecta ERP - Sidebar
 * Menú lateral con 14 módulos principales
 */

if (!defined('IN_ERP')) {
    die('Acceso directo no permitido');
}

$currentModule = $_GET['module'] ?? 'dashboard';
?>
<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-diagram-3-fill"></i>
            <span class="logo-text">Conecta ERP</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <nav>
            <!-- Dashboard -->
            <div class="menu-item <?= $currentModule == 'dashboard' ? 'active' : '' ?>">
                <a href="/app/dashboard/dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Administración -->
            <div class="menu-section">
                <div class="section-title">Administración</div>
                <div class="menu-item">
                    <a href="#" class="has-submenu">
                        <i class="bi bi-building"></i>
                        <span>Gestión Empresas</span>
                        <i class="bi bi-chevron-down arrow"></i>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-shield-lock"></i>
                        <span>Seguridad</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-gear"></i>
                        <span>Parametrización</span>
                    </a>
                </div>
            </div>

            <!-- Entidades -->
            <div class="menu-section">
                <div class="section-title">Gestión de Entidades</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-people"></i>
                        <span>Clientes</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-truck"></i>
                        <span>Proveedores</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-person-badge"></i>
                        <span>Empleados</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-box-seam"></i>
                        <span>Productos y Servicios</span>
                    </a>
                </div>
            </div>

            <!-- Finanzas (FI) -->
            <div class="menu-section">
                <div class="section-title">Finanzas (FI)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-journal-text"></i>
                        <span>Contabilidad General</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-wallet2"></i>
                        <span>Cuentas por Pagar</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-cash-coin"></i>
                        <span>Cuentas por Cobrar</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-bank"></i>
                        <span>Tesorería</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-building-gear"></i>
                        <span>Activos Fijos</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-receipt"></i>
                        <span>Comprobantes / Facturas</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Impuestos</span>
                    </a>
                </div>
            </div>

            <!-- Controlling (CO) -->
            <div class="menu-section">
                <div class="section-title">Controlling (CO)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-diagram-3"></i>
                        <span>Centros de Costo</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Rentabilidad</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-kanban"></i>
                        <span>Proyectos</span>
                    </a>
                </div>
            </div>

            <!-- Ventas (SD) -->
            <div class="menu-section">
                <div class="section-title">Ventas (SD)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-cart-check"></i>
                        <span>Pedidos</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Facturación</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-cash-register"></i>
                        <span>POS</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-tag"></i>
                        <span>Precios</span>
                    </a>
                </div>
            </div>

            <!-- Materiales (MM) -->
            <div class="menu-section">
                <div class="section-title">Materiales (MM)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-boxes"></i>
                        <span>Inventario</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-cart3"></i>
                        <span>Compras</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-house-door"></i>
                        <span>Almacenes</span>
                    </a>
                </div>
            </div>

            <!-- Producción (PP) -->
            <div class="menu-section">
                <div class="section-title">Producción (PP)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>Órdenes Producción</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-calendar2-week"></i>
                        <span>Planificación</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-award"></i>
                        <span>Calidad</span>
                    </a>
                </div>
            </div>

            <!-- RRHH (HCM) -->
            <div class="menu-section">
                <div class="section-title">RRHH (HCM)</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Personal</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-currency-dollar"></i>
                        <span>Nómina</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-calendar-check"></i>
                        <span>Asistencia</span>
                    </a>
                </div>
            </div>

            <!-- SCM -->
            <div class="menu-section">
                <div class="section-title">SCM</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-geo-alt"></i>
                        <span>Logística</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-truck-flatbed"></i>
                        <span>Transporte</span>
                    </a>
                </div>
            </div>

            <!-- CRM -->
            <div class="menu-section">
                <div class="section-title">CRM</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-people-fill"></i>
                        <span>Clientes</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-graph-up"></i>
                        <span>Oportunidades</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-funnel"></i>
                        <span>Pipeline</span>
                    </a>
                </div>
            </div>

            <!-- BI -->
            <div class="menu-section">
                <div class="section-title">Business Intelligence</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Dashboards</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-speedometer"></i>
                        <span>KPIs</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        <span>Informes</span>
                    </a>
                </div>
            </div>

            <!-- IA Auditoría -->
            <div class="menu-section">
                <div class="section-title">IA Auditoría</div>
                <div class="menu-item">
                    <a href="/app/modules/19_ia_auditoria/dashboard.php">
                        <i class="bi bi-robot"></i>
                        <span>Auditoría Inteligente</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Detección de Fraude</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-shield-check"></i>
                        <span>Cumplimiento</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-chat-dots"></i>
                        <span>Chat Auditor</span>
                    </a>
                </div>
            </div>

            <!-- Configuración -->
            <div class="menu-section">
                <div class="section-title">Sistema</div>
                <div class="menu-item">
                    <a href="#">
                        <i class="bi bi-sliders"></i>
                        <span>Configuración</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>
</aside>

<style>
.main-sidebar {
    width: 260px;
    background: #2c3e50;
    color: white;
    height: calc(100vh - 60px);
    overflow-y: auto;
    transition: width 0.3s, transform 0.3s;
    position: fixed;
    top: 60px;
    left: 0;
    z-index: 900;
}

.main-sidebar.collapsed {
    width: 70px;
}

.main-sidebar.collapsed .logo-text,
.main-sidebar.collapsed span,
.main-sidebar.collapsed .section-title,
.main-sidebar.collapsed .arrow {
    display: none;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 20px;
    font-weight: 700;
}

.logo i {
    font-size: 28px;
    color: #667eea;
}

.sidebar-menu {
    padding: 10px 0;
}

.menu-section {
    margin-bottom: 20px;
}

.section-title {
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #95a5a6;
    letter-spacing: 0.5px;
}

.menu-item {
    margin: 2px 10px;
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #ecf0f1;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s;
    gap: 12px;
}

.menu-item a:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

.menu-item.active a {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
}

.menu-item i {
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.arrow {
    margin-left: auto;
    font-size: 12px;
}

/* Scrollbar */
.main-sidebar::-webkit-scrollbar {
    width: 6px;
}

.main-sidebar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
}

.main-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.main-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

@media (max-width: 768px) {
    .main-sidebar {
        transform: translateX(-100%);
    }

    .main-sidebar.show {
        transform: translateX(0);
    }
}
</style>

<script>
// Toggle sidebar
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('mainSidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');

            // Guardar estado en localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // Restaurar estado
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    }
});
</script>
