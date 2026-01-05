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
    <title>Seguridad y Permisos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .seguridad-container { padding: 20px; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #3498db; color: white; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        
        .permission-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .permission-item { padding: 10px; background: #f8f9fa; border-radius: 4px; }
        .permission-item label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .permission-item input[type="checkbox"] { width: 18px; height: 18px; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="seguridad-container">
        <h1><i class="fas fa-shield-alt"></i> Seguridad y Permisos</h1>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('roles')">Roles</button>
            <button class="tab" onclick="cambiarTab('permisos')">Permisos</button>
            <button class="tab" onclick="cambiarTab('auditoria')">Auditoría</button>
            <button class="tab" onclick="cambiarTab('sesiones')">Sesiones Activas</button>
        </div>

        <!-- Tab Roles -->
        <div id="tab-roles" class="tab-content active">
            <div class="card">
                <h3>Roles de Usuario</h3>
                <button class="btn btn-primary" onclick="nuevoRol()">
                    <i class="fas fa-plus"></i> Nuevo Rol
                </button>
                
                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Descripción</th>
                            <th>Usuarios</th>
                            <th>Permisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="rolesBody">
                        <tr>
                            <td><strong>Administrador</strong></td>
                            <td>Acceso total al sistema</td>
                            <td>3</td>
                            <td>45/45</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Contador</strong></td>
                            <td>Acceso a módulos financieros</td>
                            <td>5</td>
                            <td>28/45</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Vendedor</strong></td>
                            <td>Acceso a ventas y clientes</td>
                            <td>12</td>
                            <td>15/45</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Permisos -->
        <div id="tab-permisos" class="tab-content">
            <div class="card">
                <h3>Matriz de Permisos</h3>
                <p>Seleccione un rol para configurar sus permisos:</p>
                
                <select id="rolSelect" style="padding: 8px; margin: 20px 0;">
                    <option value="1">Administrador</option>
                    <option value="2">Contador</option>
                    <option value="3">Vendedor</option>
                </select>

                <h4 style="margin-top: 30px;">Permisos del Sistema</h4>
                <div class="permission-grid" id="permisosGrid">
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Ver Dashboard</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Gestionar Empresas</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Ver Usuarios</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Crear Usuarios</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Editar Usuarios</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Eliminar Usuarios</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Ver Clientes</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox" checked> Crear Clientes</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox"> Ver Reportes</label>
                    </div>
                    <div class="permission-item">
                        <label><input type="checkbox"> Exportar Datos</label>
                    </div>
                </div>

                <button class="btn btn-success" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Guardar Permisos
                </button>
            </div>
        </div>

        <!-- Tab Auditoría -->
        <div id="tab-auditoria" class="tab-content">
            <div class="card">
                <h3>Log de Auditoría</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Módulo</th>
                            <th>IP</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo date('d/m/Y H:i'); ?></td>
                            <td>admin@empresa.cl</td>
                            <td>LOGIN</td>
                            <td>Sistema</td>
                            <td>192.168.1.100</td>
                            <td>Login exitoso</td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y H:i'); ?></td>
                            <td>contador@empresa.cl</td>
                            <td>CREATE</td>
                            <td>Facturas</td>
                            <td>192.168.1.101</td>
                            <td>Factura N° 12345 creada</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Sesiones -->
        <div id="tab-sesiones" class="tab-content">
            <div class="card">
                <h3>Sesiones Activas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>IP</th>
                            <th>Navegador</th>
                            <th>Inicio Sesión</th>
                            <th>Última Actividad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>admin@empresa.cl</td>
                            <td>192.168.1.100</td>
                            <td>Chrome 120</td>
                            <td><?php echo date('d/m/Y H:i'); ?></td>
                            <td>Hace 2 minutos</td>
                            <td>
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }

        function nuevoRol() {
            alert('Funcionalidad de crear rol');
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
