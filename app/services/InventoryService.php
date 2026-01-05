<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Control de Inventario Automático
 * Gestión automática de stock, alertas y movimientos
 */

class InventoryService {
    private $db;
    private $notificationService;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->notificationService = new \App\Utils\NotificationService();
    }

    /**
     * Registra movimiento de inventario
     */
    public function registerMovement($data) {
        $this->db->beginTransaction();

        try {
            // Validar datos
            $this->validateMovementData($data);

            // Crear movimiento
            $idMovimiento = $this->createMovement($data);

            // Actualizar stock
            $this->updateStock($data);

            // Verificar alertas de stock
            $this->checkStockAlerts($data['id_producto']);

            $this->db->commit();

            return [
                'success' => true,
                'id_movimiento' => $idMovimiento,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Crea registro de movimiento
     */
    private function createMovement($data) {
        $stmt = $this->db->prepare("
            INSERT INTO movimientos_inventario
            (id_producto, id_bodega, cantidad, tipo_movimiento, tipo_documento,
             id_documento, costo_unitario, observaciones, fecha_movimiento, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $data['id_producto'],
            $data['id_bodega'] ?? $this->getDefaultWarehouse($data['id_producto']),
            $data['cantidad'],
            $data['tipo_movimiento'], // entrada, salida, ajuste, transferencia
            $data['tipo_documento'] ?? null,
            $data['id_documento'] ?? null,
            $data['costo_unitario'] ?? null,
            $data['observaciones'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Actualiza stock del producto
     */
    private function updateStock($data) {
        $idBodega = $data['id_bodega'] ?? $this->getDefaultWarehouse($data['id_producto']);

        // Verificar si existe registro de stock
        $stmt = $this->db->prepare("
            SELECT id, cantidad
            FROM stock
            WHERE id_producto = ? AND id_bodega = ?
        ");
        $stmt->execute([$data['id_producto'], $idBodega]);
        $stock = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Calcular nueva cantidad
        $cantidadActual = $stock['cantidad'] ?? 0;
        $nuevaCantidad = $this->calculateNewStock($cantidadActual, $data['cantidad'], $data['tipo_movimiento']);

        // Validar que no quede en negativo
        if ($nuevaCantidad < 0) {
            throw new \Exception("Stock insuficiente. Disponible: {$cantidadActual}, Requerido: {$data['cantidad']}");
        }

        if ($stock) {
            // Actualizar stock existente
            $stmt = $this->db->prepare("
                UPDATE stock
                SET cantidad = ?,
                    fecha_ultimo_movimiento = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$nuevaCantidad, $stock['id']]);
        } else {
            // Crear nuevo registro de stock
            $stmt = $this->db->prepare("
                INSERT INTO stock
                (id_producto, id_bodega, cantidad, fecha_ultimo_movimiento, created_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$data['id_producto'], $idBodega, $nuevaCantidad]);
        }

        // Actualizar costo promedio si es entrada
        if ($data['tipo_movimiento'] === 'entrada' && isset($data['costo_unitario'])) {
            $this->updateAverageCost($data['id_producto'], $data['cantidad'], $data['costo_unitario']);
        }
    }

    /**
     * Calcula nuevo stock según tipo de movimiento
     */
    private function calculateNewStock($stockActual, $cantidad, $tipoMovimiento) {
        switch ($tipoMovimiento) {
            case 'entrada':
                return $stockActual + $cantidad;

            case 'salida':
                return $stockActual - $cantidad;

            case 'ajuste':
                return $cantidad; // En ajustes, la cantidad es absoluta

            default:
                return $stockActual;
        }
    }

    /**
     * Actualiza costo promedio del producto
     */
    private function updateAverageCost($idProducto, $cantidad, $costoUnitario) {
        // Obtener stock actual y costo promedio
        $stmt = $this->db->prepare("
            SELECT
                SUM(s.cantidad) as stock_total,
                p.costo_promedio
            FROM productos p
            LEFT JOIN stock s ON p.id = s.id_producto
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stockAnterior = ($producto['stock_total'] ?? 0) - $cantidad;
        $costoAnterior = $producto['costo_promedio'] ?? 0;

        // Calcular nuevo costo promedio ponderado
        $nuevoStock = $stockAnterior + $cantidad;
        $nuevoCostoPromedio = $nuevoStock > 0
            ? (($stockAnterior * $costoAnterior) + ($cantidad * $costoUnitario)) / $nuevoStock
            : $costoUnitario;

        // Actualizar producto
        $stmt = $this->db->prepare("
            UPDATE productos
            SET costo_promedio = ?
            WHERE id = ?
        ");
        $stmt->execute([$nuevoCostoPromedio, $idProducto]);
    }

    /**
     * Verifica alertas de stock bajo/alto
     */
    private function checkStockAlerts($idProducto) {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.nombre,
                p.codigo,
                p.stock_minimo,
                p.stock_maximo,
                SUM(s.cantidad) as stock_total
            FROM productos p
            LEFT JOIN stock s ON p.id = s.id_producto
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$producto) return;

        $stockTotal = $producto['stock_total'] ?? 0;

        // Alerta de stock bajo
        if ($stockTotal <= $producto['stock_minimo']) {
            $this->notificationService->send([
                'tipo' => 'stock_bajo',
                'titulo' => 'Alerta: Stock Bajo',
                'mensaje' => "El producto {$producto['nombre']} ({$producto['codigo']}) tiene stock bajo: {$stockTotal} unidades. Mínimo: {$producto['stock_minimo']}",
                'datos' => json_encode($producto),
            ]);

            // Sugerir orden de compra automática
            $this->suggestPurchaseOrder($producto);
        }

        // Alerta de stock alto
        if ($producto['stock_maximo'] && $stockTotal >= $producto['stock_maximo']) {
            $this->notificationService->send([
                'tipo' => 'stock_alto',
                'titulo' => 'Alerta: Stock Alto',
                'mensaje' => "El producto {$producto['nombre']} ({$producto['codigo']}) tiene stock alto: {$stockTotal} unidades. Máximo: {$producto['stock_maximo']}",
                'datos' => json_encode($producto),
            ]);
        }
    }

    /**
     * Sugiere orden de compra automática
     */
    private function suggestPurchaseOrder($producto) {
        // Calcular cantidad sugerida
        $cantidadSugerida = $producto['stock_maximo'] - $producto['stock_total'];

        if ($cantidadSugerida <= 0) return;

        // Buscar proveedor principal
        $stmt = $this->db->prepare("
            SELECT id_proveedor, precio
            FROM productos_proveedores
            WHERE id_producto = ?
            ORDER BY principal DESC, precio ASC
            LIMIT 1
        ");
        $stmt->execute([$producto['id']]);
        $proveedor = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$proveedor) return;

        // Crear sugerencia de orden de compra
        $stmt = $this->db->prepare("
            INSERT INTO sugerencias_compra
            (id_producto, id_proveedor, cantidad_sugerida, motivo, created_at)
            VALUES (?, ?, ?, 'stock_bajo', NOW())
        ");
        $stmt->execute([
            $producto['id'],
            $proveedor['id_proveedor'],
            $cantidadSugerida,
        ]);
    }

    /**
     * Transfiere stock entre bodegas
     */
    public function transferStock($idProducto, $idBodegaOrigen, $idBodegaDestino, $cantidad, $observaciones = null) {
        $this->db->beginTransaction();

        try {
            // Salida de bodega origen
            $this->registerMovement([
                'id_producto' => $idProducto,
                'id_bodega' => $idBodegaOrigen,
                'cantidad' => $cantidad,
                'tipo_movimiento' => 'salida',
                'tipo_documento' => 'transferencia',
                'observaciones' => $observaciones,
            ]);

            // Entrada a bodega destino
            $this->registerMovement([
                'id_producto' => $idProducto,
                'id_bodega' => $idBodegaDestino,
                'cantidad' => $cantidad,
                'tipo_movimiento' => 'entrada',
                'tipo_documento' => 'transferencia',
                'observaciones' => $observaciones,
            ]);

            $this->db->commit();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Realiza inventario físico (ajuste)
     */
    public function physicalInventory($idProducto, $idBodega, $cantidadFisica, $observaciones = null) {
        // Obtener stock actual
        $stmt = $this->db->prepare("
            SELECT cantidad
            FROM stock
            WHERE id_producto = ? AND id_bodega = ?
        ");
        $stmt->execute([$idProducto, $idBodega]);
        $stock = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stockActual = $stock['cantidad'] ?? 0;
        $diferencia = $cantidadFisica - $stockActual;

        if ($diferencia == 0) {
            return ['success' => true, 'message' => 'Stock correcto, sin ajustes'];
        }

        // Registrar ajuste
        return $this->registerMovement([
            'id_producto' => $idProducto,
            'id_bodega' => $idBodega,
            'cantidad' => $cantidadFisica,
            'tipo_movimiento' => 'ajuste',
            'tipo_documento' => 'inventario_fisico',
            'observaciones' => $observaciones . " (Diferencia: {$diferencia})",
        ]);
    }

    /**
     * Obtiene stock disponible de un producto
     */
    public function getAvailableStock($idProducto, $idBodega = null) {
        if ($idBodega) {
            $stmt = $this->db->prepare("
                SELECT cantidad
                FROM stock
                WHERE id_producto = ? AND id_bodega = ?
            ");
            $stmt->execute([$idProducto, $idBodega]);
            $stock = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $stock['cantidad'] ?? 0;
        } else {
            // Total en todas las bodegas
            $stmt = $this->db->prepare("
                SELECT SUM(cantidad) as total
                FROM stock
                WHERE id_producto = ?
            ");
            $stmt->execute([$idProducto]);
            $stock = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $stock['total'] ?? 0;
        }
    }

    /**
     * Obtiene bodega por defecto
     */
    private function getDefaultWarehouse($idProducto) {
        // Obtener empresa del producto
        $stmt = $this->db->prepare("SELECT id_empresa FROM productos WHERE id = ?");
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Buscar bodega principal
        $stmt = $this->db->prepare("
            SELECT id
            FROM bodegas
            WHERE id_empresa = ? AND principal = 1
            LIMIT 1
        ");
        $stmt->execute([$producto['id_empresa']]);
        $bodega = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $bodega['id'] ?? null;
    }

    /**
     * Valida datos de movimiento
     */
    private function validateMovementData($data) {
        $required = ['id_producto', 'cantidad', 'tipo_movimiento'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo requerido: {$field}");
            }
        }

        if ($data['cantidad'] <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        $tiposValidos = ['entrada', 'salida', 'ajuste', 'transferencia'];
        if (!in_array($data['tipo_movimiento'], $tiposValidos)) {
            throw new \Exception("Tipo de movimiento inválido");
        }
    }
}
