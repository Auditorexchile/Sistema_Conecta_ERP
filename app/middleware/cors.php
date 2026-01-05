<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware CORS
 * Manejo de Cross-Origin Resource Sharing
 */

class CorsMiddleware {
    private $config;

    public function __construct() {
        // Cargar configuración CORS desde archivo de configuración
        $this->config = $this->loadConfig();
    }

    /**
     * Procesa headers CORS
     */
    public function handle($request, $next) {
        // Obtener origen del request
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Verificar si el origen está permitido
        if ($this->isOriginAllowed($origin)) {
            // Configurar headers CORS
            $this->setCorsHeaders($origin);
        }

        // Manejar preflight request (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Configurar headers adicionales para preflight
            $this->setPreflightHeaders();

            // Responder inmediatamente con 204 No Content
            http_response_code(204);
            exit;
        }

        return $next($request);
    }

    /**
     * Verifica si un origen está permitido
     */
    private function isOriginAllowed($origin) {
        if (!$origin) {
            return false;
        }

        // Si se permiten todos los orígenes
        if ($this->config['allow_all_origins']) {
            return true;
        }

        // Verificar lista de orígenes permitidos
        $allowedOrigins = $this->config['allowed_origins'] ?? [];

        // Verificar origen exacto
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Verificar patrones con wildcard
        foreach ($allowedOrigins as $allowed) {
            if (strpos($allowed, '*') !== false) {
                $pattern = str_replace('*', '.*', $allowed);
                if (preg_match('#^' . $pattern . '$#', $origin)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Establece headers CORS
     */
    private function setCorsHeaders($origin) {
        // Access-Control-Allow-Origin
        if ($this->config['allow_all_origins']) {
            header('Access-Control-Allow-Origin: *');
        } else {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        // Access-Control-Allow-Credentials
        if ($this->config['allow_credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }

        // Access-Control-Allow-Methods
        $methods = implode(', ', $this->config['allowed_methods']);
        header("Access-Control-Allow-Methods: {$methods}");

        // Access-Control-Allow-Headers
        $headers = implode(', ', $this->config['allowed_headers']);
        header("Access-Control-Allow-Headers: {$headers}");

        // Access-Control-Expose-Headers
        if (!empty($this->config['exposed_headers'])) {
            $exposedHeaders = implode(', ', $this->config['exposed_headers']);
            header("Access-Control-Expose-Headers: {$exposedHeaders}");
        }

        // Access-Control-Max-Age (cache preflight)
        if ($this->config['max_age']) {
            header("Access-Control-Max-Age: {$this->config['max_age']}");
        }
    }

    /**
     * Establece headers adicionales para preflight
     */
    private function setPreflightHeaders() {
        // Vary: Origin (para cacheo correcto)
        header('Vary: Origin');

        // Vary: Access-Control-Request-Method
        header('Vary: Access-Control-Request-Method');

        // Vary: Access-Control-Request-Headers
        header('Vary: Access-Control-Request-Headers');
    }

    /**
     * Carga configuración CORS
     */
    private function loadConfig() {
        // Configuración por defecto
        $defaultConfig = [
            // Permitir todos los orígenes (usar solo en desarrollo)
            'allow_all_origins' => env('CORS_ALLOW_ALL', false),

            // Lista de orígenes permitidos
            'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),

            // Métodos HTTP permitidos
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],

            // Headers permitidos
            'allowed_headers' => [
                'Accept',
                'Authorization',
                'Content-Type',
                'X-Requested-With',
                'X-API-Key',
                'X-CSRF-Token',
                'Origin',
            ],

            // Headers expuestos al cliente
            'exposed_headers' => [
                'X-Total-Count',
                'X-Page-Count',
                'X-Per-Page',
                'X-Current-Page',
            ],

            // Permitir credenciales (cookies, auth headers)
            'allow_credentials' => true,

            // Tiempo de cache para preflight (segundos)
            'max_age' => 86400, // 24 horas
        ];

        // Intentar cargar configuración desde archivo
        $configFile = __DIR__ . '/../config/cors.php';
        if (file_exists($configFile)) {
            $customConfig = require $configFile;
            return array_merge($defaultConfig, $customConfig);
        }

        return $defaultConfig;
    }

    /**
     * Agrega origen permitido dinámicamente
     */
    public function addAllowedOrigin($origin) {
        if (!in_array($origin, $this->config['allowed_origins'])) {
            $this->config['allowed_origins'][] = $origin;
        }
    }

    /**
     * Agrega método permitido dinámicamente
     */
    public function addAllowedMethod($method) {
        $method = strtoupper($method);
        if (!in_array($method, $this->config['allowed_methods'])) {
            $this->config['allowed_methods'][] = $method;
        }
    }

    /**
     * Agrega header permitido dinámicamente
     */
    public function addAllowedHeader($header) {
        if (!in_array($header, $this->config['allowed_headers'])) {
            $this->config['allowed_headers'][] = $header;
        }
    }
}

/**
 * Helper function para obtener valor de variable de entorno
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        // Convertir strings booleanos
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;

        return $value;
    }
}
