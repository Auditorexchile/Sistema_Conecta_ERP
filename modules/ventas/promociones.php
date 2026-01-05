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
    <title>Promociones y Ofertas - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .promociones-container { padding: 20px; }
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
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; }
        .badge-activa { background: #d4edda; color: #155724; }
        .badge-programada { background: #d1ecf1; color: #0c5460; }
        .badge-finalizada { background: #d3d3d3; color: #6c757d; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="promociones-container">
        <h1><i class="fas fa-percent"></i> Promociones y Ofertas</h1>
        <div class="stats-grid">
            <div class="stat-card"><div class="value">12</div><div class="label">Promociones Activas</div></div>
            <div class="stat-card"><div class="value">$8,500,000</div><div class="label">Descuentos Otorgados</div></div>
            <div class="stat-card"><div class="value">2,450</div><div class="label">Clientes Beneficiados</div></div>
            <div class="stat-card"><div class="value">+18.5%</div><div class="label">Incremento Ventas</div></div>
        </div>
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listado')">Promociones</button>
            <button class="tab" onclick="cambiarTab('cupones')">Cupones de Descuento</button>
            <button class="tab" onclick="cambiarTab('combos')">Combos y Packs</button>
            <button class="tab" onclick="cambiarTab('resultados')">Resultados</button>
        </div>
        <div id="tab-listado" class="tab-content active">
            <div class="card">
                <h3>Promociones Configuradas</h3>
                <button class="btn btn-primary" onclick="nuevaPromocion()"><i class="fas fa-plus"></i> Nueva Promoción</button>
                <table style="margin-top: 20px;">
                    <thead>
                        <tr><th>Nombre</th><th>Tipo</th><th>Descuento</th><th>Vigencia</th><th>Productos</th><th>Uso</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cyber Monday 2026</strong></td>
                            <td>% Descuento</td>
                            <td>20%</td>
                            <td>01/01/2026 - 07/01/2026</td>
                            <td>245</td>
                            <td>1,250 / ilimitado</td>
                            <td><span class="badge badge-activa">Activa</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-pause"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>2x1 Productos Seleccionados</strong></td>
                            <td>2x1</td>
                            <td>50%</td>
                            <td>05/01/2026 - 31/01/2026</td>
                            <td>45</td>
                            <td>380 / ilimitado</td>
                            <td><span class="badge badge-activa">Activa</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-pause"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Descuento Black Friday</strong></td>
                            <td>% Descuento</td>
                            <td>30%</td>
                            <td>20/11/2026 - 30/11/2026</td>
                            <td>180</td>
                            <td>0 / ilimitado</td>
                            <td><span class="badge badge-programada">Programada</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-cupones" class="tab-content">
            <div class="card"><h3>Cupones de Descuento</h3><p>Códigos promocionales únicos</p></div>
        </div>
        <div id="tab-combos" class="tab-content">
            <div class="card"><h3>Combos y Packs Promocionales</h3><p>Ofertas de productos agrupados</p></div>
        </div>
        <div id="tab-resultados" class="tab-content">
            <div class="card"><h3>Análisis de Resultados</h3><p>ROI y efectividad de promociones</p></div>
        </div>
    </div>
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        function nuevaPromocion() { alert('Nueva promoción'); }
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
