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
    <title>Resultados Operacionales - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .resultados-container { padding: 20px; }
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
        .btn-success { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="resultados-container">
        <h1><i class="fas fa-chart-pie"></i> Resultados Operacionales</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">$95,800,000</div><div class="label">Ingresos</div></div>
            <div class="stat-card"><div class="value">$64,000,000</div><div class="label">Costos</div></div>
            <div class="stat-card"><div class="value">$19,350,000</div><div class="label">Gastos</div></div>
            <div class="stat-card"><div class="value">$12,450,000</div><div class="label">Utilidad Neta</div></div>
            <div class="stat-card"><div class="value">13.0%</div><div class="label">Margen Neto</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('estado')">Estado de Resultados</button>
            <button class="tab" onclick="cambiarTab('margenes')">Análisis de Márgenes</button>
            <button class="tab" onclick="cambiarTab('variacion')">Variación Presupuestaria</button>
            <button class="tab" onclick="cambiarTab('tendencias')">Tendencias</button>
        </div>
        <div id="tab-estado" class="tab-content active">
            <div class="card">
                <h3>Estado de Resultados - Año <?php echo date('Y'); ?></h3>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-pdf"></i> Exportar PDF</button>
                <table style="margin-top: 20px;">
                    <tbody>
                        <tr style="background: #d4edda;"><td><strong>INGRESOS OPERACIONALES</strong></td><td style="text-align: right;"><strong>$95,800,000</strong></td></tr>
                        <tr><td style="padding-left: 30px;">Ventas</td><td style="text-align: right;">$92,500,000</td></tr>
                        <tr><td style="padding-left: 30px;">Servicios</td><td style="text-align: right;">$3,300,000</td></tr>
                        <tr style="background: #f8d7da;"><td><strong>COSTOS DE VENTAS</strong></td><td style="text-align: right;"><strong>($64,000,000)</strong></td></tr>
                        <tr><td style="padding-left: 30px;">Costo Productos</td><td style="text-align: right;">($56,000,000)</td></tr>
                        <tr><td style="padding-left: 30px;">Costo Servicios</td><td style="text-align: right;">($8,000,000)</td></tr>
                        <tr style="background: #d1ecf1; font-weight: bold;"><td>MARGEN BRUTO</td><td style="text-align: right;">$31,800,000</td></tr>
                        <tr><td style="padding-left: 10px;">% Margen Bruto</td><td style="text-align: right;">33.2%</td></tr>
                        <tr style="background: #f8d7da;"><td><strong>GASTOS OPERACIONALES</strong></td><td style="text-align: right;"><strong>($19,350,000)</strong></td></tr>
                        <tr><td style="padding-left: 30px;">Gastos Administración</td><td style="text-align: right;">($8,500,000)</td></tr>
                        <tr><td style="padding-left: 30px;">Gastos Ventas</td><td style="text-align: right;">($10,850,000)</td></tr>
                        <tr style="background: #d1ecf1; font-weight: bold;"><td>RESULTADO OPERACIONAL</td><td style="text-align: right;">$12,450,000</td></tr>
                        <tr><td style="padding-left: 10px;">% Margen Operacional</td><td style="text-align: right;">13.0%</td></tr>
                        <tr><td>Ingresos No Operacionales</td><td style="text-align: right;">$500,000</td></tr>
                        <tr><td>Gastos No Operacionales</td><td style="text-align: right;">($350,000)</td></tr>
                        <tr style="background: #d1ecf1; font-weight: bold;"><td>RESULTADO ANTES IMPUESTO</td><td style="text-align: right;">$12,600,000</td></tr>
                        <tr><td>Impuesto a la Renta (27%)</td><td style="text-align: right;">($3,402,000)</td></tr>
                        <tr style="background: #27ae60; color: white; font-weight: bold;"><td>UTILIDAD NETA</td><td style="text-align: right;">$9,198,000</td></tr>
                        <tr><td style="padding-left: 10px;"><strong>% Margen Neto</strong></td><td style="text-align: right;"><strong>9.6%</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-margenes" class="tab-content">
            <div class="card"><h3>Análisis de Márgenes</h3><p>Desglose de márgenes por línea de negocio</p></div>
        </div>
        <div id="tab-variacion" class="tab-content">
            <div class="card"><h3>Variación Presupuestaria</h3><p>Real vs Presupuestado</p></div>
        </div>
        <div id="tab-tendencias" class="tab-content">
            <div class="card"><h3>Tendencias</h3><p>Evolución de resultados últimos 12 meses</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportar() { window.open('/api/reportes/estado-resultados.php'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
