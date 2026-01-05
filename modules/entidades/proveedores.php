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
    <title>Proveedores - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .proveedores-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #e74c3c; }
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
        .btn-warning { background: #f39c12; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 30px auto; padding: 30px; width: 90%; max-width: 900px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="proveedores-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-truck"></i> Gestión de Proveedores</h1>
            <div>
                <button class="btn btn-success" onclick="nuevoProveedor()">
                    <i class="fas fa-plus"></i> Nuevo Proveedor
                </button>
                <button class="btn btn-primary" onclick="exportarProveedores()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value" id="totalProveedores">0</div>
                <div class="label">Total Proveedores</div>
            </div>
            <div class="stat-card">
                <div class="value" id="proveedoresActivos">0</div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="deudaTotal">$0</div>
                <div class="label">Deuda Total</div>
            </div>
            <div class="stat-card">
                <div class="value" id="comprasUltimos30">$0</div>
                <div class="label">Compras Últimos 30 Días</div>
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
                <label style="font-size: 12px; display: block; margin-bottom: 5px;">País</label>
                <select id="filtroPais">
                    <option value="">Todos</option>
                    <option value="Chile">Chile</option>
                    <option value="Argentina">Argentina</option>
                    <option value="Perú">Perú</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="aplicarFiltros()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>

        <!-- Tabla de Proveedores -->
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <table id="tablaProveedores">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT/DNI</th>
                        <th>Razón Social</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>País</th>
                        <th>Por Pagar</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyProveedores">
                    <tr><td colspan="9" style="text-align: center; padding: 40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Proveedor -->
    <div id="modalProveedor" class="modal">
        <div class="modal-content">
            <div style="border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; margin-bottom: 25px;">
                <h2 id="modalTitulo">Nuevo Proveedor</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px; margin-top: -40px;">&times;</span>
            </div>

            <form id="formProveedor" onsubmit="guardarProveedor(event)">
                <input type="hidden" id="idProveedor">

                <div class="form-grid">
                    <div class="form-group">
                        <label>RUT/DNI *</label>
                        <input type="text" id="rut" required placeholder="12.345.678-9">
                    </div>

                    <div class="form-group">
                        <label>Razón Social *</label>
                        <input type="text" id="razonSocial" required>
                    </div>

                    <div class="form-group">
                        <label>Nombre Fantasía</label>
                        <input type="text" id="nombreFantasia">
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
                        <label>Sitio Web</label>
                        <input type="url" id="sitioweb" placeholder="https://www.ejemplo.cl">
                    </div>

                    <div class="form-group">
                        <label>Contacto Principal</label>
                        <input type="text" id="contactoPrincipal">
                    </div>

                    <div class="form-group">
                        <label>Email Contacto</label>
                        <input type="email" id="emailContacto">
                    </div>

                    <div class="form-group">
                        <label>Teléfono Contacto</label>
                        <input type="text" id="telefonoContacto">
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
                        <label>País *</label>
                        <select id="pais" required>
                            <option value="Chile">Chile</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Perú">Perú</option>
                            <option value="Colombia">Colombia</option>
                            <option value="México">México</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <select id="categoria">
                            <option value="A">A - Estratégico</option>
                            <option value="B">B - Regular</option>
                            <option value="C">C - Ocasional</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Días Plazo Pago</label>
                        <input type="number" id="diasPlazo" value="30">
                    </div>

                    <div class="form-group">
                        <label>Forma de Pago</label>
                        <select id="formaPago">
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" id="banco">
                    </div>

                    <div class="form-group">
                        <label>Tipo Cuenta</label>
                        <select id="tipoCuenta">
                            <option value="">Seleccionar...</option>
                            <option value="corriente">Cuenta Corriente</option>
                            <option value="ahorro">Cuenta de Ahorro</option>
                            <option value="vista">Cuenta Vista</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número Cuenta</label>
                        <input type="text" id="numeroCuenta">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Observaciones</label>
                        <textarea id="observaciones" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="activo" checked style="width: auto;">
                            <span>Proveedor Activo</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="agenteretencion" style="width: auto;">
                            <span>Es Agente de Retención</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 2px solid #ecf0f1; padding-top: 20px;">
                    <button type="button" class="btn" onclick="cerrarModal()" style="background: #95a5a6; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let proveedores = [];

        document.addEventListener('DOMContentLoaded', () => cargarProveedores());

        async function cargarProveedores() {
            try {
                const res = await fetch('/api/proveedores/listar.php');
                proveedores = await res.json();
                renderizar();
                actualizarStats();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar() {
            const tbody = document.getElementById('bodyProveedores');
            if (proveedores.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay proveedores registrados</td></tr>';
                return;
            }

            tbody.innerHTML = proveedores.map(p => {
                const badge = p.activo == 1 ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>';
                const deuda = parseFloat(p.deuda || 0).toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

                return `
                    <tr>
                        <td>${p.id}</td>
                        <td>${p.rut}</td>
                        <td><strong>${p.razon_social}</strong></td>
                        <td>${p.email}</td>
                        <td>${p.telefono || '-'}</td>
                        <td>${p.pais}</td>
                        <td>${deuda}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarProveedor(${p.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarProveedor(${p.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarStats() {
            document.getElementById('totalProveedores').textContent = proveedores.length;
            document.getElementById('proveedoresActivos').textContent = proveedores.filter(p => p.activo == 1).length;

            const deudaTotal = proveedores.reduce((sum, p) => sum + (parseFloat(p.deuda) || 0), 0);
            document.getElementById('deudaTotal').textContent = deudaTotal.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

            const comprasTotal = proveedores.reduce((sum, p) => sum + (parseFloat(p.compras_30d) || 0), 0);
            document.getElementById('comprasUltimos30').textContent = comprasTotal.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});
        }

        function nuevoProveedor() {
            document.getElementById('modalProveedor').style.display = 'block';
            document.getElementById('formProveedor').reset();
            document.getElementById('idProveedor').value = '';
            document.getElementById('modalTitulo').textContent = 'Nuevo Proveedor';
        }

        function cerrarModal() {
            document.getElementById('modalProveedor').style.display = 'none';
        }

        async function guardarProveedor(e) {
            e.preventDefault();

            const datos = {
                id: document.getElementById('idProveedor').value,
                rut: document.getElementById('rut').value,
                razon_social: document.getElementById('razonSocial').value,
                nombre_fantasia: document.getElementById('nombreFantasia').value,
                giro: document.getElementById('giro').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                sitioweb: document.getElementById('sitioweb').value,
                contacto_principal: document.getElementById('contactoPrincipal').value,
                email_contacto: document.getElementById('emailContacto').value,
                telefono_contacto: document.getElementById('telefonoContacto').value,
                direccion: document.getElementById('direccion').value,
                ciudad: document.getElementById('ciudad').value,
                region: document.getElementById('region').value,
                pais: document.getElementById('pais').value,
                categoria: document.getElementById('categoria').value,
                dias_plazo: document.getElementById('diasPlazo').value,
                forma_pago: document.getElementById('formaPago').value,
                banco: document.getElementById('banco').value,
                tipo_cuenta: document.getElementById('tipoCuenta').value,
                numero_cuenta: document.getElementById('numeroCuenta').value,
                observaciones: document.getElementById('observaciones').value,
                activo: document.getElementById('activo').checked ? 1 : 0,
                agente_retencion: document.getElementById('agenteretencion').checked ? 1 : 0
            };

            try {
                const url = datos.id ? '/api/proveedores/actualizar.php' : '/api/proveedores/crear.php';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await res.json();
                if (result.success) {
                    alert(datos.id ? 'Proveedor actualizado' : 'Proveedor creado');
                    cerrarModal();
                    cargarProveedores();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar proveedor');
            }
        }

        async function eliminarProveedor(id) {
            if (!confirm('¿Está seguro de eliminar este proveedor?')) return;

            try {
                const res = await fetch('/api/proveedores/eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await res.json();
                if (result.success) {
                    alert('Proveedor eliminado');
                    cargarProveedores();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al eliminar proveedor');
            }
        }

        function editarProveedor(id) {
            const proveedor = proveedores.find(p => p.id === id);
            if (!proveedor) return;

            document.getElementById('idProveedor').value = proveedor.id;
            document.getElementById('rut').value = proveedor.rut;
            document.getElementById('razonSocial').value = proveedor.razon_social;
            document.getElementById('nombreFantasia').value = proveedor.nombre_fantasia || '';
            document.getElementById('giro').value = proveedor.giro || '';
            document.getElementById('email').value = proveedor.email;
            document.getElementById('telefono').value = proveedor.telefono || '';
            document.getElementById('sitioweb').value = proveedor.sitioweb || '';
            document.getElementById('contactoPrincipal').value = proveedor.contacto_principal || '';
            document.getElementById('emailContacto').value = proveedor.email_contacto || '';
            document.getElementById('telefonoContacto').value = proveedor.telefono_contacto || '';
            document.getElementById('direccion').value = proveedor.direccion || '';
            document.getElementById('ciudad').value = proveedor.ciudad || '';
            document.getElementById('region').value = proveedor.region || '';
            document.getElementById('pais').value = proveedor.pais;
            document.getElementById('categoria').value = proveedor.categoria || 'B';
            document.getElementById('diasPlazo').value = proveedor.dias_plazo || 30;
            document.getElementById('formaPago').value = proveedor.forma_pago || 'transferencia';
            document.getElementById('banco').value = proveedor.banco || '';
            document.getElementById('tipoCuenta').value = proveedor.tipo_cuenta || '';
            document.getElementById('numeroCuenta').value = proveedor.numero_cuenta || '';
            document.getElementById('observaciones').value = proveedor.observaciones || '';
            document.getElementById('activo').checked = proveedor.activo == 1;
            document.getElementById('agenteretencion').checked = proveedor.agente_retencion == 1;

            document.getElementById('modalTitulo').textContent = 'Editar Proveedor';
            document.getElementById('modalProveedor').style.display = 'block';
        }

        function aplicarFiltros() {
            cargarProveedores();
        }

        function exportarProveedores() {
            window.location.href = '/api/exportacion.php?entidad=proveedores&formato=excel';
        }

        window.onclick = (event) => {
            if (event.target == document.getElementById('modalProveedor')) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
