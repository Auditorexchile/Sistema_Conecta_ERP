
<script type="text/javascript" src="/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=0OHXoMqePtaNm_7o8LOqBeuLaI7fHdAvmvVojiFd1ZW5h6lhTFb_zyCuIX4s9XM1Je2ucMVe7X8_GAs0iOyCMg" charset="UTF-8"></script><?php
// Configuración para UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP - Cuentas por Pagar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #20639B;
            --secondary-color: #3CAEA3;
            --dark-color: #173F5F;
            --light-color: #F6F6F6;
            --success-color: #3CAEA3;
            --warning-color: #F6D55C;
            --danger-color: #ED553B;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --banner-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-color);
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Banner styles */
        .banner {
            background-color: var(--primary-color);
            color: white;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--banner-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .banner-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            color: white;
            font-size: 1.2rem;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Sidebar styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--secondary-color);
            height: calc(100vh - var(--banner-height));
            position: fixed;
            top: var(--banner-height);
            left: 0;
            overflow-x: hidden;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 90;
            color: white;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-item {
            position: relative;
        }

        .menu-link {
            padding: 12px 20px;
            text-decoration: none;
            font-size: 0.95rem;
            color: white;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-link:hover, .menu-link.active {
            background-color: rgba(0, 0, 0, 0.1);
            border-left: 3px solid white;
        }

        .menu-icon {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .menu-text {
            display: none;
        }

        .sidebar.collapsed .menu-icon {
            margin-right: 0;
            font-size: 1.3rem;
        }

        .sidebar.collapsed .menu-link {
            justify-content: center;
            padding: 15px 10px;
        }

        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: rgba(0, 0, 0, 0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.show {
            max-height: 1000px;
        }

        .submenu-item {
            position: relative;
        }

        .submenu-link {
            padding: 10px 20px 10px 50px;
            text-decoration: none;
            font-size: 0.85rem;
            color: white;
            display: block;
            transition: all 0.3s ease;
        }

        .submenu-link:hover, .submenu-link.active {
            background-color: rgba(0, 0, 0, 0.2);
        }

        .sidebar.collapsed .submenu {
            display: none;
        }

        .menu-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .menu-item.open .menu-arrow {
            transform: rotate(90deg);
        }

        /* Main content styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--banner-height);
            padding: 20px;
            transition: all 0.3s ease;
            min-height: calc(100vh - var(--banner-height));
            background-color: #f5f7fa;
        }

        .main-content.collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Dashboard styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }

        .card-body {
            padding: 20px;
        }

        .summary-card {
            text-align: center;
            padding: 20px;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .summary-card .label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .summary-card.primary .value {
            color: var(--primary-color);
        }

        .summary-card.success .value {
            color: var(--success-color);
        }

        .summary-card.warning .value {
            color: var(--warning-color);
        }

        .summary-card.danger .value {
            color: var(--danger-color);
        }

        /* Chart container */
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            height: 100%;
        }

        /* Table styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.collapsed {
                margin-left: 0;
            }
        }

        /* Toggle button styles */
        .sidebar-toggle {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 15px;
        }

        .sidebar-toggle:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .mobile-sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 992px) {
            .mobile-sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Search bar */
        .search-bar {
            position: relative;
            max-width: 300px;
            margin-left: auto;
        }

        .search-bar input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #ddd;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .search-bar input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-bar .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: #333;
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Banner/Navbar -->
    <div class="banner">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle d-none d-lg-block" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="banner-title">Cuentas por Pagar</h1>
        </div>
        <div class="user-menu">
            <div class="search-bar d-none d-md-block">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Buscar...">
            </div>
            <div class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </div>
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="#" class="menu-link active" onclick="loadContent('dashboard')">
                    <i class="fas fa-tachometer-alt menu-icon"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-users menu-icon"></i>
                    <span class="menu-text">Proveedores</span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('proveedores-registro')">Registro y mantenimiento</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('proveedores-condiciones')">Condiciones de pago</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('proveedores-credito')">Control de crédito</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('proveedores-clasificacion')">Clasificación</a>
                    </li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-file-invoice menu-icon"></i>
                    <span class="menu-text">Facturación</span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('facturacion-registro')">Registro de facturas</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('facturacion-notas')">Notas débito/crédito</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('facturacion-ordenes')">Asociación con órdenes</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('facturacion-impuestos')">Impuestos y retenciones</a>
                    </li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-money-bill-wave menu-icon"></i>
                    <span class="menu-text">Pagos</span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('pagos-programacion')">Programación</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('pagos-aplicacion')">Aplicación</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('pagos-anticipos')">Anticipos</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('pagos-comprobantes')">Comprobantes</a>
                    </li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-chart-line menu-icon"></i>
                    <span class="menu-text">Control</span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('control-reportes')">Reportes</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('control-analisis')">Análisis de saldos</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('control-integracion')">Integración contable</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('control-fiscal')">Obligaciones fiscales</a>
                    </li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-exchange-alt menu-icon"></i>
                    <span class="menu-text">Integración</span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('integracion-compras')">Compras</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('integracion-contabilidad')">Contabilidad</a>
                    </li>
                    <li class="submenu-item">
                        <a href="#" class="submenu-link" onclick="loadContent('integracion-tesoreria')">Tesorería</a>
                    </li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="loadContent('configuracion')">
                    <i class="fas fa-cog menu-icon"></i>
                    <span class="menu-text">Configuración</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <!-- Dashboard Content -->
            <div id="dashboard-content">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0">Resumen de Cuentas por Pagar</h2>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Exportar
                                </button>
                                <button class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i> Nuevo Pago
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card primary">
                            <div class="card-body">
                                <div class="label">Total por Pagar</div>
                                <div class="value">$120,450</div>
                                <div class="text-muted small">+5% vs mes anterior</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card warning">
                            <div class="card-body">
                                <div class="label">Próximos Vencimientos</div>
                                <div class="value">18</div>
                                <div class="text-muted small">7 días próximos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card success">
                            <div class="card-body">
                                <div class="label">Pagos Realizados</div>
                                <div class="value">$35,200</div>
                                <div class="text-muted small">Este mes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card danger">
                            <div class="card-body">
                                <div class="label">Vencidos</div>
                                <div class="value">5</div>
                                <div class="text-muted small">$12,300</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Histórico de Pagos</span>
                                    <select class="form-select form-select-sm" style="width: 150px;">
                                        <option>Últimos 6 meses</option>
                                        <option>Último año</option>
                                        <option>Últimos 2 años</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="paymentsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                Distribución por Proveedor
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="suppliersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Próximos Vencimientos</span>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        Ver todos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-container">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Proveedor</th>
                                                <th>Factura</th>
                                                <th>Fecha Venc.</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Distribuidora S.A.</td>
                                                <td>FAC-001-2023</td>
                                                <td>15/06/2023</td>
                                                <td>$4,500.00</td>
                                                <td><span class="status-badge status-pending">Pendiente</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Logística Integral</td>
                                                <td>FAC-045-2023</td>
                                                <td>18/06/2023</td>
                                                <td>$7,200.00</td>
                                                <td><span class="status-badge status-pending">Pendiente</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Tecnología Avanzada</td>
                                                <td>FAC-102-2023</td>
                                                <td>20/06/2023</td>
                                                <td>$12,750.00</td>
                                                <td><span class="status-badge status-pending">Pendiente</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Suministros Generales</td>
                                                <td>FAC-087-2023</td>
                                                <td>05/06/2023</td>
                                                <td>$3,400.00</td>
                                                <td><span class="status-badge status-overdue">Vencido</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Servicios Profesionales</td>
                                                <td>FAC-056-2023</td>
                                                <td>10/06/2023</td>
                                                <td>$5,600.00</td>
                                                <td><span class="status-badge status-paid">Pagado</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                              </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic Content Container -->
            <div id="dynamic-content" style="display: none;"></div>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="mobile-sidebar-toggle d-lg-none" id="mobileSidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        let sidebarCollapsed = false;

        function toggleSidebar() {
            sidebarCollapsed = !sidebarCollapsed;
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
            
            // Store state in localStorage
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
        }

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
        }

        // Initialize sidebar state from localStorage
        function initSidebarState() {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebarCollapsed = true;
                sidebar.classList.add('collapsed');
                mainContent.classList.add('collapsed');
            }
        }

        // Initialize charts
        function initCharts() {
            // Payments Chart
            const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
            const paymentsChart = new Chart(paymentsCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Pagos Realizados',
                        data: [12000, 19000, 15000, 25000, 22000, 35200],
                        backgroundColor: 'rgba(32, 99, 155, 0.1)',
                        borderColor: '#20639B',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }, {
                        label: 'Facturas Recibidas',
                        data: [18000, 22000, 19000, 28000, 25000, 42000],
                        backgroundColor: 'rgba(237, 85, 59, 0.1)',
                        borderColor: '#ED553B',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Suppliers Chart
            const suppliersCtx = document.getElementById('suppliersChart').getContext('2d');
            const suppliersChart = new Chart(suppliersCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Distribuidora S.A.', 'Logística Integral', 'Tecnología Avanzada', 'Suministros Generales', 'Otros'],
                    datasets: [{
                        data: [25, 20, 30, 15, 10],
                        backgroundColor: [
                            '#20639B',
                            '#3CAEA3',
                            '#F6D55C',
                            '#ED553B',
                            '#173F5F'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${percentage}% ($${value.toLocaleString()})`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Toggle submenu
        function toggleSubmenu(linkElement) {
            const menuItem = linkElement.parentElement;
            const submenu = menuItem.querySelector('.submenu');
            
            // Close all other open submenus in the same level
            const allSubmenus = menuItem.parentElement.querySelectorAll('.submenu');
            allSubmenus.forEach(sm => {
                if (sm !== submenu) {
                    sm.classList.remove('show');
                    sm.previousElementSibling.classList.remove('open');
                }
            });
            
            // Toggle current submenu
            menuItem.classList.toggle('open');
            submenu.classList.toggle('show');
        }

        // Load dynamic content
        function loadContent(module) {
            // Hide dashboard and show dynamic content container
            document.getElementById('dashboard-content').style.display = 'none';
            const dynamicContent = document.getElementById('dynamic-content');
            dynamicContent.style.display = 'block';
            
            // Simulate loading (in a real app, this would be an AJAX call)
            dynamicContent.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3 class="mb-0">${getModuleTitle(module)}</h3>
                            </div>
                            <div class="card-body">
                                <p>Contenido del módulo ${module} se cargará aquí.</p>
                                <p>En una implementación real, esto sería cargado dinámicamente desde el servidor.</p>
                                <button class="btn btn-primary mt-3" onclick="backToDashboard()">
                                    <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Update active menu item
            updateActiveMenuItem(module);
        }

        function backToDashboard() {
            document.getElementById('dashboard-content').style.display = 'block';
            document.getElementById('dynamic-content').style.display = 'none';
            document.getElementById('dynamic-content').innerHTML = '';
            
            // Reset active menu items
            document.querySelectorAll('.menu-link.active, .submenu-link.active').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector('.menu-link[onclick="loadContent(\'dashboard\')"]').classList.add('active');
        }

        function getModuleTitle(module) {
            const titles = {
                'dashboard': 'Dashboard',
                'proveedores-registro': 'Registro y Mantenimiento de Proveedores',
                'proveedores-condiciones': 'Configuración de Condiciones de Pago',
                'proveedores-credito': 'Control de Crédito a Proveedores',
                'proveedores-clasificacion': 'Clasificación de Proveedores',
                'facturacion-registro': 'Registro de Facturas de Proveedores',
                'facturacion-notas': 'Notas de Débito y Crédito',
                'facturacion-ordenes': 'Asociación con Órdenes de Compra',
                'facturacion-impuestos': 'Impuestos y Retenciones',
                'pagos-programacion': 'Programación de Pagos',
                'pagos-aplicacion': 'Aplicación de Pagos',
                'pagos-anticipos': 'Control de Anticipos',
                'pagos-comprobantes': 'Comprobantes de Egreso',
                'control-reportes': 'Reportes de Cuentas por Pagar',
                'control-analisis': 'Análisis de Saldos',
                'control-integracion': 'Integración Contable',
                'control-fiscal': 'Obligaciones Fiscales',
                'integracion-compras': 'Integración con Módulo de Compras',
                'integracion-contabilidad': 'Integración con Contabilidad',
                'integracion-tesoreria': 'Integración con Tesorería',
                'configuracion': 'Configuración del Sistema'
            };
            
            return titles[module] || module;
        }

        function updateActiveMenuItem(module) {
            // Remove active class from all menu items
            document.querySelectorAll('.menu-link.active, .submenu-link.active').forEach(item => {
                item.classList.remove('active');
            });
            
            // Find and activate the corresponding menu item
            if (module === 'dashboard') {
                document.querySelector('.menu-link[onclick="loadContent(\'dashboard\')"]').classList.add('active');
                return;
            }
            
            // Try to find a submenu link that matches the module
            const submenuLink = document.querySelector(`.submenu-link[onclick="loadContent('${module}')"]`);
            if (submenuLink) {
                submenuLink.classList.add('active');
                // Also open the parent menu
                const menuItem = submenuLink.closest('.menu-item');
                menuItem.classList.add('open');
                menuItem.querySelector('.submenu').classList.add('show');
            }
        }

        // Event listeners
        sidebarToggle.addEventListener('click', toggleSidebar);
        mobileSidebarToggle.addEventListener('click', toggleMobileSidebar);
        
        // Initialize the app
        document.addEventListener('DOMContentLoaded', function() {
            initSidebarState();
            initCharts();
        });
    </script>
</body>
</html>