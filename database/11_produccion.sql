-- =====================================================
-- CONECTA ERP - MÓDULO PRODUCCIÓN
-- Archivo: 11_produccion.sql
-- Descripción: Órdenes de producción, BOM, MRP, centros de trabajo
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: bom (Bill of Materials)
-- Descripción: Lista de materiales para fabricación
-- =====================================================
CREATE TABLE bom (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto_final INT UNSIGNED NOT NULL COMMENT 'Producto terminado',
    version VARCHAR(20) DEFAULT '1.0',

    fecha_vigencia DATE NOT NULL,
    fecha_caducidad DATE NULL,

    cantidad_base DECIMAL(15,4) DEFAULT 1 COMMENT 'Cantidad del producto final',

    estado ENUM('borrador', 'activa', 'obsoleta') NOT NULL DEFAULT 'borrador',
    es_principal TINYINT(1) DEFAULT 1,

    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto_final),
    INDEX idx_version (version),
    INDEX idx_estado (estado),
    FOREIGN KEY (id_producto_final) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: bom_detalle
-- Descripción: Componentes de la lista de materiales
-- =====================================================
CREATE TABLE bom_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_bom INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_producto_componente INT UNSIGNED NOT NULL,

    cantidad DECIMAL(15,4) NOT NULL,
    unidad_medida VARCHAR(20) NULL,

    desperdicio_porcentaje DECIMAL(5,2) DEFAULT 0,
    cantidad_neta DECIMAL(15,4) AS (cantidad * (1 + desperdicio_porcentaje/100)) STORED,

    es_opcional TINYINT(1) DEFAULT 0,
    notas VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_bom (id_bom),
    INDEX idx_componente (id_producto_componente),
    FOREIGN KEY (id_bom) REFERENCES bom(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto_componente) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: centros_trabajo
-- Descripción: Centros de trabajo/máquinas
-- =====================================================
CREATE TABLE centros_trabajo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(150) NOT NULL,

    tipo ENUM('maquina', 'linea', 'celula', 'manual') DEFAULT 'maquina',
    id_bodega INT UNSIGNED NULL,

    capacidad_hora DECIMAL(15,4) DEFAULT 0 COMMENT 'Unidades por hora',
    costo_hora DECIMAL(15,4) DEFAULT 0,

    id_responsable INT UNSIGNED NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo),
    INDEX idx_bodega (id_bodega),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: rutas_produccion
-- Descripción: Secuencia de operaciones
-- =====================================================
CREATE TABLE rutas_produccion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_bom INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_centro_trabajo INT UNSIGNED NOT NULL,

    operacion VARCHAR(200) NOT NULL,
    tiempo_setup_minutos INT DEFAULT 0,
    tiempo_operacion_minutos DECIMAL(10,2) NOT NULL,

    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_bom (id_bom),
    INDEX idx_centro (id_centro_trabajo),
    FOREIGN KEY (id_bom) REFERENCES bom(id) ON DELETE CASCADE,
    FOREIGN KEY (id_centro_trabajo) REFERENCES centros_trabajo(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ordenes_produccion
-- Descripción: Órdenes de fabricación
-- =====================================================
CREATE TABLE ordenes_produccion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_orden VARCHAR(50) NOT NULL,

    id_producto INT UNSIGNED NOT NULL,
    id_bom INT UNSIGNED NULL,
    id_bodega INT UNSIGNED NOT NULL,

    cantidad_planificada DECIMAL(15,4) NOT NULL,
    cantidad_producida DECIMAL(15,4) DEFAULT 0,
    cantidad_defectuosa DECIMAL(15,4) DEFAULT 0,
    cantidad_aprobada DECIMAL(15,4) DEFAULT 0,

    fecha_inicio_planificada DATE NOT NULL,
    fecha_fin_planificada DATE NULL,
    fecha_inicio_real TIMESTAMP NULL,
    fecha_fin_real TIMESTAMP NULL,

    estado ENUM('planificada', 'liberada', 'en_proceso', 'pausada', 'finalizada', 'cerrada', 'cancelada') NOT NULL DEFAULT 'planificada',

    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',

    -- Referencia
    tipo_origen ENUM('pedido', 'stock', 'mrp', 'manual') DEFAULT 'manual',
    id_documento_origen INT UNSIGNED NULL,

    observaciones TEXT NULL,
    id_usuario_crea INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_orden),
    INDEX idx_producto (id_producto),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio_planificada, fecha_fin_planificada),
    INDEX idx_bodega (id_bodega),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_orden),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_bom) REFERENCES bom(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: ordenes_produccion_consumos
-- Descripción: Materiales consumidos en la orden
-- =====================================================
CREATE TABLE ordenes_produccion_consumos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    cantidad_planificada DECIMAL(15,4) NOT NULL,
    cantidad_consumida DECIMAL(15,4) DEFAULT 0,

    id_lote INT UNSIGNED NULL,
    fecha_consumo TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_orden (id_orden),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote),
    FOREIGN KEY (id_orden) REFERENCES ordenes_produccion(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_lote) REFERENCES lotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: control_calidad_produccion
-- Descripción: Inspecciones de calidad
-- =====================================================
CREATE TABLE control_calidad_produccion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_orden_produccion INT UNSIGNED NOT NULL,

    fecha_inspeccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cantidad_inspeccionada DECIMAL(15,4) NOT NULL,
    cantidad_aprobada DECIMAL(15,4) DEFAULT 0,
    cantidad_rechazada DECIMAL(15,4) DEFAULT 0,

    motivo_rechazo TEXT NULL,
    inspector VARCHAR(150) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_orden (id_orden_produccion),
    INDEX idx_fecha (fecha_inspeccion),
    FOREIGN KEY (id_orden_produccion) REFERENCES ordenes_produccion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
