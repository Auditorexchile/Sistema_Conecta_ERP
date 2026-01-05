<?php
/**
 * Conecta ERP - API REST v1 - Usuarios
 * CRUD de usuarios con autenticación
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/middleware/apiKey.php';
require_once __DIR__ . '/../../app/services/AuditService.php';

header('Content-Type: application/json; charset=utf-8');

// Middleware de autenticación
$apiKeyMiddleware = new \App\Middleware\ApiKeyMiddleware();
$request = ['path' => $_SERVER['REQUEST_URI']];
$apiKeyMiddleware->handle($request, function($req) { return $req; });

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Enrutamiento
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

/**
 * GET - Listar usuarios o obtener uno específico
 */
function handleGet() {
    $db = \App\Core\Database::getInstance();

    // ID en la URL: /api/v1/usuarios/123
    $id = $_GET['id'] ?? null;

    if ($id) {
        // Obtener usuario específico
        $stmt = $db->prepare("
            SELECT u.id, u.nombre, u.email, u.id_rol, u.activo, u.two_factor_enabled,
                   u.ultimo_acceso, u.created_at, r.nombre as rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.id_rol = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            sendResponse(404, ['error' => 'Usuario no encontrado']);
        }

        sendResponse(200, ['data' => $usuario]);
    } else {
        // Listar usuarios con paginación
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $offset = ($page - 1) * $perPage;

        // Filtros
        $where = ['1=1'];
        $params = [];

        if (isset($_GET['id_empresa'])) {
            $where[] = 'u.id_empresa = ?';
            $params[] = $_GET['id_empresa'];
        }

        if (isset($_GET['activo'])) {
            $where[] = 'u.activo = ?';
            $params[] = $_GET['activo'];
        }

        if (isset($_GET['search'])) {
            $where[] = '(u.nombre LIKE ? OR u.email LIKE ?)';
            $search = '%' . $_GET['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        // Total de registros
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios u WHERE {$whereClause}");
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Obtener usuarios
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare("
            SELECT u.id, u.nombre, u.email, u.id_rol, u.activo, u.two_factor_enabled,
                   u.ultimo_acceso, u.created_at, r.nombre as rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.id_rol = r.id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, [
            'data' => $usuarios,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ]);
    }
}

/**
 * POST - Crear nuevo usuario
 */
function handlePost() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $input = json_decode(file_get_contents('php://input'), true);

    // Validaciones
    $required = ['id_empresa', 'nombre', 'email', 'password', 'id_rol'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendResponse(400, ['error' => "Campo requerido: {$field}"]);
        }
    }

    // Validar email único
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        sendResponse(409, ['error' => 'El email ya está registrado']);
    }

    // Hash de contraseña
    $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);

    // Insertar usuario
    $stmt = $db->prepare("
        INSERT INTO usuarios
        (id_empresa, nombre, email, password, id_rol, activo, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $input['id_empresa'],
        $input['nombre'],
        $input['email'],
        $passwordHash,
        $input['id_rol'],
        $input['activo'] ?? 1,
    ]);

    $idUsuario = $db->lastInsertId();

    // Auditoría
    $auditService->logChange('usuarios', $idUsuario, 'create', null, $input);

    // Obtener usuario creado
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$idUsuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Remover password
    unset($usuario['password']);

    sendResponse(201, [
        'message' => 'Usuario creado exitosamente',
        'data' => $usuario,
    ]);
}

/**
 * PUT - Actualizar usuario
 */
function handlePut() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(400, ['error' => 'ID de usuario requerido']);
    }

    // Obtener datos anteriores
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $datosAnteriores = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datosAnteriores) {
        sendResponse(404, ['error' => 'Usuario no encontrado']);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Campos permitidos para actualizar
    $allowedFields = ['nombre', 'email', 'id_rol', 'activo'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "{$field} = ?";
            $params[] = $input[$field];
        }
    }

    // Password si se proporciona
    if (isset($input['password']) && !empty($input['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($input['password'], PASSWORD_BCRYPT);
    }

    if (empty($updates)) {
        sendResponse(400, ['error' => 'No hay campos para actualizar']);
    }

    $params[] = $id;

    // Actualizar
    $stmt = $db->prepare("
        UPDATE usuarios
        SET " . implode(', ', $updates) . ", updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute($params);

    // Auditoría
    $auditService->logChange('usuarios', $id, 'update', $datosAnteriores, $input);

    // Obtener usuario actualizado
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    unset($usuario['password']);

    sendResponse(200, [
        'message' => 'Usuario actualizado exitosamente',
        'data' => $usuario,
    ]);
}

/**
 * DELETE - Eliminar usuario (soft delete)
 */
function handleDelete() {
    $db = \App\Core\Database::getInstance();
    $auditService = new \App\Services\AuditService();

    $id = $_GET['id'] ?? null;

    if (!$id) {
        sendResponse(400, ['error' => 'ID de usuario requerido']);
    }

    // Verificar que existe
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        sendResponse(404, ['error' => 'Usuario no encontrado']);
    }

    // Soft delete (marcar como inactivo)
    $stmt = $db->prepare("
        UPDATE usuarios
        SET activo = 0, updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    // Auditoría
    $auditService->logChange('usuarios', $id, 'delete', $usuario, null);

    sendResponse(200, ['message' => 'Usuario eliminado exitosamente']);
}

/**
 * Envía respuesta JSON
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
