<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Generación de Reportes
 * Genera reportes en PDF, Excel, CSV con datos del sistema
 */

class ReportService {
    private $db;
    private $pdfGenerator;
    private $excelGenerator;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->pdfGenerator = new \App\Utils\PDFGenerator();
        $this->excelGenerator = new \App\Utils\ExcelGenerator();
    }

    /**
     * Genera reporte financiero
     */
    public function generateFinancialReport($idEmpresa, $fechaInicio, $fechaFin, $formato = 'pdf') {
        $data = [
            'empresa' => $this->getEmpresaData($idEmpresa),
            'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
            'ventas' => $this->getVentasData($idEmpresa, $fechaInicio, $fechaFin),
            'compras' => $this->getComprasData($idEmpresa, $fechaInicio, $fechaFin),
            'gastos' => $this->getGastosData($idEmpresa, $fechaInicio, $fechaFin),
            'ingresos' => $this->getIngresosData($idEmpresa, $fechaInicio, $fechaFin),
            'balance' => $this->calculateBalance($idEmpresa, $fechaInicio, $fechaFin),
        ];

        if ($formato === 'pdf') {
            return $this->pdfGenerator->generateReport('financial', $data);
        } elseif ($formato === 'excel') {
            return $this->excelGenerator->exportReport('financial', $data);
        } else {
            return $this->generateCSV($data);
        }
    }

    /**
     * Reporte de ventas
     */
    public function generateSalesReport($idEmpresa, $fechaInicio, $fechaFin, $options = []) {
        $groupBy = $options['group_by'] ?? 'day'; // day, week, month, product, customer

        $stmt = $this->db->prepare("
            SELECT
                DATE(f.fecha_emision) as fecha,
                COUNT(f.id) as total_facturas,
                SUM(f.total) as total_ventas,
                SUM(f.total - f.total_impuestos) as neto,
                SUM(f.total_impuestos) as impuestos,
                COUNT(DISTINCT f.id_cliente) as clientes_unicos
            FROM facturas_venta f
            WHERE f.id_empresa = ?
            AND f.fecha_emision BETWEEN ? AND ?
            AND f.estado != 'anulada'
            GROUP BY DATE(f.fecha_emision)
            ORDER BY fecha DESC
        ");

        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin]);
        $ventas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agregar productos más vendidos
        $topProducts = $this->getTopSellingProducts($idEmpresa, $fechaInicio, $fechaFin, 10);

        // Agregar mejores clientes
        $topCustomers = $this->getTopCustomers($idEmpresa, $fechaInicio, $fechaFin, 10);

        return [
            'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
            'ventas_diarias' => $ventas,
            'productos_top' => $topProducts,
            'clientes_top' => $topCustomers,
            'totales' => $this->calculateSalesTotals($ventas),
        ];
    }

    /**
     * Reporte de inventario
     */
    public function generateInventoryReport($idEmpresa, $options = []) {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.codigo,
                p.nombre,
                p.tipo,
                SUM(s.cantidad) as stock_total,
                p.stock_minimo,
                p.stock_maximo,
                p.costo_promedio,
                SUM(s.cantidad * p.costo_promedio) as valor_inventario,
                CASE
                    WHEN SUM(s.cantidad) <= p.stock_minimo THEN 'bajo'
                    WHEN SUM(s.cantidad) >= p.stock_maximo THEN 'alto'
                    ELSE 'normal'
                END as estado_stock
            FROM productos p
            LEFT JOIN stock s ON p.id = s.id_producto
            WHERE p.id_empresa = ?
            AND p.activo = 1
            GROUP BY p.id
            ORDER BY valor_inventario DESC
        ");

        $stmt->execute([$idEmpresa]);
        $productos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Productos con stock bajo
        $stockBajo = array_filter($productos, function($p) {
            return $p['estado_stock'] === 'bajo';
        });

        // Productos sin movimiento
        $sinMovimiento = $this->getProductsWithoutMovement($idEmpresa, 90); // 90 días

        return [
            'productos' => $productos,
            'stock_bajo' => array_values($stockBajo),
            'sin_movimiento' => $sinMovimiento,
            'resumen' => [
                'total_productos' => count($productos),
                'valor_total' => array_sum(array_column($productos, 'valor_inventario')),
                'alertas_stock_bajo' => count($stockBajo),
                'productos_sin_movimiento' => count($sinMovimiento),
            ],
        ];
    }

    /**
     * Reporte de cuentas por cobrar
     */
    public function generateAccountsReceivableReport($idEmpresa, $options = []) {
        $stmt = $this->db->prepare("
            SELECT
                f.id,
                f.numero_factura,
                f.fecha_emision,
                f.fecha_vencimiento,
                e.razon_social as cliente,
                e.rut as cliente_rut,
                f.total,
                COALESCE(SUM(c.monto), 0) as pagado,
                (f.total - COALESCE(SUM(c.monto), 0)) as saldo,
                DATEDIFF(CURDATE(), f.fecha_vencimiento) as dias_vencido,
                CASE
                    WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 90 THEN 'muy_vencido'
                    WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 30 THEN 'vencido'
                    WHEN DATEDIFF(CURDATE(), f.fecha_vencimiento) > 0 THEN 'por_vencer'
                    ELSE 'vigente'
                END as estado_pago
            FROM facturas_venta f
            INNER JOIN entidades e ON f.id_cliente = e.id
            LEFT JOIN cobranzas c ON c.id_factura = f.id
            WHERE f.id_empresa = ?
            AND f.estado = 'emitida'
            GROUP BY f.id
            HAVING saldo > 0
            ORDER BY dias_vencido DESC
        ");

        $stmt->execute([$idEmpresa]);
        $cuentas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'cuentas' => $cuentas,
            'resumen' => [
                'total_por_cobrar' => array_sum(array_column($cuentas, 'saldo')),
                'vigentes' => $this->sumByEstado($cuentas, 'vigente'),
                'por_vencer' => $this->sumByEstado($cuentas, 'por_vencer'),
                'vencidas' => $this->sumByEstado($cuentas, 'vencido'),
                'muy_vencidas' => $this->sumByEstado($cuentas, 'muy_vencido'),
            ],
        ];
    }

    /**
     * Reporte de cuentas por pagar
     */
    public function generateAccountsPayableReport($idEmpresa, $options = []) {
        $stmt = $this->db->prepare("
            SELECT
                oc.id,
                oc.numero_orden,
                oc.fecha_emision,
                oc.fecha_vencimiento,
                e.razon_social as proveedor,
                e.rut as proveedor_rut,
                oc.total,
                COALESCE(SUM(p.monto), 0) as pagado,
                (oc.total - COALESCE(SUM(p.monto), 0)) as saldo,
                DATEDIFF(CURDATE(), oc.fecha_vencimiento) as dias_vencido
            FROM ordenes_compra oc
            INNER JOIN entidades e ON oc.id_proveedor = e.id
            LEFT JOIN pagos p ON p.id_orden_compra = oc.id
            WHERE oc.id_empresa = ?
            AND oc.estado = 'aprobada'
            GROUP BY oc.id
            HAVING saldo > 0
            ORDER BY oc.fecha_vencimiento ASC
        ");

        $stmt->execute([$idEmpresa]);
        $cuentas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'cuentas' => $cuentas,
            'resumen' => [
                'total_por_pagar' => array_sum(array_column($cuentas, 'saldo')),
                'cantidad_facturas' => count($cuentas),
            ],
        ];
    }

    /**
     * Reporte de RRHH
     */
    public function generateHRReport($idEmpresa, $mes, $ano) {
        $empleados = $this->getEmpleadosActivos($idEmpresa);
        $nominas = $this->getNominaMes($idEmpresa, $mes, $ano);
        $asistencia = $this->getAsistenciaMes($idEmpresa, $mes, $ano);

        return [
            'periodo' => ['mes' => $mes, 'ano' => $ano],
            'empleados' => $empleados,
            'nominas' => $nominas,
            'asistencia' => $asistencia,
            'resumen' => [
                'total_empleados' => count($empleados),
                'costo_total_nomina' => array_sum(array_column($nominas, 'total')),
                'promedio_asistencia' => $this->calculateAttendanceAverage($asistencia),
            ],
        ];
    }

    // ==================== Métodos auxiliares ====================

    private function getEmpresaData($idEmpresa) {
        $stmt = $this->db->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->execute([$idEmpresa]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getVentasData($idEmpresa, $fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare("
            SELECT SUM(total) as total, COUNT(*) as cantidad
            FROM facturas_venta
            WHERE id_empresa = ? AND fecha_emision BETWEEN ? AND ?
            AND estado != 'anulada'
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getComprasData($idEmpresa, $fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare("
            SELECT SUM(total) as total, COUNT(*) as cantidad
            FROM ordenes_compra
            WHERE id_empresa = ? AND fecha_emision BETWEEN ? AND ?
            AND estado = 'aprobada'
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getGastosData($idEmpresa, $fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare("
            SELECT SUM(debe) as total
            FROM asientos_contables_detalle acd
            INNER JOIN asientos_contables ac ON acd.id_asiento = ac.id
            INNER JOIN plan_cuentas pc ON acd.id_cuenta = pc.id
            WHERE ac.id_empresa = ? AND ac.fecha BETWEEN ? AND ?
            AND pc.tipo_cuenta = 'egreso'
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getIngresosData($idEmpresa, $fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare("
            SELECT SUM(haber) as total
            FROM asientos_contables_detalle acd
            INNER JOIN asientos_contables ac ON acd.id_asiento = ac.id
            INNER JOIN plan_cuentas pc ON acd.id_cuenta = pc.id
            WHERE ac.id_empresa = ? AND ac.fecha BETWEEN ? AND ?
            AND pc.tipo_cuenta = 'ingreso'
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function calculateBalance($idEmpresa, $fechaInicio, $fechaFin) {
        $ventas = $this->getVentasData($idEmpresa, $fechaInicio, $fechaFin);
        $compras = $this->getComprasData($idEmpresa, $fechaInicio, $fechaFin);

        return [
            'utilidad_bruta' => ($ventas['total'] ?? 0) - ($compras['total'] ?? 0),
            'margen' => ($ventas['total'] ?? 0) > 0
                ? (($ventas['total'] - $compras['total']) / $ventas['total']) * 100
                : 0,
        ];
    }

    private function getTopSellingProducts($idEmpresa, $fechaInicio, $fechaFin, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT
                p.codigo,
                p.nombre,
                SUM(fvd.cantidad) as cantidad_vendida,
                SUM(fvd.subtotal) as total_ventas
            FROM facturas_venta_detalle fvd
            INNER JOIN facturas_venta fv ON fvd.id_factura = fv.id
            INNER JOIN productos p ON fvd.id_producto = p.id
            WHERE fv.id_empresa = ?
            AND fv.fecha_emision BETWEEN ? AND ?
            AND fv.estado != 'anulada'
            GROUP BY p.id
            ORDER BY cantidad_vendida DESC
            LIMIT ?
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTopCustomers($idEmpresa, $fechaInicio, $fechaFin, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT
                e.razon_social,
                e.rut,
                COUNT(fv.id) as total_compras,
                SUM(fv.total) as total_gastado
            FROM facturas_venta fv
            INNER JOIN entidades e ON fv.id_cliente = e.id
            WHERE fv.id_empresa = ?
            AND fv.fecha_emision BETWEEN ? AND ?
            AND fv.estado != 'anulada'
            GROUP BY e.id
            ORDER BY total_gastado DESC
            LIMIT ?
        ");
        $stmt->execute([$idEmpresa, $fechaInicio, $fechaFin, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function calculateSalesTotals($ventas) {
        return [
            'total_ventas' => array_sum(array_column($ventas, 'total_ventas')),
            'total_facturas' => array_sum(array_column($ventas, 'total_facturas')),
            'promedio_diario' => count($ventas) > 0
                ? array_sum(array_column($ventas, 'total_ventas')) / count($ventas)
                : 0,
        ];
    }

    private function getProductsWithoutMovement($idEmpresa, $dias = 90) {
        $stmt = $this->db->prepare("
            SELECT p.*
            FROM productos p
            WHERE p.id_empresa = ?
            AND p.id NOT IN (
                SELECT DISTINCT id_producto
                FROM movimientos_inventario
                WHERE fecha_movimiento >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            )
            AND p.activo = 1
        ");
        $stmt->execute([$idEmpresa, $dias]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function sumByEstado($cuentas, $estado) {
        $filtered = array_filter($cuentas, function($c) use ($estado) {
            return $c['estado_pago'] === $estado;
        });
        return array_sum(array_column($filtered, 'saldo'));
    }

    private function getEmpleadosActivos($idEmpresa) {
        $stmt = $this->db->prepare("
            SELECT * FROM empleados
            WHERE id_empresa = ? AND activo = 1
        ");
        $stmt->execute([$idEmpresa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getNominaMes($idEmpresa, $mes, $ano) {
        $stmt = $this->db->prepare("
            SELECT * FROM nominas
            WHERE id_empresa = ? AND mes = ? AND ano = ?
        ");
        $stmt->execute([$idEmpresa, $mes, $ano]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAsistenciaMes($idEmpresa, $mes, $ano) {
        $stmt = $this->db->prepare("
            SELECT * FROM asistencia
            WHERE id_empresa = ?
            AND MONTH(fecha) = ?
            AND YEAR(fecha) = ?
        ");
        $stmt->execute([$idEmpresa, $mes, $ano]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function calculateAttendanceAverage($asistencia) {
        if (empty($asistencia)) return 0;

        $presente = count(array_filter($asistencia, function($a) {
            return $a['estado'] === 'presente';
        }));

        return ($presente / count($asistencia)) * 100;
    }

    private function generateCSV($data) {
        // Implementación simple de CSV
        return json_encode($data); // Placeholder
    }
}
