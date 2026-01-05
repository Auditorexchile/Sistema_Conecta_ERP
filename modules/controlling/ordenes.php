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
    <title>Órdenes de Trabajo - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ordenes-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #f39c12; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #f39c12; color: white; }

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

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-proceso { background: #d1ecf1; color: #0c5460; }
        .badge-completada { background: #d4edda; color: #155724; }
        .badge-cerrada { background: #d3d3d3; color: #6c757d; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="ordenes-container">
        <h1><i class="fas fa-tasks"></i> Órdenes de Trabajo</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">145</div>
                <div class="label">Total Órdenes</div>
            </div>
            <div class="stat-card">
                <div class="value">32</div>
                <div class="label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="value">58</div>
                <div class="label">En Proceso</div>
            </div>
            <div class="stat-card">
                <div class="value">48</div>
                <div class="label">Completadas</div>
            </div>
            <div class="stat-card">
                <div class="value">85.2%</div>
                <div class="label">Cumplimiento Plazo</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('listado')">Listado de Órdenes</button>
            <button class="tab" onclick="cambiarTab('gantt')">Diagrama Gantt</button>
            <button class="tab" onclick="cambiarTab('recursos')">Asignación Recursos</button>
            <button class="tab" onclick="cambiarTab('costos')">Costos por Orden</button>
        </div>

        <!-- Tab Listado -->
        <div id="tab-listado" class="tab-content active">
            <div class="card">
                <h3>Órdenes de Trabajo</h3>
                <button class="btn btn-primary" onclick="nuevaOrden()">
                    <i class="fas fa-plus"></i> Nueva Orden
                </button>
                <button class="btn btn-success" onclick="exportarOrdenes()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Orden</th>
                            <th>Cliente</th>
                            <th>Descripción</th>
                            <th>Responsable</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Término</th>
                            <th>Avance %</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>OT-2026-001</strong></td>
                            <td>Empresa ABC S.A.</td>
                            <td>Desarrollo Sistema Web</td>
                            <td>Juan Pérez</td>
                            <td>01/01/2026</td>
                            <td>31/01/2026</td>
                            <td>75%</td>
                            <td><span class="badge badge-proceso">En Proceso</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>OT-2026-002</strong></td>
                            <td>Cliente XYZ Ltda.</td>
                            <td>Mantenimiento Equipos</td>
                            <td>María González</td>
                            <td>03/01/2026</td>
                            <td>05/01/2026</td>
                            <td>100%</td>
                            <td><span class="badge badge-completada">Completada</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>OT-2026-003</strong></td>
                            <td>Comercializadora 123</td>
                            <td>Consultoría Procesos</td>
                            <td>Carlos Silva</td>
                            <td>05/01/2026</td>
                            <td>15/02/2026</td>
                            <td>0%</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-play"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Gantt -->
        <div id="tab-gantt" class="tab-content">
            <div class="card">
                <h3>Diagrama de Gantt - Planificación de Órdenes</h3>
                <p style="color: #7f8c8d; margin-top: 10px;">Visualización de cronograma de órdenes de trabajo</p>
                <div style="margin-top: 20px; padding: 100px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #7f8c8d;">
                    <i class="fas fa-chart-bar" style="font-size: 64px; margin-bottom: 20px;"></i>
                    <p>Diagrama de Gantt interactivo</p>
                    <p style="font-size: 12px;">(Integración con biblioteca JS de Gantt)</p>
                </div>
            </div>
        </div>

        <!-- Tab Recursos -->
        <div id="tab-recursos" class="tab-content">
            <div class="card">
                <h3>Asignación de Recursos</h3>
                
                <h4 style="margin-top: 20px;">Recursos Humanos</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Recurso</th>
                            <th>Órdenes Asignadas</th>
                            <th>Horas Planificadas</th>
                            <th>Horas Ejecutadas</th>
                            <th>% Utilización</th>
                            <th>Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Pérez (Desarrollador)</td>
                            <td>3</td>
                            <td>120 hrs</td>
                            <td>85 hrs</td>
                            <td>70.8%</td>
                            <td style="color: #27ae60;"><strong>Disponible</strong></td>
                        </tr>
                        <tr>
                            <td>María González (Técnico)</td>
                            <td>5</td>
                            <td>160 hrs</td>
                            <td>150 hrs</td>
                            <td>93.8%</td>
                            <td style="color: #e74c3c;"><strong>Sobrecargado</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Costos -->
        <div id="tab-costos" class="tab-content">
            <div class="card">
                <h3>Costos por Orden de Trabajo</h3>
                <button class="btn btn-success" onclick="exportarCostos()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Orden</th>
                            <th>Mano de Obra</th>
                            <th>Materiales</th>
                            <th>Servicios</th>
                            <th>Otros</th>
                            <th>Costo Total</th>
                            <th>Precio Venta</th>
                            <th>Margen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>OT-2026-001</strong></td>
                            <td>$4,500,000</td>
                            <td>$1,200,000</td>
                            <td>$800,000</td>
                            <td>$500,000</td>
                            <td>$7,000,000</td>
                            <td>$10,000,000</td>
                            <td style="background: #d4edda; font-weight: bold;">30.0%</td>
                        </tr>
                        <tr>
                            <td><strong>OT-2026-002</strong></td>
                            <td>$1,500,000</td>
                            <td>$800,000</td>
                            <td>$200,000</td>
                            <td>$100,000</td>
                            <td>$2,600,000</td>
                            <td>$3,500,000</td>
                            <td style="background: #d4edda; font-weight: bold;">25.7%</td>
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

        function nuevaOrden() { alert('Funcionalidad de nueva orden de trabajo'); }
        function exportarOrdenes() { window.location.href = '/api/exportacion.php?entidad=ordenes&formato=excel'; }
        function exportarCostos() { window.location.href = '/api/exportacion.php?entidad=costos_ordenes&formato=excel'; }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
