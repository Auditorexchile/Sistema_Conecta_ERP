<?php
/**
 * Controlador: Plan de Cuentas
 * Permite administrar el plan de cuentas jerárquico de hasta 6 niveles
 */

if (!isLoggedIn() || !hasPermission('contabilidad', 'ver')) {
    redirect('/index.php');
}

$db = Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'] ?? 0;

if (!$idEmpresa) {
    setErrorMessage('Debe seleccionar una empresa');
    redirect('/index.php');
}

// Obtener todas las cuentas del plan jerárquicamente
$cuentas = $db->query("
    SELECT *
    FROM con_plan_cuentas
    WHERE id_empresa = :id
    AND eliminado = 0
    ORDER BY codigo_cuenta
", ['id' => $idEmpresa]);

// Organizar cuentas por nivel
$cuentasPorNivel = [];
foreach ($cuentas as $cuenta) {
    $nivel = (int)$cuenta['nivel'];
    if (!isset($cuentasPorNivel[$nivel])) {
        $cuentasPorNivel[$nivel] = [];
    }
    $cuentasPorNivel[$nivel][] = $cuenta;
}

// Estadísticas
$stats = [
    'total' => count($cuentas),
    'nivel_1' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 1 AND eliminado = 0', ['id' => $idEmpresa]),
    'nivel_2' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 2 AND eliminado = 0', ['id' => $idEmpresa]),
    'nivel_3' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 3 AND eliminado = 0', ['id' => $idEmpresa]),
    'nivel_4' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 4 AND eliminado = 0', ['id' => $idEmpresa]),
    'nivel_5' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 5 AND eliminado = 0', ['id' => $idEmpresa]),
    'nivel_6' => $db->count('con_plan_cuentas', 'id_empresa = :id AND nivel = 6 AND eliminado = 0', ['id' => $idEmpresa]),
    'imputables' => $db->count('con_plan_cuentas', 'id_empresa = :id AND imputable = 1 AND eliminado = 0', ['id' => $idEmpresa]),
];

?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-list-alt text-primary"></i>
                Plan de Cuentas
            </h1>
            <p class="text-muted mb-0">Estructura jerárquica de cuentas contables (hasta 6 niveles)</p>
        </div>
        <div>
            <?php if (hasPermission('contabilidad', 'crear')): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">
                    <i class="fas fa-plus"></i> Nueva Cuenta
                </button>
                <button type="button" class="btn btn-primary" onclick="importarPlan()">
                    <i class="fas fa-file-upload"></i> Importar Plan
                </button>
                <button type="button" class="btn btn-secondary" onclick="exportarPlan()">
                    <i class="fas fa-file-download"></i> Exportar
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Estadísticas del plan -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <h4 class="mb-0 text-primary"><?= $stats['total'] ?></h4>
                        <small class="text-muted">Total Cuentas</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_1'] ?></h5>
                        <small class="text-muted">Nivel 1</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_2'] ?></h5>
                        <small class="text-muted">Nivel 2</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_3'] ?></h5>
                        <small class="text-muted">Nivel 3</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_4'] ?></h5>
                        <small class="text-muted">Nivel 4</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_5'] ?></h5>
                        <small class="text-muted">Nivel 5</small>
                    </div>
                    <div class="col-md-1">
                        <h5 class="mb-0"><?= $stats['nivel_6'] ?></h5>
                        <small class="text-muted">Nivel 6</small>
                    </div>
                    <div class="col-md-2">
                        <h5 class="mb-0 text-success"><?= $stats['imputables'] ?></h5>
                        <small class="text-muted">Imputables</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar Cuenta</label>
                        <input type="text" id="buscar-cuenta" class="form-control" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nivel</label>
                        <select id="filtro-nivel" class="form-select">
                            <option value="">Todos los niveles</option>
                            <option value="1">Nivel 1</option>
                            <option value="2">Nivel 2</option>
                            <option value="3">Nivel 3</option>
                            <option value="4">Nivel 4</option>
                            <option value="5">Nivel 5</option>
                            <option value="6">Nivel 6</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Clasificación</label>
                        <select id="filtro-clasificacion" class="form-select">
                            <option value="">Todas</option>
                            <option value="Activo">Activo</option>
                            <option value="Pasivo">Pasivo</option>
                            <option value="Patrimonio">Patrimonio</option>
                            <option value="Ingresos">Ingresos</option>
                            <option value="Gastos">Gastos</option>
                            <option value="Orden">Orden</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select id="filtro-imputable" class="form-select">
                            <option value="">Todas</option>
                            <option value="1">Solo Imputables</option>
                            <option value="0">Solo No Imputables</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de plan de cuentas -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Estructura de Cuentas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-plan-cuentas">
                        <thead>
                            <tr>
                                <th width="120">Código</th>
                                <th>Nombre</th>
                                <th width="100">Nivel</th>
                                <th width="120">Clasificación</th>
                                <th width="100">Naturaleza</th>
                                <th width="100">Imputable</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuentas as $cuenta): ?>
                                <tr class="cuenta-row cuenta-nivel-<?= $cuenta['nivel'] ?>"
                                    data-nivel="<?= $cuenta['nivel'] ?>"
                                    data-clasificacion="<?= $cuenta['clasificacion'] ?>"
                                    data-imputable="<?= $cuenta['imputable'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($cuenta['codigo_cuenta']) ?></strong>
                                        <?php if ($cuenta['bloqueada']): ?>
                                            <i class="fas fa-lock text-danger" title="Cuenta bloqueada"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding-left: <?= ($cuenta['nivel'] - 1) * 30 ?>px;">
                                        <?= htmlspecialchars($cuenta['nombre_cuenta']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">Nivel <?= $cuenta['nivel'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $cuenta['clasificacion'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($cuenta['naturaleza'] === 'Deudora'): ?>
                                            <span class="text-primary"><i class="fas fa-arrow-up"></i> Deudora</span>
                                        <?php else: ?>
                                            <span class="text-danger"><i class="fas fa-arrow-down"></i> Acreedora</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cuenta['imputable']): ?>
                                            <span class="badge bg-success">Imputable</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Título</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="verDetalle(<?= $cuenta['id_cuenta'] ?>)"
                                                title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (hasPermission('contabilidad', 'editar') && !$cuenta['bloqueada']): ?>
                                            <button class="btn btn-sm btn-warning" onclick="editarCuenta(<?= $cuenta['id_cuenta'] ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (hasPermission('contabilidad', 'eliminar')): ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarCuenta(<?= $cuenta['id_cuenta'] ?>)"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones de filtrado
function aplicarFiltros() {
    const buscar = $('#buscar-cuenta').val().toLowerCase();
    const nivel = $('#filtro-nivel').val();
    const clasificacion = $('#filtro-clasificacion').val();
    const imputable = $('#filtro-imputable').val();

    $('#tabla-plan-cuentas tbody tr').each(function() {
        const $row = $(this);
        const texto = $row.text().toLowerCase();
        let mostrar = true;

        if (buscar && !texto.includes(buscar)) {
            mostrar = false;
        }

        if (nivel && $row.data('nivel') != nivel) {
            mostrar = false;
        }

        if (clasificacion && $row.data('clasificacion') !== clasificacion) {
            mostrar = false;
        }

        if (imputable !== '' && $row.data('imputable') != imputable) {
            mostrar = false;
        }

        $row.toggle(mostrar);
    });
}

function limpiarFiltros() {
    $('#buscar-cuenta, #filtro-nivel, #filtro-clasificacion, #filtro-imputable').val('');
    $('#tabla-plan-cuentas tbody tr').show();
}

function exportarPlan() {
    window.location.href = '?module=contabilidad&action=exportar_plan&formato=excel';
}

function eliminarCuenta(idCuenta) {
    ConectaERP.confirm('¿Está seguro de eliminar esta cuenta? Esta acción se registrará en la auditoría.', function() {
        window.location.href = '?module=contabilidad&action=eliminar_cuenta&id=' + idCuenta;
    });
}

// Inicialización
$('#buscar-cuenta').on('keyup', function() {
    aplicarFiltros();
});

$('#filtro-nivel, #filtro-clasificacion, #filtro-imputable').on('change', function() {
    aplicarFiltros();
});
</script>
