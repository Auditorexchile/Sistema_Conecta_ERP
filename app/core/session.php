<?php
/**
 * Conecta ERP - Session Handler
 * Manejo de sesiones del sistema
 */

class SessionHandler {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear nueva sesión
     */
    public function create($userId, $userData = []) {
        // Generar token único
        $token = bin2hex(random_bytes(32));

        // Calcular fecha de expiración
        $expirationTime = time() + SESSION_LIFETIME;
        $expirationDate = date('Y-m-d H:i:s', $expirationTime);

        // Insertar sesión en base de datos
        $sessionId = $this->db->insert('sesiones_usuario', [
            'id_usuario' => $userId,
            'token_sesion' => $token,
            'ip_address' => getClientIP(),
            'user_agent' => getUserAgent(),
            'fecha_inicio' => date('Y-m-d H:i:s'),
            'fecha_expiracion' => $expirationDate,
            'fecha_ultima_actividad' => date('Y-m-d H:i:s'),
            'activa' => 1,
            'datos_sesion' => json_encode($userData)
        ]);

        if ($sessionId) {
            // Establecer variables de sesión
            $_SESSION['session_id'] = $sessionId;
            $_SESSION['session_token'] = $token;
            $_SESSION['user_id'] = $userId;
            $_SESSION['authenticated'] = true;
            $_SESSION['created_at'] = time();
            $_SESSION['expires_at'] = $expirationTime;

            // Actualizar último login del usuario
            $this->db->update('usuarios_acceso', [
                'ultimo_login' => date('Y-m-d H:i:s'),
                'intentos_login' => 0
            ], 'id = :id', ['id' => $userId]);

            return true;
        }

        return false;
    }

    /**
     * Validar sesión
     */
    public function validate() {
        if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
            return false;
        }

        // Verificar que la sesión existe y está activa
        $session = $this->db->queryOne(
            "SELECT * FROM sesiones_usuario
             WHERE token_sesion = :token
             AND id_usuario = :user_id
             AND activa = 1
             AND fecha_expiracion > NOW()",
            [
                'token' => $_SESSION['session_token'],
                'user_id' => $_SESSION['user_id']
            ]
        );

        if (!$session) {
            $this->destroy();
            return false;
        }

        // Actualizar última actividad
        $this->updateActivity();

        return true;
    }

    /**
     * Actualizar actividad de sesión
     */
    public function updateActivity() {
        if (!isset($_SESSION['session_token'])) {
            return;
        }

        $this->db->execute(
            "UPDATE sesiones_usuario
             SET fecha_ultima_actividad = NOW()
             WHERE token_sesion = :token",
            ['token' => $_SESSION['session_token']]
        );
    }

    /**
     * Destruir sesión
     */
    public function destroy($reason = 'usuario') {
        if (isset($_SESSION['session_token'])) {
            // Marcar sesión como inactiva
            $this->db->execute(
                "UPDATE sesiones_usuario
                 SET activa = 0,
                     cerrada_por = :reason
                 WHERE token_sesion = :token",
                [
                    'reason' => $reason,
                    'token' => $_SESSION['session_token']
                ]
            );

            // Registrar en auditoría
            if (isset($_SESSION['user_id'])) {
                $this->db->insert('auditoria_accesos', [
                    'id_usuario' => $_SESSION['user_id'],
                    'tipo_acceso' => 'logout',
                    'exitoso' => 1,
                    'ip_address' => getClientIP(),
                    'user_agent' => getUserAgent(),
                    'fecha_acceso' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Limpiar variables de sesión
        $_SESSION = [];

        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destruir sesión
        session_destroy();
    }

    /**
     * Regenerar ID de sesión
     */
    public function regenerate() {
        session_regenerate_id(true);
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpired() {
        $this->db->execute(
            "UPDATE sesiones_usuario
             SET activa = 0,
                 cerrada_por = 'timeout'
             WHERE activa = 1
             AND fecha_expiracion < NOW()"
        );
    }

    /**
     * Obtener sesiones activas del usuario
     */
    public function getUserActiveSessions($userId) {
        return $this->db->query(
            "SELECT * FROM sesiones_usuario
             WHERE id_usuario = :user_id
             AND activa = 1
             AND fecha_expiracion > NOW()
             ORDER BY fecha_inicio DESC",
            ['user_id' => $userId]
        );
    }

    /**
     * Cerrar todas las sesiones de un usuario
     */
    public function destroyUserSessions($userId, $exceptCurrent = false) {
        $sql = "UPDATE sesiones_usuario
                SET activa = 0,
                    cerrada_por = 'admin'
                WHERE id_usuario = :user_id
                AND activa = 1";

        $params = ['user_id' => $userId];

        if ($exceptCurrent && isset($_SESSION['session_token'])) {
            $sql .= " AND token_sesion != :current_token";
            $params['current_token'] = $_SESSION['session_token'];
        }

        return $this->db->execute($sql, $params);
    }
}

// Crear instancia global
$sessionHandler = new SessionHandler();

// Limpiar sesiones expiradas periódicamente (1% de probabilidad)
if (random_int(1, 100) === 1) {
    $sessionHandler->cleanExpired();
}
