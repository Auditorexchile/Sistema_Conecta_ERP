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
    <title>Entidades Maestras - Conecta ERP</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .maestras-container { padding: 20px; }
        .tabs { display: flex; gap: 10px; background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .tab.active { background: #3498db; color: white; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }

        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }

        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-inactivo { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="maestras-container">
        <h1><i class="fas fa-database"></i> Entidades Maestras</h1>

        <div class="tabs">
            <button class="tab active" onclick="cambiarTab('paises')">Países</button>
            <button class="tab" onclick="cambiarTab('regiones')">Regiones/Estados</button>
            <button class="tab" onclick="cambiarTab('ciudades')">Ciudades/Comunas</button>
            <button class="tab" onclick="cambiarTab('bancos')">Bancos</button>
            <button class="tab" onclick="cambiarTab('monedas')">Monedas</button>
            <button class="tab" onclick="cambiarTab('categorias')">Categorías</button>
            <button class="tab" onclick="cambiarTab('unidades')">Unidades de Medida</button>
        </div>

        <!-- Tab Países -->
        <div id="tab-paises" class="tab-content active">
            <div class="card">
                <h3>Países</h3>
                <button class="btn btn-primary" onclick="nuevoPais()">
                    <i class="fas fa-plus"></i> Nuevo País
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código ISO</th>
                            <th>País</th>
                            <th>Código Teléfono</th>
                            <th>Moneda</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="paisesBody">
                        <tr>
                            <td>CL</td>
                            <td>Chile</td>
                            <td>+56</td>
                            <td>CLP</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>AR</td>
                            <td>Argentina</td>
                            <td>+54</td>
                            <td>ARS</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>PE</td>
                            <td>Perú</td>
                            <td>+51</td>
                            <td>PEN</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Regiones -->
        <div id="tab-regiones" class="tab-content">
            <div class="card">
                <h3>Regiones / Estados</h3>
                <button class="btn btn-primary" onclick="nuevaRegion()">
                    <i class="fas fa-plus"></i> Nueva Región
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Región/Estado</th>
                            <th>País</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>RM</td>
                            <td>Región Metropolitana</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>V</td>
                            <td>Región de Valparaíso</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Ciudades -->
        <div id="tab-ciudades" class="tab-content">
            <div class="card">
                <h3>Ciudades / Comunas</h3>
                <button class="btn btn-primary" onclick="nuevaCiudad()">
                    <i class="fas fa-plus"></i> Nueva Ciudad
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Ciudad/Comuna</th>
                            <th>Región</th>
                            <th>País</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>13101</td>
                            <td>Santiago Centro</td>
                            <td>Región Metropolitana</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>13201</td>
                            <td>Las Condes</td>
                            <td>Región Metropolitana</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
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
                <h3>Bancos</h3>
                <button class="btn btn-primary" onclick="nuevoBanco()">
                    <i class="fas fa-plus"></i> Nuevo Banco
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Banco</th>
                            <th>País</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>Banco de Chile</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>012</td>
                            <td>Banco del Estado</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>037</td>
                            <td>Banco Santander</td>
                            <td>Chile</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Monedas -->
        <div id="tab-monedas" class="tab-content">
            <div class="card">
                <h3>Monedas</h3>
                <button class="btn btn-primary" onclick="nuevaMoneda()">
                    <i class="fas fa-plus"></i> Nueva Moneda
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código ISO</th>
                            <th>Moneda</th>
                            <th>Símbolo</th>
                            <th>Decimales</th>
                            <th>Tasa Cambio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CLP</td>
                            <td>Peso Chileno</td>
                            <td>$</td>
                            <td>0</td>
                            <td>1.00</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>USD</td>
                            <td>Dólar Estadounidense</td>
                            <td>US$</td>
                            <td>2</td>
                            <td>850.50</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>EUR</td>
                            <td>Euro</td>
                            <td>€</td>
                            <td>2</td>
                            <td>920.75</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Categorías -->
        <div id="tab-categorias" class="tab-content">
            <div class="card">
                <h3>Categorías de Productos/Servicios</h3>
                <button class="btn btn-primary" onclick="nuevaCategoria()">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ELEC</td>
                            <td>Electrónica</td>
                            <td>Producto</td>
                            <td>Productos electrónicos</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>CONS</td>
                            <td>Consultoría</td>
                            <td>Servicio</td>
                            <td>Servicios de consultoría</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Unidades -->
        <div id="tab-unidades" class="tab-content">
            <div class="card">
                <h3>Unidades de Medida</h3>
                <button class="btn btn-primary" onclick="nuevaUnidad()">
                    <i class="fas fa-plus"></i> Nueva Unidad
                </button>
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Unidad</th>
                            <th>Símbolo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>UN</td>
                            <td>Unidad</td>
                            <td>UN</td>
                            <td>Cantidad</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>KG</td>
                            <td>Kilogramo</td>
                            <td>kg</td>
                            <td>Peso</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>LT</td>
                            <td>Litro</td>
                            <td>L</td>
                            <td>Volumen</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>MT</td>
                            <td>Metro</td>
                            <td>m</td>
                            <td>Longitud</td>
                            <td><span class="badge badge-activo">Activo</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </td>
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

        function nuevoPais() { alert('Funcionalidad de nuevo país'); }
        function nuevaRegion() { alert('Funcionalidad de nueva región'); }
        function nuevaCiudad() { alert('Funcionalidad de nueva ciudad'); }
        function nuevoBanco() { alert('Funcionalidad de nuevo banco'); }
        function nuevaMoneda() { alert('Funcionalidad de nueva moneda'); }
        function nuevaCategoria() { alert('Funcionalidad de nueva categoría'); }
        function nuevaUnidad() { alert('Funcionalidad de nueva unidad'); }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
