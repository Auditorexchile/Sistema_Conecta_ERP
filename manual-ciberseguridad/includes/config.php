<?php
/**
 * Configuración de Base de Datos
 * Manual de Ciberseguridad - AuditorEx Chile SpA
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'conectae_estudiosuser');
define('DB_PASS', 'pt125824caraud');
define('DB_NAME', 'conectae_estudiosbd');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('SITE_NAME', 'Manual de Ciberseguridad - AuditorEx Chile SpA');
define('SITE_URL', 'https://auditorexchile.cl');
define('ADMIN_EMAIL', 'gerencia@auditorexchile.cl');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
session_start();

// Conexión a la base de datos
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['usuario']);
}

// Función para requerir login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Función para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Función para formatear fecha
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>
