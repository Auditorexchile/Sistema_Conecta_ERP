<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Notificaciones Automáticas
 * Orquestación de notificaciones multi-canal con reglas de negocio
 */

class NotificationService {
    private $db;
    private $emailService;
    private $notificationUtil;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->emailService = new \App\Utils\EmailService();
        $this->notificationUtil = new \App\Utils\NotificationService();
    }

    /**
     * Envía notificación según evento del sistema
     */
    public function notifyEvent($event, $data) {
        switch ($event) {
            case 'factura_emitida':
                return $this->notifyInvoiceIssued($data);

            case 'pago_recibido':
                return $this->notifyPaymentReceived($data);

            case 'stock_bajo':
                return $this->notifyLowStock($data);

            case 'factura_por_vencer':
                return $this->notifyInvoiceDue($data);

            case 'nuevo_usuario':
                return $this->notifyNewUser($data);

            case 'aprobacion_pendiente':
                return $this->notifyPendingApproval($data);

            case 'backup_completado':
                return $this->notifyBackupCompleted($data);

            case 'error_integracion':
                return $this->notifyIntegrationError($data);

            default:
                return $this->notifyGeneric($event, $data);
        }
    }

    /**
     * Notifica factura emitida
     */
    private function notifyInvoiceIssued($data) {
        // Notificar al cliente
        if (isset($data['cliente_email']) && $data['cliente_email']) {
            $this->emailService->sendInvoice(
                $data['cliente_email'],
                $data['cliente_nombre'],
                $data['numero_factura'],
                $data['pdf_path'] ?? null
            );
        }

        // Notificar internamente
        $this->notificationUtil->send([
            'tipo' => 'factura_emitida',
            'titulo' => 'Factura Emitida',
            'mensaje' => "Se emitió la factura #{$data['numero_factura']} por {$data['total']}",
            'url' => "/facturas/{$data['id_factura']}",
        ]);

        return true;
    }

    /**
     * Notifica pago recibido
     */
    private function notifyPaymentReceived($data) {
        // Email al cliente con recibo
        if (isset($data['cliente_email'])) {
            $this->emailService->send(
                $data['cliente_email'],
                'Pago Recibido - Recibo',
                $this->renderTemplate('payment_received', $data)
            );
        }

        // Notificación interna
        $this->notificationUtil->send([
            'tipo' => 'pago_recibido',
            'titulo' => 'Pago Recibido',
            'mensaje' => "Pago recibido de {$data['cliente_nombre']} por {$data['monto']}",
            'url' => "/pagos/{$data['id_pago']}",
        ]);

        return true;
    }

    /**
     * Notifica stock bajo
     */
    private function notifyLowStock($data) {
        // Obtener usuarios con rol de compras
        $usuarios = $this->getUsersByRole('compras');

        foreach ($usuarios as $usuario) {
            // Email
            $this->emailService->send(
                $usuario['email'],
                'Alerta: Stock Bajo',
                $this->renderTemplate('low_stock', [
                    'producto' => $data['producto_nombre'],
                    'codigo' => $data['producto_codigo'],
                    'stock_actual' => $data['stock_actual'],
                    'stock_minimo' => $data['stock_minimo'],
                ])
            );

            // Notificación en sistema
            $this->notificationUtil->send([
                'id_usuario' => $usuario['id'],
                'tipo' => 'stock_bajo',
                'titulo' => 'Alerta: Stock Bajo',
                'mensaje' => "El producto {$data['producto_nombre']} tiene stock bajo ({$data['stock_actual']} unidades)",
                'url' => "/productos/{$data['id_producto']}",
            ]);
        }

        return true;
    }

    /**
     * Notifica facturas por vencer
     */
    private function notifyInvoiceDue($data) {
        // Email al cliente
        if (isset($data['cliente_email'])) {
            $this->emailService->send(
                $data['cliente_email'],
                'Recordatorio: Factura por Vencer',
                $this->renderTemplate('invoice_due', $data)
            );
        }

        // Notificación interna a cobranzas
        $usuariosCobranza = $this->getUsersByRole('cobranza');

        foreach ($usuariosCobranza as $usuario) {
            $this->notificationUtil->send([
                'id_usuario' => $usuario['id'],
                'tipo' => 'factura_por_vencer',
                'titulo' => 'Factura por Vencer',
                'mensaje' => "La factura #{$data['numero_factura']} de {$data['cliente_nombre']} vence en {$data['dias']} días",
                'url' => "/facturas/{$data['id_factura']}",
            ]);
        }

        return true;
    }

    /**
     * Notifica nuevo usuario registrado
     */
    private function notifyNewUser($data) {
        // Email de bienvenida
        $this->emailService->sendWelcome(
            $data['email'],
            $data['nombre'],
            $data['empresa'],
            $data['password_temporal'] ?? null
        );

        // Notificar a administradores
        $admins = $this->getUsersByRole('admin');

        foreach ($admins as $admin) {
            $this->notificationUtil->send([
                'id_usuario' => $admin['id'],
                'tipo' => 'nuevo_usuario',
                'titulo' => 'Nuevo Usuario Registrado',
                'mensaje' => "Se registró el usuario {$data['nombre']} ({$data['email']})",
                'url' => "/usuarios/{$data['id_usuario']}",
            ]);
        }

        return true;
    }

    /**
     * Notifica aprobación pendiente
     */
    private function notifyPendingApproval($data) {
        // Obtener aprobadores
        $aprobadores = $this->getApprovers($data['id_paso']);

        foreach ($aprobadores as $aprobador) {
            // Email
            $this->emailService->send(
                $aprobador['email'],
                'Aprobación Pendiente',
                $this->renderTemplate('pending_approval', [
                    'tipo_documento' => $data['tipo_documento'],
                    'numero' => $data['numero'],
                    'solicitante' => $data['solicitante'],
                    'monto' => $data['monto'] ?? null,
                ])
            );

            // Notificación
            $this->notificationUtil->send([
                'id_usuario' => $aprobador['id'],
                'tipo' => 'aprobacion_pendiente',
                'titulo' => 'Aprobación Pendiente',
                'mensaje' => "Tiene una aprobación pendiente de {$data['tipo_documento']}",
                'url' => "/aprobaciones/{$data['id_aprobacion']}",
                'urgente' => true,
            ]);
        }

        return true;
    }

    /**
     * Notifica backup completado
     */
    private function notifyBackupCompleted($data) {
        $admins = $this->getUsersByRole('admin');

        foreach ($admins as $admin) {
            $this->notificationUtil->send([
                'id_usuario' => $admin['id'],
                'tipo' => 'backup_completado',
                'titulo' => 'Backup Completado',
                'mensaje' => "Backup completado exitosamente. Tamaño: {$data['size']}, Duración: {$data['duration']}",
            ]);
        }

        return true;
    }

    /**
     * Notifica error de integración
     */
    private function notifyIntegrationError($data) {
        $admins = $this->getUsersByRole('admin');

        foreach ($admins as $admin) {
            // Email urgente
            $this->emailService->send(
                $admin['email'],
                'Error en Integración - Urgente',
                $this->renderTemplate('integration_error', $data)
            );

            // Notificación urgente
            $this->notificationUtil->send([
                'id_usuario' => $admin['id'],
                'tipo' => 'error_integracion',
                'titulo' => 'Error en Integración',
                'mensaje' => "Error en {$data['integracion']}: {$data['error']}",
                'urgente' => true,
            ]);
        }

        return true;
    }

    /**
     * Notificación genérica
     */
    private function notifyGeneric($event, $data) {
        $this->notificationUtil->send([
            'tipo' => $event,
            'titulo' => $data['titulo'] ?? ucfirst($event),
            'mensaje' => $data['mensaje'] ?? 'Evento del sistema',
            'datos' => json_encode($data),
        ]);

        return true;
    }

    /**
     * Programa notificación para envío futuro
     */
    public function scheduleNotification($datetime, $event, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones_programadas
            (evento, datos, fecha_programada, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $event,
            json_encode($data),
            $datetime,
        ]);
    }

    /**
     * Procesa notificaciones programadas
     */
    public function processScheduledNotifications() {
        $stmt = $this->db->prepare("
            SELECT *
            FROM notificaciones_programadas
            WHERE fecha_programada <= NOW()
            AND procesada = 0
            ORDER BY fecha_programada ASC
        ");

        $stmt->execute();
        $notificaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($notificaciones as $notif) {
            try {
                $data = json_decode($notif['datos'], true);
                $this->notifyEvent($notif['evento'], $data);

                // Marcar como procesada
                $stmt = $this->db->prepare("
                    UPDATE notificaciones_programadas
                    SET procesada = 1, fecha_procesada = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$notif['id']]);

            } catch (\Exception $e) {
                error_log("Error procesando notificación programada {$notif['id']}: " . $e->getMessage());
            }
        }

        return count($notificaciones);
    }

    /**
     * Obtiene usuarios por rol
     */
    private function getUsersByRole($rol) {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id
            WHERE r.nombre = ?
            AND u.activo = 1
        ");
        $stmt->execute([$rol]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene aprobadores de un paso
     */
    private function getApprovers($idPaso) {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM usuarios u
            INNER JOIN pasos_flujo_aprobadores pfa ON u.id = pfa.id_usuario
            WHERE pfa.id_paso = ?
            AND u.activo = 1
        ");
        $stmt->execute([$idPaso]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Renderiza template de email
     */
    private function renderTemplate($template, $data) {
        $templatePath = __DIR__ . "/../views/emails/{$template}.php";

        if (!file_exists($templatePath)) {
            return json_encode($data);
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }
}
