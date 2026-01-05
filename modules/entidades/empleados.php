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
    <title>Empleados - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .empleados-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #9b59b6; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

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
        .modal-content { background: white; margin: 20px auto; padding: 30px; width: 95%; max-width: 1000px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; font-size: 13px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="empleados-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-user-tie"></i> Gestión de Empleados</h1>
            <div>
                <button class="btn btn-success" onclick="nuevoEmpleado()">
                    <i class="fas fa-plus"></i> Nuevo Empleado
                </button>
                <button class="btn btn-primary" onclick="exportarEmpleados()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value" id="totalEmpleados">0</div>
                <div class="label">Total Empleados</div>
            </div>
            <div class="stat-card">
                <div class="value" id="empleadosActivos">0</div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="empleadosInactivos">0</div>
                <div class="label">Inactivos</div>
            </div>
            <div class="stat-card">
                <div class="value" id="nominaMensual">$0</div>
                <div class="label">Nómina Mensual</div>
            </div>
            <div class="stat-card">
                <div class="value" id="promedioSueldo">$0</div>
                <div class="label">Sueldo Promedio</div>
            </div>
        </div>

        <!-- Tabla de Empleados -->
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Listado de Empleados</h3>
            <table id="tablaEmpleados">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT</th>
                        <th>Nombre Completo</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Fecha Ingreso</th>
                        <th>Sueldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyEmpleados">
                    <tr><td colspan="11" style="text-align: center; padding: 40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Empleado -->
    <div id="modalEmpleado" class="modal">
        <div class="modal-content">
            <div style="border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; margin-bottom: 25px;">
                <h2 id="modalTitulo">Nuevo Empleado</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px; margin-top: -40px;">&times;</span>
            </div>

            <form id="formEmpleado" onsubmit="guardarEmpleado(event)">
                <input type="hidden" id="idEmpleado">

                <h4>Datos Personales</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>RUT/DNI *</label>
                        <input type="text" id="rut" required placeholder="12.345.678-9">
                    </div>

                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" id="nombres" required>
                    </div>

                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input type="text" id="apellidos" required>
                    </div>

                    <div class="form-group">
                        <label>Fecha Nacimiento</label>
                        <input type="date" id="fechaNacimiento">
                    </div>

                    <div class="form-group">
                        <label>Sexo</label>
                        <select id="sexo">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado Civil</label>
                        <select id="estadoCivil">
                            <option value="soltero">Soltero(a)</option>
                            <option value="casado">Casado(a)</option>
                            <option value="viudo">Viudo(a)</option>
                            <option value="divorciado">Divorciado(a)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nacionalidad</label>
                        <input type="text" id="nacionalidad" value="Chilena">
                    </div>

                    <div class="form-group">
                        <label>Email Personal *</label>
                        <input type="email" id="email" required>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Dirección</label>
                        <input type="text" id="direccion">
                    </div>

                    <div class="form-group">
                        <label>Comuna/Ciudad</label>
                        <input type="text" id="ciudad">
                    </div>

                    <div class="form-group">
                        <label>Región</label>
                        <input type="text" id="region">
                    </div>
                </div>

                <h4 style="margin-top: 30px;">Datos Laborales</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Cargo *</label>
                        <input type="text" id="cargo" required>
                    </div>

                    <div class="form-group">
                        <label>Departamento</label>
                        <select id="departamento">
                            <option value="Gerencia">Gerencia</option>
                            <option value="Administración">Administración</option>
                            <option value="Finanzas">Finanzas</option>
                            <option value="Ventas">Ventas</option>
                            <option value="Operaciones">Operaciones</option>
                            <option value="RRHH">Recursos Humanos</option>
                            <option value="TI">Tecnología</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fecha Ingreso *</label>
                        <input type="date" id="fechaIngreso" required>
                    </div>

                    <div class="form-group">
                        <label>Tipo Contrato</label>
                        <select id="tipoContrato">
                            <option value="indefinido">Indefinido</option>
                            <option value="plazo_fijo">Plazo Fijo</option>
                            <option value="honorarios">Honorarios</option>
                            <option value="practicante">Practicante</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jornada</label>
                        <select id="jornada">
                            <option value="completa">Jornada Completa</option>
                            <option value="parcial">Media Jornada</option>
                            <option value="turnos">Por Turnos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Sueldo Base *</label>
                        <input type="number" id="sueldoBase" required step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Moneda</label>
                        <select id="moneda">
                            <option value="CLP">CLP</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>AFP</label>
                        <select id="afp">
                            <option value="">Seleccionar...</option>
                            <option value="Capital">AFP Capital</option>
                            <option value="Cuprum">AFP Cuprum</option>
                            <option value="Habitat">AFP Habitat</option>
                            <option value="Modelo">AFP Modelo</option>
                            <option value="PlanVital">AFP PlanVital</option>
                            <option value="Provida">AFP Provida</option>
                            <option value="Uno">AFP UNO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Isapre/Fonasa</label>
                        <select id="isapre">
                            <option value="Fonasa">Fonasa</option>
                            <option value="Banmédica">Banmédica</option>
                            <option value="Colmena">Colmena</option>
                            <option value="Consalud">Consalud</option>
                            <option value="Cruz Blanca">Cruz Blanca</option>
                            <option value="Vida Tres">Vida Tres</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>% Cotización Salud</label>
                        <input type="number" id="cotizacionSalud" value="7" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" id="banco">
                    </div>

                    <div class="form-group">
                        <label>Tipo Cuenta</label>
                        <select id="tipoCuenta">
                            <option value="corriente">Cuenta Corriente</option>
                            <option value="vista">Cuenta Vista</option>
                            <option value="ahorro">Cuenta de Ahorro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número Cuenta</label>
                        <input type="text" id="numeroCuenta">
                    </div>

                    <div class="form-group">
                        <label>Email Corporativo</label>
                        <input type="email" id="emailCorporativo">
                    </div>

                    <div class="form-group">
                        <label>Jefe Directo</label>
                        <select id="jefeDirecto">
                            <option value="">Sin jefe directo</option>
                            <option value="1">Juan Pérez - Gerente</option>
                            <option value="2">María González - Subgerente</option>
                        </select>
                    </div>
                </div>

                <h4 style="margin-top: 30px;">Contacto de Emergencia</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Contacto</label>
                        <input type="text" id="contactoEmergenciaNombre">
                    </div>

                    <div class="form-group">
                        <label>Parentesco</label>
                        <input type="text" id="contactoEmergenciaParentesco">
                    </div>

                    <div class="form-group">
                        <label>Teléfono Emergencia</label>
                        <input type="text" id="contactoEmergenciaTelefono">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="activo" checked style="width: auto;">
                        <span>Empleado Activo</span>
                    </label>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 2px solid #ecf0f1; padding-top: 20px;">
                    <button type="button" class="btn" onclick="cerrarModal()" style="background: #95a5a6; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Empleado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let empleados = [];

        document.addEventListener('DOMContentLoaded', () => cargarEmpleados());

        async function cargarEmpleados() {
            try {
                const res = await fetch('/api/empleados/listar.php');
                empleados = await res.json();
                renderizar();
                actualizarStats();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar() {
            const tbody = document.getElementById('bodyEmpleados');
            if (empleados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" style="text-align: center;">No hay empleados registrados</td></tr>';
                return;
            }

            tbody.innerHTML = empleados.map(e => {
                const badge = e.activo == 1 ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>';
                const sueldo = parseFloat(e.sueldo_base || 0).toLocaleString('es-CL', {style: 'currency', currency: e.moneda || 'CLP'});
                const nombreCompleto = `${e.nombres} ${e.apellidos}`;

                return `
                    <tr>
                        <td>${e.id}</td>
                        <td>${e.rut}</td>
                        <td><strong>${nombreCompleto}</strong></td>
                        <td>${e.cargo}</td>
                        <td>${e.departamento || '-'}</td>
                        <td>${e.email}</td>
                        <td>${e.telefono || '-'}</td>
                        <td>${e.fecha_ingreso ? new Date(e.fecha_ingreso).toLocaleDateString() : '-'}</td>
                        <td>${sueldo}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarEmpleado(${e.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarEmpleado(${e.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarStats() {
            document.getElementById('totalEmpleados').textContent = empleados.length;
            document.getElementById('empleadosActivos').textContent = empleados.filter(e => e.activo == 1).length;
            document.getElementById('empleadosInactivos').textContent = empleados.filter(e => e.activo == 0).length;

            const nominaTotal = empleados.filter(e => e.activo == 1).reduce((sum, e) => sum + (parseFloat(e.sueldo_base) || 0), 0);
            document.getElementById('nominaMensual').textContent = nominaTotal.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});

            const activos = empleados.filter(e => e.activo == 1).length;
            const promedio = activos > 0 ? nominaTotal / activos : 0;
            document.getElementById('promedioSueldo').textContent = promedio.toLocaleString('es-CL', {style: 'currency', currency: 'CLP'});
        }

        function nuevoEmpleado() {
            document.getElementById('modalEmpleado').style.display = 'block';
            document.getElementById('formEmpleado').reset();
            document.getElementById('idEmpleado').value = '';
            document.getElementById('modalTitulo').textContent = 'Nuevo Empleado';
        }

        function cerrarModal() {
            document.getElementById('modalEmpleado').style.display = 'none';
        }

        async function guardarEmpleado(e) {
            e.preventDefault();

            const datos = {
                id: document.getElementById('idEmpleado').value,
                rut: document.getElementById('rut').value,
                nombres: document.getElementById('nombres').value,
                apellidos: document.getElementById('apellidos').value,
                fecha_nacimiento: document.getElementById('fechaNacimiento').value,
                sexo: document.getElementById('sexo').value,
                estado_civil: document.getElementById('estadoCivil').value,
                nacionalidad: document.getElementById('nacionalidad').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                direccion: document.getElementById('direccion').value,
                ciudad: document.getElementById('ciudad').value,
                region: document.getElementById('region').value,
                cargo: document.getElementById('cargo').value,
                departamento: document.getElementById('departamento').value,
                fecha_ingreso: document.getElementById('fechaIngreso').value,
                tipo_contrato: document.getElementById('tipoContrato').value,
                jornada: document.getElementById('jornada').value,
                sueldo_base: document.getElementById('sueldoBase').value,
                moneda: document.getElementById('moneda').value,
                afp: document.getElementById('afp').value,
                isapre: document.getElementById('isapre').value,
                cotizacion_salud: document.getElementById('cotizacionSalud').value,
                banco: document.getElementById('banco').value,
                tipo_cuenta: document.getElementById('tipoCuenta').value,
                numero_cuenta: document.getElementById('numeroCuenta').value,
                email_corporativo: document.getElementById('emailCorporativo').value,
                jefe_directo: document.getElementById('jefeDirecto').value,
                contacto_emergencia_nombre: document.getElementById('contactoEmergenciaNombre').value,
                contacto_emergencia_parentesco: document.getElementById('contactoEmergenciaParentesco').value,
                contacto_emergencia_telefono: document.getElementById('contactoEmergenciaTelefono').value,
                activo: document.getElementById('activo').checked ? 1 : 0
            };

            try {
                const url = datos.id ? '/api/empleados/actualizar.php' : '/api/empleados/crear.php';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await res.json();
                if (result.success) {
                    alert(datos.id ? 'Empleado actualizado' : 'Empleado creado');
                    cerrarModal();
                    cargarEmpleados();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar empleado');
            }
        }

        async function eliminarEmpleado(id) {
            if (!confirm('¿Está seguro de eliminar este empleado?')) return;

            try {
                const res = await fetch('/api/empleados/eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await res.json();
                if (result.success) {
                    alert('Empleado eliminado');
                    cargarEmpleados();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al eliminar empleado');
            }
        }

        function editarEmpleado(id) {
            const empleado = empleados.find(e => e.id === id);
            if (!empleado) return;

            document.getElementById('idEmpleado').value = empleado.id;
            document.getElementById('rut').value = empleado.rut;
            document.getElementById('nombres').value = empleado.nombres;
            document.getElementById('apellidos').value = empleado.apellidos;
            document.getElementById('fechaNacimiento').value = empleado.fecha_nacimiento || '';
            document.getElementById('sexo').value = empleado.sexo || 'M';
            document.getElementById('estadoCivil').value = empleado.estado_civil || 'soltero';
            document.getElementById('nacionalidad').value = empleado.nacionalidad || 'Chilena';
            document.getElementById('email').value = empleado.email;
            document.getElementById('telefono').value = empleado.telefono || '';
            document.getElementById('direccion').value = empleado.direccion || '';
            document.getElementById('ciudad').value = empleado.ciudad || '';
            document.getElementById('region').value = empleado.region || '';
            document.getElementById('cargo').value = empleado.cargo;
            document.getElementById('departamento').value = empleado.departamento || '';
            document.getElementById('fechaIngreso').value = empleado.fecha_ingreso || '';
            document.getElementById('tipoContrato').value = empleado.tipo_contrato || 'indefinido';
            document.getElementById('jornada').value = empleado.jornada || 'completa';
            document.getElementById('sueldoBase').value = empleado.sueldo_base || 0;
            document.getElementById('moneda').value = empleado.moneda || 'CLP';
            document.getElementById('afp').value = empleado.afp || '';
            document.getElementById('isapre').value = empleado.isapre || 'Fonasa';
            document.getElementById('cotizacionSalud').value = empleado.cotizacion_salud || 7;
            document.getElementById('banco').value = empleado.banco || '';
            document.getElementById('tipoCuenta').value = empleado.tipo_cuenta || 'corriente';
            document.getElementById('numeroCuenta').value = empleado.numero_cuenta || '';
            document.getElementById('emailCorporativo').value = empleado.email_corporativo || '';
            document.getElementById('jefeDirecto').value = empleado.jefe_directo || '';
            document.getElementById('contactoEmergenciaNombre').value = empleado.contacto_emergencia_nombre || '';
            document.getElementById('contactoEmergenciaParentesco').value = empleado.contacto_emergencia_parentesco || '';
            document.getElementById('contactoEmergenciaTelefono').value = empleado.contacto_emergencia_telefono || '';
            document.getElementById('activo').checked = empleado.activo == 1;

            document.getElementById('modalTitulo').textContent = 'Editar Empleado';
            document.getElementById('modalEmpleado').style.display = 'block';
        }

        function exportarEmpleados() {
            window.location.href = '/api/exportacion.php?entidad=empleados&formato=excel';
        }

        window.onclick = (event) => {
            if (event.target == document.getElementById('modalEmpleado')) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
