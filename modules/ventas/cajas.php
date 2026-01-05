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
    <title>Gestión de Cajas - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cajas-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #f39c12; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #f39c12; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; }
        .badge-abierta { background: #d4edda; color: #155724; }
        .badge-cerrada { background: #d3d3d3; color: #6c757d; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="cajas-container">
        <h1><i class="fas fa-cash-register"></i> Gestión de Cajas</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">8</div><div class="label">Cajas Activas</div></div>
            <div class="stat-card"><div class="value">$12,500,000</div><div class="label">Total en Cajas</div></div>
            <div class="stat-card"><div class="value">$8,200,000</div><div class="label">Ventas Hoy</div></div>
            <div class="stat-card"><div class="value">245</div><div class="label">Transacciones Hoy</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('cajas')">Cajas</button>
            <button class="tab" onclick="cambiarTab('arqueo')">Arqueo de Caja</button>
            <button class="tab" onclick="cambiarTab('cuadre')">Cuadre de Caja</button>
            <button class="tab" onclick="cambiarTab('movimientos')">Movimientos</button>
        </div>
        <div id="tab-cajas" class="tab-content active">
            <div class="card">
                <h3>Cajas del Punto de Venta</h3>
                <button class="btn btn-primary" onclick="nuevaCaja()"><i class="fas fa-plus"></i> Nueva Caja</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Caja</th><th>Cajero</th><th>Apertura</th><th>Saldo Inicial</th><th>Ventas</th><th>Saldo Actual</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Caja 01</strong></td>
                            <td>María González</td>
                            <td>05/01/2026 09:00</td>
                            <td>$500,000</td>
                            <td>$2,800,000</td>
                            <td>$3,300,000</td>
                            <td><span class="badge badge-abierta">Abierta</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-times-circle"></i> Cerrar</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Caja 02</strong></td>
                            <td>Juan Pérez</td>
                            <td>05/01/2026 09:00</td>
                            <td>$500,000</td>
                            <td>$3,200,000</td>
                            <td>$3,700,000</td>
                            <td><span class="badge badge-abierta">Abierta</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-times-circle"></i> Cerrar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-arqueo" class="tab-content">
            <div class="card"><h3>Arqueo de Caja</h3><p>Conteo físico de dinero en caja</p></div>
        </div>
        <div id="tab-cuadre" class="tab-content">
            <div class="card"><h3>Cuadre de Caja</h3><p>Conciliación entre ventas y efectivo</p></div>
        </div>
        <div id="tab-movimientos" class="tab-content">
            <div class="card"><h3>Movimientos de Caja</h3><p>Historial de transacciones</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevaCaja() { alert('Apertura de caja'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
