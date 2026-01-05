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
    <title>Presupuestos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .presupuestos-container { padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card .value { font-size: 28px; font-weight: bold; }
        .stat-card .label { font-size: 13px; opacity: 0.9; margin-top: 5px; }

        .toolbar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .toolbar select, .toolbar input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; position: sticky; top: 0; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }
        .nivel-1 { font-weight: bold; background: #ecf0f1; }
        .nivel-2 { padding-left: 30px; }
        .totales { background: #3498db; color: white; font-weight: bold; }

        .progress-bar { width: 100%; height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); transition: width 0.3s; }
        .progress-fill.warning { background: linear-gradient(90deg, #f39c12, #e74c3c); }

        .variance { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .variance.positive { background: #d4edda; color: #155724; }
        .variance.negative { background: #f8d7da; color: #721c24; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 30px auto; padding: 30px; width: 95%; max-width: 1000px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .detalle-presupuesto { margin-top: 20px; }
        .detalle-presupuesto table th { background: #2c3e50; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="presupuestos-container">
        <div class="page-header">
            <h1><i class="fas fa-calculator"></i> Presupuestos</h1>
            <div>
                <button class="btn btn-primary" onclick="nuevoPresupuesto()">
                    <i class="fas fa-plus"></i> Nuevo Presupuesto
                </button>
                <button class="btn btn-success" onclick="copiarPresupuesto()">
                    <i class="fas fa-copy"></i> Copiar de Período Anterior
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$125,000,000</div>
                <div class="label">Presupuesto Total 2026</div>
            </div>
            <div class="stat-card green">
                <div class="value">$98,500,000</div>
                <div class="label">Ejecutado a la Fecha</div>
            </div>
            <div class="stat-card orange">
                <div class="value">$26,500,000</div>
                <div class="label">Saldo Disponible</div>
            </div>
            <div class="stat-card blue">
                <div class="value">78.8%</div>
                <div class="label">% de Ejecución</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <label>Año:</label>
            <select id="anoPresupuesto" onchange="cargarPresupuesto()">
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </select>

            <label>Tipo:</label>
            <select id="tipoPresupuesto">
                <option value="ingresos">Ingresos</option>
                <option value="gastos">Gastos</option>
                <option value="completo">Completo (Ingresos y Gastos)</option>
            </select>

            <label>Centro de Costo:</label>
            <select id="centroCosto">
                <option value="">Todos</option>
                <option value="admin">Administración</option>
                <option value="ventas">Ventas</option>
                <option value="produccion">Producción</option>
                <option value="marketing">Marketing</option>
            </select>

            <button class="btn btn-primary" onclick="actualizarVista()">
                <i class="fas fa-sync"></i> Actualizar
            </button>

            <button class="btn btn-warning" onclick="exportar()">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>

        <!-- Presupuesto vs Real -->
        <div class="card">
            <h3>Presupuesto vs Real - Año 2026</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">Cuenta</th>
                            <th colspan="3">Presupuesto</th>
                            <th colspan="3">Real</th>
                            <th colspan="2">Variación</th>
                            <th rowspan="2">% Ejec.</th>
                        </tr>
                        <tr>
                            <th>Anual</th>
                            <th>Mensual</th>
                            <th>Acum.</th>
                            <th>Mes Actual</th>
                            <th>Acumulado</th>
                            <th>Proyectado</th>
                            <th>Monto</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="nivel-1">
                            <td>INGRESOS OPERACIONALES</td>
                            <td>$120,000,000</td>
                            <td>$10,000,000</td>
                            <td>$10,000,000</td>
                            <td>$11,200,000</td>
                            <td>$11,200,000</td>
                            <td>$134,400,000</td>
                            <td class="variance positive">+$1,200,000</td>
                            <td class="variance positive">+12%</td>
                            <td>112%</td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Venta de Productos</td>
                            <td>$100,000,000</td>
                            <td>$8,333,333</td>
                            <td>$8,333,333</td>
                            <td>$9,500,000</td>
                            <td>$9,500,000</td>
                            <td>$114,000,000</td>
                            <td class="variance positive">+$1,166,667</td>
                            <td class="variance positive">+14%</td>
                            <td>114%</td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Venta de Servicios</td>
                            <td>$20,000,000</td>
                            <td>$1,666,667</td>
                            <td>$1,666,667</td>
                            <td>$1,700,000</td>
                            <td>$1,700,000</td>
                            <td>$20,400,000</td>
                            <td class="variance positive">+$33,333</td>
                            <td class="variance positive">+2%</td>
                            <td>102%</td>
                        </tr>

                        <tr class="nivel-1">
                            <td>COSTO DE VENTAS</td>
                            <td>($60,000,000)</td>
                            <td>($5,000,000)</td>
                            <td>($5,000,000)</td>
                            <td>($5,600,000)</td>
                            <td>($5,600,000)</td>
                            <td>($67,200,000)</td>
                            <td class="variance negative">-$600,000</td>
                            <td class="variance negative">-12%</td>
                            <td>112%</td>
                        </tr>

                        <tr class="nivel-1">
                            <td>MARGEN BRUTO</td>
                            <td>$60,000,000</td>
                            <td>$5,000,000</td>
                            <td>$5,000,000</td>
                            <td>$5,600,000</td>
                            <td>$5,600,000</td>
                            <td>$67,200,000</td>
                            <td class="variance positive">+$600,000</td>
                            <td class="variance positive">+12%</td>
                            <td>112%</td>
                        </tr>

                        <tr class="nivel-1">
                            <td>GASTOS DE ADMINISTRACIÓN</td>
                            <td>($25,000,000)</td>
                            <td>($2,083,333)</td>
                            <td>($2,083,333)</td>
                            <td>($2,100,000)</td>
                            <td>($2,100,000)</td>
                            <td>($25,200,000)</td>
                            <td class="variance negative">-$16,667</td>
                            <td class="variance negative">-0.8%</td>
                            <td>101%</td>
                        </tr>

                        <tr class="nivel-1">
                            <td>GASTOS DE VENTAS</td>
                            <td>($15,000,000)</td>
                            <td>($1,250,000)</td>
                            <td>($1,250,000)</td>
                            <td>($1,350,000)</td>
                            <td>($1,350,000)</td>
                            <td>($16,200,000)</td>
                            <td class="variance negative">-$100,000</td>
                            <td class="variance negative">-8%</td>
                            <td>108%</td>
                        </tr>

                        <tr class="totales">
                            <td>RESULTADO OPERACIONAL</td>
                            <td>$20,000,000</td>
                            <td>$1,666,667</td>
                            <td>$1,666,667</td>
                            <td>$2,150,000</td>
                            <td>$2,150,000</td>
                            <td>$25,800,000</td>
                            <td class="variance positive">+$483,333</td>
                            <td class="variance positive">+29%</td>
                            <td>129%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ejecución Presupuestaria por Centro de Costo -->
        <div class="card">
            <h3>Ejecución Presupuestaria por Centro de Costo</h3>
            <table>
                <thead>
                    <tr>
                        <th>Centro de Costo</th>
                        <th>Presupuesto Anual</th>
                        <th>Ejecutado</th>
                        <th>Disponible</th>
                        <th>% Ejecución</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Administración</strong></td>
                        <td>$25,000,000</td>
                        <td>$20,500,000</td>
                        <td>$4,500,000</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 82%;"></div>
                            </div>
                            82%
                        </td>
                        <td><i class="fas fa-check-circle" style="color: #27ae60;"></i> Normal</td>
                    </tr>
                    <tr>
                        <td><strong>Ventas</strong></td>
                        <td>$15,000,000</td>
                        <td>$14,200,000</td>
                        <td>$800,000</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill warning" style="width: 95%;"></div>
                            </div>
                            95%
                        </td>
                        <td><i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i> Alerta</td>
                    </tr>
                    <tr>
                        <td><strong>Producción</strong></td>
                        <td>$60,000,000</td>
                        <td>$48,500,000</td>
                        <td>$11,500,000</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 81%;"></div>
                            </div>
                            81%
                        </td>
                        <td><i class="fas fa-check-circle" style="color: #27ae60;"></i> Normal</td>
                    </tr>
                    <tr>
                        <td><strong>Marketing</strong></td>
                        <td>$10,000,000</td>
                        <td>$6,800,000</td>
                        <td>$3,200,000</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 68%;"></div>
                            </div>
                            68%
                        </td>
                        <td><i class="fas fa-check-circle" style="color: #27ae60;"></i> Normal</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Análisis de Desviaciones -->
        <div class="card">
            <h3>Principales Desviaciones (Top 10)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Presupuesto</th>
                        <th>Real</th>
                        <th>Desviación</th>
                        <th>%</th>
                        <th>Causa</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sueldos y Salarios</td>
                        <td>$18,000,000</td>
                        <td>$19,500,000</td>
                        <td class="variance negative">-$1,500,000</td>
                        <td>-8.3%</td>
                        <td>Bonos extraordinarios</td>
                    </tr>
                    <tr>
                        <td>Publicidad Digital</td>
                        <td>$3,000,000</td>
                        <td>$4,200,000</td>
                        <td class="variance negative">-$1,200,000</td>
                        <td>-40%</td>
                        <td>Campaña nueva producto</td>
                    </tr>
                    <tr>
                        <td>Servicios Básicos</td>
                        <td>$2,500,000</td>
                        <td>$2,100,000</td>
                        <td class="variance positive">+$400,000</td>
                        <td>+16%</td>
                        <td>Eficiencia energética</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo Presupuesto -->
    <div id="modalPresupuesto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nuevo Presupuesto</h2>
                <span class="close" onclick="cerrarModal()" style="float:right;cursor:pointer;font-size:28px;">&times;</span>
            </div>

            <form id="formPresupuesto">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Año *</label>
                        <select id="ano" required>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tipo *</label>
                        <select id="tipo" required>
                            <option value="operacional">Operacional</option>
                            <option value="inversion">Inversión</option>
                            <option value="financiero">Financiero</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Versión</label>
                        <input type="text" id="version" value="v1.0">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Descripción</label>
                        <textarea id="descripcion" rows="3"></textarea>
                    </div>
                </div>

                <div class="detalle-presupuesto">
                    <h4>Detalle por Cuenta</h4>
                    <button type="button" class="btn btn-success btn-sm" onclick="agregarCuenta()">
                        <i class="fas fa-plus"></i> Agregar Cuenta
                    </button>

                    <table style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Ene</th>
                                <th>Feb</th>
                                <th>Mar</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="detallePresupuesto">
                            <!-- Dinámico -->
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarPresupuesto()">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function nuevoPresupuesto() {
            document.getElementById('modalPresupuesto').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalPresupuesto').style.display = 'none';
        }

        function copiarPresupuesto() {
            if (confirm('¿Copiar presupuesto del año anterior?')) {
                alert('Copiando presupuesto 2025 -> 2026...');
            }
        }

        function cargarPresupuesto() {
            alert('Cargando presupuesto...');
        }

        function actualizarVista() {
            alert('Actualizando vista...');
        }

        function exportar() {
            window.location.href = '/api/presupuestos/exportar.php?ano=2026&id_empresa=<?php echo $idEmpresa; ?>';
        }

        function guardarPresupuesto() {
            alert('Guardando presupuesto...');
            cerrarModal();
        }

        function agregarCuenta() {
            alert('Funcionalidad de agregar cuenta presupuestaria');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
