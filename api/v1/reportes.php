<?php
/**
 * Conecta ERP - API REST v1 - Reportes
 * Generación de reportes en PDF, Excel, CSV
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/middleware/apiKey.php';
require_once __DIR__ . '/../../app/services/ReportService.php';

header('Content-Type: application/json; charset=utf-8');

// Middleware de autenticación
$apiKeyMiddleware = new \App\Middleware\ApiKeyMiddleware();
$request = ['path' => $_SERVER['REQUEST_URI']];
$apiKeyMiddleware->handle($request, function($req) { return $req; });

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    sendResponse(405, ['error' => 'Método no permitido']);
}

$reportService = new \App\Services\ReportService();

// Obtener tipo de reporte
$tipoReporte = $_GET['tipo'] ?? null;

if (!$tipoReporte) {
    sendResponse(400, ['error' => 'Tipo de reporte requerido']);
}

try {
    switch ($tipoReporte) {
        case 'financiero':
            handleFinancialReport($reportService);
            break;

        case 'ventas':
            handleSalesReport($reportService);
            break;

        case 'inventario':
            handleInventoryReport($reportService);
            break;

        case 'cuentas_por_cobrar':
            handleAccountsReceivableReport($reportService);
            break;

        case 'cuentas_por_pagar':
            handleAccountsPayableReport($reportService);
            break;

        case 'rrhh':
            handleHRReport($reportService);
            break;

        default:
            sendResponse(400, ['error' => 'Tipo de reporte no válido']);
    }

} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function handleFinancialReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
    $formato = $_GET['formato'] ?? 'json';

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $data = $reportService->generateFinancialReport($idEmpresa, $fechaInicio, $fechaFin, $formato);

    if ($formato === 'json') {
        sendResponse(200, ['data' => $data]);
    } else {
        // PDF o Excel - descargar archivo
        downloadFile($data);
    }
}

function handleSalesReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $options = [
        'group_by' => $_GET['group_by'] ?? 'day',
    ];

    $data = $reportService->generateSalesReport($idEmpresa, $fechaInicio, $fechaFin, $options);

    sendResponse(200, ['data' => $data]);
}

function handleInventoryReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $data = $reportService->generateInventoryReport($idEmpresa);

    sendResponse(200, ['data' => $data]);
}

function handleAccountsReceivableReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $data = $reportService->generateAccountsReceivableReport($idEmpresa);

    sendResponse(200, ['data' => $data]);
}

function handleAccountsPayableReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $data = $reportService->generateAccountsPayableReport($idEmpresa);

    sendResponse(200, ['data' => $data]);
}

function handleHRReport($reportService) {
    $idEmpresa = $_GET['id_empresa'] ?? null;
    $mes = $_GET['mes'] ?? date('m');
    $ano = $_GET['ano'] ?? date('Y');

    if (!$idEmpresa) {
        sendResponse(400, ['error' => 'id_empresa requerido']);
    }

    $data = $reportService->generateHRReport($idEmpresa, $mes, $ano);

    sendResponse(200, ['data' => $data]);
}

function downloadFile($filePath) {
    if (!file_exists($filePath)) {
        sendResponse(404, ['error' => 'Archivo no encontrado']);
    }

    $filename = basename($filePath);
    $mimeType = mime_content_type($filePath);

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);
    exit;
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
