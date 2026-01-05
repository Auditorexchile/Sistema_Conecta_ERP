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
    <title>Centros de Costo - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .centros-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #8e44ad; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #8e44ad; color: white; }

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
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-inactivo { background: #f8d7da; color: #721c24; }

        .tree-item { padding: 10px; margin-bottom: 5px; background: #f8f9fa; border-radius: 4px; cursor: pointer; }
        .tree-item.level-1 { padding-left: 10px; background: #e8f4f8; font-weight: bold; }
        .tree-item.level-2 { padding-left: 30px; }
        .tree-item.level-3 { padding-left: 50px; font-size: 14px; }

        .progress-bar { width: 100%; height: 20px; background: #ecf0f1; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: #3498db; transition: width 0.3s; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="centros-container">
        <h1><i class="fas fa-building"></i> Centros de Costo</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">24</div>
                <div class="label">Centros de Costo Activos</div>
            </div>
            <div class="stat-card">
                <div class="value">$125,500,000</div>
                <div class="label">Presupuesto Total Anual</div>
            </div>
            <div class="stat-card">
                <div class="value">$78,200,000</div>
                <div class="label">Ejecutado Acumulado</div>
            </div>
            <div class="stat-card">
                <div class="value">62.3%</div>
                <div class="label">% Ejecución</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('lista')">Lista de Centros</button>
            <button class="tab" onclick="cambiarTab('jerarquia')">Jerarquía</button>
            <button class="tab" onclick="cambiarTab('presupuesto')">Presupuesto por Centro</button>
            <button class="tab" onclick="cambiarTab('ejecucion')">Ejecución Presupuestaria</button>
            <button class="tab" onclick="cambiarTab('analisis')">Análisis Comparativo</button>
        </div>

        <!-- Tab Lista de Centros -->
        <div id="tab-lista" class="tab-content active">
            <div class="card">
                <h3>Centros de Costo</h3>
                <button class="btn btn-primary" onclick="nuevoCentro()">
                    <i class="fas fa-plus"></i> Nuevo Centro de Costo
                </button>
                <button class="btn btn-success" onclick="exportarCentros()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Centro de Costo</th>
                            <th>Nivel</th>
                            <th>Responsable</th>
                            <th>Presupuesto Anual</th>
                            <th>Ejecutado</th>
                            <th>% Ejec.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>100</strong></td>
                            <td>Gerencia General</td>
                            <td>1</td>
                            <td>Juan Pérez</td>
                            <td>$15,000,000</td>
                            <td>$8,500,000</td>
                            <td>56.7%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>110</strong></td>
                            <td>Administración y Finanzas</td>
                            <td>2</td>
                            <td>María González</td>
                            <td>$28,000,000</td>
                            <td>$18,200,000</td>
                            <td>65.0%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>120</strong></td>
                            <td>Ventas y Marketing</td>
                            <td>2</td>
                            <td>Carlos Silva</td>
                            <td>$35,000,000</td>
                            <td>$22,500,000</td>
                            <td>64.3%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>130</strong></td>
                            <td>Operaciones</td>
                            <td>2</td>
                            <td>Ana Martínez</td>
                            <td>$42,000,000</td>
                            <td>$27,000,000</td>
                            <td>64.3%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>121</strong></td>
                            <td>Ventas Región Metropolitana</td>
                            <td>3</td>
                            <td>Pedro López</td>
                            <td>$18,000,000</td>
                            <td>$11,000,000</td>
                            <td>61.1%</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Jerarquía -->
        <div id="tab-jerarquia" class="tab-content">
            <div class="card">
                <h3>Estructura Jerárquica de Centros de Costo</h3>

                <div class="tree-item level-1">
                    <i class="fas fa-building"></i> <strong>100 - Gerencia General</strong>
                    <span style="float: right;">Presupuesto: $15,000,000 | Ejec: 56.7%</span>
                </div>

                <div class="tree-item level-1">
                    <i class="fas fa-chart-line"></i> <strong>110 - Administración y Finanzas</strong>
                    <span style="float: right;">Presupuesto: $28,000,000 | Ejec: 65.0%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-calculator"></i> 111 - Contabilidad
                    <span style="float: right;">Presupuesto: $8,000,000 | Ejec: 62.5%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-hand-holding-usd"></i> 112 - Tesorería
                    <span style="float: right;">Presupuesto: $6,000,000 | Ejec: 58.3%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-users"></i> 113 - Recursos Humanos
                    <span style="float: right;">Presupuesto: $14,000,000 | Ejec: 70.0%</span>
                </div>

                <div class="tree-item level-1">
                    <i class="fas fa-shopping-cart"></i> <strong>120 - Ventas y Marketing</strong>
                    <span style="float: right;">Presupuesto: $35,000,000 | Ejec: 64.3%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-map-marker-alt"></i> 121 - Ventas Región Metropolitana
                    <span style="float: right;">Presupuesto: $18,000,000 | Ejec: 61.1%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-map-marker-alt"></i> 122 - Ventas Regiones
                    <span style="float: right;">Presupuesto: $12,000,000 | Ejec: 66.7%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-bullhorn"></i> 123 - Marketing
                    <span style="float: right;">Presupuesto: $5,000,000 | Ejec: 72.0%</span>
                </div>

                <div class="tree-item level-1">
                    <i class="fas fa-cogs"></i> <strong>130 - Operaciones</strong>
                    <span style="float: right;">Presupuesto: $42,000,000 | Ejec: 64.3%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-warehouse"></i> 131 - Producción
                    <span style="float: right;">Presupuesto: $25,000,000 | Ejec: 62.0%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-boxes"></i> 132 - Logística
                    <span style="float: right;">Presupuesto: $12,000,000 | Ejec: 68.3%</span>
                </div>
                <div class="tree-item level-2">
                    <i class="fas fa-tools"></i> 133 - Mantenimiento
                    <span style="float: right;">Presupuesto: $5,000,000 | Ejec: 66.0%</span>
                </div>
            </div>
        </div>

        <!-- Tab Presupuesto por Centro -->
        <div id="tab-presupuesto" class="tab-content">
            <div class="card">
                <h3>Presupuesto por Centro de Costo - Año <?php echo date('Y'); ?></h3>
                <button class="btn btn-success" onclick="exportarPresupuesto()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Centro de Costo</th>
                            <th>Personal</th>
                            <th>Servicios</th>
                            <th>Materiales</th>
                            <th>Otros Gastos</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>100 - Gerencia General</strong></td>
                            <td>$12,000,000</td>
                            <td>$1,500,000</td>
                            <td>$500,000</td>
                            <td>$1,000,000</td>
                            <td><strong>$15,000,000</strong></td>
                        </tr>
                        <tr>
                            <td><strong>110 - Administración y Finanzas</strong></td>
                            <td>$18,000,000</td>
                            <td>$5,000,000</td>
                            <td>$2,000,000</td>
                            <td>$3,000,000</td>
                            <td><strong>$28,000,000</strong></td>
                        </tr>
                        <tr>
                            <td><strong>120 - Ventas y Marketing</strong></td>
                            <td>$22,000,000</td>
                            <td>$8,000,000</td>
                            <td>$3,000,000</td>
                            <td>$2,000,000</td>
                            <td><strong>$35,000,000</strong></td>
                        </tr>
                        <tr>
                            <td><strong>130 - Operaciones</strong></td>
                            <td>$25,000,000</td>
                            <td>$8,000,000</td>
                            <td>$7,000,000</td>
                            <td>$2,000,000</td>
                            <td><strong>$42,000,000</strong></td>
                        </tr>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>TOTAL GENERAL</td>
                            <td>$77,000,000</td>
                            <td>$22,500,000</td>
                            <td>$12,500,000</td>
                            <td>$8,000,000</td>
                            <td><strong>$120,000,000</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Ejecución Presupuestaria -->
        <div id="tab-ejecucion" class="tab-content">
            <div class="card">
                <h3>Ejecución Presupuestaria por Centro</h3>
                <button class="btn btn-success" onclick="exportarEjecucion()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Centro de Costo</th>
                            <th>Presupuesto</th>
                            <th>Ejecutado</th>
                            <th>Saldo</th>
                            <th>% Ejec.</th>
                            <th>Variación</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>100 - Gerencia General</strong></td>
                            <td>$15,000,000</td>
                            <td>$8,500,000</td>
                            <td>$6,500,000</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 56.7%; background: #27ae60;"></div>
                                </div>
                                56.7%
                            </td>
                            <td style="color: #27ae60;">-$500,000</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">OK</span></td>
                        </tr>
                        <tr>
                            <td><strong>110 - Administración</strong></td>
                            <td>$28,000,000</td>
                            <td>$18,200,000</td>
                            <td>$9,800,000</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 65%; background: #27ae60;"></div>
                                </div>
                                65.0%
                            </td>
                            <td style="color: #27ae60;">-$300,000</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">OK</span></td>
                        </tr>
                        <tr>
                            <td><strong>120 - Ventas y Marketing</strong></td>
                            <td>$35,000,000</td>
                            <td>$22,500,000</td>
                            <td>$12,500,000</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 64.3%; background: #27ae60;"></div>
                                </div>
                                64.3%
                            </td>
                            <td style="color: #27ae60;">-$200,000</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">OK</span></td>
                        </tr>
                        <tr>
                            <td><strong>130 - Operaciones</strong></td>
                            <td>$42,000,000</td>
                            <td>$29,000,000</td>
                            <td>$13,000,000</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 69%; background: #f39c12;"></div>
                                </div>
                                69.0%
                            </td>
                            <td style="color: #e74c3c;">+$2,000,000</td>
                            <td><span style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Alerta</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Análisis Comparativo -->
        <div id="tab-analisis" class="tab-content">
            <div class="card">
                <h3>Análisis Comparativo - Últimos 12 Meses</h3>
                <button class="btn btn-success" onclick="exportarAnalisis()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <h4>Resumen Ejecutivo</h4>
                    <ul>
                        <li><strong>Centro con mayor ejecución:</strong> Operaciones (69.0%)</li>
                        <li><strong>Centro con menor ejecución:</strong> Gerencia General (56.7%)</li>
                        <li><strong>Centro con mayor sobregiro:</strong> Operaciones (+$2,000,000)</li>
                        <li><strong>Eficiencia promedio:</strong> 62.3%</li>
                        <li><strong>Variación total:</strong> +$1,000,000 sobre presupuesto</li>
                    </ul>
                </div>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Centro de Costo</th>
                            <th>Mes Actual</th>
                            <th>Mes Anterior</th>
                            <th>Variación</th>
                            <th>% Var.</th>
                            <th>Promedio 3 Meses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Gerencia General</strong></td>
                            <td>$850,000</td>
                            <td>$820,000</td>
                            <td style="color: #e74c3c;">+$30,000</td>
                            <td>3.7%</td>
                            <td>$835,000</td>
                        </tr>
                        <tr>
                            <td><strong>Administración</strong></td>
                            <td>$1,820,000</td>
                            <td>$1,750,000</td>
                            <td style="color: #e74c3c;">+$70,000</td>
                            <td>4.0%</td>
                            <td>$1,785,000</td>
                        </tr>
                        <tr>
                            <td><strong>Ventas y Marketing</strong></td>
                            <td>$2,250,000</td>
                            <td>$2,180,000</td>
                            <td style="color: #e74c3c;">+$70,000</td>
                            <td>3.2%</td>
                            <td>$2,215,000</td>
                        </tr>
                        <tr>
                            <td><strong>Operaciones</strong></td>
                            <td>$2,900,000</td>
                            <td>$2,650,000</td>
                            <td style="color: #e74c3c;">+$250,000</td>
                            <td>9.4%</td>
                            <td>$2,775,000</td>
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

        function nuevoCentro() { alert('Funcionalidad de nuevo centro de costo'); }
        function exportarCentros() { window.location.href = '/api/exportacion.php?entidad=centros_costo&formato=excel'; }
        function exportarPresupuesto() { window.location.href = '/api/exportacion.php?entidad=presupuesto_centros&formato=excel'; }
        function exportarEjecucion() { window.open('/api/reportes/ejecucion-centros.php'); }
        function exportarAnalisis() { window.location.href = '/api/exportacion.php?entidad=analisis_centros&formato=excel'; }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
