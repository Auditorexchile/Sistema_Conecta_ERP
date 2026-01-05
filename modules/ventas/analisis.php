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
    <title>Análisis de Ventas - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .analisis-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
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
        .btn-success { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="analisis-container">
        <h1><i class="fas fa-chart-bar"></i> Análisis de Ventas</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">$92.5M</div><div class="label">Ventas Totales</div></div>
            <div class="stat-card"><div class="value">+12.5%</div><div class="label">vs Mes Anterior</div></div>
            <div class="stat-card"><div class="value">345</div><div class="label">Transacciones</div></div>
            <div class="stat-card"><div class="value">$268,116</div><div class="label">Ticket Promedio</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('productos')">Top Productos</button>
            <button class="tab" onclick="cambiarTab('clientes')">Top Clientes</button>
            <button class="tab" onclick="cambiarTab('tendencias')">Tendencias</button>
            <button class="tab" onclick="cambiarTab('canales')">Por Canal</button>
            <button class="tab" onclick="cambiarTab('vendedores')">Por Vendedor</button>
        </div>
        <div id="tab-productos" class="tab-content active">
            <div class="card">
                <h3>Top 10 Productos Más Vendidos</h3>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>#</th><th>Producto</th><th>Cantidad</th><th>Ventas</th><th>% del Total</th><th>Margen</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>Producto A</td><td>850</td><td>$28,500,000</td><td>30.8%</td><td>36.8%</td></tr>
                        <tr><td>2</td><td>Producto B</td><td>680</td><td>$22,300,000</td><td>24.1%</td><td>35.0%</td></tr>
                        <tr><td>3</td><td>Servicio C</td><td>420</td><td>$18,000,000</td><td>19.5%</td><td>52.8%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-clientes" class="tab-content">
            <div class="card"><h3>Top Clientes</h3><p>Clientes con mayores compras</p></div>
        </div>
        <div id="tab-tendencias" class="tab-content">
            <div class="card"><h3>Tendencias de Ventas</h3><p>Evolución mensual y estacionalidad</p></div>
        </div>
        <div id="tab-canales" class="tab-content">
            <div class="card"><h3>Ventas por Canal</h3><p>Distribución entre tienda, online, distribuidores</p></div>
        </div>
        <div id="tab-vendedores" class="tab-content">
            <div class="card"><h3>Ranking de Vendedores</h3><p>Performance del equipo comercial</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportar() { window.location.href = '/api/exportacion.php?entidad=analisis_ventas&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
