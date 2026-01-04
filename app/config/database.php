<?php
/**
 * Conecta ERP - Configuración de Base de Datos
 * Singleton PDO con credenciales reales
 */

class Database {
    private static $instance = null;
    private $connection;
    private $lastInsertId;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                PDO::ATTR_PERSISTENT => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            $this->logError("Error de conexión: " . $e->getMessage());
            if (APP_DEBUG) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                die("Error de conexión a la base de datos. Por favor contacte al administrador.");
            }
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Ejecutar consulta SELECT
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError("Error en query: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Ejecutar consulta SELECT y retornar un solo registro
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError("Error en queryOne: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Ejecutar INSERT, UPDATE, DELETE
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            $this->lastInsertId = $this->connection->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            $this->logError("Error en execute: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Insertar registro
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        if ($this->execute($sql, $data)) {
            return $this->lastInsertId;
        }
        return false;
    }

    /**
     * Actualizar registro
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $set);

        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        return $this->execute($sql, $params);
    }

    /**
     * Eliminación lógica (soft delete)
     */
    public function softDelete($table, $id, $idColumn = 'id') {
        $sql = "UPDATE {$table}
                SET eliminado = 1,
                    deleted_at = NOW(),
                    deleted_by = :usuario
                WHERE {$idColumn} = :id AND eliminado = 0";

        return $this->execute($sql, [
            'usuario' => $_SESSION['user_id'] ?? 0,
            'id' => $id
        ]);
    }

    /**
     * Contar registros
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
        $result = $this->queryOne($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Verificar si existe un registro
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback() {
        if ($this->connection->inTransaction()) {
            return $this->connection->rollBack();
        }
        return false;
    }

    /**
     * Obtener último ID insertado
     */
    public function getLastInsertId() {
        return $this->lastInsertId;
    }

    /**
     * Escapar string
     */
    public function escape($string) {
        return $this->connection->quote($string);
    }

    /**
     * Registrar error en log
     */
    private function logError($message) {
        $logFile = LOG_PATH . '/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";

        // Crear directorio de logs si no existe
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Prevenir unserialize
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
