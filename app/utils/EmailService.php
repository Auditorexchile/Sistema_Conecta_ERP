<?php
/**
 * Conecta ERP - Email Service
 * Servicio centralizado para envío de emails
 */

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $config;
    private $mailer;
    private $logger;

    public function __construct()
    {
        $this->config = require APP_PATH . '/config/email.php';
        $this->initializeMailer();
    }

    /**
     * Inicializar PHPMailer
     */
    private function initializeMailer()
    {
        $this->mailer = new PHPMailer(true);

        $smtpConfig = $this->config['mailers']['smtp'];

        try {
            // Configuración SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = $smtpConfig['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $smtpConfig['username'];
            $this->mailer->Password = $smtpConfig['password'];
            $this->mailer->SMTPSecure = $smtpConfig['encryption'];
            $this->mailer->Port = $smtpConfig['port'];
            $this->mailer->Timeout = $smtpConfig['timeout'];
            $this->mailer->CharSet = 'UTF-8';

            // Configuración debug
            if (env('APP_DEBUG', false)) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            // Remitente por defecto
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );

        } catch (Exception $e) {
            $this->logError('Mailer initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar email simple
     *
     * @param string|array $to Destinatario(s)
     * @param string $subject Asunto
     * @param string $body Cuerpo del mensaje (HTML)
     * @param array $options Opciones adicionales
     * @return bool
     */
    public function send($to, $subject, $body, array $options = [])
    {
        try {
            // Reset mailer
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->clearReplyTos();

            // Agregar destinatarios
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Reply-to
            if (isset($options['reply_to'])) {
                $replyTo = $options['reply_to'];
                if (is_array($replyTo)) {
                    $this->mailer->addReplyTo($replyTo['address'], $replyTo['name'] ?? '');
                } else {
                    $this->mailer->addReplyTo($replyTo);
                }
            } else {
                $this->mailer->addReplyTo(
                    $this->config['reply_to']['address'],
                    $this->config['reply_to']['name']
                );
            }

            // CC
            if (isset($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $this->mailer->addCC($cc);
                }
            }

            // BCC
            if (isset($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Adjuntos
            if (isset($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? ''
                        );
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }

            // Contenido
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Texto alternativo
            if (isset($options['text'])) {
                $this->mailer->AltBody = $options['text'];
            } else {
                $this->mailer->AltBody = strip_tags($body);
            }

            // Headers personalizados
            if (isset($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $this->mailer->addCustomHeader($name, $value);
                }
            }

            // Enviar
            $sent = $this->mailer->send();

            // Log
            $this->logSent($to, $subject, $sent);

            return $sent;

        } catch (Exception $e) {
            $this->logError('Email send failed: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
            ]);
            return false;
        }
    }

    /**
     * Enviar usando plantilla
     *
     * @param string|array $to
     * @param string $template Nombre de la plantilla
     * @param array $data Datos para la plantilla
     * @param array $options
     * @return bool
     */
    public function sendTemplate($to, $template, array $data = [], array $options = [])
    {
        $templatePath = $this->config['templates']['path'] . $template . $this->config['templates']['extension'];

        if (!file_exists($templatePath)) {
            $this->logError("Template not found: {$template}");
            return false;
        }

        // Cargar plantilla
        extract($data);
        ob_start();
        include $templatePath;
        $body = ob_get_clean();

        // Obtener subject de la plantilla si existe
        $subject = $data['subject'] ?? $options['subject'] ?? 'Conecta ERP';

        return $this->send($to, $subject, $body, $options);
    }

    /**
     * Enviar a la cola (asíncrono)
     *
     * @param string|array $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return int ID del job en cola
     */
    public function queue($to, $subject, $body, array $options = [])
    {
        if (!$this->config['queue']['enabled']) {
            // Si no está habilitada la cola, enviar directamente
            return $this->send($to, $subject, $body, $options);
        }

        // Insertar en cola
        $db = \App\Core\Database::getInstance();

        $jobData = [
            'tipo_tarea' => 'enviar_email',
            'payload' => json_encode([
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'options' => $options,
            ]),
            'prioridad' => $options['priority'] ?? 0,
            'disponible_en' => date('Y-m-d H:i:s'),
        ];

        $stmt = $db->prepare("
            INSERT INTO colas_procesamiento
            (id_empresa, tipo_tarea, payload, prioridad, disponible_en, created_at)
            VALUES (:id_empresa, :tipo_tarea, :payload, :prioridad, :disponible_en, NOW())
        ");

        $stmt->execute([
            'id_empresa' => $_SESSION['id_empresa'] ?? 1,
            'tipo_tarea' => $jobData['tipo_tarea'],
            'payload' => $jobData['payload'],
            'prioridad' => $jobData['prioridad'],
            'disponible_en' => $jobData['disponible_en'],
        ]);

        return $db->lastInsertId();
    }

    /**
     * Enviar email de bienvenida
     *
     * @param string $email
     * @param string $nombre
     * @param array $data
     * @return bool
     */
    public function sendWelcome($email, $nombre, array $data = [])
    {
        $data = array_merge([
            'nombre' => $nombre,
            'subject' => '¡Bienvenido a Conecta ERP!',
        ], $data);

        return $this->sendTemplate($email, 'welcome', $data);
    }

    /**
     * Enviar email de recuperación de contraseña
     *
     * @param string $email
     * @param string $token
     * @param string $nombre
     * @return bool
     */
    public function sendPasswordReset($email, $token, $nombre = '')
    {
        $resetUrl = BASE_URL . '/recover.php?token=' . $token;

        $data = [
            'nombre' => $nombre,
            'reset_url' => $resetUrl,
            'token' => $token,
            'expiration' => '15 minutos',
            'subject' => 'Recuperación de Contraseña - Conecta ERP',
        ];

        return $this->sendTemplate($email, 'password_reset', $data);
    }

    /**
     * Enviar notificación de factura
     *
     * @param string $email
     * @param array $facturaData
     * @return bool
     */
    public function sendInvoice($email, array $facturaData)
    {
        $data = array_merge([
            'subject' => 'Nueva Factura - Conecta ERP',
        ], $facturaData);

        // Adjuntar PDF si existe
        $options = [];
        if (isset($facturaData['pdf_path']) && file_exists($facturaData['pdf_path'])) {
            $options['attachments'] = [
                [
                    'path' => $facturaData['pdf_path'],
                    'name' => 'Factura_' . $facturaData['numero'] . '.pdf',
                ],
            ];
        }

        return $this->sendTemplate($email, 'invoice', $data, $options);
    }

    /**
     * Verificar límites de envío
     *
     * @return bool
     */
    private function checkLimits()
    {
        $limits = $this->config['limits'];

        // Verificar límite por hora
        $hourlyCount = $this->getEmailCountLastHour();
        if ($hourlyCount >= $limits['per_hour']) {
            $this->logError('Hourly email limit exceeded');
            return false;
        }

        // Verificar límite diario
        $dailyCount = $this->getEmailCountToday();
        if ($dailyCount >= $limits['per_day']) {
            $this->logError('Daily email limit exceeded');
            return false;
        }

        return true;
    }

    /**
     * Obtener conteo de emails enviados en la última hora
     *
     * @return int
     */
    private function getEmailCountLastHour()
    {
        // Implementar query a tabla de logs
        return 0; // Placeholder
    }

    /**
     * Obtener conteo de emails enviados hoy
     *
     * @return int
     */
    private function getEmailCountToday()
    {
        // Implementar query a tabla de logs
        return 0; // Placeholder
    }

    /**
     * Log de email enviado
     *
     * @param string|array $to
     * @param string $subject
     * @param bool $success
     */
    private function logSent($to, $subject, $success)
    {
        if (!$this->config['logging']['enabled']) {
            return;
        }

        $toStr = is_array($to) ? implode(', ', array_keys($to)) : $to;

        $logMessage = sprintf(
            '[EMAIL %s] To: %s | Subject: %s',
            $success ? 'SENT' : 'FAILED',
            $toStr,
            $subject
        );

        error_log($logMessage, 3, STORAGE_PATH . '/logs/email/email.log');
    }

    /**
     * Log de error
     *
     * @param string $message
     * @param array $context
     */
    private function logError($message, array $context = [])
    {
        $logMessage = sprintf(
            '[EMAIL ERROR] %s | Context: %s',
            $message,
            json_encode($context)
        );

        error_log($logMessage, 3, STORAGE_PATH . '/logs/email/email.log');
    }

    /**
     * Validar dirección de email
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Test de conexión SMTP
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return true;
        } catch (Exception $e) {
            $this->logError('SMTP connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
