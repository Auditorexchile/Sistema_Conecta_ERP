<?php
/**
 * Conecta ERP - API REST v1 - Webhooks
 * Recepción y procesamiento de webhooks de integraciones externas
 */

require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/services/PaymentService.php';
require_once __DIR__ . '/../../app/services/NotificationService.php';

header('Content-Type: application/json; charset=utf-8');

// Obtener payload
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Determinar origen del webhook
$source = $_GET['source'] ?? null;

if (!$source) {
    sendResponse(400, ['error' => 'Source no especificado']);
}

// Log del webhook
logWebhook($source, $payload, $headers);

try {
    switch ($source) {
        case 'transbank':
            handleTransbankWebhook($payload, $headers);
            break;

        case 'mercadopago':
            handleMercadoPagoWebhook($payload, $headers);
            break;

        case 'stripe':
            handleStripeWebhook($payload, $headers);
            break;

        case 'paypal':
            handlePayPalWebhook($payload, $headers);
            break;

        case 'sii':
            handleSIIWebhook($payload, $headers);
            break;

        case 'bancos':
            handleBankWebhook($payload, $headers);
            break;

        default:
            sendResponse(400, ['error' => 'Source no soportado']);
    }

} catch (Exception $e) {
    logWebhookError($source, $e->getMessage());
    sendResponse(500, ['error' => $e->getMessage()]);
}

function handleTransbankWebhook($payload, $headers) {
    // Verificar firma
    if (!verifyTransbankSignature($payload, $headers)) {
        sendResponse(401, ['error' => 'Firma inválida']);
    }

    $data = json_decode($payload, true);

    $paymentService = new \App\Services\PaymentService();

    // Procesar según tipo de evento
    if ($data['status'] === 'AUTHORIZED') {
        // Pago autorizado
        $paymentService->processWebhook('transbank', $data);

        sendResponse(200, ['message' => 'Webhook procesado']);
    }

    sendResponse(200, ['message' => 'Evento ignorado']);
}

function handleMercadoPagoWebhook($payload, $headers) {
    $data = json_decode($payload, true);

    // MercadoPago envía notificaciones de diferentes tipos
    if ($data['type'] === 'payment') {
        $paymentService = new \App\Services\PaymentService();
        $paymentService->processWebhook('mercadopago', $data);
    }

    sendResponse(200, ['message' => 'Webhook procesado']);
}

function handleStripeWebhook($payload, $headers) {
    // Verificar firma de Stripe
    if (!verifyStripeSignature($payload, $headers)) {
        sendResponse(401, ['error' => 'Firma inválida']);
    }

    $event = json_decode($payload, true);

    switch ($event['type']) {
        case 'payment_intent.succeeded':
            $paymentService = new \App\Services\PaymentService();
            $paymentService->processWebhook('stripe', $event['data']['object']);
            break;

        case 'payment_intent.payment_failed':
            // Notificar fallo de pago
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyEvent('pago_fallido', $event['data']['object']);
            break;
    }

    sendResponse(200, ['message' => 'Webhook procesado']);
}

function handlePayPalWebhook($payload, $headers) {
    // Verificar webhook de PayPal
    if (!verifyPayPalWebhook($payload, $headers)) {
        sendResponse(401, ['error' => 'Verificación fallida']);
    }

    $data = json_decode($payload, true);

    if ($data['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
        $paymentService = new \App\Services\PaymentService();
        $paymentService->processWebhook('paypal', $data);
    }

    sendResponse(200, ['message' => 'Webhook procesado']);
}

function handleSIIWebhook($payload, $headers) {
    // Notificaciones del SII sobre DTEs
    $data = json_decode($payload, true);

    $db = \App\Core\Database::getInstance();

    // Actualizar estado de DTE
    if (isset($data['folio']) && isset($data['estado'])) {
        $stmt = $db->prepare("
            UPDATE facturas_venta
            SET dte_estado = ?
            WHERE dte_folio = ?
        ");
        $stmt->execute([$data['estado'], $data['folio']]);
    }

    sendResponse(200, ['message' => 'Webhook procesado']);
}

function handleBankWebhook($payload, $headers) {
    // Notificaciones de bancos sobre transacciones
    $data = json_decode($payload, true);

    $db = \App\Core\Database::getInstance();

    // Registrar transacción bancaria
    $stmt = $db->prepare("
        INSERT INTO movimientos_bancarios
        (id_cuenta_bancaria, fecha, tipo, monto, descripcion, referencia, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $data['account_id'],
        $data['date'],
        $data['type'],
        $data['amount'],
        $data['description'],
        $data['reference'],
    ]);

    sendResponse(200, ['message' => 'Webhook procesado']);
}

function verifyTransbankSignature($payload, $headers) {
    // Implementar verificación de firma de Transbank
    return true; // Placeholder
}

function verifyStripeSignature($payload, $headers) {
    $config = require __DIR__ . '/../../app/config/integraciones.php';
    $webhookSecret = $config['payment_gateways']['stripe']['webhook_secret'];

    if (!isset($headers['Stripe-Signature'])) {
        return false;
    }

    $signature = $headers['Stripe-Signature'];

    // Verificar firma
    $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

    return hash_equals($expectedSignature, $signature);
}

function verifyPayPalWebhook($payload, $headers) {
    // Implementar verificación de webhook de PayPal
    return true; // Placeholder
}

function logWebhook($source, $payload, $headers) {
    $db = \App\Core\Database::getInstance();

    $stmt = $db->prepare("
        INSERT INTO webhooks_log
        (source, payload, headers, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $source,
        $payload,
        json_encode($headers),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
}

function logWebhookError($source, $error) {
    $db = \App\Core\Database::getInstance();

    $stmt = $db->prepare("
        INSERT INTO webhooks_log
        (source, payload, error, created_at)
        VALUES (?, '', ?, NOW())
    ");

    $stmt->execute([$source, $error]);
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
