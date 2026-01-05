<?php
/**
 * Conecta ERP - API REST v1 - Estadísticas
 * Estadísticas generales del sistema
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

$tipo = $_GET['tipo'] ?? 'general';
$idEmpresa = $_GET['id_empresa'] ?? null;

if (!$idEmpresa) {
    sendResponse(400, ['error' => 'id_empresa requerido']);
}

try {
    switch ($tipo) {
        case 'general':
            $stats = getGeneralStats($db, $idEmpresa);
            break;

        case 'ventas':
            $stats = getSalesStats($db, $idEmpresa);
            break;

        case 'compras':
            $stats = getPurchaseStats($db, $idEmpresa);
            break;

        case 'inventario':
            $stats = getInventoryStats($db, $idEmpresa);
            break;

        case 'financiero':
            $stats = getFinancialStats($db, $idEmpresa);
            break;

        case 'usuarios':
            $stats = getUserStats($db, $idEmpresa);
            break;

        default:
            sendResponse(400, ['error' => 'Tipo de estadística no válido']);
    }

    sendResponse(200, ['data' => $stats]);

} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function getGeneralStats($db, $idEmpresa) {
    $stats = [];

    // Total de clientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM entidades WHERE id_empresa = ? AND tipo = 'cliente' AND activo = 1");
    $stmt->execute([$idEmpresa]);
    $stats['total_clientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de proveedores
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM entidades WHERE id_empresa = ? AND tipo = 'proveedor' AND activo = 1");
    $stmt->execute([$idEmpresa]);
    $stats['total_proveedores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de productos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM productos WHERE id_empresa = ? AND activo = 1");
    $stmt->execute([$idEmpresa]);
    $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de empleados
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM empleados WHERE id_empresa = ? AND activo = 1");
    $stmt->execute([$idEmpresa]);
    $stats['total_empleados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de facturas este mes
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM facturas_venta
        WHERE id_empresa = ?
        AND MONTH(fecha_emision) = MONTH(CURDATE())
        AND YEAR(fecha_emision) = YEAR(CURDATE())
    ");
    $stmt->execute([$idEmpresa]);
    $stats['facturas_este_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    return $stats;
}

function getSalesStats($db, $idEmpresa) {
    $stats = [];

    // Ventas del día
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM facturas_venta
        WHERE id_empresa = ?
        AND DATE(fecha_emision) = CURDATE()
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    $stats['hoy'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ventas del mes
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM facturas_venta
        WHERE id_empresa = ?
        AND MONTH(fecha_emision) = MONTH(CURDATE())
        AND YEAR(fecha_emision) = YEAR(CURDATE())
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    $stats['este_mes'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ventas del año
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM facturas_venta
        WHERE id_empresa = ?
        AND YEAR(fecha_emision) = YEAR(CURDATE())
        AND estado != 'anulada'
    ");
    $stmt->execute([$idEmpresa]);
    $stats['este_ano'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Promedio de venta
    $stats['promedio_venta'] = $stats['este_mes']['cantidad'] > 0
        ? round($stats['este_mes']['total'] / $stats['este_mes']['cantidad'], 2)
        : 0;

    // Mejores clientes del mes
    $stmt = $db->prepare("
        SELECT e.razon_social, COUNT(f.id) as total_compras, SUM(f.total) as total_gastado
        FROM facturas_venta f
        INNER JOIN entidades e ON f.id_cliente = e.id
        WHERE f.id_empresa = ?
        AND MONTH(f.fecha_emision) = MONTH(CURDATE())
        AND YEAR(f.fecha_emision) = YEAR(CURDATE())
        AND f.estado != 'anulada'
        GROUP BY f.id_cliente
        ORDER BY total_gastado DESC
        LIMIT 5
    ");
    $stmt->execute([$idEmpresa]);
    $stats['top_clientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $stats;
}

function getPurchaseStats($db, $idEmpresa) {
    $stats = [];

    // Compras del mes
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM ordenes_compra
        WHERE id_empresa = ?
        AND MONTH(fecha_emision) = MONTH(CURDATE())
        AND YEAR(fecha_emision) = YEAR(CURDATE())
        AND estado = 'aprobada'
    ");
    $stmt->execute([$idEmpresa]);
    $stats['este_mes'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compras del año
    $stmt = $db->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM ordenes_compra
        WHERE id_empresa = ?
        AND YEAR(fecha_emision) = YEAR(CURDATE())
        AND estado = 'aprobada'
    ");
    $stmt->execute([$idEmpresa]);
    $stats['este_ano'] = $stmt->fetch(PDO::FETCH_ASSOC);

    return $stats;
}

function getInventoryStats($db, $idEmpresa) {
    $stats = [];

    // Valor total del inventario
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(s.cantidad * p.costo_promedio), 0) as valor_total
        FROM stock s
        INNER JOIN productos p ON s.id_producto = p.id
        WHERE p.id_empresa = ?
    ");
    $stmt->execute([$idEmpresa]);
    $stats['valor_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'];

    // Productos con stock bajo
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM productos p
        LEFT JOIN (
            SELECT id_producto, SUM(cantidad) as stock_total
            FROM stock
            GROUP BY id_producto
        ) s ON p.id = s.id_producto
        WHERE p.id_empresa = ?
        AND COALESCE(s.stock_total, 0) <= p.stock_minimo
    ");
    $stmt->execute([$idEmpresa]);
    $stats['productos_stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Productos sin stock
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM productos p
        LEFT JOIN (
            SELECT id_producto, SUM(cantidad) as stock_total
            FROM stock
            GROUP BY id_producto
        ) s ON p.id = s.id_producto
        WHERE p.id_empresa = ?
        AND COALESCE(s.stock_total, 0) = 0
    ");
    $stmt->execute([$idEmpresa]);
    $stats['productos_sin_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    return $stats;
}

function getFinancialStats($db, $idEmpresa) {
    $stats = [];

    // Cuentas por cobrar
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(f.total - COALESCE(pagos.total_pagado, 0)), 0) as total
        FROM facturas_venta f
        LEFT JOIN (
            SELECT id_factura, SUM(monto) as total_pagado
            FROM cobranzas
            GROUP BY id_factura
        ) pagos ON f.id = pagos.id_factura
        WHERE f.id_empresa = ?
        AND f.estado = 'emitida'
        AND (f.total - COALESCE(pagos.total_pagado, 0)) > 0
    ");
    $stmt->execute([$idEmpresa]);
    $stats['cuentas_por_cobrar'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Cuentas por pagar
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(oc.total - COALESCE(pagos.total_pagado, 0)), 0) as total
        FROM ordenes_compra oc
        LEFT JOIN (
            SELECT id_orden_compra, SUM(monto) as total_pagado
            FROM pagos
            GROUP BY id_orden_compra
        ) pagos ON oc.id = pagos.id_orden_compra
        WHERE oc.id_empresa = ?
        AND oc.estado = 'aprobada'
        AND (oc.total - COALESCE(pagos.total_pagado, 0)) > 0
    ");
    $stmt->execute([$idEmpresa]);
    $stats['cuentas_por_pagar'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Balance del mes
    $stmt = $db->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN fv.id IS NOT NULL THEN fv.total ELSE 0 END), 0) as ingresos,
            COALESCE(SUM(CASE WHEN oc.id IS NOT NULL THEN oc.total ELSE 0 END), 0) as egresos
        FROM (SELECT ? as id_empresa) e
        LEFT JOIN facturas_venta fv ON fv.id_empresa = e.id_empresa
            AND MONTH(fv.fecha_emision) = MONTH(CURDATE())
            AND YEAR(fv.fecha_emision) = YEAR(CURDATE())
            AND fv.estado != 'anulada'
        LEFT JOIN ordenes_compra oc ON oc.id_empresa = e.id_empresa
            AND MONTH(oc.fecha_emision) = MONTH(CURDATE())
            AND YEAR(oc.fecha_emision) = YEAR(CURDATE())
            AND oc.estado = 'aprobada'
    ");
    $stmt->execute([$idEmpresa]);
    $balance = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['balance_mes'] = [
        'ingresos' => $balance['ingresos'],
        'egresos' => $balance['egresos'],
        'resultado' => $balance['ingresos'] - $balance['egresos'],
    ];

    return $stats;
}

function getUserStats($db, $idEmpresa) {
    $stats = [];

    // Total de usuarios
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE id_empresa = ? AND activo = 1");
    $stmt->execute([$idEmpresa]);
    $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuarios activos hoy
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT id_usuario) as total
        FROM logs_login
        WHERE id_usuario IN (SELECT id FROM usuarios WHERE id_empresa = ?)
        AND DATE(fecha_accion) = CURDATE()
        AND exito = 1
    ");
    $stmt->execute([$idEmpresa]);
    $stats['activos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    return $stats;
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
