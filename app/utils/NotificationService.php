<?php
namespace App\Utils;

class NotificationService {
    public function send($userId, $title, $message, $type = 'info', $options = []) {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notificaciones (id_empresa, id_usuario_destino, tipo, titulo, mensaje, enviar_email, enviar_push, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$_SESSION['id_empresa'], $userId, $type, $title, $message, $options['email'] ?? 0, $options['push'] ?? 1]);
    }

    public function sendToAll($title, $message, $type = 'info') {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notificaciones (id_empresa, tipo, titulo, mensaje, enviar_push, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        return $stmt->execute([$_SESSION['id_empresa'], $type, $title, $message]);
    }

    public function markAsRead($notificationId, $userId) {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("UPDATE notificaciones SET leida = 1, fecha_lectura = NOW() WHERE id = ? AND id_usuario_destino = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    public function getUnread($userId, $limit = 10) {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM notificaciones WHERE id_usuario_destino = ? AND leida = 0 ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
