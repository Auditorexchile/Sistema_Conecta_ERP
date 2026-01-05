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
    <title>Pedidos de Venta - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pedidos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #3498db; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #3498db; color: white; }
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
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-aprobado { background: #d4edda; color: #155724; }
        .badge-facturado { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="pedidos-container">
        <h1><i class="fas fa-shopping-bag"></i> Pedidos de Venta</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">158</div><div class="label">Total Pedidos</div></div>
            <div class="stat-card"><div class="value">42</div><div class="label">Pendientes</div></div>
            <div class="stat-card"><div class="value">85</div><div class="label">En Preparación</div></div>
            <div class="stat-card"><div class="value">$48,500,000</div><div class="label">Valor Total</div></div>
            <div class="stat-card"><div class="value">92.3%</div><div class="label">Tasa Cumplimiento</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listado')">Listado de Pedidos</button>
            <button class="tab" onclick="cambiarTab('picking')">Picking y Preparación</button>
            <button class="tab" onclick="cambiarTab('despacho')">Despacho</button>
            <button class="tab" onclick="cambiarTab('estadisticas')">Estadísticas</button>
        </div>
        <div id="tab-listado" class="tab-content active">
            <div class="card">
                <h3>Pedidos de Venta</h3>
                <button class="btn btn-primary" onclick="nuevoPedido()"><i class="fas fa-plus"></i> Nuevo Pedido</button>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>N° Pedido</th><th>Cliente</th><th>Fecha</th><th>Vendedor</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PV-2026-0045</strong></td>
                            <td>Empresa ABC S.A.</td>
                            <td>05/01/2026</td>
                            <td>Juan Pérez</td>
                            <td>$5,500,000</td>
                            <td><span class="badge badge-aprobado">Aprobado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>PV-2026-0046</strong></td>
                            <td>Cliente XYZ Ltda.</td>
                            <td>05/01/2026</td>
                            <td>María González</td>
                            <td>$3,200,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-picking" class="tab-content">
            <div class="card"><h3>Picking y Preparación de Pedidos</h3><p>Lista de picking para bodega</p></div>
        </div>
        <div id="tab-despacho" class="tab-content">
            <div class="card"><h3>Control de Despacho</h3><p>Programación y seguimiento de entregas</p></div>
        </div>
        <div id="tab-estadisticas" class="tab-content">
            <div class="card"><h3>Estadísticas de Pedidos</h3><p>Análisis de rendimiento de ventas</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevoPedido() { alert('Nuevo pedido'); }
        function exportar() { window.location.href = '/api/exportacion.php?entidad=pedidos&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
