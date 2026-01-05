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
    <title>Contabilidad General - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contabilidad-container { padding: 20px; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .tab.active { background: #2980b9; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #2980b9; }
        .stat-card .label { font-size: 13px; color: #7f8c8d; margin-top: 8px; }

        .balance-row { display: flex; justify-content: space-between; padding: 10px; background: #f8f9fa; margin-bottom: 5px; border-radius: 4px; }
        .balance-row.level-1 { font-weight: bold; background: #e8f4f8; }
        .balance-row.level-2 { padding-left: 30px; }
        .balance-row.level-3 { padding-left: 50px; font-size: 14px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="contabilidad-container">
        <h1><i class="fas fa-calculator"></i> Contabilidad General</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$125,500,000</div>
                <div class="label">Total Activos</div>
            </div>
            <div class="stat-card">
                <div class="value">$75,200,000</div>
                <div class="label">Total Pasivos</div>
            </div>
            <div class="stat-card">
                <div class="value">$50,300,000</div>
                <div class="label">Patrimonio</div>
            </div>
            <div class="stat-card">
                <div class="value">$12,450,000</div>
                <div class="label">Resultado del Ejercicio</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('plan')">Plan de Cuentas</button>
            <button class="tab" onclick="cambiarTab('asientos')">Asientos Contables</button>
            <button class="tab" onclick="cambiarTab('mayor')">Libro Mayor</button>
            <button class="tab" onclick="cambiarTab('balance')">Balance de Comprobación</button>
            <button class="tab" onclick="cambiarTab('estados')">Estados Financieros</button>
        </div>

        <!-- Tab Plan de Cuentas -->
        <div id="tab-plan" class="tab-content active">
            <div class="card">
                <h3>Plan de Cuentas</h3>
                <button class="btn btn-primary" onclick="nuevaCuenta()">
                    <i class="fas fa-plus"></i> Nueva Cuenta
                </button>
                <button class="btn btn-success" onclick="exportarPlan()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Naturaleza</th>
                            <th>Saldo Actual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1</strong></td>
                            <td><strong>ACTIVO</strong></td>
                            <td>Activo</td>
                            <td>1</td>
                            <td>Deudor</td>
                            <td>$125,500,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1.1</td>
                            <td>ACTIVO CIRCULANTE</td>
                            <td>Activo</td>
                            <td>2</td>
                            <td>Deudor</td>
                            <td>$85,300,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1.1.01</td>
                            <td>Caja</td>
                            <td>Activo</td>
                            <td>3</td>
                            <td>Deudor</td>
                            <td>$2,500,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1.1.02</td>
                            <td>Bancos</td>
                            <td>Activo</td>
                            <td>3</td>
                            <td>Deudor</td>
                            <td>$35,800,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1.1.03</td>
                            <td>Clientes / Cuentas por Cobrar</td>
                            <td>Activo</td>
                            <td>3</td>
                            <td>Deudor</td>
                            <td>$42,000,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1.1.04</td>
                            <td>Existencias / Inventario</td>
                            <td>Activo</td>
                            <td>3</td>
                            <td>Deudor</td>
                            <td>$5,000,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>2</strong></td>
                            <td><strong>PASIVO</strong></td>
                            <td>Pasivo</td>
                            <td>1</td>
                            <td>Acreedor</td>
                            <td>$75,200,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2.1</td>
                            <td>PASIVO CIRCULANTE</td>
                            <td>Pasivo</td>
                            <td>2</td>
                            <td>Acreedor</td>
                            <td>$45,200,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2.1.01</td>
                            <td>Proveedores / Cuentas por Pagar</td>
                            <td>Pasivo</td>
                            <td>3</td>
                            <td>Acreedor</td>
                            <td>$28,500,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2.1.02</td>
                            <td>IVA Débito Fiscal</td>
                            <td>Pasivo</td>
                            <td>3</td>
                            <td>Acreedor</td>
                            <td>$8,700,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>3</strong></td>
                            <td><strong>PATRIMONIO</strong></td>
                            <td>Patrimonio</td>
                            <td>1</td>
                            <td>Acreedor</td>
                            <td>$50,300,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>4</strong></td>
                            <td><strong>INGRESOS</strong></td>
                            <td>Resultado</td>
                            <td>1</td>
                            <td>Acreedor</td>
                            <td>$95,800,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>5</strong></td>
                            <td><strong>COSTOS Y GASTOS</strong></td>
                            <td>Resultado</td>
                            <td>1</td>
                            <td>Deudor</td>
                            <td>$83,350,000</td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Asientos Contables -->
        <div id="tab-asientos" class="tab-content">
            <div class="card">
                <h3>Asientos Contables</h3>
                <button class="btn btn-primary" onclick="nuevoAsiento()">
                    <i class="fas fa-plus"></i> Nuevo Asiento
                </button>
                <button class="btn btn-success" onclick="exportarLibroDiario()">
                    <i class="fas fa-file-pdf"></i> Libro Diario
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>N° Asiento</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Glosa</th>
                            <th>Total Debe</th>
                            <th>Total Haber</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>00001</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Ingreso</td>
                            <td>Venta de mercadería</td>
                            <td>$5,950,000</td>
                            <td>$5,950,000</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Aprobado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>00002</strong></td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Egreso</td>
                            <td>Pago de servicios básicos</td>
                            <td>$450,000</td>
                            <td>$450,000</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Aprobado</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Libro Mayor -->
        <div id="tab-mayor" class="tab-content">
            <div class="card">
                <h3>Libro Mayor</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Cuenta</label>
                        <select id="cuentaMayor">
                            <option value="">Seleccionar cuenta...</option>
                            <option value="1.1.02">1.1.02 - Bancos</option>
                            <option value="1.1.03">1.1.03 - Clientes</option>
                            <option value="2.1.01">2.1.01 - Proveedores</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Período</label>
                        <input type="month" id="periodoMayor" value="<?php echo date('Y-m'); ?>">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="consultarMayor()">
                    <i class="fas fa-search"></i> Consultar
                </button>
                <button class="btn btn-success" onclick="exportarMayor()">
                    <i class="fas fa-file-pdf"></i> Exportar
                </button>

                <div style="margin-top: 30px;">
                    <h4>Cuenta: 1.1.02 - Bancos</h4>
                    <p>Saldo Inicial: $32,000,000</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>N° Asiento</th>
                                <th>Glosa</th>
                                <th>Debe</th>
                                <th>Haber</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('d/m/Y'); ?></td>
                                <td>00001</td>
                                <td>Depósito cliente ABC</td>
                                <td>$5,000,000</td>
                                <td>-</td>
                                <td>$37,000,000</td>
                            </tr>
                            <tr>
                                <td><?php echo date('d/m/Y'); ?></td>
                                <td>00002</td>
                                <td>Pago proveedor XYZ</td>
                                <td>-</td>
                                <td>$1,200,000</td>
                                <td>$35,800,000</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="margin-top: 15px;"><strong>Saldo Final: $35,800,000</strong></p>
                </div>
            </div>
        </div>

        <!-- Tab Balance de Comprobación -->
        <div id="tab-balance" class="tab-content">
            <div class="card">
                <h3>Balance de Comprobación</h3>
                <div class="form-group">
                    <label>Período</label>
                    <input type="month" id="periodoBalance" value="<?php echo date('Y-m'); ?>" style="width: 200px;">
                    <button class="btn btn-primary" onclick="generarBalance()">Generar</button>
                    <button class="btn btn-success" onclick="exportarBalance()">
                        <i class="fas fa-file-pdf"></i> Exportar
                    </button>
                </div>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Saldo Deudor</th>
                            <th>Saldo Acreedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.1.01</td>
                            <td>Caja</td>
                            <td>$3,200,000</td>
                            <td>$700,000</td>
                            <td>$2,500,000</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>1.1.02</td>
                            <td>Bancos</td>
                            <td>$48,500,000</td>
                            <td>$12,700,000</td>
                            <td>$35,800,000</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>1.1.03</td>
                            <td>Clientes</td>
                            <td>$52,000,000</td>
                            <td>$10,000,000</td>
                            <td>$42,000,000</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>2.1.01</td>
                            <td>Proveedores</td>
                            <td>$8,500,000</td>
                            <td>$37,000,000</td>
                            <td>-</td>
                            <td>$28,500,000</td>
                        </tr>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td colspan="2">TOTALES</td>
                            <td>$112,200,000</td>
                            <td>$112,200,000</td>
                            <td>$80,300,000</td>
                            <td>$28,500,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Estados Financieros -->
        <div id="tab-estados" class="tab-content">
            <div class="card">
                <h3>Estados Financieros</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <button class="btn btn-primary" onclick="generarBalanceGeneral()">
                        <i class="fas fa-file-pdf"></i> Balance General
                    </button>
                    <button class="btn btn-primary" onclick="generarEstadoResultados()">
                        <i class="fas fa-file-pdf"></i> Estado de Resultados
                    </button>
                    <button class="btn btn-primary" onclick="generarFlujoCaja()">
                        <i class="fas fa-file-pdf"></i> Flujo de Caja
                    </button>
                </div>

                <h4 style="margin-top: 30px;">Balance General (Clasificado)</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
                    <div>
                        <h5>ACTIVOS</h5>
                        <div class="balance-row level-1">
                            <span>ACTIVO CIRCULANTE</span>
                            <span>$85,300,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Caja</span>
                            <span>$2,500,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Bancos</span>
                            <span>$35,800,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Clientes</span>
                            <span>$42,000,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Inventario</span>
                            <span>$5,000,000</span>
                        </div>

                        <div class="balance-row level-1" style="margin-top: 15px;">
                            <span>ACTIVO FIJO</span>
                            <span>$40,200,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Maquinaria</span>
                            <span>$25,000,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Vehículos</span>
                            <span>$15,200,000</span>
                        </div>

                        <div class="balance-row level-1" style="margin-top: 15px; background: #2980b9; color: white;">
                            <span>TOTAL ACTIVOS</span>
                            <span>$125,500,000</span>
                        </div>
                    </div>

                    <div>
                        <h5>PASIVOS Y PATRIMONIO</h5>
                        <div class="balance-row level-1">
                            <span>PASIVO CIRCULANTE</span>
                            <span>$45,200,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Proveedores</span>
                            <span>$28,500,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>IVA Débito</span>
                            <span>$8,700,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Préstamos CP</span>
                            <span>$8,000,000</span>
                        </div>

                        <div class="balance-row level-1" style="margin-top: 15px;">
                            <span>PASIVO NO CIRCULANTE</span>
                            <span>$30,000,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Préstamos LP</span>
                            <span>$30,000,000</span>
                        </div>

                        <div class="balance-row level-1" style="margin-top: 15px;">
                            <span>PATRIMONIO</span>
                            <span>$50,300,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Capital</span>
                            <span>$40,000,000</span>
                        </div>
                        <div class="balance-row level-2">
                            <span>Resultado del Ejercicio</span>
                            <span>$10,300,000</span>
                        </div>

                        <div class="balance-row level-1" style="margin-top: 15px; background: #2980b9; color: white;">
                            <span>TOTAL PASIVO + PATRIMONIO</span>
                            <span>$125,500,000</span>
                        </div>
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

        function nuevaCuenta() { alert('Funcionalidad de nueva cuenta contable'); }
        function nuevoAsiento() { alert('Funcionalidad de nuevo asiento contable'); }
        function consultarMayor() { alert('Consultando libro mayor...'); }
        function generarBalance() { alert('Generando balance de comprobación...'); }
        function generarBalanceGeneral() { window.open('/api/reportes/balance-general.php'); }
        function generarEstadoResultados() { window.open('/api/reportes/estado-resultados.php'); }
        function generarFlujoCaja() { window.open('/api/reportes/flujo-caja.php'); }
        function exportarPlan() { window.location.href = '/api/exportacion.php?entidad=plan_cuentas&formato=excel'; }
        function exportarLibroDiario() { window.open('/api/reportes/libro-diario.php'); }
        function exportarMayor() { window.open('/api/reportes/libro-mayor.php'); }
        function exportarBalance() { window.open('/api/reportes/balance-comprobacion.php'); }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
