<?php
/**
 * Conecta ERP - Banking Integration
 * Integración con APIs bancarias
 */

namespace App\Utils;

class BankingIntegration
{
    private $config;
    private $bank;

    public function __construct($bankCode = null)
    {
        $config = require APP_PATH . '/config/integraciones.php';
        $this->config = $config['banks'];
        $this->bank = $bankCode;
    }

    /**
     * Obtener saldo de cuenta
     */
    public function getAccountBalance($accountNumber)
    {
        $endpoint = '/accounts/' . $accountNumber . '/balance';
        $response = $this->makeRequest('GET', $endpoint);
        return $response['balance'] ?? 0;
    }

    /**
     * Obtener movimientos bancarios
     */
    public function getTransactions($accountNumber, $dateFrom, $dateTo)
    {
        $endpoint = '/accounts/' . $accountNumber . '/transactions';
        $params = ['from' => $dateFrom, 'to' => $dateTo];
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Realizar transferencia
     */
    public function makeTransfer($from Account, $toAccount, $amount, $description)
    {
        $endpoint = '/transfers';
        $data = [
            'from_account' => $fromAccount,
            'to_account' => $toAccount,
            'amount' => $amount,
            'description' => $description,
        ];
        return $this->makeRequest('POST', $endpoint, $data);
    }

    /**
     * Realizar request a API bancaria
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $bankConfig = $this->config[$this->bank] ?? [];
        if (!$bankConfig['enabled']) {
            throw new \Exception("Bank API not enabled: {$this->bank}");
        }

        $url = $bankConfig['api_url'] . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $bankConfig['timeout']);

        // Headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->getAccessToken(),
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Method y data
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Bank API Error: {$error}");
        }

        return json_decode($response, true);
    }

    /**
     * Obtener access token
     */
    private function getAccessToken()
    {
        // Implementar OAuth o API key según banco
        $bankConfig = $this->config[$this->bank];
        return $bankConfig['api_key'];
    }

    /**
     * Sincronizar movimientos bancarios
     */
    public function syncTransactions($accountId, $days = 7)
    {
        $dateTo = date('Y-m-d');
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));

        $transactions = $this->getTransactions($accountId, $dateFrom, $dateTo);

        // Guardar en BD
        foreach ($transactions as $transaction) {
            $this->saveTransaction($accountId, $transaction);
        }

        return count($transactions);
    }

    /**
     * Guardar transacción en BD
     */
    private function saveTransaction($accountId, $transaction)
    {
        $db = \App\Core\Database::getInstance();

        $stmt = $db->prepare("
            INSERT INTO movimientos_bancarios
            (id_cuenta_bancaria, fecha_movimiento, tipo_movimiento, tipo_documento, monto, signo, glosa, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ");

        $stmt->execute([
            $accountId,
            $transaction['date'],
            $transaction['type'],
            $transaction['document_type'] ?? 'otro',
            $transaction['amount'],
            $transaction['amount'] > 0 ? 1 : -1,
            $transaction['description'] ?? '',
        ]);
    }
}
