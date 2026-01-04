<?php
/**
 * Conecta ERP - Configuración de Aplicación
 * Configuración general del sistema
 */

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

// Definir constantes de aplicación
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Conecta ERP');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Rutas
define('BASE_PATH', realpath(__DIR__ . '/../..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('DATABASE_PATH', BASE_PATH . '/database');

// Base de datos
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_DATABASE'] ?? 'conectae_conectaerpbd');
define('DB_USER', $_ENV['DB_USERNAME'] ?? 'conectae_conectaerpuser');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? 'pt125824caraud');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
define('DB_COLLATION', $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci');

// Sesión
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 7200);
define('SESSION_SECURE', filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('SESSION_HTTPONLY', filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('SESSION_SAMESITE', $_ENV['SESSION_SAMESITE'] ?? 'Strict');

// Seguridad
define('HASH_ALGO', $_ENV['HASH_ALGO'] ?? 'bcrypt');
define('HASH_COST', $_ENV['HASH_COST'] ?? 12);
define('CSRF_ENABLED', filter_var($_ENV['CSRF_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('MAX_LOGIN_ATTEMPTS', $_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5);
define('LOCKOUT_TIME', $_ENV['LOCKOUT_TIME'] ?? 900);

// Trial
define('TRIAL_DAYS', $_ENV['TRIAL_DAYS'] ?? 14);

// Zona horaria
define('DEFAULT_TIMEZONE', $_ENV['DEFAULT_TIMEZONE'] ?? 'America/Santiago');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Locale
define('DEFAULT_LOCALE', $_ENV['DEFAULT_LOCALE'] ?? 'es');
define('DEFAULT_CURRENCY', $_ENV['DEFAULT_CURRENCY'] ?? 'CLP');
define('DEFAULT_COUNTRY', $_ENV['DEFAULT_COUNTRY'] ?? 'CL');

// Uploads
define('MAX_UPLOAD_SIZE', $_ENV['MAX_UPLOAD_SIZE'] ?? 52428800); // 50MB
define('ALLOWED_EXTENSIONS', explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif'));

// Logs
define('LOG_PATH', STORAGE_PATH . '/logs');
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'debug');

// Cache
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('CACHE_TTL', $_ENV['CACHE_TTL'] ?? 3600);

// API
define('API_ENABLED', filter_var($_ENV['API_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('API_RATE_LIMIT', $_ENV['API_RATE_LIMIT'] ?? 60);

// Configuración de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/php-errors.log');
}

// Configuración de sesión
ini_set('session.cookie_httponly', SESSION_HTTPONLY ? 1 : 0);
ini_set('session.cookie_secure', SESSION_SECURE ? 1 : 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', SESSION_SAMESITE);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Versión del sistema
define('APP_VERSION', '1.0.0');
define('APP_BUILD', '20260104');

return [
    'name' => APP_NAME,
    'env' => APP_ENV,
    'debug' => APP_DEBUG,
    'url' => APP_URL,
    'version' => APP_VERSION,
    'build' => APP_BUILD,

    'timezone' => DEFAULT_TIMEZONE,
    'locale' => DEFAULT_LOCALE,
    'currency' => DEFAULT_CURRENCY,
    'country' => DEFAULT_COUNTRY,

    'trial_days' => TRIAL_DAYS,

    'paths' => [
        'base' => BASE_PATH,
        'app' => APP_PATH,
        'public' => PUBLIC_PATH,
        'storage' => STORAGE_PATH,
        'database' => DATABASE_PATH,
        'logs' => LOG_PATH,
        'cache' => CACHE_PATH,
    ],
];
