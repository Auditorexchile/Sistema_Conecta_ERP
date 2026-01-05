<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware de Validación de API Keys
 * Valida API keys para acceso a endpoints REST
 */

class ApiKeyMiddleware {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Verifica API key en headers
     */
    public function handle($request, $next) {
        // Obtener API key de header
        $apiKey = $this->getApiKeyFromRequest();

        if (!$apiKey) {
            return $this->unauthorized('API key no proporcionada');
        }

        // Validar API key
        $keyData = $this->validateApiKey($apiKey);

        if (!$keyData) {
            return $this->unauthorized('API key inválida');
        }

        // Verificar si está activa
        if (!$keyData['activo']) {
            return $this->unauthorized('API key desactivada');
        }

        // Verificar fecha de expiración
        if ($keyData['fecha_expiracion'] && strtotime($keyData['fecha_expiracion']) < time()) {
            return $this->unauthorized('API key expirada');
        }

        // Verificar límite de requests
        if (!$this->checkRateLimit($keyData['id'])) {
            return $this->tooManyRequests('Límite de requests excedido');
        }

        // Verificar IP whitelist (si está configurada)
        if ($keyData['ip_whitelist'] && !$this->checkIpWhitelist($keyData['ip_whitelist'])) {
            return $this->forbidden('IP no autorizada');
        }

        // Verificar permisos de endpoint
        if (!$this->checkEndpointPermission($keyData['id'], $request['path'] ?? $_SERVER['REQUEST_URI'])) {
            return $this->forbidden('Acceso denegado a este endpoint');
        }

        // Agregar información de API key al request
        $request['api_key_id'] = $keyData['id'];
        $request['api_key_empresa'] = $keyData['id_empresa'];
        $request['api_key_nombre'] = $keyData['nombre'];

        // Registrar uso
        $this->logApiKeyUsage($keyData['id'], $request);

        return $next($request);
    }

    /**
     * Obtiene API key del request
     */
    private function getApiKeyFromRequest() {
        // Método 1: Header Authorization (Bearer)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Método 2: Header X-API-Key
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }

        // Método 3: Query parameter (menos seguro)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    /**
     * Valida API key en base de datos
     */
    private function validateApiKey($apiKey) {
        $stmt = $this->db->prepare("
            SELECT id, id_empresa, nombre, permisos, activo,
                   fecha_expiracion, ip_whitelist, rate_limit_per_minute
            FROM api_keys
            WHERE api_key = ? AND activo = 1
        ");
        $stmt->execute([$apiKey]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Verifica límite de requests (rate limiting)
     */
    private function checkRateLimit($apiKeyId) {
        // Obtener configuración de rate limit
        $stmt = $this->db->prepare("
            SELECT rate_limit_per_minute
            FROM api_keys
            WHERE id = ?
        ");
        $stmt->execute([$apiKeyId]);
        $key = $stmt->fetch(\PDO::FETCH_ASSOC);

        $limit = $key['rate_limit_per_minute'] ?? 60;

        // Contar requests en el último minuto
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM api_logs
            WHERE id_api_key = ?
            AND fecha_request >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ");
        $stmt->execute([$apiKeyId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] < $limit;
    }

    /**
     * Verifica IP whitelist
     */
    private function checkIpWhitelist($whitelist) {
        if (!$whitelist) {
            return true; // Sin restricción
        }

        $allowedIps = json_decode($whitelist, true);
        if (!is_array($allowedIps)) {
            return true;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return in_array($clientIp, $allowedIps);
    }

    /**
     * Verifica permisos de endpoint
     */
    private function checkEndpointPermission($apiKeyId, $path) {
        $stmt = $this->db->prepare("
            SELECT permisos
            FROM api_keys
            WHERE id = ?
        ");
        $stmt->execute([$apiKeyId]);
        $key = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$key || !$key['permisos']) {
            return true; // Sin restricción
        }

        $permisos = json_decode($key['permisos'], true);
        if (!is_array($permisos)) {
            return true;
        }

        // Verificar si el path está permitido
        foreach ($permisos as $permiso) {
            if (preg_match('#^' . $permiso . '$#', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registra uso de API key
     */
    private function logApiKeyUsage($apiKeyId, $request) {
        $stmt = $this->db->prepare("
            INSERT INTO api_logs
            (id_api_key, endpoint, metodo, ip_address, user_agent, fecha_request)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $apiKeyId,
            $request['path'] ?? $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Genera nueva API key
     */
    public function generateApiKey($idEmpresa, $nombre, $permisos = null, $options = []) {
        // Generar key aleatoria
        $apiKey = bin2hex(random_bytes(32)); // 64 caracteres

        $stmt = $this->db->prepare("
            INSERT INTO api_keys
            (id_empresa, nombre, api_key, permisos, rate_limit_per_minute,
             fecha_expiracion, ip_whitelist, activo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $idEmpresa,
            $nombre,
            $apiKey,
            json_encode($permisos),
            $options['rate_limit'] ?? 60,
            $options['fecha_expiracion'] ?? null,
            json_encode($options['ip_whitelist'] ?? [])
        ]);

        return $apiKey;
    }

    /**
     * Revoca API key
     */
    public function revokeApiKey($apiKeyId) {
        $stmt = $this->db->prepare("
            UPDATE api_keys
            SET activo = 0, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$apiKeyId]);
    }

    /**
     * Respuesta 401 Unauthorized
     */
    private function unauthorized($message = 'No autorizado') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 401
        ]);
        exit;
    }

    /**
     * Respuesta 403 Forbidden
     */
    private function forbidden($message = 'Acceso denegado') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 403
        ]);
        exit;
    }

    /**
     * Respuesta 429 Too Many Requests
     */
    private function tooManyRequests($message = 'Demasiadas solicitudes') {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: 60');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 429
        ]);
        exit;
    }
}
