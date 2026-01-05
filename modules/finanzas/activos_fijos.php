<?php
session_start();
require_once '../app/core/Database.php';

// Verificar autenticación
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
    <title>Activos Fijos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .activos-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 14px; color: #666; margin: 0 0 10px 0; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #2c3e50; }
        .stat-card .icon { float: right; font-size: 32px; opacity: 0.3; }

        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .filters input, .filters select { margin-right: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .table-container { background: white; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-depreciado { background: #fff3cd; color: #856404; }
        .badge-vendido { background: #f8d7da; color: #721c24; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; margin: 0 2px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 50px auto; padding: 30px; width: 90%; max-width: 800px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .depreciation-chart { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .progress-bar { width: 100%; height: 20px; background: #ecf0f1; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); transition: width 0.3s; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="activos-container">
        <div class="page-header">
            <h1><i class="fas fa-building"></i> Activos Fijos</h1>
            <button class="btn btn-primary" onclick="abrirModal()">
                <i class="fas fa-plus"></i> Nuevo Activo
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-boxes icon"></i>
                <h3>Total Activos</h3>
                <div class="value" id="totalActivos">0</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign icon"></i>
                <h3>Valor Libro Total</h3>
                <div class="value" id="valorLibro">$0</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line icon"></i>
                <h3>Depreciación Acumulada</h3>
                <div class="value" id="depreciacionAcum">$0</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-percent icon"></i>
                <h3>% Depreciado</h3>
                <div class="value" id="porcentajeDepr">0%</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <select id="filtroCategoria" onchange="filtrarActivos()">
                <option value="">Todas las Categorías</option>
                <option value="edificios">Edificios</option>
                <option value="vehiculos">Vehículos</option>
                <option value="maquinaria">Maquinaria</option>
                <option value="equipos">Equipos de Oficina</option>
                <option value="computacion">Equipos de Computación</option>
                <option value="muebles">Muebles y Enseres</option>
            </select>

            <select id="filtroEstado" onchange="filtrarActivos()">
                <option value="">Todos los Estados</option>
                <option value="activo">Activo</option>
                <option value="depreciado">Totalmente Depreciado</option>
                <option value="vendido">Vendido</option>
                <option value="baja">Dado de Baja</option>
            </select>

            <input type="text" id="busqueda" placeholder="Buscar por código o descripción..." onkeyup="filtrarActivos()">

            <button class="btn btn-success" onclick="calcularDepreciacion()">
                <i class="fas fa-calculator"></i> Calcular Depreciación
            </button>

            <button class="btn btn-warning" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>

        <!-- Tabla de Activos -->
        <div class="table-container">
            <table id="tablaActivos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Fecha Compra</th>
                        <th>Valor Original</th>
                        <th>Depreciación Acum.</th>
                        <th>Valor Libro</th>
                        <th>Vida Útil</th>
                        <th>% Depr.</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyActivos">
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #ccc;"></i>
                            <p>Cargando activos fijos...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Activo -->
    <div id="modalActivo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nuevo Activo Fijo</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
            </div>

            <form id="formActivo" onsubmit="guardarActivo(event)">
                <input type="hidden" id="idActivo">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Código *</label>
                        <input type="text" id="codigo" required>
                    </div>

                    <div class="form-group">
                        <label>Categoría *</label>
                        <select id="categoria" required onchange="actualizarVidaUtil()">
                            <option value="">Seleccionar...</option>
                            <option value="edificios" data-vida="40">Edificios (40 años)</option>
                            <option value="vehiculos" data-vida="5">Vehículos (5 años)</option>
                            <option value="maquinaria" data-vida="10">Maquinaria (10 años)</option>
                            <option value="equipos" data-vida="10">Equipos de Oficina (10 años)</option>
                            <option value="computacion" data-vida="3">Equipos de Computación (3 años)</option>
                            <option value="muebles" data-vida="10">Muebles y Enseres (10 años)</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Descripción *</label>
                        <textarea id="descripcion" rows="2" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Compra *</label>
                        <input type="date" id="fechaCompra" required>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Activación</label>
                        <input type="date" id="fechaActivacion">
                    </div>

                    <div class="form-group">
                        <label>Valor Original *</label>
                        <input type="number" id="valorOriginal" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Valor Residual</label>
                        <input type="number" id="valorResidual" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label>Vida Útil (años) *</label>
                        <input type="number" id="vidaUtil" required>
                    </div>

                    <div class="form-group">
                        <label>Método Depreciación *</label>
                        <select id="metodoDepreciacion">
                            <option value="lineal">Lineal</option>
                            <option value="acelerada">Acelerada</option>
                            <option value="unidades">Por Unidades de Producción</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Proveedor</label>
                        <input type="text" id="proveedor">
                    </div>

                    <div class="form-group">
                        <label>N° Factura</label>
                        <input type="text" id="numeroFactura">
                    </div>

                    <div class="form-group">
                        <label>Ubicación</label>
                        <input type="text" id="ubicacion">
                    </div>

                    <div class="form-group">
                        <label>Responsable</label>
                        <input type="text" id="responsable">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Observaciones</label>
                        <textarea id="observaciones" rows="3"></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>

            <div class="depreciation-chart" id="chartDepreciacion" style="display: none;">
                <h3>Proyección de Depreciación</h3>
                <canvas id="canvasDepreciacion"></canvas>
            </div>
        </div>
    </div>

    <script>
        let activos = [];

        // Cargar activos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarActivos();
        });

        async function cargarActivos() {
            try {
                const response = await fetch('/api/activos-fijos/listar.php?id_empresa=<?php echo $idEmpresa; ?>');
                activos = await response.json();
                renderizarActivos(activos);
                actualizarEstadisticas();
            } catch (error) {
                console.error('Error cargando activos:', error);
                document.getElementById('bodyActivos').innerHTML = '<tr><td colspan="11" style="text-align: center; color: red;">Error al cargar activos</td></tr>';
            }
        }

        function renderizarActivos(data) {
            const tbody = document.getElementById('bodyActivos');

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" style="text-align: center;">No hay activos fijos registrados</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(activo => {
                const valorLibro = activo.valor_original - activo.depreciacion_acumulada;
                const porcentajeDepr = ((activo.depreciacion_acumulada / activo.valor_original) * 100).toFixed(1);

                let estadoBadge = '';
                if (activo.estado === 'activo') estadoBadge = '<span class="badge badge-activo">Activo</span>';
                else if (activo.estado === 'depreciado') estadoBadge = '<span class="badge badge-depreciado">Depreciado</span>';
                else if (activo.estado === 'vendido') estadoBadge = '<span class="badge badge-vendido">Vendido</span>';

                return `
                    <tr>
                        <td>${activo.codigo}</td>
                        <td>${activo.descripcion}</td>
                        <td>${activo.categoria}</td>
                        <td>${formatearFecha(activo.fecha_compra)}</td>
                        <td>$${formatearNumero(activo.valor_original)}</td>
                        <td>$${formatearNumero(activo.depreciacion_acumulada)}</td>
                        <td><strong>$${formatearNumero(valorLibro)}</strong></td>
                        <td>${activo.vida_util} años</td>
                        <td>${porcentajeDepr}%</td>
                        <td>${estadoBadge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarActivo(${activo.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="verDepreciacion(${activo.id})">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarActivo(${activo.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarEstadisticas() {
            const totalActivos = activos.length;
            const valorLibroTotal = activos.reduce((sum, a) => sum + (a.valor_original - a.depreciacion_acumulada), 0);
            const depreciacionTotal = activos.reduce((sum, a) => sum + a.depreciacion_acumulada, 0);
            const valorOriginalTotal = activos.reduce((sum, a) => sum + a.valor_original, 0);
            const porcentaje = valorOriginalTotal > 0 ? ((depreciacionTotal / valorOriginalTotal) * 100).toFixed(1) : 0;

            document.getElementById('totalActivos').textContent = totalActivos;
            document.getElementById('valorLibro').textContent = '$' + formatearNumero(valorLibroTotal);
            document.getElementById('depreciacionAcum').textContent = '$' + formatearNumero(depreciacionTotal);
            document.getElementById('porcentajeDepr').textContent = porcentaje + '%';
        }

        function filtrarActivos() {
            const categoria = document.getElementById('filtroCategoria').value;
            const estado = document.getElementById('filtroEstado').value;
            const busqueda = document.getElementById('busqueda').value.toLowerCase();

            const filtrados = activos.filter(activo => {
                return (!categoria || activo.categoria === categoria) &&
                       (!estado || activo.estado === estado) &&
                       (!busqueda || activo.codigo.toLowerCase().includes(busqueda) ||
                        activo.descripcion.toLowerCase().includes(busqueda));
            });

            renderizarActivos(filtrados);
        }

        function abrirModal() {
            document.getElementById('modalActivo').style.display = 'block';
            document.getElementById('formActivo').reset();
            document.getElementById('idActivo').value = '';
            document.getElementById('modalTitulo').textContent = 'Nuevo Activo Fijo';
        }

        function cerrarModal() {
            document.getElementById('modalActivo').style.display = 'none';
        }

        function actualizarVidaUtil() {
            const select = document.getElementById('categoria');
            const option = select.options[select.selectedIndex];
            const vidaUtil = option.getAttribute('data-vida');
            if (vidaUtil) {
                document.getElementById('vidaUtil').value = vidaUtil;
            }
        }

        async function guardarActivo(event) {
            event.preventDefault();

            const datos = {
                id: document.getElementById('idActivo').value,
                id_empresa: <?php echo $idEmpresa; ?>,
                codigo: document.getElementById('codigo').value,
                categoria: document.getElementById('categoria').value,
                descripcion: document.getElementById('descripcion').value,
                fecha_compra: document.getElementById('fechaCompra').value,
                fecha_activacion: document.getElementById('fechaActivacion').value,
                valor_original: parseFloat(document.getElementById('valorOriginal').value),
                valor_residual: parseFloat(document.getElementById('valorResidual').value),
                vida_util: parseInt(document.getElementById('vidaUtil').value),
                metodo_depreciacion: document.getElementById('metodoDepreciacion').value,
                proveedor: document.getElementById('proveedor').value,
                numero_factura: document.getElementById('numeroFactura').value,
                ubicacion: document.getElementById('ubicacion').value,
                responsable: document.getElementById('responsable').value,
                observaciones: document.getElementById('observaciones').value
            };

            try {
                const url = datos.id ? '/api/activos-fijos/actualizar.php' : '/api/activos-fijos/crear.php';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();

                if (result.success) {
                    alert(datos.id ? 'Activo actualizado' : 'Activo creado');
                    cerrarModal();
                    cargarActivos();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar activo');
            }
        }

        async function calcularDepreciacion() {
            if (!confirm('¿Calcular depreciación del mes actual para todos los activos?')) return;

            try {
                const response = await fetch('/api/activos-fijos/calcular-depreciacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_empresa: <?php echo $idEmpresa; ?> })
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Depreciación calculada: $${formatearNumero(result.monto_total)}`);
                    cargarActivos();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al calcular depreciación');
            }
        }

        function formatearNumero(num) {
            return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function formatearFecha(fecha) {
            if (!fecha) return '';
            const d = new Date(fecha);
            return d.toLocaleDateString('es-CL');
        }

        function exportarExcel() {
            window.location.href = '/api/v1/exportacion.php?tabla=activos_fijos&formato=excel&id_empresa=<?php echo $idEmpresa; ?>';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalActivo');
            if (event.target === modal) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
