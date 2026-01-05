<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware de Logging Centralizado
 * Registra todos los requests y responses para auditoría
 */

class LoggingMiddleware {
    private $db;
    private $startTime;
    private $config;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->startTime = microtime(true);
        $this->config = $this->loadConfig();
    }

    /**
     * Procesa y registra el request/response
     */
    public function handle($request, $next) {
        // Capturar información del request
        $requestData = $this->captureRequest();

        // Ejecutar siguiente middleware/controlador
        $response = $next($request);

        // Capturar información del response
        $responseData = $this->captureResponse($response);

        // Calcular tiempo de ejecución
        $executionTime = (microtime(true) - $this->startTime) * 1000; // ms

        // Registrar en base de datos
        if ($this->shouldLog($requestData)) {
            $this->logRequest($requestData, $responseData, $executionTime);
        }

        // Registrar en archivo si está habilitado
        if ($this->config['log_to_file']) {
            $this->logToFile($requestData, $responseData, $executionTime);
        }

        // Alertar si el tiempo de ejecución es muy alto
        if ($executionTime > $this->config['slow_request_threshold']) {
            $this->alertSlowRequest($requestData, $executionTime);
        }

        return $response;
    }

    /**
     * Captura información del request
     */
    private function captureRequest() {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'headers' => $this->getHeaders(),
            'body' => $this->getRequestBody(),
            'user_id' => $_SESSION['id_usuario'] ?? null,
            'empresa_id' => $_SESSION['id_empresa'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Captura información del response
     */
    private function captureResponse($response) {
        return [
            'status_code' => http_response_code(),
            'content_type' => headers_list()[0] ?? 'text/html',
            'content_length' => strlen($response ?? ''),
            'body' => $this->shouldLogResponseBody() ? $response : null,
        ];
    }

    /**
     * Obtiene IP real del cliente
     */
    private function getClientIp() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Si hay múltiples IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Obtiene headers del request
     */
    private function getHeaders() {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', substr($key, 5));

                // Filtrar headers sensibles
                if ($this->isSensitiveHeader($headerName)) {
                    $headers[$headerName] = '***REDACTED***';
                } else {
                    $headers[$headerName] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * Obtiene body del request
     */
    private function getRequestBody() {
        if (!$this->config['log_request_body']) {
            return null;
        }

        $body = file_get_contents('php://input');

        // Si es JSON, redactar campos sensibles
        if ($this->isJson($body)) {
            $data = json_decode($body, true);
            $data = $this->redactSensitiveFields($data);
            return json_encode($data);
        }

        return $body;
    }

    /**
     * Redacta campos sensibles
     */
    private function redactSensitiveFields($data) {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveFields = $this->config['sensitive_fields'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->redactSensitiveFields($value);
            }
        }

        return $data;
    }

    /**
     * Verifica si un header es sensible
     */
    private function isSensitiveHeader($header) {
        $sensitiveHeaders = [
            'AUTHORIZATION',
            'X-API-KEY',
            'COOKIE',
            'SET-COOKIE',
        ];

        return in_array(strtoupper($header), $sensitiveHeaders);
    }

    /**
     * Verifica si debe loguear este request
     */
    private function shouldLog($requestData) {
        // No loguear paths ignorados
        $ignorePaths = $this->config['ignore_paths'];
        foreach ($ignorePaths as $pattern) {
            if (preg_match('#^' . $pattern . '$#', $requestData['path'])) {
                return false;
            }
        }

        // No loguear IPs ignoradas
        if (in_array($requestData['ip_address'], $this->config['ignore_ips'])) {
            return false;
        }

        return true;
    }

    /**
     * Verifica si debe loguear el body del response
     */
    private function shouldLogResponseBody() {
        return $this->config['log_response_body'] && env('APP_DEBUG', false);
    }

    /**
     * Registra request en base de datos
     */
    private function logRequest($requestData, $responseData, $executionTime) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_logs
                (id_usuario, id_empresa, metodo, endpoint, query_string,
                 ip_address, user_agent, referer, headers, request_body,
                 status_code, response_body, execution_time_ms, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $requestData['user_id'],
                $requestData['empresa_id'],
                $requestData['method'],
                $requestData['path'],
                $requestData['query_string'],
                $requestData['ip_address'],
                $requestData['user_agent'],
                $requestData['referer'],
                json_encode($requestData['headers']),
                $requestData['body'],
                $responseData['status_code'],
                $responseData['body'],
                round($executionTime, 2)
            ]);
        } catch (\PDOException $e) {
            // No fallar si no se puede loguear
            error_log("Error logging request: " . $e->getMessage());
        }
    }

    /**
     * Registra en archivo de log
     */
    private function logToFile($requestData, $responseData, $executionTime) {
        $logDir = STORAGE_PATH . '/logs/api/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'api-' . date('Y-m-d') . '.log';

        $logEntry = sprintf(
            "[%s] %s %s - Status: %d - Time: %.2fms - IP: %s - User: %s\n",
            $requestData['timestamp'],
            $requestData['method'],
            $requestData['uri'],
            $responseData['status_code'],
            $executionTime,
            $requestData['ip_address'],
            $requestData['user_id'] ?? 'guest'
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Alerta sobre request lento
     */
    private function alertSlowRequest($requestData, $executionTime) {
        error_log(sprintf(
            "SLOW REQUEST: %s %s took %.2fms (threshold: %dms)",
            $requestData['method'],
            $requestData['uri'],
            $executionTime,
            $this->config['slow_request_threshold']
        ));

        // Opcionalmente, enviar notificación
        // NotificationService::send(...);
    }

    /**
     * Verifica si un string es JSON válido
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Carga configuración de logging
     */
    private function loadConfig() {
        return [
            'log_to_file' => env('LOG_API_TO_FILE', true),
            'log_request_body' => env('LOG_REQUEST_BODY', true),
            'log_response_body' => env('LOG_RESPONSE_BODY', false),

            'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 3000), // ms

            'ignore_paths' => [
                '/health-check',
                '/ping',
                '/favicon.ico',
                '/assets/.*',
            ],

            'ignore_ips' => [
                // '127.0.0.1',
            ],

            'sensitive_fields' => [
                'password',
                'password_confirmation',
                'token',
                'api_key',
                'api_secret',
                'secret',
                'private_key',
                'card_number',
                'cvv',
                'ssn',
            ],
        ];
    }
}
