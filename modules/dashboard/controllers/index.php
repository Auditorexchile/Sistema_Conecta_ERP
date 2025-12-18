<?php
/**
 * Dashboard Principal del Sistema
 */

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance();
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$mesActual = date('n');
$anioActual = date('Y');

// Obtener estadísticas generales
$stats = [];

// Total de cuentas del plan
$stats['total_cuentas'] = $db->count('con_plan_cuentas', 'id_empresa = :id AND eliminado = 0', ['id' => $idEmpresa]);

// Comprobantes del mes actual
$stats['comprobantes_mes'] = $db->count(
    'con_comprobantes',
    'id_empresa = :id AND YEAR(fecha_contable) = :anio AND MONTH(fecha_contable) = :mes AND eliminado = 0',
    ['id' => $idEmpresa, 'anio' => $anioActual, 'mes' => $mesActual]
);

// Comprobantes pendientes (borradores)
$stats['comprobantes_pendientes'] = $db->count(
    'con_comprobantes',
    'id_empresa = :id AND estado = "Borrador" AND eliminado = 0',
    ['id' => $idEmpresa]
);

// Periodos abiertos
$stats['periodos_abiertos'] = $db->count(
    'con_periodos',
    'id_empresa = :id AND estado = "abierto"',
    ['id' => $idEmpresa]
);

// Obtener últimos comprobantes
$ultimosComprobantes = $db->query("
    SELECT c.*, u.nombre_completo as usuario
    FROM con_comprobantes c
    LEFT JOIN sys_usuarios u ON c.created_by = u.id_usuario
    WHERE c.id_empresa = :id AND c.eliminado = 0
    ORDER BY c.created_at DESC
    LIMIT 10
", ['id' => $idEmpresa]);

// Obtener totales del mes por tipo de comprobante
$totalesPorTipo = $db->query("
    SELECT tipo_comprobante, COUNT(*) as cantidad
    FROM con_comprobantes
    WHERE id_empresa = :id
    AND YEAR(fecha_contable) = :anio
    AND MONTH(fecha_contable) = :mes
    AND estado = 'Contabilizado'
    AND eliminado = 0
    GROUP BY tipo_comprobante
", [
    'id' => $idEmpresa,
    'anio' => $anioActual,
    'mes' => $mesActual
]);

// Obtener estado del periodo actual
$periodoActual = $db->queryOne("
    SELECT * FROM con_periodos
    WHERE id_empresa = :id AND anio = :anio AND mes = :mes
", [
    'id' => $idEmpresa,
    'anio' => $anioActual,
    'mes' => $mesActual
]);

// Obtener datos para el gráfico de movimientos
$movimientosMes = $db->query("
    SELECT DATE(fecha_contable) as fecha, COUNT(*) as cantidad
    FROM con_comprobantes
    WHERE id_empresa = :id
    AND YEAR(fecha_contable) = :anio
    AND MONTH(fecha_contable) = :mes
    AND estado = 'Contabilizado'
    AND eliminado = 0
    GROUP BY DATE(fecha_contable)
    ORDER BY fecha
", [
    'id' => $idEmpresa,
    'anio' => $anioActual,
    'mes' => $mesActual
]);

?>

<div class="page-header mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-tachometer-alt text-primary"></i>
        Dashboard Contable
    </h1>
    <p class="text-muted">Resumen general del sistema contable</p>
</div>

<!-- Estadísticas principales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number"><?= $stats['total_cuentas'] ?></div>
                        <div class="stats-label">Cuentas Contables</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number"><?= $stats['comprobantes_mes'] ?></div>
                        <div class="stats-label">Comprobantes del Mes</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number"><?= $stats['comprobantes_pendientes'] ?></div>
                        <div class="stats-label">Pendientes</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number"><?= $stats['periodos_abiertos'] ?></div>
                        <div class="stats-label">Periodos Abiertos</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estado del periodo actual -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    Periodo Actual: <?= date('F Y') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($periodoActual): ?>
                    <div class="alert alert-<?= $periodoActual['estado'] === 'abierto' ? 'success' : 'danger' ?>">
                        <i class="fas fa-<?= $periodoActual['estado'] === 'abierto' ? 'unlock' : 'lock' ?>"></i>
                        Estado: <strong><?= ucfirst($periodoActual['estado']) ?></strong>
                        <?php if ($periodoActual['estado'] !== 'abierto'): ?>
                            - Cerrado el <?= formatDateTime($periodoActual['fecha_cierre']) ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        El periodo actual aún no ha sido creado. Se creará automáticamente al registrar el primer comprobante.
                    </div>
                <?php endif; ?>

                <!-- Resumen de comprobantes por tipo -->
                <?php if (!empty($totalesPorTipo)): ?>
                    <h6 class="mt-4 mb-3">Comprobantes Contabilizados este Mes:</h6>
                    <div class="row">
                        <?php foreach ($totalesPorTipo as $tipo): ?>
                            <div class="col-md-3 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <span class="fw-600"><?= $tipo['tipo_comprobante'] ?></span>
                                    <span class="badge bg-primary"><?= $tipo['cantidad'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-bolt text-warning"></i>
                    Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="acciones-rapidas">
                    <a href="?module=contabilidad&action=comprobante&tipo=ingreso" class="accion-rapida">
                        <i class="fas fa-plus-circle text-success"></i>
                        <div class="titulo">Nuevo Ingreso</div>
                    </a>
                    <a href="?module=contabilidad&action=comprobante&tipo=egreso" class="accion-rapida">
                        <i class="fas fa-minus-circle text-danger"></i>
                        <div class="titulo">Nuevo Egreso</div>
                    </a>
                    <a href="?module=contabilidad&action=comprobante&tipo=traspaso" class="accion-rapida">
                        <i class="fas fa-exchange-alt text-info"></i>
                        <div class="titulo">Nuevo Traspaso</div>
                    </a>
                    <a href="?module=contabilidad&action=libro_diario" class="accion-rapida">
                        <i class="fas fa-book text-primary"></i>
                        <div class="titulo">Libro Diario</div>
                    </a>
                    <a href="?module=contabilidad&action=f29" class="accion-rapida">
                        <i class="fas fa-file-alt text-warning"></i>
                        <div class="titulo">Formulario 29</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos comprobantes -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history text-info"></i>
                    Últimos Comprobantes
                </h5>
                <a href="?module=contabilidad&action=comprobantes_lista" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($ultimosComprobantes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Glosa</th>
                                    <th class="text-end">Total Debe</th>
                                    <th class="text-end">Total Haber</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosComprobantes as $comp): ?>
                                    <tr>
                                        <td>
                                            <a href="?module=contabilidad&action=ver_comprobante&id=<?= $comp['id_comprobante'] ?>">
                                                <?= htmlspecialchars($comp['numero_comprobante']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $comp['tipo_comprobante'] ?></span>
                                        </td>
                                        <td><?= formatDate($comp['fecha_contable']) ?></td>
                                        <td><?= htmlspecialchars(substr($comp['glosa_general'], 0, 50)) ?></td>
                                        <td class="text-end"><?= formatMoney($comp['total_debe']) ?></td>
                                        <td class="text-end"><?= formatMoney($comp['total_haber']) ?></td>
                                        <td>
                                            <span class="badge estado-<?= strtolower($comp['estado']) ?>">
                                                <?= $comp['estado'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($comp['usuario']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        No hay comprobantes registrados aún.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
