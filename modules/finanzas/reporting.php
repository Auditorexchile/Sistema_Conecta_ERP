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
    <title>Reporting Financiero - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reporting-container { padding: 20px; }
        .reports-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px; }

        .report-card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s; cursor: pointer; }
        .report-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.15); transform: translateY(-2px); }
        .report-card .icon { font-size: 48px; color: #3498db; margin-bottom: 15px; }
        .report-card h3 { margin: 10px 0; color: #2c3e50; }
        .report-card p { color: #7f8c8d; font-size: 14px; }
        .report-card .btn { margin-top: 15px; width: 100%; }

        .btn { padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 50px auto; padding: 30px; width: 90%; max-width: 800px; border-radius: 8px; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .tabs { display: flex; gap: 5px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #3498db; color: white; }

        .scheduled-reports { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="reporting-container">
        <div class="page-header">
            <h1><i class="fas fa-chart-bar"></i> Reporting Financiero</h1>
            <button class="btn btn-primary" onclick="programarReporte()">
                <i class="fas fa-clock"></i> Programar Reporte
            </button>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('disponibles')">Reportes Disponibles</button>
            <button class="tab" onclick="cambiarTab('programados')">Reportes Programados</button>
            <button class="tab" onclick="cambiarTab('historial')">Historial</button>
        </div>

        <!-- Tab: Reportes Disponibles -->
        <div id="tab-disponibles" class="tab-content">
            <div class="reports-grid">
                <!-- Reportes Financieros -->
                <div class="report-card" onclick="abrirConfigReporte('balance')">
                    <div class="icon"><i class="fas fa-balance-scale"></i></div>
                    <h3>Balance General</h3>
                    <p>Estado de situación financiera con activos, pasivos y patrimonio</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('balance', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('balance', 'excel')" style="margin-left: 5px;">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('resultados')">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Estado de Resultados</h3>
                    <p>Ingresos, costos, gastos y utilidad del período</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('resultados', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('resultados', 'excel')" style="margin-left: 5px;">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('flujo')">
                    <div class="icon"><i class="fas fa-water"></i></div>
                    <h3>Flujo de Efectivo</h3>
                    <p>Movimientos de efectivo: operación, inversión y financiamiento</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('flujo', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('flujo', 'excel')" style="margin-left: 5px;">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('patrimonio')">
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <h3>Cambios en el Patrimonio</h3>
                    <p>Evolución del patrimonio: capital, reservas, resultados acumulados</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('patrimonio', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>

                <!-- Reportes de Análisis -->
                <div class="report-card" onclick="abrirConfigReporte('ratios')">
                    <div class="icon"><i class="fas fa-percentage"></i></div>
                    <h3>Ratios Financieros</h3>
                    <p>Liquidez, endeudamiento, rentabilidad, eficiencia</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('ratios', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('analisis-vertical')">
                    <div class="icon"><i class="fas fa-chart-bar"></i></div>
                    <h3>Análisis Vertical</h3>
                    <p>Estructura porcentual de estados financieros</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('analisis-vertical', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('analisis-horizontal')">
                    <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                    <h3>Análisis Horizontal</h3>
                    <p>Variaciones comparativas entre períodos</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('analisis-horizontal', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <!-- Reportes Gerenciales -->
                <div class="report-card" onclick="abrirConfigReporte('dashboard')">
                    <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
                    <h3>Dashboard Ejecutivo</h3>
                    <p>Indicadores clave de desempeño (KPIs) y métricas principales</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="event.stopPropagation(); generarReporte('dashboard', 'pdf')">
                            <i class="fas fa-file-pdf"></i> Generar
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('ventas')">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3>Análisis de Ventas</h3>
                    <p>Ventas por producto, cliente, región, vendedor</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('ventas', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('compras')">
                    <div class="icon"><i class="fas fa-truck"></i></div>
                    <h3>Análisis de Compras</h3>
                    <p>Compras por proveedor, producto, categoría</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('compras', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('inventario')">
                    <div class="icon"><i class="fas fa-boxes"></i></div>
                    <h3>Valorización de Inventario</h3>
                    <p>Stock valorizado por bodega, categoría, valorización</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('inventario', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <!-- Reportes Tributarios -->
                <div class="report-card" onclick="abrirConfigReporte('libro-ventas')">
                    <div class="icon"><i class="fas fa-book"></i></div>
                    <h3>Libro de Ventas</h3>
                    <p>Registro de ventas para declaración IVA</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('libro-ventas', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="abrirConfigReporte('libro-compras')">
                    <div class="icon"><i class="fas fa-book-open"></i></div>
                    <h3>Libro de Compras</h3>
                    <p>Registro de compras para declaración IVA</p>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="event.stopPropagation(); generarReporte('libro-compras', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Reportes Programados -->
        <div id="tab-programados" class="tab-content" style="display:none;">
            <div class="scheduled-reports">
                <h3>Reportes Programados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Reporte</th>
                            <th>Frecuencia</th>
                            <th>Destinatarios</th>
                            <th>Próxima Ejecución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Balance General</td>
                            <td>Mensual (día 1)</td>
                            <td>gerencia@empresa.cl, contabilidad@empresa.cl</td>
                            <td>01/02/2026</td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Estado de Resultados</td>
                            <td>Mensual (día 5)</td>
                            <td>directorio@empresa.cl</td>
                            <td>05/02/2026</td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Historial -->
        <div id="tab-historial" class="tab-content" style="display:none;">
            <div class="scheduled-reports">
                <h3>Historial de Reportes Generados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha Generación</th>
                            <th>Reporte</th>
                            <th>Período</th>
                            <th>Formato</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>05/01/2026 10:30</td>
                            <td>Balance General</td>
                            <td>Diciembre 2025</td>
                            <td>PDF</td>
                            <td>Juan Pérez</td>
                            <td><button class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Descargar</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Configuración Reporte -->
    <div id="modalConfig" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Configurar Reporte</h2>
                <span class="close" onclick="cerrarModal()" style="float:right;cursor:pointer;font-size:28px;">&times;</span>
            </div>

            <form id="formReporte">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Período Desde</label>
                        <input type="date" id="fechaDesde" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Período Hasta</label>
                        <input type="date" id="fechaHasta" value="<?php echo date('Y-m-t'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Formato</label>
                        <select id="formato">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nivel de Detalle</label>
                        <select id="detalle">
                            <option value="resumido">Resumido</option>
                            <option value="detallado">Detallado</option>
                            <option value="completo">Completo con anexos</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarGeneracion()">Generar Reporte</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let reporteActual = '';

        function cambiarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            document.getElementById('tab-' + tab).style.display = 'block';
            event.target.classList.add('active');
        }

        function abrirConfigReporte(tipo) {
            reporteActual = tipo;
            document.getElementById('modalConfig').style.display = 'block';
            document.getElementById('modalTitulo').textContent = 'Configurar ' + tipo.replace('-', ' ').toUpperCase();
        }

        function cerrarModal() {
            document.getElementById('modalConfig').style.display = 'none';
        }

        async function generarReporte(tipo, formato) {
            alert(`Generando reporte ${tipo} en formato ${formato}...`);

            try {
                const url = `/api/v1/reportes.php?tipo=${tipo}&formato=${formato}&id_empresa=<?php echo $idEmpresa; ?>`;
                window.open(url, '_blank');
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function confirmarGeneracion() {
            const formato = document.getElementById('formato').value;
            generarReporte(reporteActual, formato);
            cerrarModal();
        }

        function programarReporte() {
            alert('Funcionalidad de programación de reportes');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
