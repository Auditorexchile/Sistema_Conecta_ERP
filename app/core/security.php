<?php
/**
 * Conecta ERP - Security
 * Funciones de seguridad del sistema
 */

class Security {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Hash de contraseña
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Verificar fortaleza de contraseña según políticas
     */
    public function validatePasswordStrength($password) {
        $errors = [];

        $minLength = $this->getSecurityConfig('password_min_length', 12);
        if (strlen($password) < $minLength) {
            $errors[] = "La contraseña debe tener al menos {$minLength} caracteres";
        }

        if ($this->getSecurityConfig('password_require_uppercase', true)) {
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "La contraseña debe contener al menos una mayúscula";
            }
        }

        if ($this->getSecurityConfig('password_require_lowercase', true)) {
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = "La contraseña debe contener al menos una minúscula";
            }
        }

        if ($this->getSecurityConfig('password_require_number', true)) {
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un número";
            }
        }

        if ($this->getSecurityConfig('password_require_special', true)) {
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un caracter especial";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Registrar intento de login
     */
    public function logLoginAttempt($email, $userId, $success, $reason = null) {
        $this->db->insert('intentos_login', [
            'email' => $email,
            'id_usuario' => $userId,
            'ip_address' => getClientIP(),
            'user_agent' => getUserAgent(),
            'exitoso' => $success ? 1 : 0,
            'razon_fallo' => $reason,
            'fecha_intento' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verificar si usuario está bloqueado
     */
    public function isUserLocked($email) {
        $user = $this->db->queryOne(
            "SELECT bloqueado, bloqueado_hasta FROM usuarios_acceso WHERE email = :email",
            ['email' => $email]
        );

        if (!$user) {
            return false;
        }

        if ($user['bloqueado']) {
            if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
                return true;
            } else {
                // Desbloquear automáticamente
                $this->unlockUser($email);
                return false;
            }
        }

        return false;
    }

    /**
     * Verificar intentos de login fallidos
     */
    public function checkLoginAttempts($email) {
        $maxAttempts = $this->getSecurityConfig('max_login_attempts', MAX_LOGIN_ATTEMPTS);
        $lockoutTime = $this->getSecurityConfig('lockout_time', LOCKOUT_TIME);

        $attempts = $this->db->queryOne(
            "SELECT COUNT(*) as count
             FROM intentos_login
             WHERE email = :email
             AND exitoso = 0
             AND fecha_intento > DATE_SUB(NOW(), INTERVAL :lockout SECOND)",
            [
                'email' => $email,
                'lockout' => $lockoutTime
            ]
        );

        if ($attempts && $attempts['count'] >= $maxAttempts) {
            $this->lockUser($email, $lockoutTime);
            return false;
        }

        return true;
    }

    /**
     * Bloquear usuario
     */
    public function lockUser($email, $lockoutTime = null) {
        if ($lockoutTime === null) {
            $lockoutTime = $this->getSecurityConfig('lockout_time', LOCKOUT_TIME);
        }

        $bloqueadoHasta = date('Y-m-d H:i:s', time() + $lockoutTime);

        $this->db->execute(
            "UPDATE usuarios_acceso
             SET bloqueado = 1,
                 bloqueado_hasta = :hasta,
                 intentos_login = intentos_login + 1,
                 ultimo_intento_login = NOW()
             WHERE email = :email",
            [
                'hasta' => $bloqueadoHasta,
                'email' => $email
            ]
        );
    }

    /**
     * Desbloquear usuario
     */
    public function unlockUser($email) {
        $this->db->execute(
            "UPDATE usuarios_acceso
             SET bloqueado = 0,
                 bloqueado_hasta = NULL,
                 intentos_login = 0
             WHERE email = :email",
            ['email' => $email]
        );
    }

    /**
     * Generar token de recuperación
     */
    public function generateRecoveryToken($email) {
        $token = bin2hex(random_bytes(32));
        $expiryTime = $this->getSecurityConfig('token_recovery_expiry', 900); // 15 min
        $expirationDate = date('Y-m-d H:i:s', time() + $expiryTime);

        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => $token,
            'usado' => 0,
            'ip_solicitud' => getClientIP(),
            'fecha_solicitud' => date('Y-m-d H:i:s'),
            'fecha_expiracion' => $expirationDate
        ]);

        return $token;
    }

    /**
     * Validar token de recuperación
     */
    public function validateRecoveryToken($token) {
        $reset = $this->db->queryOne(
            "SELECT * FROM password_resets
             WHERE token = :token
             AND usado = 0
             AND fecha_expiracion > NOW()",
            ['token' => $token]
        );

        return $reset !== false;
    }

    /**
     * Marcar token como usado
     */
    public function markTokenAsUsed($token) {
        $this->db->execute(
            "UPDATE password_resets
             SET usado = 1,
                 fecha_uso = NOW(),
                 ip_uso = :ip
             WHERE token = :token",
            [
                'ip' => getClientIP(),
                'token' => $token
            ]
        );
    }

    /**
     * Obtener configuración de seguridad
     */
    private function getSecurityConfig($key, $default = null) {
        static $cache = [];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $config = $this->db->queryOne(
            "SELECT valor, tipo FROM configuracion_seguridad WHERE clave = :key",
            ['key' => $key]
        );

        if (!$config) {
            $cache[$key] = $default;
            return $default;
        }

        $value = $config['valor'];

        // Convertir según tipo
        if ($config['tipo'] === 'number') {
            $value = (int)$value;
        } elseif ($config['tipo'] === 'boolean') {
            $value = (bool)$value;
        } elseif ($config['tipo'] === 'json') {
            $value = json_decode($value, true);
        }

        $cache[$key] = $value;
        return $value;
    }

    /**
     * Protección CSRF
     */
    public function validateCSRF($token) {
        if (!CSRF_ENABLED) {
            return true;
        }

        return verifyCSRFToken($token);
    }

    /**
     * Rate Limiting
     */
    public function checkRateLimit($identifier, $maxRequests = 60, $timeWindow = 60) {
        // TODO: Implementar rate limiting completo con Redis/Memcached
        // Por ahora retornamos true
        return true;
    }
}

// Crear instancia global
$security = new Security();
