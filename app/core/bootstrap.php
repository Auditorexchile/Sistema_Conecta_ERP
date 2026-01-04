<?php
/**
 * Conecta ERP - Bootstrap del Sistema
 * Inicialización de la aplicación
 */

// Cargar configuración
require_once __DIR__ . '/../config/app.php';

// Cargar helpers
require_once __DIR__ . '/helpers.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar base de datos
require_once __DIR__ . '/../config/database.php';

// Inicializar base de datos
$db = Database::getInstance();

// Cargar security
require_once __DIR__ . '/security.php';

// Cargar session handler
require_once __DIR__ . '/session.php';

// Establecer zona horaria
if (isset($_SESSION['timezone'])) {
    date_default_timezone_set($_SESSION['timezone']);
} else {
    date_default_timezone_set(DEFAULT_TIMEZONE);
}

// Verificar sesión válida en páginas protegidas
function checkAuthentication() {
    if (!isAuthenticated()) {
        redirect('/public/login.php');
    }
}

// Verificar trial
function checkTrial() {
    if (!isAuthenticated()) {
        return;
    }

    if (isSuperAdmin()) {
        return; // Superadmin sin restricciones
    }

    if (isTrialExpired()) {
        $_SESSION['trial_expired'] = true;
        // No bloqueamos el acceso, solo marcaremos funcionalidades
    }
}

// Middleware de autenticación y trial
function initMiddleware() {
    checkTrial();
}

// Registro de errores personalizados
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $errorType = match($errno) {
        E_ERROR, E_USER_ERROR => 'ERROR',
        E_WARNING, E_USER_WARNING => 'WARNING',
        E_NOTICE, E_USER_NOTICE => 'NOTICE',
        default => 'UNKNOWN'
    };

    $message = "[$errorType] $errstr in $errfile on line $errline";
    logMessage($message, strtolower($errorType), 'errors.log');

    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>$errorType:</strong> $errstr<br>";
        echo "<small>File: $errfile | Line: $errline</small>";
        echo "</div>";
    }

    return true;
});

// Registro de excepciones
set_exception_handler(function($exception) {
    $message = "EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    logMessage($message, 'error', 'exceptions.log');

    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 2px solid #f5c6cb; border-radius: 4px;'>";
        echo "<h3>Exception:</h3>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<h4>Stack Trace:</h4>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        echo "<h1>Error del Sistema</h1>";
        echo "<p>Ha ocurrido un error. Por favor contacte al administrador.</p>";
    }
});
