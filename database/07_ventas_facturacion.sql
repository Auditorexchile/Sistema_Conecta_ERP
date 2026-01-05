-- =====================================================
-- CONECTA ERP - MÓDULO VENTAS Y FACTURACIÓN
-- Archivo: 07_ventas_facturacion.sql
-- Descripción: Cotizaciones, pedidos, facturas, boletas, notas crédito/débito
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: cotizaciones
-- Descripción: Cotizaciones a clientes
-- =====================================================
CREATE TABLE cotizaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_cotizacion VARCHAR(50) NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,

    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    valida_hasta DATE NULL,

    -- Moneda y totales
    id_moneda INT UNSIGNED NOT NULL,
    tipo_cambio DECIMAL(15,6) DEFAULT 1,

    subtotal DECIMAL(15,4) DEFAULT 0,
    descuento DECIMAL(15,4) DEFAULT 0,
    impuestos DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    -- Estado
    estado ENUM('borrador', 'enviada', 'aceptada', 'rechazada', 'vencida', 'pedido') NOT NULL DEFAULT 'borrador',

    -- Condiciones comerciales
    condiciones_pago VARCHAR(255) NULL,
    tiempo_entrega VARCHAR(100) NULL,
    garantia VARCHAR(255) NULL,

    -- Información adicional
    observaciones TEXT NULL,
    notas_internas TEXT NULL,

    id_vendedor INT UNSIGNED NULL,
    id_usuario_crea INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_cotizacion),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    INDEX idx_vendedor (id_vendedor),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_cotizacion, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: cotizaciones_detalle
-- Descripción: Detalle de productos en cotizaciones
-- =====================================================
CREATE TABLE cotizaciones_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cotizacion INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,

    precio_unitario DECIMAL(15,4) NOT NULL,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    descuento_monto DECIMAL(15,4) DEFAULT 0,
    subtotal DECIMAL(15,4) AS (cantidad * precio_unitario - descuento_monto) STORED,

    impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
    impuesto_monto DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) AS (subtotal + impuesto_monto) STORED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cotizacion (id_cotizacion),
    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pedidos_venta
-- Descripción: Pedidos/órdenes de venta
-- =====================================================
CREATE TABLE pedidos_venta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_pedido VARCHAR(50) NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,
    id_cotizacion INT UNSIGNED NULL,
    id_sucursal INT UNSIGNED NULL,
    id_bodega INT UNSIGNED NOT NULL,

    fecha_pedido DATE NOT NULL,
    fecha_entrega_esperada DATE NULL,
    fecha_entrega_real DATE NULL,

    -- Moneda y totales
    id_moneda INT UNSIGNED NOT NULL,
    tipo_cambio DECIMAL(15,6) DEFAULT 1,

    subtotal DECIMAL(15,4) DEFAULT 0,
    descuento DECIMAL(15,4) DEFAULT 0,
    impuestos DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    -- Estado
    estado ENUM('borrador', 'confirmado', 'preparacion', 'preparado', 'despachado', 'entregado', 'facturado', 'cerrado', 'cancelado') NOT NULL DEFAULT 'borrador',

    -- Información de envío
    direccion_entrega TEXT NULL,
    contacto_entrega VARCHAR(150) NULL,
    telefono_entrega VARCHAR(30) NULL,
    instrucciones_entrega TEXT NULL,

    -- Información adicional
    observaciones TEXT NULL,
    id_vendedor INT UNSIGNED NULL,
    id_usuario_crea INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_pedido),
    INDEX idx_cliente (id_cliente),
    INDEX idx_cotizacion (id_cotizacion),
    INDEX idx_estado (estado),
    INDEX idx_fecha_pedido (fecha_pedido),
    INDEX idx_bodega (id_bodega),
    INDEX idx_vendedor (id_vendedor),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_pedido, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id) ON DELETE SET NULL,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pedidos_venta_detalle
-- Descripción: Detalle de productos en pedidos
-- =====================================================
CREATE TABLE pedidos_venta_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,
    cantidad_despachada DECIMAL(15,4) DEFAULT 0,
    cantidad_facturada DECIMAL(15,4) DEFAULT 0,
    cantidad_pendiente DECIMAL(15,4) AS (cantidad - cantidad_despachada) STORED,

    precio_unitario DECIMAL(15,4) NOT NULL,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    descuento_monto DECIMAL(15,4) DEFAULT 0,
    subtotal DECIMAL(15,4) AS (cantidad * precio_unitario - descuento_monto) STORED,

    impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
    impuesto_monto DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) AS (subtotal + impuesto_monto) STORED,

    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_pedido) REFERENCES pedidos_venta(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: guias_despacho
-- Descripción: Guías de despacho
-- =====================================================
CREATE TABLE guias_despacho (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_guia VARCHAR(50) NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,
    id_pedido INT UNSIGNED NULL,
    id_bodega INT UNSIGNED NOT NULL,

    fecha_emision DATE NOT NULL,
    fecha_despacho DATE NULL,

    -- Información de traslado
    tipo_traslado ENUM('venta', 'traslado_interno', 'devolucion', 'consignacion', 'otro') DEFAULT 'venta',
    motivo_traslado VARCHAR(255) NULL,

    direccion_destino TEXT NULL,
    ciudad_destino VARCHAR(100) NULL,

    -- Transporte
    patente_vehiculo VARCHAR(20) NULL,
    nombre_conductor VARCHAR(150) NULL,
    rut_conductor VARCHAR(20) NULL,

    estado ENUM('borrador', 'emitida', 'en_transito', 'entregada', 'anulada') NOT NULL DEFAULT 'borrador',

    observaciones TEXT NULL,
    id_usuario INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_guia),
    INDEX idx_cliente (id_cliente),
    INDEX idx_pedido (id_pedido),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_guia, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCIAS empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos_venta(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: guias_despacho_detalle
-- Descripción: Detalle de productos en guías
-- =====================================================
CREATE TABLE guias_despacho_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_guia INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,

    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_guia (id_guia),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_guia) REFERENCES guias_despacho(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: facturas_venta
-- Descripción: Facturas de venta (incluye DTE Chile)
-- =====================================================
CREATE TABLE facturas_venta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_documento ENUM('factura', 'factura_exenta', 'boleta', 'boleta_exenta', 'factura_electronica', 'boleta_electronica') NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    folio INT UNSIGNED NULL COMMENT 'Folio SII para Chile',

    id_cliente INT UNSIGNED NOT NULL,
    id_pedido INT UNSIGNED NULL,
    id_guia INT UNSIGNED NULL,
    id_sucursal INT UNSIGNED NULL,

    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL,

    -- Moneda y totales
    id_moneda INT UNSIGNED NOT NULL,
    tipo_cambio DECIMAL(15,6) DEFAULT 1,

    neto DECIMAL(15,4) DEFAULT 0,
    exento DECIMAL(15,4) DEFAULT 0,
    iva DECIMAL(15,4) DEFAULT 0,
    otros_impuestos DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    -- Estado
    estado ENUM('borrador', 'emitida', 'enviada', 'aceptada', 'rechazada', 'pagada', 'vencida', 'anulada') NOT NULL DEFAULT 'borrador',

    -- DTE (Chile)
    dte_generado TINYINT(1) DEFAULT 0,
    dte_xml TEXT NULL,
    dte_track_id VARCHAR(50) NULL,
    dte_estado VARCHAR(50) NULL,
    fecha_dte TIMESTAMP NULL,

    -- Información adicional
    condiciones_pago VARCHAR(255) NULL,
    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta_credito', 'tarjeta_debito', 'otro') NULL,
    glosa TEXT NULL,

    id_vendedor INT UNSIGNED NULL,
    id_usuario_crea INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_numero (numero_documento),
    INDEX idx_folio (folio),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    INDEX idx_vendedor (id_vendedor),
    UNIQUE KEY uk_empresa_tipo_numero (id_empresa, tipo_documento, numero_documento, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos_venta(id) ON DELETE SET NULL,
    FOREIGN KEY (id_guia) REFERENCES guias_despacho(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: facturas_venta_detalle
-- Descripción: Detalle de productos en facturas
-- =====================================================
CREATE TABLE facturas_venta_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_factura INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,

    precio_unitario DECIMAL(15,4) NOT NULL,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    descuento_monto DECIMAL(15,4) DEFAULT 0,
    neto_linea DECIMAL(15,4) AS (cantidad * precio_unitario - descuento_monto) STORED,

    impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
    impuesto_monto DECIMAL(15,4) DEFAULT 0,
    total_linea DECIMAL(15,4) AS (neto_linea + impuesto_monto) STORED,

    exento TINYINT(1) DEFAULT 0,

    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_factura (id_factura),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_factura) REFERENCES facturas_venta(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: notas_credito
-- Descripción: Notas de crédito
-- =====================================================
CREATE TABLE notas_credito (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_nota VARCHAR(50) NOT NULL,
    folio INT UNSIGNED NULL,

    id_factura_ref INT UNSIGNED NULL COMMENT 'Factura que anula o modifica',
    tipo_nota ENUM('anulacion', 'devolucion', 'descuento', 'correccion') NOT NULL,

    id_cliente INT UNSIGNED NOT NULL,
    fecha_emision DATE NOT NULL,

    id_moneda INT UNSIGNED NOT NULL,
    neto DECIMAL(15,4) DEFAULT 0,
    iva DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    estado ENUM('borrador', 'emitida', 'aplicada', 'anulada') NOT NULL DEFAULT 'borrador',

    motivo TEXT NULL,
    observaciones TEXT NULL,

    -- DTE
    dte_generado TINYINT(1) DEFAULT 0,
    dte_xml TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_nota),
    INDEX idx_factura_ref (id_factura_ref),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_nota, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_factura_ref) REFERENCES facturas_venta(id) ON DELETE SET NULL,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: notas_credito_detalle
-- Descripción: Detalle de notas de crédito
-- =====================================================
CREATE TABLE notas_credito_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_nota_credito INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,
    precio_unitario DECIMAL(15,4) NOT NULL,
    neto_linea DECIMAL(15,4) AS (cantidad * precio_unitario) STORED,
    iva_linea DECIMAL(15,4) DEFAULT 0,
    total_linea DECIMAL(15,4) AS (neto_linea + iva_linea) STORED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_nota (id_nota_credito),
    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_nota_credito) REFERENCES notas_credito(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: notas_debito
-- Descripción: Notas de débito
-- =====================================================
CREATE TABLE notas_debito (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_nota VARCHAR(50) NOT NULL,
    folio INT UNSIGNED NULL,

    id_factura_ref INT UNSIGNED NULL,
    tipo_nota ENUM('interes', 'recargo', 'gasto_adicional', 'correccion') NOT NULL,

    id_cliente INT UNSIGNED NOT NULL,
    fecha_emision DATE NOT NULL,

    id_moneda INT UNSIGNED NOT NULL,
    neto DECIMAL(15,4) DEFAULT 0,
    iva DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    estado ENUM('borrador', 'emitida', 'aplicada', 'anulada') NOT NULL DEFAULT 'borrador',

    motivo TEXT NULL,
    observaciones TEXT NULL,

    -- DTE
    dte_generado TINYINT(1) DEFAULT 0,
    dte_xml TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_nota),
    INDEX idx_factura_ref (id_factura_ref),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_nota, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_factura_ref) REFERENCES facturas_venta(id) ON DELETE SET NULL,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
