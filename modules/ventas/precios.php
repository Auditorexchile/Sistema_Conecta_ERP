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
    <title>Gestión de Precios - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .precios-container { padding: 20px; }
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
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="precios-container">
        <h1><i class="fas fa-tags"></i> Gestión de Precios</h1>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listas')">Listas de Precios</button>
            <button class="tab" onclick="cambiarTab('reglas')">Reglas de Pricing</button>
            <button class="tab" onclick="cambiarTab('descuentos')">Descuentos por Volumen</button>
            <button class="tab" onclick="cambiarTab('historial')">Historial de Cambios</button>
        </div>
        <div id="tab-listas" class="tab-content active">
            <div class="card">
                <h3>Listas de Precios</h3>
                <button class="btn btn-primary" onclick="nuevaLista()"><i class="fas fa-plus"></i> Nueva Lista</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Lista</th><th>Tipo Cliente</th><th>Productos</th><th>Vigencia</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Precio Público</td><td>General</td><td>245</td><td>Permanente</td><td>Activa</td><td><button class="btn btn-primary">Editar</button></td></tr>
                        <tr><td>Precio Mayorista</td><td>Distribuidor</td><td>245</td><td>Permanente</td><td>Activa</td><td><button class="btn btn-primary">Editar</button></td></tr>
                        <tr><td>Precio Especial Verano</td><td>General</td><td>85</td><td>01/12/2025 - 28/02/2026</td><td>Activa</td><td><button class="btn btn-primary">Editar</button></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-reglas" class="tab-content">
            <div class="card"><h3>Reglas de Pricing Dinámico</h3><p>Configuración de precios automáticos basados en costos, competencia, demanda</p></div>
        </div>
        <div id="tab-descuentos" class="tab-content">
            <div class="card"><h3>Descuentos por Volumen</h3><p>Escalas de descuento según cantidad comprada</p></div>
        </div>
        <div id="tab-historial" class="tab-content">
            <div class="card"><h3>Historial de Cambios de Precios</h3><p>Auditoría de modificaciones de precios</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevaLista() { alert('Nueva lista de precios'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
