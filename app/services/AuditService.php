<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Auditoría de Eventos
 * Registro y seguimiento de acciones del sistema
 */

class AuditService {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Registra evento de auditoría
     */
    public function log($event, $data = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs_auditoria
                (id_empresa, id_usuario, tabla, id_registro, accion, datos_anteriores,
                 datos_nuevos, ip_address, user_agent, fecha_evento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $_SESSION['id_empresa'] ?? null,
                $_SESSION['id_usuario'] ?? null,
                $data['tabla'] ?? $event,
                $data['id_registro'] ?? null,
                $event,
                isset($data['datos_anteriores']) ? json_encode($data['datos_anteriores']) : null,
                isset($data['datos_nuevos']) ? json_encode($data['datos_nuevos']) : null,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ]);

            return $this->db->lastInsertId();

        } catch (\Exception $e) {
            error_log("Error logging audit event: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra login
     */
    public function logLogin($idUsuario, $exito = true, $detalles = null) {
        $stmt = $this->db->prepare("
            INSERT INTO logs_login
            (id_usuario, accion, exito, ip_address, user_agent, detalles, fecha_accion)
            VALUES (?, 'login', ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $idUsuario,
            $exito ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $detalles,
        ]);
    }

    /**
     * Registra logout
     */
    public function logLogout($idUsuario) {
        $stmt = $this->db->prepare("
            INSERT INTO logs_login
            (id_usuario, accion, exito, ip_address, user_agent, fecha_accion)
            VALUES (?, 'logout', 1, ?, ?, NOW())
        ");

        return $stmt->execute([
            $idUsuario,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Registra cambio en un registro (CREATE, UPDATE, DELETE)
     */
    public function logChange($tabla, $idRegistro, $accion, $datosAnteriores = null, $datosNuevos = null) {
        // Registrar en logs_auditoria
        $this->log($accion, [
            'tabla' => $tabla,
            'id_registro' => $idRegistro,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
        ]);

        // Si es UPDATE, registrar en historial_cambios
        if ($accion === 'update' && $datosAnteriores && $datosNuevos) {
            $this->logFieldChanges($tabla, $idRegistro, $datosAnteriores, $datosNuevos);
        }
    }

    /**
     * Registra cambios específicos de campos
     */
    private function logFieldChanges($tabla, $idRegistro, $datosAnteriores, $datosNuevos) {
        foreach ($datosNuevos as $campo => $valorNuevo) {
            $valorAnterior = $datosAnteriores[$campo] ?? null;

            // Solo registrar si cambió
            if ($valorAnterior != $valorNuevo) {
                $stmt = $this->db->prepare("
                    INSERT INTO historial_cambios
                    (tabla, id_registro, campo, valor_anterior, valor_nuevo,
                     id_usuario, fecha_cambio)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $tabla,
                    $idRegistro,
                    $campo,
                    $valorAnterior,
                    $valorNuevo,
                    $_SESSION['id_usuario'] ?? null,
                ]);
            }
        }
    }

    /**
     * Obtiene historial de auditoría
     */
    public function getAuditLog($filtros = []) {
        $where = [];
        $params = [];

        if (isset($filtros['id_empresa'])) {
            $where[] = 'id_empresa = ?';
            $params[] = $filtros['id_empresa'];
        }

        if (isset($filtros['id_usuario'])) {
            $where[] = 'id_usuario = ?';
            $params[] = $filtros['id_usuario'];
        }

        if (isset($filtros['tabla'])) {
            $where[] = 'tabla = ?';
            $params[] = $filtros['tabla'];
        }

        if (isset($filtros['accion'])) {
            $where[] = 'accion = ?';
            $params[] = $filtros['accion'];
        }

        if (isset($filtros['fecha_desde'])) {
            $where[] = 'fecha_evento >= ?';
            $params[] = $filtros['fecha_desde'];
        }

        if (isset($filtros['fecha_hasta'])) {
            $where[] = 'fecha_evento <= ?';
            $params[] = $filtros['fecha_hasta'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT la.*, u.nombre as usuario_nombre, u.email as usuario_email
            FROM logs_auditoria la
            LEFT JOIN usuarios u ON la.id_usuario = u.id
            {$whereClause}
            ORDER BY la.fecha_evento DESC
            LIMIT " . ($filtros['limit'] ?? 100)
        );

        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene historial de cambios de un registro
     */
    public function getRecordHistory($tabla, $idRegistro) {
        $stmt = $this->db->prepare("
            SELECT hc.*, u.nombre as usuario_nombre
            FROM historial_cambios hc
            LEFT JOIN usuarios u ON hc.id_usuario = u.id
            WHERE hc.tabla = ?
            AND hc.id_registro = ?
            ORDER BY hc.fecha_cambio DESC
        ");

        $stmt->execute([$tabla, $idRegistro]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas de auditoría
     */
    public function getAuditStats($idEmpresa, $fechaDesde, $fechaHasta) {
        // Total de eventos
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM logs_auditoria
            WHERE id_empresa = ?
            AND fecha_evento BETWEEN ? AND ?
        ");
        $stmt->execute([$idEmpresa, $fechaDesde, $fechaHasta]);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Eventos por acción
        $stmt = $this->db->prepare("
            SELECT accion, COUNT(*) as cantidad
            FROM logs_auditoria
            WHERE id_empresa = ?
            AND fecha_evento BETWEEN ? AND ?
            GROUP BY accion
            ORDER BY cantidad DESC
        ");
        $stmt->execute([$idEmpresa, $fechaDesde, $fechaHasta]);
        $porAccion = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Eventos por usuario
        $stmt = $this->db->prepare("
            SELECT u.nombre, COUNT(*) as cantidad
            FROM logs_auditoria la
            INNER JOIN usuarios u ON la.id_usuario = u.id
            WHERE la.id_empresa = ?
            AND la.fecha_evento BETWEEN ? AND ?
            GROUP BY la.id_usuario
            ORDER BY cantidad DESC
            LIMIT 10
        ");
        $stmt->execute([$idEmpresa, $fechaDesde, $fechaHasta]);
        $porUsuario = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Eventos por tabla
        $stmt = $this->db->prepare("
            SELECT tabla, COUNT(*) as cantidad
            FROM logs_auditoria
            WHERE id_empresa = ?
            AND fecha_evento BETWEEN ? AND ?
            GROUP BY tabla
            ORDER BY cantidad DESC
            LIMIT 10
        ");
        $stmt->execute([$idEmpresa, $fechaDesde, $fechaHasta]);
        $porTabla = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total_eventos' => $total['total'],
            'por_accion' => $porAccion,
            'por_usuario' => $porUsuario,
            'por_tabla' => $porTabla,
        ];
    }

    /**
     * Obtiene intentos de login fallidos
     */
    public function getFailedLogins($limite = 100) {
        $stmt = $this->db->prepare("
            SELECT ll.*, u.nombre, u.email
            FROM logs_login ll
            LEFT JOIN usuarios u ON ll.id_usuario = u.id
            WHERE ll.exito = 0
            AND ll.accion = 'login'
            ORDER BY ll.fecha_accion DESC
            LIMIT ?
        ");

        $stmt->execute([$limite]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Detecta actividad sospechosa
     */
    public function detectSuspiciousActivity($idEmpresa) {
        $alertas = [];

        // Múltiples intentos de login fallidos
        $stmt = $this->db->prepare("
            SELECT ll.ip_address, COUNT(*) as intentos, MAX(ll.fecha_accion) as ultimo_intento
            FROM logs_login ll
            INNER JOIN usuarios u ON ll.id_usuario = u.id
            WHERE u.id_empresa = ?
            AND ll.exito = 0
            AND ll.fecha_accion >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY ll.ip_address
            HAVING intentos >= 5
        ");
        $stmt->execute([$idEmpresa]);
        $failedLogins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($failedLogins as $login) {
            $alertas[] = [
                'tipo' => 'multiple_failed_logins',
                'mensaje' => "Múltiples intentos de login fallidos desde IP {$login['ip_address']} ({$login['intentos']} intentos)",
                'severidad' => 'alta',
                'datos' => $login,
            ];
        }

        // Acceso desde múltiples IPs
        $stmt = $this->db->prepare("
            SELECT u.nombre, u.email, COUNT(DISTINCT ll.ip_address) as ips_distintas
            FROM logs_login ll
            INNER JOIN usuarios u ON ll.id_usuario = u.id
            WHERE u.id_empresa = ?
            AND ll.fecha_accion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND ll.exito = 1
            GROUP BY ll.id_usuario
            HAVING ips_distintas >= 3
        ");
        $stmt->execute([$idEmpresa]);
        $multipleIps = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($multipleIps as $user) {
            $alertas[] = [
                'tipo' => 'multiple_ips',
                'mensaje' => "Usuario {$user['nombre']} accedió desde {$user['ips_distintas']} IPs diferentes en 24 horas",
                'severidad' => 'media',
                'datos' => $user,
            ];
        }

        // Cambios masivos
        $stmt = $this->db->prepare("
            SELECT u.nombre, COUNT(*) as cambios
            FROM logs_auditoria la
            INNER JOIN usuarios u ON la.id_usuario = u.id
            WHERE la.id_empresa = ?
            AND la.fecha_evento >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND la.accion IN ('update', 'delete')
            GROUP BY la.id_usuario
            HAVING cambios >= 50
        ");
        $stmt->execute([$idEmpresa]);
        $massiveChanges = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($massiveChanges as $user) {
            $alertas[] = [
                'tipo' => 'massive_changes',
                'mensaje' => "Usuario {$user['nombre']} realizó {$user['cambios']} cambios en la última hora",
                'severidad' => 'alta',
                'datos' => $user,
            ];
        }

        return $alertas;
    }

    /**
     * Limpia logs antiguos
     */
    public function cleanupOldLogs($diasRetencion = 90) {
        $this->db->beginTransaction();

        try {
            // Limpiar logs_auditoria
            $stmt = $this->db->prepare("
                DELETE FROM logs_auditoria
                WHERE fecha_evento < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$diasRetencion]);
            $deletedAudit = $stmt->rowCount();

            // Limpiar logs_login
            $stmt = $this->db->prepare("
                DELETE FROM logs_login
                WHERE fecha_accion < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$diasRetencion]);
            $deletedLogin = $stmt->rowCount();

            $this->db->commit();

            return [
                'success' => true,
                'deleted_audit' => $deletedAudit,
                'deleted_login' => $deletedLogin,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
