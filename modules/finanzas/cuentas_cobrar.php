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
    <title>Cuentas por Cobrar - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cuentascobrar-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #27ae60; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #27ae60; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-vencida { background: #f8d7da; color: #721c24; }
        .badge-cobrada { background: #d4edda; color: #155724; }
        .badge-parcial { background: #d1ecf1; color: #0c5460; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="cuentascobrar-container">
        <h1><i class="fas fa-hand-holding-usd"></i> Cuentas por Cobrar</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$42,000,000</div>
                <div class="label">Total por Cobrar</div>
            </div>
            <div class="stat-card">
                <div class="value">$18,500,000</div>
                <div class="label">Vencidas</div>
            </div>
            <div class="stat-card">
                <div class="value">$12,300,000</div>
                <div class="label">Vencen Hoy</div>
            </div>
            <div class="stat-card">
                <div class="value">$11,200,000</div>
                <div class="label">Próximos 7 Días</div>
            </div>
            <div class="stat-card">
                <div class="value">87</div>
                <div class="label">Documentos Pendientes</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('facturas')">Facturas por Cobrar</button>
            <button class="tab" onclick="cambiarTab('cobranzas')">Cobranzas Realizadas</button>
            <button class="tab" onclick="cambiarTab('calendario')">Calendario de Cobranza</button>
            <button class="tab" onclick="cambiarTab('clientes')">Por Cliente</button>
            <button class="tab" onclick="cambiarTab('morosidad')">Análisis de Morosidad</button>
        </div>

        <!-- Tab Facturas por Cobrar -->
        <div id="tab-facturas" class="tab-content active">
            <div class="card">
                <h3>Facturas por Cobrar</h3>
                <button class="btn btn-success" onclick="registrarCobranza()">
                    <i class="fas fa-dollar-sign"></i> Registrar Cobranza
                </button>
                <button class="btn btn-warning" onclick="enviarRecordatorio()">
                    <i class="fas fa-envelope"></i> Enviar Recordatorio
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Vencido</th>
                            <th>Total</th>
                            <th>Cobrado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>F-000345</strong></td>
                            <td>Empresa ABC S.A.</td>
                            <td>10/11/2025</td>
                            <td>10/12/2025</td>
                            <td style="color: #e74c3c;"><strong>26 días</strong></td>
                            <td>$5,500,000</td>
                            <td>$0</td>
                            <td>$5,500,000</td>
                            <td><span class="badge badge-vencida">Vencida</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-warning btn-sm"><i class="fas fa-envelope"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>F-000346</strong></td>
                            <td>Cliente XYZ Ltda.</td>
                            <td>20/12/2025</td>
                            <td>05/01/2026</td>
                            <td style="color: #e74c3c;"><strong>Vence hoy</strong></td>
                            <td>$8,200,000</td>
                            <td>$3,000,000</td>
                            <td>$5,200,000</td>
                            <td><span class="badge badge-parcial">Pago Parcial</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-warning btn-sm"><i class="fas fa-envelope"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>F-000347</strong></td>
                            <td>Comercializadora 123</td>
                            <td>28/12/2025</td>
                            <td>12/01/2026</td>
                            <td>7 días</td>
                            <td>$3,800,000</td>
                            <td>$0</td>
                            <td>$3,800,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-warning btn-sm"><i class="fas fa-envelope"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>F-000348</strong></td>
                            <td>Distribuidora Global</td>
                            <td>02/01/2026</td>
                            <td>01/02/2026</td>
                            <td>27 días</td>
                            <td>$12,500,000</td>
                            <td>$0</td>
                            <td>$12,500,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-warning btn-sm"><i class="fas fa-envelope"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Cobranzas Realizadas -->
        <div id="tab-cobranzas" class="tab-content">
            <div class="card">
                <h3>Historial de Cobranzas</h3>
                <button class="btn btn-success" onclick="exportarCobranzas()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Cobranza</th>
                            <th>Fecha Cobro</th>
                            <th>Cliente</th>
                            <th>N° Factura</th>
                            <th>Forma Cobro</th>
                            <th>Banco/Caja</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>COB-00125</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Empresa ABC S.A.</td>
                            <td>F-000340</td>
                            <td>Transferencia</td>
                            <td>Banco de Chile</td>
                            <td>$6,500,000</td>
                            <td>admin@empresa.cl</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-undo"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>COB-00126</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Cliente XYZ Ltda.</td>
                            <td>F-000346</td>
                            <td>Cheque</td>
                            <td>Banco Santander</td>
                            <td>$3,000,000</td>
                            <td>admin@empresa.cl</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-undo"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Calendario de Cobranza -->
        <div id="tab-calendario" class="tab-content">
            <div class="card">
                <h3>Calendario de Cobranza - Próximos 30 Días</h3>
                <button class="btn btn-success" onclick="exportarCalendario()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Fecha Vencimiento</th>
                            <th>Días Restantes</th>
                            <th>Cliente</th>
                            <th>N° Factura</th>
                            <th>Monto</th>
                            <th>Prioridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #f8d7da;">
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td><strong style="color: #721c24;">HOY</strong></td>
                            <td>Cliente XYZ Ltda.</td>
                            <td>F-000346</td>
                            <td>$5,200,000</td>
                            <td><span class="badge badge-vencida">Alta</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+2 days')); ?></td>
                            <td>2 días</td>
                            <td>Empresa DEF S.A.</td>
                            <td>F-000349</td>
                            <td>$2,800,000</td>
                            <td><span class="badge badge-pendiente">Alta</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+7 days')); ?></td>
                            <td>7 días</td>
                            <td>Comercializadora 123</td>
                            <td>F-000347</td>
                            <td>$3,800,000</td>
                            <td><span class="badge badge-pendiente">Media</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+14 days')); ?></td>
                            <td>14 días</td>
                            <td>Cliente GHI SpA</td>
                            <td>F-000350</td>
                            <td>$7,200,000</td>
                            <td><span class="badge badge-pendiente">Media</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+27 days')); ?></td>
                            <td>27 días</td>
                            <td>Distribuidora Global</td>
                            <td>F-000348</td>
                            <td>$12,500,000</td>
                            <td><span class="badge badge-cobrada">Baja</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Total Próximos 30 Días:</td>
                            <td>$31,500,000</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tab Por Cliente -->
        <div id="tab-clientes" class="tab-content">
            <div class="card">
                <h3>Resumen por Cliente</h3>
                <button class="btn btn-success" onclick="exportarResumen()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Total Facturas</th>
                            <th>Facturas Pendientes</th>
                            <th>Total Deuda</th>
                            <th>Vencidas</th>
                            <th>Plazo Promedio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Empresa ABC S.A.</strong></td>
                            <td>25</td>
                            <td>4</td>
                            <td>$12,500,000</td>
                            <td>$5,500,000</td>
                            <td>30 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(1)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Cliente XYZ Ltda.</strong></td>
                            <td>38</td>
                            <td>7</td>
                            <td>$18,300,000</td>
                            <td>$8,200,000</td>
                            <td>45 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(2)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Comercializadora 123</strong></td>
                            <td>12</td>
                            <td>3</td>
                            <td>$6,200,000</td>
                            <td>$1,800,000</td>
                            <td>60 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(3)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Distribuidora Global</strong></td>
                            <td>18</td>
                            <td>5</td>
                            <td>$22,500,000</td>
                            <td>$3,000,000</td>
                            <td>90 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(4)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>TOTALES</td>
                            <td>93</td>
                            <td>19</td>
                            <td>$59,500,000</td>
                            <td>$18,500,000</td>
                            <td>-</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tab Análisis de Morosidad -->
        <div id="tab-morosidad" class="tab-content">
            <div class="card">
                <h3>Análisis de Morosidad (Aging)</h3>
                <button class="btn btn-success" onclick="exportarMorosidad()">
                    <i class="fas fa-file-pdf"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Al Día</th>
                            <th>1-30 días</th>
                            <th>31-60 días</th>
                            <th>61-90 días</th>
                            <th>+90 días</th>
                            <th>Total Deuda</th>
                            <th>% Riesgo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Empresa ABC S.A.</strong></td>
                            <td>$5,000,000</td>
                            <td>$2,000,000</td>
                            <td>$3,500,000</td>
                            <td>$2,000,000</td>
                            <td>$0</td>
                            <td>$12,500,000</td>
                            <td style="background: #fff3cd;">28%</td>
                        </tr>
                        <tr>
                            <td><strong>Cliente XYZ Ltda.</strong></td>
                            <td>$8,000,000</td>
                            <td>$4,100,000</td>
                            <td>$4,200,000</td>
                            <td>$2,000,000</td>
                            <td>$0</td>
                            <td>$18,300,000</td>
                            <td style="background: #fff3cd;">34%</td>
                        </tr>
                        <tr>
                            <td><strong>Comercializadora 123</strong></td>
                            <td>$4,400,000</td>
                            <td>$1,200,000</td>
                            <td>$600,000</td>
                            <td>$0</td>
                            <td>$0</td>
                            <td>$6,200,000</td>
                            <td style="background: #d4edda;">10%</td>
                        </tr>
                        <tr>
                            <td><strong>Distribuidora Global</strong></td>
                            <td>$15,500,000</td>
                            <td>$5,000,000</td>
                            <td>$2,000,000</td>
                            <td>$0</td>
                            <td>$0</td>
                            <td>$22,500,000</td>
                            <td style="background: #d4edda;">9%</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>TOTALES</td>
                            <td>$32,900,000</td>
                            <td>$12,300,000</td>
                            <td>$10,300,000</td>
                            <td>$4,000,000</td>
                            <td>$0</td>
                            <td>$59,500,000</td>
                            <td>24%</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #155724;">Bajo Riesgo (0-20%)</h4>
                        <p style="font-size: 32px; font-weight: bold; color: #155724; margin: 10px 0;">35</p>
                        <p style="margin: 0; color: #155724;">clientes</p>
                    </div>
                    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #856404;">Riesgo Medio (21-50%)</h4>
                        <p style="font-size: 32px; font-weight: bold; color: #856404; margin: 10px 0;">12</p>
                        <p style="margin: 0; color: #856404;">clientes</p>
                    </div>
                    <div style="background: #f8d7da; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #721c24;">Alto Riesgo (+50%)</h4>
                        <p style="font-size: 32px; font-weight: bold; color: #721c24; margin: 10px 0;">3</p>
                        <p style="margin: 0; color: #721c24;">clientes</p>
                    </div>
                </div>
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

        function registrarCobranza() { alert('Funcionalidad de registrar cobranza de cliente'); }
        function enviarRecordatorio() { alert('Enviar recordatorio de pago por email'); }
        function exportarCobranzas() { window.location.href = '/api/exportacion.php?entidad=cobranzas&formato=excel'; }
        function exportarCalendario() { window.open('/api/reportes/calendario-cobranza.php'); }
        function exportarResumen() { window.location.href = '/api/exportacion.php?entidad=resumen_clientes&formato=excel'; }
        function exportarMorosidad() { window.open('/api/reportes/analisis-morosidad.php'); }
        function verDetalle(id) { alert('Ver detalle del cliente ' + id); }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
