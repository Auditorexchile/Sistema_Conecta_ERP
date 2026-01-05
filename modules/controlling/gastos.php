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
    <title>Control de Gastos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gastos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #c0392b; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #c0392b; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="gastos-container">
        <h1><i class="fas fa-wallet"></i> Control de Gastos</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">$83,350,000</div><div class="label">Gastos Totales</div></div>
            <div class="stat-card"><div class="value">$42,500,000</div><div class="label">Gastos Fijos</div></div>
            <div class="stat-card"><div class="value">$40,850,000</div><div class="label">Gastos Variables</div></div>
            <div class="stat-card"><div class="value">87.0%</div><div class="label">% del Presupuesto</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('categoria')">Por Categoría</button>
            <button class="tab" onclick="cambiarTab('centro')">Por Centro Costo</button>
            <button class="tab" onclick="cambiarTab('periodo')">Por Período</button>
            <button class="tab" onclick="cambiarTab('analisis')">Análisis Comparativo</button>
        </div>
        <div id="tab-categoria" class="tab-content active">
            <div class="card">
                <h3>Gastos por Categoría</h3>
                <button class="btn btn-success" onclick="exportarGastos()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Categoría</th><th>Presupuesto</th><th>Ejecutado</th><th>Saldo</th><th>% Ejec.</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Remuneraciones</td><td>$48,000,000</td><td>$42,500,000</td><td>$5,500,000</td><td>88.5%</td></tr>
                        <tr><td>Servicios Básicos</td><td>$8,000,000</td><td>$7,200,000</td><td>$800,000</td><td>90.0%</td></tr>
                        <tr><td>Arriendo</td><td>$12,000,000</td><td>$12,000,000</td><td>$0</td><td>100.0%</td></tr>
                        <tr><td>Marketing</td><td>$15,000,000</td><td>$11,650,000</td><td>$3,350,000</td><td>77.7%</td></tr>
                        <tr><td>Mantención</td><td>$5,000,000</td><td>$4,000,000</td><td>$1,000,000</td><td>80.0%</td></tr>
                        <tr><td>Otros</td><td>$8,000,000</td><td>$6,000,000</td><td>$2,000,000</td><td>75.0%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-centro" class="tab-content">
            <div class="card"><h3>Gastos por Centro de Costo</h3><p>Distribución de gastos por unidad organizacional</p></div>
        </div>
        <div id="tab-periodo" class="tab-content">
            <div class="card"><h3>Evolución Temporal de Gastos</h3><p>Análisis de tendencia mensual</p></div>
        </div>
        <div id="tab-analisis" class="tab-content">
            <div class="card"><h3>Análisis Comparativo</h3><p>Comparación períodos anteriores</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportarGastos() { window.location.href = '/api/exportacion.php?entidad=gastos&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
