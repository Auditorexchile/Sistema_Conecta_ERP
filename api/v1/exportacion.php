<?php
/**
 * Conecta ERP - API REST v1 - Exportación de Datos
 * Exporta datos a Excel, CSV, JSON
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/middleware/apiKey.php';
require_once __DIR__ . '/../../app/utils/ExcelGenerator.php';

header('Content-Type: application/json; charset=utf-8');

// Middleware de autenticación
$apiKeyMiddleware = new \App\Middleware\ApiKeyMiddleware();
$request = ['path' => $_SERVER['REQUEST_URI']];
$apiKeyMiddleware->handle($request, function($req) { return $req; });

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    sendResponse(405, ['error' => 'Método no permitido']);
}

$db = \App\Core\Database::getInstance();

// Obtener parámetros
$tabla = $_GET['tabla'] ?? null;
$formato = $_GET['formato'] ?? 'excel'; // excel, csv, json

if (!$tabla) {
    sendResponse(400, ['error' => 'Tabla requerida']);
}

// Validar tabla permitida
$tablasPermitidas = [
    'productos', 'entidades', 'facturas_venta', 'ordenes_compra',
    'empleados', 'stock', 'movimientos_inventario', 'asientos_contables',
    'nominas', 'proyectos', 'cotizaciones',
];

if (!in_array($tabla, $tablasPermitidas)) {
    sendResponse(403, ['error' => 'Tabla no permitida para exportación']);
}

try {
    // Construir query
    $query = buildExportQuery($tabla);

    // Ejecutar query
    $stmt = $db->prepare($query['sql']);
    $stmt->execute($query['params']);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($datos)) {
        sendResponse(404, ['error' => 'No hay datos para exportar']);
    }

    // Exportar según formato
    switch ($formato) {
        case 'excel':
            exportToExcel($tabla, $datos);
            break;

        case 'csv':
            exportToCSV($tabla, $datos);
            break;

        case 'json':
            exportToJSON($datos);
            break;

        default:
            sendResponse(400, ['error' => 'Formato no soportado']);
    }

} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function buildExportQuery($tabla) {
    $where = ['1=1'];
    $params = [];

    // Filtros comunes
    if (isset($_GET['id_empresa'])) {
        $where[] = 'id_empresa = ?';
        $params[] = $_GET['id_empresa'];
    }

    if (isset($_GET['fecha_desde'])) {
        $fechaField = getFechaField($tabla);
        $where[] = "{$fechaField} >= ?";
        $params[] = $_GET['fecha_desde'];
    }

    if (isset($_GET['fecha_hasta'])) {
        $fechaField = getFechaField($tabla);
        $where[] = "{$fechaField} <= ?";
        $params[] = $_GET['fecha_hasta'];
    }

    if (isset($_GET['activo'])) {
        $where[] = 'activo = ?';
        $params[] = $_GET['activo'];
    }

    $whereClause = implode(' AND ', $where);

    // Límite
    $limit = isset($_GET['limit']) ? ' LIMIT ' . (int)$_GET['limit'] : ' LIMIT 10000';

    return [
        'sql' => "SELECT * FROM {$tabla} WHERE {$whereClause} ORDER BY id DESC {$limit}",
        'params' => $params,
    ];
}

function getFechaField($tabla) {
    $fechaFields = [
        'facturas_venta' => 'fecha_emision',
        'ordenes_compra' => 'fecha_emision',
        'cotizaciones' => 'fecha_cotizacion',
        'nominas' => 'fecha_pago',
        'asientos_contables' => 'fecha',
    ];

    return $fechaFields[$tabla] ?? 'created_at';
}

function exportToExcel($tabla, $datos) {
    $excelGenerator = new \App\Utils\ExcelGenerator();

    $filename = $tabla . '_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = $excelGenerator->exportReport($tabla, $datos, $filename);

    // Descargar archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);

    // Eliminar archivo temporal
    unlink($filePath);

    exit;
}

function exportToCSV($tabla, $datos) {
    $filename = $tabla . '_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Headers
    if (!empty($datos)) {
        fputcsv($output, array_keys($datos[0]));

        // Datos
        foreach ($datos as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

function exportToJSON($datos) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_' . date('Y-m-d_H-i-s') . '.json"');

    echo json_encode([
        'total' => count($datos),
        'exported_at' => date('Y-m-d H:i:s'),
        'data' => $datos,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    exit;
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
