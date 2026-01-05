<?php
/**
 * Conecta ERP - API REST v1 - Empresas
 * CRUD de empresas
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/middleware/apiKey.php';
require_once __DIR__ . '/../../app/services/AuditService.php';

header('Content-Type: application/json; charset=utf-8');

// Middleware de autenticación
$apiKeyMiddleware = new \App\Middleware\ApiKeyMiddleware();
$request = ['path' => $_SERVER['REQUEST_URI']];
$apiKeyMiddleware->handle($request, function($req) { return $req; });

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;

    case 'POST':
        handlePost();
        break;

    case 'PUT':
        handlePut();
        break;

    case 'DELETE':
        handleDelete();
        break;

    default:
        sendResponse(405, ['error' => 'Método no permitido']);
}

function handleGet() {
    $db = \App\Core\Database::getInstance();

    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("
            SELECT e.*, p.nombre as plan_nombre
            FROM empresas e
            LEFT JOIN planes p ON e.id_plan = p.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            sendResponse(404, ['error' => 'Empresa no encontrada']);
        }

        sendResponse(200, ['data' => $empresa]);
    } else {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if (isset($_GET['activa'])) {
            $where[] = 'e.activa = ?';
            $params[] = $_GET['activa'];
        }

        if (isset($_GET['pais'])) {
            $where[] = 'e.pais = ?';
            $params[] = $_GET['pais'];
        }

        if (isset($_GET['search'])) {
            $where[] = '(e.razon_social LIKE ? OR e.rut LIKE ?)';
            $search = '%' . $_GET['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM empresas e WHERE {$whereClause}");
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare("
            SELECT e.*, p.nombre as plan_nombre
            FROM empresas e
            LEFT JOIN planes p ON e.id_plan = p.id
            WHERE {$whereClause}
            ORDER BY e.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, [
            'data' => $empresas,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ]);
    }
}

function handlePost() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $input = json_decode(file_get_contents('php://input'), true);

    $required = ['razon_social', 'rut', 'pais', 'id_plan'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendResponse(400, ['error' => "Campo requerido: {$field}"]);
        }
    }

    // Validar RUT único
    $stmt = $db->prepare("SELECT id FROM empresas WHERE rut = ?");
    $stmt->execute([$input['rut']]);
    if ($stmt->fetch()) {
        sendResponse(409, ['error' => 'El RUT ya está registrado']);
    }

    // Insertar empresa
    $stmt = $db->prepare("
        INSERT INTO empresas
        (razon_social, nombre_fantasia, rut, giro, direccion, ciudad, region,
         pais, telefono, email, id_plan, activa, trial_hasta, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, DATE_ADD(NOW(), INTERVAL 14 DAY), NOW())
    ");

    $stmt->execute([
        $input['razon_social'],
        $input['nombre_fantasia'] ?? null,
        $input['rut'],
        $input['giro'] ?? null,
        $input['direccion'] ?? null,
        $input['ciudad'] ?? null,
        $input['region'] ?? null,
        $input['pais'],
        $input['telefono'] ?? null,
        $input['email'] ?? null,
        $input['id_plan'],
    ]);

    $idEmpresa = $db->lastInsertId();

    // Auditoría
    $auditService->logChange('empresas', $idEmpresa, 'create', null, $input);

    $stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt->execute([$idEmpresa]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    sendResponse(201, [
        'message' => 'Empresa creada exitosamente',
        'data' => $empresa,
    ]);
}

function handlePut() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(400, ['error' => 'ID de empresa requerido']);
    }

    $stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt->execute([$id]);
    $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datosAnteriores) {
        sendResponse(404, ['error' => 'Empresa no encontrada']);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $allowedFields = ['razon_social', 'nombre_fantasia', 'rut', 'giro', 'direccion',
                      'ciudad', 'region', 'pais', 'telefono', 'email', 'id_plan', 'activa'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "{$field} = ?";
            $params[] = $input[$field];
        }
    }

    if (empty($updates)) {
        sendResponse(400, ['error' => 'No hay campos para actualizar']);
    }

    $params[] = $id;

    $stmt = $db->prepare("
        UPDATE empresas
        SET " . implode(', ', $updates) . ", updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute($params);

    $auditService->logChange('empresas', $id, 'update', $datosAnteriores, $input);

    $stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt->execute([$id]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    sendResponse(200, [
        'message' => 'Empresa actualizada exitosamente',
        'data' => $empresa,
    ]);
}

function handleDelete() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(400, ['error' => 'ID de empresa requerido']);
    }

    $stmt = $db->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt->execute([$id]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empresa) {
        sendResponse(404, ['error' => 'Empresa no encontrada']);
    }

    $stmt = $db->prepare("UPDATE empresas SET activa = 0, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);

    $auditService->logChange('empresas', $id, 'delete', $empresa, null);

    sendResponse(200, ['message' => 'Empresa eliminada exitosamente']);
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
