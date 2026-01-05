<?php
session_start();
require_once '../../app/core/Database.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'superadmin') {
    header('Location: /login.php');
    exit;
}

$db = \App\Core\Database::getInstance();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empresas - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .empresas-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 36px; font-weight: bold; color: #3498db; }
        .stat-card .label { font-size: 14px; color: #7f8c8d; margin-top: 10px; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #2c3e50; color: white; padding: 15px; text-align: left; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #ecf0f1; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-trial { background: #fff3cd; color: #856404; }
        .badge-suspended { background: #f8d7da; color: #721c24; }

        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 50px auto; padding: 30px; width: 90%; max-width: 800px; border-radius: 8px; max-height: 85vh; overflow-y: auto; }
        .modal-header { border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; margin-bottom: 25px; }

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

    <div class="empresas-container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i class="fas fa-building"></i> Gestión de Empresas</h1>
            <button class="btn btn-primary" onclick="nuevaEmpresa()">
                <i class="fas fa-plus"></i> Nueva Empresa
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value" id="totalEmpresas">0</div>
                <div class="label">Total Empresas</div>
            </div>
            <div class="stat-card">
                <div class="value" id="empresasActivas">0</div>
                <div class="label">Activas</div>
            </div>
            <div class="stat-card">
                <div class="value" id="empresasTrial">0</div>
                <div class="label">En Prueba</div>
            </div>
            <div class="stat-card">
                <div class="value" id="usuariosTotales">0</div>
                <div class="label">Usuarios Totales</div>
            </div>
        </div>

        <!-- Tabla de Empresas -->
        <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 20px;">Empresas Registradas</h3>
            <table id="tablaEmpresas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Razón Social</th>
                        <th>RUT</th>
                        <th>País</th>
                        <th>Plan</th>
                        <th>Usuarios</th>
                        <th>Trial Hasta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyEmpresas">
                    <tr><td colspan="9" style="text-align: center; padding: 40px;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nueva/Editar Empresa -->
    <div id="modalEmpresa" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nueva Empresa</h2>
                <span class="close" onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
            </div>

            <form id="formEmpresa" onsubmit="guardarEmpresa(event)">
                <input type="hidden" id="idEmpresa">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Razón Social *</label>
                        <input type="text" id="razonSocial" required>
                    </div>

                    <div class="form-group">
                        <label>Nombre Fantasía</label>
                        <input type="text" id="nombreFantasia">
                    </div>

                    <div class="form-group">
                        <label>RUT *</label>
                        <input type="text" id="rut" required>
                    </div>

                    <div class="form-group">
                        <label>Giro</label>
                        <input type="text" id="giro">
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
                        <label>Plan *</label>
                        <select id="plan" required>
                            <option value="1">Básico</option>
                            <option value="2">Profesional</option>
                            <option value="3">Empresarial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email">
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Dirección</label>
                        <textarea id="direccion" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Días Trial</label>
                        <input type="number" id="diasTrial" value="14">
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select id="estado">
                            <option value="1">Activa</option>
                            <option value="0">Suspendida</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 2px solid #ecf0f1; padding-top: 20px;">
                    <button type="button" class="btn" onclick="cerrarModal()" style="background: #95a5a6; color: white; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let empresas = [];

        document.addEventListener('DOMContentLoaded', () => cargarEmpresas());

        async function cargarEmpresas() {
            try {
                const res = await fetch('/api/empresas/listar.php');
                empresas = await res.json();
                renderizar();
                actualizarStats();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderizar() {
            const tbody = document.getElementById('bodyEmpresas');
            if (empresas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay empresas registradas</td></tr>';
                return;
            }

            tbody.innerHTML = empresas.map(e => {
                let badge = e.activa == 1 ? '<span class="badge badge-active">Activa</span>' : '<span class="badge badge-suspended">Suspendida</span>';
                if (e.trial_hasta && new Date(e.trial_hasta) > new Date()) {
                    badge = '<span class="badge badge-trial">Trial</span>';
                }

                return `
                    <tr>
                        <td>${e.id}</td>
                        <td><strong>${e.razon_social}</strong></td>
                        <td>${e.rut}</td>
                        <td>${e.pais}</td>
                        <td>${e.plan_nombre || 'N/A'}</td>
                        <td>${e.usuarios_count || 0}</td>
                        <td>${e.trial_hasta ? new Date(e.trial_hasta).toLocaleDateString() : 'N/A'}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editarEmpresa(${e.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarEmpresa(${e.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function actualizarStats() {
            document.getElementById('totalEmpresas').textContent = empresas.length;
            document.getElementById('empresasActivas').textContent = empresas.filter(e => e.activa == 1).length;
            document.getElementById('empresasTrial').textContent = empresas.filter(e => e.trial_hasta && new Date(e.trial_hasta) > new Date()).length;
            document.getElementById('usuariosTotales').textContent = empresas.reduce((sum, e) => sum + (parseInt(e.usuarios_count) || 0), 0);
        }

        function nuevaEmpresa() {
            document.getElementById('modalEmpresa').style.display = 'block';
            document.getElementById('formEmpresa').reset();
            document.getElementById('idEmpresa').value = '';
            document.getElementById('modalTitulo').textContent = 'Nueva Empresa';
        }

        function cerrarModal() {
            document.getElementById('modalEmpresa').style.display = 'none';
        }

        async function guardarEmpresa(e) {
            e.preventDefault();

            const datos = {
                id: document.getElementById('idEmpresa').value,
                razon_social: document.getElementById('razonSocial').value,
                nombre_fantasia: document.getElementById('nombreFantasia').value,
                rut: document.getElementById('rut').value,
                giro: document.getElementById('giro').value,
                pais: document.getElementById('pais').value,
                id_plan: document.getElementById('plan').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                direccion: document.getElementById('direccion').value,
                dias_trial: document.getElementById('diasTrial').value,
                activa: document.getElementById('estado').value
            };

            try {
                const url = datos.id ? '/api/empresas/actualizar.php' : '/api/empresas/crear.php';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await res.json();
                if (result.success) {
                    alert(datos.id ? 'Empresa actualizada' : 'Empresa creada');
                    cerrarModal();
                    cargarEmpresas();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar empresa');
            }
        }

        async function eliminarEmpresa(id) {
            if (!confirm('¿Está seguro de eliminar esta empresa? Esta acción no se puede deshacer.')) return;

            try {
                const res = await fetch('/api/empresas/eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await res.json();
                if (result.success) {
                    alert('Empresa eliminada');
                    cargarEmpresas();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al eliminar empresa');
            }
        }

        function editarEmpresa(id) {
            const empresa = empresas.find(e => e.id === id);
            if (!empresa) return;

            document.getElementById('idEmpresa').value = empresa.id;
            document.getElementById('razonSocial').value = empresa.razon_social;
            document.getElementById('nombreFantasia').value = empresa.nombre_fantasia || '';
            document.getElementById('rut').value = empresa.rut;
            document.getElementById('giro').value = empresa.giro || '';
            document.getElementById('pais').value = empresa.pais;
            document.getElementById('plan').value = empresa.id_plan;
            document.getElementById('email').value = empresa.email || '';
            document.getElementById('telefono').value = empresa.telefono || '';
            document.getElementById('direccion').value = empresa.direccion || '';
            document.getElementById('estado').value = empresa.activa;

            document.getElementById('modalTitulo').textContent = 'Editar Empresa';
            document.getElementById('modalEmpresa').style.display = 'block';
        }

        window.onclick = (event) => {
            if (event.target == document.getElementById('modalEmpresa')) {
                cerrarModal();
            }
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
