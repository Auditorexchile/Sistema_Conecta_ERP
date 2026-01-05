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
    <title>Análisis de Costos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .costos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #d35400; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #d35400; color: white; }
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
    <div class="costos-container">
        <h1><i class="fas fa-coins"></i> Análisis de Costos</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">$64,000,000</div><div class="label">Costos Directos</div></div>
            <div class="stat-card"><div class="value">$19,350,000</div><div class="label">Costos Indirectos</div></div>
            <div class="stat-card"><div class="value">$83,350,000</div><div class="label">Costo Total</div></div>
            <div class="stat-card"><div class="value">66.8%</div><div class="label">% sobre Ventas</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('directo')">Costos Directos</button>
            <button class="tab" onclick="cambiarTab('indirecto')">Costos Indirectos</button>
            <button class="tab" onclick="cambiarTab('absorcion')">Costeo por Absorción</button>
            <button class="tab" onclick="cambiarTab('variable')">Costeo Variable</button>
        </div>
        <div id="tab-directo" class="tab-content active">
            <div class="card">
                <h3>Costos Directos</h3>
                <button class="btn btn-success" onclick="exportar()"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Concepto</th><th>Materia Prima</th><th>Mano Obra</th><th>Total</th><th>% del Total</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Producto A</td><td>$15,000,000</td><td>$3,000,000</td><td>$18,000,000</td><td>28.1%</td></tr>
                        <tr><td>Producto B</td><td>$12,000,000</td><td>$2,500,000</td><td>$14,500,000</td><td>22.7%</td></tr>
                        <tr><td>Servicio C</td><td>$1,500,000</td><td>$7,000,000</td><td>$8,500,000</td><td>13.3%</td></tr>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>TOTAL</td><td>$28,500,000</td><td>$35,500,000</td><td>$64,000,000</td><td>100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-indirecto" class="tab-content">
            <div class="card"><h3>Costos Indirectos (CIF)</h3><p>Costos Indirectos de Fabricación</p></div>
        </div>
        <div id="tab-absorcion" class="tab-content">
            <div class="card"><h3>Costeo por Absorción</h3><p>Incluye costos fijos y variables</p></div>
        </div>
        <div id="tab-variable" class="tab-content">
            <div class="card"><h3>Costeo Variable (Directo)</h3><p>Solo costos variables</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function exportar() { window.location.href = '/api/exportacion.php?entidad=costos&formato=excel'; }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
