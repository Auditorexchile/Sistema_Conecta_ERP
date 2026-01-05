<?php
/**
 * Conecta ERP - API REST v1 - Importación de Datos
 * Importa datos desde Excel, CSV
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

if ($method !== 'POST') {
    sendResponse(405, ['error' => 'Método no permitido']);
}

$db = \App\Core\Database::getInstance();
$auditService = new \App\Services\AuditService();

// Obtener archivo subido
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(400, ['error' => 'Archivo requerido']);
}

$file = $_FILES['file'];
$tabla = $_POST['tabla'] ?? null;

if (!$tabla) {
    sendResponse(400, ['error' => 'Tabla requerida']);
}

// Validar tabla permitida
$tablasPermitidas = [
    'productos', 'entidades', 'empleados',
];

if (!in_array($tabla, $tablasPermitidas)) {
    sendResponse(403, ['error' => 'Tabla no permitida para importación']);
}

try {
    // Detectar formato
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($extension === 'csv') {
        $datos = parseCSV($file['tmp_name']);
    } elseif ($extension === 'xlsx' || $extension === 'xls') {
        $datos = parseExcel($file['tmp_name']);
    } else {
        sendResponse(400, ['error' => 'Formato no soportado. Use CSV o Excel']);
    }

    // Importar datos
    $resultado = importData($tabla, $datos, $auditService);

    sendResponse(200, [
        'message' => 'Importación completada',
        'total' => $resultado['total'],
        'importados' => $resultado['importados'],
        'errores' => $resultado['errores'],
        'detalles_errores' => $resultado['detalles_errores'],
    ]);

} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function parseCSV($filePath) {
    $datos = [];
    $handle = fopen($filePath, 'r');

    if ($handle === false) {
        throw new Exception('No se pudo abrir el archivo CSV');
    }

    // Leer headers
    $headers = fgetcsv($handle);

    if ($headers === false) {
        fclose($handle);
        throw new Exception('El archivo CSV está vacío');
    }

    // Leer datos
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) === count($headers)) {
            $datos[] = array_combine($headers, $row);
        }
    }

    fclose($handle);

    return $datos;
}

function parseExcel($filePath) {
    // Usar PhpSpreadsheet
    require_once __DIR__ . '/../../vendor/autoload.php';

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();

    $datos = [];
    $headers = [];

    foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $rowData = [];
        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getValue();
        }

        if ($rowIndex === 1) {
            $headers = $rowData;
        } else {
            if (count($rowData) === count($headers)) {
                $datos[] = array_combine($headers, $rowData);
            }
        }
    }

    return $datos;
}

function importData($tabla, $datos, $auditService) {
    global $db;

    $total = count($datos);
    $importados = 0;
    $errores = 0;
    $detallesErrores = [];

    $db->beginTransaction();

    try {
        foreach ($datos as $index => $fila) {
            try {
                switch ($tabla) {
                    case 'productos':
                        importProducto($fila);
                        break;

                    case 'entidades':
                        importEntidad($fila);
                        break;

                    case 'empleados':
                        importEmpleado($fila);
                        break;

                    default:
                        throw new Exception("Tabla no soportada: {$tabla}");
                }

                $importados++;

            } catch (Exception $e) {
                $errores++;
                $detallesErrores[] = [
                    'fila' => $index + 2, // +2 porque index empieza en 0 y hay header
                    'error' => $e->getMessage(),
                    'datos' => $fila,
                ];
            }
        }

        $db->commit();

        // Auditoría
        $auditService->log('import', [
            'tabla' => $tabla,
            'total' => $total,
            'importados' => $importados,
            'errores' => $errores,
        ]);

        return [
            'total' => $total,
            'importados' => $importados,
            'errores' => $errores,
            'detalles_errores' => $detallesErrores,
        ];

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function importProducto($fila) {
    global $db;

    $required = ['codigo', 'nombre', 'id_empresa'];
    foreach ($required as $field) {
        if (!isset($fila[$field]) || empty($fila[$field])) {
            throw new Exception("Campo requerido: {$field}");
        }
    }

    // Verificar si ya existe
    $stmt = $db->prepare("SELECT id FROM productos WHERE codigo = ? AND id_empresa = ?");
    $stmt->execute([$fila['codigo'], $fila['id_empresa']]);

    if ($stmt->fetch()) {
        // Actualizar
        $stmt = $db->prepare("
            UPDATE productos
            SET nombre = ?, descripcion = ?, precio_venta = ?, costo_promedio = ?,
                stock_minimo = ?, stock_maximo = ?, activo = ?
            WHERE codigo = ? AND id_empresa = ?
        ");

        $stmt->execute([
            $fila['nombre'],
            $fila['descripcion'] ?? null,
            $fila['precio_venta'] ?? 0,
            $fila['costo_promedio'] ?? 0,
            $fila['stock_minimo'] ?? 0,
            $fila['stock_maximo'] ?? 0,
            $fila['activo'] ?? 1,
            $fila['codigo'],
            $fila['id_empresa'],
        ]);
    } else {
        // Insertar
        $stmt = $db->prepare("
            INSERT INTO productos
            (id_empresa, codigo, nombre, descripcion, tipo, precio_venta,
             costo_promedio, stock_minimo, stock_maximo, activo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $fila['id_empresa'],
            $fila['codigo'],
            $fila['nombre'],
            $fila['descripcion'] ?? null,
            $fila['tipo'] ?? 'producto',
            $fila['precio_venta'] ?? 0,
            $fila['costo_promedio'] ?? 0,
            $fila['stock_minimo'] ?? 0,
            $fila['stock_maximo'] ?? 0,
            $fila['activo'] ?? 1,
        ]);
    }
}

function importEntidad($fila) {
    global $db;

    $required = ['rut', 'razon_social', 'id_empresa'];
    foreach ($required as $field) {
        if (!isset($fila[$field]) || empty($fila[$field])) {
            throw new Exception("Campo requerido: {$field}");
        }
    }

    // Verificar si ya existe
    $stmt = $db->prepare("SELECT id FROM entidades WHERE rut = ? AND id_empresa = ?");
    $stmt->execute([$fila['rut'], $fila['id_empresa']]);

    if ($stmt->fetch()) {
        // Actualizar
        $stmt = $db->prepare("
            UPDATE entidades
            SET razon_social = ?, nombre_fantasia = ?, email = ?,
                telefono = ?, direccion = ?, ciudad = ?
            WHERE rut = ? AND id_empresa = ?
        ");

        $stmt->execute([
            $fila['razon_social'],
            $fila['nombre_fantasia'] ?? null,
            $fila['email'] ?? null,
            $fila['telefono'] ?? null,
            $fila['direccion'] ?? null,
            $fila['ciudad'] ?? null,
            $fila['rut'],
            $fila['id_empresa'],
        ]);
    } else {
        // Insertar
        $stmt = $db->prepare("
            INSERT INTO entidades
            (id_empresa, tipo, rut, razon_social, nombre_fantasia, email,
             telefono, direccion, ciudad, activo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $fila['id_empresa'],
            $fila['tipo'] ?? 'cliente',
            $fila['rut'],
            $fila['razon_social'],
            $fila['nombre_fantasia'] ?? null,
            $fila['email'] ?? null,
            $fila['telefono'] ?? null,
            $fila['direccion'] ?? null,
            $fila['ciudad'] ?? null,
        ]);
    }
}

function importEmpleado($fila) {
    global $db;

    $required = ['rut', 'nombre', 'id_empresa'];
    foreach ($required as $field) {
        if (!isset($fila[$field]) || empty($fila[$field])) {
            throw new Exception("Campo requerido: {$field}");
        }
    }

    $stmt = $db->prepare("SELECT id FROM empleados WHERE rut = ? AND id_empresa = ?");
    $stmt->execute([$fila['rut'], $fila['id_empresa']]);

    if ($stmt->fetch()) {
        throw new Exception("Empleado ya existe con RUT: {$fila['rut']}");
    }

    $stmt = $db->prepare("
        INSERT INTO empleados
        (id_empresa, rut, nombre, apellido, email, telefono, cargo,
         fecha_ingreso, activo, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");

    $stmt->execute([
        $fila['id_empresa'],
        $fila['rut'],
        $fila['nombre'],
        $fila['apellido'] ?? '',
        $fila['email'] ?? null,
        $fila['telefono'] ?? null,
        $fila['cargo'] ?? null,
        $fila['fecha_ingreso'] ?? date('Y-m-d'),
    ]);
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
