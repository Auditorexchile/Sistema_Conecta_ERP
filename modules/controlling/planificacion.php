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
    <title>Planificación y Forecast - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .planificacion-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #8e44ad; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #8e44ad; color: white; }
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
    <div class="planificacion-container">
        <h1><i class="fas fa-calendar-alt"></i> Planificación y Forecast</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">$102M</div><div class="label">Proyección Ingresos 2026</div></div>
            <div class="stat-card"><div class="value">$85M</div><div class="label">Proyección Costos 2026</div></div>
            <div class="stat-card"><div class="value">$17M</div><div class="label">Utilidad Proyectada</div></div>
            <div class="stat-card"><div class="value">16.7%</div><div class="label">Margen Proyectado</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('forecast')">Forecast Financiero</button>
            <button class="tab" onclick="cambiarTab('escenarios')">Escenarios</button>
            <button class="tab" onclick="cambiarTab('kpi')">KPIs Estratégicos</button>
            <button class="tab" onclick="cambiarTab('objetivos')">Objetivos y Metas</button>
        </div>
        <div id="tab-forecast" class="tab-content active">
            <div class="card">
                <h3>Forecast Financiero - Próximos 12 Meses</h3>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Mes</th><th>Ingresos Proyectados</th><th>Costos Proyectados</th><th>Utilidad Proyectada</th><th>Margen %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Enero 2026</td><td>$8,200,000</td><td>$6,800,000</td><td>$1,400,000</td><td>17.1%</td></tr>
                        <tr><td>Febrero 2026</td><td>$8,500,000</td><td>$7,000,000</td><td>$1,500,000</td><td>17.6%</td></tr>
                        <tr><td>Marzo 2026</td><td>$9,000,000</td><td>$7,400,000</td><td>$1,600,000</td><td>17.8%</td></tr>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>Total Trim 1</td><td>$25,700,000</td><td>$21,200,000</td><td>$4,500,000</td><td>17.5%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-escenarios" class="tab-content">
            <div class="card">
                <h3>Análisis de Escenarios</h3>
                <table>
                    <thead><tr><th>Escenario</th><th>Ingresos</th><th>Utilidad</th><th>Margen %</th><th>Probabilidad</th></tr></thead>
                    <tbody>
                        <tr><td>Optimista</td><td>$110,000,000</td><td>$19,800,000</td><td>18.0%</td><td>25%</td></tr>
                        <tr style="background: #d1ecf1;"><td><strong>Base</strong></td><td><strong>$102,000,000</strong></td><td><strong>$17,034,000</strong></td><td><strong>16.7%</strong></td><td><strong>50%</strong></td></tr>
                        <tr><td>Pesimista</td><td>$95,000,000</td><td>$14,250,000</td><td>15.0%</td><td>25%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-kpi" class="tab-content">
            <div class="card"><h3>KPIs Estratégicos</h3><p>Indicadores clave de desempeño</p></div>
        </div>
        <div id="tab-objetivos" class="tab-content">
            <div class="card"><h3>Objetivos y Metas Corporativas</h3><p>Objetivos estratégicos y cumplimiento</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportar() { window.location.href = '/api/exportacion.php?entidad=forecast&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
