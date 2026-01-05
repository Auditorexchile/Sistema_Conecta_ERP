<?php
session_start();
require_once '../app/core/Database.php';

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
    <title>Comprobantes Contables - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .comprobantes-container { padding: 20px; }
        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .table-container { background: white; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; }
        .badge-borrador { background: #6c757d; color: white; }
        .badge-aprobado { background: #28a745; color: white; }
        .badge-anulado { background: #dc3545; color: white; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; margin: 0 2px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 30px auto; padding: 30px; width: 95%; max-width: 1200px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }

        .comprobante-form { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .detalle-table { margin-top: 20px; }
        .detalle-table table { width: 100%; }
        .detalle-table th { background: #34495e; }
        .totales { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .totales-col { background: #f8f9fa; padding: 15px; border-radius: 4px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6; }
        .total-row.grand { font-size: 18px; font-weight: bold; color: #28a745; }

        .cuadrado { text-align: center; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .cuadrado.ok { background: #d4edda; color: #155724; }
        .cuadrado.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="comprobantes-container">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice"></i> Comprobantes Contables</h1>
            <button class="btn btn-primary" onclick="nuevoComprobante()">
                <i class="fas fa-plus"></i> Nuevo Comprobante
            </button>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <input type="date" id="fechaDesde" value="<?php echo date('Y-m-01'); ?>">
            <input type="date" id="fechaHasta" value="<?php echo date('Y-m-t'); ?>">

            <select id="filtroTipo" onchange="filtrar()">
                <option value="">Todos los Tipos</option>
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
                <option value="traspaso">Traspaso</option>
                <option value="ajuste">Ajuste</option>
                <option value="apertura">Apertura</option>
                <option value="cierre">Cierre</option>
            </select>

            <select id="filtroEstado" onchange="filtrar()">
                <option value="">Todos los Estados</option>
                <option value="borrador">Borrador</option>
                <option value="aprobado">Aprobado</option>
                <option value="anulado">Anulado</option>
            </select>

            <input type="text" id="busqueda" placeholder="Buscar..." onkeyup="filtrar()">

            <button class="btn btn-success" onclick="filtrar()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table id="tablaComprobantes">
                <thead>
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Glosa</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyComprobantes">
                    <tr><td colspan="9" style="text-align:center;padding:40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Comprobante -->
    <div id="modalComprobante" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nuevo Comprobante Contable</h2>
                <span class="close" onclick="cerrarModal()" style="float:right;cursor:pointer;font-size:28px;">&times;</span>
            </div>

            <form id="formComprobante">
                <input type="hidden" id="idComprobante">

                <div class="comprobante-form">
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date" id="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Tipo *</label>
                        <select id="tipo" required>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                            <option value="traspaso">Traspaso</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="apertura">Apertura</option>
                            <option value="cierre">Cierre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>N° Documento</label>
                        <input type="text" id="numeroDocumento">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Glosa *</label>
                        <textarea id="glosa" rows="2" required></textarea>
                    </div>
                </div>

                <!-- Detalle -->
                <div class="detalle-table">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <h3>Detalle del Comprobante</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="agregarLinea()">
                            <i class="fas fa-plus"></i> Agregar Línea
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th width="40%">Cuenta Contable</th>
                                <th width="20%">Debe</th>
                                <th width="20%">Haber</th>
                                <th width="15%">Centro Costo</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="detalleComprobante">
                            <!-- Se agregan dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="totales">
                    <div class="totales-col">
                        <div class="total-row">
                            <span>Total Debe:</span>
                            <strong id="totalDebe">$0.00</strong>
                        </div>
                    </div>
                    <div class="totales-col">
                        <div class="total-row">
                            <span>Total Haber:</span>
                            <strong id="totalHaber">$0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="cuadrado" id="estadoCuadrado">
                    <i class="fas fa-info-circle"></i> Debe y Haber deben ser iguales
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarBorrador()">Guardar Borrador</button>
                    <button type="button" class="btn btn-success" onclick="aprobarComprobante()">Aprobar y Contabilizar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let comprobantes = [];
        let cuentasContables = [];
        let lineaIndex = 0;

        document.addEventListener('DOMContentLoaded', function() {
            cargarCuentas();
            cargarComprobantes();
        });

        async function cargarCuentas() {
            const response = await fetch('/api/contabilidad/cuentas.php?id_empresa=<?php echo $idEmpresa; ?>');
            cuentasContables = await response.json();
        }

        async function cargarComprobantes() {
            try {
                const fechaDesde = document.getElementById('fechaDesde').value;
                const fechaHasta = document.getElementById('fechaHasta').value;

                const response = await fetch(`/api/comprobantes/listar.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`);
                comprobantes = await response.json();
                renderizar(comprobantes);
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar(data) {
            const tbody = document.getElementById('bodyComprobantes');

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No hay comprobantes</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(c => {
                let badge = '';
                if (c.estado === 'borrador') badge = '<span class="badge badge-borrador">Borrador</span>';
                else if (c.estado === 'aprobado') badge = '<span class="badge badge-aprobado">Aprobado</span>';
                else if (c.estado === 'anulado') badge = '<span class="badge badge-anulado">Anulado</span>';

                return `
                    <tr>
                        <td><strong>${c.numero_comprobante}</strong></td>
                        <td>${formatFecha(c.fecha)}</td>
                        <td>${c.tipo}</td>
                        <td>${c.glosa}</td>
                        <td>$${formatNum(c.total_debe)}</td>
                        <td>$${formatNum(c.total_haber)}</td>
                        <td>${badge}</td>
                        <td>${c.usuario}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="verComprobante(${c.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${c.estado === 'borrador' ? `
                                <button class="btn btn-success btn-sm" onclick="editarComprobante(${c.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function nuevoComprobante() {
            document.getElementById('modalComprobante').style.display = 'block';
            document.getElementById('formComprobante').reset();
            document.getElementById('detalleComprobante').innerHTML = '';
            agregarLinea();
            agregarLinea();
        }

        function cerrarModal() {
            document.getElementById('modalComprobante').style.display = 'none';
        }

        function agregarLinea() {
            const tbody = document.getElementById('detalleComprobante');
            const index = lineaIndex++;

            const optionsCuentas = cuentasContables.map(c =>
                `<option value="${c.id}">${c.codigo} - ${c.nombre}</option>`
            ).join('');

            tbody.innerHTML += `
                <tr id="linea_${index}">
                    <td>
                        <select class="cuenta-select" onchange="calcularTotales()">
                            <option value="">Seleccionar cuenta...</option>
                            ${optionsCuentas}
                        </select>
                    </td>
                    <td><input type="number" class="debe-input" step="0.01" value="0" onchange="calcularTotales()"></td>
                    <td><input type="number" class="haber-input" step="0.01" value="0" onchange="calcularTotales()"></td>
                    <td><input type="text" class="centro-costo"></td>
                    <td>
                        <button type="button" class="btn btn-sm" onclick="eliminarLinea(${index})" style="background:#dc3545;color:white;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            calcularTotales();
        }

        function eliminarLinea(index) {
            document.getElementById(`linea_${index}`).remove();
            calcularTotales();
        }

        function calcularTotales() {
            const debeInputs = document.querySelectorAll('.debe-input');
            const haberInputs = document.querySelectorAll('.haber-input');

            let totalDebe = 0;
            let totalHaber = 0;

            debeInputs.forEach(input => totalDebe += parseFloat(input.value) || 0);
            haberInputs.forEach(input => totalHaber += parseFloat(input.value) || 0);

            document.getElementById('totalDebe').textContent = '$' + formatNum(totalDebe);
            document.getElementById('totalHaber').textContent = '$' + formatNum(totalHaber);

            const cuadrado = document.getElementById('estadoCuadrado');
            const diferencia = Math.abs(totalDebe - totalHaber);

            if (diferencia < 0.01 && totalDebe > 0) {
                cuadrado.className = 'cuadrado ok';
                cuadrado.innerHTML = '<i class="fas fa-check-circle"></i> Comprobante cuadrado';
            } else {
                cuadrado.className = 'cuadrado error';
                cuadrado.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Descuadre: $${formatNum(diferencia)}`;
            }
        }

        async function aprobarComprobante() {
            const totalDebe = parseFloat(document.getElementById('totalDebe').textContent.replace(/[$,]/g, ''));
            const totalHaber = parseFloat(document.getElementById('totalHaber').textContent.replace(/[$,]/g, ''));

            if (Math.abs(totalDebe - totalHaber) > 0.01) {
                alert('El comprobante no está cuadrado. Debe y Haber deben ser iguales.');
                return;
            }

            if (!confirm('¿Aprobar y contabilizar este comprobante?')) return;

            // Guardar con estado aprobado
            await guardar('aprobado');
        }

        async function guardarBorrador() {
            await guardar('borrador');
        }

        async function guardar(estado) {
            const detalle = [];
            const tbody = document.getElementById('detalleComprobante');
            const rows = tbody.querySelectorAll('tr');

            rows.forEach(row => {
                const cuenta = row.querySelector('.cuenta-select').value;
                const debe = parseFloat(row.querySelector('.debe-input').value) || 0;
                const haber = parseFloat(row.querySelector('.haber-input').value) || 0;

                if (cuenta && (debe > 0 || haber > 0)) {
                    detalle.push({ id_cuenta: cuenta, debe, haber });
                }
            });

            if (detalle.length === 0) {
                alert('Debe agregar al menos una línea con cuenta y monto');
                return;
            }

            const datos = {
                id_empresa: <?php echo $idEmpresa; ?>,
                fecha: document.getElementById('fecha').value,
                tipo: document.getElementById('tipo').value,
                numero_documento: document.getElementById('numeroDocumento').value,
                glosa: document.getElementById('glosa').value,
                estado: estado,
                detalle: detalle
            };

            try {
                const response = await fetch('/api/comprobantes/crear.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Comprobante ' + (estado === 'aprobado' ? 'aprobado' : 'guardado'));
                    cerrarModal();
                    cargarComprobantes();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar');
            }
        }

        function formatNum(num) {
            return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function formatFecha(f) {
            return new Date(f).toLocaleDateString('es-CL');
        }

        function filtrar() {
            cargarComprobantes();
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
