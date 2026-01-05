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
    <title>Punto de Venta (POS) - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .pos-container { display: grid; grid-template-columns: 2fr 1fr; height: 100vh; }
        .productos-panel { padding: 20px; background: #ecf0f1; overflow-y: auto; }
        .carrito-panel { padding: 20px; background: white; border-left: 2px solid #ddd; display: flex; flex-direction: column; }
        .search-box { width: 100%; padding: 12px; border: 2px solid #3498db; border-radius: 8px; font-size: 16px; margin-bottom: 20px; }
        .productos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .producto-card { background: white; padding: 15px; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .producto-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .producto-img { width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 10px; background: #ecf0f1; }
        .producto-nombre { font-weight: bold; margin: 5px 0; font-size: 14px; }
        .producto-precio { color: #27ae60; font-size: 18px; font-weight: bold; }
        .carrito-items { flex: 1; overflow-y: auto; margin-bottom: 20px; }
        .carrito-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; }
        .total-section { background: #3498db; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .total-amount { font-size: 36px; font-weight: bold; text-align: center; }
        .btn { padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; margin-bottom: 10px; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Panel Productos -->
        <div class="productos-panel">
            <h2><i class="fas fa-shopping-cart"></i> Punto de Venta</h2>
            <input type="text" class="search-box" placeholder="Buscar producto por nombre, código o escanear código de barras...">
            
            <div class="productos-grid">
                <div class="producto-card" onclick="agregarProducto('Producto A', 15000)">
                    <div class="producto-img"></div>
                    <div class="producto-nombre">Producto A</div>
                    <div class="producto-precio">$15,000</div>
                </div>
                <div class="producto-card" onclick="agregarProducto('Producto B', 25000)">
                    <div class="producto-img"></div>
                    <div class="producto-nombre">Producto B</div>
                    <div class="producto-precio">$25,000</div>
                </div>
                <div class="producto-card" onclick="agregarProducto('Producto C', 35000)">
                    <div class="producto-img"></div>
                    <div class="producto-nombre">Producto C</div>
                    <div class="producto-precio">$35,000</div>
                </div>
                <div class="producto-card" onclick="agregarProducto('Producto D', 18000)">
                    <div class="producto-img"></div>
                    <div class="producto-nombre">Producto D</div>
                    <div class="producto-precio">$18,000</div>
                </div>
            </div>
        </div>

        <!-- Panel Carrito -->
        <div class="carrito-panel">
            <h3>Carrito de Compra</h3>
            <div class="carrito-items" id="carritoItems">
                <p style="text-align: center; color: #7f8c8d; padding: 40px;">Carrito vacío</p>
            </div>

            <div class="total-section">
                <div style="font-size: 14px; margin-bottom: 10px;">TOTAL A PAGAR</div>
                <div class="total-amount" id="totalAmount">$0</div>
            </div>

            <button class="btn btn-success" onclick="procesarVenta()">
                <i class="fas fa-check"></i> PROCESAR VENTA
            </button>
            <button class="btn btn-danger" onclick="limpiarCarrito()">
                <i class="fas fa-trash"></i> LIMPIAR CARRITO
            </button>
        </div>
    </div>

    <script>
        let carrito = [];
        let total = 0;

        function agregarProducto(nombre, precio) {
            carrito.push({ nombre, precio, cantidad: 1 });
            actualizarCarrito();
        }

        function actualizarCarrito() {
            const container = document.getElementById('carritoItems');
            if (carrito.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #7f8c8d; padding: 40px;">Carrito vacío</p>';
                document.getElementById('totalAmount').textContent = '$0';
                return;
            }

            container.innerHTML = carrito.map((item, index) => `
                <div class="carrito-item">
                    <div>
                        <div style="font-weight: bold;">${item.nombre}</div>
                        <div style="color: #7f8c8d; font-size: 12px;">Cantidad: ${item.cantidad}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: bold; color: #27ae60;">$${(item.precio * item.cantidad).toLocaleString('es-CL')}</div>
                        <button onclick="eliminarItem(${index})" style="background: none; border: none; color: #e74c3c; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `).join('');

            total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            document.getElementById('totalAmount').textContent = '$' + total.toLocaleString('es-CL');
        }

        function eliminarItem(index) {
            carrito.splice(index, 1);
            actualizarCarrito();
        }

        function limpiarCarrito() {
            carrito = [];
            actualizarCarrito();
        }

        function procesarVenta() {
            if (carrito.length === 0) {
                alert('El carrito está vacío');
                return;
            }
            if (confirm('¿Procesar venta por $' + total.toLocaleString('es-CL') + '?')) {
                alert('Venta procesada exitosamente');
                limpiarCarrito();
            }
        }
    </script>
</body>
</html>
