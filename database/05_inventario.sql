-- =====================================================
-- CONECTA ERP - MÓDULO INVENTARIO
-- Archivo: 05_inventario.sql
-- Descripción: Bodegas, stock, kardex, lotes, series, movimientos
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: bodegas
-- Descripción: Almacenes/bodegas de la empresa
-- =====================================================
CREATE TABLE bodegas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('principal', 'sucursal', 'transito', 'mermas', 'consignacion', 'cuarentena') NOT NULL DEFAULT 'principal',

    -- Dirección
    direccion VARCHAR(255) NULL,
    ciudad VARCHAR(100) NULL,
    region VARCHAR(100) NULL,
    codigo_postal VARCHAR(20) NULL,

    -- Responsable
    id_responsable INT UNSIGNED NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,

    -- Configuración
    permite_ventas TINYINT(1) NOT NULL DEFAULT 1,
    permite_compras TINYINT(1) NOT NULL DEFAULT 1,
    controla_ubicaciones TINYINT(1) NOT NULL DEFAULT 0,
    es_principal TINYINT(1) NOT NULL DEFAULT 0,

    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ubicaciones_bodega
-- Descripción: Ubicaciones dentro de cada bodega (rack, estante, etc)
-- =====================================================
CREATE TABLE ubicaciones_bodega (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_bodega INT UNSIGNED NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('pasillo', 'rack', 'estante', 'piso', 'zona', 'otro') NOT NULL DEFAULT 'estante',
    nivel INT NOT NULL DEFAULT 1,
    capacidad_maxima DECIMAL(15,4) NULL,
    unidad_capacidad VARCHAR(20) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_bodega (id_bodega),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_bodega_codigo (id_bodega, codigo),
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: stock
-- Descripción: Stock actual por producto y bodega
-- =====================================================
CREATE TABLE stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    id_bodega INT UNSIGNED NOT NULL,
    id_ubicacion INT UNSIGNED NULL,

    cantidad_actual DECIMAL(15,4) NOT NULL DEFAULT 0,
    cantidad_reservada DECIMAL(15,4) NOT NULL DEFAULT 0,
    cantidad_disponible DECIMAL(15,4) AS (cantidad_actual - cantidad_reservada) STORED,
    cantidad_transito DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'En transferencias',

    costo_promedio DECIMAL(15,4) DEFAULT 0,
    valor_inventario DECIMAL(15,4) AS (cantidad_actual * costo_promedio) STORED,

    ultima_entrada TIMESTAMP NULL,
    ultima_salida TIMESTAMP NULL,
    ultimo_inventario TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    INDEX idx_bodega (id_bodega),
    INDEX idx_ubicacion (id_ubicacion),
    INDEX idx_cantidad (cantidad_actual),
    UNIQUE KEY uk_producto_bodega_ubicacion (id_producto, id_bodega, id_ubicacion),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones_bodega(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: movimientos_inventario
-- Descripción: Todos los movimientos de stock (kardex)
-- =====================================================
CREATE TABLE movimientos_inventario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    id_bodega INT UNSIGNED NOT NULL,
    id_ubicacion INT UNSIGNED NULL,

    -- Tipo de movimiento
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste', 'transferencia', 'produccion', 'devolucion') NOT NULL,
    tipo_documento ENUM('compra', 'venta', 'transferencia', 'ajuste', 'produccion', 'inventario', 'merma', 'otro') NOT NULL,
    numero_documento VARCHAR(50) NULL,
    id_documento INT UNSIGNED NULL COMMENT 'ID del documento relacionado',

    -- Cantidades y costos
    cantidad DECIMAL(15,4) NOT NULL,
    signo TINYINT NOT NULL COMMENT '+1 para entrada, -1 para salida',
    costo_unitario DECIMAL(15,4) DEFAULT 0,
    costo_total DECIMAL(15,4) AS (cantidad * costo_unitario) STORED,

    -- Saldos después del movimiento
    saldo_cantidad DECIMAL(15,4) NOT NULL,
    saldo_valor DECIMAL(15,4) DEFAULT 0,
    costo_promedio DECIMAL(15,4) DEFAULT 0,

    -- Lote/Serie
    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    -- Información adicional
    glosa TEXT NULL,
    id_usuario INT UNSIGNED NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_producto (id_producto),
    INDEX idx_bodega (id_bodega),
    INDEX idx_tipo (tipo_movimiento, tipo_documento),
    INDEX idx_documento (tipo_documento, numero_documento),
    INDEX idx_fecha (fecha_movimiento),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: lotes
-- Descripción: Control de lotes de productos
-- =====================================================
CREATE TABLE lotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    numero_lote VARCHAR(50) NOT NULL,

    fecha_fabricacion DATE NULL,
    fecha_vencimiento DATE NULL,
    dias_vida_util INT NULL,

    cantidad_inicial DECIMAL(15,4) NOT NULL,
    cantidad_actual DECIMAL(15,4) NOT NULL,

    id_proveedor INT UNSIGNED NULL,
    numero_orden_compra VARCHAR(50) NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    motivo_bloqueo VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_producto (id_producto),
    INDEX idx_numero (numero_lote),
    INDEX idx_vencimiento (fecha_vencimiento),
    INDEX idx_proveedor (id_proveedor),
    UNIQUE KEY uk_empresa_producto_lote (id_empresa, id_producto, numero_lote),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: series
-- Descripción: Control de números de serie (productos únicos)
-- =====================================================
CREATE TABLE series (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    numero_serie VARCHAR(100) NOT NULL,

    id_bodega INT UNSIGNED NULL,
    estado ENUM('disponible', 'vendido', 'reservado', 'garantia', 'baja') NOT NULL DEFAULT 'disponible',

    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_venta TIMESTAMP NULL,
    id_cliente INT UNSIGNED NULL,

    id_proveedor INT UNSIGNED NULL,
    numero_orden_compra VARCHAR(50) NULL,

    garantia_meses INT NULL,
    fecha_fin_garantia DATE NULL,

    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_producto (id_producto),
    INDEX idx_serie (numero_serie),
    INDEX idx_bodega (id_bodega),
    INDEX idx_estado (estado),
    INDEX idx_cliente (id_cliente),
    UNIQUE KEY uk_empresa_producto_serie (id_empresa, id_producto, numero_serie),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: transferencias_bodega
-- Descripción: Transferencias entre bodegas
-- =====================================================
CREATE TABLE transferencias_bodega (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_transferencia VARCHAR(50) NOT NULL,

    id_bodega_origen INT UNSIGNED NOT NULL,
    id_bodega_destino INT UNSIGNED NOT NULL,

    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_envio TIMESTAMP NULL,
    fecha_recepcion TIMESTAMP NULL,

    estado ENUM('borrador', 'aprobada', 'en_transito', 'recibida', 'cancelada') NOT NULL DEFAULT 'borrador',

    id_usuario_solicita INT UNSIGNED NULL,
    id_usuario_envia INT UNSIGNED NULL,
    id_usuario_recibe INT UNSIGNED NULL,

    observaciones TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_transferencia),
    INDEX idx_origen (id_bodega_origen),
    INDEX idx_destino (id_bodega_destino),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_transferencia),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega_origen) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega_destino) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_solicita) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_envia) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_recibe) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: transferencias_detalle
-- Descripción: Detalle de productos en transferencias
-- =====================================================
CREATE TABLE transferencias_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transferencia INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    cantidad_solicitada DECIMAL(15,4) NOT NULL,
    cantidad_enviada DECIMAL(15,4) DEFAULT 0,
    cantidad_recibida DECIMAL(15,4) DEFAULT 0,

    id_lote INT UNSIGNED NULL,
    numero_serie VARCHAR(100) NULL,

    costo_unitario DECIMAL(15,4) DEFAULT 0,
    observaciones VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_transferencia (id_transferencia),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_transferencia) REFERENCES transferencias_bodega(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ajustes_inventario
-- Descripción: Ajustes de inventario (diferencias, mermas, etc)
-- =====================================================
CREATE TABLE ajustes_inventario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_ajuste VARCHAR(50) NOT NULL,
    id_bodega INT UNSIGNED NOT NULL,

    tipo_ajuste ENUM('ingreso', 'egreso', 'inventario_fisico', 'merma', 'dano', 'correccion') NOT NULL,
    fecha_ajuste TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    motivo VARCHAR(255) NULL,
    observaciones TEXT NULL,

    id_usuario INT UNSIGNED NULL,
    aprobado_por INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,

    estado ENUM('borrador', 'aprobado', 'contabilizado', 'anulado') NOT NULL DEFAULT 'borrador',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_ajuste),
    INDEX idx_bodega (id_bodega),
    INDEX idx_tipo (tipo_ajuste),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_ajuste),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_ajuste),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (aprobado_por) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ajustes_inventario_detalle
-- Descripción: Detalle de ajustes de inventario
-- =====================================================
CREATE TABLE ajustes_inventario_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_ajuste INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    cantidad_sistema DECIMAL(15,4) DEFAULT 0,
    cantidad_fisica DECIMAL(15,4) NOT NULL,
    diferencia DECIMAL(15,4) AS (cantidad_fisica - cantidad_sistema) STORED,

    costo_unitario DECIMAL(15,4) DEFAULT 0,
    valor_ajuste DECIMAL(15,4) AS (diferencia * costo_unitario) STORED,

    id_lote INT UNSIGNED NULL,
    observaciones VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ajuste (id_ajuste),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_ajuste) REFERENCES ajustes_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reservas_stock
-- Descripción: Reservas de stock para pedidos
-- =====================================================
CREATE TABLE reservas_stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    id_bodega INT UNSIGNED NOT NULL,

    tipo_documento ENUM('pedido', 'cotizacion', 'produccion', 'otro') NOT NULL,
    id_documento INT UNSIGNED NOT NULL,
    numero_documento VARCHAR(50) NULL,

    cantidad_reservada DECIMAL(15,4) NOT NULL,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento TIMESTAMP NULL,

    id_lote INT UNSIGNED NULL,
    estado ENUM('activa', 'consumida', 'liberada', 'vencida') NOT NULL DEFAULT 'activa',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    INDEX idx_bodega (id_bodega),
    INDEX idx_documento (tipo_documento, id_documento),
    INDEX idx_estado (estado),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
