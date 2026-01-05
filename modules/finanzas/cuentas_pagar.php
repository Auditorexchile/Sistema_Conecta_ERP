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
    <title>Cuentas por Pagar - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cuentaspagar-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #e74c3c; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #e74c3c; color: white; }

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
        .badge-pagada { background: #d4edda; color: #155724; }
        .badge-parcial { background: #d1ecf1; color: #0c5460; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="cuentaspagar-container">
        <h1><i class="fas fa-file-invoice-dollar"></i> Cuentas por Pagar</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$28,500,000</div>
                <div class="label">Total por Pagar</div>
            </div>
            <div class="stat-card">
                <div class="value">$12,300,000</div>
                <div class="label">Vencidas</div>
            </div>
            <div class="stat-card">
                <div class="value">$8,700,000</div>
                <div class="label">Vencen Hoy</div>
            </div>
            <div class="stat-card">
                <div class="value">$7,500,000</div>
                <div class="label">Próximos 7 Días</div>
            </div>
            <div class="stat-card">
                <div class="value">45</div>
                <div class="label">Documentos Pendientes</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('facturas')">Facturas por Pagar</button>
            <button class="tab" onclick="cambiarTab('pagos')">Pagos Realizados</button>
            <button class="tab" onclick="cambiarTab('calendario')">Calendario de Pagos</button>
            <button class="tab" onclick="cambiarTab('proveedores')">Por Proveedor</button>
        </div>

        <!-- Tab Facturas por Pagar -->
        <div id="tab-facturas" class="tab-content active">
            <div class="card">
                <h3>Facturas por Pagar</h3>
                <button class="btn btn-primary" onclick="registrarFactura()">
                    <i class="fas fa-plus"></i> Registrar Factura
                </button>
                <button class="btn btn-success" onclick="registrarPago()">
                    <i class="fas fa-dollar-sign"></i> Registrar Pago
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Proveedor</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Vencimiento</th>
                            <th>Días Vencido</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>FC-00125</strong></td>
                            <td>Proveedor ABC Ltda.</td>
                            <td>15/12/2025</td>
                            <td>15/01/2026</td>
                            <td style="color: #e74c3c;"><strong>5 días</strong></td>
                            <td>$3,500,000</td>
                            <td>$0</td>
                            <td>$3,500,000</td>
                            <td><span class="badge badge-vencida">Vencida</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>FC-00126</strong></td>
                            <td>Distribuidora XYZ S.A.</td>
                            <td>28/12/2025</td>
                            <td>05/01/2026</td>
                            <td style="color: #e74c3c;"><strong>Vence hoy</strong></td>
                            <td>$5,200,000</td>
                            <td>$2,000,000</td>
                            <td>$3,200,000</td>
                            <td><span class="badge badge-parcial">Pago Parcial</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>FC-00127</strong></td>
                            <td>Comercial 123 SpA</td>
                            <td>02/01/2026</td>
                            <td>12/01/2026</td>
                            <td>7 días</td>
                            <td>$2,800,000</td>
                            <td>$0</td>
                            <td>$2,800,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>FC-00128</strong></td>
                            <td>Importadora Global S.A.</td>
                            <td>03/01/2026</td>
                            <td>02/02/2026</td>
                            <td>28 días</td>
                            <td>$8,500,000</td>
                            <td>$0</td>
                            <td>$8,500,000</td>
                            <td><span class="badge badge-pendiente">Pendiente</span></td>
                            <td>
                                <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Pagos Realizados -->
        <div id="tab-pagos" class="tab-content">
            <div class="card">
                <h3>Historial de Pagos</h3>
                <button class="btn btn-success" onclick="exportarPagos()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Pago</th>
                            <th>Fecha Pago</th>
                            <th>Proveedor</th>
                            <th>N° Factura</th>
                            <th>Forma Pago</th>
                            <th>Banco/Caja</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PAG-00045</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Proveedor ABC Ltda.</td>
                            <td>FC-00120</td>
                            <td>Transferencia</td>
                            <td>Banco de Chile</td>
                            <td>$4,500,000</td>
                            <td>admin@empresa.cl</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-undo"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>PAG-00046</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Distribuidora XYZ S.A.</td>
                            <td>FC-00126</td>
                            <td>Cheque</td>
                            <td>Banco Santander</td>
                            <td>$2,000,000</td>
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

        <!-- Tab Calendario de Pagos -->
        <div id="tab-calendario" class="tab-content">
            <div class="card">
                <h3>Calendario de Pagos - Próximos 30 Días</h3>
                <button class="btn btn-success" onclick="exportarCalendario()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Fecha Vencimiento</th>
                            <th>Días Restantes</th>
                            <th>Proveedor</th>
                            <th>N° Factura</th>
                            <th>Monto</th>
                            <th>Prioridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #f8d7da;">
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td><strong style="color: #721c24;">HOY</strong></td>
                            <td>Distribuidora XYZ S.A.</td>
                            <td>FC-00126</td>
                            <td>$3,200,000</td>
                            <td><span class="badge badge-vencida">Alta</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+3 days')); ?></td>
                            <td>3 días</td>
                            <td>Servicios Generales SpA</td>
                            <td>FC-00127</td>
                            <td>$1,200,000</td>
                            <td><span class="badge badge-pendiente">Media</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+7 days')); ?></td>
                            <td>7 días</td>
                            <td>Comercial 123 SpA</td>
                            <td>FC-00128</td>
                            <td>$2,800,000</td>
                            <td><span class="badge badge-pendiente">Media</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+15 days')); ?></td>
                            <td>15 días</td>
                            <td>Proveedor ABC Ltda.</td>
                            <td>FC-00129</td>
                            <td>$5,500,000</td>
                            <td><span class="badge badge-pagada">Baja</span></td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime('+28 days')); ?></td>
                            <td>28 días</td>
                            <td>Importadora Global S.A.</td>
                            <td>FC-00130</td>
                            <td>$8,500,000</td>
                            <td><span class="badge badge-pagada">Baja</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Total Próximos 30 Días:</td>
                            <td>$21,200,000</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tab Por Proveedor -->
        <div id="tab-proveedores" class="tab-content">
            <div class="card">
                <h3>Resumen por Proveedor</h3>
                <button class="btn btn-success" onclick="exportarResumen()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
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
                            <td><strong>Proveedor ABC Ltda.</strong></td>
                            <td>15</td>
                            <td>3</td>
                            <td>$8,500,000</td>
                            <td>$3,500,000</td>
                            <td>30 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(1)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Distribuidora XYZ S.A.</strong></td>
                            <td>22</td>
                            <td>5</td>
                            <td>$12,300,000</td>
                            <td>$5,200,000</td>
                            <td>45 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(2)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Comercial 123 SpA</strong></td>
                            <td>8</td>
                            <td>2</td>
                            <td>$4,200,000</td>
                            <td>$0</td>
                            <td>60 días</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verDetalle(3)">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Importadora Global S.A.</strong></td>
                            <td>12</td>
                            <td>4</td>
                            <td>$18,500,000</td>
                            <td>$3,500,000</td>
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
                            <td>57</td>
                            <td>14</td>
                            <td>$43,500,000</td>
                            <td>$12,200,000</td>
                            <td>-</td>
                            <td></td>
                        </tr>
                    </tfoot>
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

        function registrarFactura() { alert('Funcionalidad de registrar factura de proveedor'); }
        function registrarPago() { alert('Funcionalidad de registrar pago a proveedor'); }
        function exportarPagos() { window.location.href = '/api/exportacion.php?entidad=pagos&formato=excel'; }
        function exportarCalendario() { window.open('/api/reportes/calendario-pagos.php'); }
        function exportarResumen() { window.location.href = '/api/exportacion.php?entidad=resumen_proveedores&formato=excel'; }
        function verDetalle(id) { alert('Ver detalle del proveedor ' + id); }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
