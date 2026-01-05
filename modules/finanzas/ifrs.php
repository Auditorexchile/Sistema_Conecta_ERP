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
    <title>IFRS / NIIF - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ifrs-container { padding: 20px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; }
        .tab { padding: 10px 20px; background: #f8f9fa; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #007bff; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }

        .metric { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .metric-value { font-size: 24px; font-weight: bold; color: #3498db; }
        .metric-label { font-size: 12px; color: #666; margin-top: 5px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: white; }

        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="ifrs-container">
        <div class="page-header">
            <h1><i class="fas fa-globe"></i> IFRS / NIIF (Normas Internacionales)</h1>
            <div>
                <button class="btn btn-success" onclick="generarReporteIFRS()">
                    <i class="fas fa-file-pdf"></i> Generar Reporte IFRS
                </button>
                <button class="btn btn-warning" onclick="sincronizar()">
                    <i class="fas fa-sync"></i> Sincronizar con GAAP Local
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('ajustes')">Ajustes IFRS</button>
            <button class="tab" onclick="cambiarTab('conversion')">Conversión Moneda</button>
            <button class="tab" onclick="cambiarTab('arrendamientos')">IFRS 16 - Arrendamientos</button>
            <button class="tab" onclick="cambiarTab('ingresos')">IFRS 15 - Ingresos</button>
            <button class="tab" onclick="cambiarTab('instrumentos')">IFRS 9 - Instrumentos Financieros</button>
        </div>

        <!-- Tab: Ajustes IFRS -->
        <div id="tab-ajustes" class="tab-content active">
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Ajustes IFRS</strong><br>
                Registre los ajustes necesarios para convertir estados financieros locales (GAAP) a IFRS.
            </div>

            <div class="card">
                <h3>Ajustes Pendientes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Norma</th>
                            <th>Descripción</th>
                            <th>Impacto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ajustesBody">
                        <tr><td colspan="6" style="text-align:center;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Conciliación GAAP Local vs IFRS</h3>
                <div class="grid-2">
                    <div>
                        <h4>GAAP Local (Chile)</h4>
                        <table>
                            <tr><td>Activos</td><td>$1,000,000</td></tr>
                            <tr><td>Pasivos</td><td>$600,000</td></tr>
                            <tr><td>Patrimonio</td><td>$400,000</td></tr>
                            <tr><td>Resultado</td><td>$50,000</td></tr>
                        </table>
                    </div>
                    <div>
                        <h4>IFRS</h4>
                        <table>
                            <tr><td>Activos</td><td>$1,050,000</td></tr>
                            <tr><td>Pasivos</td><td>$620,000</td></tr>
                            <tr><td>Patrimonio</td><td>$430,000</td></tr>
                            <tr><td>Resultado</td><td>$55,000</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Conversión Moneda -->
        <div id="tab-conversion" class="tab-content">
            <div class="alert alert-info">
                <strong><i class="fas fa-exchange-alt"></i> IAS 21 - Conversión de Moneda Extranjera</strong><br>
                Gestione tasas de cambio y conversión de operaciones en moneda extranjera.
            </div>

            <div class="card">
                <h3>Tasas de Cambio</h3>
                <button class="btn btn-primary" onclick="nuevaTasa()">
                    <i class="fas fa-plus"></i> Actualizar Tasa
                </button>

                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Moneda</th>
                            <th>Tasa de Cierre</th>
                            <th>Tasa Promedio</th>
                            <th>Fecha Actualización</th>
                            <th>Fuente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>USD - Dólar</td>
                            <td>850.50</td>
                            <td>845.20</td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Banco Central</td>
                        </tr>
                        <tr>
                            <td>EUR - Euro</td>
                            <td>920.75</td>
                            <td>915.30</td>
                            <td><?php echo date('d/m/Y'); ?></td>
                            <td>Banco Central</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Diferencias de Cambio</h3>
                <div class="grid-3">
                    <div class="metric">
                        <div class="metric-value">$12,450</div>
                        <div class="metric-label">Ganancias Realizadas</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">($8,200)</div>
                        <div class="metric-label">Pérdidas Realizadas</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">$4,250</div>
                        <div class="metric-label">Resultado Neto</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: IFRS 16 Arrendamientos -->
        <div id="tab-arrendamientos" class="tab-content">
            <div class="alert alert-info">
                <strong><i class="fas fa-file-contract"></i> IFRS 16 - Arrendamientos</strong><br>
                Contabilización de arrendamientos financieros y operativos bajo IFRS 16.
            </div>

            <div class="card">
                <h3>Contratos de Arrendamiento</h3>
                <button class="btn btn-primary" onclick="nuevoArrendamiento()">
                    <i class="fas fa-plus"></i> Nuevo Arrendamiento
                </button>

                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Activo</th>
                            <th>Inicio</th>
                            <th>Plazo</th>
                            <th>Cuota Mensual</th>
                            <th>Derecho de Uso</th>
                            <th>Pasivo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Oficina Piso 5</td>
                            <td>01/01/2024</td>
                            <td>36 meses</td>
                            <td>$1,500,000</td>
                            <td>$50,000,000</td>
                            <td>$45,000,000</td>
                            <td><span class="badge badge-activo">Vigente</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Cronograma de Amortización</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Cuota</th>
                            <th>Interés</th>
                            <th>Amortización</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mes 1</td><td>$1,500,000</td><td>$225,000</td><td>$1,275,000</td><td>$48,725,000</td></tr>
                        <tr><td>Mes 2</td><td>$1,500,000</td><td>$220,875</td><td>$1,279,125</td><td>$47,445,875</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: IFRS 15 Ingresos -->
        <div id="tab-ingresos" class="tab-content">
            <div class="alert alert-info">
                <strong><i class="fas fa-hand-holding-usd"></i> IFRS 15 - Ingresos de Contratos con Clientes</strong><br>
                Reconocimiento de ingresos según el modelo de 5 pasos de IFRS 15.
            </div>

            <div class="card">
                <h3>Contratos con Clientes</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tipo de Contrato</label>
                        <select>
                            <option>Venta de bienes</option>
                            <option>Prestación de servicios</option>
                            <option>Construcción</option>
                            <option>Licencias</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Método de Reconocimiento</label>
                        <select>
                            <option>En un momento determinado</option>
                            <option>A lo largo del tiempo (% avance)</option>
                            <option>A lo largo del tiempo (esfuerzo)</option>
                        </select>
                    </div>
                </div>

                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contrato</th>
                            <th>Valor Total</th>
                            <th>Reconocido</th>
                            <th>Por Reconocer</th>
                            <th>% Avance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cliente A</td>
                            <td>Construcción Planta</td>
                            <td>$100,000,000</td>
                            <td>$65,000,000</td>
                            <td>$35,000,000</td>
                            <td>65%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: IFRS 9 Instrumentos Financieros -->
        <div id="tab-instrumentos" class="tab-content">
            <div class="alert alert-warning">
                <strong><i class="fas fa-chart-line"></i> IFRS 9 - Instrumentos Financieros</strong><br>
                Clasificación y medición de activos y pasivos financieros. Pérdidas crediticias esperadas.
            </div>

            <div class="card">
                <h3>Activos Financieros</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Instrumento</th>
                            <th>Clasificación</th>
                            <th>Valor Nominal</th>
                            <th>Valor Razonable</th>
                            <th>Pérdida Esperada</th>
                            <th>Valor Neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cuentas por Cobrar</td>
                            <td>Costo Amortizado</td>
                            <td>$50,000,000</td>
                            <td>$50,000,000</td>
                            <td>($1,500,000)</td>
                            <td>$48,500,000</td>
                        </tr>
                        <tr>
                            <td>Inversiones en Acciones</td>
                            <td>Valor Razonable con cambios en PyG</td>
                            <td>$20,000,000</td>
                            <td>$22,500,000</td>
                            <td>-</td>
                            <td>$22,500,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Modelo de Pérdidas Crediticias Esperadas (ECL)</h3>
                <div class="grid-3">
                    <div class="metric">
                        <div class="metric-value">$1,500,000</div>
                        <div class="metric-label">ECL 12 Meses</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">$3,200,000</div>
                        <div class="metric-label">ECL Lifetime</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">3.2%</div>
                        <div class="metric-label">% de Provisión</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cambiarTab(tab) {
            // Ocultar todos
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            // Mostrar seleccionado
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }

        async function generarReporteIFRS() {
            alert('Generando reporte IFRS completo...');
            // Implementar generación de PDF
        }

        async function sincronizar() {
            if (!confirm('¿Sincronizar datos contables locales con ajustes IFRS?')) return;
            alert('Sincronizando...');
        }

        function nuevaTasa() {
            alert('Funcionalidad de actualización de tasas de cambio');
        }

        function nuevoArrendamiento() {
            alert('Funcionalidad de registro de arrendamiento IFRS 16');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
