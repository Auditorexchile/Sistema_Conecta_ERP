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
    <title>Análisis de Rentabilidad - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .rentabilidad-container { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #16a085; }
        .stat-card .label { font-size: 12px; color: #7f8c8d; margin-top: 8px; }

        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; }
        .tab.active { background: #16a085; color: white; }

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
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .metric-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .metric-box h4 { margin: 0 0 10px; color: #2c3e50; }
        .metric-value { font-size: 32px; font-weight: bold; color: #16a085; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="rentabilidad-container">
        <h1><i class="fas fa-chart-line"></i> Análisis de Rentabilidad</h1>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value">$95,800,000</div>
                <div class="label">Ingresos Totales</div>
            </div>
            <div class="stat-card">
                <div class="value">$83,350,000</div>
                <div class="label">Costos y Gastos</div>
            </div>
            <div class="stat-card">
                <div class="value">$12,450,000</div>
                <div class="label">Utilidad Neta</div>
            </div>
            <div class="stat-card">
                <div class="value">13.0%</div>
                <div class="label">Margen Neto</div>
            </div>
            <div class="stat-card">
                <div class="value">24.8%</div>
                <div class="label">ROE</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('productos')">Por Producto</button>
            <button class="tab" onclick="cambiarTab('clientes')">Por Cliente</button>
            <button class="tab" onclick="cambiarTab('canales')">Por Canal de Venta</button>
            <button class="tab" onclick="cambiarTab('regiones')">Por Región</button>
            <button class="tab" onclick="cambiarTab('indicadores')">Indicadores Financieros</button>
        </div>

        <!-- Tab Por Producto -->
        <div id="tab-productos" class="tab-content active">
            <div class="card">
                <h3>Rentabilidad por Producto/Servicio</h3>
                <button class="btn btn-success" onclick="exportarProductos()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Producto/Servicio</th>
                            <th>Ventas</th>
                            <th>Costo Directo</th>
                            <th>Margen Bruto</th>
                            <th>% Margen</th>
                            <th>Gastos Asignados</th>
                            <th>Utilidad Neta</th>
                            <th>% Rentabilidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Producto A</strong></td>
                            <td>$28,500,000</td>
                            <td>$18,000,000</td>
                            <td>$10,500,000</td>
                            <td style="background: #d4edda;">36.8%</td>
                            <td>$3,200,000</td>
                            <td>$7,300,000</td>
                            <td style="background: #d4edda; font-weight: bold;">25.6%</td>
                        </tr>
                        <tr>
                            <td><strong>Producto B</strong></td>
                            <td>$22,300,000</td>
                            <td>$14,500,000</td>
                            <td>$7,800,000</td>
                            <td style="background: #d4edda;">35.0%</td>
                            <td>$2,500,000</td>
                            <td>$5,300,000</td>
                            <td style="background: #d4edda; font-weight: bold;">23.8%</td>
                        </tr>
                        <tr>
                            <td><strong>Servicio C</strong></td>
                            <td>$18,000,000</td>
                            <td>$8,500,000</td>
                            <td>$9,500,000</td>
                            <td style="background: #d4edda;">52.8%</td>
                            <td>$2,000,000</td>
                            <td>$7,500,000</td>
                            <td style="background: #d4edda; font-weight: bold;">41.7%</td>
                        </tr>
                        <tr>
                            <td><strong>Producto D</strong></td>
                            <td>$15,000,000</td>
                            <td>$12,000,000</td>
                            <td>$3,000,000</td>
                            <td style="background: #fff3cd;">20.0%</td>
                            <td>$1,800,000</td>
                            <td>$1,200,000</td>
                            <td style="background: #fff3cd; font-weight: bold;">8.0%</td>
                        </tr>
                        <tr>
                            <td><strong>Producto E</strong></td>
                            <td>$12,000,000</td>
                            <td>$11,000,000</td>
                            <td>$1,000,000</td>
                            <td style="background: #f8d7da;">8.3%</td>
                            <td>$1,500,000</td>
                            <td style="color: #e74c3c;">-$500,000</td>
                            <td style="background: #f8d7da; font-weight: bold; color: #721c24;">-4.2%</td>
                        </tr>
                        <tr style="background: #e8f4f8; font-weight: bold;">
                            <td>TOTAL</td>
                            <td>$95,800,000</td>
                            <td>$64,000,000</td>
                            <td>$31,800,000</td>
                            <td>33.2%</td>
                            <td>$11,000,000</td>
                            <td>$20,800,000</td>
                            <td>21.7%</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
                    <strong>⚠️ Alerta:</strong> El Producto E está generando pérdidas. Se recomienda revisar estrategia de precios o descontinuar.
                </div>
            </div>
        </div>

        <!-- Tab Por Cliente -->
        <div id="tab-clientes" class="tab-content">
            <div class="card">
                <h3>Rentabilidad por Cliente</h3>
                <button class="btn btn-success" onclick="exportarClientes()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Ventas Anuales</th>
                            <th>Costo Productos</th>
                            <th>Costo Servicio</th>
                            <th>Margen Bruto</th>
                            <th>% Margen</th>
                            <th>LTV (Lifetime Value)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Empresa ABC S.A.</strong></td>
                            <td>$24,000,000</td>
                            <td>$15,000,000</td>
                            <td>$2,500,000</td>
                            <td>$6,500,000</td>
                            <td style="background: #d4edda;">27.1%</td>
                            <td>$18,500,000</td>
                        </tr>
                        <tr>
                            <td><strong>Cliente XYZ Ltda.</strong></td>
                            <td>$18,500,000</td>
                            <td>$11,000,000</td>
                            <td>$2,000,000</td>
                            <td>$5,500,000</td>
                            <td style="background: #d4edda;">29.7%</td>
                            <td>$14,200,000</td>
                        </tr>
                        <tr>
                            <td><strong>Comercializadora 123</strong></td>
                            <td>$15,300,000</td>
                            <td>$9,500,000</td>
                            <td>$1,800,000</td>
                            <td>$4,000,000</td>
                            <td style="background: #d4edda;">26.1%</td>
                            <td>$11,800,000</td>
                        </tr>
                        <tr>
                            <td><strong>Distribuidora Global</strong></td>
                            <td>$12,000,000</td>
                            <td>$8,500,000</td>
                            <td>$1,200,000</td>
                            <td>$2,300,000</td>
                            <td style="background: #fff3cd;">19.2%</td>
                            <td>$8,500,000</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-top: 30px;">Segmentación de Clientes (Análisis ABC)</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 15px;">
                    <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #155724;">Clientes A (Top 20%)</h4>
                        <p style="font-size: 28px; font-weight: bold; color: #155724; margin: 10px 0;">12</p>
                        <p style="margin: 0;">Generan 68% ingresos</p>
                    </div>
                    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #856404;">Clientes B (Medio 30%)</h4>
                        <p style="font-size: 28px; font-weight: bold; color: #856404; margin: 10px 0;">25</p>
                        <p style="margin: 0;">Generan 22% ingresos</p>
                    </div>
                    <div style="background: #f8d7da; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4 style="margin: 0; color: #721c24;">Clientes C (Resto 50%)</h4>
                        <p style="font-size: 28px; font-weight: bold; color: #721c24; margin: 10px 0;">48</p>
                        <p style="margin: 0;">Generan 10% ingresos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Por Canal -->
        <div id="tab-canales" class="tab-content">
            <div class="card">
                <h3>Rentabilidad por Canal de Venta</h3>
                <button class="btn btn-success" onclick="exportarCanales()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Canal</th>
                            <th>Ventas</th>
                            <th>Costos Directos</th>
                            <th>Gastos Canal</th>
                            <th>Utilidad</th>
                            <th>% Rentabilidad</th>
                            <th>CAC (Costo Adq. Cliente)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-store"></i> <strong>Venta Directa</strong></td>
                            <td>$42,000,000</td>
                            <td>$28,000,000</td>
                            <td>$5,000,000</td>
                            <td>$9,000,000</td>
                            <td style="background: #d4edda; font-weight: bold;">21.4%</td>
                            <td>$250,000</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-shopping-cart"></i> <strong>E-commerce</strong></td>
                            <td>$28,500,000</td>
                            <td>$18,000,000</td>
                            <td>$3,500,000</td>
                            <td>$7,000,000</td>
                            <td style="background: #d4edda; font-weight: bold;">24.6%</td>
                            <td>$180,000</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-users"></i> <strong>Distribuidores</strong></td>
                            <td>$18,300,000</td>
                            <td>$13,000,000</td>
                            <td>$2,000,000</td>
                            <td>$3,300,000</td>
                            <td style="background: #fff3cd; font-weight: bold;">18.0%</td>
                            <td>$320,000</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-briefcase"></i> <strong>Licitaciones</strong></td>
                            <td>$7,000,000</td>
                            <td>$5,000,000</td>
                            <td>$1,200,000</td>
                            <td>$800,000</td>
                            <td style="background: #fff3cd; font-weight: bold;">11.4%</td>
                            <td>$450,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Por Región -->
        <div id="tab-regiones" class="tab-content">
            <div class="card">
                <h3>Rentabilidad por Región Geográfica</h3>
                <button class="btn btn-success" onclick="exportarRegiones()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>

                <table style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Región</th>
                            <th>Ventas</th>
                            <th>Costos</th>
                            <th>Gastos Operación</th>
                            <th>Utilidad</th>
                            <th>% Rentabilidad</th>
                            <th>Cuota Mercado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Región Metropolitana</strong></td>
                            <td>$52,000,000</td>
                            <td>$34,000,000</td>
                            <td>$6,500,000</td>
                            <td>$11,500,000</td>
                            <td style="background: #d4edda; font-weight: bold;">22.1%</td>
                            <td>12.5%</td>
                        </tr>
                        <tr>
                            <td><strong>Valparaíso</strong></td>
                            <td>$18,500,000</td>
                            <td>$12,000,000</td>
                            <td>$2,500,000</td>
                            <td>$4,000,000</td>
                            <td style="background: #d4edda; font-weight: bold;">21.6%</td>
                            <td>8.3%</td>
                        </tr>
                        <tr>
                            <td><strong>Biobío</strong></td>
                            <td>$15,300,000</td>
                            <td>$10,000,000</td>
                            <td>$2,200,000</td>
                            <td>$3,100,000</td>
                            <td style="background: #d4edda; font-weight: bold;">20.3%</td>
                            <td>7.8%</td>
                        </tr>
                        <tr>
                            <td><strong>Otras Regiones</strong></td>
                            <td>$10,000,000</td>
                            <td>$7,000,000</td>
                            <td>$1,800,000</td>
                            <td>$1,200,000</td>
                            <td style="background: #fff3cd; font-weight: bold;">12.0%</td>
                            <td>5.2%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Indicadores Financieros -->
        <div id="tab-indicadores" class="tab-content">
            <div class="card">
                <h3>Indicadores Financieros y KPIs de Rentabilidad</h3>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
                    <div class="metric-box">
                        <h4>ROE (Return on Equity)</h4>
                        <div class="metric-value">24.8%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">Rendimiento sobre patrimonio</p>
                    </div>

                    <div class="metric-box">
                        <h4>ROA (Return on Assets)</h4>
                        <div class="metric-value">15.2%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">Rendimiento sobre activos</p>
                    </div>

                    <div class="metric-box">
                        <h4>ROCE (Return on Capital Employed)</h4>
                        <div class="metric-value">19.5%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">Rendimiento sobre capital empleado</p>
                    </div>

                    <div class="metric-box">
                        <h4>Margen EBITDA</h4>
                        <div class="metric-value">18.7%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">EBITDA / Ingresos</p>
                    </div>

                    <div class="metric-box">
                        <h4>Margen Operacional</h4>
                        <div class="metric-value">15.3%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">Utilidad operacional / Ingresos</p>
                    </div>

                    <div class="metric-box">
                        <h4>Margen Neto</h4>
                        <div class="metric-value">13.0%</div>
                        <p style="margin: 10px 0 0; color: #7f8c8d;">Utilidad neta / Ingresos</p>
                    </div>
                </div>

                <h4 style="margin-top: 30px;">Comparación con Industria</h4>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Nuestra Empresa</th>
                            <th>Promedio Industria</th>
                            <th>Top 25% Industria</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ROE</td>
                            <td style="font-weight: bold;">24.8%</td>
                            <td>18.5%</td>
                            <td>28.0%</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Sobre Promedio</span></td>
                        </tr>
                        <tr>
                            <td>Margen Neto</td>
                            <td style="font-weight: bold;">13.0%</td>
                            <td>10.2%</td>
                            <td>15.5%</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Sobre Promedio</span></td>
                        </tr>
                        <tr>
                            <td>EBITDA</td>
                            <td style="font-weight: bold;">18.7%</td>
                            <td>16.8%</td>
                            <td>22.0%</td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px;">Sobre Promedio</span></td>
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

        function exportarProductos() { window.location.href = '/api/exportacion.php?entidad=rentabilidad_productos&formato=excel'; }
        function exportarClientes() { window.location.href = '/api/exportacion.php?entidad=rentabilidad_clientes&formato=excel'; }
        function exportarCanales() { window.location.href = '/api/exportacion.php?entidad=rentabilidad_canales&formato=excel'; }
        function exportarRegiones() { window.location.href = '/api/exportacion.php?entidad=rentabilidad_regiones&formato=excel'; }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
