<?php
/**
 * Conecta ERP - API REST v1 - Analytics/BI
 * Análisis avanzado de datos para Business Intelligence
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/middleware/apiKey.php';

header('Content-Type: application/json; charset=utf-8');

// Middleware de autenticación
$apiKeyMiddleware = new \App\Middleware\ApiKeyMiddleware();
$request = ['path' => $_SERVER['REQUEST_URI']];
$apiKeyMiddleware->handle($request, function($req) { return $req; });

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    sendResponse(405, ['error' => 'Método no permitido']);
}

$db = \App\Core\Database::getInstance();

$tipo = $_GET['tipo'] ?? null;
$idEmpresa = $_GET['id_empresa'] ?? null;

if (!$idEmpresa) {
    sendResponse(400, ['error' => 'id_empresa requerido']);
}

if (!$tipo) {
    sendResponse(400, ['error' => 'tipo requerido']);
}

try {
    switch ($tipo) {
        case 'ventas_tendencia':
            $data = getSalesTrend($db, $idEmpresa);
            break;

        case 'productos_rentables':
            $data = getMostProfitableProducts($db, $idEmpresa);
            break;

        case 'clientes_valor':
            $data = getCustomerLifetimeValue($db, $idEmpresa);
            break;

        case 'forecast_ventas':
            $data = getSalesForecast($db, $idEmpresa);
            break;

        case 'analisis_cohortes':
            $data = getCohortAnalysis($db, $idEmpresa);
            break;

        case 'funnel_ventas':
            $data = getSalesFunnel($db, $idEmpresa);
            break;

        case 'kpis':
            $data = getKPIs($db, $idEmpresa);
            break;

        case 'dashboard':
            $data = getDashboardData($db, $idEmpresa);
            break;

        default:
            sendResponse(400, ['error' => 'Tipo de analítica no válido']);
    }

    sendResponse(200, ['data' => $data]);

} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function getSalesTrend($db, $idEmpresa) {
    $periodo = $_GET['periodo'] ?? '30days'; // 7days, 30days, 12months

    switch ($periodo) {
        case '7days':
            $interval = '7 DAY';
            $groupBy = 'DATE(fecha_emision)';
            break;
        case '12months':
            $interval = '12 MONTH';
            $groupBy = 'DATE_FORMAT(fecha_emision, "%Y-%m")';
            break;
        default:
            $interval = '30 DAY';
            $groupBy = 'DATE(fecha_emision)';
    }

    $stmt = $db->prepare("
        SELECT
            {$groupBy} as periodo,
            COUNT(*) as total_facturas,
            SUM(total) as total_ventas,
            AVG(total) as promedio_venta,
            COUNT(DISTINCT id_cliente) as clientes_unicos
        FROM facturas_venta
        WHERE id_empresa = ?
        AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL {$interval})
        AND estado != 'anulada'
        GROUP BY {$groupBy}
        ORDER BY periodo ASC
    ");

    $stmt->execute([$idEmpresa]);
    $tendencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular crecimiento
    if (count($tendencia) >= 2) {
        $ultimo = end($tendencia)['total_ventas'];
        $anterior = prev($tendencia)['total_ventas'];
        $crecimiento = $anterior > 0 ? (($ultimo - $anterior) / $anterior) * 100 : 0;
    } else {
        $crecimiento = 0;
    }

    return [
        'tendencia' => $tendencia,
        'crecimiento_porcentual' => round($crecimiento, 2),
    ];
}

function getMostProfitableProducts($db, $idEmpresa) {
    $limit = $_GET['limit'] ?? 10;

    $stmt = $db->prepare("
        SELECT
            p.id,
            p.codigo,
            p.nombre,
            COUNT(fvd.id) as veces_vendido,
            SUM(fvd.cantidad) as cantidad_total,
            SUM(fvd.subtotal) as ingresos_totales,
            SUM(fvd.cantidad * p.costo_promedio) as costo_total,
            SUM(fvd.subtotal - (fvd.cantidad * p.costo_promedio)) as utilidad_bruta,
            ROUND((SUM(fvd.subtotal - (fvd.cantidad * p.costo_promedio)) / SUM(fvd.subtotal)) * 100, 2) as margen_porcentual
        FROM facturas_venta_detalle fvd
        INNER JOIN facturas_venta fv ON fvd.id_factura = fv.id
        INNER JOIN productos p ON fvd.id_producto = p.id
        WHERE fv.id_empresa = ?
        AND fv.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        AND fv.estado != 'anulada'
        GROUP BY p.id
        ORDER BY utilidad_bruta DESC
        LIMIT ?
    ");

    $stmt->execute([$idEmpresa, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCustomerLifetimeValue($db, $idEmpresa) {
    $stmt = $db->prepare("
        SELECT
            e.id,
            e.razon_social,
            e.rut,
            COUNT(f.id) as total_compras,
            SUM(f.total) as valor_total,
            AVG(f.total) as ticket_promedio,
            MIN(f.fecha_emision) as primera_compra,
            MAX(f.fecha_emision) as ultima_compra,
            DATEDIFF(MAX(f.fecha_emision), MIN(f.fecha_emision)) as dias_como_cliente,
            ROUND(SUM(f.total) / NULLIF(DATEDIFF(MAX(f.fecha_emision), MIN(f.fecha_emision)), 0), 2) as valor_por_dia
        FROM entidades e
        INNER JOIN facturas_venta f ON e.id = f.id_cliente
        WHERE e.id_empresa = ?
        AND e.tipo = 'cliente'
        AND f.estado != 'anulada'
        GROUP BY e.id
        ORDER BY valor_total DESC
        LIMIT 50
    ");

    $stmt->execute([$idEmpresa]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesForecast($db, $idEmpresa) {
    // Obtener ventas de los últimos 12 meses
    $stmt = $db->prepare("
        SELECT
            DATE_FORMAT(fecha_emision, '%Y-%m') as mes,
            SUM(total) as total_ventas
        FROM facturas_venta
        WHERE id_empresa = ?
        AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND estado != 'anulada'
        GROUP BY DATE_FORMAT(fecha_emision, '%Y-%m')
        ORDER BY mes ASC
    ");

    $stmt->execute([$idEmpresa]);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($historico) < 3) {
        return [
            'historico' => $historico,
            'forecast' => null,
            'message' => 'Datos insuficientes para forecast (mínimo 3 meses)',
        ];
    }

    // Cálculo simple de forecast usando promedio móvil
    $ventas = array_column($historico, 'total_ventas');
    $promedio = array_sum($ventas) / count($ventas);

    // Tendencia (regresión lineal simple)
    $n = count($ventas);
    $sumX = ($n * ($n + 1)) / 2;
    $sumY = array_sum($ventas);
    $sumXY = 0;
    $sumX2 = 0;

    foreach ($ventas as $i => $y) {
        $x = $i + 1;
        $sumXY += $x * $y;
        $sumX2 += $x * $x;
    }

    $pendiente = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    $interseccion = ($sumY - $pendiente * $sumX) / $n;

    // Proyección próximos 3 meses
    $forecast = [];
    for ($i = 1; $i <= 3; $i++) {
        $mesProyeccion = date('Y-m', strtotime("+{$i} month"));
        $valorProyectado = $interseccion + $pendiente * ($n + $i);

        $forecast[] = [
            'mes' => $mesProyeccion,
            'proyeccion' => round($valorProyectado, 2),
            'confianza' => $i === 1 ? 'alta' : ($i === 2 ? 'media' : 'baja'),
        ];
    }

    return [
        'historico' => $historico,
        'forecast' => $forecast,
        'tendencia' => $pendiente > 0 ? 'creciente' : ($pendiente < 0 ? 'decreciente' : 'estable'),
    ];
}

function getCohortAnalysis($db, $idEmpresa) {
    // Análisis de cohortes por mes de primera compra
    $stmt = $db->prepare("
        SELECT
            DATE_FORMAT(primera_compra, '%Y-%m') as cohorte,
            COUNT(DISTINCT id_cliente) as clientes,
            SUM(total_compras) as total_compras,
            SUM(total_gastado) as total_gastado
        FROM (
            SELECT
                id_cliente,
                MIN(fecha_emision) as primera_compra,
                COUNT(*) as total_compras,
                SUM(total) as total_gastado
            FROM facturas_venta
            WHERE id_empresa = ?
            AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND estado != 'anulada'
            GROUP BY id_cliente
        ) cohorts
        GROUP BY DATE_FORMAT(primera_compra, '%Y-%m')
        ORDER BY cohorte ASC
    ");

    $stmt->execute([$idEmpresa]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesFunnel($db, $idEmpresa) {
    // Cotizaciones
    $stmt = $db->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto
        FROM cotizaciones
        WHERE id_empresa = ?
        AND fecha_cotizacion >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$idEmpresa]);
    $cotizaciones = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pedidos
    $stmt = $db->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto
        FROM pedidos_venta
        WHERE id_empresa = ?
        AND fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$idEmpresa]);
    $pedidos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Facturas
    $stmt = $db->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto
        FROM facturas_venta
        WHERE id_empresa = ?
        AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    $facturas = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'cotizaciones' => $cotizaciones,
        'pedidos' => $pedidos,
        'facturas' => $facturas,
        'conversion_cotizacion_pedido' => $cotizaciones['total'] > 0
            ? round(($pedidos['total'] / $cotizaciones['total']) * 100, 2)
            : 0,
        'conversion_pedido_factura' => $pedidos['total'] > 0
            ? round(($facturas['total'] / $pedidos['total']) * 100, 2)
            : 0,
    ];
}

function getKPIs($db, $idEmpresa) {
    // Calcular KPIs principales
    return [
        'ventas' => getSalesKPIs($db, $idEmpresa),
        'clientes' => getCustomerKPIs($db, $idEmpresa),
        'inventario' => getInventoryKPIs($db, $idEmpresa),
        'financiero' => getFinancialKPIs($db, $idEmpresa),
    ];
}

function getSalesKPIs($db, $idEmpresa) {
    // Revenue este mes vs mes anterior
    $stmt = $db->prepare("
        SELECT
            SUM(CASE WHEN MONTH(fecha_emision) = MONTH(CURDATE()) THEN total ELSE 0 END) as mes_actual,
            SUM(CASE WHEN MONTH(fecha_emision) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total ELSE 0 END) as mes_anterior
        FROM facturas_venta
        WHERE id_empresa = ?
        AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    $ventas = $stmt->fetch(PDO::FETCH_ASSOC);

    $crecimiento = $ventas['mes_anterior'] > 0
        ? (($ventas['mes_actual'] - $ventas['mes_anterior']) / $ventas['mes_anterior']) * 100
        : 0;

    return [
        'revenue_mes_actual' => $ventas['mes_actual'],
        'revenue_mes_anterior' => $ventas['mes_anterior'],
        'crecimiento_mom' => round($crecimiento, 2), // Month-over-Month
    ];
}

function getCustomerKPIs($db, $idEmpresa) {
    // CAC (Customer Acquisition Cost) - placeholder
    // LTV (Lifetime Value) - promedio
    $stmt = $db->prepare("
        SELECT AVG(total_cliente) as ltv_promedio
        FROM (
            SELECT id_cliente, SUM(total) as total_cliente
            FROM facturas_venta
            WHERE id_empresa = ?
            AND estado != 'anulada'
            GROUP BY id_cliente
        ) clientes
    ");
    $stmt->execute([$idEmpresa]);
    $ltv = $stmt->fetch(PDO::FETCH_ASSOC)['ltv_promedio'];

    return [
        'ltv_promedio' => round($ltv, 2),
        'cac' => 0, // Requiere datos de marketing
    ];
}

function getInventoryKPIs($db, $idEmpresa) {
    // Rotación de inventario
    // Días de inventario

    return [
        'rotacion_inventario' => 0, // Placeholder
        'dias_inventario' => 0, // Placeholder
    ];
}

function getFinancialKPIs($db, $idEmpresa) {
    // Margen bruto, EBITDA, etc.

    return [
        'margen_bruto' => 0, // Placeholder
        'ebitda' => 0, // Placeholder
    ];
}

function getDashboardData($db, $idEmpresa) {
    return [
        'ventas_hoy' => getSalesToday($db, $idEmpresa),
        'tendencia_7dias' => getSalesTrend($db, $idEmpresa),
        'productos_top' => getMostProfitableProducts($db, $idEmpresa),
        'kpis' => getKPIs($db, $idEmpresa),
    ];
}

function getSalesToday($db, $idEmpresa) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM facturas_venta
        WHERE id_empresa = ?
        AND DATE(fecha_emision) = CURDATE()
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
