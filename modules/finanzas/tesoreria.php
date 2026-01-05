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
    <title>Tesorería y Flujo de Caja - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tesoreria-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #9b59b6; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #9b59b6; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #9b59b6; padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .badge { padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-ingreso { background: #d4edda; color: #155724; }
        .badge-egreso { background: #f8d7da; color: #721c24; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }

        .flujo-row { display: flex; justify-content: space-between; padding: 10px; margin-bottom: 5px; border-radius: 4px; }
        .flujo-row.ingreso { background: #d4edda; }
        .flujo-row.egreso { background: #f8d7da; }
        .flujo-row.saldo { background: #d1ecf1; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="tesoreria-container">
        <h1><i class="fas fa-wallet"></i> Tesorería y Flujo de Caja</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$38,300,000</div>
                <div class="label">Disponible Total</div>
            </div>
            <div class="stat-card">
                <div class="value">$2,500,000</div>
                <div class="label">Caja</div>
            </div>
            <div class="stat-card">
                <div class="value">$35,800,000</div>
                <div class="label">Bancos</div>
            </div>
            <div class="stat-card">
                <div class="value">$15,500,000</div>
                <div class="label">Flujo Proyectado 30 Días</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('caja')">Caja</button>
            <button class="tab" onclick="cambiarTab('bancos')">Bancos</button>
            <button class="tab" onclick="cambiarTab('conciliacion')">Conciliación Bancaria</button>
            <button class="tab" onclick="cambiarTab('flujo')">Flujo de Caja</button>
            <button class="tab" onclick="cambiarTab('proyeccion')">Proyección de Flujo</button>
        </div>

        <!-- Tab Caja -->
        <div id="tab-caja" class="tab-content active">
            <div class="card">
                <h3>Movimientos de Caja</h3>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button class="btn btn-success" onclick="nuevoIngresoCaja()">
                        <i class="fas fa-plus"></i> Ingreso
                    </button>
                    <button class="btn btn-danger" onclick="nuevoEgresoCaja()">
                        <i class="fas fa-minus"></i> Egreso
                    </button>
                    <button class="btn btn-warning" onclick="aperturaCaja()">
                        <i class="fas fa-cash-register"></i> Apertura/Cierre Caja
                    </button>
                </div>

                <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center;">
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 12px;">Saldo Inicial</p>
                            <p style="margin: 5px 0 0; font-size: 24px; font-weight: bold; color: #2980b9;">$2,000,000</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 12px;">Movimientos Hoy</p>
                            <p style="margin: 5px 0 0; font-size: 24px; font-weight: bold; color: #27ae60;">+$800,000</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 12px;">Saldo Actual</p>
                            <p style="margin: 5px 0 0; font-size: 24px; font-weight: bold; color: #9b59b6;">$2,500,000</p>
                        </div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Ingreso</th>
                            <th>Egreso</th>
                            <th>Saldo</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo date('d/m/Y H:i'); ?></td>
                            <td><span class="badge badge-ingreso">Ingreso</span></td>
                            <td>Venta en efectivo</td>
                            <td>$500,000</td>
                            <td>-</td>
                            <td>$2,500,000</td>
                            <td>cajero@empresa.cl</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime('-1 hour')); ?></td>
                            <td><span class="badge badge-egreso">Egreso</span></td>
                            <td>Compra de insumos</td>
                            <td>-</td>
                            <td>$200,000</td>
                            <td>$2,000,000</td>
                            <td>cajero@empresa.cl</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Bancos -->
        <div id="tab-bancos" class="tab-content">
            <div class="card">
                <h3>Cuentas Bancarias</h3>
                <button class="btn btn-primary" onclick="nuevaCuentaBanco()">
                    <i class="fas fa-plus"></i> Nueva Cuenta
                </button>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
                    <div style="background: #e8f4f8; padding: 20px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0;">Banco de Chile</h4>
                            <i class="fas fa-university" style="font-size: 32px; color: #3498db;"></i>
                        </div>
                        <p style="margin: 5px 0; color: #7f8c8d; font-size: 12px;">Cuenta Corriente</p>
                        <p style="margin: 5px 0; font-weight: 500;">N° 12345678-9</p>
                        <p style="margin: 15px 0 5px; color: #7f8c8d; font-size: 12px;">Saldo Disponible</p>
                        <p style="margin: 0; font-size: 24px; font-weight: bold; color: #27ae60;">$18,500,000</p>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-primary btn-sm" onclick="verMovimientos(1)">Movimientos</button>
                            <button class="btn btn-success btn-sm" onclick="conciliar(1)">Conciliar</button>
                        </div>
                    </div>

                    <div style="background: #e8f4f8; padding: 20px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0;">Banco Santander</h4>
                            <i class="fas fa-university" style="font-size: 32px; color: #e74c3c;"></i>
                        </div>
                        <p style="margin: 5px 0; color: #7f8c8d; font-size: 12px;">Cuenta Corriente</p>
                        <p style="margin: 5px 0; font-weight: 500;">N° 98765432-1</p>
                        <p style="margin: 15px 0 5px; color: #7f8c8d; font-size: 12px;">Saldo Disponible</p>
                        <p style="margin: 0; font-size: 24px; font-weight: bold; color: #27ae60;">$12,300,000</p>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-primary btn-sm" onclick="verMovimientos(2)">Movimientos</button>
                            <button class="btn btn-success btn-sm" onclick="conciliar(2)">Conciliar</button>
                        </div>
                    </div>

                    <div style="background: #e8f4f8; padding: 20px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0;">BancoEstado</h4>
                            <i class="fas fa-university" style="font-size: 32px; color: #f39c12;"></i>
                        </div>
                        <p style="margin: 5px 0; color: #7f8c8d; font-size: 12px;">Cuenta Vista</p>
                        <p style="margin: 5px 0; font-weight: 500;">N° 55555555-5</p>
                        <p style="margin: 15px 0 5px; color: #7f8c8d; font-size: 12px;">Saldo Disponible</p>
                        <p style="margin: 0; font-size: 24px; font-weight: bold; color: #27ae60;">$5,000,000</p>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-primary btn-sm" onclick="verMovimientos(3)">Movimientos</button>
                            <button class="btn btn-success btn-sm" onclick="conciliar(3)">Conciliar</button>
                        </div>
                    </div>
                </div>

                <h4 style="margin-top: 30px;">Últimos Movimientos Bancarios</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Banco</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Ingreso</th>
                            <th>Egreso</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Banco de Chile</td>
                            <td><span class="badge badge-ingreso">Transferencia</span></td>
                            <td>Pago cliente ABC</td>
                            <td>$5,000,000</td>
                            <td>-</td>
                            <td>$18,500,000</td>
                        </tr>
                        <tr>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Banco Santander</td>
                            <td><span class="badge badge-egreso">Transferencia</span></td>
                            <td>Pago proveedor XYZ</td>
                            <td>-</td>
                            <td>$2,000,000</td>
                            <td>$12,300,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Conciliación Bancaria -->
        <div id="tab-conciliacion" class="tab-content">
            <div class="card">
                <h3>Conciliación Bancaria</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Banco</label>
                        <select id="bancoConciliacion">
                            <option value="1">Banco de Chile - 12345678-9</option>
                            <option value="2">Banco Santander - 98765432-1</option>
                            <option value="3">BancoEstado - 55555555-5</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mes</label>
                        <input type="month" id="mesConciliacion" value="<?php echo date('Y-m'); ?>">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="generarConciliacion()">
                    <i class="fas fa-sync"></i> Generar Conciliación
                </button>
                <button class="btn btn-success" onclick="importarCartola()">
                    <i class="fas fa-file-upload"></i> Importar Cartola
                </button>

                <h4 style="margin-top: 30px;">Conciliación: Banco de Chile - <?php echo date('m/Y'); ?></h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
                    <div>
                        <h5>Según Libros (ERP)</h5>
                        <div class="flujo-row">
                            <span>Saldo Inicial</span>
                            <span>$15,000,000</span>
                        </div>
                        <div class="flujo-row ingreso">
                            <span>(+) Ingresos del mes</span>
                            <span>$25,500,000</span>
                        </div>
                        <div class="flujo-row egreso">
                            <span>(-) Egresos del mes</span>
                            <span>$22,000,000</span>
                        </div>
                        <div class="flujo-row saldo">
                            <span>Saldo según Libros</span>
                            <span>$18,500,000</span>
                        </div>
                    </div>

                    <div>
                        <h5>Según Banco (Cartola)</h5>
                        <div class="flujo-row">
                            <span>Saldo Inicial</span>
                            <span>$15,000,000</span>
                        </div>
                        <div class="flujo-row ingreso">
                            <span>(+) Depósitos</span>
                            <span>$25,800,000</span>
                        </div>
                        <div class="flujo-row egreso">
                            <span>(-) Giros y Cheques</span>
                            <span>$22,500,000</span>
                        </div>
                        <div class="flujo-row saldo">
                            <span>Saldo según Banco</span>
                            <span>$18,300,000</span>
                        </div>
                    </div>
                </div>

                <h5 style="margin-top: 30px;">Partidas Conciliatorias</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Nota de Débito</td>
                            <td>Comisión Bancaria</td>
                            <td>$50,000</td>
                            <td><span class="badge badge-egreso">Pendiente Registro</span></td>
                            <td>
                                <button class="btn btn-success btn-sm">Registrar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Cheque en Tránsito</td>
                            <td>Cheque N° 12345</td>
                            <td>$500,000</td>
                            <td><span class="badge badge-egreso">No Cobrado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm">Verificar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Depósito en Tránsito</td>
                            <td>Transferencia Cliente</td>
                            <td>$350,000</td>
                            <td><span class="badge badge-ingreso">No Acreditado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm">Verificar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 8px;">
                    <strong>Diferencia Conciliatoria: $200,000</strong> (Dentro del rango aceptable)
                </div>
            </div>
        </div>

        <!-- Tab Flujo de Caja -->
        <div id="tab-flujo" class="tab-content">
            <div class="card">
                <h3>Flujo de Caja - <?php echo date('F Y'); ?></h3>
                <button class="btn btn-success" onclick="exportarFlujoCaja()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #e8f4f8;">
                            <td><strong>SALDO INICIAL</strong></td>
                            <td><strong>$32,000,000</strong></td>
                        </tr>
                        <tr style="background: #d4edda;">
                            <td colspan="2"><strong>INGRESOS</strong></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Ventas en Efectivo</td>
                            <td>$45,000,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Cobranza Facturas</td>
                            <td>$28,000,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Otros Ingresos</td>
                            <td>$2,500,000</td>
                        </tr>
                        <tr style="background: #d4edda;">
                            <td><strong>TOTAL INGRESOS</strong></td>
                            <td><strong>$75,500,000</strong></td>
                        </tr>
                        <tr style="background: #f8d7da; margin-top: 10px;">
                            <td colspan="2"><strong>EGRESOS</strong></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Pago a Proveedores</td>
                            <td>$35,000,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Remuneraciones</td>
                            <td>$18,000,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Arriendos</td>
                            <td>$5,000,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Servicios Básicos</td>
                            <td>$2,500,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Impuestos</td>
                            <td>$8,700,000</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Otros Gastos</td>
                            <td>$3,000,000</td>
                        </tr>
                        <tr style="background: #f8d7da;">
                            <td><strong>TOTAL EGRESOS</strong></td>
                            <td><strong>$72,200,000</strong></td>
                        </tr>
                        <tr style="background: #d1ecf1;">
                            <td><strong>FLUJO NETO DEL PERÍODO</strong></td>
                            <td><strong>$3,300,000</strong></td>
                        </tr>
                        <tr style="background: #9b59b6; color: white;">
                            <td><strong>SALDO FINAL</strong></td>
                            <td><strong>$35,300,000</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Proyección de Flujo -->
        <div id="tab-proyeccion" class="tab-content">
            <div class="card">
                <h3>Proyección de Flujo de Caja - Próximos 3 Meses</h3>
                <button class="btn btn-success" onclick="exportarProyeccion()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th><?php echo date('M Y'); ?></th>
                            <th><?php echo date('M Y', strtotime('+1 month')); ?></th>
                            <th><?php echo date('M Y', strtotime('+2 months')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #e8f4f8;">
                            <td><strong>Saldo Inicial</strong></td>
                            <td>$35,300,000</td>
                            <td>$38,600,000</td>
                            <td>$42,100,000</td>
                        </tr>
                        <tr style="background: #d4edda;">
                            <td><strong>Ingresos Proyectados</strong></td>
                            <td>$78,000,000</td>
                            <td>$82,000,000</td>
                            <td>$85,000,000</td>
                        </tr>
                        <tr style="background: #f8d7da;">
                            <td><strong>Egresos Proyectados</strong></td>
                            <td>$74,700,000</td>
                            <td>$78,500,000</td>
                            <td>$80,000,000</td>
                        </tr>
                        <tr style="background: #d1ecf1;">
                            <td><strong>Flujo Neto</strong></td>
                            <td>$3,300,000</td>
                            <td>$3,500,000</td>
                            <td>$5,000,000</td>
                        </tr>
                        <tr style="background: #9b59b6; color: white;">
                            <td><strong>Saldo Final Proyectado</strong></td>
                            <td><strong>$38,600,000</strong></td>
                            <td><strong>$42,100,000</strong></td>
                            <td><strong>$47,100,000</strong></td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 8px;">
                    <h4 style="margin-top: 0;">Análisis de la Proyección</h4>
                    <ul style="margin-bottom: 0;">
                        <li>Flujo de caja proyectado es positivo para los próximos 3 meses</li>
                        <li>Se espera un incremento gradual del 6% mensual en ingresos</li>
                        <li>Los egresos crecen a un ritmo menor (5% mensual)</li>
                        <li>No se requieren líneas de crédito adicionales en el corto plazo</li>
                        <li>Oportunidad de realizar inversiones con el excedente proyectado</li>
                    </ul>
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

        function nuevoIngresoCaja() { alert('Funcionalidad de registro de ingreso en caja'); }
        function nuevoEgresoCaja() { alert('Funcionalidad de registro de egreso en caja'); }
        function aperturaCaja() { alert('Funcionalidad de apertura/cierre de caja'); }
        function nuevaCuentaBanco() { alert('Funcionalidad de nueva cuenta bancaria'); }
        function verMovimientos(id) { alert('Ver movimientos del banco ' + id); }
        function conciliar(id) { alert('Conciliar banco ' + id); }
        function generarConciliacion() { alert('Generando conciliación bancaria...'); }
        function importarCartola() { alert('Importar cartola bancaria desde Excel/CSV'); }
        function exportarFlujoCaja() { window.open('/api/reportes/flujo-caja.php'); }
        function exportarProyeccion() { window.open('/api/reportes/proyeccion-flujo.php'); }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
