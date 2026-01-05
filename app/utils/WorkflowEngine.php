<?php
namespace App\Utils;

class WorkflowEngine {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    public function startWorkflow($idFlujo, $tipoDocumento, $idDocumento, $idUsuarioSolicita) {
        // Obtener configuración del flujo
        $flujo = $this->getWorkflow($idFlujo);
        if (!$flujo) {
            throw new \Exception("Workflow not found");
        }

        // Obtener pasos del flujo
        $pasos = $this->getWorkflowSteps($idFlujo);
        if (empty($pasos)) {
            throw new \Exception("Workflow has no steps");
        }

        // Crear instancia de aprobación
        $stmt = $this->db->prepare("
            INSERT INTO aprobaciones_pendientes
            (id_empresa, id_flujo, id_paso_actual, tipo_documento, id_documento, id_usuario_solicita, estado, paso_actual, total_pasos, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente', 1, ?, NOW())
        ");

        $stmt->execute([
            $_SESSION['id_empresa'],
            $idFlujo,
            $pasos[0]['id'],
            $tipoDocumento,
            $idDocumento,
            $idUsuarioSolicita,
            count($pasos)
        ]);

        $idAprobacion = $this->db->lastInsertId();

        // Notificar al primer aprobador
        $this->notifyApprover($idAprobacion, $pasos[0]);

        return $idAprobacion;
    }

    public function approve($idAprobacion, $idUsuario, $comentario = null) {
        $aprobacion = $this->getApproval($idAprobacion);

        if (!$aprobacion) {
            throw new \Exception("Approval not found");
        }

        // Registrar aprobación en historial
        $this->logAction($idAprobacion, $aprobacion['id_paso_actual'], $idUsuario, 'aprobar', $comentario);

        // Avanzar al siguiente paso
        $siguientePaso = $this->getNextStep($aprobacion['id_flujo'], $aprobacion['paso_actual']);

        if ($siguientePaso) {
            // Aún hay pasos pendientes
            $stmt = $this->db->prepare("
                UPDATE aprobaciones_pendientes
                SET id_paso_actual = ?, paso_actual = paso_actual + 1, estado = 'en_revision', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$siguientePaso['id'], $idAprobacion]);

            $this->notifyApprover($idAprobacion, $siguientePaso);
        } else {
            // Última aprobación - completar workflow
            $stmt = $this->db->prepare("
                UPDATE aprobaciones_pendientes
                SET estado = 'aprobado', fecha_resolucion = NOW(), resuelto_por = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$idUsuario, $idAprobacion]);

            // Ejecutar acciones post-aprobación
            $this->executePostApprovalActions($aprobacion);
        }

        return true;
    }

    public function reject($idAprobacion, $idUsuario, $comentario) {
        $aprobacion = $this->getApproval($idAprobacion);

        if (!$aprobacion) {
            throw new \Exception("Approval not found");
        }

        // Registrar rechazo
        $this->logAction($idAprobacion, $aprobacion['id_paso_actual'], $idUsuario, 'rechazar', $comentario);

        // Marcar como rechazado
        $stmt = $this->db->prepare("
            UPDATE aprobaciones_pendientes
            SET estado = 'rechazado', fecha_resolucion = NOW(), resuelto_por = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$idUsuario, $idAprobacion]);

        // Notificar al solicitante
        $this->notifyRejection($aprobacion, $comentario);

        return true;
    }

    private function getWorkflow($idFlujo) {
        $stmt = $this->db->prepare("SELECT * FROM flujos_trabajo WHERE id = ? AND activo = 1");
        $stmt->execute([$idFlujo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getWorkflowSteps($idFlujo) {
        $stmt = $this->db->prepare("SELECT * FROM pasos_flujo WHERE id_flujo = ? ORDER BY orden ASC");
        $stmt->execute([$idFlujo]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getApproval($idAprobacion) {
        $stmt = $this->db->prepare("SELECT * FROM aprobaciones_pendientes WHERE id = ?");
        $stmt->execute([$idAprobacion]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getNextStep($idFlujo, $pasoActual) {
        $stmt = $this->db->prepare("SELECT * FROM pasos_flujo WHERE id_flujo = ? AND orden > ? ORDER BY orden ASC LIMIT 1");
        $stmt->execute([$idFlujo, $pasoActual]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function logAction($idAprobacion, $idPaso, $idUsuario, $accion, $comentario) {
        $stmt = $this->db->prepare("
            INSERT INTO aprobaciones_historial
            (id_aprobacion, id_paso, id_usuario, accion, comentario, fecha_accion)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$idAprobacion, $idPaso, $idUsuario, $accion, $comentario]);
    }

    private function notifyApprover($idAprobacion, $paso) {
        // Implementar notificación al aprobador
    }

    private function notifyRejection($aprobacion, $comentario) {
        // Implementar notificación de rechazo
    }

    private function executePostApprovalActions($aprobacion) {
        // Ejecutar acciones configuradas post-aprobación
    }
}
