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
    <title>Devoluciones y NC - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .devoluciones-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #e74c3c; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #e74c3c; color: white; }
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
        .badge-aprobada { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="devoluciones-container">
        <h1><i class="fas fa-undo"></i> Devoluciones y Notas de Crédito</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">28</div><div class="label">Devoluciones Mes</div></div>
            <div class="stat-card"><div class="value">$3,500,000</div><div class="label">Monto Devuelto</div></div>
            <div class="stat-card"><div class="value">3.8%</div><div class="label">% sobre Ventas</div></div>
            <div class="stat-card"><div class="value">12</div><div class="label">NC Pendientes</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listado')">Devoluciones</button>
            <button class="tab" onclick="cambiarTab('nc')">Notas de Crédito</button>
            <button class="tab" onclick="cambiarTab('motivos')">Análisis por Motivo</button>
        </div>
        <div id="tab-listado" class="tab-content active">
            <div class="card">
                <h3>Solicitudes de Devolución</h3>
                <button class="btn btn-primary" onclick="nuevaDevolucion()"><i class="fas fa-plus"></i> Nueva Devolución</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>N° Devolución</th><th>Factura</th><th>Cliente</th><th>Fecha</th><th>Motivo</th><th>Monto</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>DEV-2026-015</strong></td>
                            <td>F-00340</td>
                            <td>Empresa ABC</td>
                            <td>04/01/2026</td>
                            <td>Producto defectuoso</td>
                            <td>$850,000</td>
                            <td><span class="badge badge-aprobada">Aprobada</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-file-invoice"></i> Emitir NC</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>DEV-2026-016</strong></td>
                            <td>F-00342</td>
                            <td>Cliente XYZ</td>
                            <td>05/01/2026</td>
                            <td>Error en pedido</td>
                            <td>$450,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Aprobar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-nc" class="tab-content">
            <div class="card"><h3>Notas de Crédito Emitidas</h3><p>Historial de NC y su impacto en ventas</p></div>
        </div>
        <div id="tab-motivos" class="tab-content">
            <div class="card"><h3>Análisis por Motivo de Devolución</h3><p>Estadísticas de causas de devoluciones</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevaDevolucion() { alert('Nueva devolución'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
