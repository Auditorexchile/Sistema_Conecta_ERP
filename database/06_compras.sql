-- =====================================================
-- CONECTA ERP - MÓDULO COMPRAS
-- Archivo: 06_compras.sql
-- Descripción: Órdenes de compra, recepciones, devoluciones
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: ordenes_compra
-- Descripción: Órdenes de compra a proveedores
-- =====================================================
CREATE TABLE ordenes_compra (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_orden VARCHAR(50) NOT NULL,
    id_proveedor INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,
    id_bodega INT UNSIGNED NOT NULL,

    fecha_emision DATE NOT NULL,
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
    estado ENUM('borrador', 'enviada', 'confirmada', 'recepcion_parcial', 'recibida', 'facturada', 'cerrada', 'cancelada') NOT NULL DEFAULT 'borrador',

    -- Condiciones comerciales
    condiciones_pago VARCHAR(255) NULL,
    dias_credito INT DEFAULT 0,
    forma_pago ENUM('contado', 'credito', 'anticipo', 'consignacion') DEFAULT 'credito',

    -- Información adicional
    observaciones TEXT NULL,
    notas_internas TEXT NULL,

    id_usuario_crea INT UNSIGNED NULL,
    id_usuario_aprueba INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_orden),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    INDEX idx_bodega (id_bodega),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_orden, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_proveedor) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_crea) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ordenes_compra_detalle
-- Descripción: Detalle de productos en órdenes de compra
-- =====================================================
CREATE TABLE ordenes_compra_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden_compra INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(500) NULL,
    cantidad DECIMAL(15,4) NOT NULL,
    cantidad_recibida DECIMAL(15,4) DEFAULT 0,
    cantidad_pendiente DECIMAL(15,4) AS (cantidad - cantidad_recibida) STORED,

    precio_unitario DECIMAL(15,4) NOT NULL,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    descuento_monto DECIMAL(15,4) DEFAULT 0,
    subtotal DECIMAL(15,4) AS (cantidad * precio_unitario - descuento_monto) STORED,

    impuesto_porcentaje DECIMAL(5,2) DEFAULT 0,
    impuesto_monto DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) AS (subtotal + impuesto_monto) STORED,

    fecha_entrega_linea DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_orden (id_orden_compra),
    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_orden_compra) REFERENCES ordenes_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: recepciones_compra
-- Descripción: Recepciones de mercadería
-- =====================================================
CREATE TABLE recepciones_compra (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_recepcion VARCHAR(50) NOT NULL,
    id_orden_compra INT UNSIGNED NULL,
    id_proveedor INT UNSIGNED NOT NULL,
    id_bodega INT UNSIGNED NOT NULL,

    fecha_recepcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    numero_guia_despacho VARCHAR(50) NULL,
    fecha_guia DATE NULL,

    estado ENUM('borrador', 'recibida', 'controlada', 'almacenada', 'rechazada') NOT NULL DEFAULT 'borrador',

    observaciones TEXT NULL,
    id_usuario_recibe INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_recepcion),
    INDEX idx_orden (id_orden_compra),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_bodega (id_bodega),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_recepcion),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_recepcion),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_orden_compra) REFERENCES ordenes_compra(id) ON DELETE SET NULL,
    FOREIGN KEY (id_proveedor) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_recibe) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: recepciones_compra_detalle
-- Descripción: Detalle de productos recibidos
-- =====================================================
CREATE TABLE recepciones_compra_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_recepcion INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    id_orden_detalle INT UNSIGNED NULL,

    cantidad_ordenada DECIMAL(15,4) DEFAULT 0,
    cantidad_recibida DECIMAL(15,4) NOT NULL,
    cantidad_aceptada DECIMAL(15,4) DEFAULT 0,
    cantidad_rechazada DECIMAL(15,4) DEFAULT 0,

    motivo_rechazo VARCHAR(255) NULL,
    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_recepcion (id_recepcion),
    INDEX idx_producto (id_producto),
    INDEX idx_orden_detalle (id_orden_detalle),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_recepcion) REFERENCES recepciones_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_orden_detalle) REFERENCES ordenes_compra_detalle(id) ON DELETE SET NULL,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: devoluciones_compra
-- Descripción: Devoluciones a proveedores
-- =====================================================
CREATE TABLE devoluciones_compra (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_devolucion VARCHAR(50) NOT NULL,
    id_proveedor INT UNSIGNED NOT NULL,
    id_orden_compra INT UNSIGNED NULL,
    id_recepcion INT UNSIGNED NULL,

    fecha_devolucion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo ENUM('defectuoso', 'incorrecto', 'excedente', 'vencido', 'otro') NOT NULL,
    descripcion_motivo TEXT NULL,

    estado ENUM('borrador', 'enviada', 'aceptada', 'rechazada', 'nota_credito') NOT NULL DEFAULT 'borrador',

    total_devolucion DECIMAL(15,4) DEFAULT 0,
    observaciones TEXT NULL,

    id_usuario INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_devolucion),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_orden (id_orden_compra),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_devolucion),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_proveedor) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_orden_compra) REFERENCES ordenes_compra(id) ON DELETE SET NULL,
    FOREIGN KEY (id_recepcion) REFERENCES recepciones_compra(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: devoluciones_compra_detalle
-- Descripción: Detalle de productos devueltos
-- =====================================================
CREATE TABLE devoluciones_compra_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_devolucion INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    cantidad DECIMAL(15,4) NOT NULL,
    precio_unitario DECIMAL(15,4) DEFAULT 0,
    total_linea DECIMAL(15,4) AS (cantidad * precio_unitario) STORED,

    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_devolucion (id_devolucion),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_devolucion) REFERENCES devoluciones_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: solicitudes_compra
-- Descripción: Solicitudes internas de compra (requisiciones)
-- =====================================================
CREATE TABLE solicitudes_compra (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_solicitud VARCHAR(50) NOT NULL,

    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_necesaria DATE NOT NULL,

    id_usuario_solicita INT UNSIGNED NULL,
    id_centro_costo INT UNSIGNED NULL,
    id_proyecto INT UNSIGNED NULL,

    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    estado ENUM('borrador', 'enviada', 'aprobada', 'rechazada', 'comprada', 'cerrada') NOT NULL DEFAULT 'borrador',

    justificacion TEXT NULL,
    observaciones TEXT NULL,

    id_aprobador INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_solicitud),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_solicitud),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_solicitud),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_solicita) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: solicitudes_compra_detalle
-- Descripción: Detalle de productos solicitados
-- =====================================================
CREATE TABLE solicitudes_compra_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NULL,
    descripcion VARCHAR(500) NOT NULL,

    cantidad DECIMAL(15,4) NOT NULL,
    precio_estimado DECIMAL(15,4) DEFAULT 0,

    id_proveedor_sugerido INT UNSIGNED NULL,
    observaciones VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_solicitud (id_solicitud),
    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
