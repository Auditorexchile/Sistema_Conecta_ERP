<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware de Autenticación 2FA
 * Valida código 2FA para usuarios con autenticación de dos factores activada
 */

class TwoFactorMiddleware {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Verifica si el usuario requiere 2FA y si ha sido validado
     */
    public function handle($request, $next) {
        // Solo aplicar si el usuario está autenticado
        if (!isset($_SESSION['id_usuario'])) {
            return $next($request);
        }

        $idUsuario = $_SESSION['id_usuario'];

        // Verificar si el usuario tiene 2FA activado
        $stmt = $this->db->prepare("
            SELECT two_factor_enabled, two_factor_verified
            FROM usuarios
            WHERE id = ?
        ");
        $stmt->execute([$idUsuario]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            // Usuario no encontrado
            $this->logout();
            return $this->redirectToLogin('Usuario no encontrado');
        }

        // Si 2FA está activado pero no verificado en esta sesión
        if ($user['two_factor_enabled'] && !isset($_SESSION['two_factor_verified'])) {
            // Permitir acceso solo a rutas de verificación 2FA
            $allowedPaths = [
                '/auth/two-factor',
                '/auth/two-factor/verify',
                '/auth/logout'
            ];

            $currentPath = $request['path'] ?? $_SERVER['REQUEST_URI'];

            if (!in_array($currentPath, $allowedPaths)) {
                return $this->redirectTo2FA();
            }
        }

        return $next($request);
    }

    /**
     * Genera código 2FA (TOTP)
     */
    public function generateCode($secret) {
        $time = floor(time() / 30); // Código cambia cada 30 segundos

        $hmac = hash_hmac('sha1', pack('J', $time), $secret, true);
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;

        $code = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica código 2FA ingresado por el usuario
     */
    public function verifyCode($userId, $code) {
        // Obtener secret del usuario
        $stmt = $this->db->prepare("
            SELECT two_factor_secret
            FROM usuarios
            WHERE id = ? AND two_factor_enabled = 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !$user['two_factor_secret']) {
            return false;
        }

        // Verificar código actual y ±1 ventana de tiempo (por desfase de reloj)
        for ($i = -1; $i <= 1; $i++) {
            $timeSlice = floor(time() / 30) + $i;
            $expectedCode = $this->generateCodeForTime($user['two_factor_secret'], $timeSlice);

            if ($code === $expectedCode) {
                // Marcar como verificado en sesión
                $_SESSION['two_factor_verified'] = true;

                // Log de verificación exitosa
                $this->logVerification($userId, true);

                return true;
            }
        }

        // Log de verificación fallida
        $this->logVerification($userId, false, $code);

        return false;
    }

    /**
     * Genera código para un tiempo específico
     */
    private function generateCodeForTime($secret, $timeSlice) {
        $hmac = hash_hmac('sha1', pack('J', $timeSlice), $secret, true);
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;

        $code = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Genera secret para nuevo usuario 2FA
     */
    public function generateSecret($length = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    /**
     * Genera URL para QR code (Google Authenticator compatible)
     */
    public function getQRCodeUrl($email, $secret, $issuer = 'Conecta ERP') {
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
        ]);

        return "otpauth://totp/{$issuer}:{$email}?{$params}";
    }

    /**
     * Activa 2FA para un usuario
     */
    public function enable2FA($userId) {
        $secret = $this->generateSecret();

        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET two_factor_enabled = 1,
                two_factor_secret = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$secret, $userId]);
    }

    /**
     * Desactiva 2FA para un usuario
     */
    public function disable2FA($userId) {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET two_factor_enabled = 0,
                two_factor_secret = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }

    /**
     * Registra intento de verificación
     */
    private function logVerification($userId, $success, $code = null) {
        $stmt = $this->db->prepare("
            INSERT INTO logs_login
            (id_usuario, accion, exito, ip_address, user_agent, detalles, fecha_accion)
            VALUES (?, '2fa_verification', ?, ?, ?, ?, NOW())
        ");

        $detalles = $success ? 'Verificación 2FA exitosa' : "Código 2FA inválido: {$code}";

        $stmt->execute([
            $userId,
            $success ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $detalles
        ]);
    }

    /**
     * Redirige a página de verificación 2FA
     */
    private function redirectTo2FA() {
        header('Location: /auth/two-factor');
        exit;
    }

    /**
     * Redirige a login
     */
    private function redirectToLogin($message = '') {
        $query = $message ? '?error=' . urlencode($message) : '';
        header('Location: /login' . $query);
        exit;
    }

    /**
     * Cierra sesión
     */
    private function logout() {
        session_destroy();
        session_start();
    }
}
