<?php
session_start();
require_once '../../app/core/Database.php';
if (!isset($_SESSION['id_usuario'])) { header('Location: /login.php'); exit; }
$db = \App\Core\Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturación Electrónica - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .facturacion-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #27ae60; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #27ae60; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; }
        .badge-emitida { background: #d4edda; color: #155724; }
        .badge-anulada { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="facturacion-container">
        <h1><i class="fas fa-file-invoice"></i> Facturación Electrónica</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">345</div><div class="label">Facturas Emitidas</div></div>
            <div class="stat-card"><div class="value">$92,500,000</div><div class="label">Monto Facturado</div></div>
            <div class="stat-card"><div class="value">$17,575,000</div><div class="label">IVA Generado</div></div>
            <div class="stat-card"><div class="value">98.5%</div><div class="label">DTE Aceptados SII</div></div>
            <div class="stat-card"><div class="value">12</div><div class="label">Facturas Pendientes</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listado')">Facturas Emitidas</button>
            <button class="tab" onclick="cambiarTab('emitir')">Emitir Factura</button>
            <button class="tab" onclick="cambiarTab('boletas')">Boletas</button>
            <button class="tab" onclick="cambiarTab('notas')">Notas de Crédito/Débito</button>
            <button class="tab" onclick="cambiarTab('libros')">Libro de Ventas</button>
        </div>
        <div id="tab-listado" class="tab-content active">
            <div class="card">
                <h3>Facturas Emitidas</h3>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>N° Factura</th><th>Tipo Doc</th><th>Cliente</th><th>Fecha</th><th>Neto</th><th>IVA</th><th>Total</th><th>Estado SII</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>F-00345</strong></td>
                            <td>33 - Factura</td>
                            <td>Empresa ABC</td>
                            <td>05/01/2026</td>
                            <td>$4,621,849</td>
                            <td>$878,151</td>
                            <td>$5,500,000</td>
                            <td><span class="badge badge-emitida">Aceptado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-file-pdf"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-envelope"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-emitir" class="tab-content">
            <div class="card"><h3>Emitir Nueva Factura</h3><p>Formulario de emisión de DTE</p></div>
        </div>
        <div id="tab-boletas" class="tab-content">
            <div class="card"><h3>Boletas Electrónicas</h3><p>Gestión de boletas de venta</p></div>
        </div>
        <div id="tab-notas" class="tab-content">
            <div class="card"><h3>Notas de Crédito y Débito</h3><p>Anulaciones y modificaciones</p></div>
        </div>
        <div id="tab-libros" class="tab-content">
            <div class="card"><h3>Libro de Ventas</h3><p>Registro de ventas para declaración de IVA</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportar() { window.location.href = '/api/exportacion.php?entidad=facturas&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
