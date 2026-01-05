<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Facturación Automatizada
 * Creación, emisión y gestión de facturas electrónicas
 */

class InvoiceService {
    private $db;
    private $siiIntegration;
    private $pdfGenerator;
    private $emailService;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->siiIntegration = new \App\Utils\SIIIntegration();
        $this->pdfGenerator = new \App\Utils\PDFGenerator();
        $this->emailService = new \App\Utils\EmailService();
    }

    /**
     * Crea y emite factura automáticamente
     */
    public function createInvoice($data) {
        $this->db->beginTransaction();

        try {
            // Validar datos
            $this->validateInvoiceData($data);

            // Generar número de factura
            $numeroFactura = $this->generateInvoiceNumber($data['id_empresa'], $data['tipo_documento']);

            // Crear factura
            $idFactura = $this->insertInvoice($data, $numeroFactura);

            // Insertar detalle
            $this->insertInvoiceDetails($idFactura, $data['items']);

            // Calcular totales
            $this->calculateTotals($idFactura);

            // Actualizar stock si aplica
            if ($data['tipo_documento'] === 'factura' || $data['tipo_documento'] === 'boleta') {
                $this->updateStock($idFactura, 'salida');
            }

            // Emitir DTE al SII (Chile)
            if ($data['emitir_dte'] ?? true) {
                $dteResult = $this->emitDTE($idFactura);
            }

            // Generar PDF
            $pdfPath = $this->generateInvoicePDF($idFactura);

            // Enviar email al cliente
            if ($data['enviar_email'] ?? false) {
                $this->sendInvoiceEmail($idFactura, $pdfPath);
            }

            $this->db->commit();

            return [
                'success' => true,
                'id_factura' => $idFactura,
                'numero_factura' => $numeroFactura,
                'pdf_path' => $pdfPath,
                'dte_result' => $dteResult ?? null,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Genera número de factura correlativo
     */
    private function generateInvoiceNumber($idEmpresa, $tipoDocumento) {
        // Obtener configuración de folios
        $stmt = $this->db->prepare("
            SELECT folio_actual, folio_final
            FROM configuracion_folios
            WHERE id_empresa = ? AND tipo_documento = ?
            AND activo = 1
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$idEmpresa, $tipoDocumento]);
        $config = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$config) {
            throw new \Exception("No hay folios disponibles para {$tipoDocumento}");
        }

        $folioActual = $config['folio_actual'];
        $folioFinal = $config['folio_final'];

        if ($folioActual >= $folioFinal) {
            throw new \Exception("Se agotaron los folios para {$tipoDocumento}");
        }

        // Incrementar folio
        $nuevoFolio = $folioActual + 1;

        $stmt = $this->db->prepare("
            UPDATE configuracion_folios
            SET folio_actual = ?
            WHERE id_empresa = ? AND tipo_documento = ?
        ");
        $stmt->execute([$nuevoFolio, $idEmpresa, $tipoDocumento]);

        return $nuevoFolio;
    }

    /**
     * Inserta factura en BD
     */
    private function insertInvoice($data, $numeroFactura) {
        $stmt = $this->db->prepare("
            INSERT INTO facturas_venta
            (id_empresa, id_cliente, numero_factura, tipo_documento, fecha_emision,
             fecha_vencimiento, condicion_pago, observaciones, estado, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'borrador', NOW())
        ");

        $stmt->execute([
            $data['id_empresa'],
            $data['id_cliente'],
            $numeroFactura,
            $data['tipo_documento'],
            $data['fecha_emision'] ?? date('Y-m-d'),
            $data['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days')),
            $data['condicion_pago'] ?? 'contado',
            $data['observaciones'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Inserta detalle de factura
     */
    private function insertInvoiceDetails($idFactura, $items) {
        $stmt = $this->db->prepare("
            INSERT INTO facturas_venta_detalle
            (id_factura, id_producto, cantidad, precio_unitario, descuento, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $subtotal = ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);

            $stmt->execute([
                $idFactura,
                $item['id_producto'],
                $item['cantidad'],
                $item['precio_unitario'],
                $item['descuento'] ?? 0,
                $subtotal,
            ]);
        }
    }

    /**
     * Calcula totales de factura
     */
    private function calculateTotals($idFactura) {
        // Obtener configuración de impuestos
        $stmt = $this->db->prepare("
            SELECT e.pais
            FROM facturas_venta fv
            INNER JOIN empresas e ON fv.id_empresa = e.id
            WHERE fv.id = ?
        ");
        $stmt->execute([$idFactura]);
        $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

        // IVA según país
        $tasaIVA = $this->getIVARate($factura['pais']);

        // Calcular subtotal
        $stmt = $this->db->prepare("
            SELECT SUM(subtotal) as subtotal
            FROM facturas_venta_detalle
            WHERE id_factura = ?
        ");
        $stmt->execute([$idFactura]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $subtotal = $result['subtotal'] ?? 0;

        // Calcular impuestos
        $impuestos = $subtotal * $tasaIVA;
        $total = $subtotal + $impuestos;

        // Actualizar factura
        $stmt = $this->db->prepare("
            UPDATE facturas_venta
            SET subtotal = ?, total_impuestos = ?, total = ?
            WHERE id = ?
        ");
        $stmt->execute([$subtotal, $impuestos, $total, $idFactura]);
    }

    /**
     * Obtiene tasa de IVA según país
     */
    private function getIVARate($pais) {
        $rates = [
            'Chile' => 0.19,      // 19%
            'Argentina' => 0.21,  // 21%
            'Colombia' => 0.19,   // 19%
            'México' => 0.16,     // 16%
            'Perú' => 0.18,       // 18%
        ];

        return $rates[$pais] ?? 0.19;
    }

    /**
     * Actualiza stock después de factura
     */
    private function updateStock($idFactura, $tipo = 'salida') {
        $stmt = $this->db->prepare("
            SELECT fvd.id_producto, fvd.cantidad
            FROM facturas_venta_detalle fvd
            WHERE fvd.id_factura = ?
        ");
        $stmt->execute([$idFactura]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $inventoryService = new \App\Services\InventoryService();

        foreach ($items as $item) {
            $inventoryService->registerMovement([
                'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'],
                'tipo_movimiento' => $tipo,
                'tipo_documento' => 'factura_venta',
                'id_documento' => $idFactura,
            ]);
        }
    }

    /**
     * Emite DTE al SII
     */
    private function emitDTE($idFactura) {
        try {
            // Obtener datos de factura
            $facturaData = $this->getInvoiceData($idFactura);

            // Enviar al SII
            $result = $this->siiIntegration->sendDTE($facturaData);

            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE facturas_venta
                SET estado = 'emitida',
                    dte_folio = ?,
                    dte_track_id = ?,
                    fecha_emision_dte = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $result['folio'] ?? null,
                $result['track_id'] ?? null,
                $idFactura,
            ]);

            return $result;

        } catch (\Exception $e) {
            // Marcar como error
            $stmt = $this->db->prepare("
                UPDATE facturas_venta
                SET estado = 'error_dte',
                    observaciones = CONCAT(observaciones, '\nError DTE: ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $idFactura]);

            throw $e;
        }
    }

    /**
     * Genera PDF de factura
     */
    private function generateInvoicePDF($idFactura) {
        $facturaData = $this->getInvoiceData($idFactura);
        return $this->pdfGenerator->generateInvoice($facturaData);
    }

    /**
     * Envía factura por email
     */
    private function sendInvoiceEmail($idFactura, $pdfPath) {
        // Obtener datos
        $stmt = $this->db->prepare("
            SELECT fv.*, e.razon_social, e.email
            FROM facturas_venta fv
            INNER JOIN entidades e ON fv.id_cliente = e.id
            WHERE fv.id = ?
        ");
        $stmt->execute([$idFactura]);
        $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$factura['email']) {
            return false;
        }

        return $this->emailService->sendInvoice(
            $factura['email'],
            $factura['razon_social'],
            $factura['numero_factura'],
            $pdfPath
        );
    }

    /**
     * Anula factura
     */
    public function voidInvoice($idFactura, $motivo) {
        $this->db->beginTransaction();

        try {
            // Actualizar estado
            $stmt = $this->db->prepare("
                UPDATE facturas_venta
                SET estado = 'anulada',
                    observaciones = CONCAT(observaciones, '\nMotivo anulación: ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$motivo, $idFactura]);

            // Revertir stock
            $this->updateStock($idFactura, 'entrada');

            $this->db->commit();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene datos completos de factura
     */
    private function getInvoiceData($idFactura) {
        $stmt = $this->db->prepare("
            SELECT fv.*, e.razon_social, e.rut, e.direccion, e.email,
                   emp.razon_social as empresa_nombre, emp.rut as empresa_rut
            FROM facturas_venta fv
            INNER JOIN entidades e ON fv.id_cliente = e.id
            INNER JOIN empresas emp ON fv.id_empresa = emp.id
            WHERE fv.id = ?
        ");
        $stmt->execute([$idFactura]);
        $factura = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Obtener detalle
        $stmt = $this->db->prepare("
            SELECT fvd.*, p.nombre as producto_nombre, p.codigo as producto_codigo
            FROM facturas_venta_detalle fvd
            INNER JOIN productos p ON fvd.id_producto = p.id
            WHERE fvd.id_factura = ?
        ");
        $stmt->execute([$idFactura]);
        $factura['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $factura;
    }

    /**
     * Valida datos de factura
     */
    private function validateInvoiceData($data) {
        $required = ['id_empresa', 'id_cliente', 'tipo_documento', 'items'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo requerido: {$field}");
            }
        }

        if (empty($data['items'])) {
            throw new \Exception("La factura debe tener al menos un item");
        }
    }
}
