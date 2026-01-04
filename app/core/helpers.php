<?php
/**
 * Conecta ERP - Funciones Helper
 * Funciones auxiliares globales del sistema
 */

/**
 * Escapar HTML para prevenir XSS
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Obtener configuración de país por código
 */
function getCountryConfig($countryCode) {
    static $countries = null;
    if ($countries === null) {
        $countries = require APP_PATH . '/config/countries.php';
    }
    return $countries[strtoupper($countryCode)] ?? null;
}

/**
 * Obtener configuración de moneda por código
 */
function getCurrencyConfig($currencyCode) {
    static $currencies = null;
    if ($currencies === null) {
        $currencies = require APP_PATH . '/config/currencies.php';
    }
    return $currencies[strtoupper($currencyCode)] ?? null;
}

/**
 * Obtener configuración de idioma por código
 */
function getLanguageConfig($langCode) {
    static $languages = null;
    if ($languages === null) {
        $languages = require APP_PATH . '/config/languages.php';
    }
    return $languages[strtolower($langCode)] ?? null;
}

/**
 * Formatear moneda según configuración
 */
function formatMoney($amount, $currencyCode = null) {
    if ($currencyCode === null) {
        $currencyCode = $_SESSION['currency'] ?? DEFAULT_CURRENCY;
    }

    $config = getCurrencyConfig($currencyCode);
    if (!$config) {
        return number_format($amount, 2);
    }

    $formatted = number_format(
        $amount,
        $config['decimal_places'],
        $config['decimal_separator'],
        $config['thousands_separator']
    );

    if ($config['symbol_position'] === 'before') {
        return $config['symbol'] . ' ' . $formatted;
    } else {
        return $formatted . ' ' . $config['symbol'];
    }
}

/**
 * Formatear fecha según configuración
 */
function formatDate($date, $format = null) {
    if (empty($date)) return '';

    if ($format === null) {
        $format = $_SESSION['date_format'] ?? 'd/m/Y';
    }

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    return $date->format($format);
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
 * Redireccionar
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Obtener usuario actual
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Verificar si usuario está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['authenticated']);
}

/**
 * Verificar si usuario es superadmin
 */
function isSuperAdmin() {
    return isAuthenticated() && ($_SESSION['user_type'] ?? '') === 'superadmin';
}

/**
 * Obtener empresa actual
 */
function getCurrentCompany() {
    return $_SESSION['company'] ?? null;
}

/**
 * Obtener ID de empresa actual
 */
function getCurrentCompanyId() {
    return $_SESSION['company_id'] ?? null;
}

/**
 * Verificar si está en trial
 */
function isInTrial() {
    return ($_SESSION['company_status'] ?? '') === 'trial';
}

/**
 * Verificar si trial está vencido
 */
function isTrialExpired() {
    if (!isset($_SESSION['trial_end_date'])) {
        return false;
    }
    return strtotime($_SESSION['trial_end_date']) < time();
}

/**
 * Obtener días restantes de trial
 */
function getTrialDaysRemaining() {
    if (!isset($_SESSION['trial_end_date'])) {
        return 0;
    }

    $now = time();
    $end = strtotime($_SESSION['trial_end_date']);
    $diff = $end - $now;

    return max(0, ceil($diff / 86400));
}

/**
 * Registrar en log
 */
function logMessage($message, $level = 'info', $file = 'app.log') {
    $logPath = LOG_PATH . '/' . $file;

    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}\n";

    file_put_contents($logPath, $logEntry, FILE_APPEND);
}

/**
 * Validar RUT chileno
 */
function validateRUT($rut) {
    // Limpiar RUT
    $rut = preg_replace('/[^0-9Kk]/', '', $rut);

    if (strlen($rut) < 2) {
        return false;
    }

    $dv = strtoupper(substr($rut, -1));
    $number = substr($rut, 0, -1);

    // Calcular dígito verificador
    $sum = 0;
    $multiplier = 2;

    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $sum += $number[$i] * $multiplier;
        $multiplier = $multiplier == 7 ? 2 : $multiplier + 1;
    }

    $expectedDV = 11 - ($sum % 11);
    if ($expectedDV == 11) {
        $expectedDV = '0';
    } elseif ($expectedDV == 10) {
        $expectedDV = 'K';
    } else {
        $expectedDV = (string)$expectedDV;
    }

    return $dv === $expectedDV;
}

/**
 * Formatear RUT chileno
 */
function formatRUT($rut) {
    // Limpiar
    $rut = preg_replace('/[^0-9Kk]/', '', $rut);

    if (strlen($rut) < 2) {
        return $rut;
    }

    $dv = strtoupper(substr($rut, -1));
    $number = substr($rut, 0, -1);

    // Formatear con puntos y guión
    $number = number_format($number, 0, '', '.');

    return $number . '-' . $dv;
}

/**
 * Generar contraseña segura
 */
function generateSecurePassword($length = 16, $options = []) {
    $uppercase = $options['uppercase'] ?? true;
    $lowercase = $options['lowercase'] ?? true;
    $numbers = $options['numbers'] ?? true;
    $special = $options['special'] ?? true;
    $ambiguous = $options['avoid_ambiguous'] ?? true;

    $chars = '';
    $password = '';

    if ($lowercase) {
        $chars .= $ambiguous ? 'abcdefghjkmnpqrstuvwxyz' : 'abcdefghijklmnopqrstuvwxyz';
    }
    if ($uppercase) {
        $chars .= $ambiguous ? 'ABCDEFGHJKMNPQRSTUVWXYZ' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($numbers) {
        $chars .= $ambiguous ? '23456789' : '0123456789';
    }
    if ($special) {
        $chars .= '!@#$%&*+=-_';
    }

    if (empty($chars)) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }

    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charsLength - 1)];
    }

    return $password;
}

/**
 * Evaluar fortaleza de contraseña
 */
function getPasswordStrength($password) {
    $strength = 0;
    $length = strlen($password);

    // Longitud
    if ($length >= 8) $strength++;
    if ($length >= 12) $strength++;
    if ($length >= 16) $strength++;

    // Complejidad
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;

    // Variedad
    $uniqueChars = count(array_unique(str_split($password)));
    if ($uniqueChars > $length / 2) $strength++;

    if ($strength <= 3) return 'weak';
    if ($strength <= 5) return 'medium';
    if ($strength <= 7) return 'strong';
    return 'very_strong';
}

/**
 * Sanitizar input
 */
function sanitize($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitize($item, $type);
        }, $input);
    }

    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Obtener IP del cliente
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

/**
 * Obtener User Agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Respuesta JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Respuesta de error JSON
 */
function jsonError($message, $statusCode = 400, $errors = []) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], $statusCode);
}

/**
 * Respuesta de éxito JSON
 */
function jsonSuccess($message = 'OK', $data = []) {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}

/**
 * Traducción simple (placeholder para sistema completo)
 */
function __($key, $replacements = []) {
    // TODO: Implementar sistema de traducciones completo
    $translation = $key;

    foreach ($replacements as $search => $replace) {
        $translation = str_replace(":$search", $replace, $translation);
    }

    return $translation;
}

/**
 * Debug dump
 */
function dd(...$vars) {
    if (!APP_DEBUG) {
        return;
    }

    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}
