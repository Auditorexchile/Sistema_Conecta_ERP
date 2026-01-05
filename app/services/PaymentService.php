<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Procesamiento de Pagos
 * Integración con pasarelas de pago y gestión de transacciones
 */

class PaymentService {
    private $db;
    private $config;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->config = require __DIR__ . '/../config/integraciones.php';
    }

    /**
     * Procesa pago con pasarela
     */
    public function processPayment($data) {
        $this->db->beginTransaction();

        try {
            // Validar datos
            $this->validatePaymentData($data);

            // Crear registro de pago
            $idPago = $this->createPaymentRecord($data);

            // Procesar según pasarela
            $gateway = $data['gateway'] ?? 'transbank';
            $result = $this->processWithGateway($gateway, $data, $idPago);

            if ($result['success']) {
                // Actualizar pago como exitoso
                $this->updatePaymentStatus($idPago, 'approved', $result);

                // Aplicar pago a factura/orden
                if (isset($data['id_factura'])) {
                    $this->applyPaymentToInvoice($idPago, $data['id_factura']);
                }

                $this->db->commit();

                return [
                    'success' => true,
                    'id_pago' => $idPago,
                    'transaction_id' => $result['transaction_id'],
                ];
            } else {
                // Marcar como rechazado
                $this->updatePaymentStatus($idPago, 'rejected', $result);

                $this->db->commit();

                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Pago rechazado',
                ];
            }

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Procesa pago con Transbank (Chile)
     */
    private function processTransbank($data, $idPago) {
        $config = $this->config['payment_gateways']['transbank'];

        // URL según ambiente
        $baseUrl = $config['environment'] === 'production'
            ? 'https://webpay3g.transbank.cl'
            : 'https://webpay3gint.transbank.cl';

        // Iniciar transacción
        $response = $this->httpPost($baseUrl . '/rswebpaytransaction/api/webpay/v1.2/transactions', [
            'buy_order' => 'ORDEN-' . $idPago,
            'session_id' => session_id(),
            'amount' => $data['amount'],
            'return_url' => $config['return_url'],
        ], [
            'Tbk-Api-Key-Id: ' . $config['commerce_code'],
            'Tbk-Api-Key-Secret: ' . $config['api_key'],
            'Content-Type: application/json',
        ]);

        if ($response['success']) {
            return [
                'success' => true,
                'transaction_id' => $response['token'],
                'redirect_url' => $response['url'] . '?token_ws=' . $response['token'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['error'] ?? 'Error en Transbank',
        ];
    }

    /**
     * Procesa pago con MercadoPago
     */
    private function processMercadoPago($data, $idPago) {
        $config = $this->config['payment_gateways']['mercadopago'];

        $response = $this->httpPost('https://api.mercadopago.com/v1/payments', [
            'transaction_amount' => $data['amount'],
            'description' => $data['description'] ?? 'Pago ERP',
            'payment_method_id' => $data['payment_method'] ?? 'credit_card',
            'payer' => [
                'email' => $data['payer_email'],
            ],
        ], [
            'Authorization: Bearer ' . $config['access_token'],
            'Content-Type: application/json',
        ]);

        if ($response['status'] === 'approved') {
            return [
                'success' => true,
                'transaction_id' => $response['id'],
                'status' => $response['status'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['status_detail'] ?? 'Pago rechazado',
        ];
    }

    /**
     * Procesa pago con Stripe
     */
    private function processStripe($data, $idPago) {
        $config = $this->config['payment_gateways']['stripe'];

        // Crear Payment Intent
        $response = $this->httpPost('https://api.stripe.com/v1/payment_intents',
            http_build_query([
                'amount' => $data['amount'] * 100, // Stripe usa centavos
                'currency' => $data['currency'] ?? 'clp',
                'description' => $data['description'] ?? 'Pago ERP',
                'payment_method' => $data['payment_method_id'],
                'confirm' => true,
            ]), [
                'Authorization: Bearer ' . $config['secret_key'],
                'Content-Type: application/x-www-form-urlencoded',
            ]
        );

        if ($response['status'] === 'succeeded') {
            return [
                'success' => true,
                'transaction_id' => $response['id'],
                'status' => $response['status'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['last_payment_error']['message'] ?? 'Pago rechazado',
        ];
    }

    /**
     * Procesa pago con PayPal
     */
    private function processPayPal($data, $idPago) {
        $config = $this->config['payment_gateways']['paypal'];

        // Obtener token de acceso
        $tokenResponse = $this->httpPost(
            $config['base_url'] . '/v1/oauth2/token',
            'grant_type=client_credentials',
            [
                'Authorization: Basic ' . base64_encode($config['client_id'] . ':' . $config['secret']),
                'Content-Type: application/x-www-form-urlencoded',
            ]
        );

        $accessToken = $tokenResponse['access_token'];

        // Crear orden
        $orderResponse = $this->httpPost(
            $config['base_url'] . '/v2/checkout/orders',
            json_encode([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $data['currency'] ?? 'USD',
                            'value' => $data['amount'],
                        ],
                    ],
                ],
            ]),
            [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ]
        );

        if ($orderResponse['status'] === 'CREATED') {
            return [
                'success' => true,
                'transaction_id' => $orderResponse['id'],
                'redirect_url' => $orderResponse['links'][1]['href'] ?? null,
            ];
        }

        return [
            'success' => false,
            'error' => 'Error al crear orden PayPal',
        ];
    }

    /**
     * Enruta a la pasarela correcta
     */
    private function processWithGateway($gateway, $data, $idPago) {
        switch ($gateway) {
            case 'transbank':
                return $this->processTransbank($data, $idPago);

            case 'mercadopago':
                return $this->processMercadoPago($data, $idPago);

            case 'stripe':
                return $this->processStripe($data, $idPago);

            case 'paypal':
                return $this->processPayPal($data, $idPago);

            default:
                throw new \Exception("Pasarela no soportada: {$gateway}");
        }
    }

    /**
     * Crea registro de pago
     */
    private function createPaymentRecord($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pagos
            (id_empresa, id_cliente, monto, moneda, metodo_pago, gateway,
             estado, descripcion, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");

        $stmt->execute([
            $data['id_empresa'],
            $data['id_cliente'] ?? null,
            $data['amount'],
            $data['currency'] ?? 'CLP',
            $data['payment_method'] ?? 'credit_card',
            $data['gateway'] ?? 'transbank',
            $data['description'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Actualiza estado de pago
     */
    private function updatePaymentStatus($idPago, $status, $data) {
        $stmt = $this->db->prepare("
            UPDATE pagos
            SET estado = ?,
                transaction_id = ?,
                gateway_response = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $data['transaction_id'] ?? null,
            json_encode($data),
            $idPago,
        ]);
    }

    /**
     * Aplica pago a factura
     */
    private function applyPaymentToInvoice($idPago, $idFactura) {
        // Crear registro de cobranza
        $stmt = $this->db->prepare("
            INSERT INTO cobranzas
            (id_factura, id_pago, monto, fecha_cobranza)
            VALUES (?, ?, (SELECT monto FROM pagos WHERE id = ?), NOW())
        ");
        $stmt->execute([$idFactura, $idPago, $idPago]);

        // Verificar si la factura está completamente pagada
        $stmt = $this->db->prepare("
            SELECT
                fv.total,
                COALESCE(SUM(c.monto), 0) as pagado
            FROM facturas_venta fv
            LEFT JOIN cobranzas c ON c.id_factura = fv.id
            WHERE fv.id = ?
            GROUP BY fv.id
        ");
        $stmt->execute([$idFactura]);
        $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($factura['pagado'] >= $factura['total']) {
            // Marcar factura como pagada
            $stmt = $this->db->prepare("
                UPDATE facturas_venta
                SET estado = 'pagada'
                WHERE id = ?
            ");
            $stmt->execute([$idFactura]);
        }
    }

    /**
     * Procesa webhook de pasarela
     */
    public function processWebhook($gateway, $payload) {
        switch ($gateway) {
            case 'transbank':
                return $this->processTransbankWebhook($payload);

            case 'mercadopago':
                return $this->processMercadoPagoWebhook($payload);

            case 'stripe':
                return $this->processStripeWebhook($payload);

            default:
                throw new \Exception("Webhook no soportado: {$gateway}");
        }
    }

    /**
     * HTTP POST helper
     */
    private function httpPost($url, $data, $headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? ['error' => 'Invalid response'];
    }

    /**
     * Valida datos de pago
     */
    private function validatePaymentData($data) {
        $required = ['id_empresa', 'amount', 'gateway'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo requerido: {$field}");
            }
        }

        if ($data['amount'] <= 0) {
            throw new \Exception("Monto inválido");
        }
    }
}
