<?php
session_start();
require_once '../../app/core/Database.php';
if (!isset($_SESSION['id_usuario'])) { header('Location: /login.php'); exit; }
$db = \App\Core\Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'];
$moduleName = ucfirst(str_replace('.php', '', basename(__FILE__)));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $moduleName; ?> - Materiales - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .module-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #16a085; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #16a085; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="module-container">
        <h1><i class="fas fa-boxes"></i> <?php echo $moduleName; ?> - Gestión de Materiales</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">0</div><div class="label">Métrica 1</div></div>
            <div class="stat-card"><div class="value">0</div><div class="label">Métrica 2</div></div>
            <div class="stat-card"><div class="value">0</div><div class="label">Métrica 3</div></div>
            <div class="stat-card"><div class="value">0</div><div class="label">Métrica 4</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('tab1')">Principal</button>
            <button class="tab" onclick="cambiarTab('tab2')">Secundario</button>
        </div>
        <div id="tab-tab1" class="tab-content active">
            <div class="card">
                <h3>Contenido Principal</h3>
                <button class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo</button>
                <button class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar</button>
                <table style="margin-top: 20px;">
                    <thead><tr><th>ID</th><th>Dato 1</th><th>Dato 2</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody><tr><td colspan="5" style="text-align: center; padding: 40px;">Sin datos</td></tr></tbody>
                </table>
            </div>
        </div>
        <div id="tab-tab2" class="tab-content">
            <div class="card"><h3>Contenido Secundario</h3><p>Información adicional del módulo</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
