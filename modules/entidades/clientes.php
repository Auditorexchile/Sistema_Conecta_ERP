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
    <title>Clientes - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .clientes-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #3498db; }
        .stat-card .label { font-size: 13px; color: #7f8c8d; margin-top: 8px; }

        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: end; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #ecf0f1; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-inactivo { background: #f8d7da; color: #721c24; }

        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 30px auto; padding: 30px; width: 90%; max-width: 900px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus { border-color: #3498db; outline: none; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="clientes-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-users"></i> Gestión de Clientes</h1>
            <div>
                <button class="btn btn-success" onclick="nuevoCliente()">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
                <button class="btn btn-primary" onclick="exportarClientes()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value" id="totalClientes">0</div>
                <div class="label">Total Clientes</div>
            </div>
            <div class="stat-card">
                <div class="value" id="clientesActivos">0</div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="deudaTotal">$0</div>
                <div class="label">Deuda Total</div>
            </div>
            <div class="stat-card">
                <div class="value" id="ventasUltimos30">$0</div>
                <div class="label">Ventas Últimos 30 Días</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Buscar</label>
                <input type="text" id="buscar" placeholder="Nombre, RUT, Email..." style="width: 300px;">
            </div>
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Estado</label>
                <select id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">Tipo</label>
                <select id="filtroTipo">
                    <option value="">Todos</option>
                    <option value="persona">Persona</option>
                    <option value="empresa">Empresa</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="aplicarFiltros()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>

        <!-- Tabla de Clientes -->
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <table id="tablaClientes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT/DNI</th>
                        <th>Razón Social/Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th>Deuda</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyClientes">
                    <tr><td colspan="9" style="text-align: center; padding: 40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Cliente -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <div style="border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; margin-bottom: 25px;">
                <h2 id="modalTitulo">Nuevo Cliente</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px; margin-top: -40px;">&times;</span>
            </div>

            <form id="formCliente" onsubmit="guardarCliente(event)">
                <input type="hidden" id="idCliente">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tipo *</label>
                        <select id="tipo" required onchange="cambiarTipo()">
                            <option value="persona">Persona Natural</option>
                            <option value="empresa">Empresa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>RUT/DNI *</label>
                        <input type="text" id="rut" required placeholder="12.345.678-9">
                    </div>

                    <div class="form-group" id="grupoRazonSocial">
                        <label>Razón Social *</label>
                        <input type="text" id="razonSocial">
                    </div>

                    <div class="form-group" id="grupoNombreFantasia">
                        <label>Nombre Fantasía</label>
                        <input type="text" id="nombreFantasia">
                    </div>

                    <div class="form-group" id="grupoNombres" style="display:none;">
                        <label>Nombres *</label>
                        <input type="text" id="nombres">
                    </div>

                    <div class="form-group" id="grupoApellidos" style="display:none;">
                        <label>Apellidos *</label>
                        <input type="text" id="apellidos">
                    </div>

                    <div class="form-group">
                        <label>Giro/Actividad</label>
                        <input type="text" id="giro">
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="email" required>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono">
                    </div>

                    <div class="form-group">
                        <label>Celular</label>
                        <input type="text" id="celular">
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" id="direccion">
                    </div>

                    <div class="form-group">
                        <label>Comuna/Ciudad</label>
                        <input type="text" id="ciudad">
                    </div>

                    <div class="form-group">
                        <label>Región/Estado</label>
                        <input type="text" id="region">
                    </div>

                    <div class="form-group">
                        <label>País</label>
                        <select id="pais">
                            <option value="Chile">Chile</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Perú">Perú</option>
                            <option value="Colombia">Colombia</option>
                            <option value="México">México</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Categoría Cliente</label>
                        <select id="categoria">
                            <option value="A">A - Premium</option>
                            <option value="B">B - Regular</option>
                            <option value="C">C - Ocasional</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Límite de Crédito</label>
                        <input type="number" id="limiteCredito" value="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Días Plazo Pago</label>
                        <input type="number" id="diasPlazo" value="30">
                    </div>

                    <div class="form-group">
                        <label>Vendedor Asignado</label>
                        <select id="vendedor">
                            <option value="">Sin asignar</option>
                            <option value="1">Juan Pérez</option>
                            <option value="2">María González</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Observaciones</label>
                        <textarea id="observaciones" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="activo" checked style="width: auto;">
                            <span>Cliente Activo</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 2px solid #ecf0f1; padding-top: 20px;">
                    <button type="button" class="btn" onclick="cerrarModal()" style="background: #95a5a6; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let clientes = [];

        document.addEventListener('DOMContentLoaded', () => cargarClientes());

        async function cargarClientes() {
            try {
                const res = await fetch('/api/clientes/listar.php');
                clientes = await res.json();
                renderizar();
                actualizarStats();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar() {
            const tbody = document.getElementById('bodyClientes');
            if (clientes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay clientes registrados</td></tr>';
                return;
            }

            tbody.innerHTML = clientes.map(c => {
                const badge = c.activo == 1 ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>';
                const deuda = parseFloat(c.deuda || 0).toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

                return `
                    <tr>
                        <td>${c.id}</td>
                        <td>${c.rut}</td>
                        <td><strong>${c.razon_social || c.nombres + ' ' + c.apellidos}</strong></td>
                        <td>${c.email}</td>
                        <td>${c.telefono || '-'}</td>
                        <td>${c.ciudad || '-'}</td>
                        <td>${deuda}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarCliente(${c.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarCliente(${c.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarStats() {
            document.getElementById('totalClientes').textContent = clientes.length;
            document.getElementById('clientesActivos').textContent = clientes.filter(c => c.activo == 1).length;

            const deudaTotal = clientes.reduce((sum, c) => sum + (parseFloat(c.deuda) || 0), 0);
            document.getElementById('deudaTotal').textContent = deudaTotal.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

            const ventasTotal = clientes.reduce((sum, c) => sum + (parseFloat(c.ventas_30d) || 0), 0);
            document.getElementById('ventasUltimos30').textContent = ventasTotal.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});
        }

        function nuevoCliente() {
            document.getElementById('modalCliente').style.display = 'block';
            document.getElementById('formCliente').reset();
            document.getElementById('idCliente').value = '';
            document.getElementById('modalTitulo').textContent = 'Nuevo Cliente';
            cambiarTipo();
        }

        function cerrarModal() {
            document.getElementById('modalCliente').style.display = 'none';
        }

        function cambiarTipo() {
            const tipo = document.getElementById('tipo').value;
            if (tipo === 'empresa') {
                document.getElementById('grupoRazonSocial').style.display = 'block';
                document.getElementById('grupoNombreFantasia').style.display = 'block';
                document.getElementById('grupoNombres').style.display = 'none';
                document.getElementById('grupoApellidos').style.display = 'none';
            } else {
                document.getElementById('grupoRazonSocial').style.display = 'none';
                document.getElementById('grupoNombreFantasia').style.display = 'none';
                document.getElementById('grupoNombres').style.display = 'block';
                document.getElementById('grupoApellidos').style.display = 'block';
            }
        }

        async function guardarCliente(e) {
            e.preventDefault();

            const datos = {
                id: document.getElementById('idCliente').value,
                tipo: document.getElementById('tipo').value,
                rut: document.getElementById('rut').value,
                razon_social: document.getElementById('razonSocial').value,
                nombre_fantasia: document.getElementById('nombreFantasia').value,
                nombres: document.getElementById('nombres').value,
                apellidos: document.getElementById('apellidos').value,
                giro: document.getElementById('giro').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                celular: document.getElementById('celular').value,
                direccion: document.getElementById('direccion').value,
                ciudad: document.getElementById('ciudad').value,
                region: document.getElementById('region').value,
                pais: document.getElementById('pais').value,
                categoria: document.getElementById('categoria').value,
                limite_credito: document.getElementById('limiteCredito').value,
                dias_plazo: document.getElementById('diasPlazo').value,
                id_vendedor: document.getElementById('vendedor').value,
                observaciones: document.getElementById('observaciones').value,
                activo: document.getElementById('activo').checked ? 1 : 0
            };

            try {
                const url = datos.id ? '/api/clientes/actualizar.php' : '/api/clientes/crear.php';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await res.json();
                if (result.success) {
                    alert(datos.id ? 'Cliente actualizado' : 'Cliente creado');
                    cerrarModal();
                    cargarClientes();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar cliente');
            }
        }

        async function eliminarCliente(id) {
            if (!confirm('¿Está seguro de eliminar este cliente?')) return;

            try {
                const res = await fetch('/api/clientes/eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await res.json();
                if (result.success) {
                    alert('Cliente eliminado');
                    cargarClientes();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al eliminar cliente');
            }
        }

        function editarCliente(id) {
            const cliente = clientes.find(c => c.id === id);
            if (!cliente) return;

            document.getElementById('idCliente').value = cliente.id;
            document.getElementById('tipo').value = cliente.tipo;
            document.getElementById('rut').value = cliente.rut;
            document.getElementById('razonSocial').value = cliente.razon_social || '';
            document.getElementById('nombreFantasia').value = cliente.nombre_fantasia || '';
            document.getElementById('nombres').value = cliente.nombres || '';
            document.getElementById('apellidos').value = cliente.apellidos || '';
            document.getElementById('giro').value = cliente.giro || '';
            document.getElementById('email').value = cliente.email;
            document.getElementById('telefono').value = cliente.telefono || '';
            document.getElementById('celular').value = cliente.celular || '';
            document.getElementById('direccion').value = cliente.direccion || '';
            document.getElementById('ciudad').value = cliente.ciudad || '';
            document.getElementById('region').value = cliente.region || '';
            document.getElementById('pais').value = cliente.pais;
            document.getElementById('categoria').value = cliente.categoria || 'B';
            document.getElementById('limiteCredito').value = cliente.limite_credito || 0;
            document.getElementById('diasPlazo').value = cliente.dias_plazo || 30;
            document.getElementById('vendedor').value = cliente.id_vendedor || '';
            document.getElementById('observaciones').value = cliente.observaciones || '';
            document.getElementById('activo').checked = cliente.activo == 1;

            cambiarTipo();
            document.getElementById('modalTitulo').textContent = 'Editar Cliente';
            document.getElementById('modalCliente').style.display = 'block';
        }

        function aplicarFiltros() {
            cargarClientes();
        }

        function exportarClientes() {
            window.location.href = '/api/exportacion.php?entidad=clientes&formato=excel';
        }

        window.onclick = (event) => {
            if (event.target == document.getElementById('modalCliente')) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
