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
    <title>Consolidación Financiera - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .consolidacion-container { padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .empresas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .empresa-card { background: white; padding: 20px; border-radius: 8px; border: 2px solid #ddd; cursor: pointer; transition: all 0.3s; }
        .empresa-card:hover { border-color: #3498db; box-shadow: 0 4px 8px rgba(52,152,219,0.2); }
        .empresa-card.selected { border-color: #27ae60; background: #d4edda; }
        .empresa-card h4 { margin: 0 0 10px 0; }
        .empresa-card .info { font-size: 13px; color: #666; margin: 5px 0; }

        .consolidado-table { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; position: sticky; top: 0; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }
        .nivel-1 { font-weight: bold; background: #ecf0f1; }
        .nivel-2 { padding-left: 30px; }
        .nivel-3 { padding-left: 60px; font-size: 13px; color: #555; }

        .totales { background: #3498db; color: white; font-weight: bold; font-size: 16px; }
        .subtotal { background: #95a5a6; color: white; font-weight: 600; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: white; }

        .toolbar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
        .toolbar select, .toolbar input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-box.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-box.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-box .value { font-size: 32px; font-weight: bold; }
        .stat-box .label { font-size: 14px; opacity: 0.9; margin-top: 5px; }

        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="consolidacion-container">
        <div class="page-header">
            <h1><i class="fas fa-sitemap"></i> Consolidación Financiera Multiempresa</h1>
            <div>
                <button class="btn btn-success" onclick="generarConsolidado()">
                    <i class="fas fa-sync"></i> Generar Consolidado
                </button>
                <button class="btn btn-warning" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>

        <div class="alert alert-info">
            <strong><i class="fas fa-info-circle"></i> Consolidación de Estados Financieros</strong><br>
            Seleccione las empresas del grupo para consolidar sus estados financieros. Los ajustes por participación y eliminaciones se aplican automáticamente.
        </div>

        <!-- Selección de Empresas -->
        <div class="card">
            <h3>Empresas del Grupo</h3>
            <div class="empresas-grid" id="empresasGrid">
                <!-- Se cargan dinámicamente -->
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <label>Período:</label>
            <input type="month" id="periodoConsolidacion" value="<?php echo date('Y-m'); ?>">

            <label>Tipo:</label>
            <select id="tipoConsolidado">
                <option value="balance">Balance General</option>
                <option value="resultados">Estado de Resultados</option>
                <option value="flujo">Flujo de Efectivo</option>
                <option value="patrimonio">Cambios en el Patrimonio</option>
            </select>

            <label>Método:</label>
            <select id="metodoConsolidacion">
                <option value="completo">Consolidación Completa</option>
                <option value="proporcional">Consolidación Proporcional</option>
                <option value="participacion">Método de Participación</option>
            </select>

            <button class="btn btn-primary" onclick="actualizarConsolidado()">
                <i class="fas fa-calculator"></i> Calcular
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="value" id="totalActivos">$0</div>
                <div class="label">Total Activos Consolidados</div>
            </div>
            <div class="stat-box green">
                <div class="value" id="totalPasivos">$0</div>
                <div class="label">Total Pasivos Consolidados</div>
            </div>
            <div class="stat-box orange">
                <div class="value" id="patrimonio">$0</div>
                <div class="label">Patrimonio Consolidado</div>
            </div>
            <div class="stat-box blue">
                <div class="value" id="resultado">$0</div>
                <div class="label">Resultado del Período</div>
            </div>
        </div>

        <!-- Balance Consolidado -->
        <div class="card">
            <h3>Balance General Consolidado</h3>
            <div class="consolidado-table">
                <table>
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Empresa Matriz</th>
                            <th>Filial 1</th>
                            <th>Filial 2</th>
                            <th>Ajustes</th>
                            <th>Eliminaciones</th>
                            <th>Consolidado</th>
                        </tr>
                    </thead>
                    <tbody id="consolidadoBody">
                        <tr class="nivel-1">
                            <td>ACTIVOS</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="nivel-1">
                            <td>Activos Corrientes</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Efectivo y Equivalentes</td>
                            <td>$500,000</td>
                            <td>$250,000</td>
                            <td>$150,000</td>
                            <td>$0</td>
                            <td>$0</td>
                            <td><strong>$900,000</strong></td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Cuentas por Cobrar</td>
                            <td>$800,000</td>
                            <td>$400,000</td>
                            <td>$300,000</td>
                            <td>$0</td>
                            <td>($200,000)</td>
                            <td><strong>$1,300,000</strong></td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Inventarios</td>
                            <td>$600,000</td>
                            <td>$300,000</td>
                            <td>$200,000</td>
                            <td>$0</td>
                            <td>($50,000)</td>
                            <td><strong>$1,050,000</strong></td>
                        </tr>
                        <tr class="subtotal">
                            <td>Total Activos Corrientes</td>
                            <td>$1,900,000</td>
                            <td>$950,000</td>
                            <td>$650,000</td>
                            <td>$0</td>
                            <td>($250,000)</td>
                            <td><strong>$3,250,000</strong></td>
                        </tr>
                        <tr class="nivel-1">
                            <td>Activos No Corrientes</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Propiedad, Planta y Equipo</td>
                            <td>$3,000,000</td>
                            <td>$1,500,000</td>
                            <td>$1,000,000</td>
                            <td>$100,000</td>
                            <td>$0</td>
                            <td><strong>$5,600,000</strong></td>
                        </tr>
                        <tr class="nivel-2">
                            <td>Inversiones en Filiales</td>
                            <td>$2,000,000</td>
                            <td>$0</td>
                            <td>$0</td>
                            <td>$0</td>
                            <td>($2,000,000)</td>
                            <td><strong>$0</strong></td>
                        </tr>
                        <tr class="subtotal">
                            <td>Total Activos No Corrientes</td>
                            <td>$5,000,000</td>
                            <td>$1,500,000</td>
                            <td>$1,000,000</td>
                            <td>$100,000</td>
                            <td>($2,000,000)</td>
                            <td><strong>$5,600,000</strong></td>
                        </tr>
                        <tr class="totales">
                            <td>TOTAL ACTIVOS</td>
                            <td>$6,900,000</td>
                            <td>$2,450,000</td>
                            <td>$1,650,000</td>
                            <td>$100,000</td>
                            <td>($2,250,000)</td>
                            <td><strong>$8,850,000</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ajustes de Consolidación -->
        <div class="card">
            <h3>Ajustes y Eliminaciones</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Eliminación</td>
                        <td>Inversión en Filial 1</td>
                        <td>$0</td>
                        <td>$1,200,000</td>
                        <td>Participación 100%</td>
                    </tr>
                    <tr>
                        <td>Eliminación</td>
                        <td>Patrimonio Filial 1</td>
                        <td>$1,200,000</td>
                        <td>$0</td>
                        <td>Contra inversión</td>
                    </tr>
                    <tr>
                        <td>Eliminación</td>
                        <td>Transacciones Intercompañía</td>
                        <td>$200,000</td>
                        <td>$200,000</td>
                        <td>Ventas cruzadas</td>
                    </tr>
                    <tr>
                        <td>Ajuste</td>
                        <td>Utilidad no realizada en inventario</td>
                        <td>$0</td>
                        <td>$50,000</td>
                        <td>Margen 25%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Participaciones No Controladoras -->
        <div class="card">
            <h3>Participaciones No Controladoras</h3>
            <table>
                <tr>
                    <th>Filial</th>
                    <th>% Participación</th>
                    <th>Patrimonio Filial</th>
                    <th>Participación No Controladora</th>
                </tr>
                <tr>
                    <td>Filial 2</td>
                    <td>80%</td>
                    <td>$1,000,000</td>
                    <td><strong>$200,000</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        const empresasGrupo = [
            { id: 1, nombre: 'Matriz S.A.', rut: '76.123.456-7', participacion: 100, tipo: 'Matriz' },
            { id: 2, nombre: 'Filial 1 SpA', rut: '76.234.567-8', participacion: 100, tipo: 'Filial' },
            { id: 3, nombre: 'Filial 2 Ltda', rut: '76.345.678-9', participacion: 80, tipo: 'Filial' },
            { id: 4, nombre: 'Asociada ABC', rut: '76.456.789-0', participacion: 30, tipo: 'Asociada' }
        ];

        let empresasSeleccionadas = new Set([1, 2, 3]);

        document.addEventListener('DOMContentLoaded', function() {
            renderizarEmpresas();
        });

        function renderizarEmpresas() {
            const grid = document.getElementById('empresasGrid');
            grid.innerHTML = empresasGrupo.map(emp => `
                <div class="empresa-card ${empresasSeleccionadas.has(emp.id) ? 'selected' : ''}"
                     onclick="toggleEmpresa(${emp.id})">
                    <h4><i class="fas fa-building"></i> ${emp.nombre}</h4>
                    <div class="info"><strong>RUT:</strong> ${emp.rut}</div>
                    <div class="info"><strong>Tipo:</strong> ${emp.tipo}</div>
                    <div class="info"><strong>Participación:</strong> ${emp.participacion}%</div>
                </div>
            `).join('');
        }

        function toggleEmpresa(id) {
            if (empresasSeleccionadas.has(id)) {
                empresasSeleccionadas.delete(id);
            } else {
                empresasSeleccionadas.add(id);
            }
            renderizarEmpresas();
        }

        async function generarConsolidado() {
            if (empresasSeleccionadas.size < 2) {
                alert('Debe seleccionar al menos 2 empresas para consolidar');
                return;
            }

            const periodo = document.getElementById('periodoConsolidacion').value;
            const tipo = document.getElementById('tipoConsolidado').value;

            alert(`Generando consolidado ${tipo} para período ${periodo}...`);

            // Actualizar estadísticas
            document.getElementById('totalActivos').textContent = '$8,850,000';
            document.getElementById('totalPasivos').textContent = '$5,350,000';
            document.getElementById('patrimonio').textContent = '$3,500,000';
            document.getElementById('resultado').textContent = '$450,000';
        }

        function actualizarConsolidado() {
            generarConsolidado();
        }

        function exportarExcel() {
            window.location.href = '/api/consolidacion/exportar.php?empresas=' + Array.from(empresasSeleccionadas).join(',');
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
