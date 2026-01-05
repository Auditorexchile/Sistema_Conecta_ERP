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
    <title>Gestión de Impuestos - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .impuestos-container { padding: 20px; }
        .tabs { display: flex; gap: 5px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .tab.active { background: #3498db; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-box.red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-box .value { font-size: 26px; font-weight: bold; }
        .stat-box .label { font-size: 13px; opacity: 0.9; margin-top: 5px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }

        .toolbar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
        .toolbar select, .toolbar input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; }
        .badge-declarado { background: #d4edda; color: #155724; }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-vencido { background: #f8d7da; color: #721c24; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="impuestos-container">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Gestión de Impuestos</h1>
            <div>
                <button class="btn btn-success" onclick="generarF29()">
                    <i class="fas fa-file-alt"></i> Generar F29
                </button>
                <button class="btn btn-primary" onclick="generarF22()">
                    <i class="fas fa-file-alt"></i> Generar F22
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('iva')">IVA</button>
            <button class="tab" onclick="cambiarTab('renta')">Renta</button>
            <button class="tab" onclick="cambiarTab('retencion')">Retenciones</button>
            <button class="tab" onclick="cambiarTab('declaraciones')">Declaraciones</button>
            <button class="tab" onclick="cambiarTab('calendario')">Calendario Tributario</button>
        </div>

        <!-- Tab: IVA -->
        <div id="tab-iva" class="tab-content active">
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Declaración de IVA (Formulario 29)</strong><br>
                Declaración mensual de Impuesto al Valor Agregado. Vencimiento hasta el día 12 o 20 del mes siguiente.
            </div>

            <!-- Estadísticas IVA -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="value">$5,250,000</div>
                    <div class="label">IVA Débito Fiscal (Ventas)</div>
                </div>
                <div class="stat-box green">
                    <div class="value">$3,800,000</div>
                    <div class="label">IVA Crédito Fiscal (Compras)</div>
                </div>
                <div class="stat-box red">
                    <div class="value">$1,450,000</div>
                    <div class="label">IVA por Pagar</div>
                </div>
                <div class="stat-box">
                    <div class="value">12/01/2026</div>
                    <div class="label">Próximo Vencimiento</div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <label>Período:</label>
                <input type="month" id="periodoIVA" value="<?php echo date('Y-m'); ?>">

                <button class="btn btn-primary" onclick="calcularIVA()">
                    <i class="fas fa-calculator"></i> Calcular
                </button>

                <button class="btn btn-success" onclick="descargarLibroVentas()">
                    <i class="fas fa-book"></i> Libro Ventas
                </button>

                <button class="btn btn-success" onclick="descargarLibroCompras()">
                    <i class="fas fa-book-open"></i> Libro Compras
                </button>
            </div>

            <!-- Resumen IVA -->
            <div class="grid-2">
                <div class="card">
                    <h3>Débito Fiscal (IVA Ventas)</h3>
                    <table>
                        <tr><td>Ventas Afectas</td><td align="right">$27,631,579</td></tr>
                        <tr><td>IVA 19%</td><td align="right"><strong>$5,250,000</strong></td></tr>
                        <tr><td>Ventas Exentas</td><td align="right">$0</td></tr>
                        <tr><td>Notas de Crédito</td><td align="right">($190,000)</td></tr>
                        <tr style="background: #ecf0f1; font-weight: bold;">
                            <td>Total Débito Fiscal</td>
                            <td align="right">$5,060,000</td>
                        </tr>
                    </table>
                </div>

                <div class="card">
                    <h3>Crédito Fiscal (IVA Compras)</h3>
                    <table>
                        <tr><td>Compras Afectas</td><td align="right">$20,000,000</td></tr>
                        <tr><td>IVA 19%</td><td align="right"><strong>$3,800,000</strong></td></tr>
                        <tr><td>IVA Importaciones</td><td align="right">$150,000</td></tr>
                        <tr><td>IVA Activo Fijo</td><td align="right">$200,000</td></tr>
                        <tr style="background: #ecf0f1; font-weight: bold;">
                            <td>Total Crédito Fiscal</td>
                            <td align="right">$4,150,000</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Determinación IVA a Pagar -->
            <div class="card">
                <h3>Determinación IVA a Pagar</h3>
                <table style="max-width: 600px; margin: 0 auto;">
                    <tr><td>Débito Fiscal</td><td align="right">$5,060,000</td></tr>
                    <tr><td>(-) Crédito Fiscal</td><td align="right">($4,150,000)</td></tr>
                    <tr><td>(-) Remanente Período Anterior</td><td align="right">($0)</td></tr>
                    <tr><td>(-) Otros Créditos</td><td align="right">($0)</td></tr>
                    <tr style="background: #3498db; color: white; font-size: 18px; font-weight: bold;">
                        <td>TOTAL IVA A PAGAR (Línea 89)</td>
                        <td align="right">$910,000</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Tab: Renta -->
        <div id="tab-renta" class="tab-content">
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Impuesto a la Renta (Formulario 22)</strong><br>
                Declaración anual de impuestos corporativos. Vencimiento en Abril del año siguiente.
            </div>

            <div class="card">
                <h3>Determinación Renta Líquida Imponible - AT 2025</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Código F22</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Ingresos del Giro</td><td>$120,000,000</td><td>01</td></tr>
                        <tr><td>(-) Costo Directo</td><td>($60,000,000)</td><td>02</td></tr>
                        <tr><td>Renta Bruta</td><td>$60,000,000</td><td>03</td></tr>
                        <tr><td>(-) Gastos Necesarios</td><td>($40,000,000)</td><td>04</td></tr>
                        <tr><td>(+) Agregados</td><td>$2,000,000</td><td>08</td></tr>
                        <tr><td>(-) Deducciones</td><td>($500,000)</td><td>09</td></tr>
                        <tr style="background: #ecf0f1; font-weight: bold;">
                            <td>Renta Líquida Imponible</td>
                            <td>$21,500,000</td>
                            <td>10</td>
                        </tr>
                        <tr><td>Impuesto Primera Categoría (27%)</td><td>$5,805,000</td><td>11</td></tr>
                        <tr><td>(-) Créditos</td><td>($200,000)</td><td>15</td></tr>
                        <tr><td>(-) PPM Pagados</td><td>($5,500,000)</td><td>20</td></tr>
                        <tr style="background: #3498db; color: white; font-weight: bold;">
                            <td>TOTAL A PAGAR / DEVOLVER</td>
                            <td>$105,000</td>
                            <td>89</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PPM -->
            <div class="card">
                <h3>Pagos Provisionales Mensuales (PPM)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Base Imponible</th>
                            <th>Tasa %</th>
                            <th>PPM Calculado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Enero 2026</td>
                            <td>$10,000,000</td>
                            <td>0.5%</td>
                            <td>$50,000</td>
                            <td><span class="badge badge-declarado">Pagado</span></td>
                        </tr>
                        <tr>
                            <td>Febrero 2026</td>
                            <td>$11,200,000</td>
                            <td>0.5%</td>
                            <td>$56,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Retenciones -->
        <div id="tab-retencion" class="tab-content">
            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Retenciones de Impuestos</strong><br>
                Gestión de retenciones a honorarios, dividendos, y otras rentas.
            </div>

            <div class="card">
                <h3>Retenciones a Honorarios (Formulario 29)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Profesional</th>
                            <th>RUT</th>
                            <th>Fecha</th>
                            <th>Monto Bruto</th>
                            <th>Retención 13.75%</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Pérez G.</td>
                            <td>12.345.678-9</td>
                            <td>15/01/2026</td>
                            <td>$1,000,000</td>
                            <td>$137,500</td>
                            <td><span class="badge badge-declarado">Declarada</span></td>
                        </tr>
                        <tr>
                            <td>María López S.</td>
                            <td>23.456.789-0</td>
                            <td>20/01/2026</td>
                            <td>$1,500,000</td>
                            <td>$206,250</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; text-align: right;">
                    <strong>Total Retenciones del Período: $343,750</strong>
                </div>
            </div>

            <div class="card">
                <h3>Certificados de Retención</h3>
                <button class="btn btn-success">
                    <i class="fas fa-certificate"></i> Generar Certificados
                </button>
            </div>
        </div>

        <!-- Tab: Declaraciones -->
        <div id="tab-declaraciones" class="tab-content">
            <div class="card">
                <h3>Historial de Declaraciones</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Formulario</th>
                            <th>Período</th>
                            <th>Fecha Declaración</th>
                            <th>Monto</th>
                            <th>N° Folio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>F29 - IVA</td>
                            <td>Diciembre 2025</td>
                            <td>12/01/2026</td>
                            <td>$850,000</td>
                            <td>123456789</td>
                            <td><span class="badge badge-declarado">Declarado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-download"></i> PDF</button>
                                <button class="btn btn-success btn-sm"><i class="fas fa-file-invoice-dollar"></i> Comprobante</button>
                            </td>
                        </tr>
                        <tr>
                            <td>F29 - IVA</td>
                            <td>Enero 2026</td>
                            <td>-</td>
                            <td>$910,000</td>
                            <td>-</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-paper-plane"></i> Declarar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Calendario Tributario -->
        <div id="tab-calendario" class="tab-content">
            <div class="card">
                <h3>Calendario Tributario 2026 - Chile</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Obligación</th>
                            <th>Formulario</th>
                            <th>Detalle</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>12/02/2026</td>
                            <td>Declaración y Pago IVA</td>
                            <td>F29</td>
                            <td>Período Enero 2026</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td>12/02/2026</td>
                            <td>PPM (Pago Provisional Mensual)</td>
                            <td>F29</td>
                            <td>Período Enero 2026</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td>12/03/2026</td>
                            <td>Declaración y Pago IVA</td>
                            <td>F29</td>
                            <td>Período Febrero 2026</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td>30/04/2026</td>
                            <td>Declaración Renta AT 2025</td>
                            <td>F22</td>
                            <td>Año Tributario 2025</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-success">
                <strong><i class="fas fa-bell"></i> Recordatorios Activados</strong><br>
                Recibirá notificaciones por email 7 días antes de cada vencimiento.
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

        function calcularIVA() {
            alert('Calculando IVA del período seleccionado...');
        }

        function generarF29() {
            if (confirm('¿Generar Formulario 29 (IVA) del período actual?')) {
                alert('Generando F29...');
                window.open('/api/impuestos/f29.php?periodo=' + document.getElementById('periodoIVA').value, '_blank');
            }
        }

        function generarF22() {
            if (confirm('¿Generar Formulario 22 (Renta) del año tributario?')) {
                alert('Generando F22...');
                window.open('/api/impuestos/f22.php?ano=2025', '_blank');
            }
        }

        function descargarLibroVentas() {
            window.location.href = '/api/v1/reportes.php?tipo=libro-ventas&formato=excel&id_empresa=<?php echo $idEmpresa; ?>';
        }

        function descargarLibroCompras() {
            window.location.href = '/api/v1/reportes.php?tipo=libro-compras&formato=excel&id_empresa=<?php echo $idEmpresa; ?>';
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
