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
    <title>Gestión de Proyectos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .proyectos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #e67e22; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #e67e22; color: white; }
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
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-cerrado { background: #d3d3d3; color: #6c757d; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="proyectos-container">
        <h1><i class="fas fa-project-diagram"></i> Gestión de Proyectos</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">24</div><div class="label">Proyectos Activos</div></div>
            <div class="stat-card"><div class="value">$285M</div><div class="label">Presupuesto Total</div></div>
            <div class="stat-card"><div class="value">$178M</div><div class="label">Ejecutado</div></div>
            <div class="stat-card"><div class="value">62.5%</div><div class="label">Avance Promedio</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('lista')">Proyectos</button>
            <button class="tab" onclick="cambiarTab('presupuesto')">Presupuesto</button>
            <button class="tab" onclick="cambiarTab('avance')">Avance</button>
            <button class="tab" onclick="cambiarTab('rentabilidad')">Rentabilidad</button>
        </div>
        <div id="tab-lista" class="tab-content active">
            <div class="card">
                <h3>Lista de Proyectos</h3>
                <button class="btn btn-primary" onclick="nuevoProyecto()"><i class="fas fa-plus"></i> Nuevo Proyecto</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Código</th><th>Proyecto</th><th>Cliente</th><th>Director</th><th>Inicio</th><th>Fin</th><th>Presupuesto</th><th>Avance %</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PRY-001</strong></td>
                            <td>Implementación ERP</td>
                            <td>Empresa ABC</td>
                            <td>Juan Pérez</td>
                            <td>01/01/2026</td>
                            <td>30/06/2026</td>
                            <td>$45,000,000</td>
                            <td>68%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-presupuesto" class="tab-content">
            <div class="card"><h3>Presupuesto de Proyectos</h3><p>Control presupuestario por proyecto</p></div>
        </div>
        <div id="tab-avance" class="tab-content">
            <div class="card"><h3>Avance de Proyectos</h3><p>Seguimiento de hitos y entregables</p></div>
        </div>
        <div id="tab-rentabilidad" class="tab-content">
            <div class="card"><h3>Rentabilidad por Proyecto</h3><p>Análisis costo-beneficio</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevoProyecto() { alert('Nueva proyecto'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
