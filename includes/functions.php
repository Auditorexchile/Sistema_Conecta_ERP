<?php
/**
 * Funciones auxiliares globales del sistema ERP
 */

/**
 * Formatear número con separadores chilenos
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
}

/**
 * Formatear moneda chilena
 */
function formatMoney($amount, $showSymbol = true) {
    $formatted = formatNumber($amount, 0);
    return $showSymbol ? CURRENCY_SYMBOL . ' ' . $formatted : $formatted;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Validar RUT chileno
 */
function validarRut($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);

    if (strlen($rut) < 2) {
        return false;
    }

    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);

    $dvCalculado = calcularDVRut($numero);

    return strtoupper($dv) === strtoupper($dvCalculado);
}

/**
 * Calcular dígito verificador de RUT
 */
function calcularDVRut($rut) {
    $suma = 0;
    $multiplo = 2;

    for ($i = strlen($rut) - 1; $i >= 0; $i--) {
        $suma += $multiplo * $rut[$i];
        $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
    }

    $resto = $suma % 11;
    $dv = 11 - $resto;

    if ($dv === 11) return '0';
    if ($dv === 10) return 'K';
    return (string)$dv;
}

/**
 * Formatear RUT chileno
 */
function formatRut($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);

    if (strlen($rut) < 2) {
        return $rut;
    }

    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);

    return number_format($numero, 0, '', '.') . '-' . strtoupper($dv);
}

/**
 * Sanitizar entrada
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar si usuario está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']);
}

/**
 * Verificar permiso de usuario
 */
function hasPermission($module, $action = 'ver') {
    if (!isLoggedIn()) {
        return false;
    }

    // El administrador tiene todos los permisos
    if ($_SESSION['id_perfil'] == 1) {
        return true;
    }

    $db = Database::getInstance();
    $sql = "SELECT puede_{$action} FROM sys_permisos
            WHERE id_perfil = :id_perfil AND modulo = :modulo";

    $result = $db->queryOne($sql, [
        'id_perfil' => $_SESSION['id_perfil'],
        'modulo' => $module
    ]);

    return $result && $result["puede_{$action}"] == 1;
}

/**
 * Registrar auditoría
 */
function registrarAuditoria($modulo, $accion, $tabla = null, $idRegistro = null, $descripcion = null, $datosAntes = null, $datosDespues = null) {
    if (!AUDIT_ENABLED) {
        return true;
    }

    $db = Database::getInstance();

    $data = [
        'id_usuario' => $_SESSION['id_usuario'] ?? 0,
        'id_empresa' => $_SESSION['id_empresa'] ?? 0,
        'modulo' => $modulo,
        'accion' => $accion,
        'tabla' => $tabla,
        'id_registro' => $idRegistro,
        'descripcion' => $descripcion,
        'datos_antes' => $datosAntes ? json_encode($datosAntes) : null,
        'datos_despues' => $datosDespues ? json_encode($datosDespues) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    return $db->insert('sys_auditoria', $data);
}

/**
 * Redireccionar
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Mostrar mensaje de éxito
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Mostrar mensaje de error
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Obtener y limpiar mensaje de éxito
 */
function getSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        $msg = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $msg;
    }
    return null;
}

/**
 * Obtener y limpiar mensaje de error
 */
function getErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        $msg = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $msg;
    }
    return null;
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Encriptar password
 */
function encryptPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generar número correlativo
 */
function generarNumeroComprobante($idEmpresa, $tipoComprobante, $anio, $mes) {
    $db = Database::getInstance();

    // Obtener último número usado
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(numero_comprobante, '-', -1) AS UNSIGNED)) as ultimo_numero
            FROM con_comprobantes
            WHERE id_empresa = :id_empresa
            AND tipo_comprobante = :tipo
            AND YEAR(fecha_contable) = :anio
            AND MONTH(fecha_contable) = :mes";

    $result = $db->queryOne($sql, [
        'id_empresa' => $idEmpresa,
        'tipo' => $tipoComprobante,
        'anio' => $anio,
        'mes' => $mes
    ]);

    $ultimoNumero = $result['ultimo_numero'] ?? 0;
    $nuevoNumero = $ultimoNumero + 1;

    // Formato: TIPO-AAAAMM-NUMERO
    $abreviatura = [
        'Ingreso' => 'ING',
        'Egreso' => 'EGR',
        'Traspaso' => 'TRA',
        'Ajuste' => 'AJU',
        'Apertura' => 'APE',
        'Cierre' => 'CIE',
        'Provisión' => 'PRO',
        'Centralización' => 'CEN',
        'Reverso' => 'REV'
    ];

    $tipo = $abreviatura[$tipoComprobante] ?? 'COM';

    return sprintf('%s-%04d%02d-%06d', $tipo, $anio, $mes, $nuevoNumero);
}

/**
 * Validar periodo abierto
 */
function validarPeriodoAbierto($idEmpresa, $fecha) {
    $db = Database::getInstance();

    $anio = date('Y', strtotime($fecha));
    $mes = date('n', strtotime($fecha));

    $sql = "SELECT estado FROM con_periodos
            WHERE id_empresa = :id_empresa
            AND anio = :anio
            AND mes = :mes";

    $result = $db->queryOne($sql, [
        'id_empresa' => $idEmpresa,
        'anio' => $anio,
        'mes' => $mes
    ]);

    if (!$result) {
        // Crear periodo si no existe
        $db->insert('con_periodos', [
            'id_empresa' => $idEmpresa,
            'anio' => $anio,
            'mes' => $mes,
            'nombre_periodo' => date('F Y', strtotime($fecha)),
            'fecha_desde' => date('Y-m-01', strtotime($fecha)),
            'fecha_hasta' => date('Y-m-t', strtotime($fecha)),
            'estado' => 'abierto'
        ]);
        return true;
    }

    return $result['estado'] === 'abierto';
}

/**
 * Convertir a JSON response
 */
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Debug
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}
