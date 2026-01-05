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
    <title>Catálogo de Productos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .catalogo-container { padding: 20px; }
        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; }
        .productos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .producto-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s; }
        .producto-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .producto-imagen { width: 100%; height: 200px; object-fit: cover; background: #ecf0f1; }
        .producto-info { padding: 15px; }
        .producto-nombre { font-weight: bold; margin-bottom: 10px; }
        .producto-precio { font-size: 24px; color: #27ae60; font-weight: bold; margin: 10px 0; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn-primary { background: #3498db; color: white; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 11px; }
        .badge-disponible { background: #d4edda; color: #155724; }
        .badge-agotado { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    <div class="catalogo-container">
        <h1><i class="fas fa-book"></i> Catálogo de Productos</h1>
        
        <div class="filters">
            <input type="text" placeholder="Buscar productos..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option>Todas las categorías</option>
                <option>Electrónica</option>
                <option>Ropa</option>
                <option>Alimentos</option>
            </select>
            <select style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option>Ordenar por...</option>
                <option>Precio: Menor a Mayor</option>
                <option>Precio: Mayor a Menor</option>
                <option>Más vendidos</option>
                <option>Nuevos</option>
            </select>
        </div>

        <div class="productos-grid">
            <div class="producto-card">
                <div class="producto-imagen"></div>
                <div class="producto-info">
                    <div class="producto-nombre">Producto A Premium</div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px;">SKU: PROD-A-001</div>
                    <div class="producto-precio">$15,000</div>
                    <div style="margin: 10px 0;"><span class="badge badge-disponible">En Stock: 45</span></div>
                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Agregar al Pedido</button>
                </div>
            </div>

            <div class="producto-card">
                <div class="producto-imagen"></div>
                <div class="producto-info">
                    <div class="producto-nombre">Producto B Estándar</div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px;">SKU: PROD-B-001</div>
                    <div class="producto-precio">$25,000</div>
                    <div style="margin: 10px 0;"><span class="badge badge-disponible">En Stock: 28</span></div>
                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Agregar al Pedido</button>
                </div>
            </div>

            <div class="producto-card">
                <div class="producto-imagen"></div>
                <div class="producto-info">
                    <div class="producto-nombre">Servicio C Profesional</div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px;">SKU: SERV-C-001</div>
                    <div class="producto-precio">$35,000</div>
                    <div style="margin: 10px 0;"><span class="badge badge-disponible">Disponible</span></div>
                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Agregar al Pedido</button>
                </div>
            </div>

            <div class="producto-card">
                <div class="producto-imagen"></div>
                <div class="producto-info">
                    <div class="producto-nombre">Producto D Económico</div>
                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 10px;">SKU: PROD-D-001</div>
                    <div class="producto-precio">$8,500</div>
                    <div style="margin: 10px 0;"><span class="badge badge-agotado">Agotado</span></div>
                    <button class="btn btn-primary" disabled style="background: #95a5a6; cursor: not-allowed;">Sin Stock</button>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
