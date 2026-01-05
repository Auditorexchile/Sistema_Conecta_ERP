<?php
session_start();
require_once '../../app/core/Database.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$db = \App\Core\Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos y Servicios - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .productos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #16a085; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: end; flex-wrap: wrap; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #ecf0f1; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-inactivo { background: #f8d7da; color: #721c24; }
        .badge-bajo { background: #fff3cd; color: #856404; }

        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 20px auto; padding: 30px; width: 95%; max-width: 1000px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; font-size: 13px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }

        .producto-img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="productos-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-boxes"></i> Productos y Servicios</h1>
            <div>
                <button class="btn btn-success" onclick="nuevoProducto()">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
                <button class="btn btn-primary" onclick="exportarProductos()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
                <button class="btn btn-primary" onclick="importarProductos()">
                    <i class="fas fa-file-upload"></i> Importar
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value" id="totalProductos">0</div>
                <div class="label">Total Productos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="productosActivos">0</div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="stockTotal">0</div>
                <div class="label">Stock Total</div>
            </div>
            <div class="stat-card">
                <div class="value" id="valorInventario">$0</div>
                <div class="label">Valor Inventario</div>
            </div>
            <div class="stat-card">
                <div class="value" id="bajoStock">0</div>
                <div class="label">Bajo Stock Mínimo</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Buscar</label>
                <input type="text" id="buscar" placeholder="Código, Nombre, SKU..." style="width: 300px;">
            </div>
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Tipo</label>
                <select id="filtroTipo">
                    <option value="">Todos</option>
                    <option value="producto">Producto</option>
                    <option value="servicio">Servicio</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Categoría</label>
                <select id="filtroCategoria">
                    <option value="">Todas</option>
                    <option value="1">Electrónica</option>
                    <option value="2">Ropa</option>
                    <option value="3">Alimentos</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Estado</label>
                <select id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="aplicarFiltros()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>

        <!-- Tabla de Productos -->
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <table id="tablaProductos">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyProductos">
                    <tr><td colspan="10" style="text-align: center; padding: 40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Producto -->
    <div id="modalProducto" class="modal">
        <div class="modal-content">
            <div style="border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; margin-bottom: 25px;">
                <h2 id="modalTitulo">Nuevo Producto</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px; margin-top: -40px;">&times;</span>
            </div>

            <form id="formProducto" onsubmit="guardarProducto(event)">
                <input type="hidden" id="idProducto">

                <h4>Información General</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Código Interno *</label>
                        <input type="text" id="codigo" required>
                    </div>

                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" id="sku">
                    </div>

                    <div class="form-group">
                        <label>Código de Barras</label>
                        <input type="text" id="codigoBarras">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Nombre/Descripción *</label>
                        <input type="text" id="nombre" required>
                    </div>

                    <div class="form-group">
                        <label>Tipo *</label>
                        <select id="tipo" required onchange="cambiarTipo()">
                            <option value="producto">Producto</option>
                            <option value="servicio">Servicio</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <select id="categoria">
                            <option value="">Seleccionar...</option>
                            <option value="1">Electrónica</option>
                            <option value="2">Ropa</option>
                            <option value="3">Alimentos</option>
                            <option value="4">Muebles</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select id="unidadMedida">
                            <option value="UN">Unidad</option>
                            <option value="KG">Kilogramo</option>
                            <option value="LT">Litro</option>
                            <option value="MT">Metro</option>
                            <option value="M2">Metro Cuadrado</option>
                            <option value="M3">Metro Cúbico</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" id="marca">
                    </div>

                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" id="modelo">
                    </div>
                </div>

                <h4 style="margin-top: 25px;">Precios y Costos</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Costo Unitario</label>
                        <input type="number" id="costoUnitario" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label>Precio Venta *</label>
                        <input type="number" id="precioVenta" required step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label>Margen (%)</label>
                        <input type="number" id="margen" step="0.01" value="0" readonly>
                    </div>

                    <div class="form-group">
                        <label>Precio Mayorista</label>
                        <input type="number" id="precioMayorista" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label>Precio Distribuidor</label>
                        <input type="number" id="precioDistribuidor" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label>IVA Aplicable</label>
                        <select id="afectoIVA">
                            <option value="1">Afecto a IVA (19%)</option>
                            <option value="0">Exento de IVA</option>
                        </select>
                    </div>
                </div>

                <h4 style="margin-top: 25px;">Inventario</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Stock Actual</label>
                        <input type="number" id="stockActual" value="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number" id="stockMinimo" value="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Stock Máximo</label>
                        <input type="number" id="stockMaximo" value="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Ubicación</label>
                        <input type="text" id="ubicacion" placeholder="Ej: Bodega A, Estante 3">
                    </div>

                    <div class="form-group">
                        <label>Peso (kg)</label>
                        <input type="number" id="peso" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Volumen (m³)</label>
                        <input type="number" id="volumen" step="0.01">
                    </div>
                </div>

                <h4 style="margin-top: 25px;">Proveedor</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Proveedor Principal</label>
                        <select id="proveedorPrincipal">
                            <option value="">Seleccionar...</option>
                            <option value="1">Proveedor A</option>
                            <option value="2">Proveedor B</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Código Proveedor</label>
                        <input type="text" id="codigoProveedor">
                    </div>

                    <div class="form-group">
                        <label>Días Reposición</label>
                        <input type="number" id="diasReposicion" value="7">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>Descripción Detallada</label>
                    <textarea id="descripcion" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen del Producto</label>
                    <input type="file" id="imagen" accept="image/*">
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="activo" checked style="width: auto;">
                        <span>Producto Activo</span>
                    </label>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="ventaOnline" style="width: auto;">
                        <span>Disponible para Venta Online</span>
                    </label>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 2px solid #ecf0f1; padding-top: 20px;">
                    <button type="button" class="btn" onclick="cerrarModal()" style="background: #95a5a6; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let productos = [];

        document.addEventListener('DOMContentLoaded', () => {
            cargarProductos();
            calcularMargen();
        });

        // Calcular margen automáticamente
        document.getElementById('costoUnitario')?.addEventListener('input', calcularMargen);
        document.getElementById('precioVenta')?.addEventListener('input', calcularMargen);

        function calcularMargen() {
            const costo = parseFloat(document.getElementById('costoUnitario')?.value || 0);
            const precio = parseFloat(document.getElementById('precioVenta')?.value || 0);
            if (costo > 0 && precio > 0) {
                const margen = ((precio - costo) / costo) * 100;
                document.getElementById('margen').value = margen.toFixed(2);
            }
        }

        async function cargarProductos() {
            try {
                const res = await fetch('/api/productos/listar.php');
                productos = await res.json();
                renderizar();
                actualizarStats();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar() {
            const tbody = document.getElementById('bodyProductos');
            if (productos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">No hay productos registrados</td></tr>';
                return;
            }

            tbody.innerHTML = productos.map(p => {
                const badge = p.activo == 1 ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>';
                const badgeStock = (p.stock_actual <= p.stock_minimo) ? '<span class="badge badge-bajo">Bajo</span>' : '';
                const precio = parseFloat(p.precio_venta || 0).toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});
                const imgSrc = p.imagen || '/assets/img/no-image.png';

                return `
                    <tr>
                        <td><img src="${imgSrc}" class="producto-img" alt="${p.nombre}"></td>
                        <td><strong>${p.codigo}</strong></td>
                        <td>${p.sku || '-'}</td>
                        <td>${p.nombre}</td>
                        <td>${p.tipo}</td>
                        <td>${p.categoria_nombre || '-'}</td>
                        <td>${precio}</td>
                        <td>${p.stock_actual || 0} ${badgeStock}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarProducto(${p.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${p.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarStats() {
            document.getElementById('totalProductos').textContent = productos.length;
            document.getElementById('productosActivos').textContent = productos.filter(p => p.activo == 1).length;

            const stockTotal = productos.reduce((sum, p) => sum + (parseFloat(p.stock_actual) || 0), 0);
            document.getElementById('stockTotal').textContent = Math.round(stockTotal);

            const valorInventario = productos.reduce((sum, p) => {
                return sum + ((parseFloat(p.stock_actual) || 0) * (parseFloat(p.costo_unitario) || 0));
            }, 0);
            document.getElementById('valorInventario').textContent = valorInventario.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

            const bajoStock = productos.filter(p => (parseFloat(p.stock_actual) || 0) <= (parseFloat(p.stock_minimo) || 0)).length;
            document.getElementById('bajoStock').textContent = bajoStock;
        }

        function nuevoProducto() {
            document.getElementById('modalProducto').style.display = 'block';
            document.getElementById('formProducto').reset();
            document.getElementById('idProducto').value = '';
            document.getElementById('modalTitulo').textContent = 'Nuevo Producto';
        }

        function cerrarModal() {
            document.getElementById('modalProducto').style.display = 'none';
        }

        function cambiarTipo() {
            const tipo = document.getElementById('tipo').value;
            const stockFields = document.querySelectorAll('#stockActual, #stockMinimo, #stockMaximo, #ubicacion');
            stockFields.forEach(field => {
                field.disabled = (tipo === 'servicio');
                if (tipo === 'servicio') field.value = '0';
            });
        }

        async function guardarProducto(e) {
            e.preventDefault();

            const datos = {
                id: document.getElementById('idProducto').value,
                codigo: document.getElementById('codigo').value,
                sku: document.getElementById('sku').value,
                codigo_barras: document.getElementById('codigoBarras').value,
                nombre: document.getElementById('nombre').value,
                tipo: document.getElementById('tipo').value,
                id_categoria: document.getElementById('categoria').value,
                unidad_medida: document.getElementById('unidadMedida').value,
                marca: document.getElementById('marca').value,
                modelo: document.getElementById('modelo').value,
                costo_unitario: document.getElementById('costoUnitario').value,
                precio_venta: document.getElementById('precioVenta').value,
                precio_mayorista: document.getElementById('precioMayorista').value,
                precio_distribuidor: document.getElementById('precioDistribuidor').value,
                afecto_iva: document.getElementById('afectoIVA').value,
                stock_actual: document.getElementById('stockActual').value,
                stock_minimo: document.getElementById('stockMinimo').value,
                stock_maximo: document.getElementById('stockMaximo').value,
                ubicacion: document.getElementById('ubicacion').value,
                peso: document.getElementById('peso').value,
                volumen: document.getElementById('volumen').value,
                id_proveedor: document.getElementById('proveedorPrincipal').value,
                codigo_proveedor: document.getElementById('codigoProveedor').value,
                dias_reposicion: document.getElementById('diasReposicion').value,
                descripcion: document.getElementById('descripcion').value,
                activo: document.getElementById('activo').checked ? 1 : 0,
                venta_online: document.getElementById('ventaOnline').checked ? 1 : 0
            };

            try {
                const url = datos.id ? '/api/productos/actualizar.php' : '/api/productos/crear.php';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await res.json();
                if (result.success) {
                    alert(datos.id ? 'Producto actualizado' : 'Producto creado');
                    cerrarModal();
                    cargarProductos();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar producto');
            }
        }

        async function eliminarProducto(id) {
            if (!confirm('¿Está seguro de eliminar este producto?')) return;

            try {
                const res = await fetch('/api/productos/eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await res.json();
                if (result.success) {
                    alert('Producto eliminado');
                    cargarProductos();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al eliminar producto');
            }
        }

        function editarProducto(id) {
            const producto = productos.find(p => p.id === id);
            if (!producto) return;

            document.getElementById('idProducto').value = producto.id;
            document.getElementById('codigo').value = producto.codigo;
            document.getElementById('sku').value = producto.sku || '';
            document.getElementById('codigoBarras').value = producto.codigo_barras || '';
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('tipo').value = producto.tipo;
            document.getElementById('categoria').value = producto.id_categoria || '';
            document.getElementById('unidadMedida').value = producto.unidad_medida || 'UN';
            document.getElementById('marca').value = producto.marca || '';
            document.getElementById('modelo').value = producto.modelo || '';
            document.getElementById('costoUnitario').value = producto.costo_unitario || 0;
            document.getElementById('precioVenta').value = producto.precio_venta || 0;
            document.getElementById('precioMayorista').value = producto.precio_mayorista || 0;
            document.getElementById('precioDistribuidor').value = producto.precio_distribuidor || 0;
            document.getElementById('afectoIVA').value = producto.afecto_iva || 1;
            document.getElementById('stockActual').value = producto.stock_actual || 0;
            document.getElementById('stockMinimo').value = producto.stock_minimo || 0;
            document.getElementById('stockMaximo').value = producto.stock_maximo || 0;
            document.getElementById('ubicacion').value = producto.ubicacion || '';
            document.getElementById('peso').value = producto.peso || '';
            document.getElementById('volumen').value = producto.volumen || '';
            document.getElementById('proveedorPrincipal').value = producto.id_proveedor || '';
            document.getElementById('codigoProveedor').value = producto.codigo_proveedor || '';
            document.getElementById('diasReposicion').value = producto.dias_reposicion || 7;
            document.getElementById('descripcion').value = producto.descripcion || '';
            document.getElementById('activo').checked = producto.activo == 1;
            document.getElementById('ventaOnline').checked = producto.venta_online == 1;

            cambiarTipo();
            calcularMargen();
            document.getElementById('modalTitulo').textContent = 'Editar Producto';
            document.getElementById('modalProducto').style.display = 'block';
        }

        function aplicarFiltros() {
            cargarProductos();
        }

        function exportarProductos() {
            window.location.href = '/api/exportacion.php?entidad=productos&formato=excel';
        }

        function importarProductos() {
            alert('Funcionalidad de importación masiva de productos desde Excel/CSV');
        }

        window.onclick = (event) => {
            if (event.target == document.getElementById('modalProducto')) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
